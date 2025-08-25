#!/bin/bash
################################################################################
# LibreNMS Automated Installer
# Supports Ubuntu 22.04 and 24.04
# Interactive or non-interactive installation
# Development and production deployment modes
################################################################################

set -euo pipefail

# Script version and configuration
readonly SCRIPT_VERSION="1.0.0"
readonly LIBRENMS_USER="librenms"
readonly INSTALL_DIR="/opt/librenms"
readonly CONFIG_DIR="$(dirname "$(realpath "$0")")/../config"
readonly TEMPLATES_DIR="$CONFIG_DIR/templates"
readonly ENV_DIR="$CONFIG_DIR/env"

# Color codes for output
readonly RED='\033[0;31m'
readonly GREEN='\033[0;32m'
readonly YELLOW='\033[1;33m'
readonly BLUE='\033[0;34m'
readonly NC='\033[0m' # No Color

# Global variables for configuration
declare -A CONFIG
DEPLOYMENT_TYPE=""
NON_INTERACTIVE=false
VERBOSE=false
QUIET=false
WITH_ADDONS=false

################################################################################
# Utility Functions
################################################################################

log() {
    local level="$1"
    shift
    local message="$*"
    local timestamp="$(date '+%Y-%m-%d %H:%M:%S')"
    
    if [[ "$QUIET" == "false" ]]; then
        case "$level" in
            "INFO")  echo -e "${GREEN}[INFO]${NC}  [$timestamp] $message" ;;
            "WARN")  echo -e "${YELLOW}[WARN]${NC}  [$timestamp] $message" ;;
            "ERROR") echo -e "${RED}[ERROR]${NC} [$timestamp] $message" ;;
            "DEBUG") [[ "$VERBOSE" == "true" ]] && echo -e "${BLUE}[DEBUG]${NC} [$timestamp] $message" ;;
        esac
    fi
}

error_exit() {
    log "ERROR" "$1"
    exit 1
}

show_usage() {
    cat << EOF
LibreNMS Automated Installer v$SCRIPT_VERSION

Usage: $0 [OPTIONS]

OPTIONS:
    --dev                   Install in development mode
    --prod                  Install in production mode
    --non-interactive       Run without user prompts (requires --dev or --prod)
    --with-addons           Install all possible plugins and addons
    --verbose, -v           Enable verbose logging
    --quiet, -q             Suppress non-error output
    --help, -h              Show this help message

EXAMPLES:
    $0                      Interactive installation
    $0 --prod --verbose     Production installation with verbose output
    $0 --dev --with-addons  Development installation with all addons
    $0 --prod --with-addons --non-interactive   Full production install

EOF
}

################################################################################
# System Detection and Validation
################################################################################

detect_os() {
    log "INFO" "Detecting operating system..."
    
    if [[ ! -f /etc/os-release ]]; then
        error_exit "Cannot detect OS: /etc/os-release not found"
    fi
    
    source /etc/os-release
    
    if [[ "$ID" != "ubuntu" ]]; then
        error_exit "Unsupported OS: $ID. This installer only supports Ubuntu."
    fi
    
    case "$VERSION_ID" in
        "22.04")
            CONFIG[OS_VERSION]="22.04"
            CONFIG[PHP_VERSION]="8.3"
            CONFIG[NEEDS_PHP_PPA]="true"
            ;;
        "24.04")
            CONFIG[OS_VERSION]="24.04"
            CONFIG[PHP_VERSION]="8.3"
            CONFIG[NEEDS_PHP_PPA]="false"
            ;;
        *)
            error_exit "Unsupported Ubuntu version: $VERSION_ID. Supported versions: 22.04, 24.04"
            ;;
    esac
    
    log "INFO" "Detected Ubuntu ${CONFIG[OS_VERSION]} with PHP ${CONFIG[PHP_VERSION]}"
}

check_privileges() {
    log "INFO" "Checking user privileges..."
    
    if [[ $EUID -ne 0 ]]; then
        error_exit "This script must be run as root or with sudo"
    fi
}

check_network() {
    log "INFO" "Checking network connectivity..."
    
    local test_hosts=("github.com" "packages.ubuntu.com" "ppa.launchpad.net")
    
    for host in "${test_hosts[@]}"; do
        if ! ping -c 1 -W 5 "$host" >/dev/null 2>&1; then
            error_exit "Cannot reach $host. Please check your internet connection."
        fi
    done
    
    log "INFO" "Network connectivity verified"
}

preflight_checks() {
    log "INFO" "Running preflight checks..."
    
    # Check memory
    local mem_gb=$(awk '/MemTotal/ {printf "%.0f", $2/1024/1024}' /proc/meminfo)
    if [[ $mem_gb -lt 2 ]]; then
        log "WARN" "System has ${mem_gb}GB RAM. Minimum recommended: 2GB"
        if [[ "$NON_INTERACTIVE" == "false" ]]; then
            read -p "Continue anyway? [y/N]: " -r
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                exit 1
            fi
        fi
    fi
    
    # Check disk space
    local disk_gb=$(df / | awk 'NR==2 {printf "%.0f", $4/1024/1024}')
    if [[ $disk_gb -lt 20 ]]; then
        log "WARN" "Available disk space: ${disk_gb}GB. Minimum recommended: 20GB"
        if [[ "$NON_INTERACTIVE" == "false" ]]; then
            read -p "Continue anyway? [y/N]: " -r
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                exit 1
            fi
        fi
    fi
    
    # Check for conflicting services
    local conflicting_services=("apache2" "httpd")
    for service in "${conflicting_services[@]}"; do
        if systemctl is-active --quiet "$service" 2>/dev/null; then
            log "WARN" "$service is running. This may conflict with nginx."
            if [[ "$NON_INTERACTIVE" == "false" ]]; then
                read -p "Stop $service and continue? [y/N]: " -r
                if [[ $REPLY =~ ^[Yy]$ ]]; then
                    systemctl stop "$service"
                    systemctl disable "$service"
                else
                    exit 1
                fi
            fi
        fi
    done
    
    # Check ports
    local required_ports=(80 443)
    for port in "${required_ports[@]}"; do
        if netstat -tuln 2>/dev/null | grep -q ":$port "; then
            log "WARN" "Port $port is already in use"
            if [[ "$NON_INTERACTIVE" == "false" ]]; then
                read -p "Continue anyway? [y/N]: " -r
                if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                    exit 1
                fi
            fi
        fi
    done
    
    log "INFO" "Preflight checks completed"
}

################################################################################
# Interactive Configuration
################################################################################

prompt_deployment_type() {
    if [[ -n "$DEPLOYMENT_TYPE" ]]; then
        CONFIG[DEPLOYMENT_TYPE]="$DEPLOYMENT_TYPE"
        return
    fi
    
    echo -e "${BLUE}LibreNMS Automated Installer${NC}"
    echo "=============================="
    echo ""
    echo "Select deployment type:"
    echo "1) Development - Basic security, debug enabled, smaller resource allocation"
    echo "2) Production  - Enhanced security, SSL support, production-ready configuration"
    echo ""
    
    while true; do
        read -p "Enter choice [1-2]: " -r choice
        case $choice in
            1) CONFIG[DEPLOYMENT_TYPE]="development"; break ;;
            2) CONFIG[DEPLOYMENT_TYPE]="production"; break ;;
            *) echo "Invalid choice. Please enter 1 or 2." ;;
        esac
    done
}

prompt_configuration() {
    if [[ "$NON_INTERACTIVE" == "true" ]]; then
        # Set defaults for non-interactive mode
        CONFIG[FQDN]="${CONFIG[FQDN]:-librenms.localhost}"
        CONFIG[DB_ROOT_PASS]="${CONFIG[DB_ROOT_PASS]:-$(openssl rand -base64 32)}"
        CONFIG[DB_NAME]="${CONFIG[DB_NAME]:-librenms}"
        CONFIG[DB_USER]="${CONFIG[DB_USER]:-librenms}"
        CONFIG[DB_PASS]="${CONFIG[DB_PASS]:-$(openssl rand -base64 32)}"
        CONFIG[TIMEZONE]="${CONFIG[TIMEZONE]:-UTC}"
        CONFIG[ENABLE_SSL]="false"
        return
    fi
    
    echo ""
    echo "Configuration:"
    echo "=============="
    
    # FQDN
    read -p "Enter FQDN for LibreNMS [librenms.example.com]: " -r fqdn
    CONFIG[FQDN]="${fqdn:-librenms.example.com}"
    
    # Database configuration
    echo ""
    echo "Database Configuration:"
    while true; do
        read -s -p "Enter MySQL root password (will be created): " db_root_pass
        echo
        read -s -p "Confirm MySQL root password: " db_root_pass_confirm
        echo
        if [[ "$db_root_pass" == "$db_root_pass_confirm" ]]; then
            CONFIG[DB_ROOT_PASS]="$db_root_pass"
            break
        else
            echo "Passwords do not match. Please try again."
        fi
    done
    
    read -p "Enter LibreNMS database name [librenms]: " -r db_name
    CONFIG[DB_NAME]="${db_name:-librenms}"
    
    read -p "Enter LibreNMS database username [librenms]: " -r db_user
    CONFIG[DB_USER]="${db_user:-librenms}"
    
    while true; do
        read -s -p "Enter LibreNMS database password: " db_pass
        echo
        read -s -p "Confirm LibreNMS database password: " db_pass_confirm
        echo
        if [[ "$db_pass" == "$db_pass_confirm" ]]; then
            CONFIG[DB_PASS]="$db_pass"
            break
        else
            echo "Passwords do not match. Please try again."
        fi
    done
    
    # Timezone
    echo ""
    read -p "Enter timezone [UTC]: " -r timezone
    CONFIG[TIMEZONE]="${timezone:-UTC}"
    
    # SSL configuration
    if [[ "${CONFIG[DEPLOYMENT_TYPE]}" == "production" ]]; then
        echo ""
        read -p "Enable SSL with Let's Encrypt? [y/N]: " -r enable_ssl
        if [[ $enable_ssl =~ ^[Yy]$ ]]; then
            CONFIG[ENABLE_SSL]="true"
            read -p "Enter email for Let's Encrypt registration: " -r ssl_email
            CONFIG[SSL_EMAIL]="$ssl_email"
        else
            CONFIG[ENABLE_SSL]="false"
        fi
    else
        CONFIG[ENABLE_SSL]="false"
    fi
    
    # Display configuration summary
    echo ""
    echo "Configuration Summary:"
    echo "====================="
    echo "Deployment Type: ${CONFIG[DEPLOYMENT_TYPE]}"
    echo "FQDN: ${CONFIG[FQDN]}"
    echo "Database: ${CONFIG[DB_NAME]}"
    echo "Database User: ${CONFIG[DB_USER]}"
    echo "Timezone: ${CONFIG[TIMEZONE]}"
    echo "SSL Enabled: ${CONFIG[ENABLE_SSL]}"
    echo ""
    
    read -p "Continue with this configuration? [Y/n]: " -r confirm
    if [[ $confirm =~ ^[Nn]$ ]]; then
        echo "Installation cancelled."
        exit 0
    fi
}

################################################################################
# Package Installation
################################################################################

install_packages() {
    log "INFO" "Installing required packages..."
    
    # Update package list
    apt-get update
    
    # Add PHP PPA if needed
    if [[ "${CONFIG[NEEDS_PHP_PPA]}" == "true" ]]; then
        log "INFO" "Adding Ondřej PHP PPA..."
        apt-get install -y software-properties-common
        LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
        apt-get update
    fi
    
    # Base packages
    local packages=(
        acl curl fping git graphviz imagemagick mariadb-client mariadb-server
        mtr-tiny nginx-full nmap rrdtool snmp snmpd unzip whois traceroute
        python3-pymysql python3-dotenv python3-redis python3-setuptools
        python3-psutil python3-systemd python3-pip
    )
    
    # PHP packages
    local php_version="${CONFIG[PHP_VERSION]}"
    packages+=(
        "php${php_version}-cli" "php${php_version}-curl" "php${php_version}-fpm"
        "php${php_version}-gd" "php${php_version}-gmp" "php${php_version}-mbstring"
        "php${php_version}-mysql" "php${php_version}-snmp" "php${php_version}-xml"
        "php${php_version}-zip"
    )
    
    # Ubuntu version specific packages
    if [[ "${CONFIG[OS_VERSION]}" == "24.04" ]]; then
        packages+=(python3-command-runner)
    fi
    
    # Install packages
    log "DEBUG" "Installing: ${packages[*]}"
    DEBIAN_FRONTEND=noninteractive apt-get install -y "${packages[@]}"
    
    # Install Python requirements
    log "INFO" "Installing Python requirements..."
    pip3 install --upgrade pip
    
    # Install composer
    log "INFO" "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    
    log "INFO" "Package installation completed"
}

################################################################################
# Database Configuration
################################################################################

setup_database() {
    log "INFO" "Setting up MariaDB..."
    
    # Configure MariaDB
    local mariadb_config="/etc/mysql/mariadb.conf.d/50-server.cnf"
    
    # Add LibreNMS specific configuration
    if ! grep -q "innodb_file_per_table" "$mariadb_config"; then
        log "INFO" "Adding LibreNMS database configuration..."
        cat >> "$mariadb_config" << 'EOF'

# LibreNMS Configuration
[mysqld]
innodb_file_per_table=1
lower_case_table_names=0
EOF
    fi
    
    # Start and enable MariaDB
    systemctl enable mariadb
    systemctl restart mariadb
    
    # Secure MariaDB installation
    log "INFO" "Securing MariaDB installation..."
    mysql -u root << EOF
ALTER USER 'root'@'localhost' IDENTIFIED BY '${CONFIG[DB_ROOT_PASS]}';
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF
    
    # Create LibreNMS database and user
    log "INFO" "Creating LibreNMS database and user..."
    mysql -u root -p"${CONFIG[DB_ROOT_PASS]}" << EOF
CREATE DATABASE ${CONFIG[DB_NAME]} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER '${CONFIG[DB_USER]}'@'localhost' IDENTIFIED BY '${CONFIG[DB_PASS]}';
GRANT ALL PRIVILEGES ON ${CONFIG[DB_NAME]}.* TO '${CONFIG[DB_USER]}'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    # Load timezone data
    mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -p"${CONFIG[DB_ROOT_PASS]}" mysql
    
    log "INFO" "Database setup completed"
}

################################################################################
# LibreNMS Deployment
################################################################################

deploy_librenms() {
    log "INFO" "Deploying LibreNMS..."
    
    # Create librenms user
    log "INFO" "Creating LibreNMS user..."
    if ! id "$LIBRENMS_USER" &>/dev/null; then
        useradd "$LIBRENMS_USER" -d "$INSTALL_DIR" -M -r -s "$(which bash)"
    fi
    
    # Clone or update repository
    if [[ ! -d "$INSTALL_DIR" ]]; then
        log "INFO" "Cloning LibreNMS repository..."
        git clone https://github.com/librenms/librenms.git "$INSTALL_DIR"
    else
        log "INFO" "Updating existing LibreNMS installation..."
        cd "$INSTALL_DIR"
        git pull
    fi
    
    # Set permissions
    log "INFO" "Setting file permissions..."
    chown -R "$LIBRENMS_USER:$LIBRENMS_USER" "$INSTALL_DIR"
    chmod 771 "$INSTALL_DIR"
    setfacl -d -m g::rwx "$INSTALL_DIR/rrd" "$INSTALL_DIR/logs" "$INSTALL_DIR/bootstrap/cache/" "$INSTALL_DIR/storage/"
    setfacl -R -m g::rwx "$INSTALL_DIR/rrd" "$INSTALL_DIR/logs" "$INSTALL_DIR/bootstrap/cache/" "$INSTALL_DIR/storage/"
    
    # Install PHP dependencies
    log "INFO" "Installing PHP dependencies..."
    cd "$INSTALL_DIR"
    sudo -u "$LIBRENMS_USER" ./scripts/composer_wrapper.php install --no-dev
    
    # Generate application key
    log "INFO" "Generating application key..."
    sudo -u "$LIBRENMS_USER" php artisan key:generate
    
    # Create .env file
    log "INFO" "Creating environment configuration..."
    create_env_file
    
    log "INFO" "LibreNMS deployment completed"
}

create_env_file() {
    local env_file="$INSTALL_DIR/.env"
    
    cat > "$env_file" << EOF
APP_KEY=$(sudo -u "$LIBRENMS_USER" php artisan --no-ansi -q key:generate --show)
APP_URL=http${CONFIG[ENABLE_SSL]:+s}://${CONFIG[FQDN]}

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${CONFIG[DB_NAME]}
DB_USERNAME=${CONFIG[DB_USER]}
DB_PASSWORD=${CONFIG[DB_PASS]}

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

BCRYPT_ROUNDS=12

LOG_STACK=single
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# Deployment Type: ${CONFIG[DEPLOYMENT_TYPE]}
EOF

    chown "$LIBRENMS_USER:$LIBRENMS_USER" "$env_file"
    chmod 640 "$env_file"
}

################################################################################
# Web Server Configuration
################################################################################

configure_php_fpm() {
    log "INFO" "Configuring PHP-FPM..."
    
    local php_version="${CONFIG[PHP_VERSION]}"
    local pool_config="/etc/php/${php_version}/fpm/pool.d/librenms.conf"
    
    # Create LibreNMS PHP-FPM pool
    cp "/etc/php/${php_version}/fpm/pool.d/www.conf" "$pool_config"
    
    # Configure pool
    sed -i 's/\[www\]/[librenms]/' "$pool_config"
    sed -i "s/user = www-data/user = $LIBRENMS_USER/" "$pool_config"
    sed -i "s/group = www-data/group = $LIBRENMS_USER/" "$pool_config"
    sed -i 's/listen = .*/listen = \/run\/php-fpm-librenms.sock/' "$pool_config"
    
    # Performance tuning based on deployment type
    if [[ "${CONFIG[DEPLOYMENT_TYPE]}" == "production" ]]; then
        sed -i 's/pm.max_children = .*/pm.max_children = 50/' "$pool_config"
        sed -i 's/pm.start_servers = .*/pm.start_servers = 5/' "$pool_config"
        sed -i 's/pm.min_spare_servers = .*/pm.min_spare_servers = 5/' "$pool_config"
        sed -i 's/pm.max_spare_servers = .*/pm.max_spare_servers = 10/' "$pool_config"
    fi
    
    # Configure PHP timezone
    local php_ini_fpm="/etc/php/${php_version}/fpm/php.ini"
    local php_ini_cli="/etc/php/${php_version}/cli/php.ini"
    
    sed -i "s/;date.timezone =/date.timezone = ${CONFIG[TIMEZONE]}/" "$php_ini_fpm"
    sed -i "s/;date.timezone =/date.timezone = ${CONFIG[TIMEZONE]}/" "$php_ini_cli"
    
    systemctl restart "php${php_version}-fpm"
    
    log "INFO" "PHP-FPM configuration completed"
}

configure_nginx() {
    log "INFO" "Configuring nginx..."
    
    # Remove default site
    rm -f /etc/nginx/sites-enabled/default
    
    # Create LibreNMS vhost
    local vhost_config="/etc/nginx/conf.d/librenms.conf"
    
    cat > "$vhost_config" << EOF
server {
    listen 80;
    server_name ${CONFIG[FQDN]};
    root $INSTALL_DIR/html;
    index index.php;

    charset utf-8;
    gzip on;
    gzip_types text/css application/javascript text/javascript application/x-javascript image/svg+xml text/plain text/xsd text/xsl text/xml image/x-icon;

    # Security headers for production
$(if [[ "${CONFIG[DEPLOYMENT_TYPE]}" == "production" ]]; then
cat << 'PROD_EOF'
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
PROD_EOF
fi)

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ [^/]\.php(/|$) {
        fastcgi_pass unix:/run/php-fpm-librenms.sock;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include fastcgi.conf;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets in production
$(if [[ "${CONFIG[DEPLOYMENT_TYPE]}" == "production" ]]; then
cat << 'PROD_EOF'
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
PROD_EOF
fi)
}
EOF
    
    # Test nginx configuration
    nginx -t
    systemctl restart nginx
    
    log "INFO" "nginx configuration completed"
}

################################################################################
# SSL Configuration
################################################################################

setup_ssl() {
    if [[ "${CONFIG[ENABLE_SSL]}" != "true" ]]; then
        return
    fi
    
    log "INFO" "Setting up SSL with Let's Encrypt..."
    
    # Install certbot
    apt-get install -y certbot python3-certbot-nginx
    
    # Obtain certificate
    certbot --nginx --non-interactive --agree-tos --email "${CONFIG[SSL_EMAIL]}" -d "${CONFIG[FQDN]}"
    
    # Setup auto-renewal
    systemctl enable certbot.timer
    
    log "INFO" "SSL setup completed"
}

################################################################################
# System Services Configuration
################################################################################

configure_services() {
    log "INFO" "Configuring system services..."
    
    # Configure SNMP
    log "INFO" "Configuring SNMP..."
    cp "$INSTALL_DIR/snmpd.conf.example" /etc/snmp/snmpd.conf
    
    # Download distro script
    curl -o /usr/bin/distro https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/distro
    chmod +x /usr/bin/distro
    
    # Setup cron
    log "INFO" "Setting up cron jobs..."
    cp "$INSTALL_DIR/dist/librenms.cron" /etc/cron.d/librenms
    
    # Setup scheduler
    log "INFO" "Setting up scheduler..."
    cp "$INSTALL_DIR/dist/librenms-scheduler.service" /etc/systemd/system/
    cp "$INSTALL_DIR/dist/librenms-scheduler.timer" /etc/systemd/system/
    systemctl daemon-reload
    systemctl enable librenms-scheduler.timer
    systemctl start librenms-scheduler.timer
    
    # Setup logrotate
    log "INFO" "Setting up log rotation..."
    cp "$INSTALL_DIR/misc/librenms.logrotate" /etc/logrotate.d/librenms
    
    # Enable and start services
    local services=(mariadb nginx "php${CONFIG[PHP_VERSION]}-fpm" snmpd)
    for service in "${services[@]}"; do
        systemctl enable "$service"
        systemctl start "$service"
    done
    
    log "INFO" "Services configuration completed"
}

configure_firewall() {
    log "INFO" "Configuring firewall..."
    
    # Only configure firewall in production mode
    if [[ "${CONFIG[DEPLOYMENT_TYPE]}" == "production" ]]; then
        if command -v ufw >/dev/null; then
            ufw --force enable
            ufw allow ssh
            ufw allow http
            ufw allow https
            log "INFO" "UFW firewall configured"
        elif command -v firewall-cmd >/dev/null; then
            firewall-cmd --permanent --add-service=http
            firewall-cmd --permanent --add-service=https
            firewall-cmd --permanent --add-service=ssh
            firewall-cmd --reload
            log "INFO" "firewalld configured"
        else
            log "WARN" "No firewall found. Consider installing ufw."
        fi
    else
        log "INFO" "Firewall configuration skipped in development mode"
    fi
}

################################################################################
# Post-Installation Tasks
################################################################################

run_validation() {
    log "INFO" "Running post-installation validation..."
    
    cd "$INSTALL_DIR"
    
    # Run LibreNMS validation
    if sudo -u "$LIBRENMS_USER" ./validate.php; then
        log "INFO" "LibreNMS validation passed"
        return 0
    else
        log "WARN" "LibreNMS validation found issues"
        return 1
    fi
}

create_install_report() {
    log "INFO" "Creating installation report..."
    
    local report_file="$INSTALL_DIR/install_report.txt"
    
    cat > "$report_file" << EOF
LibreNMS Installation Report
============================
Installation Date: $(date)
Deployment Type: ${CONFIG[DEPLOYMENT_TYPE]}
FQDN: ${CONFIG[FQDN]}
Database: ${CONFIG[DB_NAME]}
PHP Version: ${CONFIG[PHP_VERSION]}
SSL Enabled: ${CONFIG[ENABLE_SSL]}

System Information:
- OS: $(lsb_release -d | cut -f2)
- Kernel: $(uname -r)
- Memory: $(free -h | grep Mem | awk '{print $2}')
- Disk: $(df -h / | tail -1 | awk '{print $4}') available

Service Status:
$(systemctl is-active mariadb nginx php${CONFIG[PHP_VERSION]}-fpm snmpd | paste <(echo -e "mariadb\nnginx\nphp-fpm\nsnmpd") -)

Next Steps:
1. Access LibreNMS at: http${CONFIG[ENABLE_SSL]:+s}://${CONFIG[FQDN]}
2. Complete web-based setup
3. Add your first device (localhost recommended)

For support and documentation, visit: https://docs.librenms.org/
EOF
    
    chown "$LIBRENMS_USER:$LIBRENMS_USER" "$report_file"
    
    log "INFO" "Installation report saved to: $report_file"
}

################################################################################
# Main Installation Flow
################################################################################

main() {
    log "INFO" "Starting LibreNMS installation (v$SCRIPT_VERSION)"
    
    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --dev)
                DEPLOYMENT_TYPE="development"
                shift
                ;;
            --prod)
                DEPLOYMENT_TYPE="production"
                shift
                ;;
            --non-interactive)
                NON_INTERACTIVE=true
                shift
                ;;
            --with-addons)
                WITH_ADDONS=true
                shift
                ;;
            --verbose|-v)
                VERBOSE=true
                shift
                ;;
            --quiet|-q)
                QUIET=true
                shift
                ;;
            --help|-h)
                show_usage
                exit 0
                ;;
            *)
                echo "Unknown option: $1"
                show_usage
                exit 1
                ;;
        esac
    done
    
    # Validate arguments
    if [[ "$NON_INTERACTIVE" == "true" && -z "$DEPLOYMENT_TYPE" ]]; then
        error_exit "Non-interactive mode requires --dev or --prod flag"
    fi
    
    # Run installation steps
    detect_os
    check_privileges
    check_network
    preflight_checks
    
    prompt_deployment_type
    prompt_configuration
    
    install_packages
    setup_database
    deploy_librenms
    configure_php_fpm
    configure_nginx
    setup_ssl
    configure_services
    configure_firewall
    
    # Install addons if requested
    if [[ "$WITH_ADDONS" == "true" ]]; then
        log "INFO" "Installing all plugins and addons..."
        local script_dir="$(dirname "$(realpath "$0")")" 
        if [[ -f "$script_dir/install-addons.sh" ]]; then
            chmod +x "$script_dir/install-addons.sh"
            "$script_dir/install-addons.sh" --"${CONFIG[DEPLOYMENT_TYPE]}" $(if [[ "$VERBOSE" == "true" ]]; then echo "--verbose"; fi)
        else
            log "WARN" "Addons installer not found. Download manually from the repository."
        fi
    fi
    
    # Post-installation
    if run_validation; then
        log "INFO" "✅ Installation completed successfully!"
    else
        log "WARN" "⚠️  Installation completed with warnings"
    fi
    
    create_install_report
    
    echo ""
    echo "🎉 LibreNMS installation complete!"
    echo ""
    echo "Access your LibreNMS installation at:"
    echo "  http${CONFIG[ENABLE_SSL]:+s}://${CONFIG[FQDN]}"
    echo ""
    echo "Installation report: $INSTALL_DIR/install_report.txt"
    echo ""
    echo "Next steps:"
    echo "1. Complete the web-based setup wizard"
    echo "2. Add localhost as your first device"
    echo "3. Review the documentation at https://docs.librenms.org/"
    echo ""
}

# Run main function with all arguments
main "$@"
