<div class="admin-login-wrap">
    <section class="admin-login-card">
        <span class="eyebrow">Reset Password</span>
        <h1>Reset Password</h1>
        <?php if ($validToken): ?>
            <p>You are resetting the password for <strong><?= htmlspecialchars($accountLabel, ENT_QUOTES, 'UTF-8') ?></strong>. Enter a new password and confirm it below.</p>
            <form method="post" action="/admin/reset-password" class="admin-form">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                <label><span>New Password</span><input type="password" name="new_password" minlength="8" required></label>
                <label><span>Confirm Password</span><input type="password" name="confirm_password" minlength="8" required></label>
                <button class="btn btn-primary" type="submit">Save New Password</button>
            </form>
        <?php else: ?>
            <p>This reset link is invalid, already used, or expired. Request a fresh recovery email to continue.</p>
            <div class="admin-form__actions" style="margin-top:20px;">
                <a class="btn btn-primary" href="/admin/forgot-password">Request New Link</a>
                <a class="btn btn-ghost" href="/admin/login">Back to Login</a>
            </div>
        <?php endif; ?>
    </section>
</div>