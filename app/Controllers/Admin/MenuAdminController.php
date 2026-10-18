<?php

namespace App\Controllers\Admin;

use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\View;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;

class MenuAdminController
{
    public function index(): void
    {
        $menus = MenuItem::allAdmin();
        View::render('admin.menu.index', [
            'menus' => $menus,
            'pageTitle' => 'Kelola Menu Makanan & Minuman - Admin EFOOD'
        ]);
    }

    public function create(): void
    {
        $restaurants = Restaurant::allActive();
        $categories = Category::all();

        View::render('admin.menu.create', [
            'restaurants' => $restaurants,
            'categories' => $categories,
            'pageTitle' => 'Tambah Menu Baru - Admin EFOOD'
        ]);
    }

    public function store(): void
    {
        $resId = (int) ($_POST['restaurant_id'] ?? 0);
        $catId = (int) ($_POST['category_id'] ?? 0);
        $name = Security::sanitizeString($_POST['name'] ?? '');
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $name))) . '-' . rand(100, 999);
        $description = Security::sanitizeString($_POST['description'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $image = Security::sanitizeString($_POST['image'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&auto=format&fit=crop&q=80');
        $stock = (int) ($_POST['stock'] ?? 100);
        $status = $_POST['status'] ?? 'available';

        if (empty($name) || $resId <= 0 || $price <= 0) {
            Session::flash('danger', 'Nama menu, restoran mitra, dan harga wajib diisi.');
            header('Location: ' . View::url('admin/menu/tambah'));
            exit;
        }

        MenuItem::create([
            'restaurant_id' => $resId,
            'category_id' => $catId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'price' => $price,
            'image' => $image,
            'stock' => $stock,
            'status' => $status
        ]);

        Session::flash('success', "Menu {$name} berhasil ditambahkan!");
        header('Location: ' . View::url('admin/menu'));
        exit;
    }

    public function edit(int $id): void
    {
        $menu = MenuItem::findById($id);
        if (!$menu) {
            Session::flash('danger', 'Menu tidak ditemukan.');
            header('Location: ' . View::url('admin/menu'));
            exit;
        }

        $restaurants = Restaurant::allActive();
        $categories = Category::all();

        View::render('admin.menu.edit', [
            'menu' => $menu,
            'restaurants' => $restaurants,
            'categories' => $categories,
            'pageTitle' => "Edit Menu {$menu['name']} - Admin EFOOD"
        ]);
    }

    public function update(int $id): void
    {
        $menu = MenuItem::findById($id);
        if (!$menu) {
            Session::flash('danger', 'Menu tidak ditemukan.');
            header('Location: ' . View::url('admin/menu'));
            exit;
        }

        $resId = (int) ($_POST['restaurant_id'] ?? $menu['restaurant_id']);
        $catId = (int) ($_POST['category_id'] ?? $menu['category_id']);
        $name = Security::sanitizeString($_POST['name'] ?? $menu['name']);
        $description = Security::sanitizeString($_POST['description'] ?? '');
        $price = (float) ($_POST['price'] ?? $menu['price']);
        $image = Security::sanitizeString($_POST['image'] ?? $menu['image']);
        $stock = (int) ($_POST['stock'] ?? $menu['stock']);
        $status = in_array($_POST['status'] ?? 'available', ['available', 'sold_out', 'inactive']) ? $_POST['status'] : 'available';

        MenuItem::update($id, [
            'restaurant_id' => $resId,
            'category_id' => $catId,
            'name' => $name,
            'slug' => $menu['slug'],
            'description' => $description,
            'price' => $price,
            'image' => $image,
            'stock' => $stock,
            'status' => $status
        ]);

        Session::flash('success', "Menu {$name} berhasil diperbarui.");
        header('Location: ' . View::url('admin/menu'));
        exit;
    }

    public function delete(int $id): void
    {
        MenuItem::delete($id);
        Session::flash('info', 'Menu berhasil dihapus.');
        header('Location: ' . View::url('admin/menu'));
        exit;
    }
}
