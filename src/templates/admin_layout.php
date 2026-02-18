<?php
/** @var string $title */
/** @var string $content */
/** @var array $config */
?>
<?php $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin'; ?>
<!doctype html>
<html lang="fa" dir="rtl" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> | پنل ادمین</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body class="admin">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="brand">
                <span class="brand-mark">VY</span>
                <div>
                    <strong>Vygen Admin</strong>
                    <p>Luxury Signals</p>
                </div>
            </div>
            <nav class="admin-nav">
                <a class="nav-item <?= str_starts_with($currentPath, '/admin/categories') ? 'is-active' : '' ?>" href="/admin/categories">دسته‌بندی‌ها</a>
                <a class="nav-item <?= str_starts_with($currentPath, '/admin/signals') ? 'is-active' : '' ?>" href="/admin/signals">سیگنال‌ها</a>
                <a class="nav-item" href="/categories" target="_blank" rel="noopener">نمایش عمومی</a>
            </nav>
            <div class="sidebar-actions">
                <a class="ghost-link" href="/admin/logout">خروج امن</a>
            </div>
        </aside>
        <div class="admin-main">
            <header class="admin-topbar">
                <div>
                    <p class="app-label">پنل مدیریت</p>
                    <h1><?= htmlspecialchars($title) ?></h1>
                    <p class="subtle">مدیریت دقیق دسته‌بندی‌ها و سیگنال‌ها بدون خطا.</p>
                </div>
                <div class="topbar-actions">
                    <span class="status-pill">SSL فعال</span>
                    <span class="status-pill">PHP 8.2</span>
                </div>
            </header>
            <main class="admin-content">
                <?= $content ?>
            </main>
            <footer class="admin-footer">
                <span>Vygen Admin • Luxury Control Center</span>
            </footer>
        </div>
    </div>
    <div class="floating-menu" data-floating-menu data-open="false">
        <div class="floating-menu__actions" id="floating-menu-actions" role="menu">
            <button class="floating-menu__action" type="button" data-theme-toggle aria-label="تغییر تم">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M12 4.5a1 1 0 0 1 1 1V7a1 1 0 1 1-2 0V5.5a1 1 0 0 1 1-1Zm0 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm7.5-4.5a1 1 0 0 1 1 1v.5a1 1 0 1 1-2 0V12a1 1 0 0 1 1-1ZM4.5 11a1 1 0 0 1 1 1v.5a1 1 0 1 1-2 0V12a1 1 0 0 1 1-1Zm11.08-5.58a1 1 0 0 1 1.41 0l.36.36a1 1 0 0 1-1.41 1.41l-.36-.36a1 1 0 0 1 0-1.41ZM6.65 14.35a1 1 0 0 1 1.41 0l.36.36a1 1 0 1 1-1.41 1.41l-.36-.36a1 1 0 0 1 0-1.41Zm10.76 0a1 1 0 0 1 0 1.41l-.36.36a1 1 0 1 1-1.41-1.41l.36-.36a1 1 0 0 1 1.41 0ZM7.35 5.35a1 1 0 0 1 0 1.41l-.36.36a1 1 0 1 1-1.41-1.41l.36-.36a1 1 0 0 1 1.41 0ZM12 17a1 1 0 0 1 1 1v1.5a1 1 0 1 1-2 0V18a1 1 0 0 1 1-1Z"/>
                </svg>
            </button>
        </div>
        <button class="floating-menu__button" type="button" data-floating-trigger aria-expanded="false" aria-controls="floating-menu-actions">
            <span class="floating-menu__icon" aria-hidden="true">☰</span>
        </button>
    </div>
    <script src="/assets/app.js" defer></script>
    <script src="/assets/admin.js" defer></script>
</body>
</html>
