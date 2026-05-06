<section class="admin-page-head">
    <h1>留言审核</h1>
    <p>这里审核前台留言板内容，适合处理公开留言、合作问候和反馈信息。</p>
</section>
<div class="admin-filter-row">
    <form method="get" action="/admin/messages" class="filter-form filter-form--search">
        <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="搜索昵称、主题或留言内容">
        <select name="status">
            <option value="">全部状态</option>
            <option value="pending" <?= ($pagination['status'] ?? '') === 'pending' ? 'selected' : '' ?>>待审核</option>
            <option value="approved" <?= ($pagination['status'] ?? '') === 'approved' ? 'selected' : '' ?>>已通过</option>
        </select>
        <button class="btn btn-primary" type="submit">筛选</button>
    </form>
</div>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th>ID</th><th>留言者</th><th>主题</th><th>留言内容</th><th>状态</th><th>时间</th><th>操作</th></tr>
        </thead>
        <tbody>
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= (int) $message['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($message['nickname'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if (!empty($message['email'])): ?>
                            <p><?= htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($message['subject'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="table-text"><?= nl2br(htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8')) ?></td>
                    <td><span class="status-badge <?= $message['status'] === 'approved' ? 'status-badge--ok' : 'status-badge--pending' ?>"><?= $message['status'] === 'approved' ? '已通过' : '待审核' ?></span></td>
                    <td><?= htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="admin-actions admin-actions--stack">
                        <?php if ($message['status'] !== 'approved'): ?>
                            <form method="post" action="/admin/messages/approve/<?= (int) $message['id'] ?>">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit">通过</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($message['status'] !== 'pending'): ?>
                            <form method="post" action="/admin/messages/pending/<?= (int) $message['id'] ?>">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit">退回待审</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="/admin/messages/delete/<?= (int) $message['id'] ?>" onsubmit="return confirm('确定删除这条留言吗？');">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit">删除</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" class="table-empty">没有找到符合条件的留言。</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/admin/messages?page=<?= $p ?>&q=<?= urlencode($pagination['query']) ?>&status=<?= urlencode($pagination['status']) ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
