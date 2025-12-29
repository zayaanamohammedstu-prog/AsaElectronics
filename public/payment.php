<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$pageTitle = 'Payment - ' . SITE_NAME;

// Get payment details
$reference = $_GET['reference'] ?? '';
$amount = $_GET['amount'] ?? 0;
$email = $_GET['email'] ?? '';

if (empty($reference) || empty($amount) || empty($email)) {
    setFlash('error', 'Invalid payment request');
    redirect('/public/cart.php');
}

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="card" style="max-width: 600px; margin: 3rem auto; text-align: center; padding: 3rem;">
        <h2 style="margin-bottom: 2rem;">Complete Your Payment</h2>
        
        <div style="font-size: 3rem; font-weight: bold; color: var(--primary-color); margin-bottom: 1rem;">
            <?php echo formatPrice($amount / 100); ?>
        </div>
        
        <p style="color: var(--text-color); margin-bottom: 2rem;">
            You will be redirected to PayStack to complete your payment securely.
        </p>
        
        <button 
            id="payButton" 
            class="btn btn-primary" 
            style="font-size: 1.125rem; padding: 1rem 2rem;"
        >
            <i class="fas fa-lock"></i> Pay Now with PayStack
        </button>
        
        <p style="margin-top: 2rem; font-size: 0.875rem; color: var(--text-color);">
            Reference: <?php echo e($reference); ?>
        </p>
    </div>
</div>

<!-- PayStack Inline JavaScript -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('payButton').addEventListener('click', function() {
    var handler = PaystackPop.setup({
        key: '<?php echo PAYSTACK_PUBLIC_KEY; ?>',
        email: '<?php echo e($email); ?>',
        amount: <?php echo $amount; ?>,
        ref: '<?php echo e($reference); ?>',
        callback: function(response) {
            // Payment successful
            window.location.href = '/public/payment-callback.php?reference=' + response.reference;
        },
        onClose: function() {
            alert('Payment window closed. You can try again when ready.');
        }
    });
    handler.openIframe();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
