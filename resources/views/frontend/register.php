<?php
use App\Helpers\Security;
use App\Helpers\View;

View::partial('frontend.head', ['pageTitle' => $pageTitle]);
?>

<div class="d-flex align-items-center justify-content-between p-3 bg-white border-bottom">
    <a href="<?= View::url('login') ?>" class="btn btn-sm btn-light border rounded-circle p-2" style="width: 36px; height: 36px;">
        <i class="fa-solid fa-arrow-left text-dark"></i>
    </a>
    <h1 class="fs-6 fw-bold mb-0">Daftar Akun EFOOD</h1>
    <div style="width: 36px;"></div>
</div>

<?php View::partial('frontend.flash'); ?>

<div class="p-4">
    <div class="text-center mb-3">
        <h2 class="fs-5 fw-bold text-dark">Buat Akun Pelanggan</h2>
        <p class="text-muted small">Dapatkan kemudahan pesan antar makanan dan promo spesial.</p>
    </div>

    <form action="<?= View::url('register') ?>" method="POST">
        <?= Security::csrfField() ?>

        <div class="mb-3">
            <label class="form-label small fw-bold">Nama Lengkap</label>
            <input type="text" name="name" class="form-control" required placeholder="Contoh: Ahmad Rizki">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Alamat Email</label>
            <input type="email" name="email" class="form-control" required placeholder="nama@email.com">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Nomor Telepon / WhatsApp</label>
            <input type="tel" name="phone" class="form-control" required placeholder="081234567890">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Kata Sandi</label>
            <input type="password" name="password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Ulangi Kata Sandi</label>
            <input type="password" name="password_confirmation" class="form-control" required minlength="6" placeholder="Ketik ulang kata sandi">
        </div>

        <button type="submit" class="btn btn-efood w-100 py-2 fs-6 mb-3">
            <i class="fa-solid fa-user-plus me-2"></i> Daftar Sekarang
        </button>
    </form>

    <div class="text-center mt-2 small text-muted">
        Sudah memiliki akun? <a href="<?= View::url('login') ?>" class="text-danger fw-bold text-decoration-none">Masuk di sini</a>
    </div>
</div>

<?php
View::partial('frontend.bottom-nav', ['activeNav' => 'account']);
View::partial('frontend.footer');
?>
