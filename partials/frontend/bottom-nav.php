<?php
/**
 * Bottom Navigation Partial - EFOOD Mobile First
 */
$currentUri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$activeNav = $activeNav ?? 'home';

$cart = \App\Helpers\Session::get('cart', ['items' => []]);
$cartItemCount = count($cart['items'] ?? []);
?>
<nav class="bottom-nav">
    <a href="<?= \App\Helpers\View::url() ?>" class="bottom-nav-item <?= $activeNav === 'home' ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i>
        <span>Beranda</span>
    </a>

    <a href="<?= \App\Helpers\View::url('restoran') ?>" class="bottom-nav-item <?= $activeNav === 'restaurants' ? 'active' : '' ?>">
        <i class="fa-solid fa-store"></i>
        <span>Restoran</span>
    </a>

    <a href="<?= \App\Helpers\View::url('keranjang') ?>" class="bottom-nav-item <?= $activeNav === 'cart' ? 'active' : '' ?>">
        <div class="position-relative">
            <i class="fa-solid fa-basket-shopping"></i>
            <?php if ($cartItemCount > 0): ?>
                <span class="nav-badge"><?= $cartItemCount ?></span>
            <?php endif; ?>
        </div>
        <span>Keranjang</span>
    </a>

    <a href="<?= \App\Helpers\View::url('pesanan') ?>" class="bottom-nav-item <?= $activeNav === 'orders' ? 'active' : '' ?>">
        <i class="fa-solid fa-receipt"></i>
        <span>Pesanan</span>
    </a>

    <a href="<?= \App\Helpers\View::url('akun') ?>" class="bottom-nav-item <?= $activeNav === 'account' ? 'active' : '' ?>">
        <i class="fa-solid fa-user"></i>
        <span>Akun</span>
    </a>
</nav>
