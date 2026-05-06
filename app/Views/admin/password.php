<section class="admin-page-head">
    <h1>修改密码</h1>
    <p>为了账号安全，建议定期更新后台密码。新密码长度至少 8 位。</p>
</section>
<form method="post" action="/admin/password" class="admin-form admin-form--wide">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <label><span>当前密码</span><input type="password" name="current_password" maxlength="120" required></label>
    <label><span>新密码</span><input type="password" name="new_password" maxlength="120" required></label>
    <label><span>确认新密码</span><input type="password" name="confirm_password" maxlength="120" required></label>
    <div class="admin-form__actions">
        <button class="btn btn-primary" type="submit">更新密码</button>
        <a class="btn btn-ghost" href="/admin">返回仪表盘</a>
    </div>
</form>
