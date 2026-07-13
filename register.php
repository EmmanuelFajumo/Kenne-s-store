<?php
// register.php
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
                <h3 class="text-uppercase fw-bold" style="letter-spacing: 0.05em;">Sign Up</h3>
                <p class="text-muted">Create your KeneStore customer account</p>
            </div>
            
            <form action="Pocess_pages/register_process.php" method="POST">
                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label-minimal">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control form-control-minimal" placeholder="e.g. Kene Customer" required value="<?= isset($_SESSION['reg_name']) ? htmlspecialchars($_SESSION['reg_name']) : '' ?>">
                    <?php unset($_SESSION['reg_name']); ?>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label-minimal">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control form-control-minimal" placeholder="e.g. customer@kenestore.com" required>
                </div>
                
                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label-minimal">Password</label>
                    <input type="password" name="password" id="password" class="form-control form-control-minimal" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn btn-minimal btn-minimal-orange w-100 py-3 mb-3">Create Account</button>
                
                <div class="text-center">
                    <span class="text-muted" style="font-size: 0.85rem;">Already have an account? </span>
                    <a href="login.php<?= !empty($redirect) ? '?redirect='.urlencode($redirect) : '' ?>" class="fw-semibold" style="font-size: 0.85rem;">Log In</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
