<?php

use App\Helpers\Env;

return [
    'name' => Env::get('APP_NAME', 'EFOOD'),
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => (bool) Env::get('APP_DEBUG', false),
    'url' => Env::get('APP_URL', 'http://localhost/efood/public'),
    'timezone' => Env::get('APP_TIMEZONE', 'Asia/Jakarta'),
    'csrf_secret' => Env::get('CSRF_SECRET', 'efood_csrf_secret_key_2026'),
];
