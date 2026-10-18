<?php
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'restaurants']);
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => 'Edit Mitra Restoran']); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold mb-0 text-dark">Edit Data <?= Security::escape($restaurant['name']) ?></h4>
            <a href="<?= View::url('admin/restoran') ?>" class="btn btn-sm btn-light border">Kembali</a>
        </div>

        <div class="card border-0 shadow-sm p-4 rounded-3" style="max-width: 800px;">
            <form action="<?= View::url('admin/restoran/edit/' . $restaurant['id']) ?>" method="POST">
                <?= Security::csrfField() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nama Restoran</label>
                        <input type="text" name="name" class="form-control" value="<?= Security::escape($restaurant['name']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nomor WhatsApp Restoran</label>
                        <input type="tel" name="whatsapp" class="form-control" value="<?= Security::escape($restaurant['whatsapp']) ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Deskripsi Restoran</label>
                        <textarea name="description" class="form-control" rows="2"><?= Security::escape($restaurant['description']) ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Alamat Lengkap</label>
                        <textarea name="address" class="form-control" rows="2" required><?= Security::escape($restaurant['address']) ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Latitude GPS Restoran</label>
                        <input type="text" name="latitude" class="form-control" value="<?= $restaurant['latitude'] ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Longitude GPS Restoran</label>
                        <input type="text" name="longitude" class="form-control" value="<?= $restaurant['longitude'] ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Jam Buka</label>
                        <input type="time" name="opening_time" class="form-control" value="<?= Security::escape($restaurant['opening_time']) ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Jam Tutup</label>
                        <input type="time" name="closing_time" class="form-control" value="<?= Security::escape($restaurant['closing_time']) ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Min. Order (Rp)</label>
                        <input type="number" name="minimum_order" class="form-control" value="<?= (int) $restaurant['minimum_order'] ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Estimasi Masak</label>
                        <input type="number" name="preparation_time" class="form-control" value="<?= (int) $restaurant['preparation_time'] ?>">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Foto Sampul (URL)</label>
                        <input type="url" name="cover_image" class="form-control" value="<?= Security::escape($restaurant['cover_image']) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Status Restoran</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= $restaurant['status'] === 'active' ? 'selected' : '' ?>>Aktif (Bisa Dipesan)</option>
                            <option value="inactive" <?= $restaurant['status'] === 'inactive' ? 'selected' : '' ?>>Non-Aktif (Tutup Sementara)</option>
                        </select>
                    </div>

                    <div class="col-12 pt-3">
                        <button type="submit" class="btn btn-primary fw-bold px-4 py-2">
                            <i class="fa-solid fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <?php View::partial('admin.footer'); ?>
