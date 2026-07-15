<?php
// Pocess_pages/login_process.php

require_once '../Classes/Database.php';
require_once '../Classes/User.php';
require_once '../Classes/Cart.php';

$db = (new Database())->connect();
$userObj = new User($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = $userObj->login($email, $password);

    if ($result['success']) {
        // Merge session cart to db cart
        $cartObj = new Cart($db);
        $cartObj->mergeCart($_SESSION['user_id']);

        $redirect = $_POST['redirect'] ?? '';

        if ($userObj->isAdmin()) {
            header('Location: ../admin/dashboard.php');
        } elseif (!empty($redirect)) {
            header('Location: ../' . ltrim($redirect, '/'));
        } else {
            header('Location: ../index.php');
        }
        exit();
    } else {
        $_SESSION['error'] = $result['message'];
        header('Location: ../login.php');
        exit();
    }
} else {
    header('Location: ../login.php');
    exit();
}
