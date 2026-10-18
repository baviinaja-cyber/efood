<?php

namespace App\Services;

use App\Helpers\Database;
use App\Helpers\Session;
use PDO;
use Exception;

class OrderWorkflowService
{
    /**
     * Peta transisi status yang sah (Mencegah manipulasi request)
     */
    private static array $allowedTransitions = [
        'pending_verification' => ['verified', 'rejected', 'cancelled'],
        'verified' => ['restaurant_processing', 'rejected', 'cancelled'],
        'restaurant_processing' => ['ready_for_delivery', 'rejected'],
        'ready_for_delivery' => ['assigned_to_driver', 'rejected'],
        'assigned_to_driver' => ['picked_up', 'delivery_failed'],
        'picked_up' => ['on_delivery', 'delivery_failed'],
        'on_delivery' => ['delivered', 'delivery_failed'],
        'delivered' => [], // Final
        'rejected' => [],  // Final
        'cancelled' => [], // Final
        'delivery_failed' => ['ready_for_delivery', 'cancelled'] // Bisa ditugaskan ulang jika gagal
    ];

    public static function canTransition(string $currentStatus, string $targetStatus): bool
    {
        $allowed = self::$allowedTransitions[$currentStatus] ?? [];
        return in_array($targetStatus, $allowed, true);
    }

    /**
     * Update status pesanan dengan audit history
     */
    public static function updateStatus(int $orderId, string $newStatus, string $changedBy, string $note = ''): bool
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT id, status, payment_method, payment_status FROM orders WHERE id = :id");
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new Exception("Pesanan dengan ID #{$orderId} tidak ditemukan.");
        }

        $oldStatus = $order['status'];

        if (!self::canTransition($oldStatus, $newStatus)) {
            throw new Exception("Transisi status tidak diizinkan: dari '{$oldStatus}' ke '{$newStatus}'.");
        }

        $now = date('Y-m-d H:i:s');

        // Buka transaksi database
        $db->beginTransaction();

        try {
            // Update order
            $extraUpdates = "";
            $params = [
                ':status' => $newStatus,
                ':updated_at' => $now,
                ':id' => $orderId
            ];

            if ($newStatus === 'verified') {
                $user = Session::user();
                $extraUpdates .= ", verified_by = :verified_by, verified_at = :verified_at";
                $params[':verified_by'] = $user['id'] ?? null;
                $params[':verified_at'] = $now;
            } elseif ($newStatus === 'delivered') {
                $extraUpdates .= ", delivered_at = :delivered_at, payment_status = 'paid'";
                $params[':delivered_at'] = $now;
            } elseif ($newStatus === 'picked_up') {
                $extraUpdates .= ", picked_up_at = :picked_up_at";
                $params[':picked_up_at'] = $now;
            } elseif ($newStatus === 'ready_for_delivery') {
                $extraUpdates .= ", ready_at = :ready_at";
                $params[':ready_at'] = $now;
            }

            $updateSql = "UPDATE orders SET status = :status, updated_at = :updated_at {$extraUpdates} WHERE id = :id";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute($params);

            // Simpan ke order_status_history
            $histStmt = $db->prepare("
                INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note, created_at)
                VALUES (:order_id, :old_status, :new_status, :changed_by, :note, :created_at)
            ");
            $histStmt->execute([
                ':order_id' => $orderId,
                ':old_status' => $oldStatus,
                ':new_status' => $newStatus,
                ':changed_by' => $changedBy,
                ':note' => $note,
                ':created_at' => $now
            ]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Tugaskan Driver ke Pesanan
     */
    public static function assignDriver(int $orderId, int $driverId, string $adminName): bool
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT id, status FROM orders WHERE id = :id");
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new Exception("Pesanan tidak ditemukan.");
        }

        $stmtDriver = $db->prepare("SELECT id, name, phone, whatsapp FROM drivers WHERE id = :id AND status = 'active'");
        $stmtDriver->execute([':id' => $driverId]);
        $driver = $stmtDriver->fetch();

        if (!$driver) {
            throw new Exception("Driver tidak ditemukan atau sedang tidak aktif.");
        }

        $db->beginTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $upd = $db->prepare("
                UPDATE orders 
                SET driver_id = :driver_id, status = 'assigned_to_driver', driver_assigned_at = :assigned_at, updated_at = :updated_at 
                WHERE id = :id
            ");
            $upd->execute([
                ':driver_id' => $driverId,
                ':assigned_at' => $now,
                ':updated_at' => $now,
                ':id' => $orderId
            ]);

            // Catat history
            $hist = $db->prepare("
                INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note, created_at)
                VALUES (:order_id, :old_status, 'assigned_to_driver', :changed_by, :note, :created_at)
            ");
            $hist->execute([
                ':order_id' => $orderId,
                ':old_status' => $order['status'],
                ':changed_by' => $adminName,
                ':note' => "Pesanan ditugaskan ke driver {$driver['name']} ({$driver['whatsapp']})",
                ':created_at' => $now
            ]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
