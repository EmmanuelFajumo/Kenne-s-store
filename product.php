<?php
// product.php
require_once 'header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit();
}

$productObj = new Product($db);
$product = $productObj->getById($id);

if (!$product) {
    $_SESSION['error'] = "Product not found.";
    header('Location: index.php');
    exit();
}
?>

<div class="row pt-4">
    <!-- Product Image Column -->
    <div class="col-md-6 mb-4">
        <div class="product-detail-image-container shadow-sm">
            <img src="<?= $product['image'] ?: 'https://via.placeholder.com/600x600.png?text=Product+Image' ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid">
        </div>
    </div>
    
    <!-- Product Details Column -->
    <div class="col-md-6">
        <div class="ps-md-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">
                    <li class="breadcrumb-item"><a href="index.php">Shop</a></li>
                    <li class="breadcrumb-item"><a href="index.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
                </ol>
            </nav>
            
            <h1 class="display-5 fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h1>
            <div class="mb-4">
                <span class="price-tag">$<?= number_format($product['price'], 2) ?></span>
            </div>
            
            <div class="mb-4">
                <h6 class="form-label-minimal">Stock Status</h6>
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success" style="border-radius: 0px; font-size: 0.8rem;"><?= $product['stock'] ?> Units Available</span>
                <?php else: ?>
                    <span class="badge bg-danger" style="border-radius: 0px; font-size: 0.8rem;">Out of Stock</span>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <h6 class="form-label-minimal">Description</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?></p>
            </div>
            
            <?php if ($product['stock'] > 0): ?>
                <form action="Pocess_pages/cart_process.php" method="POST" class="row g-3 align-items-center">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="col-auto">
                        <label for="quantity" class="form-label-minimal">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control form-control-minimal" value="1" min="1" max="<?= $product['stock'] ?>" style="width: 80px;">
                    </div>
                    
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-minimal btn-minimal-dark py-3 px-5">Add to Cart</button>
                    </div>
                </form>
            <?php else: ?>
                <button class="btn btn-minimal btn-minimal-dark py-3 px-5" disabled>Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
