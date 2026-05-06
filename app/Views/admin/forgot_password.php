<div class="admin-login-wrap">
    <section class="admin-login-card">
        <span class="eyebrow">Password Recovery</span>
        <h1>Forgot Password</h1>
        <p>Enter the admin username. If the account exists, has a valid email address, and outgoing mail is configured, the system will send a reset link.</p>
        <form method="post" action="/admin/forgot-password" class="admin-form">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <label><span>Admin Username</span><input type="text" name="username" maxlength="80" required></label>
            <button class="btn btn-primary" type="submit">Send Reset Link</button>
        </form>
        <p style="margin-top:16px;"><a href="/admin/login">Back to login</a></p>
    </section>
</div>