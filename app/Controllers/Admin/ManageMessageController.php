<?php

namespace App\Controllers\Admin;

use App\Models\InteractionRepository;
use Core\Controller;

class ManageMessageController extends Controller
{
    private InteractionRepository $repository;

    public function __construct(array $config, \Core\Request $request)
    {
        parent::__construct($config, $request);
        $this->repository = new InteractionRepository($config);
    }

    public function index(array $params = []): string
    {
        $this->requirePermission('moderation.manage');
        $query = trim((string) $this->request->query('q', ''));
        $status = trim((string) $this->request->query('status', ''));
        $page = max(1, (int) $this->request->query('page', 1));
        $result = $this->repository->paginateMessages($query, $status, $page, 15);

        return $this->render('admin/messages/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Manage Messages',
            'messages' => $result['items'],
            'pagination' => $result,
        ], 'layouts/admin');
    }

    public function approve(array $params = []): string
    {
        $this->requirePermission('moderation.manage');
        $this->requireCsrf();
        $messageId = (int) ($params['id'] ?? 0);
        $this->repository->setMessageStatus($messageId, 'approved');
        $this->logAction('message.approve', 'Approved a guestbook message.', 'message', $messageId);
        $this->flash('success', 'Message approved.');
        $this->redirect('/admin/messages');
    }

    public function pending(array $params = []): string
    {
        $this->requirePermission('moderation.manage');
        $this->requireCsrf();
        $messageId = (int) ($params['id'] ?? 0);
        $this->repository->setMessageStatus($messageId, 'pending');
        $this->logAction('message.pending', 'Moved a guestbook message back to pending.', 'message', $messageId);
        $this->flash('success', 'Message moved back to pending.');
        $this->redirect('/admin/messages');
    }

    public function delete(array $params = []): string
    {
        $this->requirePermission('moderation.manage');
        $this->requireCsrf();
        $messageId = (int) ($params['id'] ?? 0);
        $this->repository->deleteMessage($messageId);
        $this->logAction('message.delete', 'Deleted a guestbook message.', 'message', $messageId);
        $this->flash('success', 'Message deleted.');
        $this->redirect('/admin/messages');
    }
}
