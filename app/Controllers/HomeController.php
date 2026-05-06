<?php

namespace App\Controllers;

use App\Models\ContentRepository;
use Core\Controller;

class HomeController extends Controller
{
    public function index(array $params = []): string
    {
        $repository = new ContentRepository($this->config);

        return $this->render('home/index', [
            'siteName' => $this->config['name'],
            'tagline' => $this->config['tagline'],
            'heroSlides' => [
                [
                    'eyebrow' => 'Moonlit Vision',
                    'title' => 'A personal site that looks like a midnight stage instead of a boring profile page.',
                    'description' => 'Yuexia runs on a self-made traditional PHP framework with a vivid jQuery front-end and room for real content management.',
                    'button' => 'See article system',
                    'anchor' => '#articles',
                ],
                [
                    'eyebrow' => 'Community Layer',
                    'title' => 'Articles, works, comments, guestbook, and multi-admin management now live inside one lightweight custom architecture.',
                    'description' => 'The site is no longer just for display. Visitors can search, browse, comment, and leave messages while admins collaborate in the backend.',
                    'button' => 'Open guestbook',
                    'anchor' => '/guestbook',
                ],
                [
                    'eyebrow' => 'Self-Made CMS',
                    'title' => 'A small content platform without Composer or third-party PHP frameworks.',
                    'description' => 'Routing, views, login, MySQL storage, moderation, and admin management are all handcrafted for this project.',
                    'button' => 'Open admin',
                    'anchor' => '/admin',
                ],
            ],
            'highlights' => [
                ['value' => '12', 'label' => 'Core modules'],
                ['value' => '03', 'label' => 'Hero slides'],
                ['value' => '04', 'label' => 'Interactive flows'],
            ],
            'features' => [
                [
                    'title' => 'Article publishing and comments',
                    'description' => 'List page, detail page, tag/category search, and moderated comment submission are all available now.',
                ],
                [
                    'title' => 'Portfolio showcase',
                    'description' => 'Works can be listed publicly with dedicated detail pages for fuller storytelling and structured filtering.',
                ],
                [
                    'title' => 'Multi-admin collaboration',
                    'description' => 'A login-protected backend lets multiple administrators manage articles, works, messages, comments, media, and taxonomy data.',
                ],
            ],
            'timeline' => [
                [
                    'year' => 'Step 1',
                    'title' => 'Custom traditional framework base',
                    'description' => 'The project starts with routing, controllers, requests, and views written from scratch.',
                ],
                [
                    'year' => 'Step 2',
                    'title' => 'Real content modules go online',
                    'description' => 'Articles and portfolio entries are stored in MySQL and rendered across the public site.',
                ],
                [
                    'year' => 'Step 3',
                    'title' => 'Community and moderation layer',
                    'description' => 'Comments, guestbook messages, password-hashed admin accounts, and moderation tools make the site practical instead of just decorative.',
                ],
            ],
            'latestArticles' => $repository->latestArticles(3),
            'latestPortfolio' => $repository->latestPortfolio(3),
        ]);
    }
}
