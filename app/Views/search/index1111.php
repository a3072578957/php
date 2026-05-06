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
                <a href="/admin/login">后台</a>
            </nav>
        </div>
    </header>
    <section class="page-section">
        <div class="wrap page-head">
            <span class="eyebrow">Global Search</span>
            <h1>全站搜索</h1>
            <p>在文章和作品之间统一搜索，并继续按类型、分类和标签进行筛选。</p>
        </div>
        <div class="wrap filter-card">
            <form method="get" action="/search" class="filter-form filter-form--search">
                <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="搜索标题、摘要、正文或技术栈">
                <select name="type">
                    <option value="all" <?= ($pagination['type'] ?? 'all') === 'all' ? 'selected' : '' ?>>全部类型</option>
                    <option value="article" <?= ($pagination['type'] ?? '') === 'article' ? 'selected' : '' ?>>仅文章</option>
                    <option value="portfolio" <?= ($pagination['type'] ?? '') === 'portfolio' ? 'selected' : '' ?>>仅作品</option>
                </select>
                <select name="category">
                    <option value="">全部分类</option>
                    <?php foreach ($categoryOptions as $option): ?>
                        <option value="<?= htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pagination['category_filter'] ?? '') === $option['value'] ? 'selected' : '' ?>><?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="tag">
                    <option value="">全部标签</option>
                    <?php foreach ($tags as $tag): ?>
                        <option value="<?= htmlspecialchars($tag['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pagination['tag'] ?? '') === $tag['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">搜索</button>
            </form>
        </div>
        <div class="wrap content-grid content-grid--search">
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $item): ?>
                <article class="content-card reveal-up">
                    <span class="content-meta"><?= ($item['item_type'] ?? '') === 'article' ? '文章' : '作品' ?> · <?= htmlspecialchars($item['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($item['summary'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="content-card__meta-links">
                        <?php if (!empty($item['category_slug']) && !empty($item['item_type'])): ?>
                            <a class="tag-link" href="/categories/<?= htmlspecialchars($item['item_type'], ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($item['category_slug'], ENT_QUOTES, 'UTF-8') ?>">分类页</a>
                        <?php endif; ?>
                        <?php if (($item['item_type'] ?? '') === 'article' && !empty($item['tags'])): ?>
                            <?php foreach ($item['tags'] as $tag): ?>
                                <a class="tag-link" href="/search?type=article&tag=<?= htmlspecialchars($tag['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8') ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <a class="text-link" href="<?= ($item['item_type'] ?? '') === 'article' ? '/articles/' : '/portfolio/' ?><?= htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8') ?>">查看详情</a>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">没有找到符合条件的内容。</div>
            <?php endif; ?>
        </div>
        <?php if (($pagination['pages'] ?? 1) > 1): ?>
            <div class="wrap pagination">
                <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                    <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/search?page=<?= $p ?>&q=<?= urlencode($pagination['query']) ?>&type=<?= urlencode($pagination['type'] ?? 'all') ?>&category=<?= urlencode($pagination['category_filter'] ?? '') ?>&tag=<?= urlencode($pagination['tag'] ?? '') ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
