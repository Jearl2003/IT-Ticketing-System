<?php
session_start();
include 'db.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
// Pagination setup
$tickets_per_page = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $tickets_per_page;
// Count total resolved tickets
$count_query = "SELECT COUNT(*) as total FROM tickets WHERE status = 'Resolved'";
$count_result = $conn->query($count_query);
$total_tickets = $count_result->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_tickets / $tickets_per_page));
// Fetch resolved tickets
$query = "SELECT t.id, e.name, t.issue_description, t.issue_type, t.status, t.solution, t.created_at FROM tickets t LEFT JOIN employees e ON t.employee_id = e.id WHERE t.status = 'Resolved' ORDER BY t.created_at DESC LIMIT $tickets_per_page OFFSET $offset";
$tickets = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resolved Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="resolved_tickets.css" rel="stylesheet">
</head>
<body class="resolved-bg">
<div class="header d-flex align-items-center" style="border-bottom:1px solid #eee;">
    <img src="logodashboard.png" alt="Logo" style="height: 40px; margin-right: 16px;" id="dashboardLogo">
    <h1 class="mb-0">Resolved Tickets</h1>
</div>
<div class="container mt-4">
    <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Back to Dashboard</a>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee Name</th>
                    <th>Description</th>
                    <th>Issue Type</th>
                    <th>Status</th>
                    <th>Solution</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tickets->num_rows > 0): while ($row = $tickets->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name'] ?? 'N/A') ?></td>
                    <td><?= nl2br(htmlspecialchars($row['issue_description'])) ?></td>
                    <td><?= htmlspecialchars($row['issue_type'] ?? 'N/A') ?></td>
                    <td><span class="badge bg-success">Resolved</span></td>
                    <td><?= $row['solution'] ? nl2br(htmlspecialchars($row['solution'])) : '<em>No solution yet</em>' ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" class="text-center text-muted">No resolved tickets found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <!-- Pagination Controls -->
        <nav aria-label="Resolved ticket pagination">
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
document.addEventListener('DOMContentLoaded', function() {
  var logo = document.getElementById('dashboardLogo');
  if (logo) {
    logo.onclick = function() {
      window.location.href = 'dashboard.php';
    };
  }
});
</script>
</body>
</html> 