<?php

namespace App\Controllers\Admin;

use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\View;
use App\Models\Restaurant;

class RestaurantAdminController
{
    public function index(): void
    {
        $restaurants = Restaurant::allAdmin();
        View::render('admin.restaurants.index', [
            'restaurants' => $restaurants,
            'pageTitle' => 'Kelola Mitra Restoran - Admin EFOOD'
        ]);
    }

    public function create(): void
    {
        View::render('admin.restaurants.create', [
            'pageTitle' => 'Tambah Restoran Baru - Admin EFOOD'
        ]);
    }

    public function store(): void
    {
        $name = Security::sanitizeString($_POST['name'] ?? '');
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $name))) . '-' . rand(100, 999);
        $description = Security::sanitizeString($_POST['description'] ?? '');
        $address = Security::sanitizeString($_POST['address'] ?? '');
        $whatsapp = Security::sanitizePhone($_POST['whatsapp'] ?? '');
        $lat = (float) ($_POST['latitude'] ?? -6.20000000);
        $lng = (float) ($_POST['longitude'] ?? 106.81666600);
        $opening = $_POST['opening_time'] ?? '08:00';
        $closing = $_POST['closing_time'] ?? '22:00';
        $minOrder = (float) ($_POST['minimum_order'] ?? 10000);
        $prepTime = (int) ($_POST['preparation_time'] ?? 20);
        $cover = Security::sanitizeString($_POST['cover_image'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&auto=format&fit=crop&q=80');

        if (empty($name) || empty($address) || empty($whatsapp)) {
            Session::flash('danger', 'Nama restoran, alamat, dan nomor WhatsApp wajib diisi.');
            header('Location: ' . View::url('admin/restoran/tambah'));
            exit;
        }

        Restaurant::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'address' => $address,
            'latitude' => $lat,
            'longitude' => $lng,
            'whatsapp' => $whatsapp,
            'opening_time' => $opening,
            'closing_time' => $closing,
            'minimum_order' => $minOrder,
            'preparation_time' => $prepTime,
            'cover_image' => $cover,
            'status' => 'active'
        ]);

        Session::flash('success', "Restoran {$name} berhasil ditambahkan!");
        header('Location: ' . View::url('admin/restoran'));
        exit;
    }

    public function edit(int $id): void
    {
        $restaurant = Restaurant::findById($id);
        if (!$restaurant) {
            Session::flash('danger', 'Restoran tidak ditemukan.');
            header('Location: ' . View::url('admin/restoran'));
            exit;
        }

        View::render('admin.restaurants.edit', [
            'restaurant' => $restaurant,
            'pageTitle' => "Edit Restoran {$restaurant['name']} - Admin EFOOD"
        ]);
    }

    public function update(int $id): void
    {
        $restaurant = Restaurant::findById($id);
        if (!$restaurant) {
            Session::flash('danger', 'Restoran tidak ditemukan.');
            header('Location: ' . View::url('admin/restoran'));
            exit;
        }

        $name = Security::sanitizeString($_POST['name'] ?? $restaurant['name']);
        $description = Security::sanitizeString($_POST['description'] ?? '');
        $address = Security::sanitizeString($_POST['address'] ?? $restaurant['address']);
        $whatsapp = Security::sanitizePhone($_POST['whatsapp'] ?? $restaurant['whatsapp']);
        $lat = (float) ($_POST['latitude'] ?? $restaurant['latitude']);
        $lng = (float) ($_POST['longitude'] ?? $restaurant['longitude']);
        $opening = $_POST['opening_time'] ?? $restaurant['opening_time'];
        $closing = $_POST['closing_time'] ?? $restaurant['closing_time'];
        $minOrder = (float) ($_POST['minimum_order'] ?? $restaurant['minimum_order']);
        $prepTime = (int) ($_POST['preparation_time'] ?? $restaurant['preparation_time']);
        $cover = Security::sanitizeString($_POST['cover_image'] ?? $restaurant['cover_image']);
        $status = in_array($_POST['status'] ?? 'active', ['active', 'inactive']) ? $_POST['status'] : 'active';

        Restaurant::update($id, [
            'name' => $name,
            'slug' => $restaurant['slug'],
            'description' => $description,
            'address' => $address,
            'latitude' => $lat,
            'longitude' => $lng,
            'whatsapp' => $whatsapp,
            'opening_time' => $opening,
            'closing_time' => $closing,
            'minimum_order' => $minOrder,
            'preparation_time' => $prepTime,
            'cover_image' => $cover,
            'status' => $status
        ]);

        Session::flash('success', "Restoran {$name} berhasil diperbarui.");
        header('Location: ' . View::url('admin/restoran'));
        exit;
    }
}
