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
    <section class="page-section">
        <div class="wrap page-head">
            <span class="eyebrow">Articles</span>
            <h1>文章列表</h1>
            <p>这里展示 Yuexia 当前的全部文章内容，并支持按关键词、分类和标签筛选。</p>
        </div>
        <div class="wrap filter-card">
            <form method="get" action="/articles" class="filter-form filter-form--search">
                <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="搜索文章标题或内容">
                <select name="category">
                    <option value="">全部分类</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pagination['category'] ?? '') === $category['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="tag">
                    <option value="">全部标签</option>
                    <?php foreach ($tags as $tag): ?>
                        <option value="<?= htmlspecialchars($tag['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pagination['tag'] ?? '') === $tag['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">筛选</button>
            </form>
        </div>
        <div class="wrap content-grid">
            <?php if (!empty($articles)): ?>
                <?php foreach ($articles as $article): ?>
                <article class="content-card reveal-up">
                    <span class="content-meta"><a href="/categories/article/<?= htmlspecialchars($article['category_slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($article['category_name'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></a> · <?= htmlspecialchars(substr($article['created_at'], 0, 10), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($article['excerpt'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if (!empty($article['tags'])): ?>
                        <div class="content-card__meta-links">
                            <?php foreach ($article['tags'] as $tag): ?>
                                <a class="tag-link" href="/articles?tag=<?= htmlspecialchars($tag['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8') ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <a class="text-link" href="/articles/<?= htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8') ?>">阅读全文</a>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">没有找到符合条件的文章。</div>
            <?php endif; ?>
        </div>
        <?php if (($pagination['pages'] ?? 1) > 1): ?>
            <div class="wrap pagination">
                <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                    <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/articles?page=<?= $p ?>&q=<?= urlencode($pagination['query']) ?>&category=<?= urlencode($pagination['category']) ?>&tag=<?= urlencode($pagination['tag'] ?? '') ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

