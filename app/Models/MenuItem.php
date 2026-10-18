<?php

namespace App\Models;

use App\Helpers\Database;
use PDO;

class MenuItem
{
    public static function getByRestaurant(int $restaurantId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT m.*, c.name as category_name
            FROM menu_items m
            LEFT JOIN categories c ON m.category_id = c.id
            WHERE m.restaurant_id = :restaurant_id AND m.status != 'inactive'
            ORDER BY m.category_id ASC, m.price ASC
        ");
        $stmt->execute([':restaurant_id' => $restaurantId]);
        return $stmt->fetchAll();
    }

    public static function allAdmin(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT m.*, r.name as restaurant_name, c.name as category_name
            FROM menu_items m
            JOIN restaurants r ON m.restaurant_id = r.id
            LEFT JOIN categories c ON m.category_id = c.id
            ORDER BY m.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT m.*, r.name as restaurant_name, r.whatsapp as restaurant_whatsapp
            FROM menu_items m
            JOIN restaurants r ON m.restaurant_id = r.id
            WHERE m.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO menu_items (restaurant_id, category_id, name, slug, description, price, image, stock, status)
            VALUES (:restaurant_id, :category_id, :name, :slug, :description, :price, :image, :stock, :status)
        ");
        $stmt->execute([
            ':restaurant_id' => $data['restaurant_id'],
            ':category_id' => $data['category_id'] ?: null,
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':image' => $data['image'] ?? null,
            ':stock' => $data['stock'] ?? 100,
            ':status' => $data['status'] ?? 'available'
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE menu_items SET
                restaurant_id = :restaurant_id,
                category_id = :category_id,
                name = :name,
                slug = :slug,
                description = :description,
                price = :price,
                image = :image,
                stock = :stock,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':restaurant_id' => $data['restaurant_id'],
            ':category_id' => $data['category_id'] ?: null,
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':image' => $data['image'] ?? null,
            ':stock' => $data['stock'],
            ':status' => $data['status']
        ]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM menu_items WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
