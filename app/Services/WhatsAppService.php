<?php

namespace App\Services;

use App\Helpers\Database;
use App\Helpers\Format;
use App\Helpers\Security;
use PDO;

class WhatsAppService
{
    /**
     * Format nomor HP Indonesia ke format internasional (misal 6281234567890)
     */
    public static function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+62')) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Bangun link wa.me dengan URL encoded text
     */
    public static function buildLink(string $phone, string $message): string
    {
        $cleanPhone = self::formatPhone($phone);
        $encodedText = rawurlencode($message);
        return "https://wa.me/{$cleanPhone}?text={$encodedText}";
    }

    /**
     * Format pesan WhatsApp untuk Restoran
     */
    public static function generateRestaurantMessage(array $order, array $items, array $restaurant): string
    {
        $restaurantName = $restaurant['name'] ?? 'Restoran';
        $orderNumber = $order['order_number'];
        $customerName = $order['customer_name'];
        $grandTotal = Format::rupiah($order['grand_total']);
        $address = $order['delivery_address'];
        $lat = $order['delivery_latitude'];
        $lng = $order['delivery_longitude'];
        $note = !empty($order['customer_note']) ? "\n*Catatan Khusus:* " . $order['customer_note'] : '';

        $menuList = "";
        foreach ($items as $index => $item) {
            $no = $index + 1;
            $qty = $item['quantity'];
            $name = $item['menu_name_snapshot'];
            $price = Format::rupiah($item['subtotal']);
            $itemNote = !empty($item['notes']) ? " (Catatan: {$item['notes']})" : "";
            $menuList .= "{$no}. *{$name}* x{$qty} = {$price}{$itemNote}\n";
        }

        return "Halo, *{$restaurantName}*.\n\n" .
               "Ada pesanan baru dari EFOOD!\n\n" .
               "• *Nomor Pesanan:* {$orderNumber}\n" .
               "• *Nama Pelanggan:* {$customerName}\n\n" .
               "*Detail Pesanan:*\n" .
               $menuList .
               $note . "\n\n" .
               "• *Total Pesanan:* {$grandTotal}\n" .
               "• *Alamat Pengiriman:* {$address}\n" .
               "• *Koordinat GPS:* {$lat}, {$lng}\n\n" .
               "Mohon konfirmasi penerimaan pesanan dan estimasi waktu persiapan melalui WhatsApp. Terima kasih!";
    }

    /**
     * Format pesan WhatsApp untuk Driver
     */
    public static function generateDriverMessage(array $order, array $items, array $restaurant, array $driver): string
    {
        $driverName = $driver['name'] ?? 'Driver EFOOD';
        $orderNumber = $order['order_number'];
        $restaurantName = $restaurant['name'];
        $restaurantAddress = $restaurant['address'];
        $restaurantMaps = "https://www.google.com/maps/dir/?api=1&destination=" . $restaurant['latitude'] . "," . $restaurant['longitude'];

        $customerName = $order['customer_name'];
        $customerPhone = $order['customer_phone'];
        $deliveryAddress = $order['delivery_address'];
        $customerMaps = "https://www.google.com/maps/dir/?api=1&destination=" . $order['delivery_latitude'] . "," . $order['delivery_longitude'];

        $menuSummary = [];
        foreach ($items as $item) {
            $menuSummary[] = "{$item['menu_name_snapshot']} (x{$item['quantity']})";
        }
        $summaryText = implode(', ', $menuSummary);

        return "Halo, *{$driverName}*.\n\n" .
               "Ada tugas pengantaran EFOOD!\n\n" .
               "• *Nomor Pesanan:* {$orderNumber}\n\n" .
               "*Lokasi Penjemputan Restoran:*\n" .
               "• *Restoran:* {$restaurantName}\n" .
               "• *Alamat Restoran:* {$restaurantAddress}\n" .
               "• *Google Maps Restoran:* {$restaurantMaps}\n\n" .
               "*Lokasi Pengantaran Pelanggan:*\n" .
               "• *Nama Pelanggan:* {$customerName}\n" .
               "• *Nomor Pelanggan:* {$customerPhone}\n" .
               "• *Alamat Pengiriman:* {$deliveryAddress}\n" .
               "• *Google Maps Tujuan:* {$customerMaps}\n\n" .
               "• *Ringkasan Pesanan:* {$summaryText}\n" .
               "• *Metode Bayar:* " . strtoupper($order['payment_method']) . " (" . Format::rupiah($order['grand_total']) . ")\n\n" .
               "Mohon konfirmasi penerimaan tugas pengantaran melalui WhatsApp. Hati-hati di jalan!";
    }

    /**
     * Catat log pengiriman WhatsApp manual ke database
     */
    public static function logManualDispatch(
        int $orderId,
        string $recipientType,
        string $phone,
        string $messageType,
        string $createdBy,
        string $notes = ''
    ): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO whatsapp_message_logs (
                order_id, recipient_type, recipient_phone, message_type, prepared_at, opened_at, created_by, notes
            ) VALUES (
                :order_id, :recipient_type, :recipient_phone, :message_type, :prepared_at, :opened_at, :created_by, :notes
            )
        ");

        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            ':order_id' => $orderId,
            ':recipient_type' => $recipientType,
            ':recipient_phone' => self::formatPhone($phone),
            ':message_type' => $messageType,
            ':prepared_at' => $now,
            ':opened_at' => $now,
            ':created_by' => $createdBy,
            ':notes' => $notes,
        ]);

        return (int) $db->lastInsertId();
    }
}
