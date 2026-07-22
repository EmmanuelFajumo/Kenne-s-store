<?php
// Process_pages/paystack_callback.php

require_once '../Classes/Database.php';
require_once '../Classes/User.php';
require_once '../Classes/Cart.php';
require_once '../Classes/Order.php';
require_once '../Classes/PaystackService.php';
require_once '../config.php';

$db = (new Database())->connect();
$userObj = new User($db);

if (!$userObj->isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$orderObj = new Order($db);
$cartObj = new Cart($db);
$paystack = new PaystackService(PAYSTACK_SK);

$reference = $_GET['reference'] ?? null;

if (!$reference) {
    header('Location: ../checkout.php');
    exit();
}

try {
    $result = $paystack->verifyTransaction($reference);

    // Look up the order by reference (not by trusting metadata from the URL)
    $stmt = $db->prepare("SELECT * FROM orders WHERE payment_reference = ?");
    $stmt->execute([$reference]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['error'] = "Order not found.";
        header('Location: ../checkout.php');
        exit();
    }

    // Security check: this order must belong to the logged-in user
    if ($order['user_id'] != $_SESSION['user_id']) {
        $_SESSION['error'] = "Unauthorized access.";
        header('Location: ../index.php');
        exit();
    }

    $paidAmount = ($result['data']['amount'] ?? 0) / 100;

    if ($result['status'] === true
        && $result['data']['status'] === 'success'
        && (float) $order['total_amount'] === (float) $paidAmount) {

        // Avoid double-processing if this page is hit twice (e.g. refresh)
        if ($order['status'] !== 'paid') {
            $orderObj->updatePayment($order['id'], $reference, 'paid');
            $cartObj->clear($order['user_id']);
        }

        header('Location: ../order_success.php?id=' . $order['id']);
        exit();
    }

    // Payment failed or amount mismatch — release the reserved stock
    if ($order['status'] !== 'failed') {
        $orderObj->restoreStock($order['id']);
        $orderObj->updateStatus($order['id'], 'failed');
    }

    $_SESSION['error'] = "Payment was not successful. Please try again.";
    header('Location: ../checkout.php');
    exit();

} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "We couldn't confirm your payment. Please contact support if you were charged.";
    header('Location: ../checkout.php');
    exit();
}