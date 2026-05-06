<section class="admin-page-head">
    <h1>评论审核</h1>
    <p>这里集中审核文章评论，可以快速批准、退回待审、发布管理员回复或直接删除。</p>
</section>
<div class="admin-filter-row">
    <form method="get" action="/admin/comments" class="filter-form filter-form--search">
        <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="搜索昵称、评论内容或文章标题">
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
            <tr><th>ID</th><th>评论者</th><th>所属文章</th><th>评论内容</th><th>状态</th><th>时间</th><th>操作</th></tr>
        </thead>
        <tbody>
        <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><?= (int) $comment['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($comment['nickname'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if (!empty($comment['is_admin_reply'])): ?>
                            <span class="status-badge status-badge--role">管理员回复</span>
                        <?php endif; ?>
                        <?php if (!empty($comment['email'])): ?>
                            <p><?= htmlspecialchars($comment['email'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/articles/<?= htmlspecialchars($comment['article_slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($comment['article_title'], ENT_QUOTES, 'UTF-8') ?></a>
                        <?php if (!empty($comment['parent_id'])): ?>
                            <p>回复给：<?= htmlspecialchars($comment['parent_nickname'] ?? '访客', ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="table-text">
                        <?php if (!empty($comment['parent_id']) && !empty($comment['parent_content'])): ?>
                            <div class="table-quote"><?= nl2br(htmlspecialchars($comment['parent_content'], ENT_QUOTES, 'UTF-8')) ?></div>
                        <?php endif; ?>
                        <?= nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')) ?>
                    </td>
                    <td><span class="status-badge <?= $comment['status'] === 'approved' ? 'status-badge--ok' : 'status-badge--pending' ?>"><?= $comment['status'] === 'approved' ? '已通过' : '待审核' ?></span></td>
                    <td><?= htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="admin-actions admin-actions--stack">
                        <?php if (empty($comment['is_admin_reply'])): ?>
                            <a href="/admin/comments/reply/<?= (int) $comment['id'] ?>">回复</a>
                        <?php endif; ?>
                        <?php if ($comment['status'] !== 'approved'): ?>
                            <form method="post" action="/admin/comments/approve/<?= (int) $comment['id'] ?>">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit">通过</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($comment['status'] !== 'pending'): ?>
                            <form method="post" action="/admin/comments/pending/<?= (int) $comment['id'] ?>">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit">退回待审</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="/admin/comments/delete/<?= (int) $comment['id'] ?>" onsubmit="return confirm('确定删除这条评论吗？');">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit">删除</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" class="table-empty">没有找到符合条件的评论。</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/admin/comments?page=<?= $p ?>&q=<?= urlencode($pagination['query']) ?>&status=<?= urlencode($pagination['status']) ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
