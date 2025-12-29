<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Shopping Cart - ' . SITE_NAME;

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);
    
    if ($action === 'add' && $productId) {
        $product = getProduct($pdo, $productId);
        if ($product) {
            $quantity = max(1, (int)($_POST['quantity'] ?? 1));
            addToCart($product['id'], $quantity, $product['price'], $product['name']);
            setFlash('success', 'Product added to cart');
        }
    } elseif ($action === 'update' && $productId) {
        $quantity = max(0, (int)($_POST['quantity'] ?? 0));
        updateCartQuantity($productId, $quantity);
        setFlash('success', 'Cart updated');
    } elseif ($action === 'remove' && $productId) {
        removeFromCart($productId);
        setFlash('success', 'Product removed from cart');
    } elseif ($action === 'clear') {
        clearCart();
        setFlash('success', 'Cart cleared');
    }
    
    redirect('/public/cart.php');
}

// Get cart items with full details
$cartItems = [];
$cartTotal = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $product = getProduct($pdo, $item['product_id']);
        if ($product) {
            $cartItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'subtotal' => $product['price'] * $item['quantity']
            ];
            $cartTotal += $product['price'] * $item['quantity'];
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1 style="margin: 2rem 0; color: var(--dark-color);">Shopping Cart</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--border-color); margin-bottom: 1rem;"></i>
            <h3>Your Cart is Empty</h3>
            <p>Start adding some products to your cart!</p>
            <a href="/public/products.php" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
            <!-- Cart Items -->
            <div>
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <img 
                            src="<?php echo $item['product']['image_url'] ? '/uploads/' . e(basename($item['product']['image_url'])) : '/public/assets/images/placeholder.png'; ?>" 
                            alt="<?php echo e($item['product']['name']); ?>" 
                            class="cart-item-image"
                        >
                        
                        <div class="cart-item-info">
                            <h3 class="cart-item-name">
                                <a href="/public/product-detail.php?id=<?php echo $item['product']['id']; ?>" style="color: var(--dark-color); text-decoration: none;">
                                    <?php echo e($item['product']['name']); ?>
                                </a>
                            </h3>
                            <div class="cart-item-price"><?php echo formatPrice($item['product']['price']); ?> each</div>
                            
                            <div style="margin-top: 1rem;">
                                <form method="POST" action="" style="display: inline-block;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <input type="hidden" name="action" value="update">
                                    <div class="cart-item-quantity">
                                        <label>Quantity:</label>
                                        <input 
                                            type="number" 
                                            name="quantity" 
                                            value="<?php echo $item['quantity']; ?>" 
                                            min="1" 
                                            max="<?php echo $item['product']['stock_quantity']; ?>"
                                            onchange="this.form.submit()"
                                        >
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div style="text-align: right;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color); margin-bottom: 1rem;">
                                <?php echo formatPrice($item['subtotal']); ?>
                            </div>
                            <form method="POST" action="">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                <input type="hidden" name="action" value="remove">
                                <button 
                                    type="submit" 
                                    class="btn btn-danger" 
                                    data-confirm="Are you sure you want to remove this item?"
                                >
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                    <form method="POST" action="">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="clear">
                        <button 
                            type="submit" 
                            class="btn btn-outline" 
                            data-confirm="Are you sure you want to clear your cart?"
                        >
                            <i class="fas fa-trash"></i> Clear Cart
                        </button>
                    </form>
                    
                    <a href="/public/products.php" class="btn btn-outline">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="cart-summary">
                <h3>Order Summary</h3>
                
                <div class="cart-summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($cartTotal); ?></span>
                </div>
                
                <div class="cart-summary-row">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                
                <div class="cart-summary-row" style="border: none; padding-top: 1rem;">
                    <span>Total</span>
                    <span class="cart-summary-total"><?php echo formatPrice($cartTotal); ?></span>
                </div>
                
                <a href="/public/checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 1rem; text-align: center;">
                    <i class="fas fa-lock"></i> Proceed to Checkout
                </a>
                
                <div style="margin-top: 1rem; padding: 1rem; background: var(--light-color); border-radius: 8px; text-align: center;">
                    <i class="fas fa-shield-alt" style="color: var(--secondary-color);"></i>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem; color: var(--text-color);">
                        Secure checkout powered by PayStack
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
