<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\View;

View::partial('admin.head', ['pageTitle' => $pageTitle]);
View::partial('admin.sidebar', ['activeMenu' => 'orders']);

$status = $order['status'];
$resMaps = "https://www.google.com/maps/dir/?api=1&destination=" . $order['restaurant_latitude'] . "," . $order['restaurant_longitude'];
$custMaps = "https://www.google.com/maps/dir/?api=1&destination=" . $order['delivery_latitude'] . "," . $order['delivery_longitude'];
?>

<div class="admin-main">
    <?php View::partial('admin.topbar', ['pageTitle' => "Kelola Alur Pesanan #{$order['order_number']}"]); ?>

    <main class="admin-content">
        <?php View::partial('admin.flash'); ?>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="<?= View::url('admin/pesanan') ?>" class="btn btn-sm btn-light border mb-2">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Pesanan
                </a>
                <h3 class="fw-bold mb-0 text-dark">Pesanan #<?= Security::escape($order['order_number']) ?></h3>
                <small class="text-muted">Dibuat pada <?= Format::datetime($order['created_at']) ?></small>
            </div>
            <div class="text-end">
                <span class="badge fs-6 px-3 py-2 <?= Format::statusBadgeClass($order['status']) ?>">
                    <?= Format::statusLabel($order['status']) ?>
                </span>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- PUSAT KONTROL ALUR WORKFLOW ORDER (TAHAP A - E) -->
        <!-- ============================================================= -->
        <div class="workflow-box mb-4">
            <div class="workflow-header">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-timeline text-danger me-2"></i> Pusat Kendali Workflow Pesanan (Tahap Aktif)
                    </h5>
                    <small class="text-muted">Ikuti langkah-langkah di bawah ini untuk memproses pesanan sampai selesai</small>
                </div>
                <span class="badge bg-danger text-white">Status: <?= Security::escape($order['status']) ?></span>
            </div>

            <!-- TAHAP A: PESANAN BARU MENUNGGU VERIFIKASI -->
            <?php if ($status === 'pending_verification'): ?>
                <div class="alert alert-warning border-0 p-3 mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-clock-rotate-left fs-4 text-warning me-2"></i>
                        <h6 class="fw-bold mb-0 text-dark">Tahap A: Pesanan Baru Masuk</h6>
                    </div>
                    <p class="small text-secondary mb-3">
                        Pelanggan baru saja menyelesaikan checkout. Harap periksa ketersediaan restoran mitra dan data pengiriman sebelum diverifikasi.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <!-- Tombol Verifikasi Pesanan -->
                        <form action="<?= View::url('admin/pesanan/verify') ?>" method="POST">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="order_number" value="<?= Security::escape($order['order_number']) ?>">
                            <button type="submit" class="btn btn-success fw-bold px-3 py-2">
                                <i class="fa-solid fa-check-circle me-1"></i> Verifikasi Pesanan Ini
                            </button>
                        </form>

                        <!-- Tombol Tolak Pesanan -->
                        <button type="button" class="btn btn-outline-danger fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fa-solid fa-ban me-1"></i> Tolak Pesanan
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAHAP B: ADMIN TERUSKAN KE RESTORAN VIA WHATSAPP -->
            <?php if ($status === 'verified'): ?>
                <div class="alert alert-info border-0 p-3 mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-share-nodes fs-4 text-info me-2"></i>
                        <h6 class="fw-bold mb-0 text-dark">Tahap B: Teruskan Pesanan ke Restoran</h6>
                    </div>
                    <p class="small text-secondary mb-3">
                        Pesanan telah diverifikasi. Langkah berikutnya: Buka WhatsApp Restoran mitra untuk meneruskan rincian pesanan, lalu klik tombol pencatatan di bawah.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <!-- Tombol Buka WhatsApp Restoran -->
                        <a href="<?= $restaurantWaLink ?>" target="_blank" class="btn btn-whatsapp fw-bold px-3 py-2">
                            <i class="fa-brands fa-whatsapp fs-5"></i> 1. Teruskan ke Restoran via WhatsApp (<?= Security::escape($order['restaurant_whatsapp']) ?>)
                        </a>

                        <!-- Tombol Catat Pesanan Diteruskan -->
                        <form action="<?= View::url('admin/pesanan/forward-restaurant') ?>" method="POST">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="order_number" value="<?= Security::escape($order['order_number']) ?>">
                            <button type="submit" class="btn btn-primary fw-bold px-3 py-2">
                                <i class="fa-solid fa-clipboard-check me-1"></i> 2. Catat Pesanan Diteruskan
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAHAP C: RESTORAN MENGONFIRMASI / PROSES MASAK -->
            <?php if ($status === 'restaurant_processing'): ?>
                <div class="alert alert-primary border-0 p-3 mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-fire-burner fs-4 text-primary me-2"></i>
                        <h6 class="fw-bold mb-0 text-dark">Tahap C: Restoran Sedang Memproses / Memasak</h6>
                    </div>
                    <p class="small text-secondary mb-3">
                        Restoran sedang menyiapkan makanan. Jika restoran membalas chat WhatsApp bahwa makanan sudah siap diantar, tekan tombol di bawah untuk lanjut ke tahap penugasan kurir.
                    </p>

                    <div class="d-flex flex-wrap gap-2">
                        <!-- Konfirmasi Siap Diantar -->
                        <form action="<?= View::url('admin/pesanan/confirm-restaurant') ?>" method="POST" class="d-inline">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="order_number" value="<?= Security::escape($order['order_number']) ?>">
                            <input type="hidden" name="restaurant_action" value="ready">
                            <button type="submit" class="btn btn-purple text-white fw-bold px-3 py-2" style="background-color: #6F42C1;">
                                <i class="fa-solid fa-box-open me-1"></i> Konfirmasi Makanan Siap Diantar
                            </button>
                        </form>

                        <!-- Catat Balasan Diterima -->
                        <button type="button" class="btn btn-outline-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#noteRestoModal">
                            <i class="fa-solid fa-comment-dots me-1"></i> Catat Balasan WhatsApp Restoran
                        </button>

                        <!-- Restoran Menolak -->
                        <form action="<?= View::url('admin/pesanan/confirm-restaurant') ?>" method="POST" class="d-inline" onsubmit="return confirm('Tandai pesanan ditolak oleh restoran?')">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="order_number" value="<?= Security::escape($order['order_number']) ?>">
                            <input type="hidden" name="restaurant_action" value="rejected">
                            <input type="hidden" name="notes" value="Restoran menolak (Bahan habis / Tutup mendadak)">
                            <button type="submit" class="btn btn-outline-danger fw-bold px-3 py-2">
                                <i class="fa-solid fa-circle-xmark me-1"></i> Restoran Menolak
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAHAP D: PESANAN SIAP DIANTAR - PENUGASAN DRIVER -->
            <?php if ($status === 'ready_for_delivery'): ?>
                <div class="alert alert-secondary border-0 p-3 mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-motorcycle fs-4 text-purple me-2"></i>
                        <h6 class="fw-bold mb-0 text-dark">Tahap D: Makanan Siap Diantar & Penugasan Driver</h6>
                    </div>
                    <p class="small text-secondary mb-3">
                        Pilih driver aktif, kirimkan tugas pengantaran via WhatsApp (lengkap dengan navigasi titik jemput dan titik antar), lalu catat penugasan driver ke sistem.
                    </p>

                    <form action="<?= View::url('admin/pesanan/assign-driver') ?>" method="POST" id="driverAssignForm">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="order_number" value="<?= Security::escape($order['order_number']) ?>">

                        <div class="row g-2 align-items-center mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Pilih Driver EFOOD Aktif:</label>
                                <select name="driver_id" id="driver_id_select" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Driver Tersedia --</option>
                                    <?php foreach ($drivers as $drv): ?>
                                        <option value="<?= $drv['id'] ?>" data-phone="<?= Security::escape($drv['whatsapp']) ?>" data-name="<?= Security::escape($drv['name']) ?>">
                                            <?= Security::escape($drv['name']) ?> (<?= Security::escape($drv['vehicle_plate']) ?> - <?= Security::escape($drv['vehicle_type']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 pt-md-4">
                                <button type="submit" class="btn btn-success fw-bold px-3 py-2">
                                    <i class="fa-solid fa-user-check me-1"></i> Catat Tugas Ditugaskan ke Driver
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- TAHAP E: PENGANTARAN (ASSIGNED, PICKED UP, ON DELIVERY) -->
            <?php if (in_array($status, ['assigned_to_driver', 'picked_up', 'on_delivery'])): ?>
                <div class="alert alert-warning border-0 p-3 mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-route fs-4 text-warning me-2"></i>
                        <h6 class="fw-bold mb-0 text-dark">Tahap E: Progres Pengantaran oleh Driver</h6>
                    </div>
                    <p class="small text-secondary mb-3">
                        Driver <b><?= Security::escape($order['driver_name']) ?></b> (<?= Security::escape($order['driver_plate']) ?>) sedang menjalankan tugas. Perbarui tahapan sesuai konfirmasi WhatsApp driver:
                    </p>

                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($status === 'assigned_to_driver'): ?>
                            <!-- Step: Driver Picked Up Makanan -->
                            <form action="<?= View::url('admin/pesanan/update-delivery') ?>" method="POST">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <input type="hidden" name="order_number" value="<?= Security::escape($order['order_number']) ?>">
                                <input type="hidden" name="delivery_status" value="picked_up">
                                <button type="submit" class="btn btn-info text-white fw-bold px-3 py-2">
                                    <i class="fa-solid fa-box-archive me-1"></i> Konfirmasi Driver Ambil Makanan (Picked Up)
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (in_array($status, ['assigned_to_driver', 'picked_up'])): ?>
                            <!-- Step: On Delivery -->
                            <form action="<?= View::url('admin/pesanan/update-delivery') ?>" method="POST">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <input type="hidden" name="order_number" value="<?= Security::escape($order['order_number']) ?>">
                                <input type="hidden" name="delivery_status" value="on_delivery">
                                <button type="submit" class="btn btn-warning text-dark fw-bold px-3 py-2">
                                    <i class="fa-solid fa-person-biking me-1"></i> Sedang Diantar ke Pelanggan (On Delivery)
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Step: Selesai / Terkirim -->
                        <form action="<?= View::url('admin/pesanan/update-delivery') ?>" method="POST" onsubmit="return confirm('Konfirmasi pesanan telah berhasil sampai di tangan pelanggan?')">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="order_number" value="<?= Security::escape($order['order_number']) ?>">
                            <input type="hidden" name="delivery_status" value="delivered">
                            <button type="submit" class="btn btn-success fw-bold px-3 py-2">
                                <i class="fa-solid fa-circle-check me-1"></i> Pesanan Selesai / Terkirim (Delivered)
                            </button>
                        </form>

                        <!-- Step: Pengantaran Gagal -->
                        <form action="<?= View::url('admin/pesanan/update-delivery') ?>" method="POST" onsubmit="return confirm('Tandai pengantaran gagal?')">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="order_number" value="<?= Security::escape($order['order_number']) ?>">
                            <input type="hidden" name="delivery_status" value="delivery_failed">
                            <button type="submit" class="btn btn-outline-danger fw-bold px-3 py-2">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Pengantaran Gagal
                            </button>
                        </form>

                        <?php if (!empty($driverWaLink)): ?>
                            <a href="<?= $driverWaLink ?>" target="_blank" class="btn btn-sm btn-outline-success fw-bold px-3 py-2">
                                <i class="fa-brands fa-whatsapp me-1"></i> Buka Chat WA Driver (<?= Security::escape($order['driver_whatsapp']) ?>)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAHAP FINAL -->
            <?php if ($status === 'delivered'): ?>
                <div class="alert alert-success border-0 p-3 mb-0">
                    <h6 class="fw-bold mb-1 text-success"><i class="fa-solid fa-circle-check me-1"></i> Pesanan Selesai dengan Sukses</h6>
                    <p class="small text-secondary mb-0">Semua tahapan pemesanan telah selesai dan pembayaran telah dicatat lunas.</p>
                </div>
            <?php elseif (in_array($status, ['rejected', 'cancelled'])): ?>
                <div class="alert alert-danger border-0 p-3 mb-0">
                    <h6 class="fw-bold mb-1 text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Pesanan Telah Dibatalkan / Ditolak</h6>
                    <p class="small text-secondary mb-0">Pesanan ini tidak dilanjutkan lagi dalam sistem.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================================================= -->
        <!-- DETAIL DATA PESANAN, MAPS & ALAMAT -->
        <!-- ============================================================= -->
        <div class="row g-4 mb-4">
            <!-- Kolom Kiri: Rincian Menu & Pembayaran -->
            <div class="col-lg-7">
                <div class="admin-table-card mb-4">
                    <div class="p-3 border-bottom">
                        <h5 class="fw-bold mb-0 text-dark">Rincian Menu yang Dipesan</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $it): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= Security::escape($it['menu_name_snapshot']) ?></div>
                                            <?php if (!empty($it['notes'])): ?>
                                                <small class="text-muted d-block">Catatan: <?= Security::escape($it['notes']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= Format::rupiah($it['price_snapshot']) ?></td>
                                        <td class="fw-bold">x<?= $it['quantity'] ?></td>
                                        <td class="text-end fw-bold text-dark"><?= Format::rupiah($it['subtotal']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end text-muted">Subtotal Makanan:</td>
                                    <td class="text-end fw-bold"><?= Format::rupiah($order['subtotal']) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end text-muted">Ongkos Kirim (<?= $order['distance_km'] ?> km):</td>
                                    <td class="text-end fw-bold"><?= Format::rupiah($order['delivery_fee']) ?></td>
                                </tr>
                                <tr class="border-top border-2">
                                    <td colspan="3" class="text-end fw-bold fs-6">Grand Total:</td>
                                    <td class="text-end fw-bold fs-5 text-danger"><?= Format::rupiah($order['grand_total']) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Log Riwayat Status Alur (Audit Trail) -->
                <div class="admin-table-card">
                    <div class="p-3 border-bottom">
                        <h5 class="fw-bold mb-0 text-dark">Riwayat Perubahan Status (Audit Trail)</h5>
                    </div>
                    <div class="p-3">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($history as $h): ?>
                                <li class="list-group-item px-0 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge <?= Format::statusBadgeClass($h['new_status']) ?>">
                                            <?= Format::statusLabel($h['new_status']) ?>
                                        </span>
                                        <small class="text-muted"><?= Format::datetime($h['created_at']) ?></small>
                                    </div>
                                    <div class="small text-dark mt-1">
                                        <b>Oleh:</b> <?= Security::escape($h['changed_by']) ?>
                                        <?php if (!empty($h['note'])): ?>
                                            - <span class="text-secondary"><?= Security::escape($h['note']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Info Pelanggan, Restoran, Driver & Maps -->
            <div class="col-lg-5">
                <!-- Info Pelanggan -->
                <div class="admin-table-card mb-3 p-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">Informasi Pelanggan</h6>
                    <div class="small mb-1"><b>Nama:</b> <?= Security::escape($order['customer_name']) ?></div>
                    <div class="small mb-1">
                        <b>WhatsApp:</b> <a href="https://wa.me/<?= Security::escape($order['customer_phone']) ?>" target="_blank" class="text-success text-decoration-none fw-bold"><?= Security::escape($order['customer_phone']) ?> <i class="fa-brands fa-whatsapp ms-1"></i></a>
                    </div>
                    <div class="small mb-2"><b>Alamat Pengiriman:</b> <?= Security::escape($order['delivery_address']) ?></div>
                    <?php if (!empty($order['customer_note'])): ?>
                        <div class="small alert alert-warning p-2 mb-2">
                            <b>Catatan Khusus:</b> <?= Security::escape($order['customer_note']) ?>
                        </div>
                    <?php endif; ?>
                    <a href="<?= $custMaps ?>" target="_blank" class="btn btn-sm btn-outline-danger w-100 fw-bold">
                        <i class="fa-solid fa-map-location-dot me-1"></i> Buka Google Maps Titik Pelanggan
                    </a>
                </div>

                <!-- Info Restoran Mitra -->
                <div class="admin-table-card mb-3 p-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">Restoran Mitra</h6>
                    <div class="small mb-1"><b>Restoran:</b> <?= Security::escape($order['restaurant_name']) ?></div>
                    <div class="small mb-1">
                        <b>WhatsApp:</b> <a href="https://wa.me/<?= Security::escape($order['restaurant_whatsapp']) ?>" target="_blank" class="text-success text-decoration-none fw-bold"><?= Security::escape($order['restaurant_whatsapp']) ?> <i class="fa-brands fa-whatsapp ms-1"></i></a>
                    </div>
                    <div class="small mb-2"><b>Alamat:</b> <?= Security::escape($order['restaurant_address']) ?></div>
                    <a href="<?= $resMaps ?>" target="_blank" class="btn btn-sm btn-outline-dark w-100 fw-bold">
                        <i class="fa-solid fa-map-pin me-1"></i> Buka Google Maps Titik Restoran
                    </a>
                </div>

                <!-- Info Driver -->
                <?php if (!empty($order['driver_name'])): ?>
                    <div class="admin-table-card mb-3 p-3 bg-light">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">Driver Pengantar Ditugaskan</h6>
                        <div class="small mb-1"><b>Nama:</b> <?= Security::escape($order['driver_name']) ?></div>
                        <div class="small mb-1"><b>Plat Motor:</b> <?= Security::escape($order['driver_plate']) ?></div>
                        <div class="small mb-1">
                            <b>WhatsApp:</b> <a href="https://wa.me/<?= Security::escape($order['driver_whatsapp']) ?>" target="_blank" class="text-success text-decoration-none fw-bold"><?= Security::escape($order['driver_whatsapp']) ?> <i class="fa-brands fa-whatsapp ms-1"></i></a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Log Pengiriman Pesan WhatsApp -->
                <div class="admin-table-card p-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">Log Riwayat WhatsApp Manual</h6>
                    <?php if (empty($waLogs)): ?>
                        <small class="text-muted">Belum ada pengiriman pesan yang dicatat.</small>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0" style="font-size: 0.78rem;">
                            <?php foreach ($waLogs as $log): ?>
                                <li class="border-bottom py-2">
                                    <div class="fw-bold text-dark">
                                        Kepada: <span class="badge bg-secondary"><?= Security::escape($log['recipient_type']) ?></span> (<?= Security::escape($log['recipient_phone']) ?>)
                                    </div>
                                    <div class="text-muted"><?= Security::escape($log['notes']) ?></div>
                                    <div class="text-secondary" style="font-size: 0.7rem;"><?= Format::datetime($log['prepared_at']) ?> oleh <?= Security::escape($log['created_by']) ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php View::partial('admin.footer'); ?>

    <!-- Modal Catat Balasan Restoran -->
    <div class="modal fade" id="noteRestoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= View::url('admin/pesanan/confirm-restaurant') ?>" method="POST">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="order_number" value="<?= Security::escape($order['order_number']) ?>">
                    <input type="hidden" name="restaurant_action" value="accepted">
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold">Catat Konfirmasi WhatsApp Restoran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label small fw-bold">Catatan Konfirmasi dari Restoran:</label>
                        <textarea name="notes" class="form-control" rows="3" required placeholder="Contoh: Restoran membalas pesanan sudah diterima, perkiraan selesai masak 15 menit..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary fw-bold">Simpan Catatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tolak Pesanan -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= View::url('admin/pesanan/reject') ?>" method="POST">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="order_number" value="<?= Security::escape($order['order_number']) ?>">
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold text-danger">Tolak Pesanan Ini</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label small fw-bold">Alasan Penolakan:</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Contoh: Alamat pengiriman di luar jangkauan / Restoran tutup dadakan..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger fw-bold">Tolak Pesanan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
