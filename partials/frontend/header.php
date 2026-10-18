<?php
/**
 * Frontend Header Partial - EFOOD
 */
$cart = \App\Helpers\Session::get('cart', ['items' => []]);
$cartCount = 0;
if (!empty($cart['items'])) {
    foreach ($cart['items'] as $it) {
        $cartCount += ($it['quantity'] ?? 1);
    }
}
$isHome = empty($isDetail);
?>
<header class="efood-header">
    <div class="d-flex align-items-center justify-content-between">
        <a href="<?= \App\Helpers\View::url() ?>" class="efood-brand">
            <i class="fa-solid fa-bowl-food"></i> EFOOD
            <span class="brand-badge">SUPER APP</span>
        </a>

        <div class="d-flex align-items-center gap-2">
            <!-- Unduh ZIP Source Code Banner/Tombol Cepat -->
            <a href="<?= \App\Helpers\View::url('download-zip') ?>" class="btn btn-sm btn-outline-warning text-dark fw-bold rounded-pill px-2 py-1" title="Unduh Source Code Siap Upload ke Hosting">
                <i class="fa-solid fa-cloud-arrow-down me-1"></i> <span class="d-none d-sm-inline">Unduh</span> ZIP
            </a>

            <!-- Tombol Keranjang -->
            <a href="<?= \App\Helpers\View::url('keranjang') ?>" class="btn btn-sm btn-light border position-relative rounded-circle p-2" style="width: 38px; height: 38px;">
                <i class="fa-solid fa-basket-shopping text-dark"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                        <?= $cartCount ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <?php if ($isHome): ?>
        <!-- Lokasi Pengiriman & Tombol GPS -->
        <div class="location-selector">
            <div class="location-text">
                <i class="fa-solid fa-location-dot text-danger"></i>
                <span id="current-location-label">Kebayoran Baru, Jakarta Selatan</span>
            </div>
            <button type="button" class="btn-gps-header" onclick="detectMyLocation()">
                <i class="fa-solid fa-crosshairs"></i> GPS
            </button>
        </div>

        <!-- Kolom Pencarian -->
        <form action="<?= \App\Helpers\View::url('restoran') ?>" method="GET" class="search-bar-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" name="q" class="search-input" placeholder="Mau makan apa hari ini? Cari restoran / menu..." value="<?= \App\Helpers\Security::escape($_GET['q'] ?? '') ?>">
        </form>
    <?php endif; ?>
</header>
