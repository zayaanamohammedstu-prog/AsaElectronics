<?php
$pageTitle = 'Admin - ' . SITE_NAME . ' - Dashboard';

include __DIR__ . '/header.php';

// Get statistics
$stats = [];

// Total revenue
$stmt = $pdo->query("
    SELECT SUM(total_amount) as total_revenue 
    FROM orders 
    WHERE payment_status = 'completed'
");
$stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$stats['total_orders'] = $stmt->fetchColumn();

// Total products
$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
$stats['total_products'] = $stmt->fetchColumn();

// Total users
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$stats['total_users'] = $stmt->fetchColumn();

// Pending orders
$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $stmt->fetchColumn();

// Low stock products
$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity < 10 AND stock_quantity > 0 AND is_active = 1");
$stats['low_stock'] = $stmt->fetchColumn();

// Get sales data for chart (last 7 days)
$stmt = $pdo->query("
    SELECT 
        DATE(created_at) as date,
        SUM(total_amount) as revenue,
        COUNT(*) as orders
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    AND payment_status = 'completed'
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$salesData = $stmt->fetchAll();

// Get order status distribution
$stmt = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM orders 
    GROUP BY status
");
$orderStatusData = $stmt->fetchAll();

// Get top selling products
$stmt = $pdo->query("
    SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.payment_status = 'completed'
    GROUP BY p.id, p.name
    ORDER BY total_sold DESC
    LIMIT 5
");
$topProducts = $stmt->fetchAll();

// Recent orders
$stmt = $pdo->query("
    SELECT o.*, u.first_name, u.last_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 10
");
$recentOrders = $stmt->fetchAll();
?>

<div class="dashboard-stats">
    <div class="stat-card primary">
        <i class="fas fa-dollar-sign"></i>
        <div class="stat-info">
            <h4>Total Revenue</h4>
            <div class="value"><?php echo formatPrice($stats['total_revenue']); ?></div>
        </div>
    </div>
    
    <div class="stat-card success">
        <i class="fas fa-shopping-cart"></i>
        <div class="stat-info">
            <h4>Total Orders</h4>
            <div class="value"><?php echo number_format($stats['total_orders']); ?></div>
        </div>
    </div>
    
    <div class="stat-card warning">
        <i class="fas fa-box"></i>
        <div class="stat-info">
            <h4>Active Products</h4>
            <div class="value"><?php echo number_format($stats['total_products']); ?></div>
        </div>
    </div>
    
    <div class="stat-card danger">
        <i class="fas fa-users"></i>
        <div class="stat-info">
            <h4>Total Customers</h4>
            <div class="value"><?php echo number_format($stats['total_users']); ?></div>
        </div>
    </div>
</div>

<div class="charts-grid">
    <!-- Sales Chart -->
    <div class="chart-container">
        <h3>Sales Overview (Last 7 Days)</h3>
        <canvas id="salesChart"></canvas>
    </div>
    
    <!-- Order Status Chart -->
    <div class="chart-container">
        <h3>Order Status Distribution</h3>
        <canvas id="statusChart"></canvas>
    </div>
</div>

<!-- Top Products -->
<div class="chart-container">
    <h3>Top Selling Products</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Units Sold</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topProducts as $product): ?>
                    <tr>
                        <td><?php echo e($product['name']); ?></td>
                        <td><?php echo number_format($product['total_sold']); ?></td>
                        <td><?php echo formatPrice($product['revenue']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Orders -->
<div class="chart-container">
    <h3>Recent Orders</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><strong>#<?php echo $order['id']; ?></strong></td>
                        <td><?php echo e($order['first_name'] . ' ' . $order['last_name']); ?></td>
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
                            <a href="/public/admin/orders.php?view=<?php echo $order['id']; ?>" class="btn btn-outline" style="padding: 0.5rem 1rem;">
                                View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_map(function($row) { return date('M d', strtotime($row['date'])); }, $salesData)); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode(array_column($salesData, 'revenue')); ?>,
            borderColor: 'rgb(37, 99, 235)',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₦' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Order Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_map('ucfirst', array_column($orderStatusData, 'status'))); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($orderStatusData, 'count')); ?>,
            backgroundColor: [
                'rgb(37, 99, 235)',
                'rgb(16, 185, 129)',
                'rgb(245, 158, 11)',
                'rgb(239, 68, 68)',
                'rgb(107, 114, 128)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
