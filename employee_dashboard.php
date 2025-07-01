<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['employee_department'])) {
    header("Location: employee_login.php");
    exit();
}

$department_id = $_SESSION['employee_department'];

// Get department name
$dept_query = $conn->query("SELECT name FROM departments WHERE id = $department_id");
$dept = $dept_query->fetch_assoc();
$department_name = $dept['name'] ?? 'Unknown';

// Pagination setup
$tickets_per_page = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $tickets_per_page;

// Count total tickets for pagination
$count_query = "SELECT COUNT(*) as total FROM tickets t JOIN employees e ON t.employee_id = e.id WHERE e.department_id = $department_id";
$count_result = $conn->query($count_query);
$total_tickets = $count_result->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_tickets / $tickets_per_page));

// Fetch tickets with employee name (paginated)
$tickets = $conn->query("
    SELECT t.*, e.name AS employee_name
    FROM tickets t
    JOIN employees e ON t.employee_id = e.id
    WHERE e.department_id = $department_id
    ORDER BY t.created_at DESC
    LIMIT $tickets_per_page OFFSET $offset
");
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($department_name); ?> Department Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="employee_dashboard.css" rel="stylesheet">
</head>

<body class="bg">
<div class="container mt-5">
    <div class="card p-4 shadow-sm">
        <h3 class="typewriter"><span>🎫 Ticket Dashboard</span></h3>
        <h4 class="text-muted mt-2"><?php echo htmlspecialchars($department_name); ?> Department</h4>


        <div class="mb-4">
            <a href="submit_ticket.php" class="btn btn-success">🛠️ Submit New Ticket</a>
            <a href="logout.php" class="btn btn-secondary float-end logout-btn">Logout</a>

        </div>

        <!-- 🔍 Search bar -->
        <div class="mb-3">
            <input type="text" id="employeeSearch" class="form-control" placeholder="Search by Employee name">
        </div>

        <!-- 🧾 Ticket Table -->
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th>Ticket ID</th>
                    <th>Employee Name</th>
                    <th>Request Description</th>
                    <th>Issue Type</th>
                    <th>Status</th>
                    <th>Submitted On</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tickets->num_rows > 0): ?>
                    <?php while ($row = $tickets->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['employee_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($row['issue_description'] ?? '(No Description)'); ?></td>
                            <td><?php echo htmlspecialchars($row['issue_type'] ?? '(No Type)'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $row['status'] == 'Resolved' ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?>
                                </span>
                            </td>
                            <td><?php echo $row['created_at'] ?? 'Unknown Date'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No tickets found for your department.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <!-- Pagination Controls -->
        <nav aria-label="Ticket pagination">
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

<!-- 🔎 Search Script -->
<script>
    document.getElementById('employeeSearch').addEventListener('keyup', function () {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        let count = 0;

        rows.forEach(row => {
            const nameCell = row.children[1]; // Employee Name column
            const name = nameCell ? nameCell.textContent.toLowerCase() : '';
            const match = name.includes(query);

            row.style.display = match ? '' : 'none';
            if (match) count++;
        });

        console.log(`Tickets found: ${count}`);
    });
    
</script>

</body>
</html>
