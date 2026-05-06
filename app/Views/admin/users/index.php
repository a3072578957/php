<section class="admin-page-head admin-page-head--row">
    <div>
        <h1>管理员管理</h1>
        <p>这里可以新增、编辑和删除后台管理员账号。密码会以哈希方式保存，邮箱可用于找回密码，角色会决定后台可见菜单和操作范围。</p>
    </div>
    <a class="btn btn-primary" href="/admin/users/create">新增管理员</a>
</section>
<div class="admin-filter-row">
    <form method="get" action="/admin/users" class="filter-form">
        <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="搜索用户名、邮箱、显示名称或角色">
        <button class="btn btn-primary" type="submit">搜索</button>
    </form>
</div>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th>ID</th><th>用户名</th><th>显示名称</th><th>邮箱</th><th>角色</th><th>创建时间</th><th>更新时间</th><th>操作</th></tr>
        </thead>
        <tbody>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= (int) $user['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                        <?php if ((int) $currentUserId === (int) $user['id']): ?>
                            <span class="status-badge status-badge--ok">当前登录</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($user['email'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="status-badge status-badge--role"><?= htmlspecialchars(\Core\Controller::roleLabel($user['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($user['updated_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="admin-actions">
                        <a href="/admin/users/edit/<?= (int) $user['id'] ?>">编辑</a>
                        <?php if ((int) $currentUserId !== (int) $user['id']): ?>
                            <form method="post" action="/admin/users/delete/<?= (int) $user['id'] ?>" onsubmit="return confirm('确定删除这个管理员吗？');">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit">删除</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8" class="table-empty">没有找到符合条件的管理员。</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/admin/users?page=<?= $p ?>&q=<?= urlencode($pagination['query']) ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>