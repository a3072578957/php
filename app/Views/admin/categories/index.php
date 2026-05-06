<section class="admin-page-head admin-page-head--row">
    <div>
        <h1>分类管理</h1>
        <p>统一管理文章分类和作品分类。</p>
    </div>
    <a class="btn btn-primary" href="/admin/categories/create">新增分类</a>
</section>
<div class="admin-filter-row">
    <form method="get" action="/admin/categories" class="filter-form">
        <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="搜索分类名称或 slug">
        <select name="type">
            <option value="">全部类型</option>
            <option value="article" <?= ($pagination['type'] ?? '') === 'article' ? 'selected' : '' ?>>文章分类</option>
            <option value="portfolio" <?= ($pagination['type'] ?? '') === 'portfolio' ? 'selected' : '' ?>>作品分类</option>
        </select>
        <button class="btn btn-primary" type="submit">筛选</button>
    </form>
</div>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>ID</th><th>类型</th><th>名称</th><th>Slug</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($categories as $category): ?>
            <tr>
                <td><?= (int) $category['id'] ?></td>
                <td><?= htmlspecialchars($category['type'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="admin-actions">
                    <a href="/admin/categories/edit/<?= (int) $category['id'] ?>">编辑</a>
                    <form method="post" action="/admin/categories/delete/<?= (int) $category['id'] ?>" onsubmit="return confirm('确定删除这个分类吗？');">
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
            <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/admin/categories?page=<?= $p ?>&type=<?= urlencode($pagination['type']) ?>&q=<?= urlencode($pagination['query']) ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
