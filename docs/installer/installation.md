# LibreNMS Automated Installation Guide

This guide covers the automated installation of LibreNMS using our custom installer script.

## Overview

The automated installer provides a complete LibreNMS setup with minimal user interaction. It supports two deployment modes and can run either interactively or in fully automated mode.

## Prerequisites

### System Requirements

**Minimum Requirements:**
- Ubuntu 22.04 LTS or 24.04 LTS
- 2GB RAM (4GB recommended)
- 20GB free disk space (50GB+ for production)
- Internet connectivity
- Root privileges (sudo access)

**Recommended for Production:**
- 8GB+ RAM
- 100GB+ SSD storage  
- 4+ CPU cores
- Dedicated network interface

### Pre-installation Checklist

1. **Fresh Ubuntu Installation**: Start with a clean Ubuntu 22.04 or 24.04 installation
2. **System Updates**: Ensure your system is up to date:
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```
3. **Network Connectivity**: Verify internet access and DNS resolution
4. **Firewall**: If using a firewall, ensure ports 80 and 443 are available
5. **Domain Name**: For production SSL, ensure your FQDN resolves to the server IP

## Installation Methods

### Method 1: One-Line Installation (Recommended)

```bash
curl -fsSL https://raw.githubusercontent.com/yourusername/librenms/main/scripts/install.sh | sudo bash
```

This method downloads and executes the installer in interactive mode.

### Method 2: Download and Run

```bash
# Download the installer
wget https://raw.githubusercontent.com/yourusername/librenms/main/scripts/install.sh
chmod +x install.sh

# Interactive installation
sudo ./install.sh
```

### Method 3: Git Clone (For Development/Customization)

```bash
# Clone the repository
git clone https://github.com/yourusername/librenms.git
cd librenms

# Run the installer
sudo ./scripts/install.sh
```

## Installation Options

### Command Line Flags

| Flag | Description | Example |
|------|-------------|---------|
| `--dev` | Development mode | `sudo ./install.sh --dev` |
| `--prod` | Production mode | `sudo ./install.sh --prod` |
| `--non-interactive` | No prompts (requires --dev or --prod) | `sudo ./install.sh --prod --non-interactive` |
| `--verbose, -v` | Detailed output | `sudo ./install.sh --dev --verbose` |
| `--quiet, -q` | Minimal output | `sudo ./install.sh --prod --quiet` |
| `--help, -h` | Show help | `./install.sh --help` |

### Deployment Modes

#### Development Mode (`--dev`)

**Best for:**
- Testing and development
- Learning LibreNMS
- Laboratory environments

**Features:**
- Debug logging enabled
- Relaxed security settings
- Higher resource limits
- Detailed error reporting
- No SSL by default
- No firewall restrictions

#### Production Mode (`--prod`)

**Best for:**
- Production deployments
- Public-facing installations
- Enterprise environments

**Features:**
- Security headers and hardening
- SSL/TLS with Let's Encrypt
- Firewall configuration
- Performance optimizations
- Minimal debug output
- Security-focused PHP settings

## Interactive Installation

When running in interactive mode, you'll be prompted for:

1. **Deployment Type**: Development or Production
2. **FQDN**: Fully qualified domain name for your LibreNMS installation
3. **Database Configuration**:
   - MySQL root password
   - LibreNMS database name
   - LibreNMS database username
   - LibreNMS database password
4. **Timezone**: Server timezone (e.g., UTC, America/New_York)
5. **SSL Configuration** (Production only):
   - Enable Let's Encrypt SSL
   - Email for certificate registration

## Non-Interactive Installation

For automation and CI/CD, use non-interactive mode:

```bash
# Development installation
sudo ./install.sh --dev --non-interactive

# Production installation  
sudo ./install.sh --prod --non-interactive
```

In non-interactive mode, defaults are used:
- FQDN: `librenms.localhost`
- Database: `librenms` / `librenms` / random password
- Timezone: `UTC`
- SSL: Disabled

## Installation Process

The installer performs these steps:

1. **System Detection**: Detects Ubuntu version and validates compatibility
2. **Privilege Check**: Verifies root access
3. **Network Validation**: Tests internet connectivity
4. **Pre-flight Checks**: Validates system resources and requirements
5. **Configuration Collection**: Interactive prompts or defaults
6. **Package Installation**: Installs required system packages
7. **Database Setup**: Configures and secures MariaDB
8. **LibreNMS Deployment**: Downloads and configures LibreNMS
9. **Web Server Configuration**: Sets up nginx and PHP-FPM
10. **SSL Setup**: Configures SSL certificates (if enabled)
11. **Service Configuration**: Configures system services
12. **Firewall Setup**: Configures security rules (production mode)
13. **Validation**: Runs post-installation checks
14. **Report Generation**: Creates installation summary

## Post-Installation

### Initial Access

1. **Web Interface**: Navigate to `http(s)://your-server-ip/`
2. **Complete Setup**: Follow the web-based setup wizard
3. **Create Admin User**: Set up your first administrative user
4. **Add First Device**: Add localhost as your first monitored device

### Verification Commands

```bash
# Check installation health
sudo /opt/librenms/scripts/healthcheck.sh

# Validate LibreNMS configuration
sudo -u librenms /opt/librenms/validate.php

# View installation report
cat /opt/librenms/install_report.txt

# Check service status
systemctl status nginx php8.3-fpm mariadb snmpd
```

### Default Locations

- **LibreNMS**: `/opt/librenms`
- **Configuration**: `/opt/librenms/.env`
- **Logs**: `/opt/librenms/logs/`
- **Web Root**: `/opt/librenms/html/`
- **Nginx Config**: `/etc/nginx/conf.d/librenms.conf`
- **PHP-FPM Pool**: `/etc/php/8.3/fpm/pool.d/librenms.conf`

## Environment Variables

For non-interactive installations, you can pre-set configuration via environment variables:

```bash
export CONFIG_FQDN="monitor.example.com"
export CONFIG_DB_ROOT_PASS="secure_root_password"
export CONFIG_DB_NAME="librenms"
export CONFIG_DB_USER="librenms"
export CONFIG_DB_PASS="secure_db_password"
export CONFIG_TIMEZONE="America/New_York"
export CONFIG_ENABLE_SSL="true"
export CONFIG_SSL_EMAIL="admin@example.com"

sudo -E ./install.sh --prod --non-interactive
```

## Installation Logs

Installation logs are available at:
- **Real-time**: Console output during installation
- **System Logs**: `/var/log/syslog` and service-specific logs
- **Installation Report**: `/opt/librenms/install_report.txt`

## Next Steps

After successful installation:

1. **Complete Web Setup**: Visit your LibreNMS URL and complete setup
2. **Add Devices**: Add network devices to monitor
3. **Configure Alerts**: Set up alerting rules and notifications
4. **Review Security**: Audit security settings for your environment
5. **Setup Backups**: Implement backup strategies
6. **Performance Tuning**: Optimize for your specific use case

## Troubleshooting

For common issues and solutions, see:
- [Troubleshooting Guide](troubleshooting.md)
- [Maintenance Guide](maintenance.md)
- LibreNMS validation: `sudo -u librenms /opt/librenms/validate.php`
- Health check: `sudo /opt/librenms/scripts/healthcheck.sh`
