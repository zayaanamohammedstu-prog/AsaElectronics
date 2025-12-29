<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$productId = $_GET['id'] ?? 0;

// Get product details
$product = getProduct($pdo, $productId);

if (!$product) {
    setFlash('error', 'Product not found');
    redirect('/public/products.php');
}

$pageTitle = $product['name'] . ' - ' . SITE_NAME;

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    verifyCsrf();
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    
    if ($quantity > $product['stock_quantity']) {
        setFlash('error', 'Not enough stock available');
    } else {
        addToCart($product['id'], $quantity, $product['price'], $product['name']);
        setFlash('success', 'Product added to cart');
        redirect('/public/cart.php');
    }
}

// Get related products (same category)
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div style="padding: 1rem 0;">
        <a href="/public/products.php" style="color: var(--primary-color); text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin: 2rem 0;">
        <!-- Product Image -->
        <div>
            <img 
                src="<?php echo $product['image_url'] ? '/uploads/' . e(basename($product['image_url'])) : '/public/assets/images/placeholder.png'; ?>" 
                alt="<?php echo e($product['name']); ?>" 
                style="width: 100%; border-radius: 12px; box-shadow: var(--shadow-lg);"
            >
        </div>
        
        <!-- Product Info -->
        <div>
            <?php if ($product['category_name']): ?>
                <div style="color: var(--primary-color); font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;">
                    <?php echo e($product['category_name']); ?>
                </div>
            <?php endif; ?>
            
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--dark-color);">
                <?php echo e($product['name']); ?>
            </h1>
            
            <div style="font-size: 2rem; font-weight: bold; color: var(--primary-color); margin-bottom: 1.5rem;">
                <?php echo formatPrice($product['price']); ?>
            </div>
            
            <?php if ($product['sku']): ?>
                <p style="color: var(--text-color); margin-bottom: 0.5rem;">
                    <strong>SKU:</strong> <?php echo e($product['sku']); ?>
                </p>
            <?php endif; ?>
            
            <p style="color: var(--text-color); margin-bottom: 1.5rem;">
                <strong>Availability:</strong> 
                <?php if ($product['stock_quantity'] > 0): ?>
                    <span style="color: var(--secondary-color);">
                        <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock_quantity']; ?> available)
                    </span>
                <?php else: ?>
                    <span style="color: var(--danger-color);">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </span>
                <?php endif; ?>
            </p>
            
            <div style="border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.5rem 0; margin: 1.5rem 0;">
                <h3 style="margin-bottom: 1rem;">Description</h3>
                <p style="line-height: 1.8; color: var(--text-color);">
                    <?php echo nl2br(e($product['description'])); ?>
                </p>
            </div>
            
            <?php if ($product['stock_quantity'] > 0): ?>
                <form method="POST" action="" style="margin-top: 2rem;">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="add_to_cart">
                    
                    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                        <label style="font-weight: 600;">Quantity:</label>
                        <input 
                            type="number" 
                            name="quantity" 
                            value="1" 
                            min="1" 
                            max="<?php echo $product['stock_quantity']; ?>" 
                            style="width: 100px; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px;"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="font-size: 1.125rem; padding: 1rem 2rem;">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-outline" disabled style="font-size: 1.125rem; padding: 1rem 2rem;">
                    <i class="fas fa-times"></i> Out of Stock
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (count($relatedProducts) > 0): ?>
        <section style="margin-top: 4rem;">
            <h2 style="margin-bottom: 2rem; color: var(--dark-color);">Related Products</h2>
            <div class="products-grid">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="product-card">
                        <img 
                            src="<?php echo $relatedProduct['image_url'] ? '/uploads/' . e(basename($relatedProduct['image_url'])) : '/public/assets/images/placeholder.png'; ?>" 
                            alt="<?php echo e($relatedProduct['name']); ?>" 
                            class="product-image"
                        >
                        <div class="product-info">
                            <div class="product-category"><?php echo e($relatedProduct['category_name']); ?></div>
                            <h3 class="product-name"><?php echo e($relatedProduct['name']); ?></h3>
                            <div class="product-price"><?php echo formatPrice($relatedProduct['price']); ?></div>
                            <div class="product-actions">
                                <a href="/public/product-detail.php?id=<?php echo $relatedProduct['id']; ?>" class="btn btn-outline">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
