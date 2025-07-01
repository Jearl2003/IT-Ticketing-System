<?php
session_start();
include 'db.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$tickets = $conn->query("SELECT * FROM tickets WHERE employee_id = $employee_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Tickets</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
<div class="container">
    <h3 class="mb-4">My Submitted Tickets</h3>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Description</th>
                <th>Status</th>
                <th>Solution</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $tickets->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['issue_description'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td><?= $row['solution'] ? $row['solution'] : '<span class="text-muted">Pending...</span>' ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="submit_ticket.php" class="btn btn-primary">Back</a>
</div>
</body>
</html>
