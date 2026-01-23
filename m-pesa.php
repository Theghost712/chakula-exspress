<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    // Daraja API credentials (M-Pesa)
    $consumerKey = MPESA_CONSUMER_KEY;
    $consumerSecret = MPESA_CONSUMER_SECRET;
    $shortCode = MPESA_SHORTCODE;
    $passkey = MPESA_PASSKEY;
    
    // Generate timestamp
    $timestamp = date('YmdHis');
    
    // Generate password
    $password = base64_encode($shortCode . $passkey . $timestamp);
    
    // Get access token
    $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
    
    $ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($result);
    $access_token = $result->access_token ?? '';
    
    if (!$access_token) {
        throw new Exception('Could not get access token');
    }
    
    // Prepare STK Push request
    $amount = round($data['amount']);
    $phone = preg_replace('/[^0-9]/', '', $data['phone']);
    $transaction_id = 'TX' . date('YmdHis') . rand(1000, 9999);
    $reference = 'CHAKULA' . date('YmdHis');
    
    $stk_push_data = [
        'BusinessShortCode' => $shortCode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => $shortCode,
        'PhoneNumber' => $phone,
        'CallBackURL' => SITE_URL . '/api/mpesa_callback.php',
        'AccountReference' => $reference,
        'TransactionDesc' => 'Food Order Payment'
    ];
    
    // For production use:
    // $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    // For sandbox:
    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stk_push_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $response_data = json_decode($response, true);
    
    // Save transaction to database
    $stmt = $pdo->prepare("
        INSERT INTO mobile_money_transactions 
        (order_id, payment_method, transaction_code, phone_number, amount, status, raw_response)
        VALUES (?, 'mpesa', ?, ?, ?, 'pending', ?)
    ");
    
    // Note: order_id will be NULL initially, updated when order is created
    $stmt->execute([
        NULL,
        $transaction_id,
        $phone,
        $amount,
        json_encode($response_data)
    ]);
    
    $transaction_db_id = $pdo->lastInsertId();
    
    // Save transaction ID in session for later reference
    $_SESSION['pending_transaction'] = [
        'id' => $transaction_db_id,
        'transaction_id' => $transaction_id,
        'payment_method' => 'mpesa',
        'phone' => $phone,
        'amount' => $amount
    ];
    
    echo json_encode([
        'success' => true,
        'transaction_id' => $transaction_db_id,
        'reference' => $reference,
        'message' => 'STK Push sent successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'M-Pesa payment failed: ' . $e->getMessage()
    ]);
}