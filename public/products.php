<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Products - ' . SITE_NAME;

// Get filters
$search = $_GET['search'] ?? '';
$categoryId = $_GET['category'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

// Build query
$where = ['p.is_active = 1'];
$params = [];

if ($search) {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryId) {
    $where[] = 'p.category_id = ?';
    $params[] = $categoryId;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countSql = "SELECT COUNT(*) FROM products p WHERE $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

// Get pagination info
$pagination = paginate($total, $perPage, $page);

// Get products
$sql = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE $whereClause 
    ORDER BY p.created_at DESC 
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = getCategories($pdo);

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1 style="margin: 2rem 0 1rem; color: var(--dark-color);">Our Products</h1>
    
    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem; margin-bottom: 2rem;">
        <!-- Filters Sidebar -->
        <aside class="card">
            <h3 style="margin-bottom: 1rem;">Filters</h3>
            
            <form method="GET" action="">
                <!-- Search -->
                <div class="form-group">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Search products..." value="<?php echo e($search); ?>">
                </div>
                
                <!-- Category Filter -->
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                
                <?php if ($search || $categoryId): ?>
                    <a href="/public/products.php" class="btn btn-outline" style="width: 100%; margin-top: 0.5rem; text-align: center;">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                <?php endif; ?>
            </form>
        </aside>
        
        <!-- Products Grid -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <p style="color: var(--text-color);">
                    Showing <?php echo count($products); ?> of <?php echo $total; ?> products
                </p>
            </div>
            
            <?php if (count($products) > 0): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
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
                                
                                <?php if ($product['stock_quantity'] <= 5 && $product['stock_quantity'] > 0): ?>
                                    <p style="color: var(--warning-color); font-size: 0.875rem; margin-bottom: 0.5rem;">
                                        Only <?php echo $product['stock_quantity']; ?> left!
                                    </p>
                                <?php endif; ?>
                                
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
                                                <i class="fas fa-cart-plus"></i> Add
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
                
                <!-- Pagination -->
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
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-search" style="font-size: 4rem; color: var(--border-color); margin-bottom: 1rem;"></i>
                    <h3>No Products Found</h3>
                    <p>Try adjusting your filters or search term</p>
                    <a href="/public/products.php" class="btn btn-primary" style="margin-top: 1rem;">View All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
