<?php

namespace App\Controllers\Admin;

use App\Models\AdminActionLogRepository;
use App\Models\AdminPasswordResetRepository;
use App\Models\UserRepository;
use Core\Controller;
use Core\Mailer;
use Core\Validator;

class AuthController extends Controller
{
    public function show(array $params = []): string
    {
        if ($this->isAdminLoggedIn()) {
            $this->redirect('/admin');
        }

        return $this->render('admin/login', [
            'siteName' => $this->config['name'],
            'tagline' => 'Admin Login',
        ], 'layouts/admin_guest');
    }

    public function showForgotPassword(array $params = []): string
    {
        if ($this->isAdminLoggedIn()) {
            $this->redirect('/admin');
        }

        return $this->render('admin/forgot_password', [
            'siteName' => $this->config['name'],
            'tagline' => 'Forgot Password',
        ], 'layouts/admin_guest');
    }

    public function sendResetLink(array $params = []): string
    {
        $this->requireCsrf();

        if (!$this->passwordResetMailConfigured()) {
            $this->flash('error', 'Password reset mail is not configured yet. Please set config/app.php mail options first.');
            $this->redirect('/admin/forgot-password');
        }

        $data = $this->request->all();
        $error = Validator::requireString($data, 'username', 'Username', 3, 80);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/forgot-password');
        }

        $username = trim((string) ($data['username'] ?? ''));
        $repository = new UserRepository($this->config);
        $admin = $repository->findByUsername($username);

        if ($admin !== null && !empty($admin['email']) && filter_var((string) $admin['email'], FILTER_VALIDATE_EMAIL)) {
            $resetRepository = new AdminPasswordResetRepository($this->config);
            $token = $resetRepository->createToken((int) $admin['id']);
            $this->sendPasswordResetEmail($admin, $token);

            $logger = new AdminActionLogRepository($this->config);
            $logger->createLog([
                'admin_user_id' => (int) $admin['id'],
                'admin_name' => (string) ($admin['display_name'] ?: $admin['username']),
                'action' => 'auth.password_reset_requested',
                'target_type' => 'admin_user',
                'target_id' => (int) $admin['id'],
                'description' => 'Requested an admin password reset email.',
                'request_path' => $this->request->path(),
                'request_method' => $this->request->method(),
                'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                'context' => [
                    'username' => $admin['username'],
                    'email' => $admin['email'],
                ],
            ]);
        }

        $this->flash('success', 'If the account exists and has a valid email address, a reset link has been sent.');
        $this->redirect('/admin/forgot-password');
    }

    public function showResetPassword(array $params = []): string
    {
        $token = trim((string) $this->request->query('token', ''));
        $resetRepository = new AdminPasswordResetRepository($this->config);
        $record = $resetRepository->findValidByToken($token);

        return $this->render('admin/reset_password', [
            'siteName' => $this->config['name'],
            'tagline' => 'Reset Password',
            'token' => $token,
            'validToken' => $record !== null,
            'accountLabel' => $record ? ((string) ($record['display_name'] ?: $record['username'])) : '',
        ], 'layouts/admin_guest');
    }

    public function resetPassword(array $params = []): string
    {
        $this->requireCsrf();

        $data = $this->request->all();
        $token = trim((string) ($data['token'] ?? ''));
        $newPassword = trim((string) ($data['new_password'] ?? ''));
        $confirmPassword = trim((string) ($data['confirm_password'] ?? ''));

        if ($token === '') {
            $this->flash('error', 'Reset token is required.');
            $this->redirect('/admin/forgot-password');
        }
        if (mb_strlen($newPassword) < 8) {
            $this->flash('error', 'New password must be at least 8 characters.');
            $this->redirect('/admin/reset-password?token=' . urlencode($token));
        }
        if ($newPassword !== $confirmPassword) {
            $this->flash('error', 'New password and confirm password do not match.');
            $this->redirect('/admin/reset-password?token=' . urlencode($token));
        }

        $resetRepository = new AdminPasswordResetRepository($this->config);
        $record = $resetRepository->findValidByToken($token);
        if ($record === null) {
            $this->flash('error', 'This reset link is invalid or has expired. Please request a new one.');
            $this->redirect('/admin/forgot-password');
        }

        $userRepository = new UserRepository($this->config);
        $userRepository->updatePassword((int) $record['admin_user_id'], $newPassword);
        $resetRepository->markUsed((int) $record['id']);
        $resetRepository->markAllUsedForUser((int) $record['admin_user_id']);

        $logger = new AdminActionLogRepository($this->config);
        $logger->createLog([
            'admin_user_id' => (int) $record['admin_user_id'],
            'admin_name' => (string) ($record['display_name'] ?: $record['username']),
            'action' => 'auth.password_reset_completed',
            'target_type' => 'admin_user',
            'target_id' => (int) $record['admin_user_id'],
            'description' => 'Reset an admin password through the recovery flow.',
            'request_path' => $this->request->path(),
            'request_method' => $this->request->method(),
            'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            'context' => [
                'username' => $record['username'],
            ],
        ]);

        $this->flash('success', 'Password reset successful. Please log in with your new password.');
        $this->redirect('/admin/login');
    }

    public function login(array $params = []): string
    {
        $this->requireCsrf();

        $data = $this->request->all();
        $error = Validator::requireString($data, 'username', 'Username', 3, 50)
            ?: Validator::requireString($data, 'password', 'Password', 6, 100);

        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/login');
        }

        $username = trim((string) $data['username']);
        $password = trim((string) $data['password']);
        $repository = new UserRepository($this->config);
        $admin = $repository->findByUsername($username);

        if ($admin && password_verify($password, (string) $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = (int) $admin['id'];
            $_SESSION['admin_name'] = (string) ($admin['display_name'] ?: $admin['username']);
            $_SESSION['admin_role'] = (string) ($admin['role'] ?? 'editor');
            $this->logAction('auth.login', 'Administrator logged in successfully.', 'admin_user', (int) $admin['id'], ['username' => $admin['username']]);
            $this->flash('success', 'Welcome back, admin.');
            $this->redirect('/admin');
        }

        $this->logFailedLogin($username, $admin !== null ? (int) $admin['id'] : null, $admin !== null ? 'password_mismatch' : 'user_not_found');
        $this->flash('error', 'Invalid username or password.');
        $this->redirect('/admin/login');
    }

    public function showPassword(array $params = []): string
    {
        $this->requireAdmin();
        return $this->render('admin/password', [
            'siteName' => $this->config['name'],
            'tagline' => 'Change Password',
        ], 'layouts/admin');
    }

    public function updatePassword(array $params = []): string
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $data = $this->request->all();
        $currentPassword = trim((string) ($data['current_password'] ?? ''));
        $newPassword = trim((string) ($data['new_password'] ?? ''));
        $confirmPassword = trim((string) ($data['confirm_password'] ?? ''));

        if ($currentPassword === '') {
            $this->flash('error', 'Current password is required.');
            $this->redirect('/admin/password');
        }
        if (mb_strlen($newPassword) < 8) {
            $this->flash('error', 'New password must be at least 8 characters.');
            $this->redirect('/admin/password');
        }
        if ($newPassword !== $confirmPassword) {
            $this->flash('error', 'New password and confirm password do not match.');
            $this->redirect('/admin/password');
        }

        $repository = new UserRepository($this->config);
        $admin = $repository->findById((int) ($this->currentAdminId() ?? 0));
        if ($admin === null) {
            $this->flash('error', 'Current admin account could not be found.');
            $this->redirect('/admin/password');
        }
        if (!password_verify($currentPassword, (string) $admin['password_hash'])) {
            $this->flash('error', 'Current password is incorrect.');
            $this->redirect('/admin/password');
        }

        $repository->updatePassword((int) $admin['id'], $newPassword);
        $this->logAction('auth.password_change', 'Administrator changed account password.', 'admin_user', (int) $admin['id']);
        $this->flash('success', 'Password updated successfully.');
        $this->redirect('/admin/password');
    }

    public function logout(array $params = []): string
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $adminId = $this->currentAdminId();
        $this->logAction('auth.logout', 'Administrator logged out.', 'admin_user', $adminId);

        unset($_SESSION['admin_logged_in'], $_SESSION['admin_user_id'], $_SESSION['admin_name'], $_SESSION['admin_role'], $_SESSION['_csrf_token']);
        session_regenerate_id(true);

        $this->flash('success', 'You have logged out.');
        $this->redirect('/admin/login');
    }

    private function logFailedLogin(string $username, ?int $adminId, string $reason): void
    {
        $logger = new AdminActionLogRepository($this->config);
        $logger->createLog([
            'admin_user_id' => $adminId,
            'admin_name' => $username !== '' ? $username : 'Unknown',
            'action' => 'auth.login_failed',
            'target_type' => 'admin_user',
            'target_id' => $adminId,
            'description' => 'Failed administrator login attempt.',
            'request_path' => $this->request->path(),
            'request_method' => $this->request->method(),
            'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            'context' => [
                'username' => $username,
                'reason' => $reason,
            ],
        ]);
    }

    private function passwordResetMailConfigured(): bool
    {
        $mail = $this->config['mail'] ?? [];
        return !empty($mail['enabled']) && filter_var((string) ($mail['from'] ?? ''), FILTER_VALIDATE_EMAIL);
    }

    private function sendPasswordResetEmail(array $admin, string $token): void
    {
        $resetUrl = $this->buildAbsoluteUrl('/admin/reset-password?token=' . urlencode($token));
        $siteName = (string) ($this->config['name'] ?? 'Yuexia');
        $subject = trim((string) (($this->config['mail']['subject_prefix'] ?? '[' . $siteName . '] ') . 'Admin Password Reset'));
        $displayName = (string) ($admin['display_name'] ?: $admin['username']);

        $html = '<div style="font-family:Arial,Helvetica,sans-serif;background:#0c1022;padding:32px;color:#eef2ff;">'
            . '<div style="max-width:640px;margin:0 auto;background:rgba(18,26,54,.94);border:1px solid rgba(122,162,255,.24);border-radius:18px;overflow:hidden;box-shadow:0 18px 60px rgba(4,10,26,.45);">'
            . '<div style="padding:28px 32px;background:linear-gradient(135deg,#122456,#264f9e 60%,#60b7ff);">'
            . '<h1 style="margin:0;font-size:26px;color:#fff;">' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . ' Admin Password Reset</h1>'
            . '<p style="margin:10px 0 0;color:rgba(255,255,255,.84);font-size:14px;">This email was generated by the admin password recovery workflow.</p>'
            . '</div>'
            . '<div style="padding:30px 32px;line-height:1.75;color:#dfe7ff;font-size:15px;">'
            . '<p style="margin-top:0;">Hello, ' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . '.</p>'
            . '<p>We received a request to reset your admin password. Use the button below to continue. This link will expire in <strong>1 hour</strong>.</p>'
            . '<p style="margin:28px 0;">'
            . '<a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:14px 26px;border-radius:999px;background:linear-gradient(135deg,#4f7cff,#70d6ff);color:#081122;text-decoration:none;font-weight:700;">Reset Password</a>'
            . '</p>'
            . '<p>If the button does not work, copy and open this link in your browser:</p>'
            . '<p style="word-break:break-all;color:#8fd6ff;">' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p>If you did not request this reset, you can safely ignore this email.</p>'
            . '</div>'
            . '</div>'
            . '</div>';

        $text = $siteName . " Admin Password Reset\n\n"
            . 'Hello, ' . $displayName . ".\n"
            . "We received a request to reset your admin password. Open the link below within 1 hour:\n"
            . $resetUrl . "\n\n"
            . "If you did not request this reset, you can safely ignore this email.";

        Mailer::sendHtml($this->config, (string) $admin['email'], $subject, $html, $text);
    }

    private function buildAbsoluteUrl(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $baseUrl = trim((string) ($this->config['base_url'] ?? ''));

        if (str_starts_with($baseUrl, 'http://') || str_starts_with($baseUrl, 'https://')) {
            return rtrim($baseUrl, '/') . $path;
        }

        $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host !== '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $prefix = ($baseUrl !== '' && $baseUrl !== '/') ? '/' . trim($baseUrl, '/') : '';
            return $scheme . '://' . $host . $prefix . $path;
        }

        if ($baseUrl !== '' && $baseUrl !== '/') {
            return rtrim($baseUrl, '/') . $path;
        }

        return $path;
    }
}