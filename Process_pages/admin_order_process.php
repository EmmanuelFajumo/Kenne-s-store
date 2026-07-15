<?php
// Pocess_pages/admin_order_process.php

require_once '../Classes/Database.php';
require_once '../Classes/User.php';
require_once '../Classes/Order.php';

$db = (new Database())->connect();
$userObj = new User($db);

// Verify admin permissions
if (!$userObj->isLoggedIn() || !$userObj->isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}

$orderObj = new Order($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? null;
    $status = $_POST['status'] ?? '';

    $allowedStatuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
    if ($orderId && in_array($status, $allowedStatuses)) {
        $success = $orderObj->updateStatus($orderId, $status);
        if ($success) {
            $_SESSION['success'] = "Order status updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update order status.";
        }
    } else {
        $_SESSION['error'] = "Invalid status update request.";
    }
}

header('Location: ../admin/orders.php');
exit();
