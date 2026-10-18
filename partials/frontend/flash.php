<?php
/**
 * Flash Notifications Partial - EFOOD
 */
$flashes = \App\Helpers\Session::getFlashes();
?>
<?php if (!empty($flashes)): ?>
    <div class="px-3 pt-3">
        <?php foreach ($flashes as $flash): ?>
            <div class="alert alert-<?= \App\Helpers\Security::escape($flash['type']) ?> alert-dismissible fade show shadow-sm border-0 mb-2" role="alert">
                <div class="d-flex align-items-center">
                    <?php if ($flash['type'] === 'success'): ?>
                        <i class="fa-solid fa-circle-check fs-5 me-2 text-success"></i>
                    <?php elseif ($flash['type'] === 'danger'): ?>
                        <i class="fa-solid fa-circle-xmark fs-5 me-2 text-danger"></i>
                    <?php elseif ($flash['type'] === 'warning'): ?>
                        <i class="fa-solid fa-triangle-exclamation fs-5 me-2 text-warning"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-circle-info fs-5 me-2 text-info"></i>
                    <?php endif; ?>
                    <div><?= \App\Helpers\Security::escape($flash['message']) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Toast Notification Container untuk AJAX JS -->
<div id="toast-container" class="efood-toast-container"></div>
