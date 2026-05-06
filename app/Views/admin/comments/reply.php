<section class="admin-page-head">
    <h1>回复评论</h1>
    <p>管理员回复会直接公开展示在文章评论区，样式会与访客评论区分。</p>
</section>
<div class="reply-thread-card">
    <div class="reply-thread-card__label">原始评论</div>
    <strong><?= htmlspecialchars($comment['nickname'], ENT_QUOTES, 'UTF-8') ?></strong>
    <p><?= nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')) ?></p>
</div>
<form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="admin-form admin-form--wide">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <label><span>回复内容</span><textarea name="content" rows="8" maxlength="2000" required></textarea></label>
    <div class="admin-form__actions">
        <button class="btn btn-primary" type="submit">发布回复</button>
        <a class="btn btn-ghost" href="/admin/comments">返回评论列表</a>
    </div>
</form>
