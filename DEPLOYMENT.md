# Deployment Guide for DigitalOcean

This guide will help you deploy the Asa Electronics platform on DigitalOcean.

## Prerequisites

- DigitalOcean account
- Domain name (optional but recommended)
- Basic knowledge of Linux commands

## Step 1: Create a Droplet

1. Log in to DigitalOcean
2. Create a new Droplet:
   - Choose Ubuntu 22.04 LTS
   - Select a plan (minimum 2GB RAM recommended)
   - Choose a datacenter region
   - Add SSH keys (recommended)
   - Create Droplet

## Step 2: Initial Server Setup

SSH into your server:

```bash
ssh root@your_server_ip
```

Update the system:

```bash
apt update && apt upgrade -y
```

## Step 3: Install LAMP Stack

Install Apache, MySQL, and PHP:

```bash
# Install Apache
apt install apache2 -y

# Install MySQL
apt install mysql-server -y
mysql_secure_installation

# Install PHP and extensions
apt install php libapache2-mod-php php-mysql php-curl php-json php-mbstring php-xml -y

# Enable Apache modules
a2enmod rewrite
systemctl restart apache2
```

## Step 4: Install Node.js and npm

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install nodejs -y
```

## Step 5: Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

## Step 6: Setup MySQL Database

```bash
mysql -u root -p

# In MySQL console:
CREATE DATABASE asa_electronics;
CREATE USER 'asa_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON asa_electronics.* TO 'asa_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Import the schema:

```bash
mysql -u root -p asa_electronics < /path/to/database/schema.sql
```

## Step 7: Deploy Application

Clone the repository:

```bash
cd /var/www/html
git clone https://github.com/zayaanamohammedstu-prog/AsaElectronics.git
cd AsaElectronics
```

Setup backend:

```bash
cd backend
composer install --no-dev --optimize-autoloader
cp .env.example .env
nano .env  # Edit configuration
```

Build frontend:

```bash
cd ../frontend
npm install
npm run build
```

Set permissions:

```bash
chown -R www-data:www-data /var/www/html/AsaElectronics
chmod -R 755 /var/www/html/AsaElectronics
mkdir -p /var/www/html/AsaElectronics/uploads
chmod 777 /var/www/html/AsaElectronics/uploads
```

## Step 8: Configure Apache

Create virtual host configuration:

```bash
nano /etc/apache2/sites-available/asaelectronics.conf
```

Add the following configuration:

```apache
<VirtualHost *:80>
    ServerAdmin admin@yourdomain.com
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/AsaElectronics/frontend/dist

    # Frontend
    <Directory /var/www/html/AsaElectronics/frontend/dist>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Handle React Router
        RewriteEngine On
        RewriteBase /
        RewriteRule ^index\.html$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.html [L]
    </Directory>

    # Backend API
    Alias /api /var/www/html/AsaElectronics/backend/api
    <Directory /var/www/html/AsaElectronics/backend/api>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Uploads
    Alias /uploads /var/www/html/AsaElectronics/uploads
    <Directory /var/www/html/AsaElectronics/uploads>
        Options -Indexes
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/asaelectronics_error.log
    CustomLog ${APACHE_LOG_DIR}/asaelectronics_access.log combined
</VirtualHost>
```

Enable the site:

```bash
a2ensite asaelectronics.conf
a2dissite 000-default.conf
systemctl reload apache2
```

## Step 9: Install phpMyAdmin (Optional)

```bash
apt install phpmyadmin -y
```

During installation:
- Select Apache2
- Configure database with dbconfig-common: Yes
- Set phpMyAdmin password

Access phpMyAdmin at: http://your_server_ip/phpmyadmin

## Step 10: Setup SSL with Let's Encrypt

Install Certbot:

```bash
apt install certbot python3-certbot-apache -y
```

Get SSL certificate:

```bash
certbot --apache -d yourdomain.com -d www.yourdomain.com
```

Follow the prompts. Certbot will automatically:
- Obtain SSL certificate
- Configure Apache for HTTPS
- Setup auto-renewal

## Step 11: Configure Firewall

```bash
# Allow SSH, HTTP, and HTTPS
ufw allow OpenSSH
ufw allow 'Apache Full'
ufw enable
```

## Step 12: Setup Automatic Backups

Create backup script:

```bash
nano /root/backup-asa.sh
```

Add:

```bash
#!/bin/bash
BACKUP_DIR="/root/backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u root -p'your_password' asa_electronics > $BACKUP_DIR/db_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/AsaElectronics

# Keep only last 7 days of backups
find $BACKUP_DIR -type f -mtime +7 -delete
```

Make executable and add to cron:

```bash
chmod +x /root/backup-asa.sh
crontab -e

# Add this line for daily backup at 2 AM:
0 2 * * * /root/backup-asa.sh
```

## Step 13: Performance Optimization

Enable PHP OPcache:

```bash
nano /etc/php/8.1/apache2/php.ini

# Add/modify:
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60

systemctl restart apache2
```

Enable compression:

```bash
a2enmod deflate
systemctl restart apache2
```

## Step 14: Monitoring and Logs

View logs:

```bash
# Apache error log
tail -f /var/log/apache2/asaelectronics_error.log

# Apache access log
tail -f /var/log/apache2/asaelectronics_access.log

# MySQL error log
tail -f /var/log/mysql/error.log
```

## Step 15: Post-Deployment Checklist

- [ ] Change default admin password
- [ ] Configure PayStack API keys
- [ ] Setup Google Analytics
- [ ] Test all functionality
- [ ] Configure backups
- [ ] Setup monitoring alerts
- [ ] Review security settings
- [ ] Configure email (if needed)
- [ ] Test payment flow
- [ ] Setup domain DNS

## Troubleshooting

### Apache won't start
```bash
apache2ctl configtest
systemctl status apache2
```

### Permission issues
```bash
chown -R www-data:www-data /var/www/html/AsaElectronics
chmod -R 755 /var/www/html/AsaElectronics
```

### Database connection issues
Check `.env` file credentials and MySQL user permissions

### API not accessible
Check Apache configuration and mod_rewrite is enabled

## Security Best Practices

1. Keep system updated: `apt update && apt upgrade`
2. Use strong passwords
3. Enable firewall (UFW)
4. Use SSL/HTTPS
5. Regular backups
6. Monitor logs
7. Disable directory listing
8. Keep PHP and dependencies updated
9. Use environment variables for secrets
10. Setup fail2ban for brute force protection

## Support

For issues or questions:
- Check the logs
- Review Apache/PHP/MySQL configuration
- Contact DigitalOcean support
- Create an issue on GitHub

## Maintenance

Regular maintenance tasks:
- Update system packages monthly
- Review and rotate logs
- Check disk space
- Monitor performance
- Backup database weekly
- Review security logs
- Update dependencies
