<?php

namespace App\Models;

use Core\Database;
use PDO;

class AdminActionLogRepository
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = Database::connection($config);
    }

    public function countLogs(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM admin_action_logs')->fetchColumn();
    }

    public function createLog(array $data): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO admin_action_logs (admin_user_id, admin_name, action, target_type, target_id, description, request_path, request_method, ip_address, context_json, created_at) VALUES (:admin_user_id, :admin_name, :action, :target_type, :target_id, :description, :request_path, :request_method, :ip_address, :context_json, :created_at)');
        $stmt->execute([
            'admin_user_id' => !empty($data['admin_user_id']) ? (int) $data['admin_user_id'] : null,
            'admin_name' => trim((string) ($data['admin_name'] ?? 'System')),
            'action' => trim((string) ($data['action'] ?? 'unknown')),
            'target_type' => $this->nullableString($data['target_type'] ?? null),
            'target_id' => !empty($data['target_id']) ? (int) $data['target_id'] : null,
            'description' => trim((string) ($data['description'] ?? '')),
            'request_path' => $this->nullableString($data['request_path'] ?? null),
            'request_method' => $this->nullableString($data['request_method'] ?? null),
            'ip_address' => $this->nullableString($data['ip_address'] ?? null),
            'context_json' => !empty($data['context']) ? json_encode($data['context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function paginateLogs(string $query = '', string $action = '', int $page = 1, int $perPage = 20): array
    {
        [$whereSql, $params] = $this->buildFilters($query, $action);

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM admin_action_logs' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT * FROM admin_action_logs' . $whereSql . ' ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset');
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
            'action' => $action,
        ];
    }

    public function listLogs(string $query = '', string $action = '', ?int $limit = null): array
    {
        [$whereSql, $params] = $this->buildFilters($query, $action);
        $sql = 'SELECT * FROM admin_action_logs' . $whereSql . ' ORDER BY created_at DESC, id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function distinctActions(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT action FROM admin_action_logs ORDER BY action ASC');
        return array_map(static fn(array $row): string => (string) $row['action'], $stmt->fetchAll());
    }

    private function buildFilters(string $query, string $action): array
    {
        $where = [];
        $params = [];

        if ($query !== '') {
            $where[] = '(admin_name LIKE :q OR action LIKE :q OR description LIKE :q OR request_path LIKE :q OR target_type LIKE :q OR context_json LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }
        if ($action !== '') {
            $where[] = 'action = :action';
            $params['action'] = $action;
        }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
        return [$whereSql, $params];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}