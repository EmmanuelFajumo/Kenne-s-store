<?php
// cart.php
require_once 'header.php';

$userId = $userObj->isLoggedIn() ? $_SESSION['user_id'] : null;
$cartItems = $cartObj->getItems($userId);
$cartTotal = $cartObj->getTotal($userId);
?>

<div class="row pt-4">
    <div class="col-12 mb-4">
        <h1 class="fw-bold text-uppercase" style="letter-spacing: -0.02em;">Shopping Cart</h1>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="col-12 text-center py-5">
            <h3 class="fw-light text-muted mb-4">Your shopping cart is empty.</h3>
            <a href="index.php" class="btn btn-minimal btn-minimal-dark">Go Shopping</a>
        </div>
    <?php else: ?>
        <!-- Cart Items Table -->
        <div class="col-lg-8 mb-4">
            <div class="table-responsive bg-white border">
                <table class="table table-minimal w-100 mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-thumbnail rounded-0 me-3" style="width: 60px; height: 60px; object-fit: cover; background-color: var(--bg-color);">
                                        <div>
                                            <a href="product.php?id=<?= $item['product_id'] ?>" class="fw-bold text-dark hover-orange d-block"><?= htmlspecialchars($item['name']) ?></a>
                                            <small class="text-muted">Stock: <?= $item['stock'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>₦<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <form action="Pocess_pages/cart_process.php" method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="form-control form-control-minimal py-1 px-2 me-2" style="width: 70px; height: 35px;">
                                        <button type="submit" class="btn btn-minimal btn-minimal-dark py-1 px-2" style="font-size: 0.75rem; height: 35px;">Update</button>
                                    </form>
                                </td>
                                <td>₦<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <a href="Pocess_pages/cart_process.php?action=remove&product_id=<?= $item['product_id'] ?>" class="text-danger" title="Remove Item" style="font-size: 1.25rem;">&times;</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between mt-3">
                <a href="index.php" class="btn btn-minimal btn-minimal-outline">Continue Shopping</a>
                <a href="Pocess_pages/cart_process.php?action=clear" class="btn btn-minimal btn-minimal-outline text-danger border-danger">Clear Cart</a>
            </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="col-lg-4">
            <div class="card border rounded-0 p-4 bg-white shadow-sm">
                <h4 class="text-uppercase mb-4" style="font-size: 1.1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Order Summary</h4>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-bold">₦<?= number_format($cartTotal, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Shipping</span>
                    <span class="text-success fw-semibold">FREE</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold">Total</span>
                    <span class="fw-bold text-dark h4 mb-0">₦<?= number_format($cartTotal, 2) ?></span>
                </div>
                
                <?php if ($userObj->isLoggedIn()): ?>
                    <a href="checkout.php" class="btn btn-minimal btn-minimal-orange w-100 py-3">Proceed to Checkout</a>
                <?php else: ?>
                    <a href="login.php?redirect=checkout.php" class="btn btn-minimal btn-minimal-dark w-100 py-3">Login to Checkout</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>
