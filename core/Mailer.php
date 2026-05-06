<?php

namespace Core;

class Mailer
{
    public static function sendText(array $config, string $to, string $subject, string $message, string $replyTo = ''): bool
    {
        return self::send($config, $to, $subject, nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')), $message, $replyTo);
    }

    public static function sendHtml(array $config, string $to, string $subject, string $html, string $text, string $replyTo = ''): bool
    {
        return self::send($config, $to, $subject, $html, $text, $replyTo);
    }

    private static function send(array $config, string $to, string $subject, string $html, string $text, string $replyTo = ''): bool
    {
        $mailConfig = $config['mail'] ?? [];
        if (!($mailConfig['enabled'] ?? false)) {
            return false;
        }

        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $from = trim((string) ($mailConfig['from'] ?? ''));
        $fromName = trim((string) ($mailConfig['from_name'] ?? ($config['name'] ?? 'Yuexia')));
        if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $boundary = '=_yuexia_' . md5((string) microtime(true));
        $headers = [
            'MIME-Version: 1.0',
            'From: ' . self::encodeName($fromName) . ' <' . $from . '>',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        $body = [];
        $body[] = '--' . $boundary;
        $body[] = 'Content-Type: text/plain; charset=UTF-8';
        $body[] = 'Content-Transfer-Encoding: 8bit';
        $body[] = '';
        $body[] = $text;
        $body[] = '--' . $boundary;
        $body[] = 'Content-Type: text/html; charset=UTF-8';
        $body[] = 'Content-Transfer-Encoding: 8bit';
        $body[] = '';
        $body[] = $html;
        $body[] = '--' . $boundary . '--';

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        return @mail($to, $encodedSubject, implode("\r\n", $body), implode("\r\n", $headers));
    }

    private static function encodeName(string $name): string
    {
        return '=?UTF-8?B?' . base64_encode($name) . '?=';
    }
}
