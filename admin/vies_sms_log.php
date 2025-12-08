<?php
session_start();

// Path to log file
$logFile = __DIR__ . '/logs/sms_log.txt';

// Create if missing
if (!file_exists($logFile)) {
    file_put_contents($logFile, '');
}

$lines = array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)); // newest first

// Pagination setup
$perPage = 20;
$totalLines = count($lines);
$totalPages = ceil($totalLines / $perPage);
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start = ($page - 1) * $perPage;
$paginatedLines = array_slice($lines, $start, $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SMS Log Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .log-entry { font-family: monospace; font-size: 14px; white-space: pre-wrap; }
        .success { background-color: #d4edda; color: #155724; }
        .failed { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body class="p-4">
    <div class="container bg-white shadow rounded p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">📱 Semaphore SMS Log Viewer</h4>
            <div>
                <a href="?page=<?= $page ?>" class="btn btn-outline-primary btn-sm">🔄 Refresh</a>
            </div>
        </div>

        <?php if (empty($lines)) : ?>
            <div class="alert alert-info">No SMS logs found yet.</div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="20%">Timestamp</th>
                            <th>Log Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($paginatedLines as $line): ?>
                        <?php
                        $isSuccess = stripos($line, '"Queued"') !== false || stripos($line, '"Success"') !== false;
                        $rowClass = $isSuccess ? 'success' : 'failed';
                        ?>
                        <tr class="log-entry <?= $rowClass ?>">
                            <?php
                            if (preg_match('/\[(.*?)\]/', $line, $matches)) {
                                $timestamp = $matches[1];
                            } else {
                                $timestamp = 'Unknown';
                            }
                            ?>
                            <td><?= htmlspecialchars($timestamp) ?></td>
                            <td><?= htmlspecialchars($line) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</body>
</html>
