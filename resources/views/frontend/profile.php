<?php
use App\Helpers\Security;
use App\Helpers\View;

View::partial('frontend.head', ['pageTitle' => $pageTitle]);
?>

<div class="d-flex align-items-center justify-content-between p-3 bg-white border-bottom sticky-top">
    <a href="<?= View::url() ?>" class="btn btn-sm btn-light border rounded-circle p-2" style="width: 36px; height: 36px;">
        <i class="fa-solid fa-arrow-left text-dark"></i>
    </a>
    <h1 class="fs-6 fw-bold mb-0">Profil & Akun Saya</h1>
    <div style="width: 36px;"></div>
</div>

<?php View::partial('frontend.flash'); ?>

<div class="p-3">
    <!-- User Card -->
    <div class="card border rounded-3 p-3 mb-3 bg-white shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-danger-subtle text-danger fw-bold d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; font-size: 1.4rem;">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div>
                <h2 class="fs-6 fw-bold mb-0 text-dark"><?= Security::escape($user['name']) ?></h2>
                <div class="text-muted small"><?= Security::escape($user['email']) ?></div>
                <div class="text-muted small"><i class="fa-brands fa-whatsapp text-success me-1"></i> <?= Security::escape($user['phone']) ?></div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Links -->
    <div class="list-group rounded-3 shadow-sm mb-3">
        <a href="<?= View::url('pesanan') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
            <div>
                <i class="fa-solid fa-receipt text-danger me-2"></i>
                <span class="fw-semibold small">Riwayat Pesanan Saya</span>
            </div>
            <i class="fa-solid fa-chevron-right text-muted small"></i>
        </a>
        <a href="<?= View::url('download-zip') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
            <div>
                <i class="fa-solid fa-cloud-arrow-down text-warning me-2"></i>
                <span class="fw-semibold small">Unduh Full Source Code (.ZIP)</span>
            </div>
            <span class="badge bg-warning text-dark">Siap Hosting</span>
        </a>
    </div>

    <!-- Alamat Tersimpan -->
    <div class="card border rounded-3 p-3 mb-3 bg-white shadow-sm">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="fs-6 fw-bold mb-0">Alamat Pengiriman</h3>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-bold" data-bs-toggle="collapse" data-bs-target="#addAddressForm">
                <i class="fa-solid fa-plus me-1"></i> Tambah Alamat
            </button>
        </div>

        <!-- Form Tambah Alamat Collapse -->
        <div class="collapse mb-3 p-3 bg-light rounded-3" id="addAddressForm">
            <form action="<?= View::url('akun/address/add') ?>" method="POST">
                <?= Security::csrfField() ?>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Label Alamat (Rumah, Kantor, dll)</label>
                    <input type="text" name="label" class="form-control form-control-sm" value="Rumah" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Alamat Lengkap</label>
                    <textarea name="address" class="form-control form-control-sm" rows="2" required placeholder="Jalan, nomor, RT/RW, kelurahan..."></textarea>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Latitude</label>
                        <input type="text" name="latitude" id="prof_lat" class="form-control form-control-sm" value="-6.24230000">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Longitude</label>
                        <input type="text" name="longitude" id="prof_lng" class="form-control form-control-sm" value="106.80410000">
                    </div>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDef">
                    <label class="form-check-label small" for="isDef">Jadikan Alamat Utama</label>
                </div>
                <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold">Simpan Alamat</button>
            </form>
        </div>

        <?php if (empty($addresses)): ?>
            <p class="text-muted small mb-0">Belum ada alamat tersimpan.</p>
        <?php else: ?>
            <?php foreach ($addresses as $addr): ?>
                <div class="border rounded-2 p-2 mb-2 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-secondary"><?= Security::escape($addr['label']) ?></span>
                        <?php if ($addr['is_default']): ?>
                            <span class="badge bg-success-subtle text-success">Utama</span>
                        <?php endif; ?>
                    </div>
                    <div class="small text-dark fw-semibold"><?= Security::escape($addr['recipient_name']) ?> (<?= Security::escape($addr['recipient_phone']) ?>)</div>
                    <div class="small text-muted"><?= Security::escape($addr['address']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Logout -->
    <a href="<?= View::url('logout') ?>" class="btn btn-outline-danger w-100 py-2 fw-bold">
        <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Keluar dari Akun
    </a>
</div>

<?php
View::partial('frontend.bottom-nav', ['activeNav' => 'account']);
View::partial('frontend.footer');
?>
