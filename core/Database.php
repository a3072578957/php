<?php

namespace Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $pdo = null;
    private static bool $initialized = false;

    public static function connection(array $config): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $db = $config['db'] ?? [];
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $db['host'] ?? '127.0.0.1',
            $db['port'] ?? 3306,
            $db['database'] ?? 'yuexia',
            $db['charset'] ?? 'utf8mb4'
        );

        try {
            self::$pdo = new PDO($dsn, $db['username'] ?? 'root', $db['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('MySQL connection failed. Please check config/app.php database settings and ensure the database exists.');
        }

        if (!self::$initialized) {
            self::ensureSchema(self::$pdo);
            self::$initialized = true;
        }

        return self::$pdo;
    }

    private static function ensureSchema(PDO $pdo): void
    {
        $queries = [
            "CREATE TABLE IF NOT EXISTS categories (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(32) NOT NULL,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(120) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uniq_type_slug (type, slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS articles (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id INT UNSIGNED NULL,
                title VARCHAR(180) NOT NULL,
                slug VARCHAR(190) NOT NULL UNIQUE,
                excerpt TEXT NOT NULL,
                content MEDIUMTEXT NOT NULL,
                cover VARCHAR(255) NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX idx_articles_category (category_id),
                CONSTRAINT fk_articles_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS portfolio_items (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id INT UNSIGNED NULL,
                title VARCHAR(180) NOT NULL,
                slug VARCHAR(190) NOT NULL UNIQUE,
                summary TEXT NOT NULL,
                content MEDIUMTEXT NOT NULL,
                stack VARCHAR(255) NULL,
                image VARCHAR(255) NULL,
                link VARCHAR(255) NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX idx_portfolio_category (category_id),
                CONSTRAINT fk_portfolio_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS tags (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(120) NOT NULL UNIQUE,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS article_tag (
                article_id INT UNSIGNED NOT NULL,
                tag_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (article_id, tag_id),
                CONSTRAINT fk_article_tag_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
                CONSTRAINT fk_article_tag_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS media (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                file_name VARCHAR(190) NOT NULL,
                file_url VARCHAR(255) NOT NULL,
                folder VARCHAR(60) NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                file_size INT UNSIGNED NOT NULL DEFAULT 0,
                alt_text VARCHAR(190) NULL,
                created_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS admin_users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(80) NOT NULL UNIQUE,
                display_name VARCHAR(120) NOT NULL,
                email VARCHAR(160) NULL,
                role VARCHAR(30) NOT NULL DEFAULT 'editor',
                password_hash VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uniq_admin_email (email),
                INDEX idx_admin_role (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS admin_password_resets (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                admin_user_id INT UNSIGNED NOT NULL,
                token_hash VARCHAR(64) NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                used_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_password_resets_user (admin_user_id),
                INDEX idx_password_resets_expires (expires_at),
                CONSTRAINT fk_password_resets_user FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS article_comments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                article_id INT UNSIGNED NOT NULL,
                parent_id INT UNSIGNED NULL,
                admin_user_id INT UNSIGNED NULL,
                nickname VARCHAR(80) NOT NULL,
                email VARCHAR(160) NULL,
                content TEXT NOT NULL,
                is_admin_reply TINYINT(1) NOT NULL DEFAULT 0,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX idx_comment_article (article_id),
                INDEX idx_comment_status (status),
                INDEX idx_comment_parent (parent_id),
                CONSTRAINT fk_comment_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS guestbook_messages (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nickname VARCHAR(80) NOT NULL,
                email VARCHAR(160) NULL,
                subject VARCHAR(160) NULL,
                content TEXT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX idx_guestbook_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS admin_action_logs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                admin_user_id INT UNSIGNED NULL,
                admin_name VARCHAR(120) NOT NULL,
                action VARCHAR(120) NOT NULL,
                target_type VARCHAR(80) NULL,
                target_id INT UNSIGNED NULL,
                description VARCHAR(255) NOT NULL,
                request_path VARCHAR(255) NULL,
                request_method VARCHAR(10) NULL,
                ip_address VARCHAR(64) NULL,
                context_json MEDIUMTEXT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_admin_logs_action (action),
                INDEX idx_admin_logs_created (created_at),
                INDEX idx_admin_logs_user (admin_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        ];

        foreach ($queries as $query) {
            $pdo->exec($query);
        }

        self::ensureColumn($pdo, 'admin_users', 'email', "ALTER TABLE admin_users ADD COLUMN email VARCHAR(160) NULL AFTER display_name");
        self::ensureColumn($pdo, 'admin_users', 'role', "ALTER TABLE admin_users ADD COLUMN role VARCHAR(30) NOT NULL DEFAULT 'editor' AFTER email");
        $pdo->exec("UPDATE admin_users SET email = NULL WHERE TRIM(COALESCE(email, '')) = ''");
        self::ensureIndex($pdo, 'admin_users', 'uniq_admin_email', 'ALTER TABLE admin_users ADD UNIQUE KEY uniq_admin_email (email)');
        self::ensureIndex($pdo, 'admin_users', 'idx_admin_role', 'ALTER TABLE admin_users ADD INDEX idx_admin_role (role)');
        $pdo->exec("UPDATE admin_users SET role = 'super_admin' WHERE role IS NULL OR role = ''");

        self::ensureColumn($pdo, 'admin_password_resets', 'used_at', 'ALTER TABLE admin_password_resets ADD COLUMN used_at DATETIME NULL AFTER expires_at');
        self::ensureIndex($pdo, 'admin_password_resets', 'idx_password_resets_user', 'ALTER TABLE admin_password_resets ADD INDEX idx_password_resets_user (admin_user_id)');
        self::ensureIndex($pdo, 'admin_password_resets', 'idx_password_resets_expires', 'ALTER TABLE admin_password_resets ADD INDEX idx_password_resets_expires (expires_at)');

        self::ensureColumn($pdo, 'article_comments', 'parent_id', 'ALTER TABLE article_comments ADD COLUMN parent_id INT UNSIGNED NULL AFTER article_id');
        self::ensureColumn($pdo, 'article_comments', 'admin_user_id', 'ALTER TABLE article_comments ADD COLUMN admin_user_id INT UNSIGNED NULL AFTER parent_id');
        self::ensureColumn($pdo, 'article_comments', 'is_admin_reply', 'ALTER TABLE article_comments ADD COLUMN is_admin_reply TINYINT(1) NOT NULL DEFAULT 0 AFTER content');
        self::ensureIndex($pdo, 'article_comments', 'idx_comment_parent', 'ALTER TABLE article_comments ADD INDEX idx_comment_parent (parent_id)');

        self::ensureColumn($pdo, 'admin_action_logs', 'request_path', 'ALTER TABLE admin_action_logs ADD COLUMN request_path VARCHAR(255) NULL AFTER description');
        self::ensureColumn($pdo, 'admin_action_logs', 'request_method', 'ALTER TABLE admin_action_logs ADD COLUMN request_method VARCHAR(10) NULL AFTER request_path');
        self::ensureColumn($pdo, 'admin_action_logs', 'ip_address', 'ALTER TABLE admin_action_logs ADD COLUMN ip_address VARCHAR(64) NULL AFTER request_method');
        self::ensureColumn($pdo, 'admin_action_logs', 'context_json', 'ALTER TABLE admin_action_logs ADD COLUMN context_json MEDIUMTEXT NULL AFTER ip_address');
    }

    private static function ensureColumn(PDO $pdo, string $table, string $column, string $sql): void
    {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE :column");
        $stmt->execute(['column' => $column]);
        if (!$stmt->fetch()) {
            $pdo->exec($sql);
        }
    }

    private static function ensureIndex(PDO $pdo, string $table, string $index, string $sql): void
    {
        $stmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE Key_name = :index_name");
        $stmt->execute(['index_name' => $index]);
        if (!$stmt->fetch()) {
            $pdo->exec($sql);
        }
    }
}