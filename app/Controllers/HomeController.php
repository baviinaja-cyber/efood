<?php

namespace App\Controllers;

use App\Helpers\View;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;

class HomeController
{
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $restaurants = Restaurant::allActive($search);
        $categories = Category::all();

        // Rekomendasi menu
        $featuredMenus = [];
        if (!empty($restaurants)) {
            $firstRes = $restaurants[0];
            $featuredMenus = MenuItem::getByRestaurant($firstRes['id']);
        }

        View::render('frontend.home', [
            'restaurants' => $restaurants,
            'categories' => $categories,
            'featuredMenus' => array_slice($featuredMenus, 0, 6),
            'search' => $search,
            'pageTitle' => 'EFOOD - Pesan Makanan Lezat Cepat & Murah'
        ]);
    }
}
