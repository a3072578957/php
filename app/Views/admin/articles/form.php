<section class="admin-page-head">
    <h1><?= $article ? '编辑文章' : '新增文章' ?></h1>
</section>
<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="admin-form admin-form--wide" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <label><span>标题</span><input type="text" name="title" value="<?= htmlspecialchars($article['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></label>
    <label>
        <span>分类</span>
        <select name="category_id" required>
            <option value="">请选择分类</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (int) ($article['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label><span>标签</span><input type="text" name="tags" list="article-tags-list" value="<?= htmlspecialchars($tagText ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="多个标签用英文逗号或中文逗号分隔"></label>
    <datalist id="article-tags-list">
        <?php foreach ($allTags as $tag): ?>
            <option value="<?= htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8') ?>"></option>
        <?php endforeach; ?>
    </datalist>
    <label><span>摘要</span><textarea name="excerpt" rows="4" required><?= htmlspecialchars($article['excerpt'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></label>
    <div class="media-field-group">
        <label><span>封面图 URL</span><input type="text" id="article-cover-field" name="cover" value="<?= htmlspecialchars($article['cover'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
        <div class="media-field-actions">
            <button class="btn btn-ghost media-open-btn" type="button" data-media-target="article-cover-field" data-media-mode="field">从图片库选择</button>
        </div>
    </div>
    <label><span>或上传封面图</span><input type="file" name="cover_upload" accept=".jpg,.jpeg,.png,.gif,.webp"></label>
    <div class="media-preview" data-preview-for="article-cover-field">
        <?php if (!empty($article['cover'])): ?>
            <img src="<?= htmlspecialchars($article['cover'], ENT_QUOTES, 'UTF-8') ?>" alt="cover preview">
        <?php endif; ?>
    </div>
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
            <button type="button" data-editor-command="image">图片</button><button class="media-open-btn" type="button" data-media-target="article-content-editor" data-media-mode="editor">图片库</button>
            <button type="button" data-editor-command="code">代码</button>
            <button type="button" data-editor-command="preview">预览</button>
        </div>
        <label><span>正文</span><textarea id="article-content-editor" name="content" class="rich-editor__input" rows="14" required><?= htmlspecialchars($article['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></label>
        <div class="rich-editor__preview" hidden></div>
    </div>
    <div class="admin-form__actions">
        <button class="btn btn-primary" type="submit">保存文章</button>
        <a class="btn btn-ghost" href="/admin/articles">返回列表</a>
    </div>
</form>

