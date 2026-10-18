<?php

namespace App\Controllers;

use App\Helpers\Format;
use App\Helpers\View;
use App\Models\MenuItem;
use App\Models\Restaurant;

class RestaurantController
{
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $restaurants = Restaurant::allActive($search);

        View::render('frontend.restaurants', [
            'restaurants' => $restaurants,
            'search' => $search,
            'pageTitle' => 'Daftar Restoran Mitra EFOOD'
        ]);
    }

    public function show(string $slug): void
    {
        $restaurant = Restaurant::findBySlug($slug);
        if (!$restaurant) {
            http_response_code(404);
            View::render('errors.404', ['message' => 'Restoran tidak ditemukan.']);
            exit;
        }

        $menus = MenuItem::getByRestaurant($restaurant['id']);

        // Kelompokkan menu berdasarkan kategori
        $groupedMenus = [];
        foreach ($menus as $m) {
            $cat = $m['category_name'] ?? 'Menu Lainnya';
            $groupedMenus[$cat][] = $m;
        }

        // Cek status buka/tutup berdasarkan jam operasional
        $currentTime = date('H:i:s');
        $isOpen = ($currentTime >= $restaurant['opening_time'] && $currentTime <= $restaurant['closing_time']);

        View::render('frontend.restaurant-detail', [
            'restaurant' => $restaurant,
            'groupedMenus' => $groupedMenus,
            'isOpen' => $isOpen,
            'pageTitle' => "{$restaurant['name']} - Menu & Pemesanan EFOOD"
        ]);
    }
}
