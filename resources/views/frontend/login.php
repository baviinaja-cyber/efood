<?php
use App\Helpers\Security;
use App\Helpers\View;

View::partial('frontend.head', ['pageTitle' => $pageTitle]);
?>

<div class="d-flex align-items-center justify-content-between p-3 bg-white border-bottom">
    <a href="<?= View::url() ?>" class="btn btn-sm btn-light border rounded-circle p-2" style="width: 36px; height: 36px;">
        <i class="fa-solid fa-arrow-left text-dark"></i>
    </a>
    <h1 class="fs-6 fw-bold mb-0">Masuk ke Akun EFOOD</h1>
    <div style="width: 36px;"></div>
</div>

<?php View::partial('frontend.flash'); ?>

<div class="p-4">
    <div class="text-center mb-4">
        <div class="d-inline-flex p-3 rounded-circle bg-danger-subtle text-danger mb-2">
            <i class="fa-solid fa-bowl-food fa-2x"></i>
        </div>
        <h2 class="fs-5 fw-bold text-dark">Selamat Datang Kembali</h2>
        <p class="text-muted small">Masuk untuk melanjutkan pesanan kuliner favorit Anda.</p>
    </div>

    <!-- Info Demo Akun Instan -->
    <div class="alert alert-info small mb-3">
        <b><i class="fa-solid fa-circle-info me-1"></i> Akun Pengujian Cepat:</b><br>
        • Pelanggan: <code>budi@gmail.com</code> / <code>budi123</code><br>
        • Admin: <code>admin@efood.com</code> / <code>admin123</code>
    </div>

    <form action="<?= View::url('login') ?>" method="POST">
        <?= Security::csrfField() ?>

        <div class="mb-3">
            <label class="form-label small fw-bold">Alamat Email</label>
            <input type="email" name="email" class="form-control py-2" required placeholder="nama@email.com" value="budi@gmail.com">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Kata Sandi</label>
            <input type="password" name="password" class="form-control py-2" required placeholder="••••••••" value="budi123">
        </div>

        <button type="submit" class="btn btn-efood w-100 py-2 fs-6 mb-3">
            <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk Sekarang
        </button>
    </form>

    <div class="text-center mt-3 small text-muted">
        Belum memiliki akun EFOOD? <a href="<?= View::url('register') ?>" class="text-danger fw-bold text-decoration-none">Daftar Akun Baru</a>
    </div>

    <div class="text-center mt-4 pt-3 border-top small">
        <a href="<?= View::url('admin/login') ?>" class="text-secondary text-decoration-none">
            <i class="fa-solid fa-lock me-1"></i> Masuk sebagai Administrator
        </a>
    </div>
</div>

<?php
View::partial('frontend.bottom-nav', ['activeNav' => 'account']);
View::partial('frontend.footer');
?>
