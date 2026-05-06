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
            <span class="eyebrow">Portfolio</span>
            <h1>作品集列表</h1>
            <p>这里展示 Yuexia 当前的全部作品项目，并支持按关键词和分类筛选。</p>
        </div>
        <div class="wrap filter-card">
            <form method="get" action="/portfolio" class="filter-form">
                <input type="search" name="q" value="<?= htmlspecialchars($pagination['query'], ENT_QUOTES, 'UTF-8') ?>" placeholder="搜索作品标题或介绍">
                <select name="category">
                    <option value="">全部分类</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pagination['category'] ?? '') === $category['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">筛选</button>
            </form>
        </div>
        <div class="wrap content-grid">
            <?php if (!empty($portfolio)): ?>
                <?php foreach ($portfolio as $work): ?>
                <article class="content-card reveal-up">
                    <span class="content-meta"><a href="/categories/portfolio/<?= htmlspecialchars($work['category_slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($work['category_name'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></a> · <?= htmlspecialchars($work['stack'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($work['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="text-link" href="/portfolio/<?= htmlspecialchars($work['slug'], ENT_QUOTES, 'UTF-8') ?>">查看详情</a>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">没有找到符合条件的作品。</div>
            <?php endif; ?>
        </div>
        <?php if (($pagination['pages'] ?? 1) > 1): ?>
            <div class="wrap pagination">
                <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                    <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/portfolio?page=<?= $p ?>&q=<?= urlencode($pagination['query']) ?>&category=<?= urlencode($pagination['category']) ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

