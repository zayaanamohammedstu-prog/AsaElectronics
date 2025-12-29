<?php
if (!isset($pageTitle)) $pageTitle = SITE_NAME;
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="/public/index.php">
                    <i class="fas fa-bolt"></i>
                    <?php echo e(SITE_NAME); ?>
                </a>
            </div>
            
            <div class="navbar-menu">
                <a href="/public/index.php">Home</a>
                <a href="/public/products.php">Products</a>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="/public/admin/dashboard.php">Admin Dashboard</a>
                    <?php endif; ?>
                    <a href="/public/account.php">My Account</a>
                    <a href="/public/logout.php">Logout</a>
                <?php else: ?>
                    <a href="/public/login.php">Login</a>
                    <a href="/public/register.php">Register</a>
                <?php endif; ?>
                
                <a href="/public/cart.php" class="cart-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                </a>
            </div>
            
            <div class="navbar-toggle" id="navbarToggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>
    
    <?php if ($message = getFlash('success')): ?>
        <div class="alert alert-success">
            <div class="container">
                <i class="fas fa-check-circle"></i> <?php echo e($message); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($message = getFlash('error')): ?>
        <div class="alert alert-error">
            <div class="container">
                <i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <main class="main-content">
