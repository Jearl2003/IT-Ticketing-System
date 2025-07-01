<?php
session_start();
include 'db.php';
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    exit('Not authorized');
}
header('Content-Type: text/html; charset=UTF-8');
$action = $_GET['action'] ?? '';

if ($action === 'resolved' || $action === 'pending') {
    $status = $action === 'resolved' ? 'Resolved' : 'Pending';
    $result = $conn->query("SELECT t.id, e.name, t.issue_description, t.issue_type, t.status, t.solution, t.created_at FROM tickets t LEFT JOIN employees e ON t.employee_id = e.id WHERE t.status = '" . $conn->real_escape_string($status) . "' ORDER BY t.created_at DESC LIMIT 100");
    echo '<div class="table-responsive"><table class="table table-bordered table-hover align-middle">';
    echo '<thead><tr><th>ID</th><th>Employee Name</th><th>Description</th><th>Issue Type</th><th>Status</th><th>Solution</th><th>Date</th></tr></thead><tbody>';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['name'] ?? 'N/A') . '</td>';
            echo '<td>' . nl2br(htmlspecialchars($row['issue_description'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['issue_type'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($row['status']) . '</td>';
            echo '<td>' . ($row['solution'] ? nl2br(htmlspecialchars($row['solution'])) : '<em>No solution yet</em>') . '</td>';
            echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7" class="text-center text-muted">No tickets found.</td></tr>';
    }
    echo '</tbody></table></div>';
    exit;
}

if ($action === 'common_issues') {
    $result = $conn->query("SELECT issue_type, COUNT(*) as count FROM tickets GROUP BY issue_type ORDER BY count DESC LIMIT 10");
    echo '<div class="table-responsive"><table class="table table-bordered table-hover align-middle">';
    echo '<thead><tr><th>Issue Type</th><th>Count</th></tr></thead><tbody>';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['issue_type'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($row['count']) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="2" class="text-center text-muted">No issues found.</td></tr>';
    }
    echo '</tbody></table></div>';
    exit;
}

echo 'Invalid request.'; 