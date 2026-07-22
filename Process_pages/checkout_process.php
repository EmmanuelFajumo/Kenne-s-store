<?php
// Pocess_pages/checkout_process.php

require_once '../Classes/Database.php';
require_once '../Classes/User.php';
require_once '../Classes/Cart.php';
require_once '../Classes/Order.php';
require_once '../Classes/PaystackService.php'; // NEW
require_once '../config.php';                  // NEW — for PAYSTACK_SK / PAYSTACK_CALLBACK_URL

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

    $totalAmount = $cartObj->getTotal($userId);
    $orderObj = new Order($db);

    if ($paymentMethod === 'card') {
        // 1. Create the order up front (status 'pending', stock deducted as a reservation)
        $orderId = $orderObj->create($userId, $shippingDetails, $totalAmount, $cartItems, 'card');

        if (!$orderId) {
            $_SESSION['error'] = "Failed to place order. An item in your cart may be out of stock.";
            header('Location: ../checkout.php');
            exit();
        }

        // 2. Hand off to Paystack instead of your own payment.php
        $paystack = new PaystackService(PAYSTACK_SK);
        $reference = 'order_' . $orderId . '_' . time(); // unique per attempt

        $orderObj->setPaymentReference($orderId, $reference);

       try {
            $response = $paystack->initializeTransaction(
                $_SESSION['user_email'], // was: $userObj->getEmailById($userId)
                (int) round($totalAmount * 100),
                $reference,
                PAYSTACK_CALLBACK_URL,
                ['order_id' => $orderId]
            );

            if ($response['status'] === true) {
                // Cart stays intact until payment is verified — cleared in paystack_callback.php
                header('Location: ' . $response['data']['authorization_url']);
                exit();
            }

            // Paystack rejected the initialize call — release the stock we reserved
            $orderObj->restoreStock($orderId);
            $orderObj->updateStatus($orderId, 'failed');
            $_SESSION['error'] = "Could not start payment: " . ($response['message'] ?? 'Unknown error');
            header('Location: ../checkout.php');
            exit();

        } catch (Exception $e) {
            error_log($e->getMessage());
            $orderObj->restoreStock($orderId);
            $orderObj->updateStatus($orderId, 'failed');
            $_SESSION['error'] = "Payment could not be initiated. Please try again.";
            header('Location: ../checkout.php');
            exit();
        }

    } else {
        // Cash on Delivery — unchanged from your original logic
        $orderId = $orderObj->create($userId, $shippingDetails, $totalAmount, $cartItems, 'Cash on Delivery');
        if ($orderId) {
            $cartObj->clear($userId);
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