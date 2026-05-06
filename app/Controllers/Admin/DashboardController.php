<?php

namespace App\Controllers\Admin;

use App\Models\AdminActionLogRepository;
use App\Models\ContentRepository;
use App\Models\InteractionRepository;
use App\Models\MediaRepository;
use App\Models\UserRepository;
use Core\Controller;

class DashboardController extends Controller
{
    public function index(array $params = []): string
    {
        $this->requirePermission('dashboard.view');
        $repository = new ContentRepository($this->config);
        $mediaRepository = new MediaRepository($this->config);
        $userRepository = new UserRepository($this->config);
        $interactionRepository = new InteractionRepository($this->config);
        $logRepository = new AdminActionLogRepository($this->config);

        return $this->render('admin/dashboard', [
            'siteName' => $this->config['name'],
            'tagline' => 'Admin Dashboard',
            'articleCount' => $repository->articleCount(),
            'portfolioCount' => $repository->portfolioCount(),
            'articleCategoryCount' => $repository->categoryCount('article'),
            'portfolioCategoryCount' => $repository->categoryCount('portfolio'),
            'tagCount' => $repository->tagCount(),
            'mediaCount' => $mediaRepository->mediaCount(),
            'adminUserCount' => $userRepository->countUsers(),
            'commentCount' => $interactionRepository->commentCount(),
            'pendingCommentCount' => $interactionRepository->commentCount('pending'),
            'messageCount' => $interactionRepository->messageCount(),
            'pendingMessageCount' => $interactionRepository->messageCount('pending'),
            'logCount' => $logRepository->countLogs(),
            'adminName' => $_SESSION['admin_name'] ?? 'admin',
            'adminRole' => $_SESSION['admin_role'] ?? '',
        ], 'layouts/admin');
    }
}
