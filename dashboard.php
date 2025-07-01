<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$resolved_count = $conn->query("SELECT COUNT(*) AS count FROM tickets WHERE status = 'Resolved'")->fetch_assoc()['count'];
$pending_count = $conn->query("SELECT COUNT(*) AS count FROM tickets WHERE status = 'Pending'")->fetch_assoc()['count'];

$most_common_issue = $conn->query("SELECT issue_type, COUNT(*) AS count 
                                   FROM tickets 
                                   GROUP BY issue_type 
                                   ORDER BY count DESC 
                                   LIMIT 1")->fetch_assoc();
$most_common_issue_name = $most_common_issue['issue_type'] ?? 'N/A';
$most_common_issue_count = $most_common_issue['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DENR Admin Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="dashboard.css" rel="stylesheet">
</head>
<body>

<!-- Header with Logo, Title and Navigation Buttons -->
<div class="header d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-3">
        <img src="logodashboard.png" alt="Logo" style="height: 40px;">
        <h1 class="mb-0">DENR Admin Dashboard</h1>
    </div>

    <div class="nav-buttons">
        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="manage_employees.php"><i class="fas fa-users"></i> Employees</a>
        <a href="edit_ticket.php"><i class="fas fa-tools"></i> Tickets</a>
       <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>

    </div>
</div>


<!-- Main Dashboard Content -->
    <div class="container">
        <div class="dashboard-section">
            <h2 class="mb-2"><span class="typewriter-text">👋 Welcome, Admin!</span></h2>
            <p>Monitor support tickets and manage employee concerns across all departments.</p>

        <div class="row g-4 mt-4">
           <!-- Resolved Tickets -->
<div class="col-md-4">
    <a href="resolved_tickets.php" style="text-decoration:none;">
    <div class="card resolved-tickets-card dashboard-card" style="cursor:pointer;">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="text-muted mb-1">Resolved Tickets</h6>
                <h5 class="card-title"><?= $resolved_count ?></h5>
            </div>
            <i class="fas fa-check-circle card-icon text-success"></i>
        </div>
    </div>
    </a>
</div>

<!-- Pending Tickets -->
<div class="col-md-4">
    <a href="pending_tickets.php" style="text-decoration:none;">
    <div class="card pending-tickets-card dashboard-card" style="cursor:pointer;">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="text-muted mb-1">Pending Tickets</h6>
                <h5 class="card-title"><?= $pending_count ?></h5>
            </div>
            <i class="fas fa-bell card-icon text-danger"></i>
        </div>
    </div>
    </a>
</div>

<!-- Most Common Issue -->
<div class="col-md-4">
    <a href="common_issues.php" style="text-decoration:none;">
    <div class="card common-issue-card dashboard-card" style="cursor:pointer;">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="text-muted mb-1">Most Common Issue</h6>
                <h5 class="card-title mb-1"><?= $most_common_issue_name ?></h5>
            </div>
            <i class="fas fa-bug card-icon text-primary"></i>
        </div>
    </div>
    </a>
</div>

<!-- Modals -->
<!-- Resolved Tickets Modal -->
<div class="modal fade" id="resolvedModal" tabindex="-1" aria-labelledby="resolvedModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="resolvedModalLabel">Resolved Tickets</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="resolvedTableContainer">Loading...</div>
      </div>
    </div>
  </div>
</div>
<!-- Pending Tickets Modal -->
<div class="modal fade" id="pendingModal" tabindex="-1" aria-labelledby="pendingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pendingModalLabel">Pending Tickets</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="pendingTableContainer">Loading...</div>
      </div>
    </div>
  </div>
</div>
<!-- Most Common Issues Modal -->
<div class="modal fade" id="commonIssueModal" tabindex="-1" aria-labelledby="commonIssueModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="commonIssueModalLabel">Most Common Issues</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="commonIssueTableContainer">Loading...</div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS and AJAX logic -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('resolvedCard').onclick = function() {
    var modal = new bootstrap.Modal(document.getElementById('resolvedModal'));
    document.getElementById('resolvedTableContainer').innerHTML = 'Loading...';
    fetch('dashboard_data.php?action=resolved')
      .then(res => res.text())
      .then(html => { document.getElementById('resolvedTableContainer').innerHTML = html; });
    modal.show();
};
document.getElementById('pendingCard').onclick = function() {
    var modal = new bootstrap.Modal(document.getElementById('pendingModal'));
    document.getElementById('pendingTableContainer').innerHTML = 'Loading...';
    fetch('dashboard_data.php?action=pending')
      .then(res => res.text())
      .then(html => { document.getElementById('pendingTableContainer').innerHTML = html; });
    modal.show();
};
document.getElementById('commonIssueCard').onclick = function() {
    var modal = new bootstrap.Modal(document.getElementById('commonIssueModal'));
    document.getElementById('commonIssueTableContainer').innerHTML = 'Loading...';
    fetch('dashboard_data.php?action=common_issues')
      .then(res => res.text())
      .then(html => { document.getElementById('commonIssueTableContainer').innerHTML = html; });
    modal.show();
};
</script>

</body>
</html>
