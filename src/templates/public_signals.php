<?php
/** @var array $category */
/** @var string $slug */
?>
<section id="signals" class="signal-list" data-endpoint="/api/signals?category=<?= htmlspecialchars($slug) ?>">
    <div class="signal-card loading">
        <h3>در حال دریافت سیگنال‌ها...</h3>
        <p>بروزرسانی بر اساس ETag فعال است.</p>
    </div>
</section>
<section class="report-modal" data-report-modal aria-hidden="true">
    <div class="report-modal__backdrop" data-report-close></div>
    <div class="report-modal__content" role="dialog" aria-modal="true" aria-labelledby="report-title">
        <div class="report-modal__header">
            <div>
                <p class="report-modal__eyebrow">گزارش لاین</p>
                <h2 id="report-title">نمای کلی عملکرد</h2>
                <p class="subtle" data-report-subtitle>آمار تجمیعی بر اساس سیگنال‌های بسته شده</p>
            </div>
            <button class="report-modal__close" type="button" data-report-close aria-label="بستن گزارش">×</button>
        </div>
        <div class="report-modal__body" data-report-scroll-body>
            <div class="report-modal__grid">
                <div class="report-panel report-panel--chart">
                    <div class="report-panel__header">
                        <span>نمودار PNL تجمعی</span>
                        <span class="badge animated-number" data-report-total data-animate-number="true" dir="ltr">0%</span>
                    </div>
                    <div class="report-chart" data-report-chart></div>
                </div>
                <div class="report-panel report-panel--stats">
                    <div class="report-stats">
                        <div class="report-stat">
                            <span>تعداد سیگنال‌ها</span>
                            <strong data-report-count>0</strong>
                        </div>
                        <div class="report-stat">
                            <span>سیگنال‌های بسته</span>
                            <strong data-report-closed>0</strong>
                        </div>
                        <div class="report-stat">
                            <span>سیگنال‌های موفق</span>
                            <strong data-report-success>0</strong>
                        </div>
                        <div class="report-stat">
                            <span>سیگنال‌های ناموفق</span>
                            <strong data-report-failed>0</strong>
                        </div>
                        <div class="report-stat">
                            <span>ایجاد لاین</span>
                            <strong data-report-created>—</strong>
                        </div>
                        <div class="report-stat">
                            <span>بسته شدن لاین</span>
                            <strong data-report-closed-at>—</strong>
                        </div>
                        <div class="report-stat">
                            <span>تارگت لاین</span>
                            <strong data-report-target>—</strong>
                        </div>
                        <div class="report-stat">
                            <span>حد ضمانت</span>
                            <strong data-report-guarantee>—</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="report-modal__scroll" type="button" data-report-scroll aria-label="رفتن به پایین گزارش">
            <span aria-hidden="true">↓</span>
        </button>
    </div>
</section>

<section class="line-progress-float" data-line-progress aria-live="polite">
    <div class="line-progress-float__header">
        <strong data-line-progress-title>پیشروی لاین</strong>
        <span class="line-progress-float__value" data-line-progress-value dir="ltr">0%</span>
    </div>
    <div class="line-progress-float__scale">
        <span data-line-progress-stop>حد ضمانت: —</span>
        <span data-line-progress-target>تارگت: —</span>
    </div>
    <div class="line-progress-float__track">
        <span class="line-progress-float__marker" data-line-progress-marker style="--marker-position:50%"></span>
    </div>
    <p class="line-progress-float__caption" data-line-progress-caption>در حال محاسبه نسبت PNL تجمعی...</p>
</section>
