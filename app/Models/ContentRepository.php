<?php

namespace App\Models;

use Core\Database;
use PDO;

class ContentRepository
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = Database::connection($config);
        $this->seed();
    }

    public function latestArticles(int $limit = 3): array
    {
        $stmt = $this->pdo->prepare('SELECT a.*, c.name AS category_name, c.slug AS category_slug FROM articles a LEFT JOIN categories c ON c.id = a.category_id ORDER BY a.created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $this->attachTagsToArticles($stmt->fetchAll());
    }

    public function latestPortfolio(int $limit = 3): array
    {
        $stmt = $this->pdo->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM portfolio_items p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function articleCount(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
    }

    public function portfolioCount(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM portfolio_items')->fetchColumn();
    }

    public function categoryCount(string $type): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM categories WHERE type = :type');
        $stmt->execute(['type' => $type]);
        return (int) $stmt->fetchColumn();
    }

    public function tagCount(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM tags')->fetchColumn();
    }

    public function articleCategories(): array
    {
        return $this->categoriesByType('article');
    }

    public function portfolioCategories(): array
    {
        return $this->categoriesByType('portfolio');
    }

    public function categoriesByType(string $type): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE type = :type ORDER BY name ASC');
        $stmt->execute(['type' => $type]);
        return $stmt->fetchAll();
    }

    public function findCategoryByTypeAndSlug(string $type, string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE type = :type AND slug = :slug LIMIT 1');
        $stmt->execute(['type' => $type, 'slug' => $slug]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function categoryExistsOfType(int $id, string $type): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = :id AND type = :type');
        $stmt->execute(['id' => $id, 'type' => $type]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function categoryNameExists(string $type, string $name, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE type = :type AND name = :name';
        $params = ['type' => $type, 'name' => $name];

        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function categoryUsageSummary(int $id): array
    {
        $articleStmt = $this->pdo->prepare('SELECT COUNT(*) FROM articles WHERE category_id = :id');
        $articleStmt->execute(['id' => $id]);
        $articleCount = (int) $articleStmt->fetchColumn();

        $portfolioStmt = $this->pdo->prepare('SELECT COUNT(*) FROM portfolio_items WHERE category_id = :id');
        $portfolioStmt->execute(['id' => $id]);
        $portfolioCount = (int) $portfolioStmt->fetchColumn();

        return [
            'articles' => $articleCount,
            'portfolio' => $portfolioCount,
            'total' => $articleCount + $portfolioCount,
        ];
    }

    public function allTags(): array
    {
        $stmt = $this->pdo->query('SELECT t.*, COUNT(at.article_id) AS usage_count FROM tags t LEFT JOIN article_tag at ON at.tag_id = t.id GROUP BY t.id ORDER BY t.name ASC');
        return $stmt->fetchAll();
    }

    public function paginateTags(string $query = '', int $page = 1, int $perPage = 15): array
    {
        $whereSql = '';
        $params = [];
        if ($query !== '') {
            $whereSql = ' WHERE (t.name LIKE :q OR t.slug LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM tags t' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT t.*, COUNT(at.article_id) AS usage_count FROM tags t LEFT JOIN article_tag at ON at.tag_id = t.id' . $whereSql . ' GROUP BY t.id ORDER BY t.name ASC LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'query' => $query,
        ];
    }

    public function findTagById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tags WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function findTagBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tags WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function tagNameExists(string $name, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM tags WHERE name = :name';
        $params = ['name' => $name];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function tagUsageCount(int $id): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM article_tag WHERE tag_id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public function createTag(array $data): void
    {
        $name = trim((string) ($data['name'] ?? ''));
        $stmt = $this->pdo->prepare('INSERT INTO tags (name, slug, created_at, updated_at) VALUES (:name, :slug, :created_at, :updated_at)');
        $stmt->execute([
            'name' => $name,
            'slug' => $this->makeUniqueSlug($name, 'tag', 'tags'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateTag(int $id, array $data): void
    {
        $name = trim((string) ($data['name'] ?? ''));
        $stmt = $this->pdo->prepare('UPDATE tags SET name = :name, slug = :slug, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'slug' => $this->makeUniqueSlug($name, 'tag', 'tags', $id),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteTag(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM tags WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function articleTags(int $articleId): array
    {
        $stmt = $this->pdo->prepare('SELECT t.* FROM tags t INNER JOIN article_tag at ON at.tag_id = t.id WHERE at.article_id = :article_id ORDER BY t.name ASC');
        $stmt->execute(['article_id' => $articleId]);
        return $stmt->fetchAll();
    }

    public function articleTagText(int $articleId): string
    {
        return implode(', ', array_map(static fn(array $tag): string => $tag['name'], $this->articleTags($articleId)));
    }

    public function paginateArticles(string $query = '', string $categorySlug = '', int $page = 1, int $perPage = 6, string $tagSlug = ''): array
    {
        return $this->searchArticles($query, $categorySlug, $page, $perPage, $tagSlug, 'article', $this->buildCategoryFilterValue('article', $categorySlug));
    }

    public function paginatePortfolio(string $query = '', string $categorySlug = '', int $page = 1, int $perPage = 6): array
    {
        return $this->searchPortfolio($query, $categorySlug, $page, $perPage, 'portfolio', $this->buildCategoryFilterValue('portfolio', $categorySlug));
    }

    public function paginateAdminArticles(string $query = '', int $categoryId = 0, int $page = 1, int $perPage = 10): array
    {
        return $this->paginateAdminContent('articles', 'article', $query, $categoryId, $page, $perPage, 'excerpt');
    }

    public function paginateAdminPortfolio(string $query = '', int $categoryId = 0, int $page = 1, int $perPage = 10): array
    {
        return $this->paginateAdminContent('portfolio_items', 'portfolio', $query, $categoryId, $page, $perPage, 'summary');
    }

    public function paginateCategories(string $type = '', string $query = '', int $page = 1, int $perPage = 12): array
    {
        $where = [];
        $params = [];

        if ($type !== '') {
            $where[] = 'type = :type';
            $params['type'] = $type;
        }
        if ($query !== '') {
            $where[] = '(name LIKE :q OR slug LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM categories' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT * FROM categories' . $whereSql . ' ORDER BY type ASC, name ASC LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'query' => $query,
            'type' => $type,
        ];
    }

    public function findArticleBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT a.*, c.name AS category_name, c.slug AS category_slug FROM articles a LEFT JOIN categories c ON c.id = a.category_id WHERE a.slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $item = $stmt->fetch();
        if (!$item) {
            return null;
        }

        $item['tags'] = $this->articleTags((int) $item['id']);
        $item['tag_text'] = implode(', ', array_map(static fn(array $tag): string => $tag['name'], $item['tags']));
        return $item;
    }

    public function findPortfolioBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM portfolio_items p LEFT JOIN categories c ON c.id = p.category_id WHERE p.slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function findArticleById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();
        if (!$item) {
            return null;
        }

        $item['tags'] = $this->articleTags((int) $item['id']);
        $item['tag_text'] = implode(', ', array_map(static fn(array $tag): string => $tag['name'], $item['tags']));
        return $item;
    }

    public function findPortfolioById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM portfolio_items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function findCategoryById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function createArticle(array $data): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO articles (category_id, title, slug, excerpt, content, cover, created_at, updated_at) VALUES (:category_id, :title, :slug, :excerpt, :content, :cover, :created_at, :updated_at)');
        $stmt->execute([
            'category_id' => $this->nullableInt($data['category_id'] ?? null),
            'title' => trim((string) ($data['title'] ?? '')),
            'slug' => $this->makeUniqueSlug(trim((string) ($data['title'] ?? '')), 'article', 'articles'),
            'excerpt' => trim((string) ($data['excerpt'] ?? '')),
            'content' => trim((string) ($data['content'] ?? '')),
            'cover' => $this->nullableString($data['cover'] ?? null),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $articleId = (int) $this->pdo->lastInsertId();
        $this->syncArticleTags($articleId, (string) ($data['tags'] ?? ''));
    }

    public function updateArticle(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE articles SET category_id = :category_id, title = :title, slug = :slug, excerpt = :excerpt, content = :content, cover = :cover, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'category_id' => $this->nullableInt($data['category_id'] ?? null),
            'title' => trim((string) ($data['title'] ?? '')),
            'slug' => $this->makeUniqueSlug(trim((string) ($data['title'] ?? '')), 'article', 'articles', $id),
            'excerpt' => trim((string) ($data['excerpt'] ?? '')),
            'content' => trim((string) ($data['content'] ?? '')),
            'cover' => $this->nullableString($data['cover'] ?? null),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->syncArticleTags($id, (string) ($data['tags'] ?? ''));
    }

    public function deleteArticle(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM articles WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function createPortfolio(array $data): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO portfolio_items (category_id, title, slug, summary, content, stack, image, link, created_at, updated_at) VALUES (:category_id, :title, :slug, :summary, :content, :stack, :image, :link, :created_at, :updated_at)');
        $stmt->execute([
            'category_id' => $this->nullableInt($data['category_id'] ?? null),
            'title' => trim((string) ($data['title'] ?? '')),
            'slug' => $this->makeUniqueSlug(trim((string) ($data['title'] ?? '')), 'work', 'portfolio_items'),
            'summary' => trim((string) ($data['summary'] ?? '')),
            'content' => trim((string) ($data['content'] ?? '')),
            'stack' => $this->nullableString($data['stack'] ?? null),
            'image' => $this->nullableString($data['image'] ?? null),
            'link' => $this->nullableString($data['link'] ?? null),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updatePortfolio(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE portfolio_items SET category_id = :category_id, title = :title, slug = :slug, summary = :summary, content = :content, stack = :stack, image = :image, link = :link, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'category_id' => $this->nullableInt($data['category_id'] ?? null),
            'title' => trim((string) ($data['title'] ?? '')),
            'slug' => $this->makeUniqueSlug(trim((string) ($data['title'] ?? '')), 'work', 'portfolio_items', $id),
            'summary' => trim((string) ($data['summary'] ?? '')),
            'content' => trim((string) ($data['content'] ?? '')),
            'stack' => $this->nullableString($data['stack'] ?? null),
            'image' => $this->nullableString($data['image'] ?? null),
            'link' => $this->nullableString($data['link'] ?? null),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deletePortfolio(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM portfolio_items WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function createCategory(array $data): void
    {
        $type = trim((string) ($data['type'] ?? 'article'));
        $name = trim((string) ($data['name'] ?? ''));

        $stmt = $this->pdo->prepare('INSERT INTO categories (type, name, slug, created_at, updated_at) VALUES (:type, :name, :slug, :created_at, :updated_at)');
        $stmt->execute([
            'type' => $type,
            'name' => $name,
            'slug' => $this->makeUniqueSlug($name, 'category', 'categories', null, $type),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateCategory(int $id, array $data): void
    {
        $type = trim((string) ($data['type'] ?? 'article'));
        $name = trim((string) ($data['name'] ?? ''));

        $stmt = $this->pdo->prepare('UPDATE categories SET type = :type, name = :name, slug = :slug, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'type' => $type,
            'name' => $name,
            'slug' => $this->makeUniqueSlug($name, 'category', 'categories', $id, $type),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteCategory(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function searchContent(string $query = '', string $type = 'all', string $categoryFilter = '', string $tagSlug = '', int $page = 1, int $perPage = 10): array
    {
        $type = in_array($type, ['all', 'article', 'portfolio'], true) ? $type : 'all';
        [$categoryType, $categorySlug] = $this->parseCategoryFilter($categoryFilter);

        if ($type === 'article') {
            return $this->searchArticles($query, $categoryType === 'article' ? $categorySlug : '', $page, $perPage, $tagSlug, $type, $categoryFilter);
        }

        if ($type === 'portfolio') {
            return $this->searchPortfolio($query, $categoryType === 'portfolio' ? $categorySlug : '', $page, $perPage, $type, $categoryFilter);
        }

        if ($tagSlug !== '' || $categoryType === 'article') {
            return $this->searchArticles($query, $categoryType === 'article' ? $categorySlug : '', $page, $perPage, $tagSlug, $type, $categoryFilter);
        }

        if ($categoryType === 'portfolio') {
            return $this->searchPortfolio($query, $categorySlug, $page, $perPage, $type, $categoryFilter);
        }

        return $this->searchEverything($query, $page, $perPage, $type, $categoryFilter, $tagSlug);
    }

    private function searchArticles(string $query, string $categorySlug, int $page, int $perPage, string $tagSlug, string $selectedType, string $categoryFilter): array
    {
        $joins = ' LEFT JOIN categories c ON c.id = a.category_id';
        if ($tagSlug !== '') {
            $joins .= ' INNER JOIN article_tag at ON at.article_id = a.id INNER JOIN tags t ON t.id = at.tag_id';
        }

        $where = [];
        $params = [];
        if ($query !== '') {
            $where[] = '(a.title LIKE :q OR a.excerpt LIKE :q OR a.content LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }
        if ($categorySlug !== '') {
            $where[] = 'c.slug = :category';
            $params['category'] = $categorySlug;
        }
        if ($tagSlug !== '') {
            $where[] = 't.slug = :tag';
            $params['tag'] = $tagSlug;
        }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
        $countStmt = $this->pdo->prepare('SELECT COUNT(DISTINCT a.id) FROM articles a' . $joins . $whereSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT DISTINCT a.*, c.name AS category_name, c.slug AS category_slug FROM articles a' . $joins . $whereSql . ' ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = $this->attachTagsToArticles($stmt->fetchAll());
        foreach ($items as &$item) {
            $item['item_type'] = 'article';
        }
        unset($item);

        return [
            'items' => $items,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'query' => $query,
            'category' => $categorySlug,
            'category_filter' => $categoryFilter,
            'tag' => $tagSlug,
            'type' => $selectedType,
        ];
    }

    private function searchPortfolio(string $query, string $categorySlug, int $page, int $perPage, string $selectedType, string $categoryFilter): array
    {
        $where = [];
        $params = [];
        if ($query !== '') {
            $where[] = '(p.title LIKE :q OR p.summary LIKE :q OR p.content LIKE :q OR p.stack LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }
        if ($categorySlug !== '') {
            $where[] = 'c.slug = :category';
            $params['category'] = $categorySlug;
        }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM portfolio_items p LEFT JOIN categories c ON c.id = p.category_id' . $whereSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM portfolio_items p LEFT JOIN categories c ON c.id = p.category_id' . $whereSql . ' ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll();
        foreach ($items as &$item) {
            $item['item_type'] = 'portfolio';
        }
        unset($item);

        return [
            'items' => $items,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'query' => $query,
            'category' => $categorySlug,
            'category_filter' => $categoryFilter,
            'tag' => '',
            'type' => $selectedType,
        ];
    }

    private function searchEverything(string $query, int $page, int $perPage, string $selectedType, string $categoryFilter, string $tagSlug): array
    {
        $articleWhere = $query !== '' ? ' WHERE (a.title LIKE :article_q OR a.excerpt LIKE :article_q OR a.content LIKE :article_q)' : '';
        $portfolioWhere = $query !== '' ? ' WHERE (p.title LIKE :portfolio_q OR p.summary LIKE :portfolio_q OR p.content LIKE :portfolio_q OR p.stack LIKE :portfolio_q)' : '';
        $params = [];
        if ($query !== '') {
            $params['article_q'] = '%' . $query . '%';
            $params['portfolio_q'] = '%' . $query . '%';
        }

        $union = "
            SELECT 'article' AS item_type, a.id, a.title, a.slug, a.excerpt AS summary, a.created_at, c.name AS category_name, c.slug AS category_slug, NULL AS stack
            FROM articles a
            LEFT JOIN categories c ON c.id = a.category_id
            {$articleWhere}
            UNION ALL
            SELECT 'portfolio' AS item_type, p.id, p.title, p.slug, p.summary AS summary, p.created_at, c.name AS category_name, c.slug AS category_slug, p.stack AS stack
            FROM portfolio_items p
            LEFT JOIN categories c ON c.id = p.category_id
            {$portfolioWhere}
        ";

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM (' . $union . ') AS search_items');
        if ($query !== '') {
            $countStmt->bindValue(':article_q', $params['article_q']);
            $countStmt->bindValue(':portfolio_q', $params['portfolio_q']);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT * FROM (' . $union . ') AS search_items ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        if ($query !== '') {
            $stmt->bindValue(':article_q', $params['article_q']);
            $stmt->bindValue(':portfolio_q', $params['portfolio_q']);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll();
        $articleItems = [];
        foreach ($items as $item) {
            if (($item['item_type'] ?? '') === 'article') {
                $articleItems[] = ['id' => (int) $item['id']];
            }
        }
        $tagMap = $this->buildArticleTagMapFromItems($articleItems);
        foreach ($items as &$item) {
            if (($item['item_type'] ?? '') === 'article') {
                $item['tags'] = $tagMap[(int) $item['id']] ?? [];
            }
        }
        unset($item);

        return [
            'items' => $items,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'query' => $query,
            'category' => '',
            'category_filter' => $categoryFilter,
            'tag' => $tagSlug,
            'type' => $selectedType,
        ];
    }

    private function paginateAdminContent(string $table, string $categoryType, string $query, int $categoryId, int $page, int $perPage, string $summaryField): array
    {
        $where = [];
        $params = [];

        if ($query !== '') {
            $where[] = '(t.title LIKE :q OR t.' . $summaryField . ' LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }
        if ($categoryId > 0) {
            $where[] = 't.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM ' . $table . ' t LEFT JOIN categories c ON c.id = t.category_id AND c.type = :category_type' . $whereSql);
        $countStmt->bindValue(':category_type', $categoryType);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT t.*, c.name AS category_name FROM ' . $table . ' t LEFT JOIN categories c ON c.id = t.category_id AND c.type = :category_type' . $whereSql . ' ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':category_type', $categoryType);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'query' => $query,
            'category_id' => $categoryId,
        ];
    }

    private function syncArticleTags(int $articleId, string $tagText): void
    {
        $tagIds = $this->findOrCreateTagIds($tagText);
        $deleteStmt = $this->pdo->prepare('DELETE FROM article_tag WHERE article_id = :article_id');
        $deleteStmt->execute(['article_id' => $articleId]);

        if ($tagIds === []) {
            return;
        }

        $insertStmt = $this->pdo->prepare('INSERT INTO article_tag (article_id, tag_id) VALUES (:article_id, :tag_id)');
        foreach ($tagIds as $tagId) {
            $insertStmt->execute([
                'article_id' => $articleId,
                'tag_id' => $tagId,
            ]);
        }
    }

    private function findOrCreateTagIds(string $tagText): array
    {
        $names = $this->parseTagNames($tagText);
        $ids = [];

        foreach ($names as $name) {
            $stmt = $this->pdo->prepare('SELECT id FROM tags WHERE name = :name LIMIT 1');
            $stmt->execute(['name' => $name]);
            $existing = $stmt->fetchColumn();
            if ($existing) {
                $ids[] = (int) $existing;
                continue;
            }

            $insert = $this->pdo->prepare('INSERT INTO tags (name, slug, created_at, updated_at) VALUES (:name, :slug, :created_at, :updated_at)');
            $insert->execute([
                'name' => $name,
                'slug' => $this->makeUniqueSlug($name, 'tag', 'tags'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $ids[] = (int) $this->pdo->lastInsertId();
        }

        return array_values(array_unique($ids));
    }

    private function parseTagNames(string $tagText): array
    {
        $parts = preg_split('/[,£¬]+/', $tagText) ?: [];
        $names = [];
        foreach ($parts as $part) {
            $name = trim($part);
            if ($name === '' || mb_strlen($name) > 100) {
                continue;
            }
            $names[] = $name;
        }

        return array_values(array_unique($names));
    }

    private function attachTagsToArticles(array $items): array
    {
        $tagMap = $this->buildArticleTagMapFromItems($items);
        foreach ($items as &$item) {
            $item['tags'] = $tagMap[(int) ($item['id'] ?? 0)] ?? [];
        }
        unset($item);
        return $items;
    }

    private function buildArticleTagMapFromItems(array $items): array
    {
        $ids = [];
        foreach ($items as $item) {
            if (!empty($item['id'])) {
                $ids[] = (int) $item['id'];
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare('SELECT at.article_id, t.id, t.name, t.slug FROM article_tag at INNER JOIN tags t ON t.id = at.tag_id WHERE at.article_id IN (' . $placeholders . ') ORDER BY t.name ASC');
        $stmt->execute($ids);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $articleId = (int) $row['article_id'];
            unset($row['article_id']);
            $map[$articleId][] = $row;
        }

        return $map;
    }

    private function parseCategoryFilter(string $categoryFilter): array
    {
        if ($categoryFilter === '' || !str_contains($categoryFilter, ':')) {
            return [null, ''];
        }

        [$type, $slug] = explode(':', $categoryFilter, 2);
        $type = in_array($type, ['article', 'portfolio'], true) ? $type : null;
        return [$type, $slug];
    }

    private function buildCategoryFilterValue(string $type, string $slug): string
    {
        if ($slug === '') {
            return '';
        }

        return $type . ':' . $slug;
    }

    private function makeUniqueSlug(string $title, string $fallback, string $table, ?int $ignoreId = null, ?string $type = null): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        if ($slug === '') {
            $slug = $fallback;
        }

        $base = $slug;
        $index = 1;
        while ($this->slugExists($table, $slug, $ignoreId, $type)) {
            $slug = $base . '-' . $index;
            $index++;
        }

        return $slug;
    }

    private function slugExists(string $table, string $slug, ?int $ignoreId = null, ?string $type = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM ' . $table . ' WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($table === 'categories' && $type !== null) {
            $sql .= ' AND type = :type';
            $params['type'] = $type;
        }

        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        $value = (int) $value;
        return $value > 0 ? $value : null;
    }

    private function ensureCategory(string $type, string $name): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM categories WHERE type = :type AND name = :name LIMIT 1');
        $stmt->execute(['type' => $type, 'name' => $name]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            return (int) $existing;
        }

        $slug = $this->makeUniqueSlug($name, 'category', 'categories', null, $type);
        $stmt = $this->pdo->prepare('INSERT INTO categories (type, name, slug, created_at, updated_at) VALUES (:type, :name, :slug, :created_at, :updated_at)');
        $stmt->execute([
            'type' => $type,
            'name' => $name,
            'slug' => $slug,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    private function seed(): void
    {
        if ($this->categoryCount('article') === 0) {
            $designId = $this->ensureCategory('article', 'Design');
            $developmentId = $this->ensureCategory('article', 'Development');

            $stmt = $this->pdo->prepare('INSERT INTO articles (category_id, title, slug, excerpt, content, cover, created_at, updated_at) VALUES (:category_id, :title, :slug, :excerpt, :content, :cover, :created_at, :updated_at)');
            $stmt->execute([
                'category_id' => $designId,
                'title' => 'How Yuexia Was Designed Under a Night-Sky Mood',
                'slug' => 'how-yuexia-was-designed-under-a-night-sky-mood',
                'excerpt' => 'A note about building a personal site that feels atmospheric instead of generic.',
                'content' => '<p>Yuexia started from a simple goal: make a personal website feel memorable. Instead of a flat landing page, the design leans into cinematic light, layered cards, and smooth movement.</p><p>The result is a homepage with personality and room to grow into a proper publishing platform.</p>',
                'cover' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
                'created_at' => '2026-04-22 20:00:00',
                'updated_at' => '2026-04-22 20:00:00',
            ]);
            $firstArticleId = (int) $this->pdo->lastInsertId();
            $this->syncArticleTags($firstArticleId, 'design, motion, branding');

            $stmt->execute([
                'category_id' => $developmentId,
                'title' => 'Why a Traditional PHP Core Still Feels Powerful',
                'slug' => 'why-a-traditional-php-core-still-feels-powerful',
                'excerpt' => 'Small routing and view layers can still deliver a polished website when the scope is clear.',
                'content' => '<p>A traditional PHP architecture remains useful for personal projects because it stays direct. You can follow the route, open the controller, render the template, and keep full control without a heavy stack.</p><p>That makes iteration fast and the codebase easier to own.</p>',
                'cover' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80',
                'created_at' => '2026-04-20 21:20:00',
                'updated_at' => '2026-04-20 21:20:00',
            ]);
            $secondArticleId = (int) $this->pdo->lastInsertId();
            $this->syncArticleTags($secondArticleId, 'php, backend, architecture');
        }

        if ($this->categoryCount('portfolio') === 0) {
            $webDesignId = $this->ensureCategory('portfolio', 'Web Design');
            $interfaceId = $this->ensureCategory('portfolio', 'Interface');

            $stmt = $this->pdo->prepare('INSERT INTO portfolio_items (category_id, title, slug, summary, content, stack, image, link, created_at, updated_at) VALUES (:category_id, :title, :slug, :summary, :content, :stack, :image, :link, :created_at, :updated_at)');
            $stmt->execute([
                'category_id' => $webDesignId,
                'title' => 'Yuexia Landing Experience',
                'slug' => 'yuexia-landing-experience',
                'summary' => 'A cinematic personal homepage with layered motion and a moonlit visual tone.',
                'content' => '<p>This project focuses on creating a homepage that feels immersive from the first screen. The composition uses a large hero carousel, luminous accents, glass panels, and timed motion to shape the experience.</p>',
                'stack' => 'PHP / jQuery / HTML / CSS',
                'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                'link' => 'https://example.com/yuexia',
                'created_at' => '2026-04-22 19:00:00',
                'updated_at' => '2026-04-22 19:00:00',
            ]);
            $stmt->execute([
                'category_id' => $interfaceId,
                'title' => 'Portfolio Story Grid',
                'slug' => 'portfolio-story-grid',
                'summary' => 'A content block system for turning project summaries into visual stories.',
                'content' => '<p>The story grid is designed for personal case studies. Instead of dumping screenshots and bullet points, it presents each project with atmosphere, hierarchy, and a sense of continuity.</p>',
                'stack' => 'PHP Templates / jQuery Effects',
                'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
                'link' => 'https://example.com/story-grid',
                'created_at' => '2026-04-18 18:30:00',
                'updated_at' => '2026-04-18 18:30:00',
            ]);
        }
    }
}


