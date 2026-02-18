<?php

declare(strict_types=1);

return [
    'app_name' => 'Vygen Signals',
    'base_url' => 'https://vygen.vyva.ir',
    'timezone' => 'Asia/Tehran',
    'admin' => [
        'username' => 'admin',
        // Change this password after first deploy.
        'password' => 'change-me',
    ],
    'storage' => [
        'db_path' => __DIR__ . '/../data/app.db',
        'prices_path' => __DIR__ . '/../data/prices.json',
    ],
    // Configure price sources to populate the price cache.
    // Each source should return JSON with symbols and last prices.
    'price_sources' => [
        // Example:
        // [
        //     'name' => 'example',
        //     'url' => 'https://example.com/prices.json',
        //     'symbol_key' => 'symbol',
        //     'price_key' => 'price',
        // ],
    ],
];
