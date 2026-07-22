<?php
// payment.php
require_once 'header.php';

if (!$userObj->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['shipping_details']) || !isset($_SESSION['checkout_total'])) {
    $_SESSION['error'] = "No checkout session active.";
    header('Location: cart.php');
    exit();
}

$total = $_SESSION['checkout_total'];
?>

<!-- Payment Processing Overlay -->
<div id="payment-loader" style="display: none;">
    <div class="text-center">
        <div class="spinner-orange mb-4 mx-auto"></div>
        <h4 class="text-uppercase fw-bold mb-2" id="loader-title" style="letter-spacing: 0.05em; font-size: 1rem;">Initiating Transaction</h4>
        <p class="text-muted" id="loader-status">Preparing secure tunnel...</p>
    </div>
</div>

<style>
#payment-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(250, 249, 246, 0.97);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}
.spinner-orange {
    border: 3px solid var(--grey-border);
    border-top: 3px solid var(--accent-color);
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<div class="row justify-content-center pt-4">
    <div class="col-md-6 mb-4">
        <div class="card border rounded-0 p-4 bg-white shadow-sm">
            <div class="text-center mb-4">
                <h4 class="text-uppercase font-weight-bold" style="letter-spacing: 0.05em;">Payment Gateway</h4>
                <p class="text-muted">Pay securely using our credit card simulator</p>
                <div class="alert alert-dark rounded-0 border-0 py-3 mb-0" style="background-color: #111111; color: #FFFFFF;">
                    <span class="text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.05em; color: var(--grey-muted);">Amount to Charge:</span>
                    <h3 class="mb-0 fw-bold mt-1" style="color: var(--accent-color);">₦<?= number_format($total, 2) ?></h3>
                </div>
            </div>
            
            <form id="payment-form" action="Process_pages/payment_process.php" method="POST">
                <!-- Cardholder Name -->
                <div class="mb-3">
                    <label for="card_name" class="form-label-minimal">Cardholder Name</label>
                    <input type="text" name="card_name" id="card_name" class="form-control form-control-minimal" placeholder="e.g. John Doe" required>
                </div>
                
                <!-- Card Number -->
                <div class="mb-3">
                    <label for="card_number" class="form-label-minimal">Card Number</label>
                    <input type="text" name="card_number" id="card_number" class="form-control form-control-minimal" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" required>
                </div>
                
                <div class="row">
                    <!-- Expiry -->
                    <div class="col-md-6 mb-3">
                        <label for="expiry" class="form-label-minimal">Expiry Date</label>
                        <input type="text" name="expiry" id="expiry" class="form-control form-control-minimal" placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <!-- CVV -->
                    <div class="col-md-6 mb-3">
                        <label for="cvv" class="form-label-minimal">CVV Code</label>
                        <input type="password" name="cvv" id="cvv" class="form-control form-control-minimal" placeholder="***" maxlength="3" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-minimal btn-minimal-orange w-100 py-3 mt-3">Authorize Payment</button>
                <a href="checkout.php" class="btn btn-minimal btn-minimal-outline w-100 py-2 mt-2">Back to Checkout</a>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const loader = document.getElementById('payment-loader');
    const title = document.getElementById('loader-title');
    const status = document.getElementById('loader-status');
    const form = this;
    
    // Display overlay
    loader.style.display = 'flex';
    
    // Simulation steps
    setTimeout(function() {
        title.innerText = 'Validating Card Details';
        status.innerText = 'Verifying structural integrity...';
        
        setTimeout(function() {
            title.innerText = 'Secure Handshake';
            status.innerText = 'Routing via encrypted node...';
            
            setTimeout(function() {
                title.innerText = 'Processing Amount';
                status.innerText = 'Transferring funds from holder...';
                
                setTimeout(function() {
                    // Submit the actual form
                    form.submit();
                }, 1200);
            }, 1200);
        }, 1200);
    }, 1200);
});

// Format card input visually (adding spaces)
document.getElementById('card_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formatted = '';
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formatted += ' ';
        }
        formatted += value[i];
    }
    e.target.value = formatted;
});

// Format expiry input visually (adding slash)
document.getElementById('expiry').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    if (value.length > 2) {
        e.target.value = value.substr(0, 2) + '/' + value.substr(2, 2);
    } else {
        e.target.value = value;
    }
});
</script>

<?php
require_once 'footer.php';
?>
