<section class="admin-page-head">
    <h1><?= $work ? '编辑作品' : '新增作品' ?></h1>
</section>
<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="admin-form admin-form--wide" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <label><span>标题</span><input type="text" name="title" value="<?= htmlspecialchars($work['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></label>
    <label>
        <span>分类</span>
        <select name="category_id" required>
            <option value="">请选择分类</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (int) ($work['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label><span>摘要</span><textarea name="summary" rows="4" required><?= htmlspecialchars($work['summary'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></label>
    <label><span>技术栈</span><input type="text" name="stack" value="<?= htmlspecialchars($work['stack'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
    <div class="media-field-group">
        <label><span>图片 URL</span><input type="text" id="portfolio-image-field" name="image" value="<?= htmlspecialchars($work['image'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
        <div class="media-field-actions">
            <button class="btn btn-ghost media-open-btn" type="button" data-media-target="portfolio-image-field" data-media-mode="field">从图片库选择</button>
        </div>
    </div>
    <label><span>或上传图片</span><input type="file" name="image_upload" accept=".jpg,.jpeg,.png,.gif,.webp"></label>
    <div class="media-preview" data-preview-for="portfolio-image-field">
        <?php if (!empty($work['image'])): ?>
            <img src="<?= htmlspecialchars($work['image'], ENT_QUOTES, 'UTF-8') ?>" alt="portfolio preview">
        <?php endif; ?>
    </div>
    <label><span>外链地址</span><input type="text" name="link" value="<?= htmlspecialchars($work['link'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
    <div class="rich-editor" data-rich-editor>
        <div class="rich-editor__toolbar">
            <button type="button" data-editor-command="p">段落</button>
            <button type="button" data-editor-command="h2">H2</button>
            <button type="button" data-editor-command="h3">H3</button>
            <button type="button" data-editor-command="strong">加粗</button>
            <button type="button" data-editor-command="em">斜体</button>
            <button type="button" data-editor-command="quote">引用</button>
            <button type="button" data-editor-command="ul">列表</button>
            <button type="button" data-editor-command="link">链接</button>
            <button type="button" data-editor-command="image">图片</button><button class="media-open-btn" type="button" data-media-target="portfolio-content-editor" data-media-mode="editor">图片库</button>
            <button type="button" data-editor-command="code">代码</button>
            <button type="button" data-editor-command="preview">预览</button>
        </div>
        <label><span>正文</span><textarea id="portfolio-content-editor" name="content" class="rich-editor__input" rows="14" required><?= htmlspecialchars($work['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></label>
        <div class="rich-editor__preview" hidden></div>
    </div>
    <div class="admin-form__actions">
        <button class="btn btn-primary" type="submit">保存作品</button>
        <a class="btn btn-ghost" href="/admin/portfolio">返回列表</a>
    </div>
</form>

