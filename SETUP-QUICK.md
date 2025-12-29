# Quick Setup Guide for Asa Electronics

## Fastest Way to Get Started

### Step 1: Database Setup (2 minutes)

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE asa_electronics;"

# Import schema
mysql -u root -p asa_electronics < database/schema.sql

# Create admin account
mysql -u root -p asa_electronics << 'EOF'
INSERT INTO users (email, password_hash, first_name, last_name, role)
VALUES (
    'admin@asaelectronics.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin',
    'User',
    'admin'
);

# Add sample categories
INSERT INTO categories (name, description, slug) VALUES
('Laptops', 'Latest laptops and notebooks', 'laptops'),
('Phones', 'Smartphones and mobile devices', 'phones'),
('Accessories', 'Tech accessories and peripherals', 'accessories');
EOF
```

### Step 2: Configure (1 minute)

```bash
# Create .env file
cat > .env << 'EOF'
DB_HOST=localhost
DB_NAME=asa_electronics
DB_USER=root
DB_PASSWORD=

PAYSTACK_SECRET_KEY=
PAYSTACK_PUBLIC_KEY=

APP_URL=http://localhost:8000
EOF
```

### Step 3: Start Server (30 seconds)

```bash
# Development server
cd public
php -S localhost:8000
```

### Step 4: Access the Site

- **Store**: http://localhost:8000
- **Admin Panel**: http://localhost:8000/admin/dashboard.php
  - Email: admin@asaelectronics.com
  - Password: admin123

## Next Steps

1. **Add Products** - Go to Admin > Products > Add Product
2. **Configure PayStack** - Get API keys from https://paystack.com
3. **Test Shopping** - Browse products, add to cart, checkout
4. **Customize** - Update colors, logo, content as needed

## Common Commands

```bash
# View PHP errors
tail -f /var/log/apache2/error.log

# Check PHP version
php -v

# Check MySQL status
sudo systemctl status mysql

# Fix permissions
sudo chmod -R 777 uploads/
```

## Production Deployment

See `README-PHP.md` for detailed production deployment instructions.

## Need Help?

- Check `README-PHP.md` for full documentation
- Review `database/schema.sql` for database structure
- See example files in each directory
