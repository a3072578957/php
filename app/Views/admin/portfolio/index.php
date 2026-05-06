<section class="admin-page-head admin-page-head--row">
    <div>
        <h1>作品管理</h1>
        <p>这里可以新增、编辑和删除作品。</p>
    </div>
    <a class="btn btn-primary" href="/admin/portfolio/create">新增作品</a>
</section>
<div class="admin-filter-row">
    <form method="get" action="/admin/portfolio" class="filter-form">
        <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="搜索作品标题或摘要">
        <select name="category_id">
            <option value="0">全部分类</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (int) ($pagination['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" type="submit">搜索</button>
    </form>
</div>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>ID</th><th>标题</th><th>分类</th><th>技术栈</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($portfolio as $work): ?>
            <tr>
                <td><?= (int) $work['id'] ?></td>
                <td><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($work['category_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($work['stack'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="admin-actions">
                    <a href="/admin/portfolio/edit/<?= (int) $work['id'] ?>">编辑</a>
                    <form method="post" action="/admin/portfolio/delete/<?= (int) $work['id'] ?>" onsubmit="return confirm('确定删除这个作品吗？');">
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
            <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/admin/portfolio?page=<?= $p ?>&q=<?= urlencode($pagination['query']) ?>&category_id=<?= (int) ($pagination['category_id'] ?? 0) ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
