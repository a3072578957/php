

Yuexia is a personal website project built with a self-made traditional PHP framework.

## Tech Stack

- PHP
- Custom MVC-style framework
- Local jQuery-compatible script
- HTML
- CSS
- MySQL

## Structure

- `public/index.php`: application entry
- `server.php`: router script for PHP built-in server
- `core/`: framework core classes
- `app/Controllers/`: controllers
- `app/Views/`: templates and layouts
- `app/Models/`: MySQL data layer
- `routes/web.php`: route definitions
- `database/schema.sql`: MySQL schema reference
- `public/assets/`: front-end static assets
- `public/uploads/`: uploaded images

## Current Features

- Traditional routing + controller + view rendering
- Homepage with vivid banner carousel and local jQuery effects
- Article list, detail, comments, admin replies, and tag/category filter
- Portfolio list, detail, and category filter
- Global search page with type, category, and tag filters
- Category detail pages with secondary filtering
- Guestbook page with public message submission and moderation flow
- Article tags system with backend management
- Admin login based on password-hashed multi-user accounts
- Role-based admin access with `super_admin`, `editor`, and `moderator`
- Backend management for articles, portfolio, categories, tags, media, comments, messages, and admin users
- Password change page for logged-in administrators
- Password recovery flow with token-based reset email
- Backend operation log page for key admin actions
- Failed login attempts are written into operation logs
- Backend operation logs can be exported as CSV
- Backend image library with upload and picker support
- Rich text editor toolbar for article and portfolio content editing
- CSRF protection for backend and front-end submission forms
- Optional guestbook email notification via styled HTML email and PHP `mail()`
- Optional admin password-reset email via PHP `mail()`
- MySQL storage, no Composer and no external PHP framework

## Admin Login

- URL: `/admin/login`
- Forgot password URL: `/admin/forgot-password`
- Default username: `admin`
- Default password: `yuexia123456`
- Default role: `super_admin`
- Default admin email: empty, please set it from `/admin/users` after first login if you want password recovery to work

The default admin user is auto-created in the database on the first successful connection when the `admin_users` table is empty.

## Roles

- `super_admin`: full access, including admin user management and operation logs
- `editor`: article, portfolio, category, tag, and media management
- `moderator`: comment and guestbook moderation

## Database Setup

1. Create a MySQL database named `yuexia`
2. Update DB credentials in `config/app.php`
3. Start the project
4. Tables will auto-create on first successful connection

You can also review the schema in `database/schema.sql`.

## Mail Configuration

If you want guestbook notifications or admin password reset emails to work, update `config/app.php`:

- `mail.enabled` => `true`
- `mail.to` => recipient email address for guestbook notifications
- `mail.from` => sender email address allowed by your server
- `mail.subject_prefix` => optional mail subject prefix

This feature uses native PHP `mail()`, so your server must be configured to send mail successfully.

## Run Locally

1. Open terminal in project root
2. Run: `php -S 127.0.0.1:8000 server.php`
3. Visit: `http://127.0.0.1:8000`

## Notes

- The project uses MySQL instead of JSON storage.
- Local front-end script is loaded from `/assets/vendor/jquery.local.js` instead of CDN.
- Categories and tags that are already in use should be adjusted from content items before deletion.
- The rich text editor is a lightweight local implementation based on textarea + toolbar interactions.
- New comments and guestbook messages are stored as `pending` by default and require admin review before they appear publicly.
- Admin replies to comments are published directly from the moderation area.
- Key backend actions such as login success/failure, password change, password recovery, CRUD, moderation, media upload, and CSV export are written into `admin_action_logs`.