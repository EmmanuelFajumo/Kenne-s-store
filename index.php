<?php
// index.php
require_once 'header.php';

$categoryId = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;
$sort = $_GET['sort'] ?? null;

$productObj = new Product($db);
$categories = $productObj->getAllCategories();
$products = $productObj->getAll($categoryId, $search, $sort);
?>

<!-- Hero Banner (Only show if not searching or filtering by category) -->
<?php if (empty($categoryId) && empty($search)): ?>
<div class="hero-minimal">
    <div class="container py-5">
        <h1 class="display-4 font-weight-bold">Minimalist Design.<br><span style="color: var(--accent-color);">Premium Quality.</span></h1>
        <p class="lead">Discover our curated collection of technical apparel, minimalist leather goods, and refined essentials.</p>
        <a href="#shop-grid" class="btn btn-minimal btn-minimal-orange mt-3">Explore Collection</a>
    </div>
</div>
<?php endif; ?>

<div class="row pt-4" id="shop-grid">
    <!-- Filters Header -->
    <div class="col-12 mb-4">
        <form action="index.php" method="GET" class="row g-3 align-items-center">
            <!-- Search -->
            <div class="col-md-5">
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-minimal" placeholder="Search products..." value="<?= htmlspecialchars($search ?? '') ?>">
                    <button class="btn btn-minimal btn-minimal-dark" type="submit">Search</button>
                </div>
            </div>
            
            <!-- Category Filter Horizontal Menu -->
            <div class="col-md-4 d-flex justify-content-md-center justify-content-start overflow-auto py-2">
                <a href="index.php" class="badge bg-<?= empty($categoryId) ? 'dark' : 'light text-dark' ?> me-2 p-2 px-3 text-uppercase" style="border-radius: 0px; font-size: 0.75rem; letter-spacing: 0.05em;">All</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="index.php?category=<?= $cat['id'] ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $sort ? '&sort='.urlencode($sort) : '' ?>" class="badge bg-<?= $categoryId == $cat['id'] ? 'dark' : 'light text-dark' ?> me-2 p-2 px-3 text-uppercase" style="border-radius: 0px; font-size: 0.75rem; letter-spacing: 0.05em;"><?= htmlspecialchars($cat['name']) ?></a>
                <?php endforeach; ?>
            </div>
            
            <!-- Sort options -->
            <div class="col-md-3">
                <select name="sort" class="form-select form-control-minimal" onchange="this.form.submit()">
                    <option value="">Sort: Default</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
                <?php if ($categoryId): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($categoryId) ?>">
                <?php endif; ?>
                <?php if ($search): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Product Grid -->
    <div class="col-12">
        <div class="row">
            <?php if (empty($products)): ?>
                <div class="col-12 text-center py-5">
                    <h3 class="fw-light text-muted">No items match your search.</h3>
                    <a href="index.php" class="btn btn-minimal btn-minimal-dark mt-3">View All Products</a>
                </div>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                    <div class="col-lg-4 col-md-6 col-sm-6 d-flex align-items-stretch">
                        <div class="product-card w-100 shadow-sm border">
                            <a href="product.php?id=<?= $prod['id'] ?>" class="img-container d-block">
                                <img src="<?= $prod['image'] ?: 'https://via.placeholder.com/400x400.png?text=Product' ?>" alt="<?= htmlspecialchars($prod['name']) ?>">
                            </a>
                            <div class="card-body">
                                <div class="product-category"><?= htmlspecialchars($prod['category_name'] ?? 'Uncategorized') ?></div>
                                <a href="product.php?id=<?= $prod['id'] ?>" class="product-title text-decoration-none">
                                    <h5 class="text-dark hover-orange"><?= htmlspecialchars($prod['name']) ?></h5>
                                </a>
                                <p class="text-muted text-truncate" style="font-size: 0.85rem;"><?= htmlspecialchars($prod['description'] ?? '') ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span class="product-price fw-bold">$<?= number_format($prod['price'], 2) ?></span>
                                    <?php if ($prod['stock'] > 0): ?>
                                        <form action="Pocess_pages/cart_process.php" method="POST">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-minimal btn-minimal-dark py-1 px-3" style="font-size: 0.75rem;">Add</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-danger fw-bold" style="font-size: 0.8rem;">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
