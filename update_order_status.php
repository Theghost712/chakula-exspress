<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    header('HTTP/1.0 403 Forbidden');
    die('Access denied');
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET order_status = ?, 
            updated_at = NOW() 
        WHERE order_id = ?
    ");
    
    $success = $stmt->execute([$data['status'], $data['order_id']]);
    
    if ($success) {
        // Log the status change
        $stmt = $pdo->prepare("
            INSERT INTO order_status_log (order_id, old_status, new_status, changed_by)
            VALUES (?, (SELECT order_status FROM orders WHERE order_id = ?), ?, ?)
        ");
        $stmt->execute([$data['order_id'], $data['order_id'], $data['status'], $_SESSION['user_id']]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>