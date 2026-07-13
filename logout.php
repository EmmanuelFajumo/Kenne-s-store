<?php
// logout.php
require_once 'Classes/Database.php';
require_once 'Classes/User.php';

$db = (new Database())->connect();
$userObj = new User($db);

$userObj->logout();
header('Location: index.php');
exit();
