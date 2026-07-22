<?php

// order_success.php
require_once 'header.php';
require_once 'Classes/Order.php';   // <-- add this

if (!$userObj->isLoggedIn()) {
    header('Location: login.php');
    exit();
}


$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    header('Location: index.php');
    exit();
}

$orderObj = new Order($db);
$order = $orderObj->getById($orderId);

if (!$order) {
    $_SESSION['error'] = "Order not found.";
    header('Location: index.php');
    exit();
}

// Security check: ensure order belongs to current user
if ($order['user_id'] != $_SESSION['user_id'] && !$userObj->isAdmin()) {
    $_SESSION['error'] = "Unauthorized access.";
    header('Location: index.php');
    exit();
}

// Convert status to appropriate badge class
$badgeClass = 'badge-pending';
if ($order['status'] === 'paid') $badgeClass = 'badge-paid';
if ($order['status'] === 'shipped') $badgeClass = 'badge-shipped';
if ($order['status'] === 'completed') $badgeClass = 'badge-completed';
if ($order['status'] === 'cancelled') $badgeClass = 'badge-cancelled';
?>

<div class="row justify-content-center pt-4">
    <div class="col-md-9 mb-4">
        <!-- Success Confirmation Header -->
        <div class="text-center mb-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-white border border-2 border-dark rounded-circle mb-3" style="width: 70px; height: 70px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-check2 text-success" viewBox="0 0 16 16">
                  <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                </svg>
            </div>
            <h1 class="fw-bold text-uppercase" style="letter-spacing: -0.01em;">Order Placed Successfully</h1>
            <p class="text-muted">A confirmation email has been simulated and sent to <?= htmlspecialchars($order['customer_email']) ?>.</p>
        </div>

        <!-- Invoice / Receipt Container -->
        <div class="card border rounded-0 bg-white shadow-sm p-4 md-p-5">
            <!-- Header Invoice Details -->
            <div class="row pb-4 mb-4 border-bottom align-items-center">
                <div class="col-sm-6 mb-3 mb-sm-0">
                    <h5 class="brand-font mb-1" style="font-size: 1.5rem; font-weight: 700; letter-spacing: 0.05em;">Kene<span style="color: var(--accent-color)">Store</span></h5>
                    <small class="text-muted">Single-Vendor E-commerce</small>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <span class="text-uppercase text-muted d-block" style="font-size: 0.75rem; letter-spacing: 0.05em;">Invoice Number</span>
                    <h5 class="fw-bold text-dark mb-0">#KS-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></h5>
                    <small class="text-muted">Date: <?= date('d M Y, H:i', strtotime($order['created_at'])) ?></small>
                </div>
            </div>

            <!-- Shipping & Billing Columns -->
            <div class="row pb-4 mb-4 border-bottom">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h6 class="form-label-minimal mb-3">Deliver To</h6>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($order['shipping_name']) ?></h5>
                    <p class="text-muted mb-1"><?= htmlspecialchars($order['shipping_address']) ?></p>
                    <p class="text-muted mb-1"><?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_zip']) ?></p>
                    <p class="text-muted mb-0">Phone: <?= htmlspecialchars($order['contact_number']) ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="form-label-minimal mb-3">Transaction Info</h6>
                    <p class="mb-1 text-muted"><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
                    <?php if ($order['payment_reference']): ?>
                        <p class="mb-1 text-muted"><strong>Reference:</strong> <?= htmlspecialchars($order['payment_reference']) ?></p>
                    <?php endif; ?>
                    <p class="mb-0 text-muted">
                        <strong>Order Status:</strong> 
                        <span class="badge-minimal <?= $badgeClass ?> ms-1"><?= htmlspecialchars($order['status']) ?></span>
                    </p>
                </div>
            </div>

            <!-- Itemized Products -->
            <h6 class="form-label-minimal mb-3">Purchased Items</h6>
            <div class="table-responsive mb-4">
                <table class="table table-minimal w-100 mb-0">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $item['product_image'] ?>" alt="" class="img-thumbnail rounded-0 me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($item['product_name']) ?></span>
                                    </div>
                                </td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-end">₦<?= number_format($item['price'], 2) ?></td>
                                <td class="text-end">₦<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- Subtotal & Grand Total Rows -->
                        <tr>
                            <td colspan="2" class="border-0"></td>
                            <td class="text-end text-muted fw-semibold py-2">Subtotal</td>
                            <td class="text-end fw-semibold py-2">₦<?= number_format($order['total_amount'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border-0"></td>
                            <td class="text-end text-muted fw-semibold py-2">Shipping</td>
                            <td class="text-end text-success fw-semibold py-2">FREE</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border-0"></td>
                            <td class="text-end fw-bold text-uppercase py-2" style="font-size: 0.9rem; letter-spacing: 0.05em; border-top: 2px solid var(--fg-color);">Total Charged</td>
                            <td class="text-end fw-bold text-dark py-2" style="font-size: 1.25rem; border-top: 2px solid var(--fg-color);">₦<?= number_format($order['total_amount'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-3" style="color: var(--grey-muted); font-size: 0.85rem;">
                Thank you for supporting KeneStore. This transaction was securely processed.
            </div>
        </div>

        <!-- Navigation CTAs -->
        <div class="d-flex justify-content-between mt-4">
            <a href="index.php" class="btn btn-minimal btn-minimal-outline">Continue Shopping</a>
            <a href="orders.php" class="btn btn-minimal btn-minimal-dark">View My Orders</a>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
