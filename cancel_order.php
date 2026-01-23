<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET order_status = 'cancelled', 
            updated_at = NOW() 
        WHERE order_id = ? 
        AND user_id = ?
        AND order_status NOT IN ('delivered', 'out_for_delivery', 'cancelled')
    ");
    
    $success = $stmt->execute([$data['order_id'], $_SESSION['user_id']]);
    
    if ($success && $stmt->rowCount() > 0) {
        // Log cancellation
        $stmt = $pdo->prepare("INSERT INTO order_cancellations (order_id, user_id, reason) VALUES (?, ?, ?)");
        $stmt->execute([$data['order_id'], $_SESSION['user_id'], $data['reason'] ?? '']);
        
        echo json_encode(['success' => true, 'message' => 'Order cancelled']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cannot cancel order at this stage']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>