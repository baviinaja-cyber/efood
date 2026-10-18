<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'orders']);
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => 'Kelola Pesanan Masuk']); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <!-- Filter & Search Toolbar -->
        <div class="admin-table-card mb-4 p-3">
            <form action="<?= View::url('admin/pesanan') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari No. Pesanan, Pelanggan, No. HP..." value="<?= Security::escape($search ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Status Pesanan --</option>
                        <option value="pending_verification" <?= ($currentStatus === 'pending_verification') ? 'selected' : '' ?>>Menunggu Verifikasi Admin</option>
                        <option value="verified" <?= ($currentStatus === 'verified') ? 'selected' : '' ?>>Terverifikasi Admin</option>
                        <option value="restaurant_processing" <?= ($currentStatus === 'restaurant_processing') ? 'selected' : '' ?>>Diproses Restoran</option>
                        <option value="ready_for_delivery" <?= ($currentStatus === 'ready_for_delivery') ? 'selected' : '' ?>>Siap Diantar</option>
                        <option value="assigned_to_driver" <?= ($currentStatus === 'assigned_to_driver') ? 'selected' : '' ?>>Driver Ditugaskan</option>
                        <option value="on_delivery" <?= ($currentStatus === 'on_delivery') ? 'selected' : '' ?>>Dalam Pengantaran</option>
                        <option value="delivered" <?= ($currentStatus === 'delivered') ? 'selected' : '' ?>>Selesai</option>
                        <option value="rejected" <?= ($currentStatus === 'rejected') ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-4 text-md-end">
                    <button type="submit" class="btn btn-sm btn-danger fw-bold"><i class="fa-solid fa-filter me-1"></i> Terapkan Filter</button>
                    <a href="<?= View::url('admin/pesanan') ?>" class="btn btn-sm btn-light border">Reset</a>
                </div>
            </form>
        </div>

        <!-- Tabel Pesanan -->
        <div class="admin-table-card">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0 text-dark">Daftar Transaksi Pesanan (<?= count($orders) ?>)</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Restoran</th>
                            <th>Driver</th>
                            <th>Total</th>
                            <th>Status Alur</th>
                            <th>Waktu Masuk</th>
                            <th class="text-end">Aksi Alur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-receipt fa-2x mb-2 text-secondary"></i>
                                    <p>Tidak ada pesanan yang sesuai dengan filter.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <td>
                                        <a href="<?= View::url('admin/pesanan/' . $ord['order_number']) ?>" class="fw-bold text-dark text-decoration-none">
                                            #<?= Security::escape($ord['order_number']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= Security::escape($ord['customer_name']) ?></div>
                                        <small class="text-muted"><?= Security::escape($ord['customer_phone']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= Security::escape($ord['restaurant_name']) ?></span>
                                    </td>
                                    <td>
                                        <?= !empty($ord['driver_name']) ? '<span class="badge bg-info-subtle text-info"><i class="fa-solid fa-motorcycle me-1"></i>' . Security::escape($ord['driver_name']) . '</span>' : '<span class="text-muted small">-</span>' ?>
                                    </td>
                                    <td class="fw-bold text-danger"><?= Format::rupiah($ord['grand_total']) ?></td>
                                    <td>
                                        <span class="badge <?= Format::statusBadgeClass($ord['status']) ?>">
                                            <?= Format::statusLabel($ord['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= Format::datetime($ord['created_at']) ?></td>
                                    <td class="text-end">
                                        <a href="<?= View::url('admin/pesanan/' . $ord['order_number']) ?>" class="btn btn-sm btn-outline-danger fw-bold">
                                            Kelola Alur <i class="fa-solid fa-arrow-right ms-1"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php View::partial('admin.footer'); ?>
