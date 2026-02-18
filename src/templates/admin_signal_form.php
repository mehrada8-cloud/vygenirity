<?php
/** @var array|null $signal */
/** @var array $categories */
/** @var array $selectedCategories */
$pricesPath = __DIR__ . '/../../data/prices.json';
$priceData = [];
if (file_exists($pricesPath)) {
    $priceData = json_decode(file_get_contents($pricesPath), true) ?: [];
}
$availableSymbols = array_keys($priceData);
?>
<section class="card admin-card">
    <div class="card-header">
        <div>
            <h2><?= $signal ? 'ویرایش سیگنال' : 'ایجاد سیگنال جدید' ?></h2>
            <p class="muted">همه اجزا قابل ویرایش هستند، زمان‌ها به میلادی ذخیره می‌شوند.</p>
        </div>
        <span class="status-pill"><?= $signal ? 'ویرایش سریع' : 'ساخت سیگنال' ?></span>
    </div>
    <?php if ($error = flash('error')): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/admin/signals/save" class="form-grid deluxe">
        <?php if ($signal): ?>
            <input type="hidden" name="id" value="<?= $signal['id'] ?>">
        <?php endif; ?>
        <label>
            عنوان سیگنال
            <input type="text" name="title" value="<?= htmlspecialchars($signal['title'] ?? '') ?>" required>
        </label>
        <label>
            نام جفت ارز
            <input type="text" name="pair" value="<?= htmlspecialchars($signal['pair'] ?? '') ?>" required>
        </label>
        <label>
            نوع سیگنال
            <select name="position_type">
                <option value="long" <?= ($signal['position_type'] ?? '') === 'long' ? 'selected' : '' ?>>Long</option>
                <option value="short" <?= ($signal['position_type'] ?? '') === 'short' ? 'selected' : '' ?>>Short</option>
            </select>
        </label>
        <label>
            بازار
            <select name="market_type">
                <option value="spot" <?= ($signal['market_type'] ?? '') === 'spot' ? 'selected' : '' ?>>Spot</option>
                <option value="futures" <?= ($signal['market_type'] ?? '') === 'futures' ? 'selected' : '' ?>>Futures</option>
                <option value="both" <?= ($signal['market_type'] ?? '') === 'both' ? 'selected' : '' ?>>Both</option>
            </select>
        </label>
        <label>
            قیمت ورود
            <input type="number" step="0.0001" name="entry_price" value="<?= htmlspecialchars($signal['entry_price'] ?? '') ?>" required>
        </label>
        <label>
            قیمت تارگت
            <input type="number" step="0.0001" name="target_price" value="<?= htmlspecialchars($signal['target_price'] ?? '') ?>" required>
        </label>
        <label>
            قیمت استاپ
            <input type="number" step="0.0001" name="stop_price" value="<?= htmlspecialchars($signal['stop_price'] ?? '') ?>" required>
        </label>
        <label>
            زمان شروع
            <input type="datetime-local" name="start_at" value="<?= isset($signal['start_at']) ? date('Y-m-d\TH:i', strtotime($signal['start_at'])) : '' ?>" required>
        </label>
        <label>
            زمان پایان (اختیاری)
            <input type="datetime-local" name="end_at" value="<?= isset($signal['end_at']) && $signal['end_at'] ? date('Y-m-d\TH:i', strtotime($signal['end_at'])) : '' ?>">
        </label>
        <label class="checkbox">
            <input type="checkbox" name="monitoring_enabled" <?= !empty($signal['monitoring_enabled']) ? 'checked' : '' ?>>
            بررسی خودکار با وب‌سرویس قیمت
        </label>
        <div class="note premium" data-available-symbols='<?= json_encode($availableSymbols, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>'>
            <strong>نمادهای موجود در فایل قیمت:</strong>
            <?= $availableSymbols ? implode('، ', array_map('htmlspecialchars', $availableSymbols)) : 'هنوز داده‌ای ثبت نشده است.' ?>
            <p class="warning" data-symbol-warning></p>
        </div>
        <fieldset>
            <legend>دسته‌بندی‌ها</legend>
            <div class="chip-group">
                <?php foreach ($categories as $category): ?>
                    <label class="chip">
                        <input type="checkbox" name="categories[]" value="<?= $category['id'] ?>"
                            <?= in_array($category['id'], $selectedCategories, true) ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($category['name']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <button class="primary" type="submit">ذخیره سیگنال</button>
    </form>
</section>
