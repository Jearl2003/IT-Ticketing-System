<?php
include 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch employee details if id is passed
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM employees WHERE id = $id");
    $employee = $result->fetch_assoc();

    // Fetch departments
    $departments = $conn->query("SELECT * FROM departments");
}

// Flag to show success alert
$showSuccess = false;

// Update employee
if (isset($_POST['update'])) {
    $first = $_POST['first_name'];
    $middle = $_POST['middle_name'];
    $last = $_POST['last_name'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $contact = $_POST['contact_number'];
    $dept = intval($_POST['department']);

    $stmt = $conn->prepare("UPDATE employees SET first_name = ?, middle_name = ?, last_name = ?, email = ?, position = ?, contact_number = ?, department_id = ? WHERE id = ?");
    $stmt->bind_param("ssssssii", $first, $middle, $last, $email, $position, $contact, $dept, $id);
    $stmt->execute();

    $showSuccess = true; // trigger success message and delay redirect
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Employee</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<?php if ($showSuccess): ?>
    <div class="alert alert-success text-center" id="successAlert">
        ✅ Updated Successfully!
    </div>
    <script>
        setTimeout(() => {
            window.location.href = "manage_employees.php";
        }, 1000); // 1 second delay
    </script>
<?php endif; ?>

<h3>Edit Employee</h3>
<form method="POST">
    <div class="row g-2">
        <div class="col-md-4">
            <label>First Name</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($employee['first_name']) ?>" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label>Middle Name</label>
            <input type="text" name="middle_name" value="<?= htmlspecialchars($employee['middle_name']) ?>" class="form-control">
        </div>
        <div class="col-md-4">
            <label>Last Name</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($employee['last_name']) ?>" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label>Position</label>
            <input type="text" name="position" value="<?= htmlspecialchars($employee['position']) ?>" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label>Contact Number</label>
            <input type="text" name="contact_number" value="<?= htmlspecialchars($employee['contact_number']) ?>" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label>Department</label>
            <select name="department" class="form-select" required>
                <option disabled>Select Department</option>
                <?php
                while ($row = $departments->fetch_assoc()) {
                    $selected = ($row['id'] == $employee['department_id']) ? 'selected' : '';
                    echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button name="update" class="btn btn-primary w-100">Update</button>
        </div>
    </div>
</form>
<a href="manage_employees.php" class="btn btn-secondary mt-3">Back to Manage Employees</a>

</body>
</html>
