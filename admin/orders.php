<?php
// admin/orders.php
require_once 'admin_header.php';

$orderObj = new Order($db);
$orders = $orderObj->getAllOrders();

// Check if we are viewing specific order details
$viewOrder = null;
$viewId = $_GET['view'] ?? null;
if ($viewId) {
    $viewOrder = $orderObj->getById($viewId);
}
?>

<div class="row">
    <div class="col-12 mb-4">
        <h2 class="fw-bold text-uppercase m-0" style="letter-spacing: -0.01em;">Manage Orders</h2>
    </div>

    <!-- Orders list (adapts size depending on whether an order details panel is open) -->
    <div class="<?= $viewOrder ? 'col-lg-7' : 'col-lg-12' ?> mb-4">
        <div class="card border rounded-0 bg-white shadow-sm p-4">
            <h4 class="text-uppercase mb-3" style="font-size: 1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Transaction Logs</h4>
            
            <div class="table-responsive">
                <table class="table table-minimal mb-0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No orders placed yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $ord): 
                                $badgeClass = 'badge-pending';
                                if ($ord['status'] === 'paid') $badgeClass = 'badge-paid';
                                if ($ord['status'] === 'shipped') $badgeClass = 'badge-shipped';
                                if ($ord['status'] === 'completed') $badgeClass = 'badge-completed';
                                if ($ord['status'] === 'cancelled') $badgeClass = 'badge-cancelled';
                            ?>
                                <tr class="<?= ($viewOrder && $viewOrder['id'] == $ord['id']) ? 'table-secondary' : '' ?>">
                                    <td class="fw-bold">#KS-<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= htmlspecialchars($ord['customer_name']) ?></td>
                                    <td style="font-size: 0.85rem;"><?= date('d M Y, H:i', strtotime($ord['created_at'])) ?></td>
                                    <td class="fw-bold">₦<?= number_format($ord['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge-minimal <?= $badgeClass ?>"><?= htmlspecialchars($ord['status']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="orders.php?view=<?= $ord['id'] ?>" class="btn btn-minimal btn-minimal-dark py-1 px-2" style="font-size: 0.75rem;">Inspect</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Detail & Inspection Panel (Right Column, only shows when viewing an order) -->
    <?php if ($viewOrder): 
        $badgeClass = 'badge-pending';
        if ($viewOrder['status'] === 'paid') $badgeClass = 'badge-paid';
        if ($viewOrder['status'] === 'shipped') $badgeClass = 'badge-shipped';
        if ($viewOrder['status'] === 'completed') $badgeClass = 'badge-completed';
        if ($viewOrder['status'] === 'cancelled') $badgeClass = 'badge-cancelled';
    ?>
        <div class="col-lg-5">
            <div class="card border rounded-0 bg-white shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 2px solid var(--accent-color); padding-bottom: 10px;">
                    <h4 class="text-uppercase m-0" style="font-size: 1rem; letter-spacing: 0.05em;">Order Details #KS-<?= str_pad($viewOrder['id'], 5, '0', STR_PAD_LEFT) ?></h4>
                    <a href="orders.php" class="text-decoration-none text-muted" style="font-size: 1.2rem;">&times;</a>
                </div>

                <!-- Update Status Form -->
                <div class="bg-light p-3 border mb-4">
                    <form action="../Pocess_pages/admin_order_process.php" method="POST" class="row g-2 align-items-center">
                        <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
                        <div class="col-auto">
                            <label for="status" class="form-label-minimal m-0">Change Status</label>
                        </div>
                        <div class="col">
                            <select name="status" id="status" class="form-select form-control-minimal py-1 px-2">
                                <option value="pending" <?= $viewOrder['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="paid" <?= $viewOrder['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="shipped" <?= $viewOrder['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="completed" <?= $viewOrder['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $viewOrder['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-minimal btn-minimal-dark py-1 px-3" style="font-size: 0.75rem;">Apply</button>
                        </div>
                    </form>
                </div>

                <!-- Delivery Details -->
                <div class="mb-4">
                    <h6 class="form-label-minimal mb-2">Customer Profile</h6>
                    <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($viewOrder['customer_name']) ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($viewOrder['customer_email']) ?></p>
                    <p class="mb-3"><strong>Contact:</strong> <?= htmlspecialchars($viewOrder['contact_number']) ?></p>

                    <h6 class="form-label-minimal mb-2">Delivery Destination</h6>
                    <p class="mb-1"><strong>Address:</strong> <?= htmlspecialchars($viewOrder['shipping_address']) ?></p>
                    <p class="mb-3"><strong>City/ZIP:</strong> <?= htmlspecialchars($viewOrder['shipping_city']) ?>, <?= htmlspecialchars($viewOrder['shipping_zip']) ?></p>

                    <h6 class="form-label-minimal mb-2">Transaction Details</h6>
                    <p class="mb-1"><strong>Payment Method:</strong> <?= htmlspecialchars($viewOrder['payment_method']) ?></p>
                    <p class="mb-1"><strong>Reference Code:</strong> <?= htmlspecialchars($viewOrder['payment_reference'] ?: 'N/A') ?></p>
                    <p class="mb-0"><strong>Receipt Timestamp:</strong> <?= date('d M Y, H:i', strtotime($viewOrder['created_at'])) ?></p>
                </div>

                <!-- Itemized Invoice breakdown -->
                <h6 class="form-label-minimal mb-2">Items Ordered</h6>
                <ul class="list-group list-group-minimal border mb-3">
                    <?php foreach ($viewOrder['items'] as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-3" style="font-size: 0.9rem;">
                            <span class="text-truncate me-2" style="max-width: 220px;"><?= htmlspecialchars($item['product_name']) ?> &times; <?= $item['quantity'] ?></span>
                            <span class="fw-bold">₦<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-3 bg-light" style="font-size: 0.9rem; font-weight: bold; border-top: 1px solid var(--fg-color);">
                        <span>Total Paid</span>
                        <span style="color: var(--accent-color);">₦<?= number_format($viewOrder['total_amount'], 2) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

</div> <!-- End col-md-10 -->
</div> <!-- End row -->
</div> <!-- End container-fluid -->
<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
