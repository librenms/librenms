## Reboot Required

Monitors whether a Debian-based host requires a reboot (e.g. after a kernel
or libc upgrade) by checking for the existence of `/var/run/reboot-required`,
which the `update-notifier-common` package creates when a pending upgrade
requires a restart.

### SNMP Extend

1. Create the extend script on the monitored host.

```bash
cat > /etc/snmp/reboot-required << 'EOF'
#!/usr/bin/env bash
[ -f /var/run/reboot-required ] && echo "1" || echo "0"
EOF
chmod +x /etc/snmp/reboot-required
```

2. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

```bash
extend reboot-required /etc/snmp/reboot-required
```

3. Restart snmpd on your host.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

### Notes

- Requires the `update-notifier-common` package on the monitored host
  (installed by default on Debian and Ubuntu).
- The graphed value is `0` (no reboot needed) or `1` (reboot needed).
