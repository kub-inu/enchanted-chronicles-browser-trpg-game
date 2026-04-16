<?php return [
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
    'title' => 'Enchanted Chronicles',
    'title_separator' => ' ~ ',
    'timezone' => "Europe/Prague",
];