<?php
$pageTitle = 'Admin - ' . SITE_NAME . ' - Users';

include __DIR__ . '/header.php';

// Get users
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = [];
$params = [];

if ($search) {
    $where[] = '(email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($roleFilter) {
    $where[] = 'role = ?';
    $params[] = $roleFilter;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countSql = "SELECT COUNT(*) FROM users $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

$pagination = paginate($total, $perPage, $page);

$sql = "
    SELECT * FROM users 
    $whereClause
    ORDER BY created_at DESC 
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get user statistics
foreach ($users as &$user) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $user['order_count'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE user_id = ? AND payment_status = 'completed'");
    $stmt->execute([$user['id']]);
    $user['total_spent'] = $stmt->fetchColumn() ?: 0;
}
?>

<div class="admin-table-actions">
    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search users..." value="<?php echo e($search); ?>">
        </form>
    </div>
    <div>
        <select onchange="window.location.href='?role=' + this.value" style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px;">
            <option value="">All Roles</option>
            <option value="customer" <?php echo $roleFilter === 'customer' ? 'selected' : ''; ?>>Customers</option>
            <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admins</option>
        </select>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Orders</th>
                <th>Total Spent</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></td>
                    <td><?php echo e($user['email']); ?></td>
                    <td><?php echo e($user['phone'] ?: '-'); ?></td>
                    <td>
                        <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-danger' : 'badge-info'; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td><?php echo number_format($user['order_count']); ?></td>
                    <td><?php echo formatPrice($user['total_spent']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($pagination['total_pages'] > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                    <?php echo $i; ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $pagination['total_pages']): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                Next <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
