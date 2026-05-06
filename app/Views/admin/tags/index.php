<section class="admin-page-head admin-page-head--row">
    <div>
        <h1>标签管理</h1>
        <p>统一维护文章标签，前台搜索和文章筛选会使用这些标签。</p>
    </div>
    <a class="btn btn-primary" href="/admin/tags/create">新增标签</a>
</section>
<div class="admin-filter-row">
    <form method="get" action="/admin/tags" class="filter-form">
        <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="搜索标签名称或 slug">
        <button class="btn btn-primary" type="submit">搜索</button>
    </form>
</div>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>ID</th><th>名称</th><th>Slug</th><th>使用次数</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($tags as $tag): ?>
            <tr>
                <td><?= (int) $tag['id'] ?></td>
                <td><?= htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($tag['slug'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) ($tag['usage_count'] ?? 0) ?></td>
                <td class="admin-actions">
                    <a href="/admin/tags/edit/<?= (int) $tag['id'] ?>">编辑</a>
                    <form method="post" action="/admin/tags/delete/<?= (int) $tag['id'] ?>" onsubmit="return confirm('确定删除这个标签吗？');">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit">删除</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/admin/tags?page=<?= $p ?>&q=<?= urlencode($pagination['query']) ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
