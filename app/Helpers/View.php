<?php

namespace App\Helpers;

class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data);
        
        $baseDir = dirname(__DIR__, 2);
        $file = $baseDir . '/resources/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            http_response_code(404);
            $notFoundFile = $baseDir . '/resources/views/errors/404.php';
            if (file_exists($notFoundFile)) {
                require $notFoundFile;
            } else {
                echo "Halaman view tidak ditemukan: " . htmlspecialchars($view);
            }
            exit;
        }

        require $file;
    }

    public static function partial(string $partial, array $data = []): void
    {
        extract($data);
        
        $baseDir = dirname(__DIR__, 2);
        $file = $baseDir . '/partials/' . str_replace('.', '/', $partial) . '.php';

        if (file_exists($file)) {
            require $file;
        } else {
            echo "<!-- Partial {$partial} tidak ditemukan -->";
        }
    }

    public static function url(string $path = ''): string
    {
        $path = ltrim($path, '/');
        // Mendeteksi base path jika dijalankan di subdirektori (misal /efood/public/)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = rtrim(dirname($scriptName), '/\\');
        
        if ($base === '/' || $base === '\\') {
            $base = '';
        }

        return $base . '/' . $path;
    }

    public static function asset(string $path): string
    {
        return self::url('assets/' . ltrim($path, '/'));
    }
}
