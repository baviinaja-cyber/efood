<?php

namespace App\Controllers;

use App\Helpers\Format;
use App\Helpers\Session;
use App\Helpers\View;
use App\Models\MenuItem;
use App\Models\Restaurant;

class CartController
{
    public function index(): void
    {
        $cart = Session::get('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]);
        $restaurant = null;

        if (!empty($cart['restaurant_id'])) {
            $restaurant = Restaurant::findById($cart['restaurant_id']);
        }

        $subtotal = 0;
        foreach ($cart['items'] as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
        }

        View::render('frontend.cart', [
            'cart' => $cart,
            'restaurant' => $restaurant,
            'subtotal' => $subtotal,
            'pageTitle' => 'Keranjang Belanja - EFOOD'
        ]);
    }

    public function add(): void
    {
        $menuId = (int) ($_POST['menu_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $notes = trim($_POST['notes'] ?? '');
        $forceClear = isset($_POST['force_clear']) && $_POST['force_clear'] === '1';

        $menu = MenuItem::findById($menuId);
        if (!$menu) {
            Session::flash('danger', 'Menu makanan tidak ditemukan.');
            header('Location: ' . View::url());
            exit;
        }

        $cart = Session::get('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]);

        // Aturan: Satu transaksi hanya boleh berasal dari satu restoran!
        if (!empty($cart['restaurant_id']) && $cart['restaurant_id'] !== $menu['restaurant_id']) {
            if (!$forceClear) {
                // Tampilkan konfirmasi ganti restoran
                Session::flash('warning', "Keranjang Anda berisi menu dari restoran {$cart['restaurant_name']}. Menambahkan menu dari {$menu['restaurant_name']} akan mengosongkan keranjang sebelumnya.");
                Session::set('pending_cart_switch', [
                    'menu_id' => $menuId,
                    'quantity' => $quantity,
                    'notes' => $notes,
                    'target_restaurant_id' => $menu['restaurant_id'],
                    'target_restaurant_name' => $menu['restaurant_name']
                ]);
                header('Location: ' . View::url('keranjang'));
                exit;
            } else {
                // Reset keranjang jika di-force
                $cart = ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []];
            }
        }

        // Set info restoran
        $cart['restaurant_id'] = $menu['restaurant_id'];
        $cart['restaurant_name'] = $menu['restaurant_name'];

        if (!isset($cart['items'][$menuId])) {
            $cart['items'][$menuId] = [
                'id' => $menu['id'],
                'name' => $menu['name'],
                'price' => (float) $menu['price'],
                'image' => $menu['image'],
                'quantity' => $quantity,
                'notes' => $notes
            ];
        } else {
            $cart['items'][$menuId]['quantity'] += $quantity;
            if (!empty($notes)) {
                $cart['items'][$menuId]['notes'] = $notes;
            }
        }

        Session::set('cart', $cart);
        Session::remove('pending_cart_switch');
        Session::flash('success', "{$menu['name']} berhasil ditambahkan ke keranjang.");

        $redirect = $_POST['redirect_to'] ?? View::url('keranjang');
        header("Location: {$redirect}");
        exit;
    }

    public function update(): void
    {
        $menuId = (int) ($_POST['menu_id'] ?? 0);
        $action = $_POST['action'] ?? 'increase'; // increase, decrease, set
        $quantity = (int) ($_POST['quantity'] ?? 1);

        $cart = Session::get('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]);

        if (isset($cart['items'][$menuId])) {
            if ($action === 'increase') {
                $cart['items'][$menuId]['quantity']++;
            } elseif ($action === 'decrease') {
                $cart['items'][$menuId]['quantity']--;
                if ($cart['items'][$menuId]['quantity'] <= 0) {
                    unset($cart['items'][$menuId]);
                }
            } elseif ($action === 'set') {
                if ($quantity <= 0) {
                    unset($cart['items'][$menuId]);
                } else {
                    $cart['items'][$menuId]['quantity'] = $quantity;
                }
            }

            if (empty($cart['items'])) {
                $cart = ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []];
            }

            Session::set('cart', $cart);
        }

        header('Location: ' . View::url('keranjang'));
        exit;
    }

    public function remove(): void
    {
        $menuId = (int) ($_POST['menu_id'] ?? 0);
        $cart = Session::get('cart', ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []]);

        if (isset($cart['items'][$menuId])) {
            unset($cart['items'][$menuId]);
            if (empty($cart['items'])) {
                $cart = ['restaurant_id' => null, 'restaurant_name' => null, 'items' => []];
            }
            Session::set('cart', $cart);
            Session::flash('info', 'Menu dihapus dari keranjang.');
        }

        header('Location: ' . View::url('keranjang'));
        exit;
    }

    public function clear(): void
    {
        Session::remove('cart');
        Session::remove('pending_cart_switch');
        Session::flash('info', 'Keranjang belanja telah dikosongkan.');
        header('Location: ' . View::url('keranjang'));
        exit;
    }
}
