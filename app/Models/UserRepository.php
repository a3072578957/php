<?php

namespace App\Models;

use Core\Database;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = Database::connection($config);
        $this->ensureDefaultAdmin();
    }

    public function countUsers(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    }

    public function countByRole(string $role): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM admin_users WHERE role = :role');
        $stmt->execute(['role' => $role]);
        return (int) $stmt->fetchColumn();
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admin_users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admin_users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function usernameExists(string $username, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM admin_users WHERE username = :username';
        $params = ['username' => $username];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM admin_users WHERE email = :email';
        $params = ['email' => $this->normalizeEmail($email)];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function paginateUsers(string $query = '', int $page = 1, int $perPage = 12): array
    {
        $whereSql = '';
        $params = [];
        if ($query !== '') {
            $whereSql = ' WHERE (username LIKE :q OR display_name LIKE :q OR email LIKE :q OR role LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM admin_users' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT id, username, display_name, email, role, created_at, updated_at FROM admin_users' . $whereSql . ' ORDER BY id ASC LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'query' => $query,
        ];
    }

    public function createUser(array $data): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO admin_users (username, display_name, email, role, password_hash, created_at, updated_at) VALUES (:username, :display_name, :email, :role, :password_hash, :created_at, :updated_at)');
        $stmt->execute([
            'username' => trim((string) ($data['username'] ?? '')),
            'display_name' => trim((string) ($data['display_name'] ?? '')),
            'email' => $this->nullableEmail($data['email'] ?? null),
            'role' => $this->normalizeRole((string) ($data['role'] ?? 'editor')),
            'password_hash' => password_hash((string) ($data['password'] ?? ''), PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateUser(int $id, array $data): void
    {
        $fields = [
            'username = :username',
            'display_name = :display_name',
            'email = :email',
            'role = :role',
            'updated_at = :updated_at',
        ];
        $params = [
            'id' => $id,
            'username' => trim((string) ($data['username'] ?? '')),
            'display_name' => trim((string) ($data['display_name'] ?? '')),
            'email' => $this->nullableEmail($data['email'] ?? null),
            'role' => $this->normalizeRole((string) ($data['role'] ?? 'editor')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $password = trim((string) ($data['password'] ?? ''));
        if ($password !== '') {
            $fields[] = 'password_hash = :password_hash';
            $params['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql = 'UPDATE admin_users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function updatePassword(int $id, string $password): void
    {
        $stmt = $this->pdo->prepare('UPDATE admin_users SET password_hash = :password_hash, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteUser(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM admin_users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function validRoles(): array
    {
        return ['super_admin', 'editor', 'moderator'];
    }

    public function normalizeRole(string $role): string
    {
        return in_array($role, $this->validRoles(), true) ? $role : 'editor';
    }

    private function ensureDefaultAdmin(): void
    {
        if ($this->countUsers() > 0) {
            return;
        }

        $stmt = $this->pdo->prepare('INSERT INTO admin_users (username, display_name, email, role, password_hash, created_at, updated_at) VALUES (:username, :display_name, :email, :role, :password_hash, :created_at, :updated_at)');
        $stmt->execute([
            'username' => 'admin',
            'display_name' => 'Yuexia Admin',
            'email' => null,
            'role' => 'super_admin',
            'password_hash' => password_hash('yuexia123456', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private function nullableEmail(mixed $email): ?string
    {
        $normalized = $this->normalizeEmail((string) $email);
        return $normalized === '' ? null : $normalized;
    }
}