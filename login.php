<?php
// login.php
require_once 'header.php';

if ($userObj->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$redirect = $_GET['redirect'] ?? '';
?>

<div class="row justify-content-center pt-5">
    <div class="col-md-5 col-sm-8 mb-4">
        <div class="card border rounded-0 p-4 bg-white shadow-sm">
            <div class="text-center mb-4">
                <h3 class="text-uppercase fw-bold" style="letter-spacing: 0.05em;">Log In</h3>
                <p class="text-muted">Sign in to your KeneStore account</p>
            </div>
            
            <form action="process_pages/login_process.php" method="POST">
                <?php if (!empty($redirect)): ?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                <?php endif; ?>
                
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label-minimal">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control form-control-minimal" placeholder="e.g. customer@kenestore.com" required value="<?= isset($_SESSION['reg_email']) ? htmlspecialchars($_SESSION['reg_email']) : '' ?>">
                    <?php unset($_SESSION['reg_email']); ?>
                </div>
                
                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label-minimal">Password</label>
                    <input type="password" name="password" id="password" class="form-control form-control-minimal" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn btn-minimal btn-minimal-orange w-100 py-3 mb-3">Sign In</button>
                
                <div class="text-center">
                    <span class="text-muted" style="font-size: 0.85rem;">Don't have an account? </span>
                    <a href="register.php<?= !empty($redirect) ? '?redirect='.urlencode($redirect) : '' ?>" class="fw-semibold" style="font-size: 0.85rem;">Sign Up</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
