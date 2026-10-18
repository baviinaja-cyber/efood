<?php
use App\Helpers\Security;
use App\Helpers\View;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::escape($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= View::url('assets/css/admin.css') ?>" rel="stylesheet">
</head>
<body style="background-color: #0F172A; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Plus Jakarta Sans', sans-serif;">

<div class="card border-0 shadow-lg p-4" style="width: 100%; max-width: 420px; border-radius: 16px;">
    <div class="text-center mb-4">
        <div class="d-inline-flex p-3 rounded-circle bg-danger-subtle text-danger mb-2">
            <i class="fa-solid fa-shield-halved fa-2x"></i>
        </div>
        <h2 class="fs-4 fw-bold text-dark">EFOOD Panel Admin</h2>
        <p class="text-muted small">Pusat Manajemen Pesanan, Restoran & Driver</p>
    </div>

    <?php 
    $flashes = \App\Helpers\Session::getFlashes();
    if (!empty($flashes)):
        foreach ($flashes as $f): 
    ?>
        <div class="alert alert-<?= Security::escape($f['type']) ?> small p-2 mb-3">
            <?= Security::escape($f['message']) ?>
        </div>
    <?php 
        endforeach; 
    endif; 
    ?>

    <div class="alert alert-light border small mb-3">
        <b><i class="fa-solid fa-key me-1 text-warning"></i> Kredensial Login Admin:</b><br>
        Email: <code>admin@efood.com</code><br>
        Password: <code>admin123</code>
    </div>

    <form action="<?= View::url('admin/login') ?>" method="POST">
        <?= Security::csrfField() ?>

        <div class="mb-3">
            <label class="form-label small fw-bold">Email Administrator</label>
            <input type="email" name="email" class="form-control" required value="admin@efood.com">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Kata Sandi</label>
            <input type="password" name="password" class="form-control" required value="admin123">
        </div>

        <button type="submit" class="btn btn-efood w-100 py-2 fw-bold">
            <i class="fa-solid fa-lock-open me-2"></i> Masuk ke Panel Admin
        </button>
    </form>

    <div class="text-center mt-4 pt-3 border-top small">
        <a href="<?= View::url() ?>" class="text-decoration-none text-muted">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Aplikasi Pelanggan
        </a>
    </div>
</div>

</body>
</html>
