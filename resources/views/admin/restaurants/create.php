<?php
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'restaurants']);
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => 'Tambah Restoran Mitra']); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold mb-0 text-dark">Form Pendaftaran Mitra Restoran</h4>
            <a href="<?= View::url('admin/restoran') ?>" class="btn btn-sm btn-light border">Kembali</a>
        </div>

        <div class="card border-0 shadow-sm p-4 rounded-3" style="max-width: 800px;">
            <form action="<?= View::url('admin/restoran/tambah') ?>" method="POST">
                <?= Security::csrfField() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nama Restoran</label>
                        <input type="text" name="name" class="form-control" required placeholder="Contoh: Bebek Goreng H. Slamet">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nomor WhatsApp Restoran</label>
                        <input type="tel" name="whatsapp" class="form-control" required placeholder="081234567890">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Deskripsi Restoran</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Sajikan menu khas bebek empuk gurih sambal korek..."></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Alamat Lengkap</label>
                        <textarea name="address" class="form-control" rows="2" required placeholder="Jalan, kelurahan, kota..."></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Latitude GPS Restoran</label>
                        <input type="text" name="latitude" class="form-control" value="-6.20000000" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Longitude GPS Restoran</label>
                        <input type="text" name="longitude" class="form-control" value="106.81666600" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Jam Buka</label>
                        <input type="time" name="opening_time" class="form-control" value="08:00">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Jam Tutup</label>
                        <input type="time" name="closing_time" class="form-control" value="22:00">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Min. Pemesanan (Rp)</label>
                        <input type="number" name="minimum_order" class="form-control" value="10000" step="1000">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Estimasi Masak (Mnt)</label>
                        <input type="number" name="preparation_time" class="form-control" value="20">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Foto Sampul (URL Gambar)</label>
                        <input type="url" name="cover_image" class="form-control" value="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&auto=format&fit=crop&q=80">
                    </div>

                    <div class="col-12 pt-3">
                        <button type="submit" class="btn btn-danger fw-bold px-4 py-2">
                            <i class="fa-solid fa-save me-1"></i> Simpan Restoran Baru
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <?php View::partial('admin.footer'); ?>
