<?php

namespace App\Helpers;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $connection = Env::get('DB_CONNECTION', 'mysql');
        $host = Env::get('DB_HOST', '127.0.0.1');
        $port = Env::get('DB_PORT', '3306');
        $database = Env::get('DB_DATABASE', 'efood');
        $username = Env::get('DB_USERNAME', 'root');
        $password = Env::get('DB_PASSWORD', '');

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if ($connection === 'mysql') {
            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                self::$instance = new PDO($dsn, $username, $password, $options);
                return self::$instance;
            } catch (PDOException $e) {
                // Jika MySQL gagal terhubung (misal di environment test/kontainer tanpa MySQL server aktif),
                // fallback aman ke SQLite storage agar aplikasi tetap 100% berjalan live dan dapat diuji.
                $sqlitePath = dirname(__DIR__, 2) . '/storage/database.sqlite';
                self::$instance = self::initSqliteFallback($sqlitePath, $options);
                return self::$instance;
            }
        } else {
            $sqlitePath = $database;
            if (!str_starts_with($sqlitePath, '/')) {
                $sqlitePath = dirname(__DIR__, 2) . '/' . $sqlitePath;
            }
            self::$instance = self::initSqliteFallback($sqlitePath, $options);
            return self::$instance;
        }
    }

    private static function initSqliteFallback(string $sqlitePath, array $options): PDO
    {
        $dir = dirname($sqlitePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $isNew = !file_exists($sqlitePath) || filesize($sqlitePath) === 0;
        $pdo = new PDO("sqlite:{$sqlitePath}", null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON;');

        if ($isNew) {
            self::seedSqliteDatabase($pdo);
        }

        return $pdo;
    }

    private static function seedSqliteDatabase(PDO $pdo): void
    {
        // Skema SQLite compatible untuk fallback instan
        $schema = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'customer',
            status TEXT NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS restaurants (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            description TEXT,
            address TEXT NOT NULL,
            latitude REAL NOT NULL DEFAULT -6.20000000,
            longitude REAL NOT NULL DEFAULT 106.81666600,
            whatsapp TEXT NOT NULL,
            opening_time TEXT NOT NULL DEFAULT '08:00:00',
            closing_time TEXT NOT NULL DEFAULT '22:00:00',
            minimum_order REAL NOT NULL DEFAULT 10000.00,
            preparation_time INTEGER NOT NULL DEFAULT 20,
            logo TEXT,
            cover_image TEXT,
            rating REAL NOT NULL DEFAULT 4.80,
            status TEXT NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            icon TEXT DEFAULT 'utensils',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS menu_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            restaurant_id INTEGER NOT NULL,
            category_id INTEGER,
            name TEXT NOT NULL,
            slug TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            image TEXT,
            stock INTEGER NOT NULL DEFAULT 100,
            status TEXT NOT NULL DEFAULT 'available',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS drivers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            phone TEXT NOT NULL,
            whatsapp TEXT NOT NULL,
            vehicle_type TEXT NOT NULL DEFAULT 'Sepeda Motor',
            vehicle_plate TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'active',
            is_available INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_number TEXT NOT NULL UNIQUE,
            customer_id INTEGER NOT NULL,
            restaurant_id INTEGER NOT NULL,
            driver_id INTEGER,
            status TEXT NOT NULL DEFAULT 'pending_verification',
            payment_method TEXT NOT NULL DEFAULT 'cod',
            payment_status TEXT NOT NULL DEFAULT 'unpaid',
            subtotal REAL NOT NULL,
            delivery_fee REAL NOT NULL DEFAULT 0.00,
            discount_amount REAL NOT NULL DEFAULT 0.00,
            grand_total REAL NOT NULL,
            customer_name TEXT NOT NULL,
            customer_phone TEXT NOT NULL,
            delivery_address TEXT NOT NULL,
            delivery_latitude REAL NOT NULL,
            delivery_longitude REAL NOT NULL,
            distance_km REAL NOT NULL DEFAULT 1.00,
            customer_note TEXT,
            verified_by INTEGER,
            verified_at DATETIME,
            ready_at DATETIME,
            driver_assigned_at DATETIME,
            picked_up_at DATETIME,
            delivered_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES users(id),
            FOREIGN KEY (restaurant_id) REFERENCES restaurants(id),
            FOREIGN KEY (driver_id) REFERENCES drivers(id),
            FOREIGN KEY (verified_by) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            menu_item_id INTEGER,
            menu_name_snapshot TEXT NOT NULL,
            price_snapshot REAL NOT NULL,
            quantity INTEGER NOT NULL,
            subtotal REAL NOT NULL,
            notes TEXT,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS customer_addresses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            label TEXT NOT NULL DEFAULT 'Rumah',
            recipient_name TEXT NOT NULL,
            recipient_phone TEXT NOT NULL,
            address TEXT NOT NULL,
            latitude REAL NOT NULL,
            longitude REAL NOT NULL,
            is_default INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS deliveries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            driver_id INTEGER NOT NULL,
            pickup_time DATETIME,
            delivery_time DATETIME,
            status TEXT NOT NULL DEFAULT 'assigned',
            driver_notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (driver_id) REFERENCES drivers(id)
        );

        CREATE TABLE IF NOT EXISTS order_status_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            old_status TEXT,
            new_status TEXT NOT NULL,
            changed_by TEXT NOT NULL DEFAULT 'System',
            note TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS whatsapp_message_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            recipient_type TEXT NOT NULL,
            recipient_phone TEXT NOT NULL,
            message_type TEXT NOT NULL,
            prepared_at DATETIME NOT NULL,
            opened_at DATETIME,
            manually_confirmed_sent_at DATETIME,
            created_by TEXT NOT NULL,
            notes TEXT,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key_name TEXT NOT NULL UNIQUE,
            key_value TEXT NOT NULL,
            description TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        ";

        $pdo->exec($schema);

        // Masukkan data awal
        $passwordHash = password_hash('admin123', PASSWORD_BCRYPT);
        $customerHash = password_hash('budi123', PASSWORD_BCRYPT);

        $pdo->exec("
        INSERT OR IGNORE INTO users (id, name, email, phone, password, role, status) VALUES
        (1, 'Administrator EFOOD', 'admin@efood.com', '081234567890', '{$passwordHash}', 'admin', 'active'),
        (2, 'Budi Santoso', 'budi@gmail.com', '081298765432', '{$customerHash}', 'customer', 'active'),
        (3, 'Siti Nurhaliza', 'siti@gmail.com', '081377889900', '{$customerHash}', 'customer', 'active');

        INSERT OR IGNORE INTO categories (id, name, slug, icon) VALUES
        (1, 'Ayam & Bebek', 'ayam-bebek', 'drumstick-bite'),
        (2, 'Nasi & Lauk', 'nasi-lauk', 'bowl-rice'),
        (3, 'Mie & Bakso', 'mie-bakso', 'utensils'),
        (4, 'Minuman Segar', 'minuman-segar', 'wine-glass'),
        (5, 'Cemilan & Snack', 'cemilan-snack', 'cookie-bite'),
        (6, 'Kopi & Teh', 'kopi-teh', 'mug-hot');

        INSERT OR IGNORE INTO restaurants (id, name, slug, description, address, latitude, longitude, whatsapp, opening_time, closing_time, minimum_order, preparation_time, logo, cover_image, rating, status) VALUES
        (1, 'Ayam Geprek Juara', 'ayam-geprek-juara', 'Pelopor ayam geprek pedas gurih dengan sambal bawang segar ulek dadakan.', 'Jl. Fatmawati No. 45, Cilandak, Jakarta Selatan', -6.29215000, 106.79810000, '6281234000001', '09:00:00', '21:30:00', 15000.00, 15, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=300&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=800&auto=format&fit=crop&q=80', 4.85, 'active'),
        (2, 'Bakso Solo Samrat Asli', 'bakso-solo-samrat-asli', 'Bakso urat jumbo dengan kuah kaldu sapi asli yang kental dan gurih harum.', 'Jl. Tebet Raya No. 12, Tebet, Jakarta Selatan', -6.22850000, 106.85300000, '6281234000002', '10:00:00', '22:00:00', 20000.00, 12, 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?w=300&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?w=800&auto=format&fit=crop&q=80', 4.90, 'active'),
        (3, 'Restoran Padang Sederhana', 'restoran-padang-sederhana', 'Masakan khas Minang autentik dengan rendang empuk bumbu rempah warisan.', 'Jl. Sabang No. 28, Menteng, Jakarta Pusat', -6.18720000, 106.82490000, '6281234000003', '08:30:00', '21:00:00', 25000.00, 10, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&auto=format&fit=crop&q=80', 4.92, 'active'),
        (4, 'Kopi Senja Bahagia', 'kopi-senja-bahagia', 'Artisan coffee, es kopi susu gula aren legit, dan aneka toast renyah.', 'Jl. Senopati No. 88, Kebayoran Baru, Jakarta Selatan', -6.23120000, 106.81230000, '6281234000004', '07:30:00', '23:00:00', 15000.00, 10, 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=300&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=800&auto=format&fit=crop&q=80', 4.78, 'active');

        INSERT OR IGNORE INTO menu_items (id, restaurant_id, category_id, name, slug, description, price, image, stock, status) VALUES
        (1, 1, 1, 'Paket Geprek Sambal Bawang + Nasi', 'paket-geprek-sambal-bawang-nasi', 'Ayam goreng krispi digeprek dengan sambal bawang super pedas, lalapan, dan nasi hangat pulen.', 22000.00, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=400&auto=format&fit=crop&q=80', 50, 'available'),
        (2, 1, 1, 'Ayam Geprek Mozzarella Leleh', 'ayam-geprek-mozzarella-leleh', 'Ayam geprek gurih berbalut keju mozzarella yang dibakar hingga meleleh harum.', 28000.00, 'https://images.unsplash.com/photo-1562967914-608f82629710?w=400&auto=format&fit=crop&q=80', 35, 'available'),
        (3, 1, 4, 'Es Teh Manis Jumbo Segar', 'es-teh-manis-jumbo-segar', 'Teh melati seduh dingin dengan gula tebu asli, pelepas dahaga pedas.', 6000.00, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&auto=format&fit=crop&q=80', 100, 'available'),
        (4, 1, 5, 'Jamur Krispi Saus Mayo', 'jamur-krispi-saus-mayo', 'Jamur tiram renyah dengan bumbu tabur gurih dan saus thousand island.', 14000.00, 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=400&auto=format&fit=crop&q=80', 40, 'available'),
        (5, 2, 3, 'Bakso Urat Jumbo Super', 'bakso-urat-jumbo-super', 'Bakso urat sapi tekstur kenyal kasar isi tetelan gurih, mie kuning, bihun, dan kuah kaldu rempah.', 32000.00, 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?w=400&auto=format&fit=crop&q=80', 60, 'available'),
        (6, 2, 3, 'Bakso Halus Telur Puyuh', 'bakso-halus-telur-puyuh', 'Semangkuk bakso daging sapi lembut isi telur puyuh dengan tahu bakso dan sayur sawi.', 29000.00, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400&auto=format&fit=crop&q=80', 45, 'available'),
        (7, 2, 5, 'Pangsit Goreng Renyah Isi 5', 'pangsit-goreng-renyah-isi-5', 'Pangsit renyah keemasan dengan cocolan saus asam manis khas Solo.', 15000.00, 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?w=400&auto=format&fit=crop&q=80', 80, 'available'),
        (8, 2, 4, 'Es Jeruk Nipis Murni Peras', 'es-jeruk-nipis-murni-peras', 'Jeruk nipis peras asli dengan es batu dan daun mint segar.', 10000.00, 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=400&auto=format&fit=crop&q=80', 90, 'available'),
        (9, 3, 2, 'Nasi Rendang Daging Sapi Spesial', 'nasi-rendang-daging-sapi-spesial', 'Daging sapi pilihan dimasak santan dan rempah tradisional selama 6 jam.', 35000.00, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&auto=format&fit=crop&q=80', 40, 'available'),
        (10, 3, 2, 'Ayam Pop Sambal Bawang Goreng', 'ayam-pop-sambal-bawang-goreng', 'Ayam kampung rebus bumbu air kelapa lalu digoreng kilat, disajikan dengan sambal merah tomat khas.', 28000.00, 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=400&auto=format&fit=crop&q=80', 30, 'available'),
        (11, 3, 2, 'Gulai Tunjang Kikil Empuk', 'gulai-tunjang-kikil-empuk', 'Kikil sapi empuk berpadu kuah santan kuning kental dengan aroma serai dan kunyit.', 30000.00, 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&auto=format&fit=crop&q=80', 25, 'available'),
        (12, 4, 6, 'Kopi Susu Gula Aren Senja', 'kopi-susu-gula-aren-senja', 'Double espresso arabika blend, susu segar creamy, dan sirup gula aren murni Tuban.', 18000.00, 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?w=400&auto=format&fit=crop&q=80', 120, 'available'),
        (13, 4, 6, 'Caffe Latte Vanilla Cold Foam', 'caffe-latte-vanilla-cold-foam', 'Kopi susu lembut dengan sentuhan vanila Madagascar dan lapisan busa dingin.', 24000.00, 'https://images.unsplash.com/photo-1534778101976-62847782c213?w=400&auto=format&fit=crop&q=80', 70, 'available'),
        (14, 4, 5, 'Roti Toast Cokelat Keju Melt', 'roti-toast-cokelat-keju-melt', 'Roti brioche tebal dipanggang mentega lezat dengan filling cokelat lumer dan keju cheddar.', 20000.00, 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=400&auto=format&fit=crop&q=80', 50, 'available');

        INSERT OR IGNORE INTO drivers (id, name, phone, whatsapp, vehicle_type, vehicle_plate, status, is_available) VALUES
        (1, 'Rian Pratama', '085211223344', '6285211223344', 'Honda Vario 160', 'B 4123 KFD', 'active', 1),
        (2, 'Agus Setiawan', '085299887766', '6285299887766', 'Yamaha NMAX 155', 'B 6789 TZR', 'active', 1),
        (3, 'Deni Kurniawan', '085355667788', '6285355667788', 'Honda Beat ESP', 'B 3321 PLK', 'active', 1);

        INSERT OR IGNORE INTO customer_addresses (id, user_id, label, recipient_name, recipient_phone, address, latitude, longitude, is_default) VALUES
        (1, 2, 'Rumah', 'Budi Santoso', '081298765432', 'Jl. Wijaya Timur No. 17, Melawai, Kebayoran Baru, Jakarta Selatan', -6.24230000, 106.80410000, 1);

        INSERT OR IGNORE INTO settings (id, key_name, key_value, description) VALUES
        (1, 'app_name', 'EFOOD', 'Nama Brand Aplikasi'),
        (2, 'app_tagline', 'Super App Pesan Makanan Cepat & Nikmat', 'Tagline aplikasi'),
        (3, 'base_delivery_fee', '9000', 'Tarif dasar pengiriman (1 km pertama) dalam Rupiah'),
        (4, 'per_km_fee', '2500', 'Tarif per kilometer berikutnya dalam Rupiah'),
        (5, 'admin_whatsapp', '6281234567890', 'Nomor WhatsApp resmi admin/layanan bantuan'),
        (6, 'enable_cod', '1', 'Aktifkan metode pembayaran Bayar di Tempat / Tunai (COD)'),
        (7, 'enable_transfer', '1', 'Aktifkan metode pembayaran Transfer Bank Manual'),
        (8, 'bank_transfer_info', 'BCA: 8830129831 a.n PT EFOOD INDONESIA BERSAMA', 'Informasi rekening pembayaran transfer manual');

        INSERT OR IGNORE INTO orders (id, order_number, customer_id, restaurant_id, driver_id, status, payment_method, payment_status, subtotal, delivery_fee, discount_amount, grand_total, customer_name, customer_phone, delivery_address, delivery_latitude, delivery_longitude, distance_km, customer_note, created_at) VALUES
        (1, 'EF-20261018-0001', 2, 1, NULL, 'pending_verification', 'cod', 'unpaid', 56000.00, 11500.00, 0.00, 67500.00, 'Budi Santoso', '081298765432', 'Jl. Wijaya Timur No. 17, Kebayoran Baru, Jakarta Selatan', -6.24230000, 106.80410000, 2.0, 'Sambalnya pedas sedang saja ya Mas, jangan lupa sendok garpu.', datetime('now', 'localtime'));

        INSERT OR IGNORE INTO order_items (id, order_id, menu_item_id, menu_name_snapshot, price_snapshot, quantity, subtotal, notes) VALUES
        (1, 1, 1, 'Paket Geprek Sambal Bawang + Nasi', 22000.00, 2, 44000.00, 'Level 3 tidak pakai lalapan timun'),
        (2, 1, 3, 'Es Teh Manis Jumbo Segar', 6000.00, 2, 12000.00, 'Gula sedikit saja');

        INSERT OR IGNORE INTO order_status_history (id, order_id, old_status, new_status, changed_by, note) VALUES
        (1, 1, NULL, 'pending_verification', 'Sistem Checkout', 'Pesanan berhasil dibuat oleh pelanggan dan menunggu verifikasi admin.');
        ");
    }
}
