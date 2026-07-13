<?php
// admin/users.php
require_once 'admin_header.php';

$usersList = $userObj->getAllUsers();
?>

<div class="row">
    <div class="col-12 mb-4">
        <h2 class="fw-bold text-uppercase m-0" style="letter-spacing: -0.01em;">Manage Users</h2>
    </div>

    <!-- Users table -->
    <div class="col-lg-12">
        <div class="card border rounded-0 bg-white shadow-sm p-4">
            <h4 class="text-uppercase mb-3" style="font-size: 1rem; letter-spacing: 0.05em; border-bottom: 2px solid var(--fg-color); padding-bottom: 10px;">Registered Accounts</h4>
            
            <div class="table-responsive">
                <table class="table table-minimal mb-0">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Email Address</th>
                            <th>Role Access</th>
                            <th>Date Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usersList)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No accounts registered in system.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usersList as $user): ?>
                                <tr>
                                    <td class="fw-bold">#USR-<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-dark rounded-0 px-2 py-1" style="font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Administrator</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-0 px-2 py-1" style="font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Customer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d M Y, H:i', strtotime($user['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div> <!-- End col-md-10 -->
</div> <!-- End row -->
</div> <!-- End container-fluid -->
<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
