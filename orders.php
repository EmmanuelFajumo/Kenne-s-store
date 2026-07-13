<?php
// orders.php
require_once 'header.php';

if (!$userObj->isLoggedIn()) {
    $_SESSION['error'] = "You must be logged in to view your orders.";
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$orderObj = new Order($db);
$orders = $orderObj->getByUser($userId);
?>

<div class="row pt-4">
    <div class="col-12 mb-4">
        <h1 class="fw-bold text-uppercase" style="letter-spacing: -0.02em;">My Orders</h1>
        <p class="text-muted">Review your purchase transaction logs and order statuses.</p>
    </div>

    <div class="col-12">
        <?php if (empty($orders)): ?>
            <div class="card border rounded-0 p-5 bg-white text-center shadow-sm">
                <h3 class="fw-light text-muted mb-4">You have not placed any orders yet.</h3>
                <a href="index.php" class="btn btn-minimal btn-minimal-dark col-md-3 mx-auto">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="table-responsive bg-white border">
                <table class="table table-minimal w-100 mb-0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th class="text-end">Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): 
                            $badgeClass = 'badge-pending';
                            if ($order['status'] === 'paid') $badgeClass = 'badge-paid';
                            if ($order['status'] === 'shipped') $badgeClass = 'badge-shipped';
                            if ($order['status'] === 'completed') $badgeClass = 'badge-completed';
                            if ($order['status'] === 'cancelled') $badgeClass = 'badge-cancelled';
                        ?>
                            <tr>
                                <td class="fw-bold">#KS-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                                <td class="fw-semibold">$<?= number_format($order['total_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                                <td>
                                    <span class="badge-minimal <?= $badgeClass ?>"><?= htmlspecialchars($order['status']) ?></span>
                                </td>
                                <td class="text-end">
                                    <a href="order_success.php?id=<?= $order['id'] ?>" class="btn btn-minimal btn-minimal-dark py-1 px-3" style="font-size: 0.75rem;">View Invoice</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'footer.php';
?>
