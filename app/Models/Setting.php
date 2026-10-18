<?php

namespace App\Models;

use App\Helpers\Database;
use PDO;

class Setting
{
    public static function all(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM settings ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT key_value FROM settings WHERE key_name = :key LIMIT 1");
        $stmt->execute([':key' => $key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    public static function set(string $key, string $value): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO settings (key_name, key_value, updated_at) 
            VALUES (:key, :val, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE key_value = :val2, updated_at = CURRENT_TIMESTAMP
        ");
        try {
            return $stmt->execute([':key' => $key, ':val' => $value, ':val2' => $value]);
        } catch (\PDOException $e) {
            // SQLite compatibility
            $stmtSqlite = $db->prepare("
                INSERT OR REPLACE INTO settings (key_name, key_value, updated_at)
                VALUES (:key, :val, datetime('now', 'localtime'))
            ");
            return $stmtSqlite->execute([':key' => $key, ':val' => $value]);
        }
    }
}
