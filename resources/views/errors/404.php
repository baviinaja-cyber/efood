<?php
use App\Helpers\Security;
use App\Helpers\View;

View::partial('frontend.head', ['pageTitle' => $pageTitle ?? '404 - Halaman Tidak Ditemukan']);
?>

<div class="text-center py-5 px-3">
    <div class="bg-light d-inline-flex p-4 rounded-circle mb-3">
        <i class="fa-solid fa-triangle-exclamation fa-3x text-warning"></i>
    </div>
    <h1 class="fs-4 fw-bold mb-2">Halaman Tidak Ditemukan (404)</h1>
    <p class="text-muted small mb-4">
        <?= Security::escape($message ?? 'Maaf, tautan atau halaman kuliner yang Anda cari tidak tersedia.') ?>
    </p>
    <a href="<?= View::url() ?>" class="btn btn-efood px-4 py-2">
        <i class="fa-solid fa-house me-1"></i> Kembali ke Beranda
    </a>
</div>

<?php
View::partial('frontend.bottom-nav', ['activeNav' => 'home']);
View::partial('frontend.footer');
?>
