# Asa Electronics E-Commerce Platform

> **⚡ Now Available in Pure PHP!**  
> This e-commerce platform is now built entirely with **PHP**, **MySQL**, **HTML**, **CSS**, and **JavaScript** - **no frameworks required**!

A modern, full-stack e-commerce platform for selling electronics with PayStack payments, admin dashboard with Chart.js analytics, and excellent UI/UX design.

## 🎯 Two Implementations Available

### 1. **Pure PHP Version** (Recommended - New!)
- ✅ **No frameworks** - Pure PHP, MySQL, HTML, CSS, JavaScript
- ✅ **Simple setup** - One installation script
- ✅ **Works with phpMyAdmin** - Easy database management
- ✅ **Modern UI/UX** - Beautiful, responsive design
- ✅ **Full e-commerce functionality** - Complete shopping experience
- 📖 [**Read PHP Documentation →**](README-PHP.md)
- 🚀 [**Quick Setup Guide →**](SETUP-QUICK.md)

### 2. Legacy React Version
- Uses React frontend with PHP backend
- Requires Node.js and npm
- More complex setup process
- See below for React setup instructions

---

## 🚀 Features (PHP Version)

### Customer Features
- **Modern, Responsive Design** - Works perfectly on all devices
- **Product Catalog** - Browse with search and category filtering
- **Shopping Cart** - Session-based cart management
- **User Authentication** - Secure registration and login
- **Checkout Process** - Smooth checkout with PayStack integration
- **Order Tracking** - View order history and status
- **Payment Integration** - Secure PayStack payment processing

### Admin Dashboard
- **Analytics Dashboard** - Real-time sales and order statistics
- **Chart.js Visualizations** - Beautiful sales and status charts
- **Product Management** - Full CRUD operations
- **Order Management** - Update status and view details
- **User Management** - Customer statistics and information
- **Inventory Tracking** - Stock management and alerts

### Backend (PHP/MySQL)
- **Session-based Authentication** - Secure user sessions
- **CSRF Protection** - Token-based form security
- **XSS Protection** - Output escaping and sanitization
- **PayStack Integration** - International payment processing
- **Product Management** - Full CRUD operations
- **Order Processing** - Complete order workflow
- **User Management** - Role-based access control
- **Input Validation** - SQL injection prevention

## 📋 Prerequisites (PHP Version)

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server (or PHP built-in server for development)
- phpMyAdmin (optional, for database management)

## 🛠️ Installation (PHP Version - Recommended)

### Quick Setup (5 minutes)

```bash
# 1. Clone the repository
git clone https://github.com/zayaanamohammedstu-prog/AsaElectronics.git
cd AsaElectronics

# 2. Run the installation script
./install.sh

# 3. Start the development server
cd public
php -S localhost:8000

# 4. Open your browser
# Store: http://localhost:8000
# Admin: http://localhost:8000/admin/dashboard.php
# Login: admin@asaelectronics.com / admin123
```

### Manual Setup

See [SETUP-QUICK.md](SETUP-QUICK.md) or [README-PHP.md](README-PHP.md) for detailed instructions.

---

## 📋 Prerequisites (React Version - Legacy)

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Node.js 16 or higher
- npm or yarn
- Composer (for PHP dependencies)
- Web server (Apache/Nginx) or PHP built-in server

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/zayaanamohammedstu-prog/AsaElectronics.git
cd AsaElectronics
```

### 2. Database Setup

```bash
# Create MySQL database
mysql -u root -p

# In MySQL console:
CREATE DATABASE asa_electronics;
exit;

# Import schema
mysql -u root -p asa_electronics < database/schema.sql
```

### 3. Backend Setup

```bash
cd backend

# Copy environment file
cp .env.example .env

# Edit .env and configure your database credentials and API keys
nano .env

# Install PHP dependencies
composer install
```

### 4. Frontend Setup

```bash
cd ../frontend

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Edit .env and configure API URL
nano .env
```

## 🔧 Configuration

### Backend Configuration

Edit `backend/.env`:

```env
# Database
DB_HOST=localhost
DB_NAME=asa_electronics
DB_USER=root
DB_PASSWORD=your_password

# PayStack (Get from https://paystack.com/)
PAYSTACK_SECRET_KEY=sk_test_xxxxx
PAYSTACK_PUBLIC_KEY=pk_test_xxxxx

# JWT Secret (Change in production!)
JWT_SECRET=your-random-secret-key

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

### Frontend Configuration

Edit `frontend/.env`:

```env
VITE_API_URL=http://localhost:8000/api
VITE_GA_TRACKING_ID=G-XXXXXXXXXX
VITE_PAYSTACK_PUBLIC_KEY=pk_test_xxxxx
```

## 🚀 Running the Application

### Development Mode

**Terminal 1 - Backend:**
```bash
cd backend
php -S localhost:8000
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
```

The application will be available at:
- Frontend: http://localhost:3000
- Backend API: http://localhost:8000/api

### Default Admin Credentials

```
Email: admin@asaelectronics.com
Password: admin123
```

**⚠️ IMPORTANT: Change the admin password immediately in production!**

## 📦 Production Deployment (DigitalOcean)

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP stack
sudo apt install apache2 mysql-server php php-mysql php-curl php-json -y

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Deploy Application

```bash
# Clone repository
cd /var/www/html
sudo git clone https://github.com/zayaanamohammedstu-prog/AsaElectronics.git
cd AsaElectronics

# Backend setup
cd backend
sudo composer install --no-dev
sudo cp .env.example .env
sudo nano .env  # Configure production settings

# Frontend build
cd ../frontend
npm install
npm run build

# Set permissions
sudo chown -R www-data:www-data /var/www/html/AsaElectronics
sudo chmod -R 755 /var/www/html/AsaElectronics
```

### 3. Configure Apache

Create `/etc/apache2/sites-available/asaelectronics.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/AsaElectronics
    
    # Frontend
    <Directory /var/www/html/AsaElectronics/frontend/dist>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Backend API
    Alias /api /var/www/html/AsaElectronics/backend/api
    <Directory /var/www/html/AsaElectronics/backend/api>
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

### 4. Setup phpMyAdmin (Optional)

```bash
sudo apt install phpmyadmin -y
# Follow prompts to configure
```

### 5. SSL Certificate (Recommended)

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d yourdomain.com
```

## 📚 API Documentation

### Authentication

**POST** `/api/auth.php/register`
```json
{
  "email": "user@example.com",
  "password": "password123",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+1234567890"
}
```

**POST** `/api/auth.php/login`
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**GET** `/api/auth.php/me` (Requires Authorization header)

### Products

**GET** `/api/products.php/products?category_id=1&search=laptop&page=1&limit=20`

**GET** `/api/products.php/{id}`

**POST** `/api/products.php/products` (Admin only)

**PUT** `/api/products.php/{id}` (Admin only)

**DELETE** `/api/products.php/{id}` (Admin only)

### Orders

**POST** `/api/orders.php/orders`
```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ],
  "address_id": 1
}
```

**GET** `/api/orders.php/orders`

**GET** `/api/orders.php/{id}`

**PUT** `/api/orders.php/{id}/status` (Admin only)

### Payments

**POST** `/api/payments.php/initialize`
```json
{
  "amount": 100.00,
  "email": "user@example.com",
  "order_id": 1,
  "callback_url": "http://yoursite.com/payment/callback"
}
```

**GET** `/api/payments.php/verify?reference=xxx`

### Analytics (Admin only)

**GET** `/api/orders.php/analytics?start_date=2024-01-01&end_date=2024-12-31`

## 🔒 Security Features

- **Password Hashing** with bcrypt
- **JWT Token** authentication
- **Prepared Statements** to prevent SQL injection
- **Input Validation** on all endpoints
- **CORS Configuration** for API security
- **XSS Protection** with output sanitization
- **HTTPS Support** with SSL certificates

## 🎨 Tech Stack

### Frontend
- React 18
- React Router 6
- Axios for HTTP requests
- Chart.js for charts
- D3.js for advanced visualizations
- React GA4 for Google Analytics
- Vite for build tooling

### Backend
- PHP 7.4+
- MySQL
- Firebase JWT library
- PDO for database access

### Payment
- PayStack for international payments

### Analytics
- Google Analytics 4
- Custom analytics API

## 📁 Project Structure

```
AsaElectronics/
├── backend/
│   ├── api/              # API endpoints
│   ├── config/           # Configuration files
│   ├── middleware/       # Auth & CORS middleware
│   ├── models/           # Database models
│   └── composer.json     # PHP dependencies
├── frontend/
│   ├── public/           # Static files
│   ├── src/
│   │   ├── components/   # React components
│   │   ├── contexts/     # React contexts
│   │   ├── pages/        # Page components
│   │   ├── services/     # API services
│   │   └── utils/        # Utility functions
│   └── package.json      # Node dependencies
├── database/
│   └── schema.sql        # Database schema
└── README.md
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License.

## 👥 Support

For support, email support@asaelectronics.com or create an issue in the repository.

## 🙏 Acknowledgments

- PayStack for payment processing
- Chart.js for beautiful charts
- React team for the amazing framework
- All contributors to this project
