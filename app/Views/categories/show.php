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
            <span class="eyebrow"><?= $type === 'article' ? 'Article Category' : 'Portfolio Category' ?></span>
            <h1><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= $type === 'article' ? '这个分类页支持关键词和标签继续筛选文章。' : '这个分类页支持关键词继续筛选作品。' ?></p>
        </div>
        <div class="wrap filter-card">
            <form method="get" action="/categories/<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>" class="filter-form <?= $type === 'article' ? 'filter-form--search' : '' ?>">
                <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="输入关键词继续筛选">
                <?php if ($type === 'article'): ?>
                    <select name="tag">
                        <option value="">全部标签</option>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?= htmlspecialchars($tag['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pagination['tag'] ?? '') === $tag['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <button class="btn btn-primary" type="submit">筛选</button>
            </form>
        </div>
        <div class="wrap content-grid">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                <article class="content-card reveal-up">
                    <span class="content-meta"><?= htmlspecialchars($item['category_name'] ?? $category['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($type === 'article' ? ($item['excerpt'] ?? '') : ($item['summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if ($type === 'article' && !empty($item['tags'])): ?>
                        <div class="content-card__meta-links">
                            <?php foreach ($item['tags'] as $tag): ?>
                                <a class="tag-link" href="/categories/article/<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>?tag=<?= htmlspecialchars($tag['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8') ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <a class="text-link" href="<?= $type === 'article' ? '/articles/' : '/portfolio/' ?><?= htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8') ?>">查看详情</a>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">这个分类下暂时没有符合条件的内容。</div>
            <?php endif; ?>
        </div>
        <?php if (($pagination['pages'] ?? 1) > 1): ?>
            <div class="wrap pagination">
                <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                    <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/categories/<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>?page=<?= $p ?>&q=<?= urlencode($pagination['query']) ?><?= $type === 'article' ? '&tag=' . urlencode($pagination['tag'] ?? '') : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

