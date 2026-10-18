<?php
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'menus']);
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => 'Edit Menu']); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold mb-0 text-dark">Edit Data Menu <?= Security::escape($menu['name']) ?></h4>
            <a href="<?= View::url('admin/menu') ?>" class="btn btn-sm btn-light border">Kembali</a>
        </div>

        <div class="card border-0 shadow-sm p-4 rounded-3" style="max-width: 800px;">
            <form action="<?= View::url('admin/menu/edit/' . $menu['id']) ?>" method="POST">
                <?= Security::csrfField() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Pilih Restoran Mitra</label>
                        <select name="restaurant_id" class="form-select" required>
                            <?php foreach ($restaurants as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= $menu['restaurant_id'] == $r['id'] ? 'selected' : '' ?>>
                                    <?= Security::escape($r['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Kategori Menu</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Tanpa Kategori --</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $menu['category_id'] == $c['id'] ? 'selected' : '' ?>>
                                    <?= Security::escape($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Nama Menu</label>
                        <input type="text" name="name" class="form-control" value="<?= Security::escape($menu['name']) ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Harga Satuan (Rp)</label>
                        <input type="number" name="price" class="form-control" value="<?= (int) $menu['price'] ?>" required step="500">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"><?= Security::escape($menu['description']) ?></textarea>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Foto Menu (URL)</label>
                        <input type="url" name="image" class="form-control" value="<?= Security::escape($menu['image']) ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Stok Porsi</label>
                        <input type="number" name="stock" class="form-control" value="<?= $menu['stock'] ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="available" <?= $menu['status'] === 'available' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="sold_out" <?= $menu['status'] === 'sold_out' ? 'selected' : '' ?>>Habis</option>
                            <option value="inactive" <?= $menu['status'] === 'inactive' ? 'selected' : '' ?>>Non-Aktif</option>
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
