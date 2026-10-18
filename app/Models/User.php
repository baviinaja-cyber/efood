<?php

namespace App\Models;

use App\Helpers\Database;
use PDO;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, name, email, phone, role, status, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO users (name, email, phone, password, role, status)
            VALUES (:name, :email, :phone, :password, :role, :status)
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':role' => $data['role'] ?? 'customer',
            ':status' => $data['status'] ?? 'active'
        ]);
        return (int) $db->lastInsertId();
    }

    public static function getAllCustomers(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT u.*, COUNT(o.id) as total_orders, COALESCE(SUM(o.grand_total), 0) as total_spent
            FROM users u
            LEFT JOIN orders o ON u.id = o.customer_id
            WHERE u.role = 'customer'
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");
        return $stmt->fetchAll();
    }
}
