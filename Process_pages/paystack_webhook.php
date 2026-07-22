<?php
// Process_pages/paystack_webhook.php
// Paystack's servers call this directly — no session/cookies, so don't
// route through anything that expects a logged-in user.

require_once '../Classes/Database.php';
require_once '../Classes/Order.php';
require_once '../config.php';

$db = (new Database())->connect();
$orderObj = new Order($db);

$input = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
$expected = hash_hmac('sha512', $input, PAYSTACK_SK);

if (!hash_equals($expected, $signature)) {
    http_response_code(401);
    exit();
}

$event = json_decode($input, true);

if (($event['event'] ?? '') === 'charge.success') {
    $reference = $event['data']['reference'];
    $paidAmount = $event['data']['amount'] / 100;

    $stmt = $db->prepare("SELECT * FROM orders WHERE payment_reference = ?");
    $stmt->execute([$reference]);
    $order = $stmt->fetch();

    if ($order && $order['status'] !== 'paid' && (float) $order['total_amount'] === (float) $paidAmount) {
        $orderObj->updatePayment($order['id'], $reference, 'paid');
        // Note: cartObj->clear() is skipped here deliberately — this endpoint
        // has no session, so it can't know which cart to reach for.
        // The user's own browser session already cleared it via paystack_callback.php
        // in the normal case; this just covers the case where they never made it back.
    }
}

http_response_code(200);