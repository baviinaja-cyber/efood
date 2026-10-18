<?php

namespace App\Helpers;

class Format
{
    public static function rupiah(float|int|string $amount): string
    {
        $num = (float) $amount;
        return 'Rp ' . number_format($num, 0, ',', '.');
    }

    public static function datetime(?string $datetime): string
    {
        if (empty($datetime)) {
            return '-';
        }

        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return $datetime;
        }

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $day = date('d', $timestamp);
        $monthNum = (int) date('m', $timestamp);
        $month = $months[$monthNum] ?? date('M', $timestamp);
        $year = date('Y', $timestamp);
        $time = date('H:i', $timestamp);

        return "{$day} {$month} {$year}, {$time} WIB";
    }

    public static function timeAgo(?string $datetime): string
    {
        if (empty($datetime)) {
            return '-';
        }

        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return "{$mins} mnt lalu";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "{$hours} jam lalu";
        } else {
            return self::datetime($datetime);
        }
    }

    public static function statusLabel(string $status): string
    {
        $labels = [
            'pending_verification' => 'Menunggu Verifikasi Admin',
            'verified' => 'Terverifikasi Admin',
            'restaurant_processing' => 'Diproses Restoran',
            'ready_for_delivery' => 'Makanan Siap Diantar',
            'assigned_to_driver' => 'Driver Ditugaskan',
            'picked_up' => 'Pesanan Diambil Driver',
            'on_delivery' => 'Sedang Diantar ke Tujuan',
            'delivered' => 'Pesanan Selesai / Terkirim',
            'rejected' => 'Pesanan Ditolak',
            'cancelled' => 'Pesanan Dibatalkan',
            'delivery_failed' => 'Pengantaran Gagal'
        ];

        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function statusBadgeClass(string $status): string
    {
        $classes = [
            'pending_verification' => 'bg-warning text-dark',
            'verified' => 'bg-info text-dark',
            'restaurant_processing' => 'bg-primary text-white',
            'ready_for_delivery' => 'bg-purple text-white',
            'assigned_to_driver' => 'bg-secondary text-white',
            'picked_up' => 'bg-info text-white',
            'on_delivery' => 'bg-warning text-dark',
            'delivered' => 'bg-success text-white',
            'rejected' => 'bg-danger text-white',
            'cancelled' => 'bg-dark text-white',
            'delivery_failed' => 'bg-danger text-white'
        ];

        return $classes[$status] ?? 'bg-secondary text-white';
    }

    public static function statusStepIndex(string $status): int
    {
        $steps = [
            'pending_verification' => 1,
            'verified' => 2,
            'restaurant_processing' => 3,
            'ready_for_delivery' => 4,
            'assigned_to_driver' => 5,
            'picked_up' => 6,
            'on_delivery' => 7,
            'delivered' => 8,
            'rejected' => 0,
            'cancelled' => 0,
            'delivery_failed' => 0
        ];

        return $steps[$status] ?? 1;
    }
}
