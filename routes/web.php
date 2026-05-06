<?php

use App\Controllers\ArticleController;
use App\Controllers\CategoryController;
use App\Controllers\GuestbookController;
use App\Controllers\HomeController;
use App\Controllers\PortfolioController;
use App\Controllers\SearchController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ManageAdminUserController;
use App\Controllers\Admin\ManageArticleController;
use App\Controllers\Admin\ManageCategoryController;
use App\Controllers\Admin\ManageCommentController;
use App\Controllers\Admin\ManageLogController;
use App\Controllers\Admin\ManageMessageController;
use App\Controllers\Admin\ManagePortfolioController;
use App\Controllers\Admin\ManageTagController;
use App\Controllers\Admin\MediaController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/articles', [ArticleController::class, 'index']],
    ['GET', '/articles/{slug}', [ArticleController::class, 'show']],
    ['POST', '/articles/{slug}/comments', [ArticleController::class, 'storeComment']],
    ['GET', '/portfolio', [PortfolioController::class, 'index']],
    ['GET', '/portfolio/{slug}', [PortfolioController::class, 'show']],
    ['GET', '/search', [SearchController::class, 'index']],
    ['GET', '/categories/{type}/{slug}', [CategoryController::class, 'show']],
    ['GET', '/guestbook', [GuestbookController::class, 'index']],
    ['POST', '/guestbook', [GuestbookController::class, 'store']],

    ['GET', '/admin/login', [AuthController::class, 'show']],
    ['POST', '/admin/login', [AuthController::class, 'login']],
    ['GET', '/admin/forgot-password', [AuthController::class, 'showForgotPassword']],
    ['POST', '/admin/forgot-password', [AuthController::class, 'sendResetLink']],
    ['GET', '/admin/reset-password', [AuthController::class, 'showResetPassword']],
    ['POST', '/admin/reset-password', [AuthController::class, 'resetPassword']],
    ['GET', '/admin/password', [AuthController::class, 'showPassword']],
    ['POST', '/admin/password', [AuthController::class, 'updatePassword']],
    ['POST', '/admin/logout', [AuthController::class, 'logout']],

    ['GET', '/admin', [DashboardController::class, 'index']],
    ['GET', '/admin/logs', [ManageLogController::class, 'index']],
    ['GET', '/admin/logs/export', [ManageLogController::class, 'exportCsv']],
    ['GET', '/admin/users', [ManageAdminUserController::class, 'index']],
    ['GET', '/admin/users/create', [ManageAdminUserController::class, 'create']],
    ['POST', '/admin/users/create', [ManageAdminUserController::class, 'store']],
    ['GET', '/admin/users/edit/{id}', [ManageAdminUserController::class, 'edit']],
    ['POST', '/admin/users/edit/{id}', [ManageAdminUserController::class, 'update']],
    ['POST', '/admin/users/delete/{id}', [ManageAdminUserController::class, 'delete']],

    ['GET', '/admin/articles', [ManageArticleController::class, 'index']],
    ['GET', '/admin/articles/create', [ManageArticleController::class, 'create']],
    ['POST', '/admin/articles/create', [ManageArticleController::class, 'store']],
    ['GET', '/admin/articles/edit/{id}', [ManageArticleController::class, 'edit']],
    ['POST', '/admin/articles/edit/{id}', [ManageArticleController::class, 'update']],
    ['POST', '/admin/articles/delete/{id}', [ManageArticleController::class, 'delete']],

    ['GET', '/admin/portfolio', [ManagePortfolioController::class, 'index']],
    ['GET', '/admin/portfolio/create', [ManagePortfolioController::class, 'create']],
    ['POST', '/admin/portfolio/create', [ManagePortfolioController::class, 'store']],
    ['GET', '/admin/portfolio/edit/{id}', [ManagePortfolioController::class, 'edit']],
    ['POST', '/admin/portfolio/edit/{id}', [ManagePortfolioController::class, 'update']],
    ['POST', '/admin/portfolio/delete/{id}', [ManagePortfolioController::class, 'delete']],

    ['GET', '/admin/categories', [ManageCategoryController::class, 'index']],
    ['GET', '/admin/categories/create', [ManageCategoryController::class, 'create']],
    ['POST', '/admin/categories/create', [ManageCategoryController::class, 'store']],
    ['GET', '/admin/categories/edit/{id}', [ManageCategoryController::class, 'edit']],
    ['POST', '/admin/categories/edit/{id}', [ManageCategoryController::class, 'update']],
    ['POST', '/admin/categories/delete/{id}', [ManageCategoryController::class, 'delete']],

    ['GET', '/admin/tags', [ManageTagController::class, 'index']],
    ['GET', '/admin/tags/create', [ManageTagController::class, 'create']],
    ['POST', '/admin/tags/create', [ManageTagController::class, 'store']],
    ['GET', '/admin/tags/edit/{id}', [ManageTagController::class, 'edit']],
    ['POST', '/admin/tags/edit/{id}', [ManageTagController::class, 'update']],
    ['POST', '/admin/tags/delete/{id}', [ManageTagController::class, 'delete']],

    ['GET', '/admin/media', [MediaController::class, 'index']],
    ['POST', '/admin/media/upload', [MediaController::class, 'upload']],

    ['GET', '/admin/comments', [ManageCommentController::class, 'index']],
    ['GET', '/admin/comments/reply/{id}', [ManageCommentController::class, 'reply']],
    ['POST', '/admin/comments/reply/{id}', [ManageCommentController::class, 'storeReply']],
    ['POST', '/admin/comments/approve/{id}', [ManageCommentController::class, 'approve']],
    ['POST', '/admin/comments/pending/{id}', [ManageCommentController::class, 'pending']],
    ['POST', '/admin/comments/delete/{id}', [ManageCommentController::class, 'delete']],

    ['GET', '/admin/messages', [ManageMessageController::class, 'index']],
    ['POST', '/admin/messages/approve/{id}', [ManageMessageController::class, 'approve']],
    ['POST', '/admin/messages/pending/{id}', [ManageMessageController::class, 'pending']],
    ['POST', '/admin/messages/delete/{id}', [ManageMessageController::class, 'delete']],
];