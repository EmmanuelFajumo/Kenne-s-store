<?php
// Pocess_pages/payment_process.php

require_once '../Classes/Database.php';
require_once '../Classes/User.php';
require_once '../Classes/Cart.php';
require_once '../Classes/Order.php';

$db = (new Database())->connect();
$userObj = new User($db);

if (!$userObj->isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$cartObj = new Cart($db);
$cartItems = $cartObj->getItems($userId);

if (!isset($_SESSION['shipping_details']) || empty($cartItems)) {
    $_SESSION['error'] = "Session expired or invalid checkout state.";
    header('Location: ../cart.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cardNumber = trim($_POST['card_number'] ?? '');
    $cardName = trim($_POST['card_name'] ?? '');
    $expiry = trim($_POST['expiry'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');

    if (empty($cardNumber) || empty($cardName) || empty($expiry) || empty($cvv)) {
        $_SESSION['error'] = "All credit card fields are required.";
        header('Location: ../payment.php');
        exit();
    }

    $shippingDetails = $_SESSION['shipping_details'];
    $totalAmount = $_SESSION['checkout_total'];
    $paymentMethod = "Credit Card";

    $orderObj = new Order($db);
    $orderId = $orderObj->create($userId, $shippingDetails, $totalAmount, $cartItems, $paymentMethod);

    if ($orderId) {
        // Generate simulated payment reference
        $reference = "TXN-" . strtoupper(bin2hex(random_bytes(6)));
        $paymentSuccess = $orderObj->updatePayment($orderId, $reference, 'paid');

        if ($paymentSuccess) {
            $cartObj->clear($userId);
            unset($_SESSION['shipping_details']);
            unset($_SESSION['payment_method']);
            unset($_SESSION['checkout_total']);
            header('Location: ../order_success.php?id=' . $orderId);
            exit();
        } else {
            $_SESSION['error'] = "Payment authorization failed, but order was registered. Contact admin.";
            header('Location: ../order_success.php?id=' . $orderId);
            exit();
        }
    } else {
        $_SESSION['error'] = "Failed to place order. A product in your cart may have gone out of stock.";
        header('Location: ../payment.php');
        exit();
    }
} else {
    header('Location: ../payment.php');
    exit();
}
