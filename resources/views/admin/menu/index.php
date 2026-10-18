<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'menus']);
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => 'Kelola Menu Makanan']); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Daftar Menu Seluruh Restoran</h4>
                <small class="text-muted">Kelola hidangan makanan, harga, stok, dan ketersediaan</small>
            </div>
            <a href="<?= View::url('admin/menu/tambah') ?>" class="btn btn-danger fw-bold">
                <i class="fa-solid fa-plus me-1"></i> Tambah Menu Baru
            </a>
        </div>

        <div class="admin-table-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Restoran</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $m): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= Security::escape($m['image']) ?>" class="rounded-2 object-fit-cover" style="width: 42px; height: 42px;" alt="">
                                        <div>
                                            <div class="fw-bold text-dark"><?= Security::escape($m['name']) ?></div>
                                            <small class="text-muted text-truncate d-inline-block" style="max-width: 220px;"><?= Security::escape($m['description']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= Security::escape($m['restaurant_name']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= Security::escape($m['category_name'] ?? 'Lainnya') ?></span>
                                </td>
                                <td class="fw-bold text-danger"><?= Format::rupiah($m['price']) ?></td>
                                <td>
                                    <span class="badge <?= $m['stock'] < 10 ? 'bg-warning text-dark' : 'bg-light text-dark border' ?>"><?= $m['stock'] ?> porsi</span>
                                </td>
                                <td>
                                    <span class="badge <?= $m['status'] === 'available' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $m['status'] === 'available' ? 'TERSEDIA' : 'HABIS' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= View::url('admin/menu/edit/' . $m['id']) ?>" class="btn btn-sm btn-outline-primary fw-bold">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="<?= View::url('admin/menu/delete/' . $m['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus menu <?= Security::escape($m['name']) ?>?')">
                                        <?= Security::csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php View::partial('admin.footer'); ?>
