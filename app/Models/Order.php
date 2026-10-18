<?php

namespace App\Models;

use App\Helpers\Database;
use App\Services\DistanceService;
use PDO;
use Exception;

class Order
{
    /**
     * Buat Pesanan Baru dengan Database Transaction
     */
    public static function createOrderWithItems(array $orderData, array $cartItems): array
    {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $restaurantId = (int) $orderData['restaurant_id'];

            // 1. Validasi restoran
            $stmtRes = $db->prepare("SELECT * FROM restaurants WHERE id = :id AND status = 'active' LIMIT 1");
            $stmtRes->execute([':id' => $restaurantId]);
            $restaurant = $stmtRes->fetch();

            if (!$restaurant) {
                throw new Exception("Restoran tidak ditemukan atau sedang tutup.");
            }

            // 2. Validasi stok dan hitung subtotal di server
            $subtotal = 0;
            $itemsToInsert = [];

            foreach ($cartItems as $menuId => $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                if ($qty <= 0) continue;

                $stmtMenu = $db->prepare("SELECT * FROM menu_items WHERE id = :id AND restaurant_id = :res_id AND status = 'available' LIMIT 1");
                $stmtMenu->execute([':id' => $menuId, ':res_id' => $restaurantId]);
                $menu = $stmtMenu->fetch();

                if (!$menu) {
                    throw new Exception("Menu item '{$item['name']}' tidak valid atau sudah habis.");
                }

                if ($menu['stock'] < $qty) {
                    throw new Exception("Stok untuk '{$menu['name']}' tidak mencukupi (sisa {$menu['stock']}).");
                }

                $itemPrice = (float) $menu['price'];
                $itemSubtotal = $itemPrice * $qty;
                $subtotal += $itemSubtotal;

                $itemsToInsert[] = [
                    'menu_id' => $menu['id'],
                    'name_snapshot' => $menu['name'],
                    'price_snapshot' => $itemPrice,
                    'quantity' => $qty,
                    'subtotal' => $itemSubtotal,
                    'notes' => $item['notes'] ?? ''
                ];
            }

            if (empty($itemsToInsert)) {
                throw new Exception("Keranjang belanja kosong.");
            }

            if ($subtotal < (float) $restaurant['minimum_order']) {
                throw new Exception("Subtotal pesanan kurang dari minimum pemesanan restoran.");
            }

            // 3. Hitung ongkir berdasarkan koordinat pengiriman
            $custLat = (float) $orderData['delivery_latitude'];
            $custLng = (float) $orderData['delivery_longitude'];
            $resLat = (float) $restaurant['latitude'];
            $resLng = (float) $restaurant['longitude'];

            $distanceKm = DistanceService::calculateDistance($resLat, $resLng, $custLat, $custLng);
            $deliveryFee = DistanceService::calculateDeliveryFee($distanceKm);
            $discountAmount = 0.00;
            $grandTotal = $subtotal + $deliveryFee - $discountAmount;

            // 4. Generate Order Number unik: EF-YYYYMMDD-XXXX
            $datePrefix = date('Ymd');
            $randNum = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $orderNumber = "EF-{$datePrefix}-{$randNum}";

            // 5. Insert orders
            $stmtOrder = $db->prepare("
                INSERT INTO orders (
                    order_number, customer_id, restaurant_id, status, payment_method, payment_status,
                    subtotal, delivery_fee, discount_amount, grand_total,
                    customer_name, customer_phone, delivery_address, delivery_latitude, delivery_longitude,
                    distance_km, customer_note, created_at, updated_at
                ) VALUES (
                    :order_number, :customer_id, :restaurant_id, 'pending_verification', :payment_method, :payment_status,
                    :subtotal, :delivery_fee, :discount_amount, :grand_total,
                    :customer_name, :customer_phone, :delivery_address, :delivery_latitude, :delivery_longitude,
                    :distance_km, :customer_note, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )
            ");

            $stmtOrder->execute([
                ':order_number' => $orderNumber,
                ':customer_id' => $orderData['customer_id'],
                ':restaurant_id' => $restaurantId,
                ':payment_method' => $orderData['payment_method'] ?? 'cod',
                ':payment_status' => 'unpaid',
                ':subtotal' => $subtotal,
                ':delivery_fee' => $deliveryFee,
                ':discount_amount' => $discountAmount,
                ':grand_total' => $grandTotal,
                ':customer_name' => $orderData['customer_name'],
                ':customer_phone' => $orderData['customer_phone'],
                ':delivery_address' => $orderData['delivery_address'],
                ':delivery_latitude' => $custLat,
                ':delivery_longitude' => $custLng,
                ':distance_km' => $distanceKm,
                ':customer_note' => $orderData['customer_note'] ?? null,
            ]);

            $orderId = (int) $db->lastInsertId();

            // 6. Insert order items & kurangi stok
            $stmtItem = $db->prepare("
                INSERT INTO order_items (order_id, menu_item_id, menu_name_snapshot, price_snapshot, quantity, subtotal, notes)
                VALUES (:order_id, :menu_item_id, :name_snapshot, :price_snapshot, :quantity, :subtotal, :notes)
            ");

            $stmtStock = $db->prepare("UPDATE menu_items SET stock = stock - :qty WHERE id = :id");

            foreach ($itemsToInsert as $it) {
                $stmtItem->execute([
                    ':order_id' => $orderId,
                    ':menu_item_id' => $it['menu_id'],
                    ':name_snapshot' => $it['name_snapshot'],
                    ':price_snapshot' => $it['price_snapshot'],
                    ':quantity' => $it['quantity'],
                    ':subtotal' => $it['subtotal'],
                    ':notes' => $it['notes']
                ]);

                $stmtStock->execute([':qty' => $it['quantity'], ':id' => $it['menu_id']]);
            }

            // 7. Insert status history
            $stmtHist = $db->prepare("
                INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note, created_at)
                VALUES (:order_id, NULL, 'pending_verification', 'Pelanggan', 'Pesanan dibuat melalui aplikasi EFOOD.', CURRENT_TIMESTAMP)
            ");
            $stmtHist->execute([':order_id' => $orderId]);

            $db->commit();

            return [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'grand_total' => $grandTotal
            ];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT o.*, 
                   r.name as restaurant_name, r.whatsapp as restaurant_whatsapp, r.address as restaurant_address,
                   r.latitude as restaurant_latitude, r.longitude as restaurant_longitude,
                   d.name as driver_name, d.phone as driver_phone, d.whatsapp as driver_whatsapp, d.vehicle_plate as driver_plate,
                   u.name as customer_account_name, u.email as customer_email
            FROM orders o
            JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN drivers d ON o.driver_id = d.id
            JOIN users u ON o.customer_id = u.id
            WHERE o.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public static function findByOrderNumber(string $orderNumber): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT o.*, 
                   r.name as restaurant_name, r.slug as restaurant_slug, r.whatsapp as restaurant_whatsapp, 
                   r.address as restaurant_address, r.latitude as restaurant_latitude, r.longitude as restaurant_longitude,
                   d.name as driver_name, d.phone as driver_phone, d.whatsapp as driver_whatsapp, d.vehicle_plate as driver_plate
            FROM orders o
            JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN drivers d ON o.driver_id = d.id
            WHERE o.order_number = :num
            LIMIT 1
        ");
        $stmt->execute([':num' => $orderNumber]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public static function getItems(int $orderId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public static function getStatusHistory(int $orderId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM order_status_history WHERE order_id = :order_id ORDER BY created_at ASC");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public static function getWhatsAppLogs(int $orderId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM whatsapp_message_logs WHERE order_id = :order_id ORDER BY prepared_at DESC");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public static function getByCustomer(int $customerId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT o.*, r.name as restaurant_name, r.slug as restaurant_slug, r.logo as restaurant_logo,
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
            FROM orders o
            JOIN restaurants r ON o.restaurant_id = r.id
            WHERE o.customer_id = :customer_id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([':customer_id' => $customerId]);
        return $stmt->fetchAll();
    }

    public static function getAllAdmin(?string $status = null, ?string $search = null): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT o.*, r.name as restaurant_name, d.name as driver_name
            FROM orders o
            JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN drivers d ON o.driver_id = d.id
            WHERE 1=1
        ";

        $params = [];
        if (!empty($status)) {
            $sql .= " AND o.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (o.order_number LIKE :search OR o.customer_name LIKE :search OR o.customer_phone LIKE :search OR r.name LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY o.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getDashboardStats(): array
    {
        $db = Database::getConnection();

        $stats = [
            'pending_verification' => 0,
            'restaurant_processing' => 0,
            'ready_for_delivery' => 0,
            'on_delivery' => 0,
            'delivered' => 0,
            'total_restaurants' => 0,
            'total_customers' => 0,
            'total_revenue' => 0.00
        ];

        // Status counts
        $stmt = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
        while ($row = $stmt->fetch()) {
            if (isset($stats[$row['status']])) {
                $stats[$row['status']] = (int) $row['cnt'];
            }
            if (in_array($row['status'], ['assigned_to_driver', 'picked_up', 'on_delivery'])) {
                $stats['on_delivery'] += (int) $row['cnt'];
            }
        }

        // Active restaurants
        $stmtRes = $db->query("SELECT COUNT(*) FROM restaurants WHERE status = 'active'");
        $stats['total_restaurants'] = (int) $stmtRes->fetchColumn();

        // Customers
        $stmtCust = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
        $stats['total_customers'] = (int) $stmtCust->fetchColumn();

        // Revenue from delivered orders
        $stmtRev = $db->query("SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE status = 'delivered'");
        $stats['total_revenue'] = (float) $stmtRev->fetchColumn();

        return $stats;
    }
}
