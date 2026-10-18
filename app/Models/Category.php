<?php

namespace App\Models;

use App\Helpers\Database;
use PDO;

class Category
{
    public static function all(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM categories ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}
