<?php
use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\View;

View::partial('frontend.head', ['pageTitle' => $pageTitle]);
View::partial('frontend.header', ['isHome' => true]);
View::partial('frontend.flash');
?>

<div class="px-3 pt-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="fs-5 fw-bold mb-0">Daftar Restoran Mitra</h1>
        <span class="badge bg-light text-dark border"><?= count($restaurants) ?> Restoran</span>
    </div>

    <?php if (empty($restaurants)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-magnifying-glass fa-2x mb-2 text-secondary"></i>
            <p>Tidak ada restoran yang cocok dengan pencarian "<?= Security::escape($search) ?>"</p>
            <a href="<?= View::url('restoran') ?>" class="btn btn-sm btn-outline-danger">Tampilkan Semua</a>
        </div>
    <?php else: ?>
        <?php foreach ($restaurants as $res): ?>
            <?php
            $currentTime = date('H:i:s');
            $isOpen = ($currentTime >= $res['opening_time'] && $currentTime <= $res['closing_time']);
            ?>
            <a href="<?= View::url('restoran/' . $res['slug']) ?>" class="restaurant-card">
                <div class="restaurant-img-wrap">
                    <img src="<?= Security::escape($res['cover_image']) ?>" alt="<?= Security::escape($res['name']) ?>" class="restaurant-img" loading="lazy">
                    <span class="restaurant-status-badge <?= $isOpen ? 'status-badge-open' : 'status-badge-closed' ?>">
                        <?= $isOpen ? 'BUKA' : 'TUTUP' ?>
                    </span>
                    <span class="restaurant-time-badge">
                        <i class="fa-regular fa-clock me-1"></i> ±<?= $res['preparation_time'] ?> mnt
                    </span>
                </div>
                <div class="restaurant-content">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="restaurant-name"><?= Security::escape($res['name']) ?></h3>
                        <div class="meta-rating">
                            <i class="fa-solid fa-star"></i> <?= number_format((float) $res['rating'], 1) ?>
                        </div>
                    </div>
                    <p class="text-muted text-truncate mb-2" style="font-size: 0.8rem;">
                        <?= Security::escape($res['description']) ?>
                    </p>
                    <div class="restaurant-meta">
                        <span><i class="fa-solid fa-location-dot text-danger me-1"></i> <?= Security::escape($res['address']) ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
View::partial('frontend.bottom-nav', ['activeNav' => 'restaurants']);
View::partial('frontend.footer');
?>
