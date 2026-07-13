<?php
// admin/dashboard.php
require_once 'admin_header.php';

$orderObj = new Order($db);
$summary = $orderObj->getSalesSummary();

// Fetch low stock items
$lowStockStmt = $db->query("SELECT id, name, stock FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 10");
$lowStock = $lowStockStmt->fetchAll();

// Fetch recent orders
$recentOrdersStmt = $db->query("
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$recentOrders = $recentOrdersStmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-uppercase m-0" style="letter-spacing: -0.01em;">Dashboard Overview</h2>
    <span class="text-muted"><?= date('F d, Y') ?></span>
</div>

<!-- KPI Summary Cards -->
<div class="row mb-4">
    <!-- Total Income -->
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card admin-card-metric border">
            <div class="metric-title">Total Revenue</div>
            <div class="metric-value" style="color: var(--accent-color);">$<?= number_format($summary['total_sales'], 2) ?></div>
        </div>
    </div>
    <!-- Total Orders -->
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card admin-card-metric border">
            <div class="metric-title">Orders Placed</div>
            <div class="metric-value"><?= $summary['total_orders'] ?></div>
        </div>
    </div>
    <!-- Pending Orders -->
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card admin-card-metric border" style="border-left-color: var(--grey-muted);">
            <div class="metric-title">Pending Orders</div>
            <div class="metric-value"><?= $summary['pending_orders'] ?></div>
        </div>
    </div>
    <!-- Paid Orders -->
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card admin-card-metric border" style="border-left-color: #28a745;">
            <div class="metric-title">Paid Orders</div>
            <div class="metric-value"><?= $summary['paid_orders'] ?></div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders Table -->
    <div class="col-lg-8 mb-4">
        <div class="card border rounded-0 bg-white shadow-sm p-4 h-100">
            <h4 class="text-uppercase mb-3" style="font-size: 1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Recent Orders</h4>
            
            <div class="table-responsive">
                <table class="table table-minimal mb-0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No recent orders.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $ord): 
                                $badgeClass = 'badge-pending';
                                if ($ord['status'] === 'paid') $badgeClass = 'badge-paid';
                                if ($ord['status'] === 'shipped') $badgeClass = 'badge-shipped';
                                if ($ord['status'] === 'completed') $badgeClass = 'badge-completed';
                                if ($ord['status'] === 'cancelled') $badgeClass = 'badge-cancelled';
                            ?>
                                <tr>
                                    <td class="fw-bold">#KS-<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= htmlspecialchars($ord['customer_name']) ?></td>
                                    <td class="fw-semibold">$<?= number_format($ord['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge-minimal <?= $badgeClass ?>"><?= htmlspecialchars($ord['status']) ?></span>
                                    </td>
                                    <td>
                                        <a href="orders.php?view=<?= $ord['id'] ?>" class="btn btn-minimal btn-minimal-dark py-1 px-2" style="font-size: 0.75rem;">Manage</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Inventory Low Stock Alerts -->
    <div class="col-lg-4 mb-4">
        <div class="card border rounded-0 bg-white shadow-sm p-4 h-100">
            <h4 class="text-uppercase mb-3" style="font-size: 1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Inventory Alerts</h4>
            
            <?php if (empty($lowStock)): ?>
                <div class="alert alert-success border-0 rounded-0" role="alert" style="background-color: #D4EDDA; color: #155724; font-size: 0.9rem;">
                    All items are well stocked.
                </div>
            <?php else: ?>
                <div class="list-group list-group-minimal">
                    <?php foreach ($lowStock as $prod): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-truncate me-2" style="max-width: 180px; font-size: 0.9rem;"><?= htmlspecialchars($prod['name']) ?></span>
                            <?php if ($prod['stock'] == 0): ?>
                                <span class="badge bg-danger text-white rounded-0" style="font-size: 0.75rem;">Out of Stock</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark rounded-0" style="font-size: 0.75rem;"><?= $prod['stock'] ?> Left</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</div> <!-- End col-md-10 -->
</div> <!-- End row -->
</div> <!-- End container-fluid -->
<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
