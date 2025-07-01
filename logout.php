<?php
session_start();
include 'db.php';

// Check if an admin or an employee is logged in
if (isset($_SESSION['admin'])) {
    session_unset();
    session_destroy();
    header("Location: login.php"); // Redirect admin to admin login
    exit();
} elseif (isset($_SESSION['employee_email'])) {
    session_unset();
    session_destroy();
    header("Location: employee_login.php"); // Redirect employee to employee login
    exit();
} else {
    // No one logged in - fallback
    session_unset();
    session_destroy();
    header("Location: employee_login.php");
    exit();
}
?>
