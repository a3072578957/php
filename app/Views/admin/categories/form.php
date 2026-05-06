<section class="admin-page-head">
    <h1><?= $category ? '编辑分类' : '新增分类' ?></h1>
</section>
<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="admin-form admin-form--wide">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <label>
        <span>类型</span>
        <select name="type" required>
            <option value="article" <?= ($category['type'] ?? '') === 'article' ? 'selected' : '' ?>>文章分类</option>
            <option value="portfolio" <?= ($category['type'] ?? '') === 'portfolio' ? 'selected' : '' ?>>作品分类</option>
        </select>
    </label>
    <label><span>分类名称</span><input type="text" name="name" value="<?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></label>
    <?php if (!empty($category)): ?>
        <div class="category-usage-note">
            当前被 <?= (int) ($usage['articles'] ?? 0) ?> 篇文章和 <?= (int) ($usage['portfolio'] ?? 0) ?> 个作品使用。
        </div>
    <?php endif; ?>
    <div class="admin-form__actions">
        <button class="btn btn-primary" type="submit">保存分类</button>
        <a class="btn btn-ghost" href="/admin/categories">返回列表</a>
    </div>
</form>
