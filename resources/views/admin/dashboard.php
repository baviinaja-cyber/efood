<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'dashboard']);
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => 'Dashboard Utama Admin']); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <!-- Stat Cards Grid -->
        <div class="row g-3 mb-4">
            <!-- 1. Menunggu Verifikasi -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Menunggu Verifikasi</div>
                        <div class="stat-val text-warning"><?= $stats['pending_verification'] ?></div>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Diproses Restoran -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Diproses Restoran</div>
                        <div class="stat-val text-primary"><?= $stats['restaurant_processing'] ?></div>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-fire-burner"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Siap Diantar -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Siap Diantar</div>
                        <div class="stat-val text-purple"><?= $stats['ready_for_delivery'] ?></div>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                </div>
            </div>

            <!-- 4. Dalam Pengantaran -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Dalam Pengantaran</div>
                        <div class="stat-val text-danger"><?= $stats['on_delivery'] ?></div>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="fa-solid fa-motorcycle"></i>
                    </div>
                </div>
            </div>

            <!-- 5. Pesanan Selesai -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Pesanan Selesai</div>
                        <div class="stat-val text-success"><?= $stats['delivered'] ?></div>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>

            <!-- 6. Restoran Aktif -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Mitra Restoran Aktif</div>
                        <div class="stat-val text-dark"><?= $stats['total_restaurants'] ?></div>
                    </div>
                    <div class="stat-icon bg-secondary-subtle text-secondary">
                        <i class="fa-solid fa-store"></i>
                    </div>
                </div>
            </div>

            <!-- 7. Pelanggan Terdaftar -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Pelanggan</div>
                        <div class="stat-val text-dark"><?= $stats['total_customers'] ?></div>
                    </div>
                    <div class="stat-icon bg-secondary-subtle text-secondary">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- 8. Total Omset Selesai -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Transaksi Sukses</div>
                        <div class="stat-val text-success fs-5"><?= Format::rupiah($stats['total_revenue']) ?></div>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-money-bill-trend-up"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Pesanan Terbaru -->
        <div class="admin-table-card">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Pesanan Terbaru Masuk</h5>
                    <small class="text-muted">Pantau dan teruskan pesanan pelanggan secara real-time</small>
                </div>
                <a href="<?= View::url('admin/pesanan') ?>" class="btn btn-sm btn-outline-danger fw-bold">
                    Lihat Semua Pesanan <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Restoran</th>
                            <th>Total</th>
                            <th>Status Alur</th>
                            <th>Waktu Masuk</th>
                            <th class="text-end">Aksi Alur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada pesanan terbaru.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $ord): ?>
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
                                    <td class="fw-bold text-danger"><?= Format::rupiah($ord['grand_total']) ?></td>
                                    <td>
                                        <span class="badge <?= Format::statusBadgeClass($ord['status']) ?>">
                                            <?= Format::statusLabel($ord['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= Format::timeAgo($ord['created_at']) ?></td>
                                    <td class="text-end">
                                        <a href="<?= View::url('admin/pesanan/' . $ord['order_number']) ?>" class="btn btn-sm btn-outline-danger fw-bold">
                                            Buka Detail <i class="fa-solid fa-arrow-right ms-1"></i>
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
