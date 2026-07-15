<?php
// Pocess_pages/register_process.php

require_once '../Classes/Database.php';
require_once '../Classes/User.php';

$db = (new Database())->connect();
$userObj = new User($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = $userObj->register($name, $email, $password, 'customer');

    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: ../login.php');
        exit();
    } else {
        $_SESSION['error'] = $result['message'];
        // Keep name and email in session to repopulate form
        $_SESSION['reg_name'] = $name;
        $_SESSION['reg_email'] = $email;
        header('Location: ../register.php');
        exit();
    }
} else {
    header('Location: ../register.php');
    exit();
}
