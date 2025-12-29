<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$pageTitle = 'Checkout - ' . SITE_NAME;

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    setFlash('error', 'Your cart is empty');
    redirect('/public/cart.php');
}

// Get cart total
$cartTotal = getCartTotal($pdo);
$user = getCurrentUser($pdo);

// Get user addresses
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt->execute([$_SESSION['user_id']]);
$addresses = $stmt->fetchAll();

// Handle new address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_address') {
    verifyCsrf();
    
    $stmt = $pdo->prepare("
        INSERT INTO addresses (user_id, address_line1, address_line2, city, state, country, postal_code, is_default) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $isDefault = empty($addresses) ? 1 : 0;
    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['address_line1'],
        $_POST['address_line2'] ?? '',
        $_POST['city'],
        $_POST['state'] ?? '',
        $_POST['country'],
        $_POST['postal_code'] ?? '',
        $isDefault
    ]);
    
    setFlash('success', 'Address added successfully');
    redirect('/public/checkout.php');
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    verifyCsrf();
    
    $addressId = (int)($_POST['address_id'] ?? 0);
    
    if (!$addressId) {
        setFlash('error', 'Please select a delivery address');
    } else {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, address_id, total_amount, status, payment_status) 
                VALUES (?, ?, ?, 'pending', 'pending')
            ");
            $stmt->execute([$_SESSION['user_id'], $addressId, $cartTotal]);
            $orderId = $pdo->lastInsertId();
            
            // Add order items
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($_SESSION['cart'] as $item) {
                $product = getProduct($pdo, $item['product_id']);
                if ($product) {
                    $stmt->execute([
                        $orderId,
                        $item['product_id'],
                        $item['quantity'],
                        $product['price']
                    ]);
                    
                    // Update stock
                    $updateStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                    $updateStmt->execute([$item['quantity'], $item['product_id']]);
                }
            }
            
            $pdo->commit();
            
            // Initialize PayStack payment
            $amount = $cartTotal * 100; // Convert to kobo
            $email = $user['email'];
            $reference = 'ASA-' . $orderId . '-' . time();
            
            // Update order with payment reference
            $stmt = $pdo->prepare("UPDATE orders SET payment_reference = ? WHERE id = ?");
            $stmt->execute([$reference, $orderId]);
            
            // Clear cart
            clearCart();
            
            // Redirect to PayStack payment page
            $_SESSION['pending_order_id'] = $orderId;
            redirect('/public/payment.php?reference=' . $reference . '&amount=' . $amount . '&email=' . urlencode($email));
            
        } catch (Exception $e) {
            $pdo->rollBack();
            setFlash('error', 'Order placement failed. Please try again.');
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1 style="margin: 2rem 0; color: var(--dark-color);">Checkout</h1>
    
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
        <!-- Checkout Form -->
        <div>
            <!-- Delivery Address -->
            <div class="card">
                <h3 style="margin-bottom: 1rem;">Delivery Address</h3>
                
                <?php if (empty($addresses)): ?>
                    <p style="color: var(--text-color); margin-bottom: 1rem;">You don't have any saved addresses. Please add one below.</p>
                <?php else: ?>
                    <form method="POST" action="" id="checkoutForm">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="place_order">
                        
                        <?php foreach ($addresses as $address): ?>
                            <div style="border: 2px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; cursor: pointer; transition: border-color 0.3s;" class="address-option" onclick="document.getElementById('address_<?php echo $address['id']; ?>').checked = true; this.parentElement.querySelectorAll('.address-option').forEach(el => el.style.borderColor = 'var(--border-color)'); this.style.borderColor = 'var(--primary-color)';">
                                <label style="cursor: pointer; display: flex; align-items: start; gap: 1rem;">
                                    <input 
                                        type="radio" 
                                        name="address_id" 
                                        value="<?php echo $address['id']; ?>" 
                                        id="address_<?php echo $address['id']; ?>"
                                        <?php echo $address['is_default'] ? 'checked' : ''; ?>
                                    >
                                    <div>
                                        <strong><?php echo e($address['address_line1']); ?></strong>
                                        <?php if ($address['address_line2']): ?>
                                            <br><?php echo e($address['address_line2']); ?>
                                        <?php endif; ?>
                                        <br><?php echo e($address['city']); ?>, <?php echo e($address['state']); ?>
                                        <br><?php echo e($address['country']); ?> <?php echo e($address['postal_code']); ?>
                                        <?php if ($address['is_default']): ?>
                                            <span class="badge badge-info">Default</span>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </form>
                <?php endif; ?>
                
                <!-- Add New Address Form -->
                <details style="margin-top: 1rem;">
                    <summary style="cursor: pointer; font-weight: 600; color: var(--primary-color);">
                        <i class="fas fa-plus"></i> Add New Address
                    </summary>
                    
                    <form method="POST" action="" style="margin-top: 1rem;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="add_address">
                        
                        <div class="form-group">
                            <label>Address Line 1 *</label>
                            <input type="text" name="address_line1" required placeholder="Street address">
                        </div>
                        
                        <div class="form-group">
                            <label>Address Line 2</label>
                            <input type="text" name="address_line2" placeholder="Apartment, suite, etc.">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label>City *</label>
                                <input type="text" name="city" required>
                            </div>
                            
                            <div class="form-group">
                                <label>State</label>
                                <input type="text" name="state">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label>Country *</label>
                                <input type="text" name="country" required value="Nigeria">
                            </div>
                            
                            <div class="form-group">
                                <label>Postal Code</label>
                                <input type="text" name="postal_code">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Address
                        </button>
                    </form>
                </details>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div>
            <div class="cart-summary">
                <h3>Order Summary</h3>
                
                <div style="margin: 1rem 0;">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <?php $product = getProduct($pdo, $item['product_id']); ?>
                        <?php if ($product): ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem;">
                                <span><?php echo e($product['name']); ?> x<?php echo $item['quantity']; ?></span>
                                <span><?php echo formatPrice($product['price'] * $item['quantity']); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($cartTotal); ?></span>
                </div>
                
                <div class="cart-summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                
                <div class="cart-summary-row" style="border: none; padding-top: 1rem;">
                    <span>Total</span>
                    <span class="cart-summary-total"><?php echo formatPrice($cartTotal); ?></span>
                </div>
                
                <?php if (!empty($addresses)): ?>
                    <button 
                        type="submit" 
                        form="checkoutForm" 
                        class="btn btn-primary" 
                        style="width: 100%; margin-top: 1rem;"
                    >
                        <i class="fas fa-lock"></i> Pay with PayStack
                    </button>
                <?php endif; ?>
                
                <div style="margin-top: 1rem; padding: 1rem; background: var(--light-color); border-radius: 8px; text-align: center;">
                    <i class="fas fa-shield-alt" style="color: var(--secondary-color);"></i>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem; color: var(--text-color);">
                        Secure payment powered by PayStack
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
