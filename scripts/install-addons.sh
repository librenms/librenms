#!/bin/bash
################################################################################
# LibreNMS Addons and Extensions Installer
# Installs all possible plugins, addons, and extensions
# Supports Ubuntu 22.04 and 24.04
################################################################################

set -euo pipefail

# Color codes for output
readonly RED='\033[0;31m'
readonly GREEN='\033[0;32m'
readonly YELLOW='\033[1;33m'
readonly BLUE='\033[0;34m'
readonly NC='\033[0m' # No Color

# Configuration
readonly LIBRENMS_DIR="/opt/librenms"
readonly LIBRENMS_USER="librenms"

# Global variables
VERBOSE=false
DEPLOYMENT_TYPE=""

################################################################################
# Utility Functions
################################################################################

log() {
    local level="$1"
    shift
    local message="$*"
    local timestamp="$(date '+%Y-%m-%d %H:%M:%S')"
    
    case "$level" in
        "INFO")  echo -e "${GREEN}[INFO]${NC}  [$timestamp] $message" ;;
        "WARN")  echo -e "${YELLOW}[WARN]${NC}  [$timestamp] $message" ;;
        "ERROR") echo -e "${RED}[ERROR]${NC} [$timestamp] $message" ;;
        "DEBUG") [[ "$VERBOSE" == "true" ]] && echo -e "${BLUE}[DEBUG]${NC} [$timestamp] $message" ;;
    esac
}

error_exit() {
    log "ERROR" "$1"
    exit 1
}

################################################################################
# Addon Installation Functions
################################################################################

install_librenms_agent() {
    log "INFO" "Installing LibreNMS Agent..."
    
    # Install the LibreNMS agent for enhanced monitoring
    cd /opt
    if [[ ! -d "librenms-agent" ]]; then
        git clone https://github.com/librenms/librenms-agent.git
        cd librenms-agent
    else
        cd librenms-agent
        git pull
    fi
    
    # Install check_mk agent
    cp check_mk_agent /usr/bin/check_mk_agent
    chmod +x /usr/bin/check_mk_agent
    
    # Create directories
    mkdir -p /usr/lib/check_mk_agent/plugins /usr/lib/check_mk_agent/local
    
    # Install systemd service
    cp check_mk@.service check_mk.socket /etc/systemd/system/
    systemctl daemon-reload
    systemctl enable check_mk.socket
    systemctl start check_mk.socket
    
    # Install all available local scripts
    log "INFO" "Installing agent local scripts..."
    find agent-local -name "*.py" -o -name "*.sh" -o -name "*.pl" | while read script; do
        cp "$script" /usr/lib/check_mk_agent/local/
        chmod +x "/usr/lib/check_mk_agent/local/$(basename "$script")"
        log "DEBUG" "Installed agent script: $(basename "$script")"
    done
    
    log "INFO" "LibreNMS Agent installation completed"
}

install_oxidized() {
    log "INFO" "Installing Oxidized for configuration backup..."
    
    # Install Ruby and dependencies
    apt-get install -y ruby ruby-dev libsqlite3-dev libssl-dev pkg-config cmake libssh2-1-dev
    
    # Install Oxidized gem
    gem install oxidized oxidized-web oxidized-script
    
    # Create oxidized user
    if ! id "oxidized" &>/dev/null; then
        useradd -r -d /var/lib/oxidized -m -s /bin/bash oxidized
    fi
    
    # Create configuration directory
    mkdir -p /etc/oxidized
    chown oxidized:oxidized /etc/oxidized
    
    # Basic Oxidized configuration
    cat > /etc/oxidized/config << 'EOF'
---
username: admin
password: admin
model_map:
  cisco: ios
  juniper: junos
interval: 3600
use_syslog: false
debug: false
threads: 30
timeout: 20
retries: 3
prompt: !ruby/regexp /^([\w.@-]+[#>]\s?)$/
rest: 127.0.0.1:8888
next_adds_job: false
vars: {}
groups: {}
models: {}
pid: "/var/lib/oxidized/.config/oxidized/pid"
input:
  default: ssh, telnet
  debug: false
  ssh:
    secure: false
output:
  default: git
  git:
    user: Oxidized
    email: oxidized@example.com
    repo: "/var/lib/oxidized/.config/oxidized/configs.git"
source:
  default: csv
  csv:
    file: "/var/lib/oxidized/.config/oxidized/router.db"
    delimiter: !ruby/regexp /:/
    map:
      name: 0
      ip: 1
      model: 2
      username: 3
      password: 4
    vars_map:
      enable: 0
model_map:
  airos: aireos
  vrp: huawei
  ros: routeros
EOF
    
    chown oxidized:oxidized /etc/oxidized/config
    
    # Create systemd service
    cat > /etc/systemd/system/oxidized.service << 'EOF'
[Unit]
Description=Oxidized Network Device Configuration Backup
After=network.target

[Service]
Type=forking
PIDFile=/var/lib/oxidized/.config/oxidized/pid
User=oxidized
Group=oxidized
WorkingDirectory=/var/lib/oxidized
Environment=OXIDIZED_HOME=/var/lib/oxidized/.config/oxidized
ExecStart=/usr/local/bin/oxidized --daemonize
Restart=always

[Install]
WantedBy=multi-user.target
EOF
    
    systemctl daemon-reload
    systemctl enable oxidized
    systemctl start oxidized
    
    log "INFO" "Oxidized installation completed"
}

install_metric_storage_backends() {
    log "INFO" "Installing metric storage backends..."
    
    # Install InfluxDB
    log "INFO" "Installing InfluxDB..."
    wget -qO- https://repos.influxdata.com/influxdb.key | apt-key add -
    echo "deb https://repos.influxdata.com/ubuntu $(lsb_release -cs) stable" > /etc/apt/sources.list.d/influxdb.list
    apt-get update
    apt-get install -y influxdb influxdb-client
    
    # Configure InfluxDB
    systemctl enable influxdb
    systemctl start influxdb
    
    # Install Grafana
    log "INFO" "Installing Grafana..."
    wget -qO- https://packages.grafana.com/gpg.key | apt-key add -
    echo "deb https://packages.grafana.com/oss/deb stable main" > /etc/apt/sources.list.d/grafana.list
    apt-get update
    apt-get install -y grafana
    
    systemctl enable grafana-server
    systemctl start grafana-server
    
    # Install Redis for caching and sessions
    log "INFO" "Installing Redis..."
    apt-get install -y redis-server redis-tools
    
    # Configure Redis
    sed -i 's/^# maxmemory <bytes>/maxmemory 256mb/' /etc/redis/redis.conf
    sed -i 's/^# maxmemory-policy noeviction/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf
    
    systemctl enable redis-server
    systemctl restart redis-server
    
    # Install Elasticsearch (optional, for advanced logging)
    if [[ "$DEPLOYMENT_TYPE" == "production" ]]; then
        log "INFO" "Installing Elasticsearch..."
        wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | apt-key add -
        echo "deb https://artifacts.elastic.co/packages/8.x/apt stable main" > /etc/apt/sources.list.d/elastic-8.x.list
        apt-get update
        apt-get install -y elasticsearch
        
        systemctl enable elasticsearch
        systemctl start elasticsearch
    fi
    
    log "INFO" "Metric storage backends installation completed"
}

install_monitoring_tools() {
    log "INFO" "Installing additional monitoring tools..."
    
    # Install Smokeping for latency monitoring
    log "INFO" "Installing Smokeping..."
    apt-get install -y smokeping
    
    # Configure Smokeping
    systemctl enable smokeping
    systemctl start smokeping
    
    # Install NFSEN for NetFlow analysis (if available)
    log "INFO" "Installing network analysis tools..."
    apt-get install -y nfcapd flow-tools rrdtool
    
    # Install additional SNMP tools
    apt-get install -y snmp-mibs-downloader
    
    # Download MIBs
    download-mibs
    
    log "INFO" "Monitoring tools installation completed"
}

install_security_addons() {
    log "INFO" "Installing security addons..."
    
    # Install fail2ban for security
    apt-get install -y fail2ban
    
    # Configure fail2ban for LibreNMS
    cat > /etc/fail2ban/jail.local << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3

[nginx-http-auth]
enabled = true

[nginx-noscript]
enabled = true

[nginx-badbots]
enabled = true

[nginx-noproxy]
enabled = true

[sshd]
enabled = true
port = ssh
logpath = %(sshd_log)s
backend = %(sshd_backend)s
EOF
    
    systemctl enable fail2ban
    systemctl restart fail2ban
    
    # Install ClamAV for malware scanning (production only)
    if [[ "$DEPLOYMENT_TYPE" == "production" ]]; then
        log "INFO" "Installing ClamAV..."
        apt-get install -y clamav clamav-daemon clamav-freshclam
        
        # Update virus definitions
        freshclam
        
        systemctl enable clamav-daemon
        systemctl start clamav-daemon
    fi
    
    log "INFO" "Security addons installation completed"
}

install_performance_tools() {
    log "INFO" "Installing performance monitoring tools..."
    
    # Install htop for system monitoring
    apt-get install -y htop iotop atop sysstat
    
    # Install and configure RRDCached for better performance
    apt-get install -y rrdcached
    
    # Configure RRDCached
    cat > /etc/default/rrdcached << 'EOF'
DAEMON_USER="librenms"
DAEMON_GROUP="librenms"
WRITE_THREADS=4
WRITE_TIMEOUT=1800
WRITE_JITTER=1800
BASE_PATH="/opt/librenms/rrd/"
JOURNAL_PATH="/var/lib/rrdcached/journal/"
PIDFILE="/var/run/rrdcached.pid"
SOCKFILE="/var/run/rrdcached.sock"
SOCKGROUP="librenms"
SOCKMODE="0660"
BASE_OPTIONS="-B -F -s librenms -U librenms -G librenms -w 1800 -z 1800 -f 3600"
EOF
    
    # Create journal directory
    mkdir -p /var/lib/rrdcached/journal/
    chown librenms:librenms /var/lib/rrdcached/journal/
    
    systemctl enable rrdcached
    systemctl start rrdcached
    
    log "INFO" "Performance tools installation completed"
}

install_application_monitoring() {
    log "INFO" "Installing application monitoring extensions..."
    
    # Install packages for various application monitoring
    local app_packages=(
        # Web servers
        apache2-utils nginx-extras
        
        # Databases  
        mysql-client postgresql-client redis-tools
        
        # System monitoring
        lm-sensors smartmontools
        
        # Network tools
        iperf3 mtr-tiny traceroute nmap
        
        # Container monitoring
        docker.io docker-compose
        
        # Mail systems
        postfix mailutils
        
        # DNS
        bind9-dnsutils
        
        # Backup tools
        rsync duplicity
        
        # Development tools (for dev mode)
        $(if [[ "$DEPLOYMENT_TYPE" == "development" ]]; then
            echo "build-essential git-core vim-nox tmux screen"
        fi)
    )
    
    log "DEBUG" "Installing application packages: ${app_packages[*]}"
    apt-get install -y "${app_packages[@]}" || log "WARN" "Some application packages failed to install"
    
    # Install Python packages for applications
    local python_packages=(
        "psutil>=5.6.0"
        "PyMySQL"
        "redis>=3.0.0"
        "python-memcached"
        "influxdb-client"
        "elasticsearch"
        "kafka-python"
        "paho-mqtt"
        "docker"
        "boto3"  # AWS integration
        "azure-storage-blob"  # Azure integration
        "google-cloud-storage"  # GCP integration
    )
    
    log "INFO" "Installing Python application packages..."
    for package in "${python_packages[@]}"; do
        pip3 install "$package" || log "WARN" "Failed to install Python package: $package"
    done
    
    # Install Perl modules for advanced features
    log "INFO" "Installing Perl modules..."
    apt-get install -y cpanminus libdevel-nytprof-perl
    
    local perl_modules=(
        "MIME::Base64"
        "Gzip::Faster"
        "JSON::XS"
        "Net::SNMP"
        "DBI"
        "DBD::mysql"
    )
    
    for module in "${perl_modules[@]}"; do
        cpanm "$module" || log "WARN" "Failed to install Perl module: $module"
    done
    
    log "INFO" "Application monitoring installation completed"
}

install_librenms_agent_scripts() {
    log "INFO" "Installing LibreNMS agent and all available scripts..."
    
    # Clone or update agent repository
    cd /opt
    if [[ ! -d "librenms-agent" ]]; then
        git clone https://github.com/librenms/librenms-agent.git
    else
        cd librenms-agent
        git pull
        cd /opt
    fi
    
    cd librenms-agent
    
    # Install all agent-local scripts
    log "INFO" "Installing all agent-local monitoring scripts..."
    
    # Copy all scripts to check_mk local directory
    find agent-local -type f \( -name "*.py" -o -name "*.sh" -o -name "*.pl" \) | while read script; do
        script_name=$(basename "$script")
        cp "$script" "/usr/lib/check_mk_agent/local/$script_name"
        chmod +x "/usr/lib/check_mk_agent/local/$script_name"
        log "DEBUG" "Installed monitoring script: $script_name"
    done
    
    # Install SNMP extends
    log "INFO" "Installing SNMP extend scripts..."
    find snmp -type f -name "*.py" -o -name "*.sh" -o -name "*.pl" | while read script; do
        script_name=$(basename "$script")
        cp "$script" "/usr/local/bin/$script_name"
        chmod +x "/usr/local/bin/$script_name"
        log "DEBUG" "Installed SNMP extend: $script_name"
    done
    
    # Install librenms_return_optimizer for JSON optimization
    log "INFO" "Installing return optimizer..."
    wget -O /usr/local/bin/librenms_return_optimizer \
        https://raw.githubusercontent.com/librenms/librenms-agent/master/utils/librenms_return_optimizer
    chmod +x /usr/local/bin/librenms_return_optimizer
    
    log "INFO" "LibreNMS agent scripts installation completed"
}

configure_advanced_snmp() {
    log "INFO" "Configuring advanced SNMP monitoring..."
    
    # Enhanced SNMP configuration
    cat >> /etc/snmp/snmpd.conf << 'EOF'

# Extended SNMP monitoring configuration
extend apache /usr/local/bin/apache-stats
extend nginx /usr/local/bin/nginx-stats  
extend mysql /usr/local/bin/mysql-stats
extend redis /usr/local/bin/redis-stats
extend memcached /usr/local/bin/memcached-stats
extend postfix /usr/local/bin/postfix-stats
extend fail2ban /usr/local/bin/fail2ban-stats
extend certificate /usr/local/bin/certificate-check
extend systemd /usr/local/bin/systemd-stats
extend docker /usr/local/bin/docker-stats
extend postgres /usr/local/bin/postgres-stats
extend bind /usr/local/bin/bind-stats
extend powerdns /usr/local/bin/powerdns-stats
extend unbound /usr/local/bin/unbound-stats
extend ntp /usr/local/bin/ntp-stats
extend chronyd /usr/local/bin/chronyd-stats
extend entropy /usr/local/bin/entropy-check
extend smart /usr/local/bin/smart-stats
extend zfs /usr/local/bin/zfs-stats
extend mdadm /usr/local/bin/mdadm-stats
extend ups /usr/local/bin/ups-stats
extend sensors /usr/local/bin/sensors-stats
extend portactivity /usr/local/bin/librenms_return_optimizer -- /usr/local/bin/portactivity-check

# Hardware monitoring
extend dell-openmanage /usr/local/bin/check_openmanage
extend supermicro /usr/local/bin/supermicro-stats
extend hp-ilo /usr/local/bin/hp-ilo-stats

# Application specific monitoring
extend pihole /usr/local/bin/pihole-stats
extend seafile /usr/local/bin/seafile-stats
extend nextcloud /usr/local/bin/nextcloud-stats
extend mailcow /usr/local/bin/mailcow-stats
extend proxmox /usr/local/bin/proxmox-stats
extend gitlab /usr/local/bin/gitlab-stats
extend jenkins /usr/local/bin/jenkins-stats
extend elasticsearch /usr/local/bin/elasticsearch-stats
extend influxdb /usr/local/bin/influxdb-stats
extend grafana /usr/local/bin/grafana-stats

# Network services
extend dhcp /usr/local/bin/dhcp-stats
extend fping /usr/local/bin/fping-stats
extend smokeping /usr/local/bin/smokeping-stats
extend rancid /usr/local/bin/rancid-stats

# Security monitoring
extend ossec /usr/local/bin/ossec-stats
extend suricata /usr/local/bin/suricata-stats
extend snort /usr/local/bin/snort-stats

# Backup monitoring
extend backupninja /usr/local/bin/backupninja-stats
extend borgbackup /usr/local/bin/borgbackup-stats
extend duplicity /usr/local/bin/duplicity-stats

# Virtualization monitoring
extend libvirt /usr/local/bin/libvirt-stats
extend xen /usr/local/bin/xen-stats
extend openvz /usr/local/bin/openvz-stats

# Storage monitoring
extend nfs /usr/local/bin/nfs-stats
extend samba /usr/local/bin/samba-stats
extend iscsi /usr/local/bin/iscsi-stats

# High availability
extend pacemaker /usr/local/bin/pacemaker-stats
extend keepalived /usr/local/bin/keepalived-stats
extend haproxy /usr/local/bin/haproxy-stats

EOF
    
    # Restart SNMP daemon
    systemctl restart snmpd
    
    log "INFO" "Advanced SNMP configuration completed"
}

install_optional_php_extensions() {
    log "INFO" "Installing optional PHP extensions..."
    
    local php_version="8.3"
    
    # Get Ubuntu version for conditional installs
    source /etc/os-release
    
    local php_extensions=(
        "php${php_version}-bcmath"     # Better calculations
        "php${php_version}-bz2"        # Compression
        "php${php_version}-intl"       # Internationalization
        "php${php_version}-ldap"       # LDAP authentication
        "php${php_version}-gmp"        # Large number support
        "php${php_version}-imagick"    # Advanced image processing
        "php${php_version}-memcached"  # Memcached support
        "php${php_version}-redis"      # Redis support
        "php${php_version}-soap"       # SOAP protocol support
        "php${php_version}-ssh2"       # SSH2 protocol support
        "php${php_version}-xsl"        # XSL transformation
        "php${php_version}-tidy"       # HTML tidy
        "php${php_version}-zip"        # ZIP file support
        "php${php_version}-opcache"    # OpCode caching
        "php${php_version}-apcu"       # User cache
    )
    
    # Install extensions
    for ext in "${php_extensions[@]}"; do
        apt-get install -y "$ext" || log "WARN" "Failed to install: $ext"
    done
    
    # Configure OpCache for better performance
    cat >> "/etc/php/${php_version}/fpm/conf.d/10-opcache.ini" << 'EOF'
; Enhanced OpCache configuration for LibreNMS
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=1
opcache.save_comments=1
opcache.enable_file_override=0
opcache.validate_timestamps=1
opcache.max_file_size=0
opcache.fast_shutdown=1
EOF
    
    systemctl restart "php${php_version}-fpm"
    
    log "INFO" "PHP extensions installation completed"
}

install_network_discovery_tools() {
    log "INFO" "Installing network discovery and analysis tools..."
    
    # Advanced network discovery
    apt-get install -y nmap masscan zmap
    
    # Network analysis tools  
    apt-get install -y tcpdump wireshark-common tshark
    
    # SNMP tools
    apt-get install -y snmp-mibs-downloader libsnmp-dev
    
    # Network utilities
    apt-get install -y iperf3 iftop nethogs ss bridge-utils vlan
    
    # DNS tools
    apt-get install -y dig host nslookup dnsutils
    
    # Certificate monitoring
    apt-get install -y openssl ca-certificates
    
    log "INFO" "Network discovery tools installation completed"
}

install_database_extensions() {
    log "INFO" "Installing database extensions and tools..."
    
    # MariaDB additional tools
    apt-get install -y mariadb-backup percona-toolkit
    
    # Database monitoring tools
    apt-get install -y mytop innotop
    
    # Configure enhanced MariaDB settings
    cat >> /etc/mysql/mariadb.conf.d/50-server.cnf << 'EOF'

# Enhanced LibreNMS configuration
max_connections = 300
innodb_buffer_pool_size = 256M
innodb_log_file_size = 128M
innodb_log_buffer_size = 32M
innodb_flush_log_at_trx_commit = 2
innodb_lock_wait_timeout = 50
query_cache_type = 1
query_cache_size = 32M
query_cache_limit = 2M
tmp_table_size = 32M
max_heap_table_size = 32M
max_allowed_packet = 16M
thread_stack = 192K
thread_cache_size = 8
EOF
    
    systemctl restart mariadb
    
    log "INFO" "Database extensions installation completed"
}

install_backup_solutions() {
    log "INFO" "Installing backup solutions..."
    
    # Install backup tools
    apt-get install -y rsync borgbackup duplicity rdiff-backup
    
    # Create backup script for LibreNMS
    cat > /usr/local/bin/librenms-backup << 'EOF'
#!/bin/bash
# LibreNMS backup script

BACKUP_DIR="/backup/librenms"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p "$BACKUP_DIR"

# Database backup
mysqldump -u root -p librenms > "$BACKUP_DIR/librenms_db_$DATE.sql"

# Configuration backup
tar -czf "$BACKUP_DIR/librenms_config_$DATE.tar.gz" /opt/librenms/.env /opt/librenms/config.php

# RRD data backup (optional, can be large)
tar -czf "$BACKUP_DIR/librenms_rrd_$DATE.tar.gz" /opt/librenms/rrd/

# Web customizations backup
tar -czf "$BACKUP_DIR/librenms_custom_$DATE.tar.gz" /opt/librenms/html/custom/

# Cleanup old backups (keep 7 days)
find "$BACKUP_DIR" -name "*.sql" -mtime +7 -delete
find "$BACKUP_DIR" -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
EOF
    
    chmod +x /usr/local/bin/librenms-backup
    
    # Add backup cron job
    cat > /etc/cron.d/librenms-backup << 'EOF'
# LibreNMS daily backup
0 2 * * * root /usr/local/bin/librenms-backup > /var/log/librenms-backup.log 2>&1
EOF
    
    log "INFO" "Backup solutions installation completed"
}

install_developer_tools() {
    if [[ "$DEPLOYMENT_TYPE" != "development" ]]; then
        return
    fi
    
    log "INFO" "Installing developer tools..."
    
    # Development packages
    apt-get install -y \
        build-essential \
        git-core \
        vim-nox \
        tmux \
        screen \
        htop \
        tree \
        jq \
        curl \
        wget \
        unzip \
        zip \
        sqlite3 \
        nodejs \
        npm \
        yarn
    
    # Install Node.js packages for frontend development
    npm install -g @vue/cli webpack webpack-cli
    
    # Install additional development PHP extensions
    local dev_php_extensions=(
        "php8.3-xdebug"
        "php8.3-dev"
    )
    
    for ext in "${dev_php_extensions[@]}"; do
        apt-get install -y "$ext" || log "WARN" "Failed to install dev extension: $ext"
    done
    
    # Configure Xdebug
    cat > /etc/php/8.3/fpm/conf.d/20-xdebug.ini << 'EOF'
zend_extension=xdebug.so
xdebug.mode=debug,develop
xdebug.start_with_request=trigger
xdebug.client_host=localhost
xdebug.client_port=9003
xdebug.idekey=VSCODE
EOF
    
    systemctl restart php8.3-fpm
    
    log "INFO" "Developer tools installation completed"
}

configure_librenms_extensions() {
    log "INFO" "Configuring LibreNMS extensions..."
    
    cd "$LIBRENMS_DIR"
    
    # Enable all applications by default
    log "INFO" "Enabling LibreNMS applications..."
    
    # Configure Redis caching
    sudo -u "$LIBRENMS_USER" ./lnms config:set cache.driver redis
    sudo -u "$LIBRENMS_USER" ./lnms config:set session.driver redis
    
    # Configure RRDCached
    sudo -u "$LIBRENMS_USER" ./lnms config:set rrdcached.enabled true
    sudo -u "$LIBRENMS_USER" ./lnms config:set rrdcached.host 127.0.0.1
    sudo -u "$LIBRENMS_USER" ./lnms config:set rrdcached.port 42217
    
    # Enable auto-discovery for applications
    sudo -u "$LIBRENMS_USER" ./lnms config:set discovery_modules.applications true
    
    # Configure application discovery
    local applications=(
        apache asterisk backupninja bind certificate chronyd dhcp-stats
        docker entropy fail2ban fbsd-nfs-client fbsd-nfs-server freeradius
        gpsd mailcow-postfix mdadm memcached mysql nginx ntp-client
        ntp-server opensearch postfix postgres powerdns proxmox puppet-agent
        pureftpd redis seafile smart squid suricata unbound ups zfs
        pihole nextcloud gitlab elasticsearch influxdb grafana
    )
    
    log "INFO" "Enabling application modules..."
    for app in "${applications[@]}"; do
        sudo -u "$LIBRENMS_USER" ./lnms config:set apps."$app".enabled true || log "WARN" "Failed to enable app: $app"
    done
    
    # Configure distributed polling if Redis is available
    if systemctl is-active --quiet redis-server; then
        sudo -u "$LIBRENMS_USER" ./lnms config:set distributed_poller true
        sudo -u "$LIBRENMS_USER" ./lnms config:set distributed_poller_group 0
    fi
    
    # Enable additional features
    sudo -u "$LIBRENMS_USER" ./lnms config:set enable_billing true
    sudo -u "$LIBRENMS_USER" ./lnms config:set enable_services true
    sudo -u "$LIBRENMS_USER" ./lnms config:set enable_syslog true
    sudo -u "$LIBRENMS_USER" ./lnms config:set auth_mechanism mysql
    
    # Configure alerting
    sudo -u "$LIBRENMS_USER" ./lnms config:set alert.default_only false
    sudo -u "$LIBRENMS_USER" ./lnms config:set alert.syscontact true
    
    # Enable API
    sudo -u "$LIBRENMS_USER" ./lnms config:set api.enabled true
    
    log "INFO" "LibreNMS extensions configuration completed"
}

install_monitoring_dashboards() {
    log "INFO" "Setting up monitoring dashboards..."
    
    # Install Grafana dashboards for LibreNMS
    if systemctl is-active --quiet grafana-server; then
        log "INFO" "Configuring Grafana dashboards..."
        
        # Wait for Grafana to be ready
        sleep 10
        
        # Add InfluxDB data source
        curl -X POST http://admin:admin@localhost:3000/api/datasources \
            -H "Content-Type: application/json" \
            -d '{
                "name": "LibreNMS-InfluxDB",
                "type": "influxdb",
                "url": "http://localhost:8086",
                "database": "librenms",
                "access": "proxy"
            }' || log "WARN" "Failed to add Grafana data source"
        
        # Download LibreNMS Grafana dashboards
        mkdir -p /tmp/grafana-dashboards
        cd /tmp/grafana-dashboards
        
        # Download community dashboards
        wget -O device-overview.json "https://grafana.com/api/dashboards/1955/revisions/1/download" || true
        
        # Import dashboards
        if [[ -f "device-overview.json" ]]; then
            curl -X POST http://admin:admin@localhost:3000/api/dashboards/db \
                -H "Content-Type: application/json" \
                -d @device-overview.json || log "WARN" "Failed to import dashboard"
        fi
        
        cd - >/dev/null
        rm -rf /tmp/grafana-dashboards
    fi
    
    log "INFO" "Monitoring dashboards setup completed"
}

create_addon_management_script() {
    log "INFO" "Creating addon management script..."
    
    cat > /usr/local/bin/librenms-addons << 'EOF'
#!/bin/bash
# LibreNMS Addon Management Script

case "$1" in
    "list")
        echo "Installed LibreNMS Addons:"
        echo "========================="
        systemctl list-units --type=service | grep -E "(oxidized|grafana|influxdb|redis|smokeping)" || echo "No additional services found"
        
        echo ""
        echo "Available SNMP Extensions:"
        ls -la /usr/local/bin/ | grep -E "(stats|check)" | wc -l
        echo "extensions installed"
        
        echo ""
        echo "Agent Scripts:"
        ls -la /usr/lib/check_mk_agent/local/ | grep -v "^total" | wc -l
        echo "agent scripts installed"
        ;;
    "status")
        echo "LibreNMS Addon Status:"
        echo "====================="
        systemctl status oxidized grafana-server influxdb redis-server --no-pager -l
        ;;
    "restart")
        echo "Restarting LibreNMS addons..."
        systemctl restart oxidized grafana-server influxdb redis-server nginx php8.3-fpm mariadb
        echo "All services restarted"
        ;;
    "backup")
        /usr/local/bin/librenms-backup
        ;;
    *)
        echo "Usage: $0 {list|status|restart|backup}"
        echo ""
        echo "  list    - Show installed addons"
        echo "  status  - Show addon service status"  
        echo "  restart - Restart all addon services"
        echo "  backup  - Run backup procedure"
        ;;
esac
EOF
    
    chmod +x /usr/local/bin/librenms-addons
    
    log "INFO" "Addon management script created"
}

################################################################################
# Main Installation Flow
################################################################################

show_usage() {
    cat << EOF
LibreNMS Addons and Extensions Installer

Usage: $0 [OPTIONS]

OPTIONS:
    --dev                   Install development addons
    --prod                  Install production addons  
    --verbose, -v           Enable verbose logging
    --help, -h              Show this help message

This script installs all possible plugins, addons, and extensions for LibreNMS:
- LibreNMS Agent with all monitoring scripts
- Oxidized for configuration backup
- Metric storage backends (InfluxDB, Redis, Grafana)
- Security addons (fail2ban, ClamAV)
- Performance tools (RRDCached, monitoring utilities)
- Application monitoring for 80+ applications
- Network discovery and analysis tools
- Backup solutions
- Developer tools (dev mode only)

EOF
}

main() {
    log "INFO" "Starting LibreNMS addons installation..."
    
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
            --verbose|-v)
                VERBOSE=true
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
    
    # Auto-detect deployment type if not specified
    if [[ -z "$DEPLOYMENT_TYPE" ]]; then
        if [[ -f "$LIBRENMS_DIR/.env" ]]; then
            if grep -q "APP_ENV=local\|APP_DEBUG=true" "$LIBRENMS_DIR/.env"; then
                DEPLOYMENT_TYPE="development"
            else
                DEPLOYMENT_TYPE="production"
            fi
        else
            DEPLOYMENT_TYPE="production"  # Default to production
        fi
    fi
    
    log "INFO" "Installing addons for $DEPLOYMENT_TYPE environment"
    
    # Check if LibreNMS is installed
    if [[ ! -d "$LIBRENMS_DIR" ]]; then
        error_exit "LibreNMS not found at $LIBRENMS_DIR. Please run the main installer first."
    fi
    
    # Check privileges
    if [[ $EUID -ne 0 ]]; then
        error_exit "This script must be run as root or with sudo"
    fi
    
    # Update package list
    apt-get update
    
    # Install all addon categories
    install_optional_php_extensions
    install_librenms_agent
    install_librenms_agent_scripts
    configure_advanced_snmp
    install_metric_storage_backends
    install_monitoring_tools
    install_security_addons
    install_performance_tools
    install_application_monitoring
    install_network_discovery_tools
    install_database_extensions
    install_backup_solutions
    install_oxidized
    configure_librenms_extensions
    install_monitoring_dashboards
    install_developer_tools  # Only installs if dev mode
    create_addon_management_script
    
    log "INFO" "✅ All addons and extensions installation completed!"
    
    echo ""
    echo "🎉 LibreNMS Addons Installation Complete!"
    echo ""
    echo "Installed Components:"
    echo "===================="
    echo "📊 Monitoring:"
    echo "  - LibreNMS Agent with 80+ monitoring scripts"
    echo "  - SNMP extensions for all major applications"
    echo "  - Performance monitoring tools"
    echo ""
    echo "📈 Metrics & Analytics:"
    echo "  - InfluxDB time series database"
    echo "  - Grafana dashboards"  
    echo "  - Redis caching and sessions"
    echo "  - RRDCached for performance"
    echo ""
    echo "🔐 Security:"
    echo "  - fail2ban intrusion prevention"
    echo "  $(if [[ "$DEPLOYMENT_TYPE" == "production" ]]; then echo "- ClamAV malware scanning"; fi)"
    echo "  - Enhanced security monitoring"
    echo ""
    echo "💾 Backup & Maintenance:"
    echo "  - Oxidized configuration backup"
    echo "  - Automated backup scripts"
    echo "  - Database optimization tools"
    echo ""
    echo "🛠️ Management:"
    echo "  - Addon management script: /usr/local/bin/librenms-addons"
    echo "  - Backup script: /usr/local/bin/librenms-backup"
    echo ""
    echo "Next Steps:"
    echo "1. Run: librenms-addons list"
    echo "2. Configure applications in LibreNMS web interface"
    echo "3. Access Grafana at: http://your-server:3000 (admin/admin)"
    echo "4. Access Oxidized at: http://your-server:8888"
    echo ""
}

# Run main function with all arguments
main "$@"
