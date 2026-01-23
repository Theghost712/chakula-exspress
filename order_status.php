<?php
// Correct the include path
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Get order_id from GET parameters
$order_id = $_GET['order_id'] ?? 0;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Prepare the query
$sql = "SELECT order_status, updated_at FROM orders WHERE order_id = ? AND user_id = ?";
$params = [$order_id, $_SESSION['user_id']];

// If user is admin, allow viewing any order
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    $sql = "SELECT order_status, updated_at FROM orders WHERE order_id = ?";
    $params = [$order_id];
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $order = $stmt->fetch();
    
    if ($order) {
        echo json_encode([
            'success' => true,
            'status' => $order['order_status'],
            'updated_at' => $order['updated_at'],
            'timestamp' => time()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Order not found or access denied'
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Database error in check_order_status: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
?>