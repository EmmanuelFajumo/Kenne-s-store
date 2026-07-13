<?php
// admin/categories.php
require_once 'admin_header.php';

$productObj = new Product($db);
$categories = $productObj->getAllCategories();
?>

<div class="row">
    <div class="col-12 mb-4">
        <h2 class="fw-bold text-uppercase m-0" style="letter-spacing: -0.01em;">Manage Categories</h2>
    </div>

    <!-- Category list (Left Column) -->
    <div class="col-lg-8 mb-4">
        <div class="card border rounded-0 bg-white shadow-sm p-4">
            <h4 class="text-uppercase mb-3" style="font-size: 1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Product Categories</h4>
            
            <div class="table-responsive">
                <table class="table table-minimal mb-0">
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Name</th>
                            <th>Date Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No categories created yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="fw-bold">#CAT-<?= str_pad($cat['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($cat['name']) ?></td>
                                    <td><?= date('d M Y, H:i', strtotime($cat['created_at'])) ?></td>
                                    <td class="text-end">
                                        <a href="../Pocess_pages/admin_category_process.php?action=delete&id=<?= $cat['id'] ?>" class="btn btn-minimal btn-minimal-outline text-danger border-danger py-1 px-2" style="font-size: 0.75rem;" onclick="return confirm('Are you sure you want to delete this category? All associated products will have their category cleared.');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Category Form (Right Column) -->
    <div class="col-lg-4">
        <div class="card border rounded-0 bg-white shadow-sm p-4">
            <h4 class="text-uppercase mb-3" style="font-size: 1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Add Category</h4>
            
            <form action="../Pocess_pages/admin_category_process.php" method="POST">
                <input type="hidden" name="action" value="add">
                
                <!-- Category Name -->
                <div class="mb-4">
                    <label for="name" class="form-label-minimal">Category Name</label>
                    <input type="text" name="name" id="name" class="form-control form-control-minimal" placeholder="e.g. Outerwear" required>
                </div>

                <button type="submit" class="btn btn-minimal btn-minimal-orange w-100 py-3">Create Category</button>
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
