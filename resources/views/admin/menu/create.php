<?php
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'menus']);
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => 'Tambah Menu Baru']); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold mb-0 text-dark">Form Tambah Menu Makanan / Minuman</h4>
            <a href="<?= View::url('admin/menu') ?>" class="btn btn-sm btn-light border">Kembali</a>
        </div>

        <div class="card border-0 shadow-sm p-4 rounded-3" style="max-width: 800px;">
            <form action="<?= View::url('admin/menu/tambah') ?>" method="POST">
                <?= Security::csrfField() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Pilih Restoran Mitra</label>
                        <select name="restaurant_id" class="form-select" required>
                            <option value="">-- Pilih Restoran --</option>
                            <?php foreach ($restaurants as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= Security::escape($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Kategori Menu</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Tanpa Kategori --</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= Security::escape($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Nama Menu Makanan / Minuman</label>
                        <input type="text" name="name" class="form-control" required placeholder="Contoh: Paket Ayam Penyet Sambal Ijo">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Harga Satuan (Rp)</label>
                        <input type="number" name="price" class="form-control" required placeholder="25000" step="500">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Deskripsi Bahan & Porsi</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Ayam ungkep bumbu kuning digoreng garing dengan lalapan..."></textarea>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Foto Menu (URL Gambar)</label>
                        <input type="url" name="image" class="form-control" value="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&auto=format&fit=crop&q=80">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Stok Porsi</label>
                        <input type="number" name="stock" class="form-control" value="50">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="available">Tersedia</option>
                            <option value="sold_out">Habis</option>
                        </select>
                    </div>

                    <div class="col-12 pt-3">
                        <button type="submit" class="btn btn-danger fw-bold px-4 py-2">
                            <i class="fa-solid fa-save me-1"></i> Simpan Menu
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <?php View::partial('admin.footer'); ?>
