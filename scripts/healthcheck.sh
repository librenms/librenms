#!/bin/bash
################################################################################
# LibreNMS Health Check Script
# Validates system health and LibreNMS functionality
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

# Counters
TOTAL_CHECKS=0
PASSED_CHECKS=0
FAILED_CHECKS=0
WARNING_CHECKS=0

################################################################################
# Utility Functions
################################################################################

log_info() {
    echo -e "${GREEN}[INFO]${NC}  $*"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC}  $*"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $*"
}

check_result() {
    local status="$1"
    local message="$2"
    
    ((TOTAL_CHECKS++))
    
    case "$status" in
        "PASS")
            echo -e "  ${GREEN}✓${NC} $message"
            ((PASSED_CHECKS++))
            ;;
        "WARN")
            echo -e "  ${YELLOW}⚠${NC} $message"
            ((WARNING_CHECKS++))
            ;;
        "FAIL")
            echo -e "  ${RED}✗${NC} $message"
            ((FAILED_CHECKS++))
            ;;
    esac
}

################################################################################
# Health Checks
################################################################################

check_system_requirements() {
    echo ""
    echo "System Requirements:"
    echo "==================="
    
    # Check if running as root
    if [[ $EUID -eq 0 ]]; then
        check_result "PASS" "Running with appropriate privileges"
    else
        check_result "FAIL" "Script should be run as root or with sudo"
    fi
    
    # Check memory
    local mem_gb=$(awk '/MemTotal/ {printf "%.1f", $2/1024/1024}' /proc/meminfo)
    if (( $(echo "$mem_gb >= 2.0" | bc -l) )); then
        check_result "PASS" "Memory: ${mem_gb}GB (>= 2GB required)"
    else
        check_result "WARN" "Memory: ${mem_gb}GB (< 2GB recommended)"
    fi
    
    # Check disk space
    local disk_usage=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
    local disk_avail=$(df -h / | awk 'NR==2 {print $4}')
    if [[ $disk_usage -lt 80 ]]; then
        check_result "PASS" "Disk usage: ${disk_usage}% (${disk_avail} available)"
    elif [[ $disk_usage -lt 90 ]]; then
        check_result "WARN" "Disk usage: ${disk_usage}% (${disk_avail} available)"
    else
        check_result "FAIL" "Disk usage: ${disk_usage}% (critically low space)"
    fi
    
    # Check load average
    local load1=$(uptime | awk -F'load average:' '{print $2}' | awk -F, '{print $1}' | xargs)
    local cpu_cores=$(nproc)
    if (( $(echo "$load1 <= $cpu_cores" | bc -l) )); then
        check_result "PASS" "Load average: $load1 (${cpu_cores} cores)"
    else
        check_result "WARN" "Load average: $load1 (high load on ${cpu_cores} cores)"
    fi
}

check_services() {
    echo ""
    echo "System Services:"
    echo "================"
    
    local services=("nginx" "php8.3-fpm" "mariadb" "snmpd")
    
    for service in "${services[@]}"; do
        if systemctl is-active --quiet "$service"; then
            check_result "PASS" "$service is running"
        else
            check_result "FAIL" "$service is not running"
        fi
    done
    
    # Check if services are enabled
    for service in "${services[@]}"; do
        if systemctl is-enabled --quiet "$service"; then
            check_result "PASS" "$service is enabled on boot"
        else
            check_result "WARN" "$service is not enabled on boot"
        fi
    done
}

check_network() {
    echo ""
    echo "Network Connectivity:"
    echo "===================="
    
    # Test internet connectivity
    if ping -c 1 -W 5 8.8.8.8 >/dev/null 2>&1; then
        check_result "PASS" "Internet connectivity available"
    else
        check_result "FAIL" "No internet connectivity"
    fi
    
    # Test DNS resolution
    if nslookup github.com >/dev/null 2>&1; then
        check_result "PASS" "DNS resolution working"
    else
        check_result "FAIL" "DNS resolution failed"
    fi
    
    # Check if ports are listening
    local ports=(80 443 3306)
    for port in "${ports[@]}"; do
        if netstat -tuln 2>/dev/null | grep -q ":$port "; then
            check_result "PASS" "Port $port is listening"
        else
            check_result "WARN" "Port $port is not listening"
        fi
    done
}

check_database() {
    echo ""
    echo "Database Status:"
    echo "================"
    
    # Check if MariaDB is accessible
    if mysql -u root -e "SELECT 1;" >/dev/null 2>&1; then
        check_result "PASS" "MariaDB is accessible"
        
        # Check LibreNMS database
        if mysql -u root -e "USE librenms; SELECT 1;" >/dev/null 2>&1; then
            check_result "PASS" "LibreNMS database exists"
        else
            check_result "FAIL" "LibreNMS database not found"
        fi
        
        # Check database size
        local db_size=$(mysql -u root -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'DB Size in MB' FROM information_schema.tables WHERE table_schema='librenms';" -s -N 2>/dev/null || echo "0")
        check_result "PASS" "Database size: ${db_size} MB"
        
    else
        check_result "FAIL" "Cannot connect to MariaDB"
    fi
}

check_librenms() {
    echo ""
    echo "LibreNMS Status:"
    echo "================"
    
    # Check if LibreNMS directory exists
    if [[ -d "$LIBRENMS_DIR" ]]; then
        check_result "PASS" "LibreNMS directory exists"
    else
        check_result "FAIL" "LibreNMS directory not found"
        return
    fi
    
    # Check LibreNMS user
    if id "$LIBRENMS_USER" &>/dev/null; then
        check_result "PASS" "LibreNMS user exists"
    else
        check_result "FAIL" "LibreNMS user not found"
    fi
    
    # Check file permissions
    if [[ -r "$LIBRENMS_DIR/.env" ]]; then
        local env_perms=$(stat -c "%a" "$LIBRENMS_DIR/.env")
        if [[ "$env_perms" == "640" ]]; then
            check_result "PASS" ".env file permissions correct (640)"
        else
            check_result "WARN" ".env file permissions: $env_perms (should be 640)"
        fi
    else
        check_result "FAIL" ".env file not found or not readable"
    fi
    
    # Check key directories
    local key_dirs=("logs" "rrd" "storage" "bootstrap/cache")
    for dir in "${key_dirs[@]}"; do
        if [[ -d "$LIBRENMS_DIR/$dir" && -w "$LIBRENMS_DIR/$dir" ]]; then
            check_result "PASS" "$dir directory writable"
        else
            check_result "FAIL" "$dir directory not found or not writable"
        fi
    done
    
    # Check composer dependencies
    if [[ -f "$LIBRENMS_DIR/vendor/autoload.php" ]]; then
        check_result "PASS" "Composer dependencies installed"
    else
        check_result "FAIL" "Composer dependencies missing"
    fi
    
    # Run LibreNMS validation
    cd "$LIBRENMS_DIR"
    if sudo -u "$LIBRENMS_USER" ./validate.php >/dev/null 2>&1; then
        check_result "PASS" "LibreNMS validation passed"
    else
        check_result "WARN" "LibreNMS validation found issues"
    fi
}

check_web_access() {
    echo ""
    echo "Web Access:"
    echo "==========="
    
    # Get FQDN from nginx config
    local fqdn=$(grep -o 'server_name [^;]*' /etc/nginx/conf.d/librenms.conf 2>/dev/null | awk '{print $2}' || echo "localhost")
    
    # Test HTTP access
    if curl -s -I "http://$fqdn/" | grep -q "200 OK"; then
        check_result "PASS" "HTTP access working (http://$fqdn/)"
    else
        check_result "WARN" "HTTP access test failed"
    fi
    
    # Test HTTPS if configured
    if grep -q "443" /etc/nginx/conf.d/librenms.conf 2>/dev/null; then
        if curl -s -I "https://$fqdn/" | grep -q "200 OK"; then
            check_result "PASS" "HTTPS access working (https://$fqdn/)"
        else
            check_result "WARN" "HTTPS access test failed"
        fi
    fi
}

check_cron_and_scheduler() {
    echo ""
    echo "Cron and Scheduler:"
    echo "=================="
    
    # Check cron file
    if [[ -f "/etc/cron.d/librenms" ]]; then
        check_result "PASS" "Cron configuration exists"
    else
        check_result "FAIL" "Cron configuration missing"
    fi
    
    # Check scheduler timer
    if systemctl is-active --quiet librenms-scheduler.timer; then
        check_result "PASS" "Scheduler timer is running"
    else
        check_result "WARN" "Scheduler timer is not running"
    fi
    
    # Check recent cron activity
    if [[ -f "$LIBRENMS_DIR/logs/librenms.log" ]]; then
        local recent_activity=$(grep -c "$(date '+%Y-%m-%d')" "$LIBRENMS_DIR/logs/librenms.log" 2>/dev/null || echo "0")
        if [[ $recent_activity -gt 0 ]]; then
            check_result "PASS" "Recent cron activity detected ($recent_activity entries today)"
        else
            check_result "WARN" "No recent cron activity found"
        fi
    else
        check_result "WARN" "LibreNMS log file not found"
    fi
}

check_snmp() {
    echo ""
    echo "SNMP Configuration:"
    echo "=================="
    
    # Check SNMP daemon
    if systemctl is-active --quiet snmpd; then
        check_result "PASS" "SNMP daemon is running"
    else
        check_result "FAIL" "SNMP daemon is not running"
    fi
    
    # Test SNMP locally
    if snmpwalk -v2c -c public localhost 1.3.6.1.2.1.1.1.0 >/dev/null 2>&1; then
        check_result "PASS" "SNMP query to localhost successful"
    else
        check_result "WARN" "SNMP query to localhost failed (check community string)"
    fi
    
    # Check distro script
    if [[ -x "/usr/bin/distro" ]]; then
        check_result "PASS" "Distro script installed and executable"
    else
        check_result "WARN" "Distro script missing or not executable"
    fi
}

check_security() {
    echo ""
    echo "Security Status:"
    echo "==============="
    
    # Check firewall status
    if command -v ufw >/dev/null; then
        if ufw status | grep -q "Status: active"; then
            check_result "PASS" "UFW firewall is active"
        else
            check_result "WARN" "UFW firewall is not active"
        fi
    elif command -v firewall-cmd >/dev/null; then
        if firewall-cmd --state 2>/dev/null | grep -q "running"; then
            check_result "PASS" "firewalld is running"
        else
            check_result "WARN" "firewalld is not running"
        fi
    else
        check_result "WARN" "No firewall detected"
    fi
    
    # Check SSL certificate
    if [[ -d "/etc/letsencrypt/live" ]]; then
        local cert_count=$(find /etc/letsencrypt/live -name "cert.pem" | wc -l)
        if [[ $cert_count -gt 0 ]]; then
            check_result "PASS" "SSL certificate(s) found ($cert_count)"
        fi
    fi
    
    # Check for world-writable files
    local writable_files=$(find "$LIBRENMS_DIR" -type f -perm -002 2>/dev/null | wc -l)
    if [[ $writable_files -eq 0 ]]; then
        check_result "PASS" "No world-writable files found"
    else
        check_result "WARN" "$writable_files world-writable files found"
    fi
}

display_summary() {
    echo ""
    echo "Health Check Summary:"
    echo "===================="
    echo -e "Total Checks: $TOTAL_CHECKS"
    echo -e "${GREEN}Passed: $PASSED_CHECKS${NC}"
    echo -e "${YELLOW}Warnings: $WARNING_CHECKS${NC}"
    echo -e "${RED}Failed: $FAILED_CHECKS${NC}"
    echo ""
    
    if [[ $FAILED_CHECKS -eq 0 && $WARNING_CHECKS -eq 0 ]]; then
        echo -e "${GREEN}🎉 All checks passed! Your LibreNMS installation is healthy.${NC}"
        exit 0
    elif [[ $FAILED_CHECKS -eq 0 ]]; then
        echo -e "${YELLOW}⚠️  Health check completed with warnings. Review the issues above.${NC}"
        exit 1
    else
        echo -e "${RED}❌ Health check found critical issues. Please address the failures above.${NC}"
        exit 2
    fi
}

################################################################################
# Main Function
################################################################################

main() {
    echo "LibreNMS Health Check"
    echo "===================="
    echo "Timestamp: $(date)"
    echo "Hostname: $(hostname)"
    echo ""
    
    # Run all checks
    check_system_requirements
    check_services
    check_network
    check_database
    check_librenms
    check_web_access
    check_cron_and_scheduler
    check_snmp
    check_security
    
    # Display summary
    display_summary
}

# Check if bc is available for floating point comparison
if ! command -v bc >/dev/null; then
    apt-get update && apt-get install -y bc
fi

# Run main function
main "$@"
