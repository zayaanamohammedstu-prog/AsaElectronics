<?php
$pageTitle = 'Admin - ' . SITE_NAME . ' - Orders';

include __DIR__ . '/header.php';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    verifyCsrf();
    
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $orderId]);
    
    setFlash('success', 'Order status updated successfully');
    redirect('/public/admin/orders.php');
}

// Get orders
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = [];
$params = [];

if ($search) {
    $where[] = '(o.id = ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)';
    $params[] = $search;
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($statusFilter) {
    $where[] = 'o.status = ?';
    $params[] = $statusFilter;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countSql = "
    SELECT COUNT(*) 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    $whereClause
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

$pagination = paginate($total, $perPage, $page);

$sql = "
    SELECT o.*, u.first_name, u.last_name, u.email, a.city, a.country
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN addresses a ON o.address_id = a.id
    $whereClause
    ORDER BY o.created_at DESC 
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="admin-table-actions">
    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search orders..." value="<?php echo e($search); ?>">
        </form>
    </div>
    <div style="display: flex; gap: 1rem;">
        <select onchange="window.location.href='?status=' + this.value" style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px;">
            <option value="">All Statuses</option>
            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
            <option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
            <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
            <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                    <td>
                        <?php echo e($order['first_name'] . ' ' . $order['last_name']); ?>
                        <br><small style="color: var(--text-color);"><?php echo e($order['email']); ?></small>
                    </td>
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
                        <form method="POST" style="display: inline;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select 
                                name="status" 
                                onchange="this.form.submit()" 
                                style="padding: 0.25rem 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;"
                            >
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                    <td><?php echo e($order['city'] . ', ' . $order['country']); ?></td>
                    <td>
                        <button class="btn btn-outline" onclick='viewOrder(<?php echo htmlspecialchars(json_encode($order), ENT_QUOTES); ?>)'>
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- View Order Modal -->
<div id="viewOrderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Order Details</h3>
            <button class="modal-close" onclick="closeModal('viewOrderModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="orderDetailsContent"></div>
    </div>
</div>

<script>
function viewOrder(order) {
    // Fetch order items
    fetch('/public/admin/get-order-items.php?id=' + order.id)
        .then(response => response.json())
        .then(items => {
            let itemsHtml = '<div class="table-container"><table><thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>';
            
            items.forEach(item => {
                itemsHtml += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>₦${parseFloat(item.price).toFixed(2)}</td>
                        <td>₦${(item.quantity * item.price).toFixed(2)}</td>
                    </tr>
                `;
            });
            
            itemsHtml += '</tbody></table></div>';
            
            document.getElementById('orderDetailsContent').innerHTML = `
                <div style="margin-bottom: 1rem;">
                    <strong>Customer:</strong> ${order.first_name} ${order.last_name}<br>
                    <strong>Email:</strong> ${order.email}<br>
                    <strong>Order Date:</strong> ${new Date(order.created_at).toLocaleDateString()}<br>
                    <strong>Total:</strong> ₦${parseFloat(order.total_amount).toFixed(2)}
                </div>
                <h4>Order Items</h4>
                ${itemsHtml}
            `;
            
            openModal('viewOrderModal');
        });
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
