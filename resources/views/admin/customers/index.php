<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'customers']);
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => 'Daftar Pelanggan']); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Data Pelanggan EFOOD</h4>
                <small class="text-muted">Daftar pengguna terdaftar dan histori akumulasi transaksi</small>
            </div>
            <span class="badge bg-danger fs-6 px-3 py-2"><?= count($customers) ?> Pelanggan</span>
        </div>

        <div class="admin-table-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Email</th>
                            <th>No. WhatsApp</th>
                            <th>Total Pesanan</th>
                            <th>Total Belanja</th>
                            <th>Terdaftar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light text-danger fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                            <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                        </div>
                                        <div class="fw-bold text-dark"><?= Security::escape($c['name']) ?></div>
                                    </div>
                                </td>
                                <td><?= Security::escape($c['email']) ?></td>
                                <td>
                                    <a href="https://wa.me/<?= Security::escape($c['phone']) ?>" target="_blank" class="text-success fw-bold text-decoration-none">
                                        <i class="fa-brands fa-whatsapp me-1"></i> <?= Security::escape($c['phone']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= $c['total_orders'] ?> pesanan</span>
                                </td>
                                <td class="fw-bold text-danger"><?= Format::rupiah($c['total_spent']) ?></td>
                                <td class="small text-muted"><?= Format::datetime($c['created_at']) ?></td>
                                <td>
                                    <span class="badge <?= $c['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= strtoupper($c['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php View::partial('admin.footer'); ?>
