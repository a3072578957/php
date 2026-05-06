<section class="admin-page-head">
    <h1>控制台概览</h1>
    <p>欢迎回来，<?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>。当前身份是 <?= htmlspecialchars(\Core\Controller::roleLabel((string) $adminRole), ENT_QUOTES, 'UTF-8') ?>，这里可以快速查看当前内容数量。</p>
</section>
<div class="admin-stats admin-stats--wide">
    <article class="admin-stat-card"><strong><?= (int) $adminUserCount ?></strong><span>管理员数量</span></article>
    <article class="admin-stat-card"><strong><?= (int) $articleCount ?></strong><span>文章数量</span></article>
    <article class="admin-stat-card"><strong><?= (int) $commentCount ?></strong><span>评论总数</span></article>
    <article class="admin-stat-card"><strong><?= (int) $pendingCommentCount ?></strong><span>待审评论</span></article>
    <article class="admin-stat-card"><strong><?= (int) $portfolioCount ?></strong><span>作品数量</span></article>
    <article class="admin-stat-card"><strong><?= (int) $messageCount ?></strong><span>留言总数</span></article>
    <article class="admin-stat-card"><strong><?= (int) $pendingMessageCount ?></strong><span>待审留言</span></article>
    <article class="admin-stat-card"><strong><?= (int) $articleCategoryCount ?></strong><span>文章分类</span></article>
    <article class="admin-stat-card"><strong><?= (int) $portfolioCategoryCount ?></strong><span>作品分类</span></article>
    <article class="admin-stat-card"><strong><?= (int) $tagCount ?></strong><span>文章标签</span></article>
    <article class="admin-stat-card"><strong><?= (int) $mediaCount ?></strong><span>图片资源</span></article>
    <article class="admin-stat-card"><strong><?= (int) $logCount ?></strong><span>操作日志</span></article>
</div>
