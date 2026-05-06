<?php

namespace App\Models;

use Core\Database;
use PDO;

class InteractionRepository
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = Database::connection($config);
    }

    public function commentCount(string $status = ''): int
    {
        if ($status === '') {
            return (int) $this->pdo->query('SELECT COUNT(*) FROM article_comments')->fetchColumn();
        }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM article_comments WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function messageCount(string $status = ''): int
    {
        if ($status === '') {
            return (int) $this->pdo->query('SELECT COUNT(*) FROM guestbook_messages')->fetchColumn();
        }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM guestbook_messages WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function approvedCommentTreeForArticle(int $articleId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM article_comments WHERE article_id = :article_id AND status = :status ORDER BY created_at ASC, id ASC');
        $stmt->execute(['article_id' => $articleId, 'status' => 'approved']);
        $items = $stmt->fetchAll();

        $roots = [];
        $repliesByParent = [];
        foreach ($items as $item) {
            $item['replies'] = [];
            if (!empty($item['parent_id'])) {
                $repliesByParent[(int) $item['parent_id']][] = $item;
                continue;
            }
            $roots[(int) $item['id']] = $item;
        }

        foreach ($roots as $id => $root) {
            $roots[$id]['replies'] = $repliesByParent[$id] ?? [];
        }

        return array_values($roots);
    }

    public function approvedMessages(int $page = 1, int $perPage = 12): array
    {
        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM guestbook_messages WHERE status = :status');
        $countStmt->execute(['status' => 'approved']);
        $total = (int) $countStmt->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT * FROM guestbook_messages WHERE status = :status ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':status', 'approved');
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
        ];
    }

    public function createComment(int $articleId, array $data): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO article_comments (article_id, parent_id, admin_user_id, nickname, email, content, is_admin_reply, status, created_at, updated_at) VALUES (:article_id, NULL, NULL, :nickname, :email, :content, 0, :status, :created_at, :updated_at)');
        $stmt->execute([
            'article_id' => $articleId,
            'nickname' => trim((string) ($data['nickname'] ?? '')),
            'email' => $this->nullableString($data['email'] ?? null),
            'content' => trim((string) ($data['content'] ?? '')),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function createAdminReply(int $commentId, array $admin, string $content): void
    {
        $parent = $this->findCommentById($commentId);
        if ($parent === null) {
            return;
        }

        $rootId = (int) ($parent['parent_id'] ?: $parent['id']);
        $stmt = $this->pdo->prepare('INSERT INTO article_comments (article_id, parent_id, admin_user_id, nickname, email, content, is_admin_reply, status, created_at, updated_at) VALUES (:article_id, :parent_id, :admin_user_id, :nickname, NULL, :content, 1, :status, :created_at, :updated_at)');
        $stmt->execute([
            'article_id' => (int) $parent['article_id'],
            'parent_id' => $rootId,
            'admin_user_id' => (int) ($admin['id'] ?? 0),
            'nickname' => trim((string) ($admin['display_name'] ?? $admin['username'] ?? 'Admin')),
            'content' => trim($content),
            'status' => 'approved',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function createMessage(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO guestbook_messages (nickname, email, subject, content, status, created_at, updated_at) VALUES (:nickname, :email, :subject, :content, :status, :created_at, :updated_at)');
        $stmt->execute([
            'nickname' => trim((string) ($data['nickname'] ?? '')),
            'email' => $this->nullableString($data['email'] ?? null),
            'subject' => $this->nullableString($data['subject'] ?? null),
            'content' => trim((string) ($data['content'] ?? '')),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function paginateComments(string $query = '', string $status = '', int $page = 1, int $perPage = 15): array
    {
        $where = [];
        $params = [];
        if ($query !== '') {
            $where[] = '(ac.nickname LIKE :q OR ac.content LIKE :q OR a.title LIKE :q OR parent.nickname LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }
        if ($status !== '') {
            $where[] = 'ac.status = :status';
            $params['status'] = $status;
        }
        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

        $baseSql = ' FROM article_comments ac INNER JOIN articles a ON a.id = ac.article_id LEFT JOIN article_comments parent ON parent.id = ac.parent_id';
        $countStmt = $this->pdo->prepare('SELECT COUNT(*)' . $baseSql . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT ac.*, a.title AS article_title, a.slug AS article_slug, parent.nickname AS parent_nickname, parent.content AS parent_content' . $baseSql . $whereSql . ' ORDER BY ac.created_at DESC, ac.id DESC LIMIT :limit OFFSET :offset');
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
            'status' => $status,
        ];
    }

    public function paginateMessages(string $query = '', string $status = '', int $page = 1, int $perPage = 15): array
    {
        $where = [];
        $params = [];
        if ($query !== '') {
            $where[] = '(nickname LIKE :q OR subject LIKE :q OR content LIKE :q)';
            $params['q'] = '%' . $query . '%';
        }
        if ($status !== '') {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }
        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM guestbook_messages' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare('SELECT * FROM guestbook_messages' . $whereSql . ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
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
            'status' => $status,
        ];
    }

    public function findCommentById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM article_comments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function setCommentStatus(int $id, string $status): void
    {
        $comment = $this->findCommentById($id);
        if ($comment === null) {
            return;
        }

        if (empty($comment['parent_id'])) {
            $stmt = $this->pdo->prepare('UPDATE article_comments SET status = :status, updated_at = :updated_at WHERE id = :id OR parent_id = :id');
            $stmt->execute(['id' => $id, 'status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
            return;
        }

        $stmt = $this->pdo->prepare('UPDATE article_comments SET status = :status, updated_at = :updated_at WHERE id = :id');
        $stmt->execute(['id' => $id, 'status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function setMessageStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE guestbook_messages SET status = :status, updated_at = :updated_at WHERE id = :id');
        $stmt->execute(['id' => $id, 'status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function deleteComment(int $id): void
    {
        $comment = $this->findCommentById($id);
        if ($comment === null) {
            return;
        }

        if (empty($comment['parent_id'])) {
            $stmt = $this->pdo->prepare('DELETE FROM article_comments WHERE id = :id OR parent_id = :id');
            $stmt->execute(['id' => $id]);
            return;
        }

        $stmt = $this->pdo->prepare('DELETE FROM article_comments WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function deleteMessage(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM guestbook_messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
