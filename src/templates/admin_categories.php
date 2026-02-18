<?php
/** @var array $categories */
$success = flash('success');
$error = flash('error');
?>
<section class="grid two">
    <div class="card admin-card">
        <h2 data-category-form-title>ایجاد / ویرایش دسته‌بندی</h2>
        <p class="muted">نام و اسلاگ یکتا برای نمایش عمومی و لینک اختصاصی.</p>
        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/admin/categories/save" class="form-grid" data-category-form>
            <input type="hidden" name="id" value="">
            <label>
                نام دسته‌بندی
                <input type="text" name="name" required>
            </label>
            <label>
                اسلاگ (اختیاری)
                <input type="text" name="slug" placeholder="auto">
            </label>
            <label>
                تارگت لاین (%)
                <input type="number" name="target_percent" step="0.01" min="0" placeholder="مثلاً 15">
            </label>
            <label>
                حد ضمانت (%)
                <input type="number" name="guarantee_percent" step="0.01" min="0" placeholder="مثلاً 10">
            </label>
            <div class="form-actions">
                <button class="primary" type="submit">ذخیره</button>
                <button class="link" type="button" data-category-reset hidden>لغو ویرایش</button>
            </div>
        </form>
    </div>
    <div class="card admin-card">
        <h2>لیست دسته‌بندی‌ها</h2>
        <p class="muted">حذف امن بدون تداخل با سیگنال‌های قبلی.</p>
        <div class="admin-toolbar">
            <label class="admin-search" for="category-search-input">
                <span>جستجو</span>
                <input id="category-search-input" type="search" placeholder="نام یا اسلاگ..." data-table-search="categories">
            </label>
            <label class="admin-filter" for="category-status-filter">
                <span>وضعیت</span>
                <select id="category-status-filter" data-table-filter="categories">
                    <option value="all">همه</option>
                    <option value="active">فعال</option>
                    <option value="closed">بسته شده</option>
                    <option value="deleted">حذف شده</option>
                </select>
            </label>
        </div>
        <div class="table admin-table">
            <div class="table-row header">
                <span>نام</span>
                <span>اسلاگ</span>
                <span>وضعیت</span>
                <span>تارگت / ضمانت</span>
                <span>ایجاد</span>
                <span>بستن</span>
                <span>عملیات</span>
            </div>
            <?php foreach ($categories as $category): ?>
                <?php
                $status = $category['deleted_at'] ? 'deleted' : (!empty($category['closed_at']) ? 'closed' : 'active');
                ?>
                <div class="table-row" data-row="categories" data-searchable="<?= htmlspecialchars(strtolower($category['name'] . ' ' . $category['slug'])) ?>" data-status="<?= $status ?>">
                    <span><?= htmlspecialchars($category['name']) ?></span>
                    <span class="mono"><?= htmlspecialchars($category['slug']) ?></span>
                    <span>
                        <?php if ($category['deleted_at']): ?>
                            حذف شده
                        <?php elseif (!empty($category['closed_at'])): ?>
                            بسته شده
                        <?php else: ?>
                            فعال
                        <?php endif; ?>
                    </span>
                    <span class="mono">
                        <?= $category['target_percent'] !== null ? htmlspecialchars((string)$category['target_percent']) . '%' : '—' ?>
                        /
                        <?= $category['guarantee_percent'] !== null ? htmlspecialchars((string)$category['guarantee_percent']) . '%' : '—' ?>
                    </span>
                    <span class="mono"><?= htmlspecialchars($category['created_at']) ?></span>
                    <span class="mono"><?= $category['closed_at'] ? htmlspecialchars($category['closed_at']) : '—' ?></span>
                    <span class="row-actions">
                        <?php if (!$category['deleted_at']): ?>
                            <button class="link" data-edit-category
                                    data-id="<?= $category['id'] ?>"
                                    data-name="<?= htmlspecialchars($category['name'], ENT_QUOTES) ?>"
                                    data-slug="<?= htmlspecialchars($category['slug'], ENT_QUOTES) ?>"
                                    data-target-percent="<?= htmlspecialchars((string)($category['target_percent'] ?? ''), ENT_QUOTES) ?>"
                                    data-guarantee-percent="<?= htmlspecialchars((string)($category['guarantee_percent'] ?? ''), ENT_QUOTES) ?>">ویرایش</button>
                            <?php if (empty($category['closed_at'])): ?>
                                <form method="post" action="/admin/categories/close">
                                    <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                    <button class="link" type="submit">بستن لاین</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="/admin/categories/delete">
                                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                <button class="link danger" type="submit">حذف</button>
                            </form>
                        <?php else: ?>
                            --
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="table-empty" data-table-empty="categories" hidden>دسته‌بندی مطابق فیلتر پیدا نشد.</p>
    </div>
</section>
