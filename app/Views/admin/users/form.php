<section class="admin-page-head">
    <h1><?= $user ? '编辑管理员' : '新增管理员' ?></h1>
    <p><?= $user ? '你可以修改账号资料、邮箱和角色，留空密码则保持原密码不变。' : '创建新的后台管理员账号，密码长度至少 8 位，并建议立即配置可接收找回密码邮件的邮箱。' ?></p>
</section>
<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="admin-form admin-form--wide">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <label><span>用户名</span><input type="text" name="username" maxlength="80" value="<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></label>
    <label><span>显示名称</span><input type="text" name="display_name" maxlength="120" value="<?= htmlspecialchars($user['display_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></label>
    <label><span>邮箱</span><input type="email" name="email" maxlength="160" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></label>
    <label>
        <span>角色</span>
        <select name="role" required>
            <?php foreach ($roles as $role): ?>
                <option value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>" <?= ($user['role'] ?? 'editor') === $role ? 'selected' : '' ?>><?= htmlspecialchars(\Core\Controller::roleLabel($role), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label><span><?= $passwordRequired ? '登录密码' : '新密码' ?></span><input type="password" name="password" maxlength="120" <?= $passwordRequired ? 'required' : '' ?>></label>
    <div class="category-usage-note">
        角色说明：超级管理员拥有全部权限，编辑可管理内容与媒体，审核员可处理评论和留言审核。<?= $passwordRequired ? ' 密码会使用 PHP password_hash 进行加密存储。' : ' 如果不需要修改密码，可以保持为空。' ?>
        管理员邮箱会用于“找回密码”邮件发送，因此建议填写真实可用邮箱。
    </div>
    <div class="admin-form__actions">
        <button class="btn btn-primary" type="submit">保存管理员</button>
        <a class="btn btn-ghost" href="/admin/users">返回列表</a>
    </div>
</form>