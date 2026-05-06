<?php

namespace App\Controllers\Admin;

use App\Models\MediaRepository;
use Core\Controller;
use RuntimeException;

class MediaController extends Controller
{
    private MediaRepository $repository;

    public function __construct(array $config, \Core\Request $request)
    {
        parent::__construct($config, $request);
        $this->repository = new MediaRepository($config);
    }

    public function index(array $params = []): string
    {
        $this->requirePermission('media.manage');
        $query = trim((string) $this->request->query('q', ''));
        $page = max(1, (int) $this->request->query('page', 1));
        $picker = (int) $this->request->query('picker', 0) === 1;
        $target = trim((string) $this->request->query('target', ''));
        $mode = trim((string) $this->request->query('mode', 'field'));
        $result = $this->repository->paginateMedia($query, $page, 18);

        return $this->render('admin/media/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Media Library',
            'media' => $result['items'],
            'pagination' => $result,
            'isPicker' => $picker,
            'target' => $target,
            'mode' => $mode,
            'uploadAction' => '/admin/media/upload' . ($picker ? ('?picker=1&target=' . rawurlencode($target) . '&mode=' . rawurlencode($mode)) : ''),
        ], $picker ? 'layouts/admin_guest' : 'layouts/admin');
    }

    public function upload(array $params = []): string
    {
        $this->requirePermission('media.manage');
        $this->requireCsrf();
        $picker = (int) $this->request->query('picker', 0) === 1;
        $target = trim((string) $this->request->query('target', ''));
        $mode = trim((string) $this->request->query('mode', 'field'));

        try {
            $url = $this->storeUpload('media_upload', 'library', (string) $this->request->input('alt_text', ''));
            $this->logAction('media.upload', 'Uploaded an image to media library.', 'media', null, ['file_url' => $url]);
            $this->flash('success', 'Image uploaded to media library.');
        } catch (RuntimeException $exception) {
            $this->flash('error', $exception->getMessage());
        }

        $redirect = '/admin/media';
        if ($picker) {
            $redirect .= '?picker=1&target=' . rawurlencode($target) . '&mode=' . rawurlencode($mode);
        }
        $this->redirect($redirect);
    }

    private function storeUpload(string $field, string $folder, string $altText = ''): string
    {
        $file = $this->request->file($field);
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('Please choose an image first.');
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image upload failed. Please retry.');
        }
        if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
            throw new RuntimeException('Image size must be smaller than 3MB.');
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowed, true)) {
            throw new RuntimeException('Only jpg, jpeg, png, gif, and webp images are allowed.');
        }

        if (!is_string($file['tmp_name'] ?? null) || @getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException('Uploaded file must be a valid image.');
        }

        $targetDir = rtrim($this->config['upload_path'], '/\\') . '/' . $folder;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Upload directory is not writable.');
        }

        $filename = uniqid($folder . '-', true) . '.' . $extension;
        $targetPath = $targetDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Unable to save uploaded image.');
        }

        $url = rtrim($this->config['upload_url'], '/\\') . '/' . $folder . '/' . $filename;
        $this->repository->createMedia([
            'file_name' => (string) ($file['name'] ?? $filename),
            'file_url' => $url,
            'folder' => $folder,
            'mime_type' => (string) ($file['type'] ?? 'image/' . $extension),
            'file_size' => (int) ($file['size'] ?? 0),
            'alt_text' => trim($altText),
        ]);

        return $url;
    }
}
