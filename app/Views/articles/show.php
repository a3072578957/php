<div class="noise-layer"></div>
<div class="site-shell page-shell-alt">
    <header class="site-header is-scrolled">
        <div class="wrap header-inner">
            <a href="/" class="brand">
                <span class="brand-mark">YX</span>
                <span class="brand-copy"><strong><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8') ?></small></span>
            </a>
            <nav class="site-nav">
                <a href="/">首页</a>
                <a href="/articles">文章</a>
                <a href="/portfolio">作品</a>
                <a href="/search">搜索</a>
                <a href="/guestbook">留言</a>
                <a href="/admin/login">后台</a>
            </nav>
        </div>
    </header>
    <section class="page-section article-detail">
        <div class="wrap article-shell">
            <span class="eyebrow">Article Detail</span>
            <h1><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="article-meta">
                <?php if (!empty($article['category_slug'])): ?>
                    <a href="/categories/article/<?= htmlspecialchars($article['category_slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($article['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8') ?></a>
                    ·
                <?php endif; ?>
                <?= htmlspecialchars(substr($article['created_at'], 0, 10), ENT_QUOTES, 'UTF-8') ?>
            </p>
            <?php if (!empty($article['cover'])): ?>
                <div class="detail-image" style="background-image:url('<?= htmlspecialchars($article['cover'], ENT_QUOTES, 'UTF-8') ?>');"></div>
            <?php endif; ?>
            <?php if (!empty($article['tags'])): ?>
                <div class="content-card__meta-links content-card__meta-links--detail">
                    <?php foreach ($article['tags'] as $tag): ?>
                        <a class="tag-link" href="/search?type=article&tag=<?= htmlspecialchars($tag['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="detail-body rich-content">
                <?= $article['content'] ?>
            </div>
            <div class="section-action">
                <a class="btn btn-primary" href="/articles">返回文章列表</a>
                <a class="btn btn-ghost" href="/search?type=article&q=<?= urlencode($article['title']) ?>">搜索相关文章</a>
            </div>
        </div>
    </section>

    <section class="page-section comments-section" id="comments">
        <div class="wrap comments-grid">
            <div class="comments-block">
                <div class="page-head page-head--compact">
                    <span class="eyebrow">Comments</span>
                    <h2>文章评论</h2>
                    <p>评论提交后会先进入审核队列，审核通过后展示在这里。管理员回复会以单独样式显示。</p>
                </div>
                <div class="comments-list">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <article class="comment-card">
                                <div class="comment-card__head">
                                    <strong><?= htmlspecialchars($comment['nickname'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars(substr($comment['created_at'], 0, 16), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <p><?= nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                                <?php if (!empty($comment['replies'])): ?>
                                    <div class="comment-replies">
                                        <?php foreach ($comment['replies'] as $reply): ?>
                                            <article class="comment-card comment-card--reply">
                                                <div class="comment-card__head">
                                                    <strong><?= htmlspecialchars($reply['nickname'], ENT_QUOTES, 'UTF-8') ?><span class="comment-role-label">管理员回复</span></strong>
                                                    <span><?= htmlspecialchars(substr($reply['created_at'], 0, 16), ENT_QUOTES, 'UTF-8') ?></span>
                                                </div>
                                                <p><?= nl2br(htmlspecialchars($reply['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state empty-state--left">这篇文章还没有公开评论，欢迎来留下第一条看法。</div>
                    <?php endif; ?>
                </div>
            </div>
            <aside class="comment-form-card">
                <span class="eyebrow">Leave A Comment</span>
                <h3>写下你的留言</h3>
                <p>昵称和内容为必填项，邮箱仅用于联系，不会公开显示。</p>
                <form method="post" action="/articles/<?= htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8') ?>/comments" class="admin-form admin-form--comment">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <label><span>昵称</span><input type="text" name="nickname" maxlength="80" required></label>
                    <label><span>邮箱</span><input type="email" name="email" maxlength="160" placeholder="选填"></label>
                    <label><span>评论内容</span><textarea name="content" rows="7" maxlength="2000" required></textarea></label>
                    <button class="btn btn-primary" type="submit">提交评论</button>
                </form>
            </aside>
        </div>
    </section>
</div>
