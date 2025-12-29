#!/bin/bash

# Asa Electronics - Installation Script
# This script helps set up the e-commerce platform

set -e

echo "============================================"
echo "  Asa Electronics - Installation Script"
echo "============================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if MySQL is installed
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}Error: MySQL is not installed. Please install MySQL first.${NC}"
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo -e "${RED}Error: PHP is not installed. Please install PHP first.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ MySQL and PHP are installed${NC}"
echo ""

# Get database credentials
echo "Please provide your MySQL credentials:"
read -p "MySQL Host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "MySQL Username [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "MySQL Password: " DB_PASS
echo ""

read -p "Database Name [asa_electronics]: " DB_NAME
DB_NAME=${DB_NAME:-asa_electronics}

echo ""
echo -e "${YELLOW}Creating database...${NC}"

# Create database
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database created successfully${NC}"
else
    echo -e "${RED}Error creating database. Please check your credentials.${NC}"
    exit 1
fi

# Import schema
echo -e "${YELLOW}Importing database schema...${NC}"
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/schema.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Schema imported successfully${NC}"
else
    echo -e "${RED}Error importing schema.${NC}"
    exit 1
fi

# Ask if user wants sample data
read -p "Do you want to import sample data? (y/n) [y]: " IMPORT_SAMPLE
IMPORT_SAMPLE=${IMPORT_SAMPLE:-y}

if [ "$IMPORT_SAMPLE" = "y" ] || [ "$IMPORT_SAMPLE" = "Y" ]; then
    echo -e "${YELLOW}Importing sample data...${NC}"
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/sample_data.sql
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Sample data imported successfully${NC}"
    else
        echo -e "${RED}Error importing sample data.${NC}"
    fi
fi

# Create .env file
echo ""
echo -e "${YELLOW}Creating environment configuration...${NC}"

if [ -f .env ]; then
    echo -e "${YELLOW}Warning: .env file already exists. Creating backup...${NC}"
    cp .env .env.backup
fi

# Get PayStack keys
echo ""
echo "Please provide your PayStack API keys (optional - you can add them later):"
read -p "PayStack Secret Key (press Enter to skip): " PAYSTACK_SECRET
read -p "PayStack Public Key (press Enter to skip): " PAYSTACK_PUBLIC

# Create .env file
cat > .env << EOF
# Database Configuration
DB_HOST=$DB_HOST
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASSWORD=$DB_PASS

# PayStack Configuration
PAYSTACK_SECRET_KEY=$PAYSTACK_SECRET
PAYSTACK_PUBLIC_KEY=$PAYSTACK_PUBLIC

# Application Configuration
APP_URL=http://localhost:8000
APP_ENV=development
APP_DEBUG=true

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8000
EOF

echo -e "${GREEN}✓ Environment configuration created${NC}"

# Set permissions
echo ""
echo -e "${YELLOW}Setting file permissions...${NC}"

chmod 755 public
chmod 777 uploads

if [ -w uploads ]; then
    echo -e "${GREEN}✓ Permissions set successfully${NC}"
else
    echo -e "${YELLOW}Warning: Could not set permissions. You may need to run:${NC}"
    echo "  sudo chmod 777 uploads"
fi

# Success message
echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  Installation Complete!${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo "To start the development server, run:"
echo -e "${YELLOW}  cd public${NC}"
echo -e "${YELLOW}  php -S localhost:8000${NC}"
echo ""
echo "Then open your browser to:"
echo -e "${GREEN}  Store: http://localhost:8000${NC}"
echo -e "${GREEN}  Admin: http://localhost:8000/admin/dashboard.php${NC}"
echo ""
echo "Default admin credentials:"
echo -e "${YELLOW}  Email: admin@asaelectronics.com${NC}"
echo -e "${YELLOW}  Password: admin123${NC}"
echo ""
echo -e "${RED}IMPORTANT: Change the admin password after first login!${NC}"
echo ""

# Ask if user wants to start server
read -p "Do you want to start the development server now? (y/n) [n]: " START_SERVER
START_SERVER=${START_SERVER:-n}

if [ "$START_SERVER" = "y" ] || [ "$START_SERVER" = "Y" ]; then
    echo ""
    echo -e "${GREEN}Starting development server...${NC}"
    echo -e "${YELLOW}Press Ctrl+C to stop the server${NC}"
    echo ""
    cd public
    php -S localhost:8000
fi
