<?php

namespace App\Services;

use App\Helpers\Database;
use PDO;

class DistanceService
{
    /**
     * Hitung jarak Haversine dalam kilometer antara dua titik GPS
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // radius bumi dalam km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * Hitung ongkir berdasarkan jarak km dan konfigurasi tarif
     */
    public static function calculateDeliveryFee(float $distanceKm): float
    {
        $db = Database::getConnection();
        
        $stmt = $db->query("SELECT key_name, key_value FROM settings WHERE key_name IN ('base_delivery_fee', 'per_km_fee')");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $baseFee = (float) ($settings['base_delivery_fee'] ?? 9000);
        $perKmFee = (float) ($settings['per_km_fee'] ?? 2500);

        if ($distanceKm <= 1.0) {
            return $baseFee;
        }

        $extraKm = $distanceKm - 1.0;
        $totalFee = $baseFee + ($extraKm * $perKmFee);

        // Bulatkan ke kelipatan 500 terdekat
        return ceil($totalFee / 500) * 500;
    }

    /**
     * Generate link Google Maps untuk navigasi driver
     */
    public static function getGoogleMapsUrl(float $latitude, float $longitude): string
    {
        return "https://www.google.com/maps/dir/?api=1&destination={$latitude},{$longitude}";
    }
}
