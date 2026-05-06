<?php

namespace App\Controllers\Admin;

use App\Models\UserRepository;
use Core\Controller;
use Core\Validator;

class ManageAdminUserController extends Controller
{
    private UserRepository $repository;

    public function __construct(array $config, \Core\Request $request)
    {
        parent::__construct($config, $request);
        $this->repository = new UserRepository($config);
    }

    public function index(array $params = []): string
    {
        $this->requirePermission('users.manage');
        $query = trim((string) $this->request->query('q', ''));
        $page = max(1, (int) $this->request->query('page', 1));
        $result = $this->repository->paginateUsers($query, $page, 12);

        return $this->render('admin/users/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Manage Admin Users',
            'users' => $result['items'],
            'pagination' => $result,
            'currentUserId' => (int) ($_SESSION['admin_user_id'] ?? 0),
        ], 'layouts/admin');
    }

    public function create(array $params = []): string
    {
        $this->requirePermission('users.manage');
        return $this->render('admin/users/form', [
            'siteName' => $this->config['name'],
            'tagline' => 'Create Admin User',
            'action' => '/admin/users/create',
            'user' => null,
            'passwordRequired' => true,
            'roles' => $this->repository->validRoles(),
        ], 'layouts/admin');
    }

    public function store(array $params = []): string
    {
        $this->requirePermission('users.manage');
        $this->requireCsrf();
        $data = $this->request->all();
        $error = $this->validateUser($data, null, true);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/users/create');
        }

        $this->repository->createUser($data);
        $created = $this->repository->findByUsername(trim((string) $data['username']));
        $this->logAction('admin_user.create', 'Created a new admin account.', 'admin_user', (int) ($created['id'] ?? 0), [
            'username' => $data['username'] ?? '',
            'email' => $data['email'] ?? '',
            'role' => $data['role'] ?? '',
        ]);
        $this->flash('success', 'Admin user created successfully.');
        $this->redirect('/admin/users');
    }

    public function edit(array $params = []): string
    {
        $this->requirePermission('users.manage');
        $user = $this->repository->findById((int) ($params['id'] ?? 0));
        if ($user === null) {
            $this->flash('error', 'Admin user not found.');
            $this->redirect('/admin/users');
        }

        return $this->render('admin/users/form', [
            'siteName' => $this->config['name'],
            'tagline' => 'Edit Admin User',
            'action' => '/admin/users/edit/' . $user['id'],
            'user' => $user,
            'passwordRequired' => false,
            'roles' => $this->repository->validRoles(),
        ], 'layouts/admin');
    }

    public function update(array $params = []): string
    {
        $this->requirePermission('users.manage');
        $this->requireCsrf();
        $id = (int) ($params['id'] ?? 0);
        $user = $this->repository->findById($id);
        if ($user === null) {
            $this->flash('error', 'Admin user not found.');
            $this->redirect('/admin/users');
        }

        $data = $this->request->all();
        $error = $this->validateUser($data, $id, false);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/users/edit/' . $id);
        }

        $newRole = $this->repository->normalizeRole((string) ($data['role'] ?? 'editor'));
        if ($user['role'] === 'super_admin' && $newRole !== 'super_admin' && $this->repository->countByRole('super_admin') <= 1) {
            $this->flash('error', 'The last super admin cannot be downgraded.');
            $this->redirect('/admin/users/edit/' . $id);
        }

        $this->repository->updateUser($id, $data);
        if ($id === (int) ($_SESSION['admin_user_id'] ?? 0)) {
            $_SESSION['admin_name'] = trim((string) ($data['display_name'] ?? $data['username'] ?? $_SESSION['admin_name'] ?? 'admin'));
            $_SESSION['admin_role'] = $newRole;
        }
        $this->logAction('admin_user.update', 'Updated admin account settings.', 'admin_user', $id, [
            'username' => $data['username'] ?? '',
            'email' => $data['email'] ?? '',
            'role' => $newRole,
        ]);
        $this->flash('success', 'Admin user updated successfully.');
        $this->redirect('/admin/users');
    }

    public function delete(array $params = []): string
    {
        $this->requirePermission('users.manage');
        $this->requireCsrf();
        $id = (int) ($params['id'] ?? 0);
        $target = $this->repository->findById($id);
        if ($target === null) {
            $this->flash('error', 'Admin user not found.');
            $this->redirect('/admin/users');
        }
        if ($this->repository->countUsers() <= 1) {
            $this->flash('error', 'At least one admin user must remain.');
            $this->redirect('/admin/users');
        }
        if ($target['role'] === 'super_admin' && $this->repository->countByRole('super_admin') <= 1) {
            $this->flash('error', 'The last super admin cannot be deleted.');
            $this->redirect('/admin/users');
        }
        if ($id === (int) ($_SESSION['admin_user_id'] ?? 0)) {
            $this->flash('error', 'You cannot delete the account currently in use.');
            $this->redirect('/admin/users');
        }

        $this->repository->deleteUser($id);
        $this->logAction('admin_user.delete', 'Deleted an admin account.', 'admin_user', $id, [
            'username' => $target['username'] ?? '',
            'email' => $target['email'] ?? '',
            'role' => $target['role'] ?? '',
        ]);
        $this->flash('success', 'Admin user deleted successfully.');
        $this->redirect('/admin/users');
    }

    private function validateUser(array $data, ?int $ignoreId, bool $passwordRequired): ?string
    {
        $error = Validator::requireString($data, 'username', 'Username', 3, 80)
            ?: Validator::requireString($data, 'display_name', 'Display name', 2, 120)
            ?: Validator::requireString($data, 'email', 'Email', 5, 160);
        if ($error !== null) {
            return $error;
        }

        $username = trim((string) ($data['username'] ?? ''));
        if ($this->repository->usernameExists($username, $ignoreId)) {
            return 'This username is already in use.';
        }

        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Please enter a valid email address.';
        }
        if ($this->repository->emailExists($email, $ignoreId)) {
            return 'This email address is already in use.';
        }

        if (!in_array((string) ($data['role'] ?? ''), $this->repository->validRoles(), true)) {
            return 'Please choose a valid role.';
        }

        $password = trim((string) ($data['password'] ?? ''));
        if ($passwordRequired && mb_strlen($password) < 8) {
            return 'Password must be at least 8 characters.';
        }
        if (!$passwordRequired && $password !== '' && mb_strlen($password) < 8) {
            return 'Password must be at least 8 characters.';
        }

        return null;
    }
}