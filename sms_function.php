<?php
// Africa's Talking SMS Integration for Tanzania
function sendSMS_TZ($phone, $message) {
    // Remove any non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Add country code if missing
    if (substr($phone, 0, 3) !== '255') {
        $phone = '255' . substr($phone, -9);
    }
    
    $username = 'chakulaexpress'; // Your Africa's Talking username
    $apiKey = 'YOUR_AFRICAS_TALKING_API_KEY';
    
    // Prepare POST data
    $postData = [
        'username' => $username,
        'to' => $phone,
        'message' => $message,
        'from' => 'CHAKULA'
    ];
    
    $headers = [
        'apiKey: ' . $apiKey,
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.africastalking.com/version1/messaging');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log SMS sending
    $logData = [
        'phone' => $phone,
        'message' => $message,
        'response' => $response,
        'http_code' => $httpCode,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents('sms_log.json', json_encode($logData) . "\n", FILE_APPEND);
    
    return $response;
}

// Send order confirmation SMS
function sendOrderConfirmationSMS($orderId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT o.order_number, o.total_amount, u.phone, u.name
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if ($order) {
        $message = "Habari {$order['name']}! Oda yako #{$order['order_number']} imepokelewa. " .
                  "Jumla: TSh " . number_format($order['total_amount'], 2) . ". " .
                  "Asante kwa kuagiza na Chakula Express!";
        
        return sendSMS_TZ($order['phone'], $message);
    }
    
    return false;
}

// Send delivery status update SMS
function sendDeliveryUpdateSMS($orderId, $status) {
    global $pdo;
    
    $statusMessages = [
        'preparing' => 'Oda yako inatayarishwa na mkahawa.',
        'ready' => 'Oda yako imetayarishwa na inangojea usafirishaji.',
        'out_for_delivery' => 'Oda yako imetoka kwa usafirishaji! Mtumaji atawasilisha hivi karibuni.',
        'delivered' => 'Oda yako imewasilishwa! Asante kwa kuagiza na Chakula Express.'
    ];
    
    $stmt = $pdo->prepare("
        SELECT o.order_number, u.phone, u.name
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if ($order && isset($statusMessages[$status])) {
        $message = "Habari {$order['name']}! Oda #{$order['order_number']}: " . 
                  $statusMessages[$status];
        
        return sendSMS_TZ($order['phone'], $message);
    }
    
    return false;
}
?>