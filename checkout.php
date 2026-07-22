<?php
// checkout.php
require_once 'header.php';

if (!$userObj->isLoggedIn()) {
    $_SESSION['error'] = "You must be logged in to access checkout.";
    header('Location: login.php?redirect=checkout.php');
    exit();
}

$userId = $_SESSION['user_id'];
$cartItems = $cartObj->getItems($userId);
$cartTotal = $cartObj->getTotal($userId);

if (empty($cartItems)) {
    $_SESSION['error'] = "Your cart is empty.";
    header('Location: cart.php');
    exit();
}
?>

<div class="row pt-4">
    <div class="col-12 mb-4">
        <h1 class="fw-bold text-uppercase" style="letter-spacing: -0.02em;">Checkout</h1>
    </div>

    <!-- Billing/Shipping Information Form -->
    <div class="col-lg-7 mb-4">
        <div class="card border rounded-0 p-4 bg-white shadow-sm">
            <h4 class="text-uppercase mb-4" style="font-size: 1.1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Shipping Address</h4>
            
            <form action="Process_pages/checkout_process.php" method="POST">
                <!-- Full Name -->
                <div class="mb-3">
                    <label for="name" class="form-label-minimal">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control form-control-minimal" required value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                </div>

                <!-- Delivery Address -->
                <div class="mb-3">
                    <label for="address" class="form-label-minimal">Delivery Address</label>
                    <textarea name="address" id="address" rows="3" class="form-control form-control-minimal" required placeholder="Street address, apartment, suite, etc."></textarea>
                </div>

                <div class="row">
                    <!-- City -->
                    <div class="col-md-6 mb-3">
                        <label for="city" class="form-label-minimal">City</label>
                        <input type="text" name="city" id="city" class="form-control form-control-minimal" required>
                    </div>
                    <!-- ZIP Code -->
                    <div class="col-md-6 mb-3">
                        <label for="zip" class="form-label-minimal">ZIP / Postal Code</label>
                        <input type="text" name="zip" id="zip" class="form-control form-control-minimal" required>
                    </div>
                </div>

                <!-- Contact Number -->
                <div class="mb-4">
                    <label for="contact" class="form-label-minimal">Contact Number</label>
                    <input type="tel" name="contact" id="contact" class="form-control form-control-minimal" required placeholder="e.g. +234 80 1234 5678">
                </div>

                <h4 class="text-uppercase mb-3 mt-4" style="font-size: 1.1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Payment Method</h4>
                
                <!-- Payment Choices -->
                <div class="mb-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="pay_card" value="card" checked>
                        <label class="form-check-label fw-semibold" for="pay_card">
                            Credit / Debit Card <small class="text-muted d-block font-weight-normal" style="font-size: 0.8rem;">Secure instant card transaction simulation</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="cod">
                        <label class="form-check-label fw-semibold" for="pay_cod">
                            Cash on Delivery (COD) <small class="text-muted d-block font-weight-normal" style="font-size: 0.8rem;">Pay with cash when your items arrive</small>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-minimal btn-minimal-orange w-100 py-3 mt-2">Place Order</button>
            </form>
        </div>
    </div>

    <!-- Order Items Review Panel -->
    <div class="col-lg-5">
        <div class="card border rounded-0 p-4 bg-white shadow-sm">
            <h4 class="text-uppercase mb-4" style="font-size: 1.1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Review Items</h4>
            
            <div class="mb-4 overflow-auto" style="max-height: 300px;">
                <?php foreach ($cartItems as $item): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <img src="<?= $item['image'] ?>" alt="" class="img-thumbnail rounded-0 me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <span class="fw-bold d-block text-truncate" style="max-width: 180px; font-size: 0.9rem;"><?= htmlspecialchars($item['name']) ?></span>
                                <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                            </div>
                        </div>
                        <span class="fw-semibold text-dark">₦<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <hr>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Subtotal</span>
                <span>₦<?= number_format($cartTotal, 2) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Shipping</span>
                <span class="text-success fw-semibold">FREE</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-0">
                <span class="fw-bold">Total to Pay</span>
                <span class="fw-bold text-dark h4 mb-0">₦<?= number_format($cartTotal, 2) ?></span>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
