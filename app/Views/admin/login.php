<div class="admin-login-wrap">
    <section class="admin-login-card">
        <span class="eyebrow">Yuexia Admin</span>
        <h1>Admin Login</h1>
        <p>The admin panel now supports multi-user accounts, role-based permissions, password hashing, failed-login logs, and password recovery through each admin account email address.</p>
        <form method="post" action="/admin/login" class="admin-form">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <label><span>Username</span><input type="text" name="username" required></label>
            <label><span>Password</span><input type="password" name="password" required></label>
            <button class="btn btn-primary" type="submit">Login</button>
        </form>
        <p style="margin-top:16px;"><a href="/admin/forgot-password">Forgot your password?</a></p>
    </section>
</div>