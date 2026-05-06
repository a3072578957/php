<section class="admin-page-head">
    <h1><?= $tag ? '编辑标签' : '新增标签' ?></h1>
</section>
<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="admin-form admin-form--wide">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <label><span>标签名称</span><input type="text" name="name" value="<?= htmlspecialchars($tag['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></label>
    <?php if (!empty($tag)): ?>
        <div class="category-usage-note">当前这个标签被 <?= (int) $usageCount ?> 篇文章使用。</div>
    <?php endif; ?>
    <div class="admin-form__actions">
        <button class="btn btn-primary" type="submit">保存标签</button>
        <a class="btn btn-ghost" href="/admin/tags">返回列表</a>
    </div>
</form>
