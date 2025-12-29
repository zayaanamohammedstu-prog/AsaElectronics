<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$pageTitle = 'Order Details - ' . SITE_NAME;

$orderId = (int)($_GET['id'] ?? 0);

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, a.address_line1, a.address_line2, a.city, a.state, a.country, a.postal_code
    FROM orders o
    LEFT JOIN addresses a ON o.address_id = a.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found');
    redirect('/public/account.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_url
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div style="padding: 1rem 0;">
        <a href="/public/account.php" style="color: var(--primary-color); text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to My Account
        </a>
    </div>
    
    <h1 style="margin: 2rem 0; color: var(--dark-color);">Order #<?php echo $order['id']; ?></h1>
    
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
        <!-- Order Details -->
        <div>
            <div class="card">
                <h3 style="margin-bottom: 1rem;">Order Items</h3>
                
                <?php foreach ($items as $item): ?>
                    <div style="display: flex; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid var(--border-color);">
                        <img 
                            src="<?php echo $item['image_url'] ? '/uploads/' . e(basename($item['image_url'])) : '/public/assets/images/placeholder.png'; ?>" 
                            alt="<?php echo e($item['name']); ?>" 
                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;"
                        >
                        <div style="flex: 1;">
                            <h4><?php echo e($item['name']); ?></h4>
                            <p style="color: var(--text-color); font-size: 0.875rem;">
                                Quantity: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['price']); ?>
                            </p>
                        </div>
                        <div style="font-weight: bold; color: var(--primary-color);">
                            <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div style="text-align: right; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <div style="font-size: 1.25rem; font-weight: bold; color: var(--dark-color);">
                        Total: <?php echo formatPrice($order['total_amount']); ?>
                    </div>
                </div>
            </div>
            
            <div class="card" style="margin-top: 1.5rem;">
                <h3 style="margin-bottom: 1rem;">Delivery Address</h3>
                <p>
                    <?php echo e($order['address_line1']); ?>
                    <?php if ($order['address_line2']): ?>
                        <br><?php echo e($order['address_line2']); ?>
                    <?php endif; ?>
                    <br><?php echo e($order['city']); ?>, <?php echo e($order['state']); ?>
                    <br><?php echo e($order['country']); ?> <?php echo e($order['postal_code']); ?>
                </p>
            </div>
        </div>
        
        <!-- Order Status -->
        <div>
            <div class="card">
                <h3 style="margin-bottom: 1rem;">Order Status</h3>
                
                <div style="margin-bottom: 1rem;">
                    <strong>Order Date:</strong>
                    <div><?php echo date('F d, Y \a\t g:i A', strtotime($order['created_at'])); ?></div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <strong>Payment Status:</strong>
                    <div>
                        <?php
                        $badgeClass = 'badge-info';
                        if ($order['payment_status'] === 'completed') $badgeClass = 'badge-success';
                        if ($order['payment_status'] === 'failed') $badgeClass = 'badge-danger';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <strong>Order Status:</strong>
                    <div>
                        <?php
                        $statusBadgeClass = 'badge-info';
                        if ($order['status'] === 'delivered') $statusBadgeClass = 'badge-success';
                        if ($order['status'] === 'cancelled') $statusBadgeClass = 'badge-danger';
                        if ($order['status'] === 'processing' || $order['status'] === 'shipped') $statusBadgeClass = 'badge-warning';
                        ?>
                        <span class="badge <?php echo $statusBadgeClass; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>
                
                <?php if ($order['payment_reference']): ?>
                    <div style="margin-bottom: 1rem;">
                        <strong>Payment Reference:</strong>
                        <div style="font-size: 0.875rem; color: var(--text-color);">
                            <?php echo e($order['payment_reference']); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Track Order Status -->
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                    <div style="position: relative;">
                        <?php
                        $statuses = ['pending', 'processing', 'shipped', 'delivered'];
                        $currentIndex = array_search($order['status'], $statuses);
                        if ($currentIndex === false) $currentIndex = 0;
                        
                        foreach ($statuses as $index => $status):
                            $isActive = $index <= $currentIndex;
                            $color = $isActive ? 'var(--primary-color)' : 'var(--border-color)';
                        ?>
                            <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                                <div style="width: 30px; height: 30px; border-radius: 50%; background: <?php echo $color; ?>; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <?php if ($isActive): ?>
                                        <i class="fas fa-check"></i>
                                    <?php endif; ?>
                                </div>
                                <div style="margin-left: 1rem; color: <?php echo $color; ?>; font-weight: <?php echo $isActive ? '600' : '400'; ?>;">
                                    <?php echo ucfirst($status); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
