<?php
/** @var string $content */
/** @var array $config */
$currentRole = (string) ($_SESSION['admin_role'] ?? '');
$currentName = (string) ($_SESSION['admin_name'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($siteName ?? $config['name'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars($tagline ?? '', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand admin-brand--stack">
            <div class="admin-brand__row">
                <span class="admin-brand__mark">YX</span>
                <div>
                    <strong><?= htmlspecialchars($config['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <small>Admin Panel</small>
                </div>
            </div>
            <div class="admin-role-note">
                <strong><?= htmlspecialchars($currentName, ENT_QUOTES, 'UTF-8') ?></strong>
                <span class="status-badge status-badge--role"><?= htmlspecialchars(\Core\Controller::roleLabel($currentRole), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
        <nav class="admin-nav">
            <?php if (\Core\Controller::roleCan($currentRole, 'dashboard.view')): ?>
                <a href="/admin">仪表盘</a>
            <?php endif; ?>
            <a href="/admin/password">修改密码</a>
            <?php if (\Core\Controller::roleCan($currentRole, 'users.manage')): ?>
                <a href="/admin/users">管理员</a>
            <?php endif; ?>
            <?php if (\Core\Controller::roleCan($currentRole, 'logs.view')): ?>
                <a href="/admin/logs">操作日志</a>
            <?php endif; ?>
            <?php if (\Core\Controller::roleCan($currentRole, 'content.manage')): ?>
                <a href="/admin/articles">文章管理</a>
                <a href="/admin/portfolio">作品管理</a>
            <?php endif; ?>
            <?php if (\Core\Controller::roleCan($currentRole, 'moderation.manage')): ?>
                <a href="/admin/comments">评论审核</a>
                <a href="/admin/messages">留言审核</a>
            <?php endif; ?>
            <?php if (\Core\Controller::roleCan($currentRole, 'taxonomy.manage')): ?>
                <a href="/admin/categories">分类管理</a>
                <a href="/admin/tags">标签管理</a>
            <?php endif; ?>
            <?php if (\Core\Controller::roleCan($currentRole, 'media.manage')): ?>
                <a href="/admin/media">图片库</a>
            <?php endif; ?>
            <a href="/guestbook" target="_blank">前台留言</a>
            <a href="/search" target="_blank">前台搜索</a>
            <a href="/" target="_blank">查看前台</a>
            <form method="post" action="/admin/logout" class="admin-logout-form">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">退出登录</button>
            </form>
        </nav>
    </aside>
    <main class="admin-main">
        <?php if (!empty($flash['success'])): ?>
            <div class="admin-flash admin-flash--ok"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($flash['error'])): ?>
            <div class="admin-flash admin-flash--err"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?= $content ?>
    </main>
</div>
<script src="/assets/vendor/jquery.local.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>
