<?php
// ajax_register_customer.php
require __DIR__ . '/includes/db_connect.php';
header('Content-Type: application/json');

$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';

if (empty($name) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Name and phone are required.']);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO customers (name, phone_number) VALUES (?, ?)");
    $stmt->execute([$name, $phone]);
    $new_customer_id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'id' => $new_customer_id, 'name' => $name, 'phone' => $phone]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'This phone number is already registered.']);
}