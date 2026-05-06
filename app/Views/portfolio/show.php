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
            <span class="eyebrow">Portfolio Detail</span>
            <h1><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="article-meta">
                <?php if (!empty($work['category_slug'])): ?>
                    <a href="/categories/portfolio/<?= htmlspecialchars($work['category_slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($work['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8') ?></a>
                    ·
                <?php endif; ?>
                <?= htmlspecialchars($work['stack'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <?php if (!empty($work['image'])): ?>
                <div class="detail-image" style="background-image:url('<?= htmlspecialchars($work['image'], ENT_QUOTES, 'UTF-8') ?>');"></div>
            <?php endif; ?>
            <div class="detail-body rich-content">
                <?= $work['content'] ?>
                <?php if (!empty($work['link'])): ?>
                    <p><a class="text-link" href="<?= htmlspecialchars($work['link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">打开项目链接</a></p>
                <?php endif; ?>
            </div>
            <div class="section-action">
                <a class="btn btn-primary" href="/portfolio">返回作品列表</a>
                <a class="btn btn-ghost" href="/search?type=portfolio&q=<?= urlencode($work['title']) ?>">搜索相关作品</a>
            </div>
        </div>
    </section>
</div>

