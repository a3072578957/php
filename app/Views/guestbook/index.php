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
            <span class="eyebrow">Guestbook</span>
            <h1>访客留言板</h1>
            <p>这里收集来自访问者的留言和问候，提交后会进入后台审核，审核通过后公开展示。</p>
        </div>
        <div class="wrap guestbook-grid">
            <section class="guestbook-form-card">
                <span class="eyebrow">Leave A Message</span>
                <h2>写一条公开留言</h2>
                <p>适合放问候、建议、合作意向或对站点内容的反馈。</p>
                <form method="post" action="/guestbook" class="admin-form admin-form--comment">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <label><span>昵称</span><input type="text" name="nickname" maxlength="80" required></label>
                    <label><span>邮箱</span><input type="email" name="email" maxlength="160" placeholder="选填"></label>
                    <label><span>主题</span><input type="text" name="subject" maxlength="160" placeholder="选填"></label>
                    <label><span>留言内容</span><textarea name="content" rows="7" maxlength="2000" required></textarea></label>
                    <button class="btn btn-primary" type="submit">提交留言</button>
                </form>
            </section>
            <section class="guestbook-list-block">
                <div class="page-head page-head--compact">
                    <span class="eyebrow">Approved Messages</span>
                    <h2>已公开留言</h2>
                    <p>这些内容已经在后台通过审核。</p>
                </div>
                <div class="guestbook-list">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                            <article class="guestbook-card reveal-up">
                                <div class="guestbook-card__head">
                                    <div>
                                        <strong><?= htmlspecialchars($message['nickname'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <?php if (!empty($message['subject'])): ?>
                                            <span><?= htmlspecialchars($message['subject'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <small><?= htmlspecialchars(substr($message['created_at'], 0, 16), ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                                <p><?= nl2br(htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state empty-state--left">暂时还没有公开留言，欢迎成为第一位访客。</div>
                    <?php endif; ?>
                </div>
                <?php if (($pagination['pages'] ?? 1) > 1): ?>
                    <div class="pagination">
                        <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                            <a class="pagination-link <?= $p === (int) $pagination['page'] ? 'is-active' : '' ?>" href="/guestbook?page=<?= $p ?>"><?= $p ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </section>
</div>
