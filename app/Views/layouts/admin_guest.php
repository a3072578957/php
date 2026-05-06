<?php
/** @var string $content */
/** @var array $config */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($siteName ?? $config['name'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars($tagline ?? '', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-login-body">
    <?php if (!empty($flash['success'])): ?>
        <div class="admin-login-flash admin-flash admin-flash--ok"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($flash['error'])): ?>
        <div class="admin-login-flash admin-flash admin-flash--err"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?= $content ?>
    <script src="/assets/vendor/jquery.local.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
