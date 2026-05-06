<?php

namespace App\Controllers\Admin;

use App\Models\ContentRepository;
use App\Models\MediaRepository;
use Core\Controller;
use Core\RichText;
use Core\Validator;
use RuntimeException;

class ManagePortfolioController extends Controller
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
        $result = $this->repository->paginateAdminPortfolio($query, $categoryId, $page, 10);

        return $this->render('admin/portfolio/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Manage Portfolio',
            'portfolio' => $result['items'],
            'pagination' => $result,
            'categories' => $this->repository->portfolioCategories(),
        ], 'layouts/admin');
    }

    public function create(array $params = []): string
    {
        $this->requirePermission('content.manage');
        return $this->render('admin/portfolio/form', [
            'siteName' => $this->config['name'],
            'tagline' => 'Create Portfolio Entry',
            'action' => '/admin/portfolio/create',
            'work' => null,
            'categories' => $this->repository->portfolioCategories(),
        ], 'layouts/admin');
    }

    public function store(array $params = []): string
    {
        $this->requirePermission('content.manage');
        $this->requireCsrf();
        $data = $this->prepareData($this->request->all());
        $error = $this->validateWork($data);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/portfolio/create');
        }

        try {
            $uploaded = $this->handleUpload('image_upload', 'portfolio', $data['title']);
        } catch (RuntimeException $exception) {
            $this->flash('error', $exception->getMessage());
            $this->redirect('/admin/portfolio/create');
        }

        if ($uploaded !== null) {
            $data['image'] = $uploaded;
        }
        $this->repository->createPortfolio($data);
        $this->logAction('portfolio.create', 'Created a new portfolio entry.', 'portfolio', null, ['title' => $data['title']]);
        $this->flash('success', 'Portfolio entry created successfully.');
        $this->redirect('/admin/portfolio');
    }

    public function edit(array $params = []): string
    {
        $this->requirePermission('content.manage');
        $work = $this->repository->findPortfolioById((int) ($params['id'] ?? 0));
        if ($work === null) {
            $this->flash('error', 'Portfolio entry not found.');
            $this->redirect('/admin/portfolio');
        }

        return $this->render('admin/portfolio/form', [
            'siteName' => $this->config['name'],
            'tagline' => 'Edit Portfolio Entry',
            'action' => '/admin/portfolio/edit/' . $work['id'],
            'work' => $work,
            'categories' => $this->repository->portfolioCategories(),
        ], 'layouts/admin');
    }

    public function update(array $params = []): string
    {
        $this->requirePermission('content.manage');
        $this->requireCsrf();
        $workId = (int) ($params['id'] ?? 0);
        $data = $this->prepareData($this->request->all());
        $error = $this->validateWork($data);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/portfolio/edit/' . $workId);
        }

        try {
            $uploaded = $this->handleUpload('image_upload', 'portfolio', $data['title']);
        } catch (RuntimeException $exception) {
            $this->flash('error', $exception->getMessage());
            $this->redirect('/admin/portfolio/edit/' . $workId);
        }

        if ($uploaded !== null) {
            $data['image'] = $uploaded;
        }
        $this->repository->updatePortfolio($workId, $data);
        $this->logAction('portfolio.update', 'Updated a portfolio entry.', 'portfolio', $workId, ['title' => $data['title']]);
        $this->flash('success', 'Portfolio entry updated successfully.');
        $this->redirect('/admin/portfolio');
    }

    public function delete(array $params = []): string
    {
        $this->requirePermission('content.manage');
        $this->requireCsrf();
        $workId = (int) ($params['id'] ?? 0);
        $this->repository->deletePortfolio($workId);
        $this->logAction('portfolio.delete', 'Deleted a portfolio entry.', 'portfolio', $workId);
        $this->flash('success', 'Portfolio entry deleted successfully.');
        $this->redirect('/admin/portfolio');
    }

    private function prepareData(array $data): array
    {
        $data['title'] = trim((string) ($data['title'] ?? ''));
        $data['summary'] = trim((string) ($data['summary'] ?? ''));
        $data['content'] = RichText::sanitize((string) ($data['content'] ?? ''));
        $data['stack'] = trim((string) ($data['stack'] ?? ''));
        $data['image'] = trim((string) ($data['image'] ?? ''));
        $data['link'] = trim((string) ($data['link'] ?? ''));
        return $data;
    }

    private function validateWork(array $data): ?string
    {
        $categoryId = (int) ($data['category_id'] ?? 0);
        if (!$this->repository->categoryExistsOfType($categoryId, 'portfolio')) {
            return 'Please choose a valid portfolio category.';
        }

        return Validator::requireString($data, 'title', 'Title', 3, 180)
            ?: Validator::requireString($data, 'summary', 'Summary', 10, 300)
            ?: Validator::requireString($data, 'content', 'Content', 10, 50000)
            ?: Validator::optionalUrl($data, 'image', 'Image URL')
            ?: Validator::optionalUrl($data, 'link', 'Project link');
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
