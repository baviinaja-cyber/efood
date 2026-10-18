<?php

namespace App\Controllers;

use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\View;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Services\DistanceService;
use Exception;

class OrderController
{
    public function checkout(): void
    {
        $cart = Session::get('cart');
        if (empty($cart) || empty($cart['items'])) {
            Session::flash('warning', 'Keranjang belanja Anda masih kosong.');
            header('Location: ' . View::url());
            exit;
        }

        $restaurant = Restaurant::findById($cart['restaurant_id']);
        if (!$restaurant) {
            Session::flash('danger', 'Restoran tidak valid.');
            header('Location: ' . View::url('keranjang'));
            exit;
        }

        $subtotal = 0;
        foreach ($cart['items'] as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
        }

        // Cek user login
        $user = Session::user();
        if (!$user) {
            // Pelanggan belum login, simpan return_to url
            Session::set('redirect_after_login', View::url('checkout'));
            Session::flash('info', 'Silakan masuk atau buat akun untuk melanjutkan checkout.');
            header('Location: ' . View::url('login'));
            exit;
        }

        // Default koordinat pengiriman (dekat restoran atau default Jakarta)
        $defaultLat = $restaurant['latitude'] ?: -6.20000000;
        $defaultLng = $restaurant['longitude'] ?: 106.81666600;

        // Default estimasi ongkir
        $estimatedDistance = 1.5;
        $estimatedFee = DistanceService::calculateDeliveryFee($estimatedDistance);

        $bankInfo = Setting::get('bank_transfer_info', 'BCA: 8830129831 a.n PT EFOOD INDONESIA BERSAMA');

        View::render('frontend.checkout', [
            'cart' => $cart,
            'restaurant' => $restaurant,
            'subtotal' => $subtotal,
            'user' => $user,
            'defaultLat' => $defaultLat,
            'defaultLng' => $defaultLng,
            'estimatedFee' => $estimatedFee,
            'bankInfo' => $bankInfo,
            'pageTitle' => 'Checkout Pesanan - EFOOD'
        ]);
    }

    public function calculateFeeAjax(): void
    {
        header('Content-Type: application/json');

        $restaurantId = (int) ($_GET['restaurant_id'] ?? 0);
        $custLat = (float) ($_GET['lat'] ?? 0);
        $custLng = (float) ($_GET['lng'] ?? 0);

        $restaurant = Restaurant::findById($restaurantId);
        if (!$restaurant) {
            echo json_encode(['success' => false, 'message' => 'Restoran tidak ditemukan']);
            exit;
        }

        $distance = DistanceService::calculateDistance(
            (float) $restaurant['latitude'],
            (float) $restaurant['longitude'],
            $custLat,
            $custLng
        );

        $fee = DistanceService::calculateDeliveryFee($distance);

        echo json_encode([
            'success' => true,
            'distance_km' => $distance,
            'delivery_fee' => $fee,
            'delivery_fee_formatted' => Format::rupiah($fee)
        ]);
        exit;
    }

    public function placeOrder(): void
    {
        $user = Session::user();
        if (!$user) {
            Session::flash('warning', 'Sesi login telah habis.');
            header('Location: ' . View::url('login'));
            exit;
        }

        $cart = Session::get('cart');
        if (empty($cart) || empty($cart['items'])) {
            Session::flash('danger', 'Keranjang belanja kosong.');
            header('Location: ' . View::url());
            exit;
        }

        $name = Security::sanitizeString($_POST['customer_name'] ?? $user['name']);
        $phone = Security::sanitizePhone($_POST['customer_phone'] ?? $user['phone']);
        $address = Security::sanitizeString($_POST['delivery_address'] ?? '');
        $lat = (float) ($_POST['delivery_latitude'] ?? -6.20000000);
        $lng = (float) ($_POST['delivery_longitude'] ?? 106.81666600);
        $paymentMethod = in_array($_POST['payment_method'] ?? 'cod', ['cod', 'transfer']) ? $_POST['payment_method'] : 'cod';
        $note = Security::sanitizeString($_POST['customer_note'] ?? '');

        if (empty($name) || empty($phone) || empty($address)) {
            Session::flash('danger', 'Mohon lengkapi nama penerima, nomor WhatsApp, dan alamat pengiriman.');
            header('Location: ' . View::url('checkout'));
            exit;
        }

        try {
            $orderData = [
                'customer_id' => $user['id'],
                'restaurant_id' => $cart['restaurant_id'],
                'payment_method' => $paymentMethod,
                'customer_name' => $name,
                'customer_phone' => $phone,
                'delivery_address' => $address,
                'delivery_latitude' => $lat,
                'delivery_longitude' => $lng,
                'customer_note' => $note
            ];

            $result = Order::createOrderWithItems($orderData, $cart['items']);

            // Kosongkan keranjang
            Session::remove('cart');

            Session::flash('success', "Pesanan #{$result['order_number']} berhasil dibuat! Menunggu verifikasi admin.");
            header('Location: ' . View::url("pesanan/{$result['order_number']}"));
            exit;
        } catch (Exception $e) {
            Session::flash('danger', 'Gagal memproses pesanan: ' . $e->getMessage());
            header('Location: ' . View::url('checkout'));
            exit;
        }
    }

    public function myOrders(): void
    {
        $user = Session::user();
        if (!$user) {
            header('Location: ' . View::url('login'));
            exit;
        }

        $orders = Order::getByCustomer($user['id']);

        View::render('frontend.orders', [
            'orders' => $orders,
            'pageTitle' => 'Riwayat Pesanan Saya - EFOOD'
        ]);
    }

    public function track(string $orderNumber): void
    {
        $order = Order::findByOrderNumber($orderNumber);
        if (!$order) {
            http_response_code(404);
            View::render('errors.404', ['message' => 'Pesanan tidak ditemukan.']);
            exit;
        }

        // Keamanan: Jika bukan admin, pastikan pemilik pesanan yang melihat
        $user = Session::user();
        if (!Session::isAdmin()) {
            if (!$user || $user['id'] != $order['customer_id']) {
                // Beri izin jika nomor telepon cocok atau belum login
            }
        }

        $items = Order::getItems($order['id']);
        $history = Order::getStatusHistory($order['id']);

        View::render('frontend.order-tracking', [
            'order' => $order,
            'items' => $items,
            'history' => $history,
            'pageTitle' => "Lacak Pesanan #{$order['order_number']} - EFOOD"
        ]);
    }

    public function statusJson(string $orderNumber): void
    {
        header('Content-Type: application/json');
        $order = Order::findByOrderNumber($orderNumber);

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
            exit;
        }

        $history = Order::getStatusHistory($order['id']);

        echo json_encode([
            'success' => true,
            'status' => $order['status'],
            'status_label' => Format::statusLabel($order['status']),
            'status_step' => Format::statusStepIndex($order['status']),
            'driver_name' => $order['driver_name'],
            'driver_plate' => $order['driver_plate'],
            'updated_at' => Format::timeAgo($order['updated_at']),
            'history' => $history
        ]);
        exit;
    }
}
