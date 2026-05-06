<?php

namespace Core;

class Controller
{
    protected array $config;
    protected Request $request;
    protected View $view;

    public function __construct(array $config, Request $request)
    {
        $this->config = $config;
        $this->request = $request;
        $this->view = new View($config);
    }

    protected function render(string $template, array $data = [], ?string $layout = null): string
    {
        return $this->view->render($template, $data, $layout);
    }

    protected function redirect(string $path): string
    {
        header('Location: ' . $path);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }

    protected function isAdminLoggedIn(): bool
    {
        return !empty($_SESSION['admin_logged_in']);
    }

    protected function requireAdmin(): void
    {
        if (!$this->isAdminLoggedIn()) {
            $this->flash('error', 'Please login first.');
            $this->redirect('/admin/login');
        }
    }

    protected function adminRole(): string
    {
        return (string) ($_SESSION['admin_role'] ?? '');
    }

    protected function currentAdminId(): ?int
    {
        $id = (int) ($_SESSION['admin_user_id'] ?? 0);
        return $id > 0 ? $id : null;
    }

    protected function currentAdminName(): string
    {
        return (string) ($_SESSION['admin_name'] ?? 'System');
    }

    protected function requirePermission(string $permission): void
    {
        $this->requireAdmin();
        if (!self::roleCan($this->adminRole(), $permission)) {
            $this->flash('error', 'You do not have permission to access this area.');
            $this->redirect('/admin');
        }
    }

    protected function requireCsrf(): void
    {
        if (!Security::verifyCsrf((string) $this->request->input('_token', ''))) {
            $this->flash('error', 'Invalid CSRF token. Please retry.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }

    protected function logAction(string $action, string $description, string $targetType = '', ?int $targetId = null, array $context = []): void
    {
        if (!class_exists(\App\Models\AdminActionLogRepository::class)) {
            return;
        }

        $repository = new \App\Models\AdminActionLogRepository($this->config);
        $repository->createLog([
            'admin_user_id' => $this->currentAdminId(),
            'admin_name' => $this->currentAdminName(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'description' => $description,
            'request_path' => $this->request->path(),
            'request_method' => $this->request->method(),
            'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            'context' => $context,
        ]);
    }

    public static function permissionsForRole(string $role): array
    {
        return match ($role) {
            'super_admin' => [
                'dashboard.view',
                'users.manage',
                'content.manage',
                'taxonomy.manage',
                'media.manage',
                'moderation.manage',
                'logs.view',
            ],
            'editor' => [
                'dashboard.view',
                'content.manage',
                'taxonomy.manage',
                'media.manage',
            ],
            'moderator' => [
                'dashboard.view',
                'moderation.manage',
            ],
            default => [],
        };
    }

    public static function roleCan(string $role, string $permission): bool
    {
        return in_array($permission, self::permissionsForRole($role), true);
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            'super_admin' => '超级管理员',
            'editor' => '编辑',
            'moderator' => '审核员',
            default => '未分配角色',
        };
    }
}
