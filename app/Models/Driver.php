<?php

namespace App\Models;

use App\Helpers\Database;
use PDO;

class Driver
{
    public static function allActive(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM drivers WHERE status = 'active' ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public static function all(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT d.*, 
                   COUNT(o.id) as total_deliveries,
                   SUM(CASE WHEN o.status = 'delivered' THEN 1 ELSE 0 END) as successful_deliveries
            FROM drivers d
            LEFT JOIN orders o ON d.id = o.driver_id
            GROUP BY d.id
            ORDER BY d.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM drivers WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $driver = $stmt->fetch();
        return $driver ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO drivers (name, phone, whatsapp, vehicle_type, vehicle_plate, status, is_available)
            VALUES (:name, :phone, :whatsapp, :vehicle_type, :vehicle_plate, :status, :is_available)
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':phone' => $data['phone'],
            ':whatsapp' => $data['whatsapp'],
            ':vehicle_type' => $data['vehicle_type'] ?? 'Sepeda Motor',
            ':vehicle_plate' => $data['vehicle_plate'],
            ':status' => $data['status'] ?? 'active',
            ':is_available' => $data['is_available'] ?? 1
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE drivers SET
                name = :name,
                phone = :phone,
                whatsapp = :whatsapp,
                vehicle_type = :vehicle_type,
                vehicle_plate = :vehicle_plate,
                status = :status,
                is_available = :is_available,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':phone' => $data['phone'],
            ':whatsapp' => $data['whatsapp'],
            ':vehicle_type' => $data['vehicle_type'],
            ':vehicle_plate' => $data['vehicle_plate'],
            ':status' => $data['status'],
            ':is_available' => $data['is_available']
        ]);
    }
}
