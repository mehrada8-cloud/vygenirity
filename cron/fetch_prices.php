<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$config = $config ?? require __DIR__ . '/../config/app.php';
$pricesPath = $config['storage']['prices_path'];

$prices = [];
foreach ($config['price_sources'] as $source) {
    $url = $source['url'] ?? '';
    if ($url === '') {
        continue;
    }

    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        continue;
    }

    $payload = json_decode($response, true);
    if (!is_array($payload)) {
        continue;
    }

    foreach ($payload as $row) {
        $symbolKey = $source['symbol_key'] ?? 'symbol';
        $priceKey = $source['price_key'] ?? 'price';
        $symbol = $row[$symbolKey] ?? null;
        $price = $row[$priceKey] ?? null;
        if (!$symbol || !is_numeric($price)) {
            continue;
        }
        $prices[strtoupper($symbol)] = (float)$price;
    }
}

file_put_contents($pricesPath, json_encode($prices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
