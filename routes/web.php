<?php

namespace App\Routes;

use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\HomeController;
use App\Controllers\OrderController;
use App\Controllers\ProfileController;
use App\Controllers\RestaurantController;
use App\Controllers\Admin\CustomerAdminController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\DriverAdminController;
use App\Controllers\Admin\MenuAdminController;
use App\Controllers\Admin\OrderAdminController;
use App\Controllers\Admin\RestaurantAdminController;
use App\Controllers\Admin\SettingAdminController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

class Router
{
    private static array $routes = [];

    public static function get(string $path, array $handler, array $middlewares = []): void
    {
        self::$routes['GET'][] = ['path' => $path, 'handler' => $handler, 'middlewares' => $middlewares];
    }

    public static function post(string $path, array $handler, array $middlewares = []): void
    {
        self::$routes['POST'][] = ['path' => $path, 'handler' => $handler, 'middlewares' => $middlewares];
    }

    public static function dispatch(string $method, string $uri): void
    {
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        $uri = strtok($uri, '?');
        $uri = trim($uri, '/');
        
        $routesForMethod = self::$routes[$method] ?? [];

        foreach ($routesForMethod as $route) {
            $pattern = trim($route['path'], '/');
            // Ganti {param} menjadi regex capture group
            $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $pattern);
            $regex = "#^" . $regex . "$#";

            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches); // hilangkan full match

                // Jalankan middleware
                foreach ($route['middlewares'] as $middleware) {
                    $middleware::handle();
                }

                [$controllerClass, $actionMethod] = $route['handler'];
                $controller = new $controllerClass();
                call_user_func_array([$controller, $actionMethod], $matches);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        \App\Helpers\View::render('errors.404', [
            'pageTitle' => '404 - Halaman Tidak Ditemukan'
        ]);
    }
}

// -----------------------------------------------------------------------------
// DEFINISI RUTE APLIKASI EFOOD
// -----------------------------------------------------------------------------

// Download ZIP Source Code
Router::get('download-zip', [HomeController::class, 'index']); // Ditangani khusus di index.php jika diminta

// Frontend Pelanggan
Router::get('', [HomeController::class, 'index']);
Router::get('restoran', [RestaurantController::class, 'index']);
Router::get('restoran/{slug}', [RestaurantController::class, 'show']);

// Keranjang
Router::get('keranjang', [CartController::class, 'index']);
Router::post('cart/add', [CartController::class, 'add'], [CsrfMiddleware::class]);
Router::post('cart/update', [CartController::class, 'update'], [CsrfMiddleware::class]);
Router::post('cart/remove', [CartController::class, 'remove'], [CsrfMiddleware::class]);
Router::post('cart/clear', [CartController::class, 'clear'], [CsrfMiddleware::class]);

// Checkout & Pesanan
Router::get('checkout', [OrderController::class, 'checkout']);
Router::get('checkout/calculate-fee', [OrderController::class, 'calculateFeeAjax']);
Router::post('checkout/place-order', [OrderController::class, 'placeOrder'], [CsrfMiddleware::class]);
Router::get('pesanan', [OrderController::class, 'myOrders']);
Router::get('pesanan/{order_number}', [OrderController::class, 'track']);
Router::get('api/order-status/{order_number}', [OrderController::class, 'statusJson']);

// Akun & Profil Pelanggan
Router::get('akun', [ProfileController::class, 'index']);
Router::post('akun/address/add', [ProfileController::class, 'addAddress'], [CsrfMiddleware::class]);

// Autentikasi Pelanggan
Router::get('login', [AuthController::class, 'showLogin']);
Router::post('login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
Router::get('register', [AuthController::class, 'showRegister']);
Router::post('register', [AuthController::class, 'register'], [CsrfMiddleware::class]);
Router::get('logout', [AuthController::class, 'logout']);

// Panel Admin - Autentikasi
Router::get('admin/login', [AuthController::class, 'showAdminLogin']);
Router::post('admin/login', [AuthController::class, 'adminLogin'], [CsrfMiddleware::class]);

// Panel Admin - Rute Terlindungi (AdminMiddleware)
Router::get('admin/dashboard', [DashboardController::class, 'index'], [AdminMiddleware::class]);
Router::get('admin', [DashboardController::class, 'index'], [AdminMiddleware::class]);

// Admin - Kelola Pesanan & Workflow WhatsApp
Router::get('admin/pesanan', [OrderAdminController::class, 'index'], [AdminMiddleware::class]);
Router::get('admin/pesanan/{order_number}', [OrderAdminController::class, 'show'], [AdminMiddleware::class]);
Router::post('admin/pesanan/verify', [OrderAdminController::class, 'verify'], [AdminMiddleware::class, CsrfMiddleware::class]);
Router::post('admin/pesanan/forward-restaurant', [OrderAdminController::class, 'forwardToRestaurant'], [AdminMiddleware::class, CsrfMiddleware::class]);
Router::post('admin/pesanan/confirm-restaurant', [OrderAdminController::class, 'confirmRestaurant'], [AdminMiddleware::class, CsrfMiddleware::class]);
Router::post('admin/pesanan/assign-driver', [OrderAdminController::class, 'assignDriver'], [AdminMiddleware::class, CsrfMiddleware::class]);
Router::post('admin/pesanan/update-delivery', [OrderAdminController::class, 'updateDeliveryStatus'], [AdminMiddleware::class, CsrfMiddleware::class]);
Router::post('admin/pesanan/reject', [OrderAdminController::class, 'reject'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin - Kelola Restoran
Router::get('admin/restoran', [RestaurantAdminController::class, 'index'], [AdminMiddleware::class]);
Router::get('admin/restoran/tambah', [RestaurantAdminController::class, 'create'], [AdminMiddleware::class]);
Router::post('admin/restoran/tambah', [RestaurantAdminController::class, 'store'], [AdminMiddleware::class, CsrfMiddleware::class]);
Router::get('admin/restoran/edit/{id}', [RestaurantAdminController::class, 'edit'], [AdminMiddleware::class]);
Router::post('admin/restoran/edit/{id}', [RestaurantAdminController::class, 'update'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin - Kelola Menu
Router::get('admin/menu', [MenuAdminController::class, 'index'], [AdminMiddleware::class]);
Router::get('admin/menu/tambah', [MenuAdminController::class, 'create'], [AdminMiddleware::class]);
Router::post('admin/menu/tambah', [MenuAdminController::class, 'store'], [AdminMiddleware::class, CsrfMiddleware::class]);
Router::get('admin/menu/edit/{id}', [MenuAdminController::class, 'edit'], [AdminMiddleware::class]);
Router::post('admin/menu/edit/{id}', [MenuAdminController::class, 'update'], [AdminMiddleware::class, CsrfMiddleware::class]);
Router::post('admin/menu/delete/{id}', [MenuAdminController::class, 'delete'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin - Kelola Driver
Router::get('admin/driver', [DriverAdminController::class, 'index'], [AdminMiddleware::class]);
Router::post('admin/driver', [DriverAdminController::class, 'store'], [AdminMiddleware::class, CsrfMiddleware::class]);
Router::post('admin/driver/toggle/{id}', [DriverAdminController::class, 'toggle'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin - Pelanggan & Pengaturan
Router::get('admin/pelanggan', [CustomerAdminController::class, 'index'], [AdminMiddleware::class]);
Router::get('admin/pengaturan', [SettingAdminController::class, 'index'], [AdminMiddleware::class]);
Router::post('admin/pengaturan', [SettingAdminController::class, 'update'], [AdminMiddleware::class, CsrfMiddleware::class]);
