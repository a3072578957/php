<?php

namespace App\Controllers\Admin;

use App\Models\ContentRepository;
use Core\Controller;
use Core\Validator;

class ManageTagController extends Controller
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
        $query = trim((string) $this->request->query('q', ''));
        $page = max(1, (int) $this->request->query('page', 1));
        $result = $this->repository->paginateTags($query, $page, 15);

        return $this->render('admin/tags/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Manage Tags',
            'tags' => $result['items'],
            'pagination' => $result,
        ], 'layouts/admin');
    }

    public function create(array $params = []): string
    {
        $this->requirePermission('taxonomy.manage');
        return $this->render('admin/tags/form', [
            'siteName' => $this->config['name'],
            'tagline' => 'Create Tag',
            'action' => '/admin/tags/create',
            'tag' => null,
            'usageCount' => 0,
        ], 'layouts/admin');
    }

    public function store(array $params = []): string
    {
        $this->requirePermission('taxonomy.manage');
        $this->requireCsrf();
        $data = $this->request->all();
        $error = $this->validateTag($data);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/tags/create');
        }

        $this->repository->createTag($data);
        $this->logAction('tag.create', 'Created a tag.', 'tag', null, ['name' => $data['name'] ?? '']);
        $this->flash('success', 'Tag created successfully.');
        $this->redirect('/admin/tags');
    }

    public function edit(array $params = []): string
    {
        $this->requirePermission('taxonomy.manage');
        $tag = $this->repository->findTagById((int) ($params['id'] ?? 0));
        if ($tag === null) {
            $this->flash('error', 'Tag not found.');
            $this->redirect('/admin/tags');
        }

        return $this->render('admin/tags/form', [
            'siteName' => $this->config['name'],
            'tagline' => 'Edit Tag',
            'action' => '/admin/tags/edit/' . $tag['id'],
            'tag' => $tag,
            'usageCount' => $this->repository->tagUsageCount((int) $tag['id']),
        ], 'layouts/admin');
    }

    public function update(array $params = []): string
    {
        $this->requirePermission('taxonomy.manage');
        $this->requireCsrf();
        $id = (int) ($params['id'] ?? 0);
        $tag = $this->repository->findTagById($id);
        if ($tag === null) {
            $this->flash('error', 'Tag not found.');
            $this->redirect('/admin/tags');
        }

        $data = $this->request->all();
        $error = $this->validateTag($data, $id);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/tags/edit/' . $id);
        }

        $this->repository->updateTag($id, $data);
        $this->logAction('tag.update', 'Updated a tag.', 'tag', $id, ['name' => $data['name'] ?? '']);
        $this->flash('success', 'Tag updated successfully.');
        $this->redirect('/admin/tags');
    }

    public function delete(array $params = []): string
    {
        $this->requirePermission('taxonomy.manage');
        $this->requireCsrf();
        $id = (int) ($params['id'] ?? 0);
        if ($this->repository->tagUsageCount($id) > 0) {
            $this->flash('error', 'Please remove this tag from articles before deleting it.');
            $this->redirect('/admin/tags');
        }

        $this->repository->deleteTag($id);
        $this->logAction('tag.delete', 'Deleted a tag.', 'tag', $id);
        $this->flash('success', 'Tag deleted successfully.');
        $this->redirect('/admin/tags');
    }

    private function validateTag(array $data, ?int $ignoreId = null): ?string
    {
        $error = Validator::requireString($data, 'name', 'Tag name', 2, 100);
        if ($error !== null) {
            return $error;
        }

        $name = trim((string) ($data['name'] ?? ''));
        if ($this->repository->tagNameExists($name, $ignoreId)) {
            return 'A tag with the same name already exists.';
        }

        return null;
    }
}
