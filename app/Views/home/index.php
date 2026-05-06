<div class="noise-layer"></div>
<div class="site-shell">
    <header class="site-header" id="top">
        <div class="wrap header-inner">
            <a href="#top" class="brand">
                <span class="brand-mark">YX</span>
                <span class="brand-copy">
                    <strong><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8') ?></small>
                </span>
            </a>
            <nav class="site-nav">
                <a href="#about">关于</a>
                <a href="#articles">文章</a>
                <a href="#portfolio">作品</a>
                <a href="#story">旅程</a>
                <a href="/search">搜索</a>
                <a href="/guestbook">留言</a>
                <a href="/admin">后台</a>
            </nav>
        </div>
    </header>

    <section class="hero-section">
        <div class="sky sky-a"></div>
        <div class="sky sky-b"></div>
        <div class="sky sky-c"></div>
        <div class="star-field"></div>
        <div class="wrap hero-grid">
            <div class="hero-copy reveal-up">
                <span class="eyebrow">Yuexia Personal Space</span>
                <h1>一个带有月下氛围、霓光层次和绚丽动效的 PHP 个人网站。</h1>
                <p>这个项目不依赖 Composer，不使用现成框架，而是用自研传统 PHP 架构来支撑页面渲染、路由分发、文章系统、作品集模块、多管理员后台、评论审核和访客留言。前端完全采用 jQuery、HTML、CSS 来实现氛围感和动态体验。</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="#articles">查看文章系统</a>
                    <a class="btn btn-ghost" href="/guestbook">访客留言板</a>
                </div>
                <div class="hero-stats">
                    <?php foreach ($highlights as $highlight): ?>
                    <article class="stat-card">
                        <strong class="counter" data-target="<?= (int) $highlight['value'] ?>">0</strong>
                        <span><?= htmlspecialchars($highlight['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hero-stage reveal-up" data-depth-zone>
                <div class="banner-shell">
                    <div class="banner-track">
                        <?php foreach ($heroSlides as $index => $slide): ?>
                        <article class="banner-card<?= $index === 0 ? ' is-active' : '' ?>">
                            <div class="banner-card__glow"></div>
                            <span class="banner-card__eyebrow"><?= htmlspecialchars($slide['eyebrow'], ENT_QUOTES, 'UTF-8') ?></span>
                            <h2><?= htmlspecialchars($slide['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p><?= htmlspecialchars($slide['description'], ENT_QUOTES, 'UTF-8') ?></p>
                            <a class="btn btn-primary" href="<?= htmlspecialchars($slide['anchor'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($slide['button'], ENT_QUOTES, 'UTF-8') ?></a>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="banner-controls">
                        <button class="banner-arrow banner-prev" type="button" aria-label="上一张">&#10094;</button>
                        <div class="banner-dots"></div>
                        <button class="banner-arrow banner-next" type="button" aria-label="下一张">&#10095;</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section about-section" id="about">
        <div class="wrap split-grid">
            <div class="section-copy reveal-up">
                <span class="eyebrow">自研传统框架</span>
                <h2>不用 ThinkPHP，不靠 Composer，也能把个人站做成一个可维护的小型内容平台。</h2>
                <p>Yuexia 当前已经具备前台文章、作品展示、评论留言和后台内容管理。框架层保持清晰：入口文件、路由器、请求对象、控制器、视图层、CSRF 防护和 MySQL 数据层全部独立，适合继续往下迭代。</p>
            </div>
            <div class="feature-list">
                <?php foreach ($features as $key => $feature): ?>
                <article class="feature-card reveal-up">
                    <span class="feature-index">0<?= $key + 1 ?></span>
                    <h3><?= htmlspecialchars($feature['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($feature['description'], ENT_QUOTES, 'UTF-8') ?></p>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section content-section" id="articles">
        <div class="wrap">
            <div class="section-head reveal-up">
                <span class="eyebrow">文章系统</span>
                <h2>最近文章已经接入真实数据源，前台列表、详情页和评论区都可以正常扩展。</h2>
            </div>
            <div class="content-grid">
                <?php foreach ($latestArticles as $article): ?>
                <article class="content-card reveal-up">
                    <span class="content-meta"><?= htmlspecialchars(substr($article['created_at'], 0, 10), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($article['excerpt'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="text-link" href="/articles/<?= htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8') ?>">阅读全文</a>
                </article>
                <?php endforeach; ?>
            </div>
            <div class="section-action reveal-up">
                <a class="btn btn-primary" href="/articles">进入文章列表</a>
                <a class="btn btn-ghost" href="/search?type=article">搜索文章</a>
            </div>
        </div>
    </section>

    <section class="section works-section" id="portfolio">
        <div class="wrap">
            <div class="section-head reveal-up">
                <span class="eyebrow">作品集</span>
                <h2>作品模块现在支持公开列表和独立详情页，适合后续继续扩成完整作品库。</h2>
            </div>
            <div class="content-grid">
                <?php foreach ($latestPortfolio as $work): ?>
                <article class="content-card reveal-up">
                    <span class="content-meta"><?= htmlspecialchars($work['stack'], ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($work['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="text-link" href="/portfolio/<?= htmlspecialchars($work['slug'], ENT_QUOTES, 'UTF-8') ?>">查看详情</a>
                </article>
                <?php endforeach; ?>
            </div>
            <div class="section-action reveal-up">
                <a class="btn btn-primary" href="/portfolio">进入作品列表</a>
                <a class="btn btn-ghost" href="/search?type=portfolio">搜索作品</a>
            </div>
        </div>
    </section>

    <section class="section timeline-section" id="story">
        <div class="wrap">
            <div class="section-head reveal-up">
                <span class="eyebrow">成长路径</span>
                <h2>一个好的个人站，不只是展示信息，更应该像在讲自己的持续演化过程。</h2>
            </div>
            <div class="timeline-list">
                <?php foreach ($timeline as $item): ?>
                <article class="timeline-item reveal-up">
                    <div class="timeline-year"><?= htmlspecialchars($item['year'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="timeline-body">
                        <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section contact-section" id="contact">
        <div class="wrap contact-card reveal-up">
            <div>
                <span class="eyebrow">Admin Ready</span>
                <h2>现在你已经可以登录后台，对文章、作品、管理员、评论、留言、分类、标签和图片做统一管理。</h2>
                <p>默认后台地址是 `/admin/login`。现在已经接入 MySQL、CSRF 防护、本地 jQuery、后台图片库、富文本编辑、前台全站搜索、多用户管理员和评论留言审核。</p>
            </div>
            <div class="contact-actions">
                <a class="btn btn-primary" href="/admin/login">进入后台</a>
                <a class="btn btn-ghost" href="/guestbook">查看留言板</a>
            </div>
        </div>
    </section>
</div>
