<?php
// Pocess_pages/cart_process.php

require_once '../Classes/Database.php';
require_once '../Classes/Cart.php';
require_once '../Classes/User.php';

$db = (new Database())->connect();
$userObj = new User($db);
$cartObj = new Cart($db);

$userId = $userObj->isLoggedIn() ? $_SESSION['user_id'] : null;

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;
    $quantity = intval($_POST['quantity'] ?? 1);
} else {
    $productId = $_GET['product_id'] ?? null;
    $quantity = intval($_GET['quantity'] ?? 1);
}

if ($action === 'add' && $productId) {
    $cartObj->add($userId, $productId, $quantity);
    header('Location: ../cart.php');
    exit();
} elseif ($action === 'update' && $productId) {
    $cartObj->updateQuantity($userId, $productId, $quantity);
    header('Location: ../cart.php');
    exit();
} elseif ($action === 'remove' && $productId) {
    $cartObj->remove($userId, $productId);
    header('Location: ../cart.php');
    exit();
} elseif ($action === 'clear') {
    $cartObj->clear($userId);
    header('Location: ../cart.php');
    exit();
} else {
    header('Location: ../cart.php');
    exit();
}
