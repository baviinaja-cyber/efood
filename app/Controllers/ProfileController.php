<?php

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\View;
use App\Models\User;

class ProfileController
{
    public function index(): void
    {
        $user = Session::user();
        if (!$user) {
            header('Location: ' . View::url('login'));
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM customer_addresses WHERE user_id = :user_id ORDER BY is_default DESC, id DESC");
        $stmt->execute([':user_id' => $user['id']]);
        $addresses = $stmt->fetchAll();

        View::render('frontend.profile', [
            'user' => $user,
            'addresses' => $addresses,
            'pageTitle' => 'Profil Saya - EFOOD'
        ]);
    }

    public function addAddress(): void
    {
        $user = Session::user();
        if (!$user) {
            header('Location: ' . View::url('login'));
            exit;
        }

        $label = Security::sanitizeString($_POST['label'] ?? 'Rumah');
        $name = Security::sanitizeString($_POST['recipient_name'] ?? $user['name']);
        $phone = Security::sanitizePhone($_POST['recipient_phone'] ?? $user['phone']);
        $address = Security::sanitizeString($_POST['address'] ?? '');
        $lat = (float) ($_POST['latitude'] ?? -6.20000000);
        $lng = (float) ($_POST['longitude'] ?? 106.81666600);
        $isDefault = isset($_POST['is_default']) ? 1 : 0;

        if (empty($address)) {
            Session::flash('danger', 'Alamat tidak boleh kosong.');
            header('Location: ' . View::url('akun'));
            exit;
        }

        $db = Database::getConnection();
        if ($isDefault) {
            $db->prepare("UPDATE customer_addresses SET is_default = 0 WHERE user_id = :uid")->execute([':uid' => $user['id']]);
        }

        $stmt = $db->prepare("
            INSERT INTO customer_addresses (user_id, label, recipient_name, recipient_phone, address, latitude, longitude, is_default)
            VALUES (:uid, :label, :name, :phone, :address, :lat, :lng, :is_default)
        ");
        $stmt->execute([
            ':uid' => $user['id'],
            ':label' => $label,
            ':name' => $name,
            ':phone' => $phone,
            ':address' => $address,
            ':lat' => $lat,
            ':lng' => $lng,
            ':is_default' => $isDefault
        ]);

        Session::flash('success', 'Alamat berhasil ditambahkan.');
        header('Location: ' . View::url('akun'));
        exit;
    }
}
