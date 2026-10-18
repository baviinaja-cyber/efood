<?php

namespace App\Models;

use App\Helpers\Database;
use PDO;

class Restaurant
{
    public static function allActive(?string $search = null): array
    {
        $db = Database::getConnection();
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM menu_items m WHERE m.restaurant_id = r.id AND m.status = 'available') as total_menu
                FROM restaurants r 
                WHERE r.status = 'active'";

        $params = [];
        if (!empty($search)) {
            $sql .= " AND (r.name LIKE :search OR r.description LIKE :search OR r.address LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY r.rating DESC, r.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function allAdmin(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT r.*, COUNT(m.id) as total_menu_count
            FROM restaurants r
            LEFT JOIN menu_items m ON r.id = m.restaurant_id
            GROUP BY r.id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM restaurants WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM restaurants WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO restaurants (
                name, slug, description, address, latitude, longitude, whatsapp,
                opening_time, closing_time, minimum_order, preparation_time, logo, cover_image, status
            ) VALUES (
                :name, :slug, :description, :address, :latitude, :longitude, :whatsapp,
                :opening_time, :closing_time, :minimum_order, :preparation_time, :logo, :cover_image, :status
            )
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':description' => $data['description'] ?? null,
            ':address' => $data['address'],
            ':latitude' => $data['latitude'] ?? -6.20000000,
            ':longitude' => $data['longitude'] ?? 106.81666600,
            ':whatsapp' => $data['whatsapp'],
            ':opening_time' => $data['opening_time'] ?? '08:00:00',
            ':closing_time' => $data['closing_time'] ?? '22:00:00',
            ':minimum_order' => $data['minimum_order'] ?? 10000,
            ':preparation_time' => $data['preparation_time'] ?? 20,
            ':logo' => $data['logo'] ?? null,
            ':cover_image' => $data['cover_image'] ?? null,
            ':status' => $data['status'] ?? 'active'
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE restaurants SET
                name = :name,
                slug = :slug,
                description = :description,
                address = :address,
                latitude = :latitude,
                longitude = :longitude,
                whatsapp = :whatsapp,
                opening_time = :opening_time,
                closing_time = :closing_time,
                minimum_order = :minimum_order,
                preparation_time = :preparation_time,
                logo = :logo,
                cover_image = :cover_image,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':description' => $data['description'] ?? null,
            ':address' => $data['address'],
            ':latitude' => $data['latitude'],
            ':longitude' => $data['longitude'],
            ':whatsapp' => $data['whatsapp'],
            ':opening_time' => $data['opening_time'],
            ':closing_time' => $data['closing_time'],
            ':minimum_order' => $data['minimum_order'],
            ':preparation_time' => $data['preparation_time'],
            ':logo' => $data['logo'] ?? null,
            ':cover_image' => $data['cover_image'] ?? null,
            ':status' => $data['status'] ?? 'active'
        ]);
    }
}
