<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'Classes/Database.php';
require_once 'Classes/User.php';
require_once 'Classes/Product.php';
require_once 'Classes/Cart.php';

$db = (new Database())->connect();
$userObj = new User($db);
$cartObj = new Cart($db);

$userId = $userObj->isLoggedIn() ? $_SESSION['user_id'] : null;
$cartCount = $cartObj->getCount($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KeneStore | Minimal E-commerce</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="Assets/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-minimal shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Kene<span style="color: var(--accent-color)">Store</span></a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Shop</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="cart.php" class="me-4 position-relative py-2 text-uppercase font-weight-semibold" style="font-size: 0.85rem; letter-spacing: 0.05em; color: var(--fg-color);">
                        Cart
                        <span class="cart-badge"><?= $cartCount ?></span>
                    </a>
                    
                    <?php if ($userObj->isLoggedIn()): ?>
                        <div class="dropdown">
                            <a class="btn btn-link text-dark dropdown-toggle text-decoration-none text-uppercase fw-semibold p-0" href="#" role="button" id="userMenuLink" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.85rem; letter-spacing: 0.05em;">
                                <?= htmlspecialchars($_SESSION['user_name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2" aria-labelledby="userMenuLink" style="border-radius: 0px;">
                                <?php if ($userObj->isAdmin()): ?>
                                    <li><a class="dropdown-item text-uppercase" href="admin/dashboard.php" style="font-size: 0.8rem; letter-spacing: 0.05em;">Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item text-uppercase" href="orders.php" style="font-size: 0.8rem; letter-spacing: 0.05em;">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-uppercase text-danger" href="logout.php" style="font-size: 0.8rem; letter-spacing: 0.05em;">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-minimal btn-minimal-outline me-2 py-2 px-3">Log In</a>
                        <a href="register.php" class="btn btn-minimal btn-minimal-dark py-2 px-3">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content Container -->
    <div class="container my-4" style="min-height: 60vh;">
        <!-- Status Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-0" role="alert" style="background-color: #F8D7DA; color: #721C24;">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 rounded-0" role="alert" style="background-color: #D4EDDA; color: #155724;">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
