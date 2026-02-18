<?php
/** @var string|null $error */
?>
<section class="form-panel">
    <form method="post" class="form-grid">
        <h2>ورود امن به پنل</h2>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <label>
            نام کاربری
            <input type="text" name="username" required>
        </label>
        <label>
            رمز عبور
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="primary">ورود</button>
    </form>
</section>
