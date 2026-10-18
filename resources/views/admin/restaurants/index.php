<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'restaurants']);
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => 'Kelola Mitra Restoran']); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Daftar Restoran Mitra EFOOD</h4>
                <small class="text-muted">Kelola profil cabang, jam operasional, titik GPS, dan kontak WhatsApp</small>
            </div>
            <a href="<?= View::url('admin/restoran/tambah') ?>" class="btn btn-danger fw-bold">
                <i class="fa-solid fa-plus me-1"></i> Tambah Restoran Baru
            </a>
        </div>

        <div class="admin-table-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Restoran</th>
                            <th>Kontak WhatsApp</th>
                            <th>Jam Buka</th>
                            <th>Min. Order</th>
                            <th>Jumlah Menu</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($restaurants as $res): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= Security::escape($res['cover_image']) ?>" class="rounded-2 object-fit-cover" style="width: 44px; height: 44px;" alt="">
                                        <div>
                                            <div class="fw-bold text-dark"><?= Security::escape($res['name']) ?></div>
                                            <small class="text-muted"><?= Security::escape($res['address']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="https://wa.me/<?= Security::escape($res['whatsapp']) ?>" target="_blank" class="text-success fw-bold text-decoration-none">
                                        <i class="fa-brands fa-whatsapp me-1"></i> <?= Security::escape($res['whatsapp']) ?>
                                    </a>
                                </td>
                                <td class="small"><?= Security::escape($res['opening_time']) ?> - <?= Security::escape($res['closing_time']) ?></td>
                                <td class="small fw-semibold"><?= Format::rupiah($res['minimum_order']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= $res['total_menu_count'] ?> menu</span>
                                </td>
                                <td>
                                    <span class="badge <?= $res['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= strtoupper($res['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= View::url('admin/restoran/edit/' . $res['id']) ?>" class="btn btn-sm btn-outline-primary fw-bold">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                    </a>
                                    <a href="<?= View::url('restoran/' . $res['slug']) ?>" target="_blank" class="btn btn-sm btn-light border">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php View::partial('admin.footer'); ?>
