<?php

namespace App\Controllers\Admin;

use App\Helpers\View;
use App\Models\User;

class CustomerAdminController
{
    public function index(): void
    {
        $customers = User::getAllCustomers();
        View::render('admin.customers.index', [
            'customers' => $customers,
            'pageTitle' => 'Daftar Pelanggan - Admin EFOOD'
        ]);
    }
}
