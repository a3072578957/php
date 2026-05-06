<section class="admin-page-head admin-page-head--row">
    <div>
        <h1><?= $isPicker ? '选择图片' : '图片库' ?></h1>
        <p><?= $isPicker ? '点击选择按钮即可把图片地址回填到当前表单。' : '统一管理后台上传的图片资源。' ?></p>
    </div>
</section>
<div class="media-upload-card">
    <form method="post" action="<?= htmlspecialchars($uploadAction, ENT_QUOTES, 'UTF-8') ?>" class="admin-form" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <label><span>图片备注</span><input type="text" name="alt_text" placeholder="可选，用于图片描述"></label>
        <label><span>上传图片</span><input type="file" name="media_upload" accept=".jpg,.jpeg,.png,.gif,.webp" required></label>
        <div class="admin-form__actions">
            <button class="btn btn-primary" type="submit">上传到图片库</button>
            <?php if ($isPicker): ?>
                <button class="btn btn-ghost" type="button" onclick="window.close();">关闭窗口</button>
            <?php endif; ?>
        </div>
    </form>
</div>
<div class="admin-filter-row">
    <form method="get" action="/admin/media" class="filter-form">
        <?php if ($isPicker): ?>
            <input type="hidden" name="picker" value="1">
            <input type="hidden" name="target" value="<?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="搜索文件名或地址">
        <button class="btn btn-primary" type="submit">搜索</button>
    </form>
</div>
<div class="media-grid">
    <?php foreach ($media as $item): ?>
        <article class="media-card">
            <div class="media-card__thumb"><img src="<?= htmlspecialchars($item['file_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['alt_text'] ?: $item['file_name'], ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="media-card__body">
                <strong><?= htmlspecialchars($item['file_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <small><?= htmlspecialchars($item['folder'], ENT_QUOTES, 'UTF-8') ?> · <?= (int) $item['file_size'] ?> bytes</small>
                <input type="text" readonly value="<?= htmlspecialchars($item['file_url'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="media-card__actions">
                    <button class="btn btn-ghost media-copy-btn" type="button" data-copy-url="<?= htmlspecialchars($item['file_url'], ENT_QUOTES, 'UTF-8') ?>">复制地址</button>
                    <?php if ($isPicker): ?>
                        <button class="btn btn-primary" type="button" onclick="window.YuexiaMediaPicker && window.YuexiaMediaPicker.pickFromPopup('<?= htmlspecialchars($item['file_url'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8') ?>');">选择</button>
                    <?php else: ?>
                        <a class="btn btn-primary" href="<?= htmlspecialchars($item['file_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">预览</a>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/admin/media?page=<?= $p ?>&q=<?= urlencode($pagination['query']) ?><?= $isPicker ? '&picker=1&target=' . urlencode($target) . '&mode=' . urlencode($mode) : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
