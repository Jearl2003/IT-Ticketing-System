<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Handle update
if (isset($_POST['update'])) {
    $ticket_id = $_POST['ticket_id'];
    $solution = $_POST['solution'];
    $issue_type = $_POST['issue_type'];  // Added issue_type

    $stmt = $conn->prepare("UPDATE tickets SET status = 'Resolved', solution = ?, issue_type = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $solution, $issue_type, $ticket_id);
    $stmt->execute();

    $success = "Ticket #$ticket_id marked as Resolved.";
}

// Handle filter
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

// Pagination setup
$tickets_per_page = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $tickets_per_page;

// Count total tickets for pagination
$count_query = "SELECT COUNT(*) as total FROM tickets";
if ($filter_status) {
    $count_query .= " WHERE status = '" . $conn->real_escape_string($filter_status) . "'";
}
$count_result = $conn->query($count_query);
$total_tickets = $count_result->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_tickets / $tickets_per_page));

// Ticket list query with LIMIT
$filter_query = "
    SELECT t.*, e.name, e.contact_number, e.position, e.email, e.age, e.birthdate, e.address, e.gender,
           d.name AS department_name
    FROM tickets t
    LEFT JOIN employees e ON t.employee_id = e.id
    LEFT JOIN departments d ON e.department_id = d.id
";
if ($filter_status) {
    $filter_query .= " WHERE t.status = '" . $conn->real_escape_string($filter_status) . "'";
}
$filter_query .= " ORDER BY t.created_at DESC LIMIT $tickets_per_page OFFSET $offset";
$tickets = $conn->query($filter_query);
$admin_name = $_SESSION['admin'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
     <link rel="stylesheet" href="edit_ticket.css">
    <script>
        function printTicket(id) {
            const printContentElem = document.getElementById('print-' + id);
            if (!printContentElem) {
                alert('Print section not found for ticket: ' + id);
                return;
            }
            const printContent = printContentElem.innerHTML;
            const originalContent = document.body.innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            // Optionally, reload scripts or re-attach event listeners if needed
        }
         // 🔍 Filter by employee name
        function filterByName() {
            let input = document.getElementById('employeeSearch');
            let filter = input.value.toLowerCase();
            let rows = document.querySelectorAll('tbody tr:not(.print-section)');

            rows.forEach(row => {
                let nameCell = row.querySelector('td:nth-child(2)');
                if (nameCell) {
                    let nameText = nameCell.textContent.toLowerCase();
                    row.style.display = nameText.includes(filter) ? '' : 'none';
                }
            });
        }
    </script>

</head>
<body>

<div class="header">
    <h2 class="mb-0">Tickets</h2>
    <div class="nav-buttons">
        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="manage_employees.php"><i class="fas fa-users"></i> Employees</a>
        <a href="edit_ticket.php"><i class="fas fa-tools"></i> Tickets</a>
       <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>

    </div>
</div>

<div class="container">
    <?php if (isset($success)): ?>
        <div class="alert alert-success" id="successAlert"><?= $success ?></div>
    <?php endif; ?>

    <!-- Filter and Search -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <form method="GET" class="d-flex align-items-center">
            <label for="filter_status" class="me-2 fw-bold">Filter By Status:</label>
            <select name="filter_status" id="filter_status" class="form-select" onchange="this.form.submit()">
                <option value="">All Tickets</option>
                <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Resolved" <?= $filter_status == 'Resolved' ? 'selected' : '' ?>>Resolved</option>
            </select>
        </form>

        <!-- 🔍 Search Bar -->
        <div>
            <input type="text" id="employeeSearch" class="form-control" placeholder="Search" onkeyup="filterByName()">
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee Info</th>
                    <th>Request Description</th>
                    <th>Issue Type</th>
                    <th>Status</th>
                    <th>Solution</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $tickets->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                            <strong>Name:</strong> <?= htmlspecialchars($row['name']) ?><br>
                            <strong>Department:</strong> <?= htmlspecialchars($row['department_name'] ?? 'N/A') ?><br>
                            <strong>Position:</strong> <?= htmlspecialchars($row['position']) ?><br>
                            <strong>Contact:</strong> <?= htmlspecialchars($row['contact_number']) ?><br>
                            <strong>Email:</strong> <?= htmlspecialchars($row['email']) ?>
                        </td>
                        <td><?= nl2br(htmlspecialchars($row['issue_description'])) ?></td>
                        <td><?= htmlspecialchars($row['issue_type'] ?? 'N/A') ?></td>
                        <td>
                            <?= $row['status'] === 'Resolved'
                                ? '<span class="badge bg-success">Resolved</span>'
                                : '<span class="badge bg-warning text-dark">Pending</span>' ?>
                        </td>
                        <td>
                            <?= $row['solution'] ? nl2br(htmlspecialchars($row['solution'])) : '<em>No solution yet</em>' ?>
                        </td>
                        <td class="no-print">
                            <?php if ($row['status'] !== 'Resolved'): ?>
                                <form method="POST" class="d-flex flex-column gap-2">
                                    <input type="hidden" name="ticket_id" value="<?= $row['id'] ?>">
                                    <textarea name="solution" class="form-control" rows="3" required placeholder="Enter solution here..."></textarea>
                                    <select name="issue_type" class="form-select mt-2" required>
                                        <option value="">Select Issue Type</option>
                                        <option value="Software">Software</option>
                                        <option value="Hardware">Hardware</option>
                                    </select>
                                    <button type="submit" name="update" class="btn btn-success btn-sm mt-2">Mark as Resolved</button>
                                </form>
                            <?php else: ?>
                                <em>No action needed</em>
                            <?php endif; ?>
                            <button type="button" onclick="printTicket(<?= $row['id'] ?>)" class="btn btn-primary btn-sm mt-2">🖨️ Print</button>
                        </td>
                    </tr>

                    <!-- Hidden Print Section -->
                    <tr class="print-section">
                        <td colspan="6">
                            <div id="print-<?= $row['id'] ?>" style="font-family: Arial, sans-serif; padding: 30px; border: 1px solid #000; max-width: 950px; margin: auto; background: #fff;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td rowspan="3" style="width: 90px; text-align: center; border: 1px solid #000;">
                                            <img src="logodenr.png" alt="DENR Logo" style="height: 60px;">
                                        </td>
                                        <td colspan="2" style="font-size: 16px; font-weight: bold; text-align: center; border: 1px solid #000;">PLANNING SECTION</td>
                                        <td style="font-size: 12px; border: 1px solid #000; padding: 2px;" rowspan="3">
                                            Document ID #: R13-BISLIG.ROAAP.008<br>
                                            Revision No.: 1<br>
                                            Effectivity<br>
                                            January 24, 2023
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="font-size: 14px; text-align: center; border: 1px solid #000;">CENRO Information and Communication Technology Unit</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="font-size: 18px; font-weight: bold; text-align: center; border: 1px solid #000;">SERVICE REQUEST FORM (SRF)</td>
                                    </tr>
                                </table>
                                <div style="font-size: 12px; margin: 8px 0 4px 0;">
                                    <b>Reminder:</b> Please complete this form and submit it at the ICT Unit located at the Espiritu Street, Bislig City, Surigao del Sur or email a scanned copy to <u>cenrobislig@denr.gov.ph</u>. Once processed, a Technical Support Representative will contact you to schedule a service.
                                </div>
                                <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 0;">
                                    <tr>
                                        <td style="border: 1px solid #000;"><b>Ticket No.:</b> <?= $row['id'] ?></td>
                                        <td style="border: 1px solid #000;"><b>Date (mm/dd/yyyy):</b> <?= date('m/d/Y', strtotime($row['created_at'])) ?></td>
                                        <td style="border: 1px solid #000;"><b>Time (hh:mm):</b> <?= date('H:i', strtotime($row['created_at'])) ?></td>
                                    </tr>
                                    <tr><td colspan="3" style="border: 1px solid #000; font-weight: bold; background: #eaeaea;">Requester's Information</td></tr>
                                    <tr>
                                        <td style="border: 1px solid #000;">Name: <?= htmlspecialchars($row['name']) ?></td>
                                        <td style="border: 1px solid #000;">Position/Designation: <?= htmlspecialchars($row['position']) ?></td>
                                        <td style="border: 1px solid #000;">Division: <?= htmlspecialchars($row['department_name'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000;">Section/Unit: ___________________________</td>
                                        <td style="border: 1px solid #000;">Contact Number: <?= htmlspecialchars($row['contact_number']) ?></td>
                                        <td style="border: 1px solid #000;">Email Address: <?= htmlspecialchars($row['email']) ?></td>
                                    </tr>
                                    <tr><td colspan="3" style="border: 1px solid #000; font-weight: bold; background: #eaeaea;">Request Information</td></tr>
                                    <tr>
                                        <td colspan="3" style="border: 1px solid #000;">
                                            Type of request:
                                            <input type="checkbox" <?= ($row['issue_type'] == 'Technical Assistance') ? 'checked' : '' ?>>Technical Assistance
                                            <input type="checkbox" <?= ($row['issue_type'] == 'Asset/Borrow') ? 'checked' : '' ?>>Asset/Borrow
                                            <input type="checkbox" <?= ($row['issue_type'] == 'E-mail') ? 'checked' : '' ?>>E-mail
                                            <input type="checkbox" <?= (!in_array($row['issue_type'], ['Technical Assistance','Asset/Borrow','E-mail'])) ? 'checked' : '' ?>>Others (specify): <?= htmlspecialchars($row['issue_type']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="border: 1px solid #000;">
                                            <b>DESCRIPTION OF REQUEST</b> (Please clearly write down the details of the request.)<br>
                                            <div style="min-height: 60px; border: 1px solid #ccc; padding: 5px; margin-top: 3px; white-space: pre-wrap;">
                                                <?= nl2br(htmlspecialchars($row['issue_description'])) ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr><td colspan="3" style="border: 1px solid #000; font-weight: bold; background: #eaeaea;">Authorization</td></tr>
                                    <tr>
                                        <td colspan="3" style="border: 1px solid #000; font-size: 12px;">
                                            All requests for service must be approved by the appropriate <b>manager/supervisor</b> (at least division chief, OIC, immediate supervisor or next in rank staff) of the requester. By signing below the manager/supervisor certifies that the service is required.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000;">Full Name: ___________________________</td>
                                        <td style="border: 1px solid #000;">Position/Title: ___________________________</td>
                                        <td style="border: 1px solid #000;"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="border: 1px solid #000; height: 40px; vertical-align: bottom;">
                                            <span style="display: inline-block; width: 250px; border-bottom: 1px solid #000;">&nbsp;</span><br>
                                            Signature (Manager/Supervisor)
                                        </td>
                                        <td style="border: 1px solid #000; vertical-align: bottom;">
                                            Date (mm - dd - yyyy)
                                        </td>
                                    </tr>
                                    <tr><td colspan="3" style="border: 1px solid #000; font-weight: bold; background: #eaeaea;">Infrastructure Service Authorization</td></tr>
                                    <tr>
                                        <td style="border: 1px solid #000;">Full Name: ROSEANNE M. REBUYON</td>
                                        <td style="border: 1px solid #000;">Title/Position: Forester I/Designated Planning Officer</td>
                                        <td style="border: 1px solid #000;"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="border: 1px solid #000; height: 40px; vertical-align: bottom;">
                                            <span style="display: inline-block; width: 250px; border-bottom: 1px solid #000;">&nbsp;</span><br>
                                            Signature
                                        </td>
                                        <td style="border: 1px solid #000; vertical-align: bottom;">
                                            Date (mm - dd - yyyy)
                                        </td>
                                    </tr>
                                    <tr><td colspan="3" style="border: 1px solid #000; font-weight: bold; background: #eaeaea;">For PICTU Staff Only</td></tr>
                                    <tr>
                                        <td colspan="3" style="padding: 0;">
                                            <table style="width: 100%; border-collapse: collapse; font-size: 13px; border: 1px solid #000; margin: 0;">
                                                <tr style="background: #eaeaea;">
                                                    <th style="border: 1px solid #000;">Date</th>
                                                    <th style="border: 1px solid #000;">Time</th>
                                                    <th style="border: 1px solid #000;">Action Taken</th>
                                                    <th style="border: 1px solid #000;">Action Staff</th>
                                                    <th style="border: 1px solid #000;">Signature</th>
                                                </tr>
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                <tr>
                                                    <td style="border: 1px solid #000; height: 25px;"></td>
                                                    <td style="border: 1px solid #000;"></td>
                                                    <td style="border: 1px solid #000;"></td>
                                                    <td style="border: 1px solid #000;"></td>
                                                    <td style="border: 1px solid #000;"></td>
                                                </tr>
                                                <?php endfor; ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="border: 1px solid #000;">
                                            <b>Feedback Rating:</b>
                                            <input type="checkbox"> Excellent
                                            <input type="checkbox"> Very Satisfactory
                                            <input type="checkbox"> Satisfactory
                                            <input type="checkbox"> Below Satisfactory
                                            <input type="checkbox"> Poor
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="border: 1px solid #000;">
                                            <input type="checkbox"> Completed<br>
                                            <span>Acknowledged by:</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="border: 1px solid #000;">
                                            <span style="display: inline-block; width: 250px; border-bottom: 1px solid #000; margin-top: 10px;"></span>
                                            <span style="margin-left: 40px;">Signature over printed name</span>
                                            <span style="margin-left: 40px;">Date/Time: ____________________</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="border: 1px solid #000; font-size: 11px; color: #888;">Ref: NIMD Service Request Form 22 March 2021</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <!-- Pagination Controls -->
        <nav aria-label="Ticket pagination">
            <ul class="pagination justify-content-center mt-3">
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $start_page + 6);
                $filter_status_url = $filter_status ? '&filter_status=' . urlencode($filter_status) : '';
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1' . $filter_status_url . '">&laquo; First</a></li>';
                }
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . $filter_status_url . '">' . $i . '</a></li>';
                }
                if ($end_page < $total_pages) {
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . $filter_status_url . '">Last &raquo;</a></li>';
                }
                ?>
            </ul>
        </nav>
    </div>
</div>

<script>
window.onload = function() {
    var alert = document.getElementById('successAlert');
    if (alert) {
        setTimeout(function() {
            alert.style.display = 'none';
        }, 2000); // 2 seconds
    }
};
</script>
</body>
</html>