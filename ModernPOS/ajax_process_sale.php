<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

require __DIR__ . '/includes/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$cart_items = $data['cart'] ?? [];
$customer_id = $data['customerId'] ?? 1;
$payment_method = $data['paymentMethod'] ?? 'cash';
$payment_provider = $data['paymentProvider'] ?? null;

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
    exit();
}

$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO sales (user_id, customer_id, total_amount, payment_method, payment_provider) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $customer_id, $total_amount, $payment_method, $payment_provider]);
    $sale_id = $pdo->lastInsertId();

    $stmt_item = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price_per_unit) VALUES (?, ?, ?, ?)");
    $stmt_update = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");

    foreach ($cart_items as $item) {
        $stmt_item->execute([$sale_id, $item['id'], $item['quantity'], $item['price']]);
        $stmt_update->execute([$item['quantity'], $item['id']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'sale_id' => $sale_id]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Failed to process sale: ' . $e->getMessage()]);
}
?>