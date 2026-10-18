<?php
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'drivers']);
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => 'Kelola Armada Driver']); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Armada Driver Pengantaran</h4>
                <small class="text-muted">Kelola kurir pengantar, nomor WhatsApp dispatch, dan armada kendaraan</small>
            </div>
            <button type="button" class="btn btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#addDriverModal">
                <i class="fa-solid fa-plus me-1"></i> Daftarkan Driver Baru
            </button>
        </div>

        <div class="admin-table-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th>Kontak WhatsApp</th>
                            <th>Kendaraan & Plat</th>
                            <th>Total Antar</th>
                            <th>Ketersediaan</th>
                            <th>Status Akun</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($drivers as $d): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-danger-subtle text-danger fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="fa-solid fa-motorcycle"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= Security::escape($d['name']) ?></div>
                                            <small class="text-muted"><?= Security::escape($d['phone']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="https://wa.me/<?= Security::escape($d['whatsapp']) ?>" target="_blank" class="text-success fw-bold text-decoration-none">
                                        <i class="fa-brands fa-whatsapp me-1"></i> <?= Security::escape($d['whatsapp']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold small"><?= Security::escape($d['vehicle_type']) ?></div>
                                    <span class="badge bg-light text-dark border"><?= Security::escape($d['vehicle_plate']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info fw-bold"><?= $d['total_deliveries'] ?? 0 ?> order</span>
                                </td>
                                <td>
                                    <span class="badge <?= $d['is_available'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $d['is_available'] ? 'SIAP ANTAR' : 'SIBUK' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $d['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= strtoupper($d['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="<?= View::url('admin/driver/toggle/' . $d['id']) ?>" method="POST" class="d-inline">
                                        <?= Security::csrfField() ?>
                                        <button type="submit" class="btn btn-sm <?= $d['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?> fw-bold">
                                            <?= $d['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?>
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

    <!-- Modal Tambah Driver -->
    <div class="modal fade" id="addDriverModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= View::url('admin/driver') ?>" method="POST">
                    <?= Security::csrfField() ?>
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold">Pendaftaran Driver Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Lengkap Driver</label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Rahmat Hidayat">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nomor Telepon / WhatsApp</label>
                            <input type="tel" name="phone" class="form-control" required placeholder="0852xxxxxxxx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Jenis Kendaraan</label>
                            <input type="text" name="vehicle_type" class="form-control" value="Sepeda Motor Honda Beat">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nomor Plat Kendaraan</label>
                            <input type="text" name="vehicle_plate" class="form-control" required placeholder="Contoh: B 1234 XYZ">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger fw-bold">Simpan Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
