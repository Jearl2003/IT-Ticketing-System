<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $departmentLogin = strtolower(trim($_POST['department'])); // convert to lowercase

    // Find the department by name
    $deptResult = $conn->prepare("SELECT id FROM departments WHERE LOWER(name) = ?");
    $deptResult->bind_param("s", $departmentLogin);
    $deptResult->execute();
    $deptData = $deptResult->get_result();

    if ($deptData->num_rows == 1) {
        $dept = $deptData->fetch_assoc();
        $deptId = $dept['id'];

        // Fetch any employee from that department
        $empQuery = $conn->query("SELECT * FROM employees WHERE department_id = $deptId LIMIT 1");
        if ($empQuery->num_rows == 1) {
            $employee = $empQuery->fetch_assoc();
            $_SESSION['employee_id'] = $employee['id'];
            $_SESSION['employee_name'] = $employee['first_name'] . ' ' . $employee['last_name'];
            $_SESSION['employee_email'] = $employee['email'];
            $_SESSION['employee_department'] = $employee['department_id'];

            header("Location: employee_dashboard.php");
            exit();
        } else {
            $error = "No employee found for this department.";
        }
    } else {
        $error = "Invalid department name.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Login</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="employee_login.css"> <!-- Optional external CSS -->
</head>
<body>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 500px;">
        <!-- Login Section -->
        <div class="col-md-5 order-md-1 order-2 d-flex justify-content-center">
            <div class="login-card w-100">
                <h4 class="card-title mb-4 text-center">Employee Login</h4>
                <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label for="department" class="form-label">Department Name (e.g., cds, rps, ems)</label>
                        <input type="text" id="department" name="department" class="form-control" required />
                    </div>
                    <button type="submit" class="btn btn-success w-100">Login</button>
                </form>
            </div>
        </div>

        <!-- Logo Section -->
        <div class="col-md-5 order-md-2 order-1 d-flex justify-content-center align-items-center logo-section">
            <img src="logodenr.png" alt="DENR Logo" class="img-fluid" style="max-height: 600px; pointer-events: none;" />
        </div>
    </div>
</div>

</body>
</html>
