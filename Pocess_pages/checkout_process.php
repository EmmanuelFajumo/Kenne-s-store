<?php
// Pocess_pages/checkout_process.php

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

if (empty($cartItems)) {
    $_SESSION['error'] = "Your cart is empty.";
    header('Location: ../cart.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'card');

    if (empty($name) || empty($address) || empty($city) || empty($zip) || empty($contact)) {
        $_SESSION['error'] = "All shipping fields are required.";
        header('Location: ../checkout.php');
        exit();
    }

    $shippingDetails = [
        'name' => $name,
        'address' => $address,
        'city' => $city,
        'zip' => $zip,
        'contact' => $contact
    ];

    $_SESSION['shipping_details'] = $shippingDetails;
    $_SESSION['payment_method'] = $paymentMethod;
    
    $totalAmount = $cartObj->getTotal($userId);
    $_SESSION['checkout_total'] = $totalAmount;

    if ($paymentMethod === 'card') {
        header('Location: ../payment.php');
        exit();
    } else {
        // Cash on Delivery
        $orderObj = new Order($db);
        $orderId = $orderObj->create($userId, $shippingDetails, $totalAmount, $cartItems, 'Cash on Delivery');
        if ($orderId) {
            $cartObj->clear($userId);
            unset($_SESSION['shipping_details']);
            unset($_SESSION['payment_method']);
            unset($_SESSION['checkout_total']);
            header('Location: ../order_success.php?id=' . $orderId);
            exit();
        } else {
            $_SESSION['error'] = "Failed to place order. An item in your cart may be out of stock.";
            header('Location: ../checkout.php');
            exit();
        }
    }
} else {
    header('Location: ../checkout.php');
    exit();
}
