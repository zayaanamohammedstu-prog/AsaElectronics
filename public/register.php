<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/public/index.php');
}

$pageTitle = 'Register - ' . SITE_NAME;

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address';
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($firstName)) {
        $errors[] = 'Please provide your first name';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email address is already registered';
        }
    }
    
    // Register user
    if (empty($errors)) {
        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password_hash, first_name, last_name, phone, role) 
                VALUES (?, ?, ?, ?, ?, 'customer')
            ");
            $stmt->execute([$email, $passwordHash, $firstName, $lastName, $phone]);
            
            setFlash('success', 'Registration successful! Please login.');
            redirect('/public/login.php');
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
    
    // Show errors
    if (!empty($errors)) {
        setFlash('error', implode('<br>', $errors));
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <h2>Create Your Account</h2>
    
    <form method="POST" action="">
        <?php echo csrfField(); ?>
        
        <div class="form-group">
            <label for="first_name">First Name *</label>
            <input 
                type="text" 
                id="first_name" 
                name="first_name" 
                required 
                placeholder="John"
                value="<?php echo e($_POST['first_name'] ?? ''); ?>"
            >
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input 
                type="text" 
                id="last_name" 
                name="last_name" 
                placeholder="Doe"
                value="<?php echo e($_POST['last_name'] ?? ''); ?>"
            >
        </div>
        
        <div class="form-group">
            <label for="email">Email Address *</label>
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
            <label for="phone">Phone Number</label>
            <input 
                type="tel" 
                id="phone" 
                name="phone" 
                placeholder="+234 XXX XXX XXXX"
                value="<?php echo e($_POST['phone'] ?? ''); ?>"
            >
        </div>
        
        <div class="form-group">
            <label for="password">Password *</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                placeholder="At least 6 characters"
            >
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password *</label>
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                required 
                placeholder="Re-enter your password"
            >
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">
            <i class="fas fa-user-plus"></i> Register
        </button>
    </form>
    
    <p style="text-align: center; margin-top: 1.5rem; color: var(--text-color);">
        Already have an account? <a href="/public/login.php" style="color: var(--primary-color); font-weight: 600;">Login here</a>
    </p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
