<?php

namespace App\Controllers\Admin;

use App\Models\ContentRepository;
use Core\Controller;
use Core\Validator;

class ManageCategoryController extends Controller
{
    private ContentRepository $repository;

    public function __construct(array $config, \Core\Request $request)
    {
        parent::__construct($config, $request);
        $this->repository = new ContentRepository($config);
    }

    public function index(array $params = []): string
    {
        $this->requirePermission('taxonomy.manage');
        $type = trim((string) $this->request->query('type', ''));
        $query = trim((string) $this->request->query('q', ''));
        $page = max(1, (int) $this->request->query('page', 1));
        $result = $this->repository->paginateCategories($type, $query, $page, 12);

        return $this->render('admin/categories/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Manage Categories',
            'categories' => $result['items'],
            'pagination' => $result,
        ], 'layouts/admin');
    }

    public function create(array $params = []): string
    {
        $this->requirePermission('taxonomy.manage');
        return $this->render('admin/categories/form', [
            'siteName' => $this->config['name'],
            'tagline' => 'Create Category',
            'action' => '/admin/categories/create',
            'category' => null,
            'usage' => ['total' => 0],
        ], 'layouts/admin');
    }

    public function store(array $params = []): string
    {
        $this->requirePermission('taxonomy.manage');
        $this->requireCsrf();
        $data = $this->request->all();
        $error = $this->validateCategory($data);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/categories/create');
        }
        $this->repository->createCategory($data);
        $this->logAction('category.create', 'Created a category.', 'category', null, ['name' => $data['name'] ?? '', 'type' => $data['type'] ?? '']);
        $this->flash('success', 'Category created successfully.');
        $this->redirect('/admin/categories');
    }

    public function edit(array $params = []): string
    {
        $this->requirePermission('taxonomy.manage');
        $category = $this->repository->findCategoryById((int) ($params['id'] ?? 0));
        if ($category === null) {
            $this->flash('error', 'Category not found.');
            $this->redirect('/admin/categories');
        }

        return $this->render('admin/categories/form', [
            'siteName' => $this->config['name'],
            'tagline' => 'Edit Category',
            'action' => '/admin/categories/edit/' . $category['id'],
            'category' => $category,
            'usage' => $this->repository->categoryUsageSummary((int) $category['id']),
        ], 'layouts/admin');
    }

    public function update(array $params = []): string
    {
        $this->requirePermission('taxonomy.manage');
        $this->requireCsrf();

        $id = (int) ($params['id'] ?? 0);
        $existing = $this->repository->findCategoryById($id);
        if ($existing === null) {
            $this->flash('error', 'Category not found.');
            $this->redirect('/admin/categories');
        }

        $data = $this->request->all();
        $error = $this->validateCategory($data, $id);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/categories/edit/' . $id);
        }

        $newType = trim((string) ($data['type'] ?? 'article'));
        $usage = $this->repository->categoryUsageSummary($id);
        if ($existing['type'] !== $newType && $usage['total'] > 0) {
            $this->flash('error', 'This category is already in use, so its type cannot be changed.');
            $this->redirect('/admin/categories/edit/' . $id);
        }

        $this->repository->updateCategory($id, $data);
        $this->logAction('category.update', 'Updated category settings.', 'category', $id, ['name' => $data['name'] ?? '', 'type' => $newType]);
        $this->flash('success', 'Category updated successfully.');
        $this->redirect('/admin/categories');
    }

    public function delete(array $params = []): string
    {
        $this->requirePermission('taxonomy.manage');
        $this->requireCsrf();

        $id = (int) ($params['id'] ?? 0);
        $usage = $this->repository->categoryUsageSummary($id);
        if ($usage['total'] > 0) {
            $this->flash('error', 'Please move or delete the related content before deleting this category.');
            $this->redirect('/admin/categories');
        }

        $this->repository->deleteCategory($id);
        $this->logAction('category.delete', 'Deleted a category.', 'category', $id);
        $this->flash('success', 'Category deleted successfully.');
        $this->redirect('/admin/categories');
    }

    private function validateCategory(array $data, ?int $ignoreId = null): ?string
    {
        $type = trim((string) ($data['type'] ?? ''));
        if (!in_array($type, ['article', 'portfolio'], true)) {
            return 'Category type is invalid.';
        }

        $error = Validator::requireString($data, 'name', 'Category name', 2, 100);
        if ($error !== null) {
            return $error;
        }

        $name = trim((string) ($data['name'] ?? ''));
        if ($this->repository->categoryNameExists($type, $name, $ignoreId)) {
            return 'A category with the same name already exists under this type.';
        }

        return null;
    }
}
