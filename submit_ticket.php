<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['employee_id'], $_SESSION['employee_department'])) {
    header("Location: employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$department_id = $_SESSION['employee_department'];

// Fetch employees in the same department
$employees_in_dept = $conn->query("SELECT id, first_name, middle_name, last_name, age, birthdate, address, gender, contact_number, email, position FROM employees WHERE department_id = $department_id ORDER BY first_name");

$employees_data = [];
while ($row = $employees_in_dept->fetch_assoc()) {
    $full_name = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $employees_data[$row['id']] = [
        'full_name' => $full_name,
        'age' => $row['age'],
        'birthdate' => $row['birthdate'],
        'address' => $row['address'],
        'gender' => $row['gender'],
        'contact_number' => $row['contact_number'],
        'email' => $row['email'],
        'position' => $row['position'],
    ];
}

if (isset($_POST['submit_ticket'])) {
    $selected_employee_id = intval($_POST['name']);
    $age = intval($_POST['age']);
    $birthdate = $_POST['birthdate'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $issue_description = trim($_POST['issue_description']);

    if (empty($selected_employee_id) || empty($issue_description)) {
        $error = "Please select your name and provide a description of the request.";
    } else {
        $stmt = $conn->prepare("INSERT INTO tickets 
            (employee_id, age, birthdate, address, gender, contact_number, email, position, issue_description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("iisssssss", $selected_employee_id, $age, $birthdate, $address, $gender, $contact_number, $email, $position, $issue_description);

        if (!$stmt->execute()) {
            die("Database error: " . $stmt->error);
        }

        $stmt->close();

        $success = "Ticket submitted successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Submit Ticket</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="submit_ticket.css">
</head>

<body class="p-4">
<div class="container">
    <h3>Submit a Ticket</h3>
    <a href="employee_dashboard.php" class="btn btn-primary mb-3">&larr; Back </a>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="ticketForm">
        <div class="mb-3">
            <label for="name">Name</label>
            <select name="name" id="nameSelect" class="form-select" required>
                <option value="" disabled selected>Select your name</option>
                <?php foreach ($employees_data as $id => $info): ?>
                    <option value="<?= $id ?>"><?= htmlspecialchars($info['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Age</label>
            <input type="text" name="age" id="age" class="form-control" readonly required>
        </div>

        <div class="mb-3">
            <label>Birthdate</label>
            <input type="date" name="birthdate" id="birthdate" class="form-control" readonly required>
        </div>

        <div class="mb-3">
            <label>Address</label>
            <input type="text" name="address" id="address" class="form-control" readonly required>
        </div>

        <div class="mb-3">
            <label>Gender</label>
            <input type="text" name="gender" id="gender" class="form-control" readonly required>
        </div>

        <div class="mb-3">
            <label>Contact Number</label>
            <input type="text" name="contact_number" id="contact_number" class="form-control" readonly required>
        </div>

        <div class="mb-3">
            <label>Email Address</label>
            <input type="email" name="email" id="email" class="form-control" readonly required>
        </div>

        <div class="mb-3">
            <label>Position/Designation</label>
            <input type="text" name="position" id="position" class="form-control" readonly required>
        </div>

        <div class="mb-3">
            <label>Description of Request</label>
            <textarea name="issue_description" class="form-control" rows="4" required></textarea>
        </div>

        <button type="submit" name="submit_ticket" class="btn btn-success">Submit</button>
        <a href="employee_login.php" class="btn btn-danger float-end">Logout</a>
    </form>
</div>

<script>
const employees = <?= json_encode($employees_data); ?>;

document.getElementById('nameSelect').addEventListener('change', function () {
    const selectedId = this.value;
    if (selectedId && employees[selectedId]) {
        document.getElementById('age').value = employees[selectedId].age;
        document.getElementById('birthdate').value = employees[selectedId].birthdate;
        document.getElementById('address').value = employees[selectedId].address;
        document.getElementById('gender').value = employees[selectedId].gender;
        document.getElementById('contact_number').value = employees[selectedId].contact_number;
        document.getElementById('email').value = employees[selectedId].email;
        document.getElementById('position').value = employees[selectedId].position;
    } else {
        ['age', 'birthdate', 'address', 'gender', 'contact_number', 'email', 'position'].forEach(id => {
            document.getElementById(id).value = '';
        });
    }
});
</script>
</body>
</html>
