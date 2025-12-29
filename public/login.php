<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/public/index.php');
}

$pageTitle = 'Login - ' . SITE_NAME;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        setFlash('error', 'Please provide both email and password');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            
            setFlash('success', 'Welcome back, ' . e($user['first_name']) . '!');
            
            // Redirect to intended page or home
            $redirect = $_GET['redirect'] ?? '/public/index.php';
            redirect($redirect);
        } else {
            setFlash('error', 'Invalid email or password');
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <h2>Login to Your Account</h2>
    
    <form method="POST" action="">
        <?php echo csrfField(); ?>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                required 
                placeholder="your@email.com"
                value="<?php echo e($_POST['email'] ?? ''); ?>"
            >
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                placeholder="Enter your password"
            >
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">
            <i class="fas fa-sign-in-alt"></i> Login
        </button>
    </form>
    
    <p style="text-align: center; margin-top: 1.5rem; color: var(--text-color);">
        Don't have an account? <a href="/public/register.php" style="color: var(--primary-color); font-weight: 600;">Register here</a>
    </p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
