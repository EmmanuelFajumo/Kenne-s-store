<?php
// Pocess_pages/admin_category_process.php

require_once '../Classes/Database.php';
require_once '../Classes/User.php';
require_once '../Classes/Product.php';

$db = (new Database())->connect();
$userObj = new User($db);

// Verify admin permissions
if (!$userObj->isLoggedIn() || !$userObj->isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}

$productObj = new Product($db);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $name = trim($_POST['name'] ?? '');
    if (empty($name)) {
        $_SESSION['error'] = "Category name is required.";
    } else {
        $success = $productObj->createCategory($name);
        if ($success) {
            $_SESSION['success'] = "Category added successfully.";
        } else {
            $_SESSION['error'] = "Failed to add category. It may already exist.";
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $success = $productObj->deleteCategory($id);
        if ($success) {
            $_SESSION['success'] = "Category deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete category.";
        }
    }
}

header('Location: ../admin/categories.php');
exit();
