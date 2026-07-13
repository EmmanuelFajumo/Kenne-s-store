<?php
// admin/admin_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../Classes/Database.php';
require_once '../Classes/User.php';
require_once '../Classes/Product.php';
require_once '../Classes/Order.php';

$db = (new Database())->connect();
$userObj = new User($db);

// Verify admin permissions
if (!$userObj->isLoggedIn() || !$userObj->isAdmin()) {
    $_SESSION['error'] = "Access denied. Administrator permissions required.";
    header('Location: ../login.php');
    exit();
}

$page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KeneStore Admin | Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="../Assets/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-minimal shadow-sm py-2">
        <div class="container-fluid">
            <a class="navbar-brand font-weight-bold" href="dashboard.php">
                Kene<span style="color: var(--accent-color)">Store</span> <span class="text-muted" style="font-size: 0.85rem; letter-spacing: 0px; text-transform: none;">Admin Console</span>
            </a>
            <div class="d-flex align-items-center">
                <span class="text-uppercase fw-semibold me-3" style="font-size: 0.8rem; letter-spacing: 0.05em; color: var(--grey-muted);">
                    Root Admin
                </span>
                <a href="../logout.php" class="btn btn-minimal btn-minimal-outline py-1 px-3 text-danger border-danger" style="font-size: 0.75rem;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-2 p-0 admin-sidebar">
                <a href="dashboard.php" class="<?= $page === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
                <a href="products.php" class="<?= $page === 'products.php' ? 'active' : '' ?>">Products</a>
                <a href="categories.php" class="<?= $page === 'categories.php' ? 'active' : '' ?>">Categories</a>
                <a href="orders.php" class="<?= $page === 'orders.php' ? 'active' : '' ?>">Orders</a>
                <a href="users.php" class="<?= $page === 'users.php' ? 'active' : '' ?>">Users</a>
                <hr style="background-color: var(--grey-dark); margin: 1.5rem 0;">
                <a href="../index.php" target="_blank" style="font-size: 0.8rem; color: var(--accent-color);">View Storefront &rarr;</a>
            </div>

            <!-- Content Area -->
            <div class="col-md-10 py-4 px-4 bg-light" style="min-height: calc(100vh - 65px);">
                <!-- Alerts Block -->
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
