<?php

namespace App\Controllers;

use App\Models\ContentRepository;
use Core\Controller;

class SearchController extends Controller
{
    public function index(array $params = []): string
    {
        $repository = new ContentRepository($this->config);
        $query = trim((string) $this->request->query('q', ''));
        $type = trim((string) $this->request->query('type', 'all'));
        $type = in_array($type, ['all', 'article', 'portfolio'], true) ? $type : 'all';
        $categoryFilter = trim((string) $this->request->query('category', ''));
        $tag = trim((string) $this->request->query('tag', ''));
        if ($type === 'portfolio') {
            $tag = '';
        }
        $page = max(1, (int) $this->request->query('page', 1));
        $result = $repository->searchContent($query, $type, $categoryFilter, $tag, $page, 10);

        return $this->render('search/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Search',
            'results' => $result['items'],
            'pagination' => $result,
            'categoryOptions' => $this->categoryOptions($repository, $type),
            'tags' => $repository->allTags(),
        ]);
    }

    private function categoryOptions(ContentRepository $repository, string $type): array
    {
        $options = [];
        if ($type === 'all' || $type === 'article') {
            foreach ($repository->articleCategories() as $category) {
                $options[] = [
                    'value' => 'article:' . $category['slug'],
                    'label' => 'ÎÄÕÂ / ' . $category['name'],
                ];
            }
        }
        if ($type === 'all' || $type === 'portfolio') {
            foreach ($repository->portfolioCategories() as $category) {
                $options[] = [
                    'value' => 'portfolio:' . $category['slug'],
                    'label' => '×÷Æ· / ' . $category['name'],
                ];
            }
        }
        return $options;
    }
}
