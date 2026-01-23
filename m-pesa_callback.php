<?php
require_once '../includes/config.php';

// M-Pesa callback endpoint
$callbackData = file_get_contents('php://input');
$data = json_decode($callbackData, true);

file_put_contents('mpesa_callback.log', date('Y-m-d H:i:s') . ' - ' . $callbackData . "\n", FILE_APPEND);

if (isset($data['Body']['stkCallback'])) {
    $callback = $data['Body']['stkCallback'];
    $resultCode = $callback['ResultCode'];
    $resultDesc = $callback['ResultDesc'];
    $merchantRequestID = $callback['MerchantRequestID'];
    $checkoutRequestID = $callback['CheckoutRequestID'];
    
    // Check if transaction was successful
    if ($resultCode == 0) {
        // Transaction successful
        $metadata = $callback['CallbackMetadata']['Item'] ?? [];
        $transactionData = [];
        
        foreach ($metadata as $item) {
            $transactionData[$item['Name']] = $item['Value'] ?? '';
        }
        
        $mpesaReceiptNumber = $transactionData['MpesaReceiptNumber'] ?? '';
        $transactionDate = $transactionData['TransactionDate'] ?? '';
        $phoneNumber = $transactionData['PhoneNumber'] ?? '';
        $amount = $transactionData['Amount'] ?? 0;
        
        // Update transaction in database
        $stmt = $pdo->prepare("
            UPDATE mobile_money_transactions 
            SET transaction_code = ?, 
                status = 'completed',
                completion_time = NOW(),
                raw_response = ?
            WHERE transaction_code = ? OR raw_response LIKE ?
        ");
        
        $stmt->execute([
            $mpesaReceiptNumber,
            $callbackData,
            $checkoutRequestID,
            '%' . $checkoutRequestID . '%'
        ]);
        
        // If we have a pending order session, create the order
        if (isset($_SESSION['pending_transaction']) && isset($_SESSION['pending_order'])) {
            // You would create the order here
            // This is called by M-Pesa, not by user session
        }
        
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
        
    } else {
        // Transaction failed
        $stmt = $pdo->prepare("
            UPDATE mobile_money_transactions 
            SET status = 'failed',
                completion_time = NOW(),
                raw_response = ?
            WHERE raw_response LIKE ? OR transaction_code = ?
        ");
        
        $stmt->execute([
            $callbackData,
            '%' . $checkoutRequestID . '%',
            $checkoutRequestID
        ]);
        
        echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Failed']);
    }
}
?>