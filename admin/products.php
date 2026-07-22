<?php
// admin/products.php
require_once 'admin_header.php';

$productObj = new Product($db);
$categories = $productObj->getAllCategories();
$products = $productObj->getAll();

// Check if we are in Edit mode
$editProduct = null;
$editId = $_GET['edit'] ?? null;
if ($editId) {
    $editProduct = $productObj->getById($editId);
}
?>

<div class="row">
    <div class="col-12 mb-4 d-flex justify-content-between align-items-center">
        <h2 class="fw-bold text-uppercase m-0" style="letter-spacing: -0.01em;">Manage Products</h2>
    </div>

    <!-- Product list (Left Column) -->
    <div class="col-lg-8 mb-4">
        <div class="card border rounded-0 bg-white shadow-sm p-4">
            <h4 class="text-uppercase mb-3" style="font-size: 1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Products Inventory</h4>
            
            <div class="table-responsive">
                <table class="table table-minimal mb-0">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No products in inventory.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $prod): ?>
                                <tr>
                                    <td>
                                        <img src="../<?= $prod['image'] ?: 'https://via.placeholder.com/40px' ?>" alt="" class="img-thumbnail rounded-0" style="width: 40px; height: 40px; object-fit: cover;">
                                    </td>
                                    <td class="fw-semibold" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= htmlspecialchars($prod['name']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($prod['category_name'] ?? 'None') ?></td>
                                    <td class="fw-bold">₦<?= number_format($prod['price'], 2) ?></td>
                                    <td>
                                        <?php if ($prod['stock'] <= 5): ?>
                                            <span class="text-danger fw-bold"><?= $prod['stock'] ?> (Low)</span>
                                        <?php else: ?>
                                            <span><?= $prod['stock'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="products.php?edit=<?= $prod['id'] ?>" class="btn btn-minimal btn-minimal-outline py-1 px-2 me-1" style="font-size: 0.75rem;">Edit</a>
                                        <a href="../Pocess_pages/admin_product_process.php?action=delete&id=<?= $prod['id'] ?>" class="btn btn-minimal btn-minimal-outline text-danger border-danger py-1 px-2" style="font-size: 0.75rem;" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit form (Right Column) -->
    <div class="col-lg-4">
        <div class="card border rounded-0 bg-white shadow-sm p-4">
            <?php if ($editProduct): ?>
                <h4 class="text-uppercase mb-3" style="font-size: 1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--accent-color); padding-bottom: 10px;">Edit Product</h4>
                <form action="../Pocess_pages/admin_product_process.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
            <?php else: ?>
                <h4 class="text-uppercase mb-3" style="font-size: 1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Add New Product</h4>
                <form action="../Pocess_pages/admin_product_process.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
            <?php endif; ?>

                <!-- Product Name -->
                <div class="mb-3">
                    <label for="name" class="form-label-minimal">Product Name</label>
                    <input type="text" name="name" id="name" class="form-control form-control-minimal" value="<?= $editProduct ? htmlspecialchars($editProduct['name']) : '' ?>" required>
                </div>

                <!-- Category -->
                <div class="mb-3">
                    <label for="category_id" class="form-label-minimal">Category</label>
                    <select name="category_id" id="category_id" class="form-select form-control-minimal">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($editProduct && $editProduct['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Price -->
                <div class="mb-3">
                    <label for="price" class="form-label-minimal">Price (₦)</label>
                    <input type="number" name="price" id="price" step="0.01" min="0.01" class="form-control form-control-minimal" value="<?= $editProduct ? htmlspecialchars($editProduct['price']) : '' ?>" required>
                </div>

                <!-- Stock -->
                <div class="mb-3">
                    <label for="stock" class="form-label-minimal">Stock Quantity</label>
                    <input type="number" name="stock" id="stock" min="0" class="form-control form-control-minimal" value="<?= $editProduct ? htmlspecialchars($editProduct['stock']) : '0' ?>" required>
                </div>

                <!-- Image -->
                <div class="mb-3">
                    <label for="image" class="form-label-minimal">Product Image</label>
                    <input type="file" name="image" id="image" class="form-control form-control-minimal" accept="image/*">
                    <?php if ($editProduct && $editProduct['image']): ?>
                        <div class="mt-2">
                            <span class="text-muted d-block" style="font-size: 0.8rem;">Current Image:</span>
                            <img src="../<?= $editProduct['image'] ?>" alt="" class="img-thumbnail mt-1" style="max-height: 80px;">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="form-label-minimal">Description</label>
                    <textarea name="description" id="description" rows="4" class="form-control form-control-minimal"><?= $editProduct ? htmlspecialchars($editProduct['description']) : '' ?></textarea>
                </div>

                <!-- Submit buttons -->
                <button type="submit" class="btn btn-minimal btn-minimal-orange w-100 py-3 mb-2">
                    <?= $editProduct ? 'Save Changes' : 'Create Product' ?>
                </button>
                
                <?php if ($editProduct): ?>
                    <a href="products.php" class="btn btn-minimal btn-minimal-outline w-100 py-2">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

</div> <!-- End col-md-10 -->
</div> <!-- End row -->
</div> <!-- End container-fluid -->
<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
