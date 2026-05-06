<?php

namespace App\Controllers\Admin;

use App\Models\InteractionRepository;
use App\Models\UserRepository;
use Core\Controller;
use Core\Validator;

class ManageCommentController extends Controller
{
    private InteractionRepository $repository;
    private UserRepository $userRepository;

    public function __construct(array $config, \Core\Request $request)
    {
        parent::__construct($config, $request);
        $this->repository = new InteractionRepository($config);
        $this->userRepository = new UserRepository($config);
    }

    public function index(array $params = []): string
    {
        $this->requirePermission('moderation.manage');
        $query = trim((string) $this->request->query('q', ''));
        $status = trim((string) $this->request->query('status', ''));
        $page = max(1, (int) $this->request->query('page', 1));
        $result = $this->repository->paginateComments($query, $status, $page, 15);

        return $this->render('admin/comments/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Manage Comments',
            'comments' => $result['items'],
            'pagination' => $result,
        ], 'layouts/admin');
    }

    public function reply(array $params = []): string
    {
        $this->requirePermission('moderation.manage');
        $comment = $this->repository->findCommentById((int) ($params['id'] ?? 0));
        if ($comment === null) {
            $this->flash('error', 'Comment not found.');
            $this->redirect('/admin/comments');
        }

        return $this->render('admin/comments/reply', [
            'siteName' => $this->config['name'],
            'tagline' => 'Reply Comment',
            'comment' => $comment,
            'action' => '/admin/comments/reply/' . $comment['id'],
        ], 'layouts/admin');
    }

    public function storeReply(array $params = []): string
    {
        $this->requirePermission('moderation.manage');
        $this->requireCsrf();
        $commentId = (int) ($params['id'] ?? 0);
        $comment = $this->repository->findCommentById($commentId);
        if ($comment === null) {
            $this->flash('error', 'Comment not found.');
            $this->redirect('/admin/comments');
        }

        $data = $this->request->all();
        $error = Validator::requireString($data, 'content', 'Reply content', 3, 2000);
        if ($error !== null) {
            $this->flash('error', $error);
            $this->redirect('/admin/comments/reply/' . $commentId);
        }

        $admin = $this->userRepository->findById((int) ($_SESSION['admin_user_id'] ?? 0));
        if ($admin === null) {
            $this->flash('error', 'Current admin account could not be found.');
            $this->redirect('/admin/comments');
        }

        $this->repository->createAdminReply($commentId, $admin, trim((string) $data['content']));
        $this->logAction('comment.reply', 'Published an admin reply to a comment.', 'comment', $commentId);
        $this->flash('success', 'Reply published successfully.');
        $this->redirect('/admin/comments');
    }

    public function approve(array $params = []): string
    {
        $this->requirePermission('moderation.manage');
        $this->requireCsrf();
        $commentId = (int) ($params['id'] ?? 0);
        $this->repository->setCommentStatus($commentId, 'approved');
        $this->logAction('comment.approve', 'Approved a comment thread.', 'comment', $commentId);
        $this->flash('success', 'Comment approved.');
        $this->redirect('/admin/comments');
    }

    public function pending(array $params = []): string
    {
        $this->requirePermission('moderation.manage');
        $this->requireCsrf();
        $commentId = (int) ($params['id'] ?? 0);
        $this->repository->setCommentStatus($commentId, 'pending');
        $this->logAction('comment.pending', 'Moved a comment thread back to pending.', 'comment', $commentId);
        $this->flash('success', 'Comment moved back to pending.');
        $this->redirect('/admin/comments');
    }

    public function delete(array $params = []): string
    {
        $this->requirePermission('moderation.manage');
        $this->requireCsrf();
        $commentId = (int) ($params['id'] ?? 0);
        $this->repository->deleteComment($commentId);
        $this->logAction('comment.delete', 'Deleted a comment thread or reply.', 'comment', $commentId);
        $this->flash('success', 'Comment deleted.');
        $this->redirect('/admin/comments');
    }
}
