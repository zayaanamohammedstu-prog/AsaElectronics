<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$pageTitle = 'My Account - ' . SITE_NAME;

$user = getCurrentUser($pdo);

// Get user orders
$stmt = $pdo->prepare("
    SELECT o.*, a.address_line1, a.city, a.country
    FROM orders o
    LEFT JOIN addresses a ON o.address_id = a.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1 style="margin: 2rem 0; color: var(--dark-color);">My Account</h1>
    
    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
        <!-- Sidebar -->
        <aside class="card">
            <h3 style="margin-bottom: 1rem;">Account</h3>
            <div style="padding: 1rem 0; border-bottom: 1px solid var(--border-color);">
                <strong><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                <div style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.25rem;">
                    <?php echo e($user['email']); ?>
                </div>
            </div>
            <a href="/public/logout.php" class="btn btn-outline" style="width: 100%; margin-top: 1rem; text-align: center;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </aside>
        
        <!-- Orders -->
        <div>
            <h2 style="margin-bottom: 1.5rem;">My Orders</h2>
            
            <?php if (empty($orders)): ?>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-box" style="font-size: 4rem; color: var(--border-color); margin-bottom: 1rem;"></i>
                    <h3>No Orders Yet</h3>
                    <p>Start shopping to see your orders here!</p>
                    <a href="/public/products.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-shopping-bag"></i> Browse Products
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Payment Status</th>
                                <th>Order Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-info';
                                        if ($order['payment_status'] === 'completed') $badgeClass = 'badge-success';
                                        if ($order['payment_status'] === 'failed') $badgeClass = 'badge-danger';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusBadgeClass = 'badge-info';
                                        if ($order['status'] === 'delivered') $statusBadgeClass = 'badge-success';
                                        if ($order['status'] === 'cancelled') $statusBadgeClass = 'badge-danger';
                                        if ($order['status'] === 'processing' || $order['status'] === 'shipped') $statusBadgeClass = 'badge-warning';
                                        ?>
                                        <span class="badge <?php echo $statusBadgeClass; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/public/order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline" style="padding: 0.5rem 1rem;">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
