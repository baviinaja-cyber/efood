<?php

namespace App\Controllers;

use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\View;
use App\Models\User;

class AuthController
{
    public function showLogin(): void
    {
        if (Session::check()) {
            if (Session::isAdmin()) {
                header('Location: ' . View::url('admin/dashboard'));
            } else {
                header('Location: ' . View::url());
            }
            exit;
        }

        View::render('frontend.login', [
            'pageTitle' => 'Masuk ke Akun - EFOOD'
        ]);
    }

    public function login(): void
    {
        $email = Security::sanitizeString($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Session::flash('danger', 'Email dan kata sandi wajib diisi.');
            header('Location: ' . View::url('login'));
            exit;
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::flash('danger', 'Email atau kata sandi yang Anda masukkan salah.');
            header('Location: ' . View::url('login'));
            exit;
        }

        if ($user['status'] !== 'active') {
            Session::flash('danger', 'Akun Anda sedang dinonaktifkan.');
            header('Location: ' . View::url('login'));
            exit;
        }

        // Keamanan: Regenerate Session ID
        Session::regenerate();
        Session::set('user', [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role']
        ]);

        Session::flash('success', "Selamat datang kembali, {$user['name']}!");

        // Redirect
        $redirect = Session::get('redirect_after_login');
        Session::remove('redirect_after_login');

        if ($redirect) {
            header("Location: {$redirect}");
        } elseif ($user['role'] === 'admin') {
            header('Location: ' . View::url('admin/dashboard'));
        } else {
            header('Location: ' . View::url());
        }
        exit;
    }

    public function showRegister(): void
    {
        if (Session::check()) {
            header('Location: ' . View::url());
            exit;
        }

        View::render('frontend.register', [
            'pageTitle' => 'Daftar Akun Baru - EFOOD'
        ]);
    }

    public function register(): void
    {
        $name = Security::sanitizeString($_POST['name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $phone = Security::sanitizePhone($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';

        if (empty($name) || !$email || empty($phone) || empty($password)) {
            Session::flash('danger', 'Semua data registrasi wajib diisi dengan benar.');
            header('Location: ' . View::url('register'));
            exit;
        }

        if (strlen($password) < 6) {
            Session::flash('danger', 'Kata sandi minimal 6 karakter.');
            header('Location: ' . View::url('register'));
            exit;
        }

        if ($password !== $passwordConfirm) {
            Session::flash('danger', 'Konfirmasi kata sandi tidak cocok.');
            header('Location: ' . View::url('register'));
            exit;
        }

        if (User::findByEmail($email)) {
            Session::flash('danger', 'Email sudah terdaftar. Silakan gunakan email lain atau login.');
            header('Location: ' . View::url('register'));
            exit;
        }

        $userId = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => 'customer',
            'status' => 'active'
        ]);

        Session::regenerate();
        Session::set('user', [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => 'customer'
        ]);

        Session::flash('success', 'Pendaftaran akun berhasil! Selamat berbelanja di EFOOD.');
        header('Location: ' . View::url());
        exit;
    }

    public function logout(): void
    {
        Session::destroy();
        header('Location: ' . View::url());
        exit;
    }

    public function showAdminLogin(): void
    {
        if (Session::isAdmin()) {
            header('Location: ' . View::url('admin/dashboard'));
            exit;
        }

        View::render('admin.login', [
            'pageTitle' => 'Masuk Panel Admin - EFOOD'
        ]);
    }

    public function adminLogin(): void
    {
        $email = Security::sanitizeString($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password']) || $user['role'] !== 'admin') {
            Session::flash('danger', 'Kredensial Admin tidak valid atau tidak memiliki akses.');
            header('Location: ' . View::url('admin/login'));
            exit;
        }

        Session::regenerate();
        Session::set('user', [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => 'admin'
        ]);

        Session::flash('success', "Selamat datang di Panel Admin EFOOD, {$user['name']}!");
        header('Location: ' . View::url('admin/dashboard'));
        exit;
    }
}
