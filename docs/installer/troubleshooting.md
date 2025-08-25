# LibreNMS Automated Installer - Troubleshooting Guide

This guide covers common issues encountered during and after automated installation, along with their solutions.

## General Troubleshooting Steps

### 1. Run Health Check
Always start with the built-in health check:
```bash
sudo /opt/librenms/scripts/healthcheck.sh
```

### 2. Validate LibreNMS
Run the LibreNMS validation tool:
```bash
sudo -u librenms /opt/librenms/validate.php
```

### 3. Check Installation Report
Review the installation summary:
```bash
cat /opt/librenms/install_report.txt
```

### 4. Examine Logs
Check various log files:
```bash
# Installation logs (if available)
tail -f /var/log/syslog

# Nginx logs
tail -f /var/log/nginx/librenms_error.log
tail -f /var/log/nginx/librenms_access.log

# PHP-FPM logs
tail -f /var/log/php8.3-fpm.log

# LibreNMS logs
tail -f /opt/librenms/logs/librenms.log
```

## Installation Issues

### Operating System Not Supported

**Error**: `Unsupported OS` or `Unsupported Ubuntu version`

**Solution**:
1. Verify you're running Ubuntu 22.04 or 24.04:
   ```bash
   lsb_release -a
   ```
2. If using an unsupported version, upgrade Ubuntu or use a supported version
3. For other distributions, use the manual installation method

### Insufficient Privileges  

**Error**: `This script must be run as root or with sudo`

**Solution**:
```bash
# Run with sudo
sudo ./install.sh

# Or become root
sudo su -
./install.sh
```

### Network Connectivity Issues

**Error**: `Cannot reach github.com` or package download failures

**Solution**:
1. Check internet connectivity:
   ```bash
   ping -c 4 8.8.8.8
   ping -c 4 github.com
   ```
2. Verify DNS resolution:
   ```bash
   nslookup github.com
   ```
3. Check proxy settings if behind a corporate firewall:
   ```bash
   export http_proxy=http://proxy:port
   export https_proxy=https://proxy:port
   ```

### Package Installation Failures

**Error**: Package installation errors or dependency conflicts

**Solution**:
1. Update package lists:
   ```bash
   sudo apt update
   ```
2. Fix broken packages:
   ```bash
   sudo apt --fix-broken install
   ```
3. Clean package cache:
   ```bash
   sudo apt clean
   sudo apt autoclean
   ```
4. For persistent issues, try:
   ```bash
   sudo dpkg --configure -a
   ```

### Port Conflicts

**Error**: `Port 80 is already in use` or nginx fails to start

**Solution**:
1. Identify what's using the port:
   ```bash
   sudo netstat -tlpn | grep ':80'
   ```
2. Stop conflicting services:
   ```bash
   sudo systemctl stop apache2    # If Apache is running
   sudo systemctl disable apache2
   ```
3. For other services, either stop them or change ports

### Disk Space Issues

**Error**: `No space left on device`

**Solution**:
1. Check disk space:
   ```bash
   df -h
   ```
2. Clean up space:
   ```bash
   sudo apt autoremove
   sudo apt autoclean
   sudo journalctl --vacuum-time=7d
   ```
3. If `/tmp` is full:
   ```bash
   sudo rm -rf /tmp/*
   ```

## Database Issues

### MariaDB Connection Failures

**Error**: `Cannot connect to MariaDB` or database errors

**Solution**:
1. Check MariaDB status:
   ```bash
   sudo systemctl status mariadb
   ```
2. Start MariaDB if stopped:
   ```bash
   sudo systemctl start mariadb
   ```
3. Reset root password if needed:
   ```bash
   sudo mysql_secure_installation
   ```
4. Test connection manually:
   ```bash
   mysql -u root -p
   ```

### Database Creation Issues

**Error**: Database or user creation failures

**Solution**:
1. Connect to MariaDB as root:
   ```bash
   mysql -u root -p
   ```
2. Manually create the database:
   ```sql
   CREATE DATABASE librenms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'librenms'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'localhost';
   FLUSH PRIVILEGES;
   exit
   ```

### Permission Issues with Database

**Error**: `Access denied for user` errors

**Solution**:
1. Verify database credentials in `.env` file:
   ```bash
   sudo cat /opt/librenms/.env | grep DB_
   ```
2. Test database connection:
   ```bash
   mysql -u librenms -p librenms
   ```
3. Reset user privileges if needed

## Web Server Issues

### Nginx Configuration Errors

**Error**: `nginx: configuration file test failed`

**Solution**:
1. Test nginx configuration:
   ```bash
   sudo nginx -t
   ```
2. Check configuration syntax:
   ```bash
   sudo nginx -T
   ```
3. If errors exist, restore from backup or recreate:
   ```bash
   sudo rm /etc/nginx/conf.d/librenms.conf
   # Re-run installer or manually create config
   ```

### PHP-FPM Issues

**Error**: `502 Bad Gateway` or PHP connection errors

**Solution**:
1. Check PHP-FPM status:
   ```bash
   sudo systemctl status php8.3-fpm
   ```
2. Verify socket exists:
   ```bash
   ls -la /run/php-fpm-librenms.sock
   ```
3. Check PHP-FPM pool configuration:
   ```bash
   sudo cat /etc/php/8.3/fpm/pool.d/librenms.conf
   ```
4. Restart PHP-FPM:
   ```bash
   sudo systemctl restart php8.3-fpm
   ```

### File Permission Issues

**Error**: Permission denied errors in web interface

**Solution**:
1. Fix LibreNMS permissions:
   ```bash
   sudo chown -R librenms:librenms /opt/librenms
   sudo chmod 771 /opt/librenms
   sudo setfacl -R -m g::rwx /opt/librenms/rrd /opt/librenms/logs /opt/librenms/bootstrap/cache/ /opt/librenms/storage/
   ```
2. Verify `.env` file permissions:
   ```bash
   sudo chmod 640 /opt/librenms/.env
   sudo chown librenms:librenms /opt/librenms/.env
   ```

## SSL/HTTPS Issues

### Let's Encrypt Certificate Failures

**Error**: Certificate generation failures

**Solution**:
1. Ensure domain resolves to server:
   ```bash
   nslookup your-domain.com
   ```
2. Check ports are accessible:
   ```bash
   sudo ufw status
   telnet your-domain.com 80
   ```
3. Manually obtain certificate:
   ```bash
   sudo certbot --nginx -d your-domain.com
   ```
4. Check certbot logs:
   ```bash
   sudo journalctl -u certbot
   ```

### Mixed Content Issues

**Error**: Browser security warnings with HTTPS

**Solution**:
1. Update `.env` file:
   ```bash
   sudo sed -i 's/APP_URL=http:/APP_URL=https:/' /opt/librenms/.env
   ```
2. Clear application cache:
   ```bash
   sudo -u librenms php artisan config:clear
   sudo -u librenms php artisan cache:clear
   ```

## Service Issues

### Services Not Starting

**Error**: Services fail to start automatically

**Solution**:
1. Check service status:
   ```bash
   sudo systemctl status nginx mariadb php8.3-fpm snmpd
   ```
2. Enable services:
   ```bash
   sudo systemctl enable nginx mariadb php8.3-fpm snmpd
   ```
3. Start services manually:
   ```bash
   sudo systemctl start nginx mariadb php8.3-fpm snmpd
   ```

### SNMP Issues

**Error**: SNMP queries failing

**Solution**:
1. Test SNMP locally:
   ```bash
   snmpwalk -v2c -c public localhost 1.3.6.1.2.1.1.1.0
   ```
2. Check SNMP configuration:
   ```bash
   sudo cat /etc/snmp/snmpd.conf
   ```
3. Restart SNMP daemon:
   ```bash
   sudo systemctl restart snmpd
   ```
4. Verify community string settings

## LibreNMS Specific Issues

### Composer Dependency Issues

**Error**: Composer or dependency errors

**Solution**:
1. Update composer:
   ```bash
   sudo composer self-update
   ```
2. Clear composer cache:
   ```bash
   sudo -u librenms composer clear-cache
   ```
3. Reinstall dependencies:
   ```bash
   cd /opt/librenms
   sudo -u librenms ./scripts/composer_wrapper.php install --no-dev
   ```

### Application Key Issues

**Error**: `Application key not set` or encryption errors

**Solution**:
1. Generate new application key:
   ```bash
   cd /opt/librenms
   sudo -u librenms php artisan key:generate
   ```
2. Verify key in `.env` file:
   ```bash
   grep APP_KEY /opt/librenms/.env
   ```

### Migration Issues

**Error**: Database migration failures

**Solution**:
1. Run migrations manually:
   ```bash
   cd /opt/librenms
   sudo -u librenms php artisan migrate --force
   ```
2. If migrations fail, check database connectivity
3. For corrupted migrations, seek community help

## Performance Issues

### High Load or Slow Response

**Symptoms**: Slow web interface, high server load

**Solution**:
1. Check system resources:
   ```bash
   top
   free -h
   df -h
   ```
2. Optimize PHP-FPM settings:
   ```bash
   sudo nano /etc/php/8.3/fpm/pool.d/librenms.conf
   # Adjust pm.max_children, pm.start_servers, etc.
   ```
3. Enable OpCache:
   ```bash
   sudo nano /etc/php/8.3/fpm/php.ini
   # Ensure opcache.enable=1
   ```
4. Consider Redis for caching in production

### Memory Issues

**Error**: Out of memory errors

**Solution**:
1. Increase PHP memory limit:
   ```bash
   sudo nano /etc/php/8.3/fpm/php.ini
   # Set memory_limit = 512M or higher
   ```
2. Optimize MySQL settings:
   ```bash
   sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
   # Adjust innodb_buffer_pool_size
   ```
3. Add swap space if needed:
   ```bash
   sudo fallocate -l 2G /swapfile
   sudo chmod 600 /swapfile
   sudo mkswap /swapfile
   sudo swapon /swapfile
   ```

## Recovery Procedures

### Reinstallation

If all else fails, you can reinstall:

1. **Clean Slate Reinstall**:
   ```bash
   # Remove LibreNMS
   sudo rm -rf /opt/librenms
   
   # Remove database (CAUTION: This deletes all data!)
   mysql -u root -p -e "DROP DATABASE librenms;"
   
   # Re-run installer
   sudo ./install.sh
   ```

2. **Preserve Database**:
   ```bash
   # Backup database first
   mysqldump -u root -p librenms > librenms_backup.sql
   
   # Remove LibreNMS files only
   sudo rm -rf /opt/librenms
   
   # Re-run installer with same database credentials
   sudo ./install.sh
   ```

### Backup and Restore

**Create Backup**:
```bash
# Database backup
mysqldump -u root -p librenms > /backup/librenms_$(date +%Y%m%d).sql

# Configuration backup  
sudo cp /opt/librenms/.env /backup/librenms_env_$(date +%Y%m%d).backup

# RRD data backup
sudo tar -czf /backup/librenms_rrd_$(date +%Y%m%d).tar.gz /opt/librenms/rrd/
```

**Restore from Backup**:
```bash
# Restore database
mysql -u root -p librenms < /backup/librenms_20240825.sql

# Restore configuration
sudo cp /backup/librenms_env_20240825.backup /opt/librenms/.env
sudo chown librenms:librenms /opt/librenms/.env

# Restore RRD data
sudo tar -xzf /backup/librenms_rrd_20240825.tar.gz -C /
```

## Getting Help

If these solutions don't resolve your issue:

1. **Community Forums**: [https://community.librenms.org](https://community.librenms.org)
2. **Discord**: [https://discord.gg/librenms](https://discord.gg/librenms)
3. **GitHub Issues**: [https://github.com/librenms/librenms/issues](https://github.com/librenms/librenms/issues)
4. **Documentation**: [https://docs.librenms.org](https://docs.librenms.org)

When asking for help, provide:
- Ubuntu version (`lsb_release -a`)
- Installation method used
- Error messages (exact text)
- Output of health check script
- Relevant log entries
