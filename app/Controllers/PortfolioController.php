<?php

namespace App\Controllers;

use App\Models\ContentRepository;
use Core\Controller;

class PortfolioController extends Controller
{
    public function index(array $params = []): string
    {
        $repository = new ContentRepository($this->config);
        $query = trim((string) $this->request->query('q', ''));
        $category = trim((string) $this->request->query('category', ''));
        $page = max(1, (int) $this->request->query('page', 1));
        $result = $repository->paginatePortfolio($query, $category, $page, 6);

        return $this->render('portfolio/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Portfolio',
            'portfolio' => $result['items'],
            'pagination' => $result,
            'categories' => $repository->portfolioCategories(),
        ]);
    }

    public function show(array $params = []): string
    {
        $repository = new ContentRepository($this->config);
        $work = $repository->findPortfolioBySlug($params['slug'] ?? '');
        if ($work === null) {
            http_response_code(404);
            return 'Portfolio entry not found';
        }

        return $this->render('portfolio/show', [
            'siteName' => $this->config['name'],
            'tagline' => $work['title'],
            'work' => $work,
        ]);
    }
}
