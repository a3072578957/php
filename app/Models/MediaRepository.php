<?php

namespace App\Models;

use Core\Database;
use PDO;

class MediaRepository
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = Database::connection($config);
    }

    public function mediaCount(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM media')->fetchColumn();
    }

    public function paginateMedia(string $query = '', int $page = 1, int $perPage = 18): array
    {
        $whereSql = '';
        $params = [];

        if ($query !== '') {
            $whereSql = ' WHERE (file_name LIKE :q OR file_url LIKE :q OR alt_text LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM media' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT * FROM media' . $whereSql . ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
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

    public function createMedia(array $data): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO media (file_name, file_url, folder, mime_type, file_size, alt_text, created_at) VALUES (:file_name, :file_url, :folder, :mime_type, :file_size, :alt_text, :created_at)');
        $stmt->execute([
            'file_name' => trim((string) ($data['file_name'] ?? '')),
            'file_url' => trim((string) ($data['file_url'] ?? '')),
            'folder' => trim((string) ($data['folder'] ?? 'library')),
            'mime_type' => trim((string) ($data['mime_type'] ?? 'image/jpeg')),
            'file_size' => (int) ($data['file_size'] ?? 0),
            'alt_text' => trim((string) ($data['alt_text'] ?? '')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
