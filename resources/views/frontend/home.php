<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\View;

View::partial('frontend.head', ['pageTitle' => $pageTitle]);
View::partial('frontend.header', ['isHome' => true]);
View::partial('frontend.flash');
?>

<!-- Promo Banner Carousel / Card -->
<div class="promo-banner">
    <div class="d-flex align-items-center justify-content-between position-relative" style="z-index: 2;">
        <div>
            <span class="badge bg-white text-danger fw-bold mb-2">PROMO GAJIAN</span>
            <h3 class="promo-title">Diskon s/d 50%</h3>
            <p class="promo-subtitle">Makan enak serba hemat bareng EFOOD hari ini!</p>
        </div>
        <div class="d-none d-sm-block text-end">
            <i class="fa-solid fa-gift fa-3x text-white opacity-75"></i>
        </div>
    </div>
</div>

<!-- Kategori Makanan -->
<div class="mt-2">
    <div class="section-head">
        <h2 class="section-title">Kategori Kuliner</h2>
        <a href="<?= View::url('restoran') ?>" class="section-link">Lihat Semua <i class="fa-solid fa-angle-right"></i></a>
    </div>

    <div class="category-scroll">
        <?php foreach ($categories as $cat): ?>
            <a href="<?= View::url('restoran?cat=' . $cat['slug']) ?>" class="category-item">
                <div class="category-icon-box">
                    <i class="fa-solid fa-<?= Security::escape($cat['icon']) ?>"></i>
                </div>
                <span class="category-name"><?= Security::escape($cat['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Restoran Populer Terdekat -->
<div class="mt-3">
    <div class="section-head">
        <div>
            <h2 class="section-title">Restoran Terdekat & Populer</h2>
            <small class="text-muted" style="font-size: 0.78rem;">Pilihan mitra favorit di sekitar lokasi Anda</small>
        </div>
        <a href="<?= View::url('restoran') ?>" class="section-link">Semua Mitra <i class="fa-solid fa-angle-right"></i></a>
    </div>

    <?php if (empty($restaurants)): ?>
        <div class="p-4 text-center text-muted">
            <i class="fa-solid fa-store-slash fa-2x mb-2 text-secondary"></i>
            <p>Belum ada restoran yang tersedia di area ini.</p>
        </div>
    <?php else: ?>
        <div class="mt-2">
            <?php foreach ($restaurants as $res): ?>
                <?php
                $currentTime = date('H:i:s');
                $isOpen = ($currentTime >= $res['opening_time'] && $currentTime <= $res['closing_time']);
                ?>
                <a href="<?= View::url('restoran/' . $res['slug']) ?>" class="restaurant-card">
                    <div class="restaurant-img-wrap">
                        <img src="<?= Security::escape($res['cover_image']) ?>" alt="<?= Security::escape($res['name']) ?>" class="restaurant-img" loading="lazy">
                        <span class="restaurant-status-badge <?= $isOpen ? 'status-badge-open' : 'status-badge-closed' ?>">
                            <?= $isOpen ? 'BUKA SEKARANG' : 'TUTUP' ?>
                        </span>
                        <span class="restaurant-time-badge">
                            <i class="fa-regular fa-clock me-1"></i> ±<?= $res['preparation_time'] ?> mnt
                        </span>
                    </div>
                    <div class="restaurant-content">
                        <div class="d-flex align-items-center justify-content-between">
                            <h3 class="restaurant-name"><?= Security::escape($res['name']) ?></h3>
                            <div class="meta-rating">
                                <i class="fa-solid fa-star"></i> <?= number_format((float) $res['rating'], 1) ?>
                            </div>
                        </div>
                        <p class="text-muted text-truncate mb-2" style="font-size: 0.8rem;">
                            <?= Security::escape($res['description']) ?>
                        </p>
                        <div class="restaurant-meta">
                            <span><i class="fa-solid fa-motorcycle text-primary me-1"></i> Ongkir mulai Rp 9.000</span>
                            <span>&bull;</span>
                            <span>Min. <?= Format::rupiah($res['minimum_order']) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Rekomendasi Menu Hari Ini -->
<?php if (!empty($featuredMenus)): ?>
<div class="mt-3 mb-4">
    <div class="section-head">
        <h2 class="section-title">Menu Rekomendasi Paling Laris</h2>
    </div>

    <div class="px-3">
        <?php foreach ($featuredMenus as $menu): ?>
            <div class="card border mb-3 rounded-3 overflow-hidden shadow-sm">
                <div class="row g-0 align-items-center">
                    <div class="col-4">
                        <img src="<?= Security::escape($menu['image']) ?>" class="img-fluid rounded-start h-100 object-fit-cover" style="min-height: 100px;" alt="<?= Security::escape($menu['name']) ?>">
                    </div>
                    <div class="col-8">
                        <div class="card-body p-3">
                            <h5 class="card-title fs-6 fw-bold mb-1"><?= Security::escape($menu['name']) ?></h5>
                            <p class="card-text text-muted small text-truncate mb-2"><?= Security::escape($menu['description']) ?></p>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-bold text-danger"><?= Format::rupiah($menu['price']) ?></span>
                                <form action="<?= View::url('cart/add') ?>" method="POST">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="menu_id" value="<?= $menu['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold">
                                        <i class="fa-solid fa-plus me-1"></i> Tambah
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php
View::partial('frontend.bottom-nav', ['activeNav' => 'home']);
View::partial('frontend.footer');
?>
