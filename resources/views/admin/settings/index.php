<?php
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'settings']);
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => 'Pengaturan & Tarif']); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Pengaturan Sistem & Biaya Ongkos Kirim</h4>
                <small class="text-muted">Konfigurasi tarif dasar pengiriman, nomor WhatsApp resmi, dan metode pembayaran</small>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4 rounded-3" style="max-width: 800px;">
            <form action="<?= View::url('admin/pengaturan') ?>" method="POST">
                <?= Security::csrfField() ?>

                <h5 class="fw-bold fs-6 text-dark border-bottom pb-2 mb-3">Identitas Brand & Kontak</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nama Aplikasi</label>
                        <input type="text" name="app_name" class="form-control" value="<?= Security::escape($settings['app_name'] ?? 'EFOOD') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nomor WhatsApp Resmi Admin</label>
                        <input type="tel" name="admin_whatsapp" class="form-control" value="<?= Security::escape($settings['admin_whatsapp'] ?? '6281234567890') ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Tagline Aplikasi</label>
                        <input type="text" name="app_tagline" class="form-control" value="<?= Security::escape($settings['app_tagline'] ?? 'Super App Pesan Makanan Cepat & Nikmat') ?>">
                    </div>
                </div>

                <h5 class="fw-bold fs-6 text-dark border-bottom pb-2 mb-3">Tarif & Kalkulasi Ongkos Kirim (Haversine Formula)</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Tarif Dasar Pengiriman (1 km pertama)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="base_delivery_fee" class="form-control" value="<?= (int) ($settings['base_delivery_fee'] ?? 9000) ?>" step="500" required>
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">Biaya tetap minimum untuk pengantaran jarak dekat.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Tarif per Kilometer Berikutnya</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="per_km_fee" class="form-control" value="<?= (int) ($settings['per_km_fee'] ?? 2500) ?>" step="500" required>
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">Dikalikan dengan jarak GPS di atas 1 km.</small>
                    </div>
                </div>

                <h5 class="fw-bold fs-6 text-dark border-bottom pb-2 mb-3">Metode Pembayaran</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="enable_cod" id="sw_cod" value="1" <?= ($settings['enable_cod'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold small" for="sw_cod">Aktifkan Pembayaran Tunai saat Pengantaran (COD)</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="enable_transfer" id="sw_tf" value="1" <?= ($settings['enable_transfer'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold small" for="sw_tf">Aktifkan Pembayaran Transfer Bank Manual</label>
                        </div>

                        <label class="form-label small fw-bold">Informasi Rekening Bank Transfer</label>
                        <textarea name="bank_transfer_info" class="form-control" rows="2"><?= Security::escape($settings['bank_transfer_info'] ?? 'BCA: 8830129831 a.n PT EFOOD INDONESIA BERSAMA') ?></textarea>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-danger fw-bold px-4 py-2">
                        <i class="fa-solid fa-save me-1"></i> Simpan Seluruh Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </main>

    <?php View::partial('admin.footer'); ?>
