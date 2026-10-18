<?php

namespace App\Controllers\Admin;

use App\Helpers\Format;
use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\View;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\OrderWorkflowService;
use App\Services\WhatsAppService;
use Exception;

class OrderAdminController
{
    public function index(): void
    {
        $status = $_GET['status'] ?? null;
        $search = $_GET['q'] ?? null;

        $orders = Order::getAllAdmin($status, $search);

        View::render('admin.orders.index', [
            'orders' => $orders,
            'currentStatus' => $status,
            'search' => $search,
            'pageTitle' => 'Kelola Pesanan Masuk - Admin EFOOD'
        ]);
    }

    public function show(string $orderNumber): void
    {
        $order = Order::findByOrderNumber($orderNumber);
        if (!$order) {
            Session::flash('danger', 'Pesanan tidak ditemukan.');
            header('Location: ' . View::url('admin/pesanan'));
            exit;
        }

        $items = Order::getItems($order['id']);
        $history = Order::getStatusHistory($order['id']);
        $waLogs = Order::getWhatsAppLogs($order['id']);
        $drivers = Driver::allActive();
        $restaurant = Restaurant::findById($order['restaurant_id']);

        // Generate URL WhatsApp untuk Restoran
        $restaurantWaMessage = WhatsAppService::generateRestaurantMessage($order, $items, $restaurant);
        $restaurantWaLink = WhatsAppService::buildLink($restaurant['whatsapp'], $restaurantWaMessage);

        // Generate URL WhatsApp untuk Driver jika driver sudah dipilih
        $driverWaLink = '';
        $driverWaMessage = '';
        if (!empty($order['driver_id'])) {
            $currentDriver = Driver::findById($order['driver_id']);
            if ($currentDriver) {
                $driverWaMessage = WhatsAppService::generateDriverMessage($order, $items, $restaurant, $currentDriver);
                $driverWaLink = WhatsAppService::buildLink($currentDriver['whatsapp'], $driverWaMessage);
            }
        }

        View::render('admin.orders.detail', [
            'order' => $order,
            'items' => $items,
            'history' => $history,
            'waLogs' => $waLogs,
            'drivers' => $drivers,
            'restaurant' => $restaurant,
            'restaurantWaLink' => $restaurantWaLink,
            'restaurantWaMessage' => $restaurantWaMessage,
            'driverWaLink' => $driverWaLink,
            'driverWaMessage' => $driverWaMessage,
            'pageTitle' => "Detail Pesanan #{$order['order_number']} - Admin EFOOD"
        ]);
    }

    public function verify(): void
    {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $user = Session::user();
        $adminName = $user['name'] ?? 'Admin';

        try {
            OrderWorkflowService::updateStatus($orderId, 'verified', $adminName, 'Admin telah memverifikasi data dan ketersediaan pesanan.');
            Session::flash('success', 'Pesanan berhasil diverifikasi. Silakan teruskan ke Restoran melalui WhatsApp.');
        } catch (Exception $e) {
            Session::flash('danger', 'Gagal memverifikasi pesanan: ' . $e->getMessage());
        }

        $orderNumber = $_POST['order_number'] ?? '';
        header('Location: ' . View::url("admin/pesanan/{$orderNumber}"));
        exit;
    }

    public function forwardToRestaurant(): void
    {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $orderNumber = $_POST['order_number'] ?? '';
        $user = Session::user();
        $adminName = $user['name'] ?? 'Admin';

        $order = Order::findById($orderId);
        if (!$order) {
            Session::flash('danger', 'Pesanan tidak ditemukan.');
            header('Location: ' . View::url('admin/pesanan'));
            exit;
        }

        try {
            // Catat log WhatsApp manual
            WhatsAppService::logManualDispatch(
                $orderId,
                'restaurant',
                $order['restaurant_whatsapp'],
                'restaurant_order_dispatch',
                $adminName,
                'Pesan diteruskan ke WhatsApp Restoran secara manual oleh Admin.'
            );

            // Ubah status ke restaurant_processing
            OrderWorkflowService::updateStatus(
                $orderId,
                'restaurant_processing',
                $adminName,
                "Pesanan diteruskan ke WhatsApp Restoran ({$order['restaurant_whatsapp']}). Menunggu konfirmasi dan proses masak."
            );

            Session::flash('success', 'Penerusan pesanan ke WhatsApp Restoran berhasil dicatat. Status pesanan kini: Diproses Restoran.');
        } catch (Exception $e) {
            Session::flash('danger', 'Gagal mencatat penerusan pesanan: ' . $e->getMessage());
        }

        header('Location: ' . View::url("admin/pesanan/{$orderNumber}"));
        exit;
    }

    public function confirmRestaurant(): void
    {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $orderNumber = $_POST['order_number'] ?? '';
        $action = $_POST['restaurant_action'] ?? 'accepted'; // accepted, ready, rejected
        $notes = Security::sanitizeString($_POST['notes'] ?? '');
        $user = Session::user();
        $adminName = $user['name'] ?? 'Admin';

        try {
            if ($action === 'ready') {
                OrderWorkflowService::updateStatus(
                    $orderId,
                    'ready_for_delivery',
                    $adminName,
                    'Restoran mengonfirmasi makanan telah selesai dimasak dan SIAP DIANTAR: ' . $notes
                );
                Session::flash('success', 'Konfirmasi SIAP DIANTAR dicatat. Silakan pilih dan tugaskan driver pengantaran.');
            } elseif ($action === 'rejected') {
                OrderWorkflowService::updateStatus(
                    $orderId,
                    'rejected',
                    $adminName,
                    'Restoran MENOLAK pesanan karena: ' . ($notes ?: 'Bahan habis / Restoran sibuk.')
                );
                Session::flash('warning', 'Pesanan ditandai DITOLAK oleh restoran.');
            } else {
                // Catat di status history sebagai konfirmasi diterima
                $db = \App\Helpers\Database::getConnection();
                $hist = $db->prepare("
                    INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note, created_at)
                    VALUES (:order_id, 'restaurant_processing', 'restaurant_processing', :changed_by, :note, CURRENT_TIMESTAMP)
                ");
                $hist->execute([
                    ':order_id' => $orderId,
                    ':changed_by' => $adminName,
                    ':note' => 'Restoran membalas WhatsApp: Pesanan DITERIMA dan sedang dimasak. ' . $notes
                ]);
                Session::flash('info', 'Catatan konfirmasi restoran berhasil disimpan.');
            }
        } catch (Exception $e) {
            Session::flash('danger', 'Gagal memperbarui status: ' . $e->getMessage());
        }

        header('Location: ' . View::url("admin/pesanan/{$orderNumber}"));
        exit;
    }

    public function assignDriver(): void
    {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $orderNumber = $_POST['order_number'] ?? '';
        $driverId = (int) ($_POST['driver_id'] ?? 0);
        $user = Session::user();
        $adminName = $user['name'] ?? 'Admin';

        if ($driverId <= 0) {
            Session::flash('danger', 'Silakan pilih driver pengantar terlebih dahulu.');
            header('Location: ' . View::url("admin/pesanan/{$orderNumber}"));
            exit;
        }

        $driver = Driver::findById($driverId);
        if (!$driver) {
            Session::flash('danger', 'Driver tidak ditemukan.');
            header('Location: ' . View::url("admin/pesanan/{$orderNumber}"));
            exit;
        }

        try {
            // Assign driver & update status ke assigned_to_driver
            OrderWorkflowService::assignDriver($orderId, $driverId, $adminName);

            // Catat log WhatsApp driver
            WhatsAppService::logManualDispatch(
                $orderId,
                'driver',
                $driver['whatsapp'],
                'driver_dispatch',
                $adminName,
                "Tugas pengantaran diteruskan ke WhatsApp Driver {$driver['name']}."
            );

            Session::flash('success', "Driver {$driver['name']} berhasil ditugaskan dan log WhatsApp dicatat.");
        } catch (Exception $e) {
            Session::flash('danger', 'Gagal menugaskan driver: ' . $e->getMessage());
        }

        header('Location: ' . View::url("admin/pesanan/{$orderNumber}"));
        exit;
    }

    public function updateDeliveryStatus(): void
    {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $orderNumber = $_POST['order_number'] ?? '';
        $newStatus = $_POST['delivery_status'] ?? '';
        $notes = Security::sanitizeString($_POST['notes'] ?? '');
        $user = Session::user();
        $adminName = $user['name'] ?? 'Admin';

        try {
            OrderWorkflowService::updateStatus($orderId, $newStatus, $adminName, $notes ?: "Pembaruan status pengantaran menjadi " . Format::statusLabel($newStatus));
            Session::flash('success', "Status pengantaran berhasil diperbarui menjadi: " . Format::statusLabel($newStatus));
        } catch (Exception $e) {
            Session::flash('danger', 'Gagal memperbarui status pengantaran: ' . $e->getMessage());
        }

        header('Location: ' . View::url("admin/pesanan/{$orderNumber}"));
        exit;
    }

    public function reject(): void
    {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $orderNumber = $_POST['order_number'] ?? '';
        $reason = Security::sanitizeString($_POST['reason'] ?? 'Pesanan ditolak oleh admin.');
        $user = Session::user();
        $adminName = $user['name'] ?? 'Admin';

        try {
            OrderWorkflowService::updateStatus($orderId, 'rejected', $adminName, 'Ditolak: ' . $reason);
            Session::flash('warning', "Pesanan #{$orderNumber} telah ditolak.");
        } catch (Exception $e) {
            Session::flash('danger', 'Gagal menolak pesanan: ' . $e->getMessage());
        }

        header('Location: ' . View::url("admin/pesanan/{$orderNumber}"));
        exit;
    }
}
