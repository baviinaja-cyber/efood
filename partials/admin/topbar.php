<?php
/**
 * Admin Topbar Partial - EFOOD
 */
$adminUser = \App\Helpers\Session::user();
?>
<div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn btn-sm btn-light border d-lg-none" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
            <i class="fa-solid fa-bars"></i>
        </button>
        <h5 class="mb-0 fw-bold text-dark"><?= \App\Helpers\Security::escape($pageTitle ?? 'Panel Admin') ?></h5>
    </div>

    <div class="topbar-user">
        <div class="text-end d-none d-sm-block">
            <div class="fw-bold fs-6 text-dark"><?= \App\Helpers\Security::escape($adminUser['name'] ?? 'Administrator') ?></div>
            <small class="text-muted text-uppercase" style="font-size: 0.7rem; font-weight: 700;">Super Admin</small>
        </div>
        <div class="user-avatar">
            <?= strtoupper(substr($adminUser['name'] ?? 'A', 0, 1)) ?>
        </div>
    </div>
</div>
