<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\View;

View::partial('frontend.head', ['pageTitle' => $pageTitle]);
$pendingSwitch = Session::get('pending_cart_switch');
?>

<div class="d-flex align-items-center justify-content-between p-3 bg-white border-bottom sticky-top">
    <a href="<?= View::url() ?>" class="btn btn-sm btn-light border rounded-circle p-2" style="width: 36px; height: 36px;">
        <i class="fa-solid fa-arrow-left text-dark"></i>
    </a>
    <h1 class="fs-6 fw-bold mb-0">Keranjang Belanja</h1>
    <div style="width: 36px;"></div>
</div>

<?php View::partial('frontend.flash'); ?>

<!-- Konfirmasi Pergantian Restoran jika ada -->
<?php if (!empty($pendingSwitch)): ?>
    <div class="m-3 p-3 bg-warning-subtle border border-warning rounded-3">
        <h5 class="fs-6 fw-bold text-dark mb-1">
            <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Ganti Restoran Mitra?
        </h5>
        <p class="small text-secondary mb-3">
            Satu pesanan hanya dapat berasal dari 1 restoran. Ingin mengosongkan keranjang dari <b><?= Security::escape($cart['restaurant_name']) ?></b> dan memesan dari <b><?= Security::escape($pendingSwitch['target_restaurant_name']) ?></b>?
        </p>
        <div class="d-flex gap-2">
            <form action="<?= View::url('cart/add') ?>" method="POST" class="flex-grow-1">
                <?= Security::csrfField() ?>
                <input type="hidden" name="menu_id" value="<?= $pendingSwitch['menu_id'] ?>">
                <input type="hidden" name="quantity" value="<?= $pendingSwitch['quantity'] ?>">
                <input type="hidden" name="notes" value="<?= Security::escape($pendingSwitch['notes']) ?>">
                <input type="hidden" name="force_clear" value="1">
                <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold">
                    Kosongkan & Pesan Ini
                </button>
            </form>
            <form action="<?= View::url('cart/clear-switch') ?>" method="POST">
                <?= Security::csrfField() ?>
                <a href="<?= View::url('keranjang') ?>" class="btn btn-sm btn-light border">Batal</a>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if (empty($cart['items'])): ?>
    <!-- Keranjang Kosong -->
    <div class="text-center py-5 px-3">
        <div class="bg-light d-inline-flex p-4 rounded-circle mb-3">
            <i class="fa-solid fa-basket-shopping fa-3x text-secondary"></i>
        </div>
        <h2 class="fs-5 fw-bold mb-1">Keranjang Masih Kosong</h2>
        <p class="text-muted small mb-4">Yuk cari makanan favoritmu dari berbagai restoran pilihan di EFOOD.</p>
        <a href="<?= View::url() ?>" class="btn btn-efood px-4 py-2">
            <i class="fa-solid fa-utensils me-1"></i> Mulai Pesan Makanan
        </a>
    </div>
<?php else: ?>
    <!-- Restoran Asal -->
    <div class="p-3 bg-white border-bottom">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-store text-danger fs-5"></i>
                <div>
                    <h2 class="fs-6 fw-bold mb-0"><?= Security::escape($cart['restaurant_name']) ?></h2>
                    <small class="text-muted" style="font-size: 0.72rem;">Pesanan Anda disiapkan dari cabang ini</small>
                </div>
            </div>
            <form action="<?= View::url('cart/clear') ?>" method="POST" onsubmit="return confirm('Kosongkan semua menu dari keranjang?')">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-bold" style="font-size: 0.78rem;">
                    <i class="fa-regular fa-trash-can me-1"></i> Kosongkan
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Menu di Keranjang -->
    <div class="p-3 bg-white mb-3 border-bottom">
        <?php foreach ($cart['items'] as $menuId => $it): ?>
            <div class="d-flex align-items-center justify-content-between py-3 border-bottom <?= $menuId === array_key_last($cart['items']) ? 'border-0 pb-0' : '' ?>">
                <div class="d-flex align-items-center gap-3">
                    <img src="<?= Security::escape($it['image']) ?>" class="rounded-2 object-fit-cover" style="width: 54px; height: 54px;" alt="">
                    <div>
                        <h3 class="fs-6 fw-bold mb-0"><?= Security::escape($it['name']) ?></h3>
                        <div class="text-danger fw-bold small"><?= Format::rupiah($it['price']) ?></div>
                        <?php if (!empty($it['notes'])): ?>
                            <small class="text-muted fst-italic d-block" style="font-size: 0.72rem;">
                                "<?= Security::escape($it['notes']) ?>"
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pengatur Kuantitas -->
                <div class="d-flex align-items-center gap-2">
                    <form action="<?= View::url('cart/update') ?>" method="POST" class="d-inline">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="menu_id" value="<?= $menuId ?>">
                        <input type="hidden" name="action" value="decrease">
                        <button type="submit" class="btn btn-sm btn-light border rounded-circle p-0" style="width: 26px; height: 26px;">
                            <i class="fa-solid fa-minus text-secondary" style="font-size: 10px;"></i>
                        </button>
                    </form>

                    <span class="fw-bold px-1" style="min-width: 20px; text-align: center;"><?= $it['quantity'] ?></span>

                    <form action="<?= View::url('cart/update') ?>" method="POST" class="d-inline">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="menu_id" value="<?= $menuId ?>">
                        <input type="hidden" name="action" value="increase">
                        <button type="submit" class="btn btn-sm btn-light border rounded-circle p-0" style="width: 26px; height: 26px;">
                            <i class="fa-solid fa-plus text-secondary" style="font-size: 10px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Ringkasan Pembayaran Awal -->
    <div class="p-3 bg-white mb-4 border-bottom">
        <h4 class="fs-6 fw-bold mb-3">Ringkasan Pesanan</h4>
        <div class="d-flex justify-content-between text-muted small mb-2">
            <span>Subtotal Menu</span>
            <span class="fw-semibold text-dark"><?= Format::rupiah($subtotal) ?></span>
        </div>
        <div class="d-flex justify-content-between text-muted small mb-2">
            <span>Ongkos Kirim Estimasi</span>
            <span class="fw-semibold text-dark">Dihitung saat Checkout</span>
        </div>
        <?php if ($restaurant && $subtotal < $restaurant['minimum_order']): ?>
            <div class="alert alert-warning small p-2 mt-2 mb-0">
                <i class="fa-solid fa-circle-exclamation me-1"></i> Minimum pemesanan <?= Format::rupiah($restaurant['minimum_order']) ?>. Kurang <?= Format::rupiah($restaurant['minimum_order'] - $subtotal) ?> lagi.
            </div>
        <?php endif; ?>
    </div>

    <!-- Checkout Button -->
    <div class="p-3">
        <?php $canCheckout = !$restaurant || ($subtotal >= $restaurant['minimum_order']); ?>
        <a href="<?= $canCheckout ? View::url('checkout') : '#' ?>" class="btn btn-efood w-100 py-3 d-flex align-items-center justify-content-between <?= !$canCheckout ? 'disabled opacity-50' : '' ?>">
            <span>Lanjut ke Pengiriman</span>
            <span class="fw-bold"><?= Format::rupiah($subtotal) ?> <i class="fa-solid fa-arrow-right ms-1"></i></span>
        </a>
    </div>
<?php endif; ?>

<?php
View::partial('frontend.bottom-nav', ['activeNav' => 'cart']);
View::partial('frontend.footer');
?>
