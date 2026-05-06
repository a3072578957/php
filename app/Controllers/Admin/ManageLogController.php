<?php

namespace App\Controllers\Admin;

use App\Models\AdminActionLogRepository;
use Core\Controller;

class ManageLogController extends Controller
{
    private AdminActionLogRepository $repository;

    public function __construct(array $config, \Core\Request $request)
    {
        parent::__construct($config, $request);
        $this->repository = new AdminActionLogRepository($config);
    }

    public function index(array $params = []): string
    {
        $this->requirePermission('logs.view');
        $query = trim((string) $this->request->query('q', ''));
        $action = trim((string) $this->request->query('action', ''));
        $page = max(1, (int) $this->request->query('page', 1));
        $result = $this->repository->paginateLogs($query, $action, $page, 20);

        return $this->render('admin/logs/index', [
            'siteName' => $this->config['name'],
            'tagline' => 'Operation Logs',
            'logs' => $result['items'],
            'pagination' => $result,
            'actions' => $this->repository->distinctActions(),
        ], 'layouts/admin');
    }

    public function exportCsv(array $params = []): string
    {
        $this->requirePermission('logs.view');
        $query = trim((string) $this->request->query('q', ''));
        $action = trim((string) $this->request->query('action', ''));
        $logs = $this->repository->listLogs($query, $action);

        $this->logAction('log.export_csv', 'Exported backend operation logs as CSV.', 'admin_action_log', null, [
            'query' => $query,
            'action' => $action,
            'count' => count($logs),
        ]);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="yuexia-logs-' . date('Ymd-His') . '.csv"');

        $output = fopen('php://output', 'wb');
        if ($output === false) {
            $this->flash('error', 'Unable to export CSV right now.');
            $this->redirect('/admin/logs');
        }

        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', 'Admin Name', 'Action', 'Target Type', 'Target ID', 'Description', 'Request Method', 'Request Path', 'IP Address', 'Context JSON', 'Created At']);
        foreach ($logs as $log) {
            fputcsv($output, [
                (int) ($log['id'] ?? 0),
                (string) ($log['admin_name'] ?? ''),
                (string) ($log['action'] ?? ''),
                (string) ($log['target_type'] ?? ''),
                (string) ($log['target_id'] ?? ''),
                (string) ($log['description'] ?? ''),
                (string) ($log['request_method'] ?? ''),
                (string) ($log['request_path'] ?? ''),
                (string) ($log['ip_address'] ?? ''),
                (string) ($log['context_json'] ?? ''),
                (string) ($log['created_at'] ?? ''),
            ]);
        }
        fclose($output);
        exit;
    }
}