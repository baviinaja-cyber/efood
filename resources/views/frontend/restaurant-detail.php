<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\View;

View::partial('frontend.head', ['pageTitle' => $pageTitle]);
?>
<!-- Top Navigation Header with Back button -->
<div class="d-flex align-items-center justify-content-between p-3 bg-white border-bottom sticky-top">
    <a href="<?= View::url('restoran') ?>" class="btn btn-sm btn-light border rounded-circle p-2" style="width: 36px; height: 36px;">
        <i class="fa-solid fa-arrow-left text-dark"></i>
    </a>
    <h1 class="fs-6 fw-bold mb-0 text-truncate px-2" style="max-width: 260px;"><?= Security::escape($restaurant['name']) ?></h1>
    <a href="<?= View::url('keranjang') ?>" class="btn btn-sm btn-light border rounded-circle p-2 position-relative" style="width: 36px; height: 36px;">
        <i class="fa-solid fa-basket-shopping text-dark"></i>
        <?php 
        $cart = Session::get('cart');
        $cCount = count($cart['items'] ?? []);
        if ($cCount > 0): 
        ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                <?= $cCount ?>
            </span>
        <?php endif; ?>
    </a>
</div>

<?php View::partial('frontend.flash'); ?>

<!-- Restaurant Cover & Profile -->
<div class="position-relative">
    <img src="<?= Security::escape($restaurant['cover_image']) ?>" class="w-100" style="height: 180px; object-fit: cover;" alt="<?= Security::escape($restaurant['name']) ?>">
    <span class="restaurant-status-badge <?= $isOpen ? 'status-badge-open' : 'status-badge-closed' ?>" style="top: 12px; left: 12px;">
        <?= $isOpen ? 'BUKA HARI INI' : 'TUTUP' ?>
    </span>
</div>

<div class="p-3 bg-white border-bottom">
    <div class="d-flex align-items-start justify-content-between">
        <div>
            <h2 class="fs-5 fw-bold mb-1"><?= Security::escape($restaurant['name']) ?></h2>
            <div class="d-flex align-items-center gap-2 text-muted small mb-2">
                <span class="text-warning fw-bold"><i class="fa-solid fa-star"></i> <?= number_format((float) $restaurant['rating'], 1) ?></span>
                <span>&bull;</span>
                <span><?= Security::escape($restaurant['opening_time']) ?> - <?= Security::escape($restaurant['closing_time']) ?></span>
                <span>&bull;</span>
                <span>±<?= $restaurant['preparation_time'] ?> mnt</span>
            </div>
        </div>
    </div>
    <p class="text-secondary small mb-2"><?= Security::escape($restaurant['description']) ?></p>
    <div class="d-flex align-items-center gap-2 text-secondary small bg-light p-2 rounded-2">
        <i class="fa-solid fa-location-dot text-danger"></i>
        <span class="text-truncate"><?= Security::escape($restaurant['address']) ?></span>
    </div>
</div>

<!-- Menu Catalog grouped by Category -->
<div class="pb-5">
    <?php if (empty($groupedMenus)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-utensils fa-2x mb-2 text-secondary"></i>
            <p>Belum ada daftar menu yang aktif untuk restoran ini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($groupedMenus as $catName => $menus): ?>
            <div class="pt-3">
                <div class="px-3 pb-2 border-bottom">
                    <h3 class="fs-6 fw-bold mb-0 text-dark"><?= Security::escape($catName) ?></h3>
                </div>

                <div>
                    <?php foreach ($menus as $m): ?>
                        <div class="menu-card">
                            <div class="menu-info">
                                <h4 class="menu-title"><?= Security::escape($m['name']) ?></h4>
                                <p class="menu-desc"><?= Security::escape($m['description']) ?></p>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="menu-price"><?= Format::rupiah($m['price']) ?></span>
                                    <?php if ($m['stock'] < 10): ?>
                                        <span class="badge bg-warning-subtle text-dark" style="font-size: 0.65rem;">Sisa <?= $m['stock'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="menu-img-wrap">
                                <img src="<?= Security::escape($m['image']) ?>" alt="<?= Security::escape($m['name']) ?>" class="menu-img" loading="lazy">
                                <button type="button" class="btn-add-menu" data-bs-toggle="modal" data-bs-target="#menuModal<?= $m['id'] ?>">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Modal Detail & Tambah Menu ke Keranjang -->
                        <div class="modal fade" id="menuModal<?= $m['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form action="<?= View::url('cart/add') ?>" method="POST">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="menu_id" value="<?= $m['id'] ?>">
                                        <input type="hidden" name="redirect_to" value="<?= View::url('restoran/' . $restaurant['slug']) ?>">

                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold fs-6">Pesan <?= Security::escape($m['name']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <img src="<?= Security::escape($m['image']) ?>" class="w-100 rounded-3 mb-3" style="height: 160px; object-fit: cover;" alt="">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold fs-5 text-danger"><?= Format::rupiah($m['price']) ?></span>
                                                <span class="badge bg-light text-muted border">Stok: <?= $m['stock'] ?></span>
                                            </div>
                                            <p class="text-secondary small"><?= Security::escape($m['description']) ?></p>

                                            <!-- Jumlah Pesanan -->
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Jumlah Porsi</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="number" name="quantity" class="form-control text-center fw-bold" value="1" min="1" max="<?= $m['stock'] ?>" style="width: 100px;">
                                                    <span class="small text-muted">porsi</span>
                                                </div>
                                            </div>

                                            <!-- Catatan Menu -->
                                            <div class="mb-2">
                                                <label class="form-label fw-bold small">Catatan Menu (opsional)</label>
                                                <input type="text" name="notes" class="form-control" placeholder="Contoh: Sambal dipisah, tidak pakai daun bawang...">
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="submit" class="btn btn-efood w-100 py-2">
                                                <i class="fa-solid fa-basket-shopping me-2"></i> Tambah ke Keranjang
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Floating View Cart Sticky Button if Cart has items for this restaurant -->
<?php if (!empty($cart['items']) && $cart['restaurant_id'] == $restaurant['id']): ?>
    <div class="p-3 sticky-bottom bg-white border-top shadow-lg" style="z-index: 1010;">
        <a href="<?= View::url('keranjang') ?>" class="btn btn-efood w-100 py-3 d-flex align-items-center justify-content-between">
            <span><i class="fa-solid fa-basket-shopping me-2"></i> Lihat Keranjang (<?= $cCount ?> item)</span>
            <span class="fw-bold">Lanjut Checkout <i class="fa-solid fa-arrow-right ms-1"></i></span>
        </a>
    </div>
<?php endif; ?>

<?php
View::partial('frontend.bottom-nav', ['activeNav' => 'restaurants']);
View::partial('frontend.footer');
?>
