<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home - ' . SITE_NAME;

// Get featured products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 
    ORDER BY p.created_at DESC 
    LIMIT 8
");
$stmt->execute();
$featuredProducts = $stmt->fetchAll();

// Get categories
$categories = getCategories($pdo);

include __DIR__ . '/includes/header.php';
?>

<div class="hero">
    <div class="container">
        <h1>Welcome to <?php echo e(SITE_NAME); ?></h1>
        <p>Your one-stop shop for quality electronics at unbeatable prices</p>
        <form method="GET" action="/public/products.php" class="hero-search">
            <input type="text" name="search" placeholder="Search for products..." value="<?php echo e($_GET['search'] ?? ''); ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
</div>

<div class="container">
    <section class="categories-section">
        <h2 style="margin-bottom: 2rem; text-align: center; color: var(--dark-color);">Shop by Category</h2>
        <div class="products-grid">
            <?php foreach ($categories as $category): ?>
                <a href="/public/products.php?category=<?php echo e($category['id']); ?>" class="card" style="text-decoration: none; text-align: center; transition: transform 0.3s;">
                    <i class="fas fa-laptop" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                    <h3 style="color: var(--dark-color); margin-bottom: 0.5rem;"><?php echo e($category['name']); ?></h3>
                    <?php if ($category['description']): ?>
                        <p style="color: var(--text-color); font-size: 0.875rem;"><?php echo e($category['description']); ?></p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="featured-section" style="margin-top: 4rem;">
        <h2 style="margin-bottom: 2rem; text-align: center; color: var(--dark-color);">Featured Products</h2>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card">
                    <img 
                        src="<?php echo $product['image_url'] ? '/uploads/' . e(basename($product['image_url'])) : '/public/assets/images/placeholder.png'; ?>" 
                        alt="<?php echo e($product['name']); ?>" 
                        class="product-image"
                    >
                    <div class="product-info">
                        <?php if ($product['category_name']): ?>
                            <div class="product-category"><?php echo e($product['category_name']); ?></div>
                        <?php endif; ?>
                        <h3 class="product-name"><?php echo e($product['name']); ?></h3>
                        <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                        <div class="product-actions">
                            <a href="/public/product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">
                                View Details
                            </a>
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <form method="POST" action="/public/cart.php" style="flex: 1;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-outline" disabled style="flex: 1;">Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="/public/products.php" class="btn btn-primary">View All Products</a>
        </div>
    </section>

    <section id="about" class="about-section" style="margin-top: 4rem; padding: 3rem 0; background: var(--light-color); margin-left: -20px; margin-right: -20px; padding-left: 20px; padding-right: 20px;">
        <div class="container">
            <h2 style="margin-bottom: 2rem; text-align: center; color: var(--dark-color);">Why Choose Us?</h2>
            <div class="stats-grid">
                <div class="card" style="text-align: center;">
                    <i class="fas fa-shipping-fast" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                    <h3>Fast Delivery</h3>
                    <p>Quick and reliable shipping to your doorstep</p>
                </div>
                <div class="card" style="text-align: center;">
                    <i class="fas fa-shield-alt" style="font-size: 3rem; color: var(--secondary-color); margin-bottom: 1rem;"></i>
                    <h3>Secure Payment</h3>
                    <p>Safe and secure payment with PayStack</p>
                </div>
                <div class="card" style="text-align: center;">
                    <i class="fas fa-headset" style="font-size: 3rem; color: var(--warning-color); margin-bottom: 1rem;"></i>
                    <h3>24/7 Support</h3>
                    <p>Always here to help you with your orders</p>
                </div>
                <div class="card" style="text-align: center;">
                    <i class="fas fa-medal" style="font-size: 3rem; color: var(--danger-color); margin-bottom: 1rem;"></i>
                    <h3>Quality Products</h3>
                    <p>Only the best electronics for our customers</p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
