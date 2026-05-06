<?php

namespace App\Controllers;

use App\Models\ContentRepository;
use App\Models\InteractionRepository;
use Core\Controller;
use Core\Validator;

class ArticleController extends Controller
{
    public function index(array $params = []): string
    {
        $repository = new ContentRepository($this->config);
        $query = trim((string) $this->request->query('q', ''));
        $category = trim((string) $this->request->query('category', ''));
        $tag = trim((string) $this->request->query('tag', ''));
        $page = max(1, (int) $this->request->query('page', 1));
        $result = $repository->paginateArticles($query, $category, $page, 6, $tag);

        return $this->render('articles/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Articles',
            'articles' => $result['items'],
            'pagination' => $result,
            'categories' => $repository->articleCategories(),
            'tags' => $repository->allTags(),
        ]);
    }

    public function show(array $params = []): string
    {
        $repository = new ContentRepository($this->config);
        $article = $repository->findArticleBySlug($params['slug'] ?? '');
        if ($article === null) {
            http_response_code(404);
            return 'Article not found';
        }

        $interactionRepository = new InteractionRepository($this->config);

        return $this->render('articles/show', [
            'siteName' => $this->config['name'],
            'tagline' => $article['title'],
            'article' => $article,
            'comments' => $interactionRepository->approvedCommentTreeForArticle((int) $article['id']),
        ]);
    }

    public function storeComment(array $params = []): string
    {
        $repository = new ContentRepository($this->config);
        $article = $repository->findArticleBySlug($params['slug'] ?? '');
        if ($article === null) {
            http_response_code(404);
            return 'Article not found';
        }

        $this->requireCsrf();
        $data = $this->request->all();
        $error = Validator::requireString($data, 'nickname', 'Nickname', 2, 80)
            ?: Validator::requireString($data, 'content', 'Comment', 5, 2000);

        if ($error === null) {
            $email = trim((string) ($data['email'] ?? ''));
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email format is invalid.';
            }
        }

        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/articles/' . $article['slug'] . '#comments');
        }

        $interactionRepository = new InteractionRepository($this->config);
        $interactionRepository->createComment((int) $article['id'], $data);

        $this->flash('success', 'Your comment has been submitted and is awaiting review.');
        $this->redirect('/articles/' . $article['slug'] . '#comments');
    }
}
