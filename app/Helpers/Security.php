<?php

namespace App\Helpers;

class Security
{
    public static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function escape(mixed $data): string
    {
        if ($data === null) {
            return '';
        }
        return htmlspecialchars((string) $data, ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeString(?string $str): string
    {
        if ($str === null) {
            return '';
        }
        return trim(strip_tags($str));
    }

    public static function sanitizePhone(string $phone): string
    {
        // Bersihkan karakter non-numerik
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali 08, ubah jadi 628
        if (str_starts_with($cleaned, '08')) {
            $cleaned = '628' . substr($cleaned, 2);
        }

        return $cleaned;
    }
}
