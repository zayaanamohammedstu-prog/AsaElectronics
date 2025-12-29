<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$reference = $_GET['reference'] ?? '';

if (empty($reference)) {
    setFlash('error', 'Invalid payment reference');
    redirect('/public/index.php');
}

// Verify payment with PayStack
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Cache-Control: no-cache",
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    setFlash('error', 'Payment verification failed. Please contact support.');
    redirect('/public/account.php');
}

$result = json_decode($response, true);

// Update order based on verification
if ($result && $result['status'] && $result['data']['status'] === 'success') {
    // Payment successful
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'completed', status = 'processing' 
        WHERE payment_reference = ?
    ");
    $stmt->execute([$reference]);
    
    setFlash('success', 'Payment successful! Your order has been confirmed.');
    redirect('/public/account.php');
} else {
    // Payment failed
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'failed' 
        WHERE payment_reference = ?
    ");
    $stmt->execute([$reference]);
    
    setFlash('error', 'Payment failed. Please try again or contact support.');
    redirect('/public/account.php');
}
