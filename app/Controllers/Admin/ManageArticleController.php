<?php

namespace App\Controllers\Admin;

use App\Models\ContentRepository;
use App\Models\MediaRepository;
use Core\Controller;
use Core\RichText;
use Core\Validator;
use RuntimeException;

class ManageArticleController extends Controller
{
    private ContentRepository $repository;
    private MediaRepository $mediaRepository;

    public function __construct(array $config, \Core\Request $request)
    {
        parent::__construct($config, $request);
        $this->repository = new ContentRepository($config);
        $this->mediaRepository = new MediaRepository($config);
    }

    public function index(array $params = []): string
    {
        $this->requirePermission('content.manage');
        $query = trim((string) $this->request->query('q', ''));
        $categoryId = max(0, (int) $this->request->query('category_id', 0));
        $page = max(1, (int) $this->request->query('page', 1));
        $result = $this->repository->paginateAdminArticles($query, $categoryId, $page, 10);

        return $this->render('admin/articles/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Manage Articles',
            'articles' => $result['items'],
            'pagination' => $result,
            'categories' => $this->repository->articleCategories(),
        ], 'layouts/admin');
    }

    public function create(array $params = []): string
    {
        $this->requirePermission('content.manage');
        return $this->render('admin/articles/form', [
            'siteName' => $this->config['name'],
            'tagline' => 'Create Article',
            'action' => '/admin/articles/create',
            'article' => null,
            'categories' => $this->repository->articleCategories(),
            'tagText' => '',
            'allTags' => $this->repository->allTags(),
        ], 'layouts/admin');
    }

    public function store(array $params = []): string
    {
        $this->requirePermission('content.manage');
        $this->requireCsrf();
        $data = $this->prepareData($this->request->all());
        $error = $this->validateArticle($data);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/articles/create');
        }

        try {
            $uploaded = $this->handleUpload('cover_upload', 'articles', $data['title']);
        } catch (RuntimeException $exception) {
            $this->flash('error', $exception->getMessage());
            $this->redirect('/admin/articles/create');
        }

        if ($uploaded !== null) {
            $data['cover'] = $uploaded;
        }
        $this->repository->createArticle($data);
        $this->logAction('article.create', 'Created a new article.', 'article', null, ['title' => $data['title']]);
        $this->flash('success', 'Article created successfully.');
        $this->redirect('/admin/articles');
    }

    public function edit(array $params = []): string
    {
        $this->requirePermission('content.manage');
        $article = $this->repository->findArticleById((int) ($params['id'] ?? 0));
        if ($article === null) {
            $this->flash('error', 'Article not found.');
            $this->redirect('/admin/articles');
        }

        return $this->render('admin/articles/form', [
            'siteName' => $this->config['name'],
            'tagline' => 'Edit Article',
            'action' => '/admin/articles/edit/' . $article['id'],
            'article' => $article,
            'categories' => $this->repository->articleCategories(),
            'tagText' => $article['tag_text'] ?? '',
            'allTags' => $this->repository->allTags(),
        ], 'layouts/admin');
    }

    public function update(array $params = []): string
    {
        $this->requirePermission('content.manage');
        $this->requireCsrf();
        $articleId = (int) ($params['id'] ?? 0);
        $data = $this->prepareData($this->request->all());
        $error = $this->validateArticle($data);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/articles/edit/' . $articleId);
        }

        try {
            $uploaded = $this->handleUpload('cover_upload', 'articles', $data['title']);
        } catch (RuntimeException $exception) {
            $this->flash('error', $exception->getMessage());
            $this->redirect('/admin/articles/edit/' . $articleId);
        }

        if ($uploaded !== null) {
            $data['cover'] = $uploaded;
        }
        $this->repository->updateArticle($articleId, $data);
        $this->logAction('article.update', 'Updated an article.', 'article', $articleId, ['title' => $data['title']]);
        $this->flash('success', 'Article updated successfully.');
        $this->redirect('/admin/articles');
    }

    public function delete(array $params = []): string
    {
        $this->requirePermission('content.manage');
        $this->requireCsrf();
        $articleId = (int) ($params['id'] ?? 0);
        $this->repository->deleteArticle($articleId);
        $this->logAction('article.delete', 'Deleted an article.', 'article', $articleId);
        $this->flash('success', 'Article deleted successfully.');
        $this->redirect('/admin/articles');
    }

    private function prepareData(array $data): array
    {
        $data['title'] = trim((string) ($data['title'] ?? ''));
        $data['excerpt'] = trim((string) ($data['excerpt'] ?? ''));
        $data['content'] = RichText::sanitize((string) ($data['content'] ?? ''));
        $data['cover'] = trim((string) ($data['cover'] ?? ''));
        $data['tags'] = trim((string) ($data['tags'] ?? ''));
        return $data;
    }

    private function validateArticle(array $data): ?string
    {
        $categoryId = (int) ($data['category_id'] ?? 0);
        if (!$this->repository->categoryExistsOfType($categoryId, 'article')) {
            return 'Please choose a valid article category.';
        }

        if (mb_strlen((string) ($data['tags'] ?? '')) > 300) {
            return 'Tags must be at most 300 characters.';
        }

        return Validator::requireString($data, 'title', 'Title', 3, 180)
            ?: Validator::requireString($data, 'excerpt', 'Excerpt', 10, 300)
            ?: Validator::requireString($data, 'content', 'Content', 10, 50000)
            ?: Validator::optionalUrl($data, 'cover', 'Cover URL');
    }

    private function handleUpload(string $field, string $folder, string $altText = ''): ?string
    {
        $file = $this->request->file($field);
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image upload failed. Please retry.');
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new RuntimeException('Image size must be smaller than 2MB.');
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowed, true)) {
            throw new RuntimeException('Only jpg, jpeg, png, gif, and webp images are allowed.');
        }

        if (!is_string($file['tmp_name'] ?? null) || @getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException('Uploaded file must be a valid image.');
        }

        $targetDir = rtrim($this->config['upload_path'], '/\\') . '/' . $folder;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Upload directory is not writable.');
        }

        $filename = uniqid($folder . '-', true) . '.' . $extension;
        $targetPath = $targetDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Unable to save uploaded image.');
        }

        $url = rtrim($this->config['upload_url'], '/\\') . '/' . $folder . '/' . $filename;
        $this->mediaRepository->createMedia([
            'file_name' => (string) ($file['name'] ?? $filename),
            'file_url' => $url,
            'folder' => $folder,
            'mime_type' => (string) ($file['type'] ?? 'image/' . $extension),
            'file_size' => (int) ($file['size'] ?? 0),
            'alt_text' => $altText,
        ]);

        return $url;
    }
}
