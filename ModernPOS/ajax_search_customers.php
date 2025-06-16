<?php
// ajax_search_customers.php
require __DIR__ . '/includes/db_connect.php';
header('Content-Type: application/json');

$term = $_GET['term'] ?? '';

// If the search term is empty, get all customers (excluding the default one).
// Otherwise, search by name or phone number.
if (empty(trim($term))) {
    $stmt = $pdo->query("SELECT id, name, phone_number FROM customers WHERE id != 1 ORDER BY name ASC LIMIT 15");
} else {
    $stmt = $pdo->prepare("
        SELECT id, name, phone_number 
        FROM customers 
        WHERE (phone_number LIKE ? OR name LIKE ?) AND id != 1 
        LIMIT 15
    ");
    $stmt->execute(['%' . $term . '%', '%' . $term . '%']);
}

$customers = $stmt->fetchAll();
echo json_encode($customers);