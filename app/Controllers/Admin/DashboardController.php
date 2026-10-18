<?php

namespace App\Controllers\Admin;

use App\Helpers\View;
use App\Models\Order;

class DashboardController
{
    public function index(): void
    {
        $stats = Order::getDashboardStats();
        $recentOrders = Order::getAllAdmin(null, null);
        $recentOrders = array_slice($recentOrders, 0, 10);

        View::render('admin.dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'pageTitle' => 'Dashboard Utama - Admin EFOOD'
        ]);
    }
}
