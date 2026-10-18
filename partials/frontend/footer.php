<?php
/**
 * Frontend Footer Partial - EFOOD
 */
?>
    <footer class="mt-4 pt-4 pb-5 px-3 border-top text-center text-muted" style="font-size: 0.78rem; background-color: #FAFAFA;">
        <div class="mb-2">
            <span class="fw-bold text-dark"><i class="fa-solid fa-bowl-food text-danger me-1"></i> EFOOD Indonesia</span> &copy; <?= date('Y') ?>
        </div>
        <p class="mb-2 text-secondary">
            Super App Pemesanan Makanan Multi-Restoran Cepat, Lezat, & Terpercaya.
        </p>
        <div class="d-flex justify-content-center gap-3 mb-3">
            <a href="<?= \App\Helpers\View::url('admin/login') ?>" class="text-decoration-none text-secondary">
                <i class="fa-solid fa-lock me-1"></i> Panel Admin
            </a>
            <span>•</span>
            <a href="<?= \App\Helpers\View::url('download-zip') ?>" class="text-decoration-none text-primary fw-semibold">
                <i class="fa-solid fa-download me-1"></i> Unduh Source Code (ZIP)
            </a>
        </div>
    </footer>

</div> <!-- End .efood-container -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= \App\Helpers\View::url('assets/js/app.js') ?>"></script>
<script src="<?= \App\Helpers\View::url('assets/js/geolocation.js') ?>"></script>
<script src="<?= \App\Helpers\View::url('assets/js/order-tracker.js') ?>"></script>
</body>
</html>
