<?php
/** @var array $signals */
/** @var array $categoryMap */
$success = flash('success');
$error = flash('error');
?>
<section class="card admin-card">
    <div class="card-header">
        <div>
            <h2>سیگنال‌ها</h2>
            <p class="muted">ایجاد، ویرایش و بستن سریع سیگنال‌ها با کنترل کامل.</p>
        </div>
        <a class="primary-link" href="/admin/signals/new">ایجاد سیگنال جدید</a>
    </div>
    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="admin-toolbar">
        <label class="admin-search" for="signal-search-input">
            <span>جستجو</span>
            <input id="signal-search-input" type="search" placeholder="عنوان یا جفت ارز..." data-table-search="signals">
        </label>
        <label class="admin-filter" for="signal-status-filter">
            <span>وضعیت</span>
            <select id="signal-status-filter" data-table-filter="signals">
                <option value="all">همه</option>
                <option value="open">فعال</option>
                <option value="closed">بسته</option>
            </select>
        </label>
    </div>
    <div class="table admin-table">
        <div class="table-row header">
            <span>عنوان</span>
            <span>جفت ارز</span>
            <span>وضعیت</span>
            <span>دسته‌ها</span>
            <span>عملیات</span>
        </div>
        <?php foreach ($signals as $signal): ?>
            <div class="table-row" data-row="signals" data-status="<?= $signal['status'] ?>" data-searchable="<?= htmlspecialchars(strtolower($signal['title'] . ' ' . $signal['pair'])) ?>">
                <span><?= htmlspecialchars($signal['title']) ?></span>
                <span class="mono"><?= htmlspecialchars($signal['pair']) ?></span>
                <span>
                    <span class="badge <?= $signal['status'] === 'open' ? 'badge-success' : 'badge-muted' ?>">
                        <?= $signal['status'] === 'open' ? 'فعال' : 'بسته' ?>
                    </span>
                </span>
                <span><?= htmlspecialchars(implode('، ', $categoryMap[$signal['id']] ?? [])) ?></span>
                <span class="row-actions">
                    <a class="link" href="/admin/signals/edit?id=<?= $signal['id'] ?>">ویرایش</a>
                    <?php if ($signal['status'] === 'open'): ?>
                        <form method="post" action="/admin/signals/close" class="inline-form signal-close-form" data-close-form>
                            <input type="hidden" name="id" value="<?= $signal['id'] ?>">
                            <select name="close_reason" data-close-reason>
                                <option value="target">تارگت</option>
                                <option value="stop">استاپ</option>
                                <option value="manual" selected>بستن دستی</option>
                            </select>
                            <input type="number" step="0.0001" name="exit_price" placeholder="قیمت خروج دستی" data-exit-price>
                            <button class="link danger" type="submit">بستن</button>
                        </form>
                    <?php else: ?>
                        --
                    <?php endif; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="table-empty" data-table-empty="signals" hidden>سیگنالی مطابق فیلتر پیدا نشد.</p>
</section>
