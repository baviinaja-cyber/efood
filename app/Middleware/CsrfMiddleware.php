<?php

namespace App\Middleware;

use App\Helpers\Security;

class CsrfMiddleware
{
    public static function handle(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!Security::validateCsrfToken($token)) {
                http_response_code(403);
                header('Content-Type: text/html; charset=utf-8');
                echo '<!DOCTYPE html><html><head><title>403 Forbidden</title><meta name="viewport" content="width=device-width, initial-scale=1"></head><body style="font-family:sans-serif;text-align:center;padding:50px;"><h2>Sesi Form Telah Kedaluwarsa (403 CSRF)</h2><p>Silakan muat ulang halaman dan coba kembali.</p><a href="javascript:history.back()" style="display:inline-block;padding:10px 20px;background:#FF5722;color:#fff;text-decoration:none;border-radius:8px;">Kembali</a></body></html>';
                exit;
            }
        }
        return true;
    }
}
