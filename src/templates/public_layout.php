<?php
/** @var string $title */
/** @var string $content */
/** @var array $config */
/** @var bool $hide_footer */
/** @var bool $hide_header */
?>
<!doctype html>
<html lang="fa" dir="rtl" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
    <div class="app-shell">
        <?php if (empty($hide_header)): ?>
            <header class="site-header">
                <div>
                    <p class="app-label"><?= htmlspecialchars($config['app_name']) ?></p>
                    <h1><?= htmlspecialchars($title) ?></h1>
                    <p class="subtle">پلتفرم سیگنال حرفه‌ای با بروزرسانی هوشمند و طراحی لوکس.</p>
                </div>
                <div class="header-actions">
                    <a class="ghost-link" href="/categories">دسته‌بندی‌ها</a>
                </div>
            </header>
        <?php endif; ?>
        <main>
            <?= $content ?>
        </main>
        <?php if (empty($hide_footer)): ?>
            <footer class="site-footer">
                <span>© <?= date('Y') ?> Vygen</span>
                <span class="footer-note">اطلاعات سیگنال‌ها صرفاً جهت نمایش بوده و توسط ادمین مدیریت می‌شود.</span>
            </footer>
        <?php endif; ?>
    </div>
    <div class="floating-menu" data-floating-menu data-open="false">
        <div class="floating-menu__actions" id="floating-menu-actions" role="menu">
            <button class="floating-menu__action" type="button" data-report-open aria-label="گزارش">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M4 19.5a1 1 0 0 1-1-1V5a1 1 0 1 1 2 0v13.5h14a1 1 0 1 1 0 2H4Zm4.25-3.5a1 1 0 0 1-1-1v-5a1 1 0 1 1 2 0v5a1 1 0 0 1-1 1Zm4.25 0a1 1 0 0 1-1-1v-8a1 1 0 1 1 2 0v8a1 1 0 0 1-1 1Zm4.25 0a1 1 0 0 1-1-1V7a1 1 0 1 1 2 0v8a1 1 0 0 1-1 1Z"/>
                </svg>
            </button>
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
</body>
</html>
