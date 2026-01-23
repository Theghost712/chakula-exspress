<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$transaction_id = $_GET['transaction_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT status, transaction_code, amount, phone_number
    FROM mobile_money_transactions
    WHERE id = ?
");
$stmt->execute([$transaction_id]);
$transaction = $stmt->fetch();

if ($transaction) {
    echo json_encode([
        'status' => $transaction['status'],
        'transaction_code' => $transaction['transaction_code'],
        'amount' => $transaction['amount'],
        'phone' => $transaction['phone_number']
    ]);
} else {
    echo json_encode(['status' => 'not_found']);
}
?>