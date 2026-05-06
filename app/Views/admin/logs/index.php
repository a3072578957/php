<section class="admin-page-head admin-page-head--row">
    <div>
        <h1>后台操作日志</h1>
        <p>这里记录关键后台动作，包括登录成功与失败、密码修改与找回、内容管理、审核操作和媒体上传。</p>
    </div>
    <a class="btn btn-ghost" href="/admin/logs/export?q=<?= urlencode($pagination['query'] ?? '') ?>&action=<?= urlencode($pagination['action'] ?? '') ?>">导出 CSV</a>
</section>
<div class="admin-filter-row">
    <form method="get" action="/admin/logs" class="filter-form filter-form--search">
        <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="搜索管理员、描述、动作、路径或对象类型">
        <select name="action">
            <option value="">全部动作</option>
            <?php foreach ($actions as $actionName): ?>
                <option value="<?= htmlspecialchars($actionName, ENT_QUOTES, 'UTF-8') ?>" <?= ($pagination['action'] ?? '') === $actionName ? 'selected' : '' ?>><?= htmlspecialchars($actionName, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" type="submit">筛选</button>
    </form>
</div>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th>ID</th><th>管理员</th><th>动作</th><th>对象</th><th>描述</th><th>请求</th><th>时间</th></tr>
        </thead>
        <tbody>
        <?php if (!empty($logs)): ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= (int) $log['id'] ?></td>
                    <td><?= htmlspecialchars($log['admin_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="status-badge status-badge--role"><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td>
                        <?= htmlspecialchars($log['target_type'] ?: '-', ENT_QUOTES, 'UTF-8') ?>
                        <?php if (!empty($log['target_id'])): ?>
                            <p>#<?= (int) $log['target_id'] ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="table-text">
                        <?= htmlspecialchars($log['description'], ENT_QUOTES, 'UTF-8') ?>
                        <?php if (!empty($log['context_json'])): ?>
                            <p><?= htmlspecialchars($log['context_json'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($log['request_method'] ?: '-', ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if (!empty($log['request_path'])): ?>
                            <p><?= htmlspecialchars($log['request_path'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <?php if (!empty($log['ip_address'])): ?>
                            <p>IP: <?= htmlspecialchars($log['ip_address'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" class="table-empty">暂时还没有操作日志。</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/admin/logs?page=<?= $p ?>&q=<?= urlencode($pagination['query']) ?>&action=<?= urlencode($pagination['action']) ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>