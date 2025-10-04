# MMHC CRM Deployment Guide

## 🚀 VPS Deployment Process

### Phase 1: Git Repository Setup

1. **Initialize Git Repository**
```bash
cd mmhc-crm
git init
git add .
git commit -m "Initial commit: MMHC CRM with Auth, Profiles, and Plans modules"
```

2. **Create GitHub/GitLab Repository**
- Create a new repository on GitHub/GitLab
- Add remote origin:
```bash
git remote add origin https://github.com/yourusername/mmhc-crm.git
git branch -M main
git push -u origin main
```

### Phase 2: VPS Server Setup

**Server Requirements:**
- PHP 8.2+ with extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML
- MySQL 8.0+
- Composer
- Git
- Web server (Apache/Nginx)

**Commands for Ubuntu/Debian:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-zip php8.2-mbstring php8.2-gd php8.2-bcmath php8.2-intl -y

# Install MySQL
sudo apt install mysql-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Git
sudo apt install git -y

# Install Nginx
sudo apt install nginx -y
```

### Phase 3: Database Setup

1. **Create Database and User**
```sql
CREATE DATABASE mmhc_crm_production;
CREATE USER 'mmhc_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON mmhc_crm_production.* TO 'mmhc_user'@'localhost';
FLUSH PRIVILEGES;
```

### Phase 4: Deploy Application

1. **Clone Repository**
```bash
cd /var/www
sudo git clone https://github.com/yourusername/mmhc-crm.git mmhc-crm
sudo chown -R www-data:www-data mmhc-crm
cd mmhc-crm
```

2. **Install Dependencies**
```bash
composer install --optimize-autoloader --no-dev
```

3. **Environment Configuration**
```bash
cp .env.production .env
# Edit .env with your production settings
nano .env
```

**Production .env Settings:**
```env
APP_NAME="MMHC CRM"
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://crm.themmhc.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mmhc_crm_production
DB_USERNAME=mmhc_user
DB_PASSWORD=secure_password_here

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

4. **Generate Application Key**
```bash
php artisan key:generate
```

5. **Run Migrations and Seeders**
```bash
php artisan migrate --force
php artisan seed:plans
```

6. **Optimize for Production**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Phase 5: Web Server Configuration

**Nginx Configuration** (`/etc/nginx/sites-available/crm.themmhc.com`):
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name crm.themmhc.com;
    root /var/www/mmhc-crm/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable Site:**
```bash
sudo ln -s /etc/nginx/sites-available/crm.themmhc.com /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Phase 6: SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d crm.themmhc.com
```

### Phase 7: DNS Configuration

**In your domain registrar/DNS provider:**
- Add A record: `crm` → `your_vps_ip_address`
- Wait for DNS propagation (up to 48 hours)

### Phase 8: Testing

1. **Test Application:**
   - Visit: https://crm.themmhc.com
   - Login with admin credentials
   - Test all modules (Auth, Profiles, Plans)

2. **Performance Optimization:**
```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/mmhc-crm
sudo chmod -R 755 /var/www/mmhc-crm
sudo chmod -R 775 /var/www/mmhc-crm/storage
sudo chmod -R 775 /var/www/mmhc-crm/bootstrap/cache
```

### Phase 9: Monitoring & Maintenance

**Setup Cron Jobs:**
```bash
crontab -e
# Add this line:
* * * * * cd /var/www/mmhc-crm && php artisan schedule:run >> /dev/null 2>&1
```

**Log Monitoring:**
```bash
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
tail -f /var/www/mmhc-crm/storage/logs/laravel.log
```

## 🔧 Troubleshooting

**Common Issues:**
1. **Permission Errors:** Ensure www-data owns the files
2. **Database Connection:** Check .env database credentials
3. **Module Loading:** Run `php artisan config:clear`
4. **SSL Issues:** Check certificate status with `certbot certificates`

## 📝 Post-Deployment Checklist

- [ ] Application loads correctly
- [ ] Admin login works
- [ ] All modules accessible
- [ ] Database connections working
- [ ] File uploads working
- [ ] SSL certificate active
- [ ] Cron jobs configured
- [ ] Backup strategy in place

## 🔄 Updates & Maintenance

**To update the application:**
```bash
cd /var/www/mmhc-crm
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
