<?php

namespace App\Middleware;

use App\Helpers\Session;
use App\Helpers\View;

class AdminMiddleware
{
    public static function handle(): bool
    {
        if (!Session::isAdmin()) {
            Session::flash('danger', 'Akses terbatas untuk Administrator EFOOD.');
            $redirect = View::url('admin/login');
            header("Location: {$redirect}");
            exit;
        }
        return true;
    }
}
