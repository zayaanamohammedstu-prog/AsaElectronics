# Quick Start Guide

Get Asa Electronics running in 5 minutes!

## Prerequisites Check

Make sure you have:
- ✅ PHP 7.4+ (`php --version`)
- ✅ MySQL 5.7+ (`mysql --version`)
- ✅ Node.js 16+ (`node --version`)
- ✅ npm (`npm --version`)

## Option 1: Automated Setup (Recommended)

```bash
# Clone the repository
git clone https://github.com/zayaanamohammedstu-prog/AsaElectronics.git
cd AsaElectronics

# Run the setup script
./setup.sh
```

The script will:
- Check prerequisites
- Set up the database
- Configure environment files
- Install all dependencies

## Option 2: Manual Setup

### 1. Clone Repository
```bash
git clone https://github.com/zayaanamohammedstu-prog/AsaElectronics.git
cd AsaElectronics
```

### 2. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE asa_electronics;"

# Import schema
mysql -u root -p asa_electronics < database/schema.sql

# Optional: Import sample products
mysql -u root -p asa_electronics < database/sample_products.sql
```

### 3. Backend Setup
```bash
cd backend

# Copy and configure environment
cp .env.example .env
nano .env  # Edit database credentials

# Install dependencies
composer install
```

### 4. Frontend Setup
```bash
cd ../frontend

# Copy and configure environment
cp .env.example .env

# Install dependencies
npm install
```

## Running the Application

### Terminal 1 - Start Backend Server
```bash
cd backend
php -S localhost:8000
```

### Terminal 2 - Start Frontend Development Server
```bash
cd frontend
npm run dev
```

## Access the Application

🌐 **Frontend:** http://localhost:3000

🔑 **Admin Login:**
- Email: `admin@asaelectronics.com`
- Password: `admin123`

⚠️ **Important:** Change the admin password immediately after first login!

## Quick Tour

### Customer Features
1. Browse products at http://localhost:3000/products
2. Register an account
3. Add products to cart
4. Proceed to checkout
5. Complete payment with PayStack

### Admin Features
1. Login with admin credentials
2. Access admin dashboard at http://localhost:3000/admin
3. View analytics and charts
4. Manage products
5. View and manage orders
6. View registered users

## Configuration

### PayStack Setup (Required for Payments)
1. Sign up at https://paystack.com/
2. Get your API keys from the dashboard
3. Update `.env` files:

Backend (`backend/.env`):
```env
PAYSTACK_SECRET_KEY=sk_test_xxxxx
PAYSTACK_PUBLIC_KEY=pk_test_xxxxx
```

Frontend (`frontend/.env`):
```env
VITE_PAYSTACK_PUBLIC_KEY=pk_test_xxxxx
```

### Google Analytics (Optional)
1. Create a GA4 property at https://analytics.google.com/
2. Get your Measurement ID
3. Update `frontend/.env`:
```env
VITE_GA_TRACKING_ID=G-XXXXXXXXXX
```

## Next Steps

- ✅ Change admin password
- ✅ Configure PayStack for payments
- ✅ Add your products
- ✅ Customize branding and colors
- ✅ Set up Google Analytics
- ✅ Read API documentation (API.md)
- ✅ Check deployment guide (DEPLOYMENT.md)

## Common Issues

### Database Connection Error
- Check MySQL is running: `sudo systemctl status mysql`
- Verify credentials in `backend/.env`
- Ensure database exists

### Port Already in Use
- Backend: Change port `php -S localhost:8001`
- Frontend: Port is set in `vite.config.js`

### Composer Not Found
- Install Composer: https://getcomposer.org/download/

### npm Install Fails
- Clear npm cache: `npm cache clean --force`
- Delete `node_modules` and try again

## Building for Production

```bash
cd frontend
npm run build
```

The production build will be in `frontend/dist/`

See DEPLOYMENT.md for complete production deployment instructions.

## Getting Help

- 📚 Read the full [README.md](README.md)
- 🔧 Check [API Documentation](API.md)
- 🚀 Review [Deployment Guide](DEPLOYMENT.md)
- 🤝 See [Contributing Guide](CONTRIBUTING.md)
- 🐛 Report issues on GitHub

## Success! 🎉

You should now have Asa Electronics running locally. Happy coding!
