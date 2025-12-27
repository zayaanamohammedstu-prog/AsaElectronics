#!/bin/bash

# Asa Electronics Setup Script
# This script helps you set up the development environment

echo "=========================================="
echo "Asa Electronics E-Commerce Platform Setup"
echo "=========================================="
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    echo "Warning: Please don't run this script as root"
    echo "Run as your normal user instead"
    exit 1
fi

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check prerequisites
echo "Checking prerequisites..."

if ! command_exists php; then
    echo "❌ PHP is not installed. Please install PHP 7.4 or higher"
    exit 1
else
    echo "✓ PHP is installed"
fi

if ! command_exists mysql; then
    echo "❌ MySQL is not installed. Please install MySQL 5.7 or higher"
    exit 1
else
    echo "✓ MySQL is installed"
fi

if ! command_exists node; then
    echo "❌ Node.js is not installed. Please install Node.js 16 or higher"
    exit 1
else
    echo "✓ Node.js is installed"
fi

if ! command_exists npm; then
    echo "❌ npm is not installed. Please install npm"
    exit 1
else
    echo "✓ npm is installed"
fi

if ! command_exists composer; then
    echo "⚠ Composer is not installed. Installing composer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    sudo mv composer.phar /usr/local/bin/composer
    echo "✓ Composer installed"
else
    echo "✓ Composer is installed"
fi

echo ""
echo "=========================================="
echo "Database Setup"
echo "=========================================="
echo ""

read -p "Enter MySQL username (default: root): " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Enter MySQL password: " DB_PASSWORD
echo ""

read -p "Enter database name (default: asa_electronics): " DB_NAME
DB_NAME=${DB_NAME:-asa_electronics}

# Test MySQL connection
if ! mysql -u"$DB_USER" -p"$DB_PASSWORD" -e "SELECT 1" >/dev/null 2>&1; then
    echo "❌ Failed to connect to MySQL. Please check your credentials"
    exit 1
fi

echo "✓ MySQL connection successful"

# Create database
echo "Creating database..."
mysql -u"$DB_USER" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"

# Import schema
echo "Importing database schema..."
mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < database/schema.sql

echo "✓ Database setup complete"

echo ""
echo "=========================================="
echo "Backend Setup"
echo "=========================================="
echo ""

cd backend

# Create .env file
if [ ! -f .env ]; then
    cp .env.example .env
    
    # Update .env with database credentials
    sed -i "s/DB_USER=root/DB_USER=$DB_USER/" .env
    sed -i "s/DB_PASSWORD=/DB_PASSWORD=$DB_PASSWORD/" .env
    sed -i "s/DB_NAME=asa_electronics/DB_NAME=$DB_NAME/" .env
    
    # Generate random JWT secret
    JWT_SECRET=$(openssl rand -base64 32)
    sed -i "s/JWT_SECRET=your-secret-key-change-in-production/JWT_SECRET=$JWT_SECRET/" .env
    
    echo "✓ Backend .env file created"
else
    echo "⚠ Backend .env file already exists, skipping..."
fi

# Install PHP dependencies
echo "Installing PHP dependencies..."
if composer install; then
    echo "✓ PHP dependencies installed"
else
    echo "❌ Failed to install PHP dependencies"
    exit 1
fi

cd ..

echo ""
echo "=========================================="
echo "Frontend Setup"
echo "=========================================="
echo ""

cd frontend

# Create .env file
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✓ Frontend .env file created"
else
    echo "⚠ Frontend .env file already exists, skipping..."
fi

# Install Node dependencies
echo "Installing Node dependencies..."
if npm install; then
    echo "✓ Node dependencies installed"
else
    echo "❌ Failed to install Node dependencies"
    exit 1
fi

cd ..

echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "To start the development servers:"
echo ""
echo "1. Backend (in one terminal):"
echo "   cd backend && php -S localhost:8000"
echo ""
echo "2. Frontend (in another terminal):"
echo "   cd frontend && npm run dev"
echo ""
echo "Then visit: http://localhost:3000"
echo ""
echo "Default admin credentials:"
echo "Email: admin@asaelectronics.com"
echo "Password: admin123"
echo ""
echo "⚠ IMPORTANT: Change the admin password after first login!"
echo ""
echo "For production deployment, see DEPLOYMENT.md"
echo ""
