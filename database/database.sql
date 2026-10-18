-- ======================================================
-- EFOOD Database Schema & Seed Data (MySQL 8+ / MariaDB)
-- Encoding: UTF-8 (utf8mb4)
-- Engine: InnoDB
-- ======================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `whatsapp_message_logs`;
DROP TABLE IF EXISTS `order_status_history`;
DROP TABLE IF EXISTS `deliveries`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `customer_addresses`;
DROP TABLE IF EXISTS `menu_items`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `drivers`;
DROP TABLE IF EXISTS `restaurants`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Tabel users
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(25) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
  `status` ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel restaurants
CREATE TABLE `restaurants` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `address` TEXT NOT NULL,
  `latitude` DECIMAL(10, 8) NOT NULL DEFAULT -6.20000000,
  `longitude` DECIMAL(11, 8) NOT NULL DEFAULT 106.81666600,
  `whatsapp` VARCHAR(25) NOT NULL,
  `opening_time` TIME NOT NULL DEFAULT '08:00:00',
  `closing_time` TIME NOT NULL DEFAULT '22:00:00',
  `minimum_order` DECIMAL(12, 2) NOT NULL DEFAULT 10000.00,
  `preparation_time` INT NOT NULL DEFAULT 20, -- menit
  `logo` VARCHAR(255) NULL,
  `cover_image` VARCHAR(255) NULL,
  `rating` DECIMAL(3, 2) NOT NULL DEFAULT 4.80,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_restaurants_status` (`status`),
  INDEX `idx_restaurants_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel categories
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(110) NOT NULL UNIQUE,
  `icon` VARCHAR(100) NULL DEFAULT 'utensils',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel menu_items
CREATE TABLE `menu_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `restaurant_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(12, 2) NOT NULL,
  `image` VARCHAR(255) NULL,
  `stock` INT NOT NULL DEFAULT 100,
  `status` ENUM('available', 'sold_out', 'inactive') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_menu_restaurant` (`restaurant_id`),
  INDEX `idx_menu_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel drivers
CREATE TABLE `drivers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(25) NOT NULL,
  `whatsapp` VARCHAR(25) NOT NULL,
  `vehicle_type` VARCHAR(50) NOT NULL DEFAULT 'Sepeda Motor',
  `vehicle_plate` VARCHAR(20) NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `is_available` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_drivers_available` (`is_available`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel orders
CREATE TABLE `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(30) NOT NULL UNIQUE,
  `customer_id` INT UNSIGNED NOT NULL,
  `restaurant_id` INT UNSIGNED NOT NULL,
  `driver_id` INT UNSIGNED NULL,
  `status` ENUM(
    'pending_verification',
    'verified',
    'restaurant_processing',
    'ready_for_delivery',
    'assigned_to_driver',
    'picked_up',
    'on_delivery',
    'delivered',
    'rejected',
    'cancelled',
    'delivery_failed'
  ) NOT NULL DEFAULT 'pending_verification',
  `payment_method` ENUM('cod', 'transfer') NOT NULL DEFAULT 'cod',
  `payment_status` ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid',
  `subtotal` DECIMAL(12, 2) NOT NULL,
  `delivery_fee` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `grand_total` DECIMAL(12, 2) NOT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(25) NOT NULL,
  `delivery_address` TEXT NOT NULL,
  `delivery_latitude` DECIMAL(10, 8) NOT NULL,
  `delivery_longitude` DECIMAL(11, 8) NOT NULL,
  `distance_km` DECIMAL(6, 2) NOT NULL DEFAULT 1.00,
  `customer_note` TEXT NULL,
  `verified_by` INT UNSIGNED NULL,
  `verified_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`),
  FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_orders_status` (`status`),
  INDEX `idx_orders_customer` (`customer_id`),
  INDEX `idx_orders_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel order_items
CREATE TABLE `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `menu_item_id` INT UNSIGNED NULL,
  `menu_name_snapshot` VARCHAR(150) NOT NULL,
  `price_snapshot` DECIMAL(12, 2) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `subtotal` DECIMAL(12, 2) NOT NULL,
  `notes` VARCHAR(255) NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE SET NULL,
  INDEX `idx_order_items_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabel customer_addresses
CREATE TABLE `customer_addresses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(50) NOT NULL DEFAULT 'Rumah',
  `recipient_name` VARCHAR(100) NOT NULL,
  `recipient_phone` VARCHAR(25) NOT NULL,
  `address` TEXT NOT NULL,
  `latitude` DECIMAL(10, 8) NOT NULL,
  `longitude` DECIMAL(11, 8) NOT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_customer_address_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabel deliveries
CREATE TABLE `deliveries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `driver_id` INT UNSIGNED NOT NULL,
  `pickup_time` DATETIME NULL,
  `delivery_time` DATETIME NULL,
  `status` ENUM('assigned', 'picked_up', 'delivered', 'failed') NOT NULL DEFAULT 'assigned',
  `driver_notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`),
  INDEX `idx_deliveries_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tabel order_status_history
CREATE TABLE `order_status_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `old_status` VARCHAR(50) NULL,
  `new_status` VARCHAR(50) NOT NULL,
  `changed_by` VARCHAR(100) NOT NULL DEFAULT 'System',
  `note` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  INDEX `idx_history_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tabel whatsapp_message_logs
CREATE TABLE `whatsapp_message_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `recipient_type` ENUM('restaurant', 'driver', 'customer') NOT NULL,
  `recipient_phone` VARCHAR(25) NOT NULL,
  `message_type` VARCHAR(50) NOT NULL,
  `prepared_at` DATETIME NOT NULL,
  `opened_at` DATETIME NULL,
  `manually_confirmed_sent_at` DATETIME NULL,
  `created_by` VARCHAR(100) NOT NULL,
  `notes` TEXT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  INDEX `idx_wa_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Tabel settings
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key_name` VARCHAR(100) NOT NULL UNIQUE,
  `key_value` TEXT NOT NULL,
  `description` VARCHAR(255) NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- SEED DATA AWAL
-- ======================================================

-- 1. Users (Password: admin123 untuk admin, budi123 untuk budi)
-- bcrypt hashes generated with cost 10
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`) VALUES
(1, 'Administrator EFOOD', 'admin@efood.com', '081234567890', '$2y$10$wO3t4hD.eEaH3u5kH0O8cOXd8qR1q2T1zM5P6vO5w0zGzVzU1y2W2', 'admin', 'active'),
(2, 'Budi Santoso', 'budi@gmail.com', '081298765432', '$2y$10$wO3t4hD.eEaH3u5kH0O8cOXd8qR1q2T1zM5P6vO5w0zGzVzU1y2W2', 'customer', 'active'),
(3, 'Siti Nurhaliza', 'siti@gmail.com', '081377889900', '$2y$10$wO3t4hD.eEaH3u5kH0O8cOXd8qR1q2T1zM5P6vO5w0zGzVzU1y2W2', 'customer', 'active');

-- 2. Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`) VALUES
(1, 'Ayam & Bebek', 'ayam-bebek', 'drumstick-bite'),
(2, 'Nasi & Lauk', 'nasi-lauk', 'bowl-rice'),
(3, 'Mie & Bakso', 'mie-bakso', 'utensils'),
(4, 'Minuman Segar', 'minuman-segar', 'wine-glass'),
(5, 'Cemilan & Snack', 'cemilan-snack', 'cookie-bite'),
(6, 'Kopi & Teh', 'kopi-teh', 'mug-hot');

-- 3. Restaurants
INSERT INTO `restaurants` (`id`, `name`, `slug`, `description`, `address`, `latitude`, `longitude`, `whatsapp`, `opening_time`, `closing_time`, `minimum_order`, `preparation_time`, `logo`, `cover_image`, `rating`, `status`) VALUES
(1, 'Ayam Geprek Juara', 'ayam-geprek-juara', 'Pelopor ayam geprek pedas gurih dengan sambal bawang segar ulek dadakan.', 'Jl. Fatmawati No. 45, Cilandak, Jakarta Selatan', -6.29215000, 106.79810000, '6281234000001', '09:00:00', '21:30:00', 15000.00, 15, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=300&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=800&auto=format&fit=crop&q=80', 4.85, 'active'),
(2, 'Bakso Solo Samrat Asli', 'bakso-solo-samrat-asli', 'Bakso urat jumbo dengan kuah kaldu sapi asli yang kental dan gurih harum.', 'Jl. Tebet Raya No. 12, Tebet, Jakarta Selatan', -6.22850000, 106.85300000, '6281234000002', '10:00:00', '22:00:00', 20000.00, 12, 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?w=300&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?w=800&auto=format&fit=crop&q=80', 4.90, 'active'),
(3, 'Restoran Padang Sederhana', 'restoran-padang-sederhana', 'Masakan khas Minang autentik dengan rendang empuk bumbu rempah warisan.', 'Jl. Sabang No. 28, Menteng, Jakarta Pusat', -6.18720000, 106.82490000, '6281234000003', '08:30:00', '21:00:00', 25000.00, 10, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&auto=format&fit=crop&q=80', 4.92, 'active'),
(4, 'Kopi Senja Bahagia', 'kopi-senja-bahagia', 'Artisan coffee, es kopi susu gula aren legit, dan aneka toast renyah.', 'Jl. Senopati No. 88, Kebayoran Baru, Jakarta Selatan', -6.23120000, 106.81230000, '6281234000004', '07:30:00', '23:00:00', 15000.00, 10, 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=300&auto=format&fit=crop&q=80', 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=800&auto=format&fit=crop&q=80', 4.78, 'active');

-- 4. Menu Items
INSERT INTO `menu_items` (`id`, `restaurant_id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `stock`, `status`) VALUES
-- Menu Ayam Geprek Juara
(1, 1, 1, 'Paket Geprek Sambal Bawang + Nasi', 'paket-geprek-sambal-bawang-nasi', 'Ayam goreng krispi digeprek dengan sambal bawang super pedas, lalapan, dan nasi hangat pulen.', 22000.00, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=400&auto=format&fit=crop&q=80', 50, 'available'),
(2, 1, 1, 'Ayam Geprek Mozzarella Leleh', 'ayam-geprek-mozzarella-leleh', 'Ayam geprek gurih berbalut keju mozzarella yang dibakar hingga meleleh harum.', 28000.00, 'https://images.unsplash.com/photo-1562967914-608f82629710?w=400&auto=format&fit=crop&q=80', 35, 'available'),
(3, 1, 4, 'Es Teh Manis Jumbo Segar', 'es-teh-manis-jumbo-segar', 'Teh melati seduh dingin dengan gula tebu asli, pelepas dahaga pedas.', 6000.00, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&auto=format&fit=crop&q=80', 100, 'available'),
(4, 1, 5, 'Jamur Krispi Saus Mayo', 'jamur-krispi-saus-mayo', 'Jamur tiram renyah dengan bumbu tabur gurih dan saus thousand island.', 14000.00, 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=400&auto=format&fit=crop&q=80', 40, 'available'),

-- Menu Bakso Solo Samrat Asli
(5, 2, 3, 'Bakso Urat Jumbo Super', 'bakso-urat-jumbo-super', 'Bakso urat sapi tekstur kenyal kasar isi tetelan gurih, mie kuning, bihun, dan kuah kaldu rempah.', 32000.00, 'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?w=400&auto=format&fit=crop&q=80', 60, 'available'),
(6, 2, 3, 'Bakso Halus Telur Puyuh', 'bakso-halus-telur-puyuh', 'Semangkuk bakso daging sapi lembut isi telur puyuh dengan tahu bakso dan sayur sawi.', 29000.00, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400&auto=format&fit=crop&q=80', 45, 'available'),
(7, 2, 5, 'Pangsit Goreng Renyah Isi 5', 'pangsit-goreng-renyah-isi-5', 'Pangsit renyah keemasan dengan cocolan saus asam manis khas Solo.', 15000.00, 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?w=400&auto=format&fit=crop&q=80', 80, 'available'),
(8, 2, 4, 'Es Jeruk Nipis Murni Peras', 'es-jeruk-nipis-murni-peras', 'Jeruk nipis peras asli dengan es batu dan daun mint segar.', 10000.00, 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=400&auto=format&fit=crop&q=80', 90, 'available'),

-- Menu Padang Sederhana
(9, 3, 2, 'Nasi Rendang Daging Sapi Spesial', 'nasi-rendang-daging-sapi-spesial', 'Daging sapi pilihan dimasak santan dan rempah tradisional selama 6 jam, disajikan dengan daun singkong dan sambal hijau.', 35000.00, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&auto=format&fit=crop&q=80', 40, 'available'),
(10, 3, 2, 'Ayam Pop Sambal Bawang Goreng', 'ayam-pop-sambal-bawang-goreng', 'Ayam kampung rebus bumbu air kelapa lalu digoreng kilat, disajikan dengan sambal merah tomat khas.', 28000.00, 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=400&auto=format&fit=crop&q=80', 30, 'available'),
(11, 3, 2, 'Gulai Tunjang Kikil Empuk', 'gulai-tunjang-kikil-empuk', 'Kikil sapi empuk berpadu kuah santan kuning kental dengan aroma serai dan kunyit.', 30000.00, 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&auto=format&fit=crop&q=80', 25, 'available'),

-- Menu Kopi Senja Bahagia
(12, 4, 6, 'Kopi Susu Gula Aren Senja', 'kopi-susu-gula-aren-senja', 'Double espresso arabika blend, susu segar creamy, dan sirup gula aren murni Tuban.', 18000.00, 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?w=400&auto=format&fit=crop&q=80', 120, 'available'),
(13, 4, 6, 'Caffe Latte Vanilla Cold Foam', 'caffe-latte-vanilla-cold-foam', 'Kopi susu lembut dengan sentuhan vanila Madagascar dan lapisan busa dingin.', 24000.00, 'https://images.unsplash.com/photo-1534778101976-62847782c213?w=400&auto=format&fit=crop&q=80', 70, 'available'),
(14, 4, 5, 'Roti Toast Cokelat Keju Melt', 'roti-toast-cokelat-keju-melt', 'Roti brioche tebal dipanggang mentega lezat dengan filling cokelat lumer dan keju cheddar.', 20000.00, 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=400&auto=format&fit=crop&q=80', 50, 'available');

-- 5. Drivers
INSERT INTO `drivers` (`id`, `name`, `phone`, `whatsapp`, `vehicle_type`, `vehicle_plate`, `status`, `is_available`) VALUES
(1, 'Rian Pratama', '085211223344', '6285211223344', 'Honda Vario 160', 'B 4123 KFD', 'active', 1),
(2, 'Agus Setiawan', '085299887766', '6285299887766', 'Yamaha NMAX 155', 'B 6789 TZR', 'active', 1),
(3, 'Deni Kurniawan', '085355667788', '6285355667788', 'Honda Beat ESP', 'B 3321 PLK', 'active', 1);

-- 6. Customer Addresses
INSERT INTO `customer_addresses` (`id`, `user_id`, `label`, `recipient_name`, `recipient_phone`, `address`, `latitude`, `longitude`, `is_default`) VALUES
(1, 2, 'Rumah', 'Budi Santoso', '081298765432', 'Jl. Wijaya Timur No. 17, Melawai, Kebayoran Baru, Jakarta Selatan', -6.24230000, 106.80410000, 1),
(2, 2, 'Kantor', 'Budi Santoso', '081298765432', 'Gedung Menara Sudirman Lt. 12, Jl. Jend. Sudirman Kav 60, Jakarta Pusat', -6.21980000, 106.80900000, 0);

-- 7. Settings
INSERT INTO `settings` (`id`, `key_name`, `key_value`, `description`) VALUES
(1, 'app_name', 'EFOOD', 'Nama Brand Aplikasi'),
(2, 'app_tagline', 'Super App Pesan Makanan Cepat & Nikmat', 'Tagline aplikasi'),
(3, 'base_delivery_fee', '9000', 'Tarif dasar pengiriman (1 km pertama) dalam Rupiah'),
(4, 'per_km_fee', '2500', 'Tarif per kilometer berikutnya dalam Rupiah'),
(5, 'admin_whatsapp', '6281234567890', 'Nomor WhatsApp resmi admin/layanan bantuan'),
(6, 'enable_cod', '1', 'Aktifkan metode pembayaran Bayar di Tempat / Tunai (COD)'),
(7, 'enable_transfer', '1', 'Aktifkan metode pembayaran Transfer Bank Manual'),
(8, 'bank_transfer_info', 'BCA: 8830129831 a.n PT EFOOD INDONESIA BERSAMA', 'Informasi rekening pembayaran transfer manual');

-- 8. Sample Initial Order (untuk contoh workflow pesanan siap diuji)
INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `restaurant_id`, `driver_id`, `status`, `payment_method`, `payment_status`, `subtotal`, `delivery_fee`, `discount_amount`, `grand_total`, `customer_name`, `customer_phone`, `delivery_address`, `delivery_latitude`, `delivery_longitude`, `distance_km`, `customer_note`, `created_at`) VALUES
(1, 'EF-20261018-0001', 2, 1, NULL, 'pending_verification', 'cod', 'unpaid', 56000.00, 11500.00, 0.00, 67500.00, 'Budi Santoso', '081298765432', 'Jl. Wijaya Timur No. 17, Kebayoran Baru, Jakarta Selatan', -6.24230000, 106.80410000, 2.0, 'Sambalnya pedas sedang saja ya Mas, jangan lupa sendok garpu.', NOW());

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `menu_name_snapshot`, `price_snapshot`, `quantity`, `subtotal`, `notes`) VALUES
(1, 1, 1, 'Paket Geprek Sambal Bawang + Nasi', 22000.00, 2, 44000.00, 'Level 3 tidak pakai lalapan timun'),
(2, 1, 3, 'Es Teh Manis Jumbo Segar', 6000.00, 2, 12000.00, 'Gula sedikit saja');

INSERT INTO `order_status_history` (`id`, `order_id`, `old_status`, `new_status`, `changed_by`, `note`) VALUES
(1, 1, NULL, 'pending_verification', 'Sistem Checkout', 'Pesanan berhasil dibuat oleh pelanggan dan menunggu verifikasi admin.');
