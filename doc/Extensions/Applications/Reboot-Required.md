## Reboot Required

Monitors whether a host requires a reboot to apply a pending kernel or
library update. Detects the distro family automatically via
`/etc/os-release` and picks the appropriate check:

- Debian/Ubuntu: existence of `/var/run/reboot-required`, which
  `update-notifier-common` creates when a pending upgrade requires a
  restart.
- RHEL/CentOS/Rocky/Alma/Fedora/Amazon Linux: `needs-restarting -r`
  (from `yum-utils`/`dnf-utils`).
- SUSE/SLES/openSUSE: `zypper needs-rebooting`.
- Arch Linux: compares the running kernel against
  `/usr/lib/modules/$(uname -r)`, since Arch has no official tool for
  this.

On an unsupported distro, or when the expected detection tool is
missing, the script exits non-zero with no output, which surfaces as
a poll error rather than a silent false "no reboot needed".

### SNMP Extend

1. Fetch the script in question and make it executable.

```bash
wget https://github.com/librenms/librenms-agent/raw/master/snmp/reboot-required -O /etc/snmp/reboot-required
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

- On Debian/Ubuntu, requires the `update-notifier-common` package
  (installed by default).
- On RHEL-family hosts, requires `yum-utils`/`dnf-utils` for
  `needs-restarting`.
- The graphed value is `0` (no reboot needed) or `1` (reboot needed).
