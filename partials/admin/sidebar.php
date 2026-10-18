<?php
/**
 * Admin Sidebar Partial - EFOOD
 */
$activeMenu = $activeMenu ?? 'dashboard';
?>
<aside class="admin-sidebar" id="adminSidebar">
    <a href="<?= \App\Helpers\View::url('admin/dashboard') ?>" class="sidebar-brand">
        <i class="fa-solid fa-bowl-food text-warning"></i>
        <span>EFOOD</span> Admin
    </a>

    <ul class="sidebar-menu">
        <li class="menu-category">Utama</li>
        <li class="menu-item <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
            <a href="<?= \App\Helpers\View::url('admin/dashboard') ?>">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="menu-item <?= $activeMenu === 'orders' ? 'active' : '' ?>">
            <a href="<?= \App\Helpers\View::url('admin/pesanan') ?>">
                <i class="fa-solid fa-receipt"></i>
                <span>Pesanan Masuk</span>
            </a>
        </li>

        <li class="menu-category">Manajemen Mitra & Menu</li>
        <li class="menu-item <?= $activeMenu === 'restaurants' ? 'active' : '' ?>">
            <a href="<?= \App\Helpers\View::url('admin/restoran') ?>">
                <i class="fa-solid fa-store"></i>
                <span>Restoran Mitra</span>
            </a>
        </li>
        <li class="menu-item <?= $activeMenu === 'menus' ? 'active' : '' ?>">
            <a href="<?= \App\Helpers\View::url('admin/menu') ?>">
                <i class="fa-solid fa-utensils"></i>
                <span>Menu Makanan</span>
            </a>
        </li>

        <li class="menu-category">Logistik & Pelanggan</li>
        <li class="menu-item <?= $activeMenu === 'drivers' ? 'active' : '' ?>">
            <a href="<?= \App\Helpers\View::url('admin/driver') ?>">
                <i class="fa-solid fa-motorcycle"></i>
                <span>Armada Driver</span>
            </a>
        </li>
        <li class="menu-item <?= $activeMenu === 'customers' ? 'active' : '' ?>">
            <a href="<?= \App\Helpers\View::url('admin/pelanggan') ?>">
                <i class="fa-solid fa-users"></i>
                <span>Pelanggan</span>
            </a>
        </li>

        <li class="menu-category">Konfigurasi</li>
        <li class="menu-item <?= $activeMenu === 'settings' ? 'active' : '' ?>">
            <a href="<?= \App\Helpers\View::url('admin/pengaturan') ?>">
                <i class="fa-solid fa-gear"></i>
                <span>Pengaturan & Tarif</span>
            </a>
        </li>

        <li class="menu-category">Toko & Rilis</li>
        <li class="menu-item">
            <a href="<?= \App\Helpers\View::url() ?>" target="_blank">
                <i class="fa-solid fa-mobile-screen"></i>
                <span>Lihat Aplikasi Web</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="<?= \App\Helpers\View::url('download-zip') ?>" class="text-warning">
                <i class="fa-solid fa-cloud-arrow-down"></i>
                <span>Unduh ZIP Source Code</span>
            </a>
        </li>
    </ul>

    <div class="p-3 border-top border-secondary">
        <a href="<?= \App\Helpers\View::url('logout') ?>" class="btn btn-outline-danger btn-sm w-100 fw-bold">
            <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Keluar
        </a>
    </div>
</aside>
