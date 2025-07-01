<?php
include 'db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    if ($name === '') {
        echo json_encode(['success' => false, 'message' => 'Department name required.']);
        exit;
    }
    // Check if department already exists
    $stmt = $conn->prepare('SELECT id FROM departments WHERE name = ?');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Department already exists.']);
        exit;
    }
    $stmt->close();
    // Insert new department
    $stmt = $conn->prepare('INSERT INTO departments (name) VALUES (?)');
    $stmt->bind_param('s', $name);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    $stmt->close();
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request.']); 