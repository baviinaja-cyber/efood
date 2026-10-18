<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\View;

View::partial('frontend.head', ['pageTitle' => $pageTitle]);

$stepIdx = Format::statusStepIndex($order['status']);
?>

<div class="d-flex align-items-center justify-content-between p-3 bg-white border-bottom sticky-top">
    <a href="<?= View::url('pesanan') ?>" class="btn btn-sm btn-light border rounded-circle p-2" style="width: 36px; height: 36px;">
        <i class="fa-solid fa-arrow-left text-dark"></i>
    </a>
    <h1 class="fs-6 fw-bold mb-0">Status & Pelacakan Pesanan</h1>
    <a href="<?= View::url() ?>" class="btn btn-sm btn-light border rounded-circle p-2" style="width: 36px; height: 36px;">
        <i class="fa-solid fa-house text-dark"></i>
    </a>
</div>

<?php View::partial('frontend.flash'); ?>

<!-- Header Status Kartu -->
<div class="p-3 bg-white mb-2 border-bottom text-center">
    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Nomor Pesanan</small>
    <div class="fs-5 fw-bold text-dark mb-2"><?= Security::escape($order['order_number']) ?></div>
    
    <div>
        <span id="order-status-badge" class="badge px-3 py-2 fs-6 shadow-sm <?= Format::statusBadgeClass($order['status']) ?>">
            <?= Format::statusLabel($order['status']) ?>
        </span>
    </div>

    <div class="text-muted small mt-2">
        <i class="fa-regular fa-clock me-1"></i> Dibuat pada <?= Format::datetime($order['created_at']) ?>
    </div>
</div>

<!-- Informasi Driver jika sudah ditugaskan -->
<div id="driver-info-box" class="p-3 bg-white mb-2 border-bottom <?= empty($order['driver_name']) ? 'd-none' : '' ?>">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
            <i class="fa-solid fa-motorcycle fs-5"></i>
        </div>
        <div class="flex-grow-1">
            <small class="text-muted d-block" style="font-size: 0.72rem;">Driver Pengantar Anda</small>
            <div class="fw-bold fs-6 text-dark" id="driver-name-display"><?= Security::escape($order['driver_name'] ?? '') ?></div>
            <div class="text-muted small">
                Plat: <span class="badge bg-light text-dark border" id="driver-plate-display"><?= Security::escape($order['driver_plate'] ?? '') ?></span>
            </div>
        </div>
        <?php if (!empty($order['driver_whatsapp'])): ?>
            <a href="https://wa.me/<?= Security::escape($order['driver_whatsapp']) ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold">
                <i class="fa-brands fa-whatsapp me-1"></i> Chat
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Timeline Progres Pesanan Super App -->
<div class="p-3 bg-white mb-2 border-bottom">
    <h3 class="fs-6 fw-bold mb-3">Timeline Proses Pesanan</h3>

    <div class="tracking-timeline">
        <!-- Step 1: Pesanan Masuk -->
        <div class="timeline-step <?= $stepIdx > 1 ? 'completed' : ($stepIdx === 1 ? 'active' : '') ?>" data-step="1">
            <div class="timeline-dot">
                <i class="fa-solid <?= $stepIdx > 1 ? 'fa-check' : 'fa-receipt' ?>"></i>
            </div>
            <div class="timeline-title">Pesanan Dibuat</div>
            <div class="timeline-desc">Pesanan masuk ke sistem dan menunggu verifikasi admin.</div>
        </div>

        <!-- Step 2: Verifikasi Admin -->
        <div class="timeline-step <?= $stepIdx > 2 ? 'completed' : ($stepIdx === 2 ? 'active' : '') ?>" data-step="2">
            <div class="timeline-dot">
                <i class="fa-solid <?= $stepIdx > 2 ? 'fa-check' : 'fa-user-check' ?>"></i>
            </div>
            <div class="timeline-title">Diverifikasi Admin</div>
            <div class="timeline-desc">Admin telah mengecek data dan meneruskan pesanan ke restoran via WhatsApp.</div>
        </div>

        <!-- Step 3: Proses Restoran -->
        <div class="timeline-step <?= $stepIdx > 3 ? 'completed' : ($stepIdx === 3 ? 'active' : '') ?>" data-step="3">
            <div class="timeline-dot">
                <i class="fa-solid <?= $stepIdx > 3 ? 'fa-check' : 'fa-fire-burner' ?>"></i>
            </div>
            <div class="timeline-title">Sedang Dimasak Restoran</div>
            <div class="timeline-desc"><?= Security::escape($order['restaurant_name']) ?> sedang menyiapkan makanan Anda dengan higienis.</div>
        </div>

        <!-- Step 4: Siap Diantar -->
        <div class="timeline-step <?= $stepIdx > 4 ? 'completed' : ($stepIdx === 4 ? 'active' : '') ?>" data-step="4">
            <div class="timeline-dot">
                <i class="fa-solid <?= $stepIdx > 4 ? 'fa-check' : 'fa-box' ?>"></i>
            </div>
            <div class="timeline-title">Makanan Siap Diantar</div>
            <div class="timeline-desc">Restoran mengonfirmasi pesanan telah siap diambil oleh driver.</div>
        </div>

        <!-- Step 5: Driver Ditugaskan & Pickup -->
        <div class="timeline-step <?= $stepIdx > 6 ? 'completed' : ($stepIdx >= 5 ? 'active' : '') ?>" data-step="5">
            <div class="timeline-dot">
                <i class="fa-solid <?= $stepIdx > 6 ? 'fa-check' : 'fa-motorcycle' ?>"></i>
            </div>
            <div class="timeline-title">Driver Menjemput Pesanan</div>
            <div class="timeline-desc">Driver telah menerima tugas pengantaran dan bergerak ke restoran.</div>
        </div>

        <!-- Step 6: Dalam Pengantaran -->
        <div class="timeline-step <?= $stepIdx > 7 ? 'completed' : ($stepIdx === 7 ? 'active' : '') ?>" data-step="7">
            <div class="timeline-dot">
                <i class="fa-solid <?= $stepIdx > 7 ? 'fa-check' : 'fa-location-arrow' ?>"></i>
            </div>
            <div class="timeline-title">Sedang Diantar ke Tujuan</div>
            <div class="timeline-desc">Driver dalam perjalanan menuju alamat rumah Anda. Mohon siapkan pembayaran tunai (jika COD).</div>
        </div>

        <!-- Step 7: Selesai -->
        <div class="timeline-step <?= $stepIdx === 8 ? 'completed' : '' ?>" data-step="8">
            <div class="timeline-dot">
                <i class="fa-solid fa-flag-checkered"></i>
            </div>
            <div class="timeline-title">Pesanan Selesai</div>
            <div class="timeline-desc">Makanan telah diterima dengan baik. Selamat menikmati hidangan Anda!</div>
        </div>
    </div>
</div>

<!-- Rincian Item Pesanan -->
<div class="p-3 bg-white mb-2 border-bottom">
    <h3 class="fs-6 fw-bold mb-3">Detail Menu yang Dipesan</h3>

    <?php foreach ($items as $it): ?>
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <div>
                <div class="fw-bold small text-dark"><?= Security::escape($it['menu_name_snapshot']) ?> <span class="text-danger">x<?= $it['quantity'] ?></span></div>
                <?php if (!empty($it['notes'])): ?>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">Catatan: <?= Security::escape($it['notes']) ?></small>
                <?php endif; ?>
            </div>
            <div class="fw-semibold small text-dark"><?= Format::rupiah($it['subtotal']) ?></div>
        </div>
    <?php endforeach; ?>

    <div class="pt-2 text-muted small">
        <div class="d-flex justify-content-between mb-1">
            <span>Subtotal</span>
            <span><?= Format::rupiah($order['subtotal']) ?></span>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span>Ongkos Kirim (<?= $order['distance_km'] ?> km)</span>
            <span><?= Format::rupiah($order['delivery_fee']) ?></span>
        </div>
        <hr class="my-2">
        <div class="d-flex justify-content-between fw-bold fs-6 text-dark">
            <span>Grand Total</span>
            <span class="text-danger"><?= Format::rupiah($order['grand_total']) ?></span>
        </div>
        <div class="mt-2 text-end">
            <span class="badge <?= $order['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                Pembayaran: <?= strtoupper($order['payment_method']) ?> (<?= $order['payment_status'] === 'paid' ? 'Lunas' : 'Belum Lunas' ?>)
            </span>
        </div>
    </div>
</div>

<!-- Alamat Pengantaran -->
<div class="p-3 bg-white mb-4 border-bottom">
    <h3 class="fs-6 fw-bold mb-2">Tujuan Pengiriman</h3>
    <div class="small text-secondary mb-1">
        <b><?= Security::escape($order['customer_name']) ?></b> (<?= Security::escape($order['customer_phone']) ?>)
    </div>
    <div class="small text-muted mb-2">
        <i class="fa-solid fa-location-dot text-danger me-1"></i> <?= Security::escape($order['delivery_address']) ?>
    </div>
    <?php if (!empty($order['customer_note'])): ?>
        <div class="alert alert-light border small p-2 mb-0">
            <b>Catatan Khusus:</b> <?= Security::escape($order['customer_note']) ?>
        </div>
    <?php endif; ?>
</div>

<!-- Tombol Bantuan / Refresh -->
<div class="p-3 pb-5">
    <div class="d-grid gap-2">
        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20EFOOD,%20saya%20ingin%20menanyakan%20status%20pesanan%20nomor%20<?= Security::escape($order['order_number']) ?>" target="_blank" class="btn btn-whatsapp py-2">
            <i class="fa-brands fa-whatsapp fs-5"></i> Butuh Bantuan? Hubungi Admin EFOOD
        </a>
        <button type="button" class="btn btn-light border py-2" onclick="location.reload()">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Segarkan Status
        </button>
    </div>
</div>

<?php
View::partial('frontend.bottom-nav', ['activeNav' => 'orders']);
View::partial('frontend.footer');
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Mulai AJAX polling setiap 6 detik
    startOrderTracking('<?= Security::escape($order['order_number']) ?>', '<?= Security::escape($order['status']) ?>');
});
</script>
