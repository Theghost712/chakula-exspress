<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $pdo->beginTransaction();
    
    // Get pending order data
    $orderData = $_SESSION['pending_order'];
    
    // Create order
    $orderNumber = 'OD' . date('Ymd') . strtoupper(substr(uniqid(), -6));
    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (order_number, user_id, restaurant_id, total_amount, 
         delivery_address, delivery_city, delivery_phone, 
         delivery_instructions, payment_method, payment_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $payment_status = $data['payment_method'] == 'cod' ? 'pending' : 'paid';
    
    $stmt->execute([
        $orderNumber,
        $_SESSION['user_id'],
        $orderData['restaurant_id'],
        $orderData['total'],
        $data['address'],
        $data['city'],
        $data['phone'],
        $data['instructions'] ?? '',
        $data['payment_method'],
        $payment_status
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    // Add order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, item_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($orderData['items'] as $item) {
        $stmt->execute([$orderId, $item['item_id'], $item['quantity'], $item['price']]);
    }
    
    // Add tax record
    $stmt = $pdo->prepare("
        INSERT INTO order_taxes (order_id, tax_type, tax_rate, tax_amount)
        VALUES (?, 'VAT', ?, ?)
    ");
    $stmt->execute([$orderId, VAT_RATE * 100, $orderData['vat']]);
    
    // Update mobile money transaction with order ID if exists
    if (isset($_SESSION['pending_transaction'])) {
        $transaction = $_SESSION['pending_transaction'];
        
        $stmt = $pdo->prepare("
            UPDATE mobile_money_transactions 
            SET order_id = ?
            WHERE id = ?
        ");
        $stmt->execute([$orderId, $transaction['id']]);
        
        // Update order with transaction code
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET payment_reference = ?
            WHERE order_id = ?
        ");
        $stmt->execute([$transaction['transaction_id'], $orderId]);
    }
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    $pdo->commit();
    
    // Clear session data
    unset($_SESSION['pending_order']);
    unset($_SESSION['pending_transaction']);
    
    // Send SMS notification (Tanzania)
    sendTZSMS($data['phone'], "Habari! Oda yako #$orderNumber imepokelewa. Bei: TSh " . 
              number_format($orderData['total'], 2) . ". Asante kwa kuagiza na Chakula Express!");
    
    // Send WhatsApp message if possible
    sendWhatsAppMessage($data['phone'], $orderId);
    
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'message' => 'Order placed successfully'
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

<?php
// Helper function to send SMS in Tanzania
function sendTZSMS($phone, $message) {
    // Using Africa's Talking SMS API (popular in East Africa)
    $username = 'YOUR_AT_USERNAME';
    $apiKey = 'YOUR_AT_API_KEY';
    
    $recipients = $phone;
    $from = 'CHAKULA'; // Sender ID
    
    // Prepare POST data
    $postData = [
        'username' => $username,
        'to' => $recipients,
        'message' => $message,
        'from' => $from
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.africastalking.com/version1/messaging');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apiKey: ' . $apiKey,
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

function sendWhatsAppMessage($phone, $orderId) {
    // Using WhatsApp Business API or Twilio
    // This is a placeholder for WhatsApp integration
}
?>