<?php

namespace App\Controllers;

use App\Models\InteractionRepository;
use Core\Controller;
use Core\Mailer;
use Core\Validator;

class GuestbookController extends Controller
{
    public function index(array $params = []): string
    {
        $repository = new InteractionRepository($this->config);
        $page = max(1, (int) $this->request->query('page', 1));
        $messages = $repository->approvedMessages($page, 12);

        return $this->render('guestbook/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Guestbook',
            'messages' => $messages['items'],
            'pagination' => $messages,
        ]);
    }

    public function store(array $params = []): string
    {
        $this->requireCsrf();
        $data = $this->request->all();
        $error = Validator::requireString($data, 'nickname', 'Nickname', 2, 80)
            ?: Validator::requireString($data, 'content', 'Message', 5, 2000);

        if ($error === null) {
            $email = trim((string) ($data['email'] ?? ''));
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email format is invalid.';
            }
        }

        if ($error === null) {
            $subject = trim((string) ($data['subject'] ?? ''));
            if ($subject !== '' && mb_strlen($subject) > 160) {
                $error = 'Subject must be at most 160 characters.';
            }
        }

        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/guestbook');
        }

        $repository = new InteractionRepository($this->config);
        $repository->createMessage($data);
        $this->sendGuestbookNotification($data);
        $this->flash('success', 'Your message has been submitted and is awaiting review.');
        $this->redirect('/guestbook');
    }

    private function sendGuestbookNotification(array $data): void
    {
        $mailConfig = $this->config['mail'] ?? [];
        $to = trim((string) ($mailConfig['to'] ?? ''));
        if ($to === '') {
            return;
        }

        $prefix = (string) ($mailConfig['subject_prefix'] ?? '[Yuexia] ');
        $subject = $prefix . 'New guestbook message pending review';
        $configuredBaseUrl = trim((string) ($this->config['base_url'] ?? ''));
        $reviewUrl = (filter_var($configuredBaseUrl, FILTER_VALIDATE_URL) ? rtrim($configuredBaseUrl, '/') : '') . '/admin/messages';
        $reviewPath = '/admin/messages';

        $nickname = htmlspecialchars(trim((string) ($data['nickname'] ?? '')), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(trim((string) ($data['email'] ?? '')), ENT_QUOTES, 'UTF-8');
        $subjectText = htmlspecialchars(trim((string) ($data['subject'] ?? '')), ENT_QUOTES, 'UTF-8');
        $content = nl2br(htmlspecialchars(trim((string) ($data['content'] ?? '')), ENT_QUOTES, 'UTF-8'));

        $reviewActionHtml = $reviewUrl !== ''
            ? '<a href="' . htmlspecialchars($reviewUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:12px 22px;border-radius:999px;background:linear-gradient(135deg,#79f1ff,#39a7ff);color:#07111f;text-decoration:none;font-weight:700;">进入审核页</a>'
            : '<div style="display:inline-block;padding:12px 18px;border-radius:16px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#d9edf7;">后台路径：' . htmlspecialchars($reviewPath, ENT_QUOTES, 'UTF-8') . '</div>';

        $html = '<div style="margin:0;padding:32px;background:#09111f;font-family:Segoe UI,Arial,sans-serif;color:#eaf7ff;">'
            . '<div style="max-width:720px;margin:0 auto;background:linear-gradient(180deg,#101a2d 0%,#0b1322 100%);border:1px solid rgba(121,241,255,0.16);border-radius:24px;overflow:hidden;box-shadow:0 24px 70px rgba(0,0,0,0.28);">'
            . '<div style="padding:28px 32px;background:radial-gradient(circle at top right,#1c7ea4 0%,#101a2d 55%,#0b1322 100%);">'
            . '<div style="font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#79f1ff;margin-bottom:10px;">Yuexia Notification</div>'
            . '<h1 style="margin:0;font-size:28px;line-height:1.3;color:#ffffff;">新的留言待审核</h1>'
            . '<p style="margin:12px 0 0;color:#c7dceb;line-height:1.75;">站点收到了一条新的访客留言，已经进入待审核状态。你可以在后台留言审核页查看并处理。</p>'
            . '</div>'
            . '<div style="padding:28px 32px;">'
            . '<table style="width:100%;border-collapse:collapse;">'
            . '<tr><td style="padding:10px 0;color:#79f1ff;width:110px;vertical-align:top;">昵称</td><td style="padding:10px 0;color:#ffffff;">' . $nickname . '</td></tr>'
            . '<tr><td style="padding:10px 0;color:#79f1ff;vertical-align:top;">邮箱</td><td style="padding:10px 0;color:#ffffff;">' . ($email !== '' ? $email : '-') . '</td></tr>'
            . '<tr><td style="padding:10px 0;color:#79f1ff;vertical-align:top;">主题</td><td style="padding:10px 0;color:#ffffff;">' . ($subjectText !== '' ? $subjectText : '-') . '</td></tr>'
            . '</table>'
            . '<div style="margin-top:20px;padding:18px 20px;border-radius:18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#d9edf7;line-height:1.8;">' . $content . '</div>'
            . '<div style="margin-top:24px;">' . $reviewActionHtml . '</div>'
            . '</div>'
            . '</div>'
            . '</div>';

        $text = "A new guestbook message has been submitted.\n\n"
            . 'Nickname: ' . trim((string) ($data['nickname'] ?? '')) . "\n"
            . 'Email: ' . trim((string) ($data['email'] ?? '')) . "\n"
            . 'Subject: ' . trim((string) ($data['subject'] ?? '')) . "\n\n"
            . "Content:\n" . trim((string) ($data['content'] ?? '')) . "\n\n"
            . 'Review it at: ' . ($reviewUrl !== '' ? $reviewUrl : $reviewPath);

        Mailer::sendHtml($this->config, $to, $subject, $html, $text, trim((string) ($data['email'] ?? '')));
    }
}
