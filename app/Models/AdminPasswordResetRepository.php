<?php

namespace App\Models;

use Core\Database;
use PDO;

class AdminPasswordResetRepository
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = Database::connection($config);
    }

    public function createToken(int $adminUserId, int $expiresInSeconds = 3600): string
    {
        $this->purgeExpired();
        $this->markAllUsedForUser($adminUserId);

        $token = bin2hex(random_bytes(32));
        $stmt = $this->pdo->prepare('INSERT INTO admin_password_resets (admin_user_id, token_hash, expires_at, used_at, created_at) VALUES (:admin_user_id, :token_hash, :expires_at, NULL, :created_at)');
        $stmt->execute([
            'admin_user_id' => $adminUserId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + $expiresInSeconds),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    public function findValidByToken(string $token): ?array
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT r.*, u.username, u.display_name, u.email FROM admin_password_resets r INNER JOIN admin_users u ON u.id = r.admin_user_id WHERE r.token_hash = :token_hash AND r.used_at IS NULL AND r.expires_at >= :now LIMIT 1');
        $stmt->execute([
            'token_hash' => hash('sha256', $token),
            'now' => date('Y-m-d H:i:s'),
        ]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE admin_password_resets SET used_at = :used_at WHERE id = :id AND used_at IS NULL');
        $stmt->execute([
            'id' => $id,
            'used_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markAllUsedForUser(int $adminUserId): void
    {
        $stmt = $this->pdo->prepare('UPDATE admin_password_resets SET used_at = :used_at WHERE admin_user_id = :admin_user_id AND used_at IS NULL');
        $stmt->execute([
            'admin_user_id' => $adminUserId,
            'used_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function purgeExpired(): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM admin_password_resets WHERE used_at IS NOT NULL OR expires_at < :now');
        $stmt->execute(['now' => date('Y-m-d H:i:s')]);
    }
}