<?php
// Pocess_pages/admin_product_process.php

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $categoryId = $_POST['category_id'] ?? null;

    // Handle Image Upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../Assets/uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('prod_') . '.' . $fileExt;
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = "Assets/uploads/" . $fileName;
        }
    }

    if ($action === 'add') {
        if (empty($name) || $price <= 0) {
            $_SESSION['error'] = "Product name and positive price are required.";
            header('Location: ../admin/products.php');
            exit();
        }

        $success = $productObj->create($name, $description, $price, $imagePath, $stock, $categoryId);
        if ($success) {
            $_SESSION['success'] = "Product added successfully.";
        } else {
            $_SESSION['error'] = "Failed to add product.";
        }
        header('Location: ../admin/products.php');
        exit();

    } elseif ($action === 'edit' && $id) {
        if (empty($name) || $price <= 0) {
            $_SESSION['error'] = "Product name and positive price are required.";
            header('Location: ../admin/products.php');
            exit();
        }

        $success = $productObj->update($id, $name, $description, $price, $imagePath, $stock, $categoryId);
        if ($success) {
            $_SESSION['success'] = "Product updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update product.";
        }
        header('Location: ../admin/products.php');
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        // Optional: fetch product to delete upload image file from disk
        $prod = $productObj->getById($id);
        if ($prod && $prod['image'] && strpos($prod['image'], 'data:image') === false) {
            $fullPath = "../" . $prod['image'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
        
        $success = $productObj->delete($id);
        if ($success) {
            $_SESSION['success'] = "Product deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete product.";
        }
    }
    header('Location: ../admin/products.php');
    exit();
}

header('Location: ../admin/products.php');
exit();
