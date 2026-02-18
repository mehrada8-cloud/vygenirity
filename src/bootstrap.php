<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config/app.php';

if (!is_dir(__DIR__ . '/../data')) {
    mkdir(__DIR__ . '/../data', 0775, true);
}

date_default_timezone_set($config['timezone']);

function db(array $config): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbPath = $config['storage']['db_path'];
    $needsInit = !file_exists($dbPath);

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($needsInit) {
        init_db($pdo);
    }
    ensure_signal_columns($pdo);
    ensure_category_columns($pdo);

    return $pdo;
}

function init_db(PDO $pdo): void
{
    $schema = <<<SQL
    CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        target_percent REAL NULL,
        guarantee_percent REAL NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        deleted_at TEXT NULL,
        closed_at TEXT NULL
    );

    CREATE TABLE IF NOT EXISTS signals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        pair TEXT NOT NULL,
        position_type TEXT NOT NULL,
        market_type TEXT NOT NULL,
        entry_price REAL NOT NULL,
        target_price REAL NOT NULL,
        stop_price REAL NOT NULL,
        start_at TEXT NOT NULL,
        end_at TEXT NULL,
        status TEXT NOT NULL DEFAULT 'open',
        close_reason TEXT NULL,
        monitoring_enabled INTEGER NOT NULL DEFAULT 0,
        progress_percent REAL NULL,
        last_price REAL NULL,
        target_percent REAL NULL,
        pnl_percent REAL NULL,
        exit_price REAL NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS signal_categories (
        signal_id INTEGER NOT NULL,
        category_id INTEGER NOT NULL,
        PRIMARY KEY (signal_id, category_id),
        FOREIGN KEY (signal_id) REFERENCES signals(id),
        FOREIGN KEY (category_id) REFERENCES categories(id)
    );

    CREATE INDEX IF NOT EXISTS idx_signals_status ON signals(status);
    CREATE INDEX IF NOT EXISTS idx_categories_slug ON categories(slug);
    SQL;

    $pdo->exec($schema);
}

function ensure_signal_columns(PDO $pdo): void
{
    $tableExists = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'signals'")
        ->fetchColumn();
    if (!$tableExists) {
        init_db($pdo);
    }

    $stmt = $pdo->query("PRAGMA table_info(signals)");
    $columns = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $columns[$row['name']] = true;
    }

    $toAdd = [
        'target_percent' => 'REAL NULL',
        'pnl_percent' => 'REAL NULL',
        'exit_price' => 'REAL NULL',
    ];

    foreach ($toAdd as $column => $definition) {
        if (!isset($columns[$column])) {
            $pdo->exec("ALTER TABLE signals ADD COLUMN {$column} {$definition}");
        }
    }
}

function ensure_category_columns(PDO $pdo): void
{
    $tableExists = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'categories'")
        ->fetchColumn();
    if (!$tableExists) {
        init_db($pdo);
    }

    $stmt = $pdo->query("PRAGMA table_info(categories)");
    $columns = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $columns[$row['name']] = true;
    }

    $toAdd = [
        'closed_at' => 'TEXT NULL',
        'target_percent' => 'REAL NULL',
        'guarantee_percent' => 'REAL NULL',
    ];

    foreach ($toAdd as $column => $definition) {
        if (!isset($columns[$column])) {
            $pdo->exec("ALTER TABLE categories ADD COLUMN {$column} {$definition}");
        }
    }
}

function calculate_progress_percent(float $current, ?float $threshold): ?float
{
    if ($threshold === null || abs($threshold) < 0.000001) {
        return null;
    }

    return ($current / $threshold) * 100;
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9\p{Arabic}]+/u', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : uniqid('cat-', true);
}

function json_response(array $payload, string $etag = ''): void
{
    header('Content-Type: application/json; charset=utf-8');
    if ($etag !== '') {
        header('ETag: ' . $etag);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function client_etag_matches(string $etag): bool
{
    $client = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
    return $client !== '' && $client === $etag;
}

function format_datetime(string $value, array $config): string
{
    $tz = new DateTimeZone($config['timezone']);
    $date = new DateTime($value, $tz);
    $gregorian = $date->format('Y/m/d H:i');
    $jalali = gregorian_to_jalali((int)$date->format('Y'), (int)$date->format('m'), (int)$date->format('d'));
    $jalaliDate = sprintf('%04d/%02d/%02d', $jalali['y'], $jalali['m'], $jalali['d']);

    return $gregorian . ' / ' . to_persian_digits($jalaliDate . ' ' . $date->format('H:i'));
}

function normalize_datetime(?string $value, array $config): string
{
    if (!$value) {
        return date('c');
    }

    $tz = new DateTimeZone($config['timezone']);
    $parsed = DateTime::createFromFormat('Y-m-d\\TH:i', $value, $tz);
    if ($parsed instanceof DateTime) {
        return $parsed->format('c');
    }

    return (new DateTime($value, $tz))->format('c');
}

function gregorian_to_jalali(int $gy, int $gm, int $gd): array
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + (int)(($gy2 + 3) / 4) - (int)(($gy2 + 99) / 100)
        + (int)(($gy2 + 399) / 400) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * (int)($days / 12053));
    $days %= 12053;
    $jy += 4 * (int)($days / 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += (int)(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
    $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));

    return ['y' => $jy, 'm' => $jm, 'd' => $jd];
}

function to_persian_digits(string $value): string
{
    $map = [
        '0' => '۰',
        '1' => '۱',
        '2' => '۲',
        '3' => '۳',
        '4' => '۴',
        '5' => '۵',
        '6' => '۶',
        '7' => '۷',
        '8' => '۸',
        '9' => '۹',
    ];
    return strtr($value, $map);
}

function normalize_position_type(string $positionType): string
{
    return strtolower(trim($positionType));
}

function calculate_target_percent(float $entryPrice, float $targetPrice, string $positionType): ?float
{
    if ($entryPrice <= 0) {
        return null;
    }

    $difference = $targetPrice - $entryPrice;
    if (normalize_position_type($positionType) === 'short') {
        $difference = $entryPrice - $targetPrice;
    }

    return ($difference / $entryPrice) * 100;
}

function calculate_pnl_percent(float $entryPrice, float $exitPrice, string $positionType): ?float
{
    if ($entryPrice <= 0) {
        return null;
    }

    $difference = $exitPrice - $entryPrice;
    if (normalize_position_type($positionType) === 'short') {
        $difference = $entryPrice - $exitPrice;
    }

    return ($difference / $entryPrice) * 100;
}

function require_admin(): void
{
    session_start();
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: /admin/login');
        exit;
    }
}

function flash(string $key, ?string $message = null): ?string
{
    session_start();
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!empty($_SESSION['flash'][$key])) {
        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    return null;
}
