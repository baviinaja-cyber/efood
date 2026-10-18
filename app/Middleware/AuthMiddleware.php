<?php

namespace App\Middleware;

use App\Helpers\Session;
use App\Helpers\View;

class AuthMiddleware
{
    public static function handle(): bool
    {
        if (!Session::check()) {
            Session::flash('warning', 'Silakan masuk ke akun Anda terlebih dahulu.');
            $redirect = View::url('login');
            header("Location: {$redirect}");
            exit;
        }
        return true;
    }
}
