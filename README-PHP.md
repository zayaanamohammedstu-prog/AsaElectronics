# Asa Electronics - Pure PHP E-Commerce Platform

A modern, full-stack e-commerce platform built with **pure PHP** and **MySQL**, featuring PayStack payment integration, admin dashboard with analytics, and excellent UI/UX design.

## 🚀 Features

### Customer Features
- **Modern, Responsive Design** - Works perfectly on mobile, tablet, and desktop
- **Product Catalog** - Browse products with search and category filtering
- **Shopping Cart** - Add products to cart with session persistence
- **User Authentication** - Secure registration and login system
- **Checkout Process** - Smooth checkout with address management
- **PayStack Integration** - Secure payment processing
- **Order Tracking** - View order history and status
- **Product Details** - Detailed product pages with related products

### Admin Dashboard
- **Analytics Dashboard** - Real-time sales and order statistics
- **Chart.js Visualizations** - Sales trends and order status charts
- **Product Management** - Full CRUD operations for products
- **Order Management** - Update order status and view details
- **User Management** - View customer information and statistics
- **Inventory Tracking** - Stock management and low stock alerts

## 📋 Prerequisites

- **PHP** 7.4 or higher
- **MySQL** 5.7 or higher
- **Apache** web server (with mod_rewrite enabled)
- **phpMyAdmin** (optional, for database management)

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/zayaanamohammedstu-prog/AsaElectronics.git
cd AsaElectronics
```

### 2. Database Setup

Create a MySQL database and import the schema:

```bash
# Login to MySQL
mysql -u root -p

# In MySQL console:
CREATE DATABASE asa_electronics;
exit;

# Import schema
mysql -u root -p asa_electronics < database/schema.sql
```

### 3. Configure Environment

Copy the example environment file and update with your credentials:

```bash
cp .env.example .env
nano .env
```

Update the following in your `.env` file:

```env
# Database
DB_HOST=localhost
DB_NAME=asa_electronics
DB_USER=root
DB_PASSWORD=your_mysql_password

# PayStack (Get from https://paystack.com/)
PAYSTACK_SECRET_KEY=sk_test_xxxxx
PAYSTACK_PUBLIC_KEY=pk_test_xxxxx

# Application
APP_URL=http://localhost
```

### 4. Configure Apache

#### Option A: Using Built-in PHP Server (Development)

```bash
cd public
php -S localhost:8000
```

Visit: http://localhost:8000

#### Option B: Apache Virtual Host (Production)

Create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/AsaElectronics
    
    <Directory /var/www/html/AsaElectronics>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/asaelectronics_error.log
    CustomLog ${APACHE_LOG_DIR}/asaelectronics_access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2ensite asaelectronics
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 5. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/html/AsaElectronics
sudo chmod -R 755 /var/www/html/AsaElectronics
sudo chmod -R 777 /var/www/html/AsaElectronics/uploads
```

### 6. Create Admin Account

Run this SQL to create an admin account:

```sql
INSERT INTO users (email, password_hash, first_name, last_name, role)
VALUES (
    'admin@asaelectronics.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    'Admin',
    'User',
    'admin'
);
```

## 🔧 Configuration

### PayStack Setup

1. Sign up at https://paystack.com/
2. Get your API keys from the dashboard
3. Update `PAYSTACK_SECRET_KEY` and `PAYSTACK_PUBLIC_KEY` in `.env`

### phpMyAdmin Setup (Optional)

```bash
sudo apt install phpmyadmin
# Follow the installation prompts
```

Access phpMyAdmin at: http://localhost/phpmyadmin

## 📁 Project Structure

```
AsaElectronics/
├── public/                      # Public web directory
│   ├── admin/                   # Admin panel pages
│   │   ├── dashboard.php       # Analytics dashboard
│   │   ├── products.php        # Product management
│   │   ├── orders.php          # Order management
│   │   └── users.php           # User management
│   ├── assets/                  # Static assets
│   │   ├── css/                # Stylesheets
│   │   ├── js/                 # JavaScript files
│   │   └── images/             # Images
│   ├── includes/                # Shared includes
│   │   ├── config.php          # Configuration
│   │   ├── functions.php       # Helper functions
│   │   ├── header.php          # Site header
│   │   └── footer.php          # Site footer
│   ├── index.php                # Homepage
│   ├── products.php             # Product listing
│   ├── product-detail.php       # Product details
│   ├── cart.php                 # Shopping cart
│   ├── checkout.php             # Checkout page
│   ├── payment.php              # Payment page
│   ├── account.php              # User account
│   ├── login.php                # Login page
│   └── register.php             # Registration page
├── backend/                     # Backend API (kept for compatibility)
├── database/                    # Database files
│   └── schema.sql              # Database schema
├── uploads/                     # User uploaded files
├── .htaccess                    # Apache configuration
├── .env                         # Environment configuration
└── README-PHP.md               # This file
```

## 🎨 Key Technologies

- **PHP 7.4+** - Server-side programming
- **MySQL** - Database management
- **HTML5/CSS3** - Modern markup and styling
- **JavaScript** - Client-side interactivity
- **Chart.js** - Data visualization
- **PayStack** - Payment processing
- **Font Awesome** - Icons

## 🔒 Security Features

- **Password Hashing** - bcrypt algorithm
- **CSRF Protection** - Token-based validation on all forms
- **XSS Protection** - Output escaping and sanitization
- **SQL Injection Prevention** - Prepared statements
- **Session Security** - Secure session management
- **Input Validation** - Server-side validation
- **File Upload Security** - Type and size restrictions

## 📊 Admin Dashboard Features

### Analytics
- Total revenue tracking
- Order count and status
- Product inventory levels
- Customer statistics
- Sales charts (last 7 days)
- Order status distribution
- Top selling products

### Product Management
- Add/Edit/Delete products
- Category assignment
- SKU management
- Stock tracking
- Image upload
- Active/Inactive toggle

### Order Management
- View all orders
- Update order status
- Filter by status
- Customer information
- Order details and items

### User Management
- View all users
- Customer statistics
- Order history per user
- Total spent tracking

## 🚀 Default Credentials

**Admin Account:**
- Email: admin@asaelectronics.com
- Password: admin123

**⚠️ IMPORTANT:** Change the admin password immediately after installation!

## 💡 Usage Tips

### Adding Products
1. Login as admin
2. Go to Admin Dashboard > Products
3. Click "Add Product"
4. Fill in product details
5. Upload product image
6. Set stock quantity
7. Click "Add Product"

### Managing Orders
1. Go to Admin Dashboard > Orders
2. View order details by clicking "View"
3. Update status using the dropdown
4. Status automatically updates on submit

### Processing Payments
1. Customer completes checkout
2. PayStack payment window opens
3. Customer enters payment details
4. Payment is verified automatically
5. Order status updates to "Processing"

## 🔍 Troubleshooting

### Database Connection Failed
- Check MySQL is running: `sudo systemctl status mysql`
- Verify credentials in `.env` file
- Ensure database exists: `SHOW DATABASES;`

### File Upload Issues
- Check permissions: `sudo chmod 777 uploads/`
- Verify PHP upload settings in `php.ini`
- Check `MAX_FILE_SIZE` in configuration

### PayStack Integration
- Verify API keys are correct
- Check you're using test keys for development
- Ensure callback URL is accessible

### Session Issues
- Check session directory permissions
- Verify `session.save_path` in `php.ini`
- Clear browser cookies and cache

## 📝 License

This project is licensed under the MIT License.

## 🙏 Acknowledgments

- **PayStack** for payment processing
- **Chart.js** for beautiful charts
- **Font Awesome** for icons
- **PHP Community** for excellent documentation

## 📞 Support

For support:
- Create an issue in the repository
- Email: support@asaelectronics.com

## 🔄 Updates & Maintenance

### Regular Tasks
- Backup database regularly
- Monitor error logs
- Update product inventory
- Review pending orders
- Check low stock alerts

### Security Updates
- Keep PHP updated
- Update MySQL regularly
- Review user accounts
- Monitor failed login attempts
- Check file upload directory

---

**Built with ❤️ using Pure PHP - No frameworks required!**
