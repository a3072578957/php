<?php

namespace App\Controllers;

use App\Models\ContentRepository;
use Core\Controller;

class CategoryController extends Controller
{
    public function show(array $params = []): string
    {
        $type = trim((string) ($params['type'] ?? ''));
        $slug = trim((string) ($params['slug'] ?? ''));
        if (!in_array($type, ['article', 'portfolio'], true)) {
            http_response_code(404);
            return 'Category not found';
        }

        $repository = new ContentRepository($this->config);
        $category = $repository->findCategoryByTypeAndSlug($type, $slug);
        if ($category === null) {
            http_response_code(404);
            return 'Category not found';
        }

        $query = trim((string) $this->request->query('q', ''));
        $tag = trim((string) $this->request->query('tag', ''));
        $page = max(1, (int) $this->request->query('page', 1));

        if ($type === 'article') {
            $result = $repository->paginateArticles($query, $slug, $page, 6, $tag);
        } else {
            $result = $repository->paginatePortfolio($query, $slug, $page, 6);
        }

        return $this->render('categories/show', [
            'siteName' => $this->config['name'],
            'tagline' => $category['name'],
            'category' => $category,
            'type' => $type,
            'items' => $result['items'],
            'pagination' => $result,
            'tags' => $type === 'article' ? $repository->allTags() : [],
        ]);
    }
}
