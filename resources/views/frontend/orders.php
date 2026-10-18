<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\View;

View::partial('frontend.head', ['pageTitle' => $pageTitle]);
?>

<div class="d-flex align-items-center justify-content-between p-3 bg-white border-bottom sticky-top">
    <a href="<?= View::url() ?>" class="btn btn-sm btn-light border rounded-circle p-2" style="width: 36px; height: 36px;">
        <i class="fa-solid fa-arrow-left text-dark"></i>
    </a>
    <h1 class="fs-6 fw-bold mb-0">Riwayat Pesanan Saya</h1>
    <div style="width: 36px;"></div>
</div>

<?php View::partial('frontend.flash'); ?>

<div class="p-3">
    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <div class="bg-light d-inline-flex p-4 rounded-circle mb-3">
                <i class="fa-solid fa-receipt fa-3x text-secondary"></i>
            </div>
            <h2 class="fs-5 fw-bold mb-1">Belum Ada Riwayat Pesanan</h2>
            <p class="text-muted small mb-4">Anda belum pernah melakukan pemesanan di EFOOD.</p>
            <a href="<?= View::url() ?>" class="btn btn-efood px-4 py-2">Mulai Pesan Sekarang</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $ord): ?>
            <div class="card border rounded-3 mb-3 shadow-sm overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold fs-6 text-dark"><?= Security::escape($ord['restaurant_name']) ?></div>
                            <small class="text-muted" style="font-size: 0.72rem;"><?= Format::datetime($ord['created_at']) ?></small>
                        </div>
                        <span class="badge <?= Format::statusBadgeClass($ord['status']) ?>">
                            <?= Format::statusLabel($ord['status']) ?>
                        </span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between py-2 border-top border-bottom my-2">
                        <span class="small text-muted"><?= $ord['item_count'] ?> menu dipesan</span>
                        <span class="fw-bold text-danger"><?= Format::rupiah($ord['grand_total']) ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="small text-muted">#<?= Security::escape($ord['order_number']) ?></span>
                        <a href="<?= View::url('pesanan/' . $ord['order_number']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">
                            Lacak Pesanan <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
View::partial('frontend.bottom-nav', ['activeNav' => 'orders']);
View::partial('frontend.footer');
?>
