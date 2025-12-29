<?php
$pageTitle = 'Admin - ' . SITE_NAME . ' - Products';

include __DIR__ . '/header.php';

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $categoryId = (int)$_POST['category_id'];
        $stockQuantity = (int)$_POST['stock_quantity'];
        $sku = trim($_POST['sku']);
        
        // Handle image upload
        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $uploadDir = dirname(__DIR__, 2) . '/uploads/';
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imageUrl = $fileName;
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, category_id, stock_quantity, sku, image_url, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$name, $description, $price, $categoryId, $stockQuantity, $sku, $imageUrl]);
        
        setFlash('success', 'Product added successfully');
        redirect('/public/admin/products.php');
        
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $categoryId = (int)$_POST['category_id'];
        $stockQuantity = (int)$_POST['stock_quantity'];
        $sku = trim($_POST['sku']);
        
        // Get current product
        $product = getProduct($pdo, $id);
        $imageUrl = $product['image_url'];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $uploadDir = dirname(__DIR__, 2) . '/uploads/';
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Delete old image
                if ($imageUrl && file_exists($uploadDir . basename($imageUrl))) {
                    unlink($uploadDir . basename($imageUrl));
                }
                $imageUrl = $fileName;
            }
        }
        
        $stmt = $pdo->prepare("
            UPDATE products 
            SET name = ?, description = ?, price = ?, category_id = ?, stock_quantity = ?, sku = ?, image_url = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $price, $categoryId, $stockQuantity, $sku, $imageUrl, $id]);
        
        setFlash('success', 'Product updated successfully');
        redirect('/public/admin/products.php');
        
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        
        // Get product to delete image
        $product = getProduct($pdo, $id);
        if ($product && $product['image_url']) {
            $uploadDir = dirname(__DIR__, 2) . '/uploads/';
            // Validate filename to prevent directory traversal
            $filename = basename($product['image_url']);
            if (preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
                $imagePath = $uploadDir . $filename;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        setFlash('success', 'Product deleted successfully');
        redirect('/public/admin/products.php');
        
    } elseif ($action === 'toggle_active') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        
        setFlash('success', 'Product status updated');
        redirect('/public/admin/products.php');
    }
}

// Get products
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = [];
$params = [];

if ($search) {
    $where[] = '(p.name LIKE ? OR p.sku LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countSql = "SELECT COUNT(*) FROM products p $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

$pagination = paginate($total, $perPage, $page);

$sql = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $whereClause
    ORDER BY p.created_at DESC 
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = getCategories($pdo);
?>

<div class="admin-table-actions">
    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo e($search); ?>">
        </form>
    </div>
    <button class="btn btn-primary" onclick="openModal('addProductModal')">
        <i class="fas fa-plus"></i> Add Product
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <img 
                            src="<?php echo $product['image_url'] ? '/uploads/' . e(basename($product['image_url'])) : '/public/assets/images/placeholder.png'; ?>" 
                            alt="<?php echo e($product['name']); ?>"
                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                        >
                    </td>
                    <td><strong><?php echo e($product['name']); ?></strong></td>
                    <td><?php echo e($product['category_name']); ?></td>
                    <td><?php echo e($product['sku']); ?></td>
                    <td><?php echo formatPrice($product['price']); ?></td>
                    <td>
                        <?php if ($product['stock_quantity'] <= 0): ?>
                            <span class="badge badge-danger">Out of Stock</span>
                        <?php elseif ($product['stock_quantity'] < 10): ?>
                            <span class="badge badge-warning"><?php echo $product['stock_quantity']; ?></span>
                        <?php else: ?>
                            <span class="badge badge-success"><?php echo $product['stock_quantity']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $product['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-outline" onclick='editProduct(<?php echo htmlspecialchars(json_encode($product), ENT_QUOTES); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display: inline;">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-<?php echo $product['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button 
                                    type="submit" 
                                    class="btn btn-danger" 
                                    data-confirm="Are you sure you want to delete this product?"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Product</h3>
            <button class="modal-close" onclick="closeModal('addProductModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4"></textarea>
            </div>
            
            <div class="admin-form-grid">
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>SKU</label>
                    <input type="text" name="sku">
                </div>
                
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" name="price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock_quantity" min="0" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="image" accept="image/*" data-preview="addPreview">
                <img id="addPreview" class="image-preview">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Product</h3>
            <button class="modal-close" onclick="closeModal('editProductModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" id="editProductForm">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" rows="4"></textarea>
            </div>
            
            <div class="admin-form-grid">
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" id="edit_category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>SKU</label>
                    <input type="text" name="sku" id="edit_sku">
                </div>
                
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" name="price" id="edit_price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock_quantity" id="edit_stock_quantity" min="0" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="image" accept="image/*" data-preview="editPreview">
                <img id="editPreview" class="image-preview">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-save"></i> Update Product
            </button>
        </form>
    </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('edit_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_description').value = product.description || '';
    document.getElementById('edit_category_id').value = product.category_id;
    document.getElementById('edit_sku').value = product.sku || '';
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_stock_quantity').value = product.stock_quantity;
    
    if (product.image_url) {
        const preview = document.getElementById('editPreview');
        preview.src = '/uploads/' + product.image_url.split('/').pop();
        preview.classList.add('show');
    }
    
    openModal('editProductModal');
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
