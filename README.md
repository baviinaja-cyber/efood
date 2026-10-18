# EFOOD - Food Delivery Super App (PHP Native 8.2+ & MySQL)

Aplikasi web pemesanan makanan multi-restoran modern bergaya **Super App** dibangun menggunakan **PHP Native 8.2+**, **MySQL 8+ / MariaDB**, PDO Prepared Statements, Clean URL (Apache `.htaccess`), Geolocation GPS & Leaflet OpenStreetMap, PWA (Progressive Web App), dan sistem dispatch pesanan WhatsApp manual.

Aplikasi ini dirancang siap pakai (*production-ready*) untuk di-upload langsung ke hosting cPanel/VPS atau dijalankan di XAMPP lokal.

---

## 🚀 Fitur Utama

1. **Desain Super App Modern & Mobile-First**
   - Palette warna oranye cerah (`#FF5722` & `#FF3D00`) dengan aksen bersih.
   - Header dinamis: Logo brand, selector lokasi pengiriman, tombol pendeteksi GPS, kolom pencarian, dan indikator keranjang belanja.
   - Banner promo diskon dan slider kategori kuliner.
   - Kartu restoran modern: Rating bintang, estimasi waktu masak, status Buka/Tutup real-time, dan biaya ongkir.
   - Bottom navigation untuk kenyamanan akses satu tangan di smartphone.

2. **Sistem Multi-Restoran & Validasi Keranjang Ketat**
   - Mendukung banyak restoran mitra dengan menu, harga, stok, jam operasional, dan koordinat GPS masing-masing.
   - **Aturan Keranjang Tunggal**: Satu transaksi pemesanan hanya boleh berasal dari satu restoran. Jika pelanggan memilih menu dari restoran lain, sistem menampilkan dialog konfirmasi untuk mengosongkan keranjang sebelumnya.
   - Validasi ketersediaan menu, stok, dan total harga di sisi server.

3. **GPS Geolocation & Peta Leaflet OpenStreetMap**
   - Menggunakan Browser Geolocation API (`enableHighAccuracy: true`) untuk mendeteksi koordinat lintang/bujur pelanggan.
   - Peta interaktif Leaflet OpenStreetMap dengan pin yang dapat digeser (*draggable marker*).
   - Perhitungan jarak otomatis menggunakan rumus **Haversine** (`DistanceService.php`).
   - Kalkulasi ongkir otomatis berbasis jarak kilometer (tarif dasar + tarif per km) via AJAX tanpa me-reload halaman.
   - Link langsung navigasi Google Maps untuk rute penjemputan dan pengantaran kurir.

4. **Workflow Pesanan 5 Tahap (Tahap A — E) & WhatsApp Dispatch**
   - **Tahap A (Pesanan Baru)**: Status awal `pending_verification`. Muncul di dashboard admin.
   - **Tahap B (Verifikasi Admin)**: Admin menekan *Verifikasi Pesanan*. Tombol WhatsApp Restoran terbuka dengan format pesan rapi:
     ```text
     Halo, *Ayam Geprek Juara*.
     Ada pesanan baru dari EFOOD.
     Nomor pesanan: EF-20261018-XXXX
     Nama pelanggan: ...
     Detail pesanan: ...
     Total: ...
     Alamat pengiriman: ...
     Koordinat GPS: ...
     Mohon konfirmasi penerimaan pesanan dan estimasi waktu persiapan melalui WhatsApp.
     ```
     Setelah admin mengirim pesan manual di WhatsApp, admin menekan tombol *Catat Pesanan Diteruskan* dan status menjadi `restaurant_processing`.
   - **Tahap C (Restoran Mengonfirmasi)**: Restoran membalas chat WhatsApp secara manual. Admin mencatat hasil konfirmasi (*Diterima & Sedang Dimasak* atau *Siap Diantar*).
   - **Tahap D (Siap Diantar & Penugasan Driver)**: Status `ready_for_delivery`. Admin memilih driver dari daftar aktif lalu menekan *Teruskan ke Driver via WhatsApp*. Link Google Maps penjemputan restoran dan pengantaran pelanggan otomatis terisi. Admin mencatat penugasan dan status menjadi `assigned_to_driver`.
   - **Tahap E (Pengantaran)**: Admin memperbarui tahapan driver (`picked_up` -> `on_delivery` -> `delivered` / `delivery_failed`).
   - Audit trail lengkap di tabel `order_status_history` dan `whatsapp_message_logs`.
   - Pelanggan melihat timeline pembaruan secara real-time via AJAX polling setiap 6 detik.

5. **PWA (Progressive Web App)**
   - Mendukung instalasi aplikasi di layar utama smartphone / desktop.
   - File `manifest.webmanifest` & `service-worker.js`.
   - Cache aset statis yang aman (halaman sensitif seperti admin, checkout, dan akun tidak di-cache demi keamanan).

6. **Keamanan Standar Industri (Security Hardened)**
   - Menggunakan `PDO` dengan prepared statements untuk mencegah SQL Injection.
   - Enkripsi password menggunakan `password_hash()` (algoritma BCRYPT).
   - Proteksi CSRF Token pada seluruh request POST form.
   - Output escaping (`htmlspecialchars`) untuk mencegah XSS.
   - Regenerasi ID sesi setelah login untuk mencegah Session Fixation.
   - Cookie sesi `HttpOnly`, `SameSite=Lax`.
   - Clean URL front controller dengan sanitasi URI.

---

## 📁 Struktur Direktori Aplikasi

```text
efood/
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── RestaurantController.php
│   │   ├── CartController.php
│   │   ├── OrderController.php
│   │   ├── AuthController.php
│   │   ├── ProfileController.php
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── OrderAdminController.php
│   │       ├── RestaurantAdminController.php
│   │       ├── MenuAdminController.php
│   │       ├── DriverAdminController.php
│   │       ├── CustomerAdminController.php
│   │       └── SettingAdminController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Restaurant.php
│   │   ├── MenuItem.php
│   │   ├── Category.php
│   │   ├── Order.php
│   │   ├── Driver.php
│   │   └── Setting.php
│   ├── Services/
│   │   ├── WhatsAppService.php
│   │   ├── DistanceService.php
│   │   └── OrderWorkflowService.php
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── AdminMiddleware.php
│   │   └── CsrfMiddleware.php
│   └── Helpers/
│       ├── Database.php
│       ├── Env.php
│       ├── Format.php
│       ├── Security.php
│       ├── Session.php
│       └── View.php
├── config/
│   ├── app.php
│   ├── database.php
│   └── pwa.php
├── database/
│   └── database.sql
├── partials/
│   ├── frontend/
│   │   ├── head.php
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── bottom-nav.php
│   │   └── flash.php
│   └── admin/
│       ├── head.php
│       ├── sidebar.php
│       ├── topbar.php
│       ├── footer.php
│       └── flash.php
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── app.css
│   │   │   └── admin.css
│   │   └── js/
│   │       ├── app.js
│   │       ├── geolocation.js
│   │       └── order-tracker.js
│   ├── icons/
│   │   ├── icon.svg
│   │   ├── icon-192.png
│   │   └── icon-512.png
│   ├── uploads/
│   ├── index.php
│   ├── manifest.webmanifest
│   ├── service-worker.js
│   └── .htaccess
├── resources/
│   └── views/
│       ├── frontend/
│       │   ├── home.php
│       │   ├── restaurants.php
│       │   ├── restaurant-detail.php
│       │   ├── cart.php
│       │   ├── checkout.php
│       │   ├── orders.php
│       │   ├── order-tracking.php
│       │   ├── login.php
│       │   ├── register.php
│       │   └── profile.php
│       ├── admin/
│       │   ├── login.php
│       │   ├── dashboard.php
│       │   ├── orders/
│       │   │   ├── index.php
│       │   │   └── detail.php
│       │   ├── restaurants/
│       │   │   ├── index.php
│       │   │   ├── create.php
│       │   │   └── edit.php
│       │   ├── menu/
│       │   │   ├── index.php
│       │   │   ├── create.php
│       │   │   └── edit.php
│       │   ├── drivers/
│       │   │   └── index.php
│       │   ├── customers/
│       │   │   └── index.php
│       │   └── settings/
│       │       └── index.php
│       └── errors/
│           └── 404.php
├── routes/
│   └── web.php
├── storage/
│   └── logs/
├── .env.example
├── .htaccess
└── README.md
```

---

## 💻 Panduan Instalasi di XAMPP

1. **Salin Folder Proyek**
   - Ekstrak atau salin folder `efood` ke dalam direktori `htdocs` XAMPP:
     `C:\xampp\htdocs\efood`

2. **Buat Database MySQL**
   - Buka browser dan akses `http://localhost/phpmyadmin/`.
   - Buat database baru bernama `efood` dengan collation `utf8mb4_unicode_ci`.
   - Masuk ke tab **Import**, pilih file `database/database.sql`, lalu klik **Import**.

3. **Konfigurasi File `.env`**
   - Salin file `.env.example` menjadi `.env` di dalam folder `efood/`:
     ```bash
     cp .env.example .env
     ```
   - Sesuaikan konfigurasi koneksi:
     ```ini
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=efood
     DB_USERNAME=root
     DB_PASSWORD=
     ```

4. **Akses Aplikasi**
   - Buka browser dan buka:
     - Halaman Pelanggan: `http://localhost/efood/public/`
     - Panel Admin: `http://localhost/efood/public/admin/login`

---

## 🌐 Panduan Upload ke Hosting cPanel

1. **Upload File**
   - Kompres seluruh isi folder `efood` menjadi file `.zip`.
   - Buka **File Manager** cPanel.
   - Upload file zip ke root akun hosting Anda (sejajar dengan `public_html`).
   - Ekstrak file zip sehingga struktur folder `app/`, `config/`, `database/`, dll berada di luar `public_html`.
   - Pindahkan seluruh isi folder `public/*` ke dalam `public_html/`.
   - Edit baris definisi path di `public_html/index.php` jika diperlukan:
     ```php
     define('EFOOD_ROOT', dirname(__DIR__) . '/efood');
     ```

2. **Import Database**
   - Buka menu **MySQL Databases** di cPanel, buat database dan user baru, lalu beri izin *ALL PRIVILEGES*.
   - Buka **phpMyAdmin**, pilih database yang baru dibuat, dan import file `database.sql`.

3. **Sesuaikan `.env`**
   - Buat file `.env` di folder `efood/` dengan nama database, user, dan password MySQL dari hosting Anda.

---

## 🔑 Akun Default untuk Pengujian

| Peran | Email | Kata Sandi | Halaman Login |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@efood.com` | `admin123` | `/admin/login` |
| **Pelanggan Demo** | `budi@gmail.com` | `budi123` | `/login` |

---

## 📋 Checklist Pengujian Fitur Inti

- [x] **Pilih Restoran & Menu**: Tambahkan menu dari satu restoran ke keranjang.
- [x] **Konfirmasi Multi-Restoran**: Coba tambahkan menu dari restoran lain, pastikan muncul peringatan pergantian restoran.
- [x] **Deteksi GPS & Peta Leaflet**: Buka halaman checkout, klik tombol *Gunakan Lokasi Saya*, dan geser pin di peta untuk melihat perhitungan ulang ongkir secara real-time.
- [x] **Checkout Pesanan**: Isi nama dan nomor WhatsApp, pilih COD, dan submit pesanan.
- [x] **Tahap A - Verifikasi**: Masuk ke panel admin, buka detail pesanan, lalu klik *Verifikasi Pesanan*.
- [x] **Tahap B - WhatsApp Restoran**: Klik *Teruskan ke Restoran via WhatsApp*, periksa teks yang terisi otomatis di wa.me, lalu klik *Catat Pesanan Diteruskan*.
- [x] **Tahap C - Konfirmasi Restoran**: Simpan catatan balasan restoran dan klik *Konfirmasi Makanan Siap Diantar*.
- [x] **Tahap D - WhatsApp Driver**: Pilih driver aktif, periksa link Google Maps penjemputan dan pengantaran, lalu klik *Catat Tugas Ditugaskan ke Driver*.
- [x] **Tahap E - Pengantaran**: Perbarui status menjadi *Picked Up*, *On Delivery*, dan *Delivered*.
- [x] **Live Timeline Pelanggan**: Pantau halaman `/pesanan/{nomor_pesanan}` milik pelanggan, pastikan status ter-update secara otomatis tanpa reload halaman.
