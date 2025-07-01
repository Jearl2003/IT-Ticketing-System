<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Check if employee added successfully
$showSuccess = false;
if (isset($_SESSION['added_employee']) && $_SESSION['added_employee'] === true) {
    $showSuccess = true;
    unset($_SESSION['added_employee']);
}

if (isset($_POST['add'])) {
    $first = trim($_POST['first_name']);
    $middle = trim($_POST['middle_name']);
    $last = trim($_POST['last_name']);
    $age = intval($_POST['age']);
    $birthdate = $_POST['birthdate'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $contact = $_POST['contact_number'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $dept = intval($_POST['department']);

    // Construct full name
    $full_name = $first;
    if (!empty($middle)) {
        $full_name .= ' ' . $middle;
    }
    $full_name .= ' ' . $last;

    if ($dept > 0) {
        $stmt = $conn->prepare("INSERT INTO employees (name, first_name, middle_name, last_name, age, birthdate, address, gender, contact_number, email, position, department_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssissssssi', 
            $full_name,
            $first,
            $middle,
            $last,
            $age,
            $birthdate,
            $address,
            $gender,
            $contact,
            $email,
            $position,
            $dept
        );
        $stmt->execute();
        $_SESSION['added_employee'] = true;  // Flag to show success alert
        header("Location: manage_employees.php");
        exit();
    } else {
        echo "<script>alert('Please select a valid Department!'); window.history.back();</script>";
    }
}

// Delete employee
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM employees WHERE id=$id");
    header("Location: manage_employees.php");
    exit();
}

// Filter
$filter_department = isset($_GET['filter_department']) ? intval($_GET['filter_department']) : 0;

// Pagination setup
$employees_per_page = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $employees_per_page;

// Count total employees for pagination
$count_query = "SELECT COUNT(*) as total FROM employees";
if ($filter_department > 0) {
    $count_query .= " WHERE department_id = $filter_department";
}
$count_result = $conn->query($count_query);
$total_employees = $count_result->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_employees / $employees_per_page));

// Employee list query with LIMIT
$query = "SELECT employees.*, departments.name AS dept 
          FROM employees 
          LEFT JOIN departments ON employees.department_id = departments.id";
if ($filter_department > 0) {
    $query .= " WHERE employees.department_id = $filter_department";
}
$query .= " ORDER BY employees.last_name ASC LIMIT $employees_per_page OFFSET $offset";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Employees</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="manage_employees.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php if ($showSuccess): ?>
    <div id="success-popup" role="alert" aria-live="assertive" aria-atomic="true">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        Added Successfully
    </div>
    <script>
        setTimeout(() => {
            const popup = document.getElementById('success-popup');
            popup.classList.add('hide');
        }, 100); // Hide after 100 second
    </script>
<?php endif; ?>

<!-- Header -->
<div class="header">
    <h2 class="mb-0">Employee</h2>
    <div class="nav-buttons">
        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="manage_employees.php"><i class="fas fa-users"></i> Employees</a>
        <a href="edit_ticket.php"><i class="fas fa-tools"></i> Tickets</a>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>

    </div>
</div>

<div class="container mt-4">
    <!-- Add Employee Form -->
    <div class="card p-4 mb-4 add-employee-card">
        <h5 class="mb-3">➕ Add New Employee</h5>
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>

                <div class="col-md-2">
                    <label>Age</label>
                    <input type="number" name="age" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>Birthdate</label>
                    <input type="date" name="birthdate" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="" disabled selected>Select</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Position</label>
                    <input type="text" name="position" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label>Department</label>
                    <div class="input-group">
                        <select name="department" id="departmentSelect" class="form-select" required>
                            <option value="" disabled selected>Select Department</option>
                            <?php
                            $departments = $conn->query("SELECT * FROM departments");
                            while ($row = $departments->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>" . htmlspecialchars($row['name']) . "</option>";
                            }
                            ?>
                        </select>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addDeptModal">Add</button>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button name="add" class="btn btn-success w-100">Add</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Employee List -->
    <div class="card p-4 employee-list-card">
        <h5 class="mb-3 d-flex justify-content-between align-items-center">
            📋 Employee List
            <form method="GET" class="d-inline-block">
                <select name="filter_department" class="form-select d-inline-block" onchange="this.form.submit()">
                    <option value="0">All Departments</option>
                    <?php
                    $dept_options = $conn->query("SELECT * FROM departments");
                    while ($d = $dept_options->fetch_assoc()) {
                        $selected = ($filter_department == $d['id']) ? 'selected' : '';
                        echo "<option value='{$d['id']}' $selected>" . htmlspecialchars($d['name']) . "</option>";
                    }
                    ?>
                </select>
            </form>
        </h5>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th style="width: 150px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $full_name = htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name']);
                        echo "<tr>
                            <td>$full_name</td>
                            <td>" . htmlspecialchars($row['email']) . "</td>
                            <td>" . htmlspecialchars($row['dept']) . "</td>
                            <td>
                                <a href='edit_employee.php?id={$row['id']}' class='btn btn-warning btn-sm'>Edit</a>
                                <a href='?delete={$row['id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Delete this employee?')\">Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center text-muted'>No employees found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <!-- Pagination Controls -->
        <nav aria-label="Employee pagination">
            <ul class="pagination justify-content-center mt-3">
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $start_page + 6);
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1&filter_department=' . $filter_department . '">&laquo; First</a></li>';
                }
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . '&filter_department=' . $filter_department . '">' . $i . '</a></li>';
                }
                if ($end_page < $total_pages) {
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&filter_department=' . $filter_department . '">Last &raquo;</a></li>';
                }
                ?>
            </ul>
        </nav>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-labelledby="addDeptModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addDeptModalLabel">Add New Department</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="newDeptName" class="form-control" placeholder="Department Name">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveDeptBtn">Add Department</button>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('saveDeptBtn').onclick = function() {
    var deptName = document.getElementById('newDeptName').value.trim();
    if (!deptName) {
        alert('Please enter a department name.');
        return;
    }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'add_department.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            var resp = JSON.parse(xhr.responseText);
            if (resp.success) {
                var select = document.getElementById('departmentSelect');
                var option = document.createElement('option');
                option.value = resp.id;
                option.text = deptName;
                option.selected = true;
                select.appendChild(option);
                var modal = bootstrap.Modal.getInstance(document.getElementById('addDeptModal'));
                modal.hide();
                document.getElementById('newDeptName').value = '';
            } else {
                alert('Error: ' + resp.message);
            }
        }
    };
    xhr.send('name=' + encodeURIComponent(deptName));
};
</script>
</body>
</html>
