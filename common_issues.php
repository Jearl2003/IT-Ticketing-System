<?php
session_start();
include 'db.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
// Pagination setup
$issues_per_page = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $issues_per_page;
// Count total unique issue types
$count_query = "SELECT COUNT(DISTINCT issue_type) as total FROM tickets";
$count_result = $conn->query($count_query);
$total_issues = $count_result->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_issues / $issues_per_page));
// Fetch most common issues
$query = "SELECT issue_type, COUNT(*) as count FROM tickets GROUP BY issue_type ORDER BY count DESC LIMIT $issues_per_page OFFSET $offset";
$issues = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Most Common Issues</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="common_issues.css" rel="stylesheet">
</head>
<body class="common-bg">
<div class="header d-flex align-items-center" style="border-bottom:1px solid #eee;">
    <img src="logodashboard.png" alt="Logo" style="height: 40px; margin-right: 16px;" id="dashboardLogo">
    <h1 class="mb-0">Most Common Issues</h1>
</div>
<div class="container mt-4">
    <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Back to Dashboard</a>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th>Issue Type</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($issues->num_rows > 0): while ($row = $issues->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['issue_type'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['count']) ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="2" class="text-center text-muted">No issues found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <!-- Pagination Controls -->
        <nav aria-label="Common issues pagination">
            <ul class="pagination justify-content-center mt-3">
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $start_page + 4);
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1">&laquo; First</a></li>';
                }
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                }
                if ($end_page < $total_pages) {
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">Last &raquo;</a></li>';
                }
                ?>
            </ul>
        </nav>
    </div>
</div>
<script>
document.getElementById('dashboardLogo').onclick = function() {
    window.location.href = 'dashboard.php';
};
</script>
</body>
</html> 