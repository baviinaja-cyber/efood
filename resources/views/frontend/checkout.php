<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\View;

View::partial('frontend.head', ['pageTitle' => $pageTitle]);
$initialGrandTotal = $subtotal + $estimatedFee;
?>

<div class="d-flex align-items-center justify-content-between p-3 bg-white border-bottom sticky-top">
    <a href="<?= View::url('keranjang') ?>" class="btn btn-sm btn-light border rounded-circle p-2" style="width: 36px; height: 36px;">
        <i class="fa-solid fa-arrow-left text-dark"></i>
    </a>
    <h1 class="fs-6 fw-bold mb-0">Pengiriman & Checkout</h1>
    <div style="width: 36px;"></div>
</div>

<?php View::partial('frontend.flash'); ?>

<form action="<?= View::url('checkout/place-order') ?>" method="POST" id="checkout-form">
    <?= Security::csrfField() ?>

    <!-- Hidden Inputs untuk Subtotal & Koordinat GPS -->
    <input type="hidden" id="subtotal-val" value="<?= $subtotal ?>">
    <input type="hidden" name="delivery_latitude" id="delivery_latitude" value="<?= $defaultLat ?>">
    <input type="hidden" name="delivery_longitude" id="delivery_longitude" value="<?= $defaultLng ?>">

    <!-- Ringkasan Restoran -->
    <div class="p-3 bg-white mb-2 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-store text-danger fs-5"></i>
            <div>
                <h2 class="fs-6 fw-bold mb-0"><?= Security::escape($restaurant['name']) ?></h2>
                <small class="text-muted" style="font-size: 0.72rem;"><?= Security::escape($restaurant['address']) ?></small>
            </div>
        </div>
    </div>

    <!-- Peta Titik Pengantaran Leaflet & GPS -->
    <div class="p-3 bg-white mb-2 border-bottom">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h3 class="fs-6 fw-bold mb-0">Titik Lokasi Pengiriman</h3>
            <button type="button" class="btn btn-sm btn-outline-danger fw-bold rounded-pill" onclick="detectMyLocation(<?= $restaurant['id'] ?>)">
                <i class="fa-solid fa-crosshairs me-1"></i> Gunakan Lokasi Saya
            </button>
        </div>

        <p class="text-secondary small mb-2">
            Peta OpenStreetMap dengan Pin interaktif. Silakan geser pin oranye tepat di gerbang atau pintu rumah Anda:
        </p>

        <!-- Container Peta Leaflet -->
        <div id="map-container" class="mb-2"></div>

        <div id="gps-status-badge" class="d-none"></div>

        <div class="d-flex align-items-center justify-content-between text-muted small mt-1">
            <span><i class="fa-solid fa-location-crosshairs text-danger me-1"></i> Koordinat:</span>
            <code id="coord-display"><?= number_format($defaultLat, 6) ?>, <?= number_format($defaultLng, 6) ?></code>
        </div>
    </div>

    <!-- Data Penerima & Alamat Lengkap -->
    <div class="p-3 bg-white mb-2 border-bottom">
        <h3 class="fs-6 fw-bold mb-3">Informasi Penerima</h3>

        <div class="mb-3">
            <label class="form-label small fw-bold">Nama Penerima</label>
            <input type="text" name="customer_name" class="form-control" value="<?= Security::escape($user['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Nomor WhatsApp Aktif</label>
            <input type="tel" name="customer_phone" class="form-control" value="<?= Security::escape($user['phone']) ?>" required placeholder="Contoh: 081234567890">
            <small class="text-muted" style="font-size: 0.7rem;">Kurir & restoran akan menghubungi lewat nomor WhatsApp ini.</small>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Alamat Lengkap & Patokan Rumah</label>
            <textarea name="delivery_address" id="delivery_address" class="form-control" rows="2" required placeholder="Nama jalan, nomor rumah, RT/RW, warna pagar, patokan...">Jl. Wijaya Timur No. 17, Melawai, Kebayoran Baru, Jakarta Selatan</textarea>
        </div>

        <div class="mb-2">
            <label class="form-label small fw-bold">Catatan untuk Driver / Restoran (opsional)</label>
            <input type="text" name="customer_note" class="form-control" placeholder="Contoh: Titip ke satpam / Sambalnya pedas manis">
        </div>
    </div>

    <!-- Metode Pembayaran -->
    <div class="p-3 bg-white mb-2 border-bottom">
        <h3 class="fs-6 fw-bold mb-3">Metode Pembayaran</h3>

        <div class="form-check p-3 border rounded-3 mb-2 bg-light">
            <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="cod" checked>
            <label class="form-check-label w-100 ms-2" for="pay_cod">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-bold"><i class="fa-solid fa-money-bill-wave text-success me-2"></i> Bayar di Tempat (Tunai / COD)</span>
                    <span class="badge bg-success-subtle text-success">Instan</span>
                </div>
                <small class="text-muted d-block mt-1">Bayar tunai kepada driver EFOOD saat makanan tiba di tempat.</small>
            </label>
        </div>

        <div class="form-check p-3 border rounded-3 mb-2">
            <input class="form-check-input" type="radio" name="payment_method" id="pay_transfer" value="transfer">
            <label class="form-check-label w-100 ms-2" for="pay_transfer">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-bold"><i class="fa-solid fa-building-columns text-primary me-2"></i> Transfer Bank Manual</span>
                    <span class="badge bg-light text-secondary border">Verifikasi</span>
                </div>
                <small class="text-muted d-block mt-1"><?= Security::escape($bankInfo) ?></small>
            </label>
        </div>
    </div>

    <!-- Rincian Biaya -->
    <div class="p-3 bg-white mb-4 border-bottom">
        <h3 class="fs-6 fw-bold mb-3">Rincian Pembayaran</h3>

        <div class="d-flex justify-content-between text-muted small mb-2">
            <span>Subtotal Makanan</span>
            <span class="fw-semibold text-dark"><?= Format::rupiah($subtotal) ?></span>
        </div>

        <div class="d-flex justify-content-between text-muted small mb-2">
            <span>Jarak Pengantaran Estimasi</span>
            <span class="fw-semibold text-dark" id="distance-display">1.5 km</span>
        </div>

        <div class="d-flex justify-content-between text-muted small mb-2">
            <span>Ongkos Kirim</span>
            <span class="fw-semibold text-dark" id="delivery-fee-display"><?= Format::rupiah($estimatedFee) ?></span>
        </div>

        <hr class="my-2">

        <div class="d-flex justify-content-between align-items-center pt-1">
            <span class="fw-bold fs-6">Total Pembayaran</span>
            <span class="fw-bold fs-5 text-danger" id="grand-total-display"><?= Format::rupiah($initialGrandTotal) ?></span>
        </div>
    </div>

    <!-- Tombol Buat Pesanan -->
    <div class="p-3 pb-5">
        <button type="submit" class="btn btn-efood w-100 py-3 fs-6">
            <i class="fa-solid fa-circle-check me-2"></i> Konfirmasi & Pesan Sekarang
        </button>
        <p class="text-center text-muted small mt-2">
            <i class="fa-solid fa-shield-halved text-success me-1"></i> Pesanan terenkripsi aman dan langsung diverifikasi admin.
        </p>
    </div>
</form>

<?php
View::partial('frontend.bottom-nav', ['activeNav' => 'cart']);
View::partial('frontend.footer');
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    initCheckoutMap(
        <?= (float) $defaultLat ?>, 
        <?= (float) $defaultLng ?>, 
        <?= (float) $restaurant['latitude'] ?>, 
        <?= (float) $restaurant['longitude'] ?>, 
        <?= (int) $restaurant['id'] ?>
    );
});
</script>
