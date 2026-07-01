# Mdadm

Monitors Linux `mdadm` arrays from the LibreNMS agent. See the [Linux md documentation](https://docs.kernel.org/admin-guide/md.html) for kernel RAID details.

Collected data includes array health, operation state, disk counts, sync progress, mismatch count, member disk health/errors, and disk I/O graphs. Current agent output is stored in database tables for the mdadm drive/app pages and drive overview panel.

!!! note
    The current (v3) agent is served over SNMP as a custom MIB
    ([`MDADM-MIB`](https://github.com/librenms/librenms/blob/master/mibs/librenms/MDADM-MIB),
    enterprise OID `.1.3.6.1.4.1.60652.101`) via `pass_persist`. The older v1/v2
    agents are still supported through the legacy JSON `extend` and receive a
    reduced feature set - see [Agent Version Support](#agent-version-support).

## Prerequisites

The v3 agent is a self-contained Python 3 `pass_persist` responder. It requires
`snmpd`, `python3`, `mdadm`, and `udev` (it calls `udevadm` for drive
model/serial). `sudo` is also needed: the agent runs `mdadm --detail` and
`mdadm -E` as root (see [sudo access](#sudo-access)). PyYAML is optional - if it
is not installed the agent falls back to a minimal config parser.

=== "Debian/Ubuntu"

    ```bash
    sudo apt install snmpd python3 mdadm udev sudo
    ```

=== "RHEL/RockyLinux"

    ```bash
    sudo dnf install net-snmp python3 mdadm udev sudo
    ```

## SNMP Pass Persist (v3, current)

The v3 agent is a `pass_persist` responder: `snmpd` keeps it running and forwards
SNMP requests for the `MDADM-MIB` subtree to it directly. There is no cache file
and no `extend` entry - LibreNMS reads the array, device, health, and sync tables
straight from the MIB.

1. Download the agent and make it executable.

    ```bash
    sudo install -d -m 0755 /usr/local/lib/snmpd
    sudo curl -fsSL https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mdadm/mdadm -o /usr/local/lib/snmpd/mdadm
    sudo chmod 0755 /usr/local/lib/snmpd/mdadm
    ```

2. Grant the `snmpd` user passwordless `sudo` for `mdadm` (see
   [sudo access](#sudo-access) below).

3. Optional: configure the agent in `/etc/snmp/extension/mdadm.yaml`.

    ```bash
    sudo install -d -m 0755 /etc/snmp/extension
    sudo curl -fsSL https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mdadm/mdadm.yaml.example -o /etc/snmp/extension/mdadm.yaml
    ```

4. Register the agent with `snmpd` as a `pass_persist` responder for the
   `MDADM-MIB` enterprise OID, then restart `snmpd`.

    ```bash
    echo 'pass_persist .1.3.6.1.4.1.60652.101 /usr/local/lib/snmpd/mdadm' | sudo tee -a /etc/snmp/snmpd.conf.d/librenms.conf
    sudo service snmpd restart
    ```

    Ensure `/etc/snmp/snmpd.conf` includes `/etc/snmp/snmpd.conf.d`:

    ```bash
    echo 'includeDir /etc/snmp/snmpd.conf.d' | sudo tee -a /etc/snmp/snmpd.conf
    ```

5. Verify the MIB responds. Point `snmpwalk` at the `MDADM-MIB` shipped with
   LibreNMS (under `mibs/librenms`):

    ```bash
    snmpwalk -v2c -c public -M +/opt/librenms/mibs/librenms -m MDADM-MIB localhost MDADM-MIB::mdadmMIB
    ```

    A bare numeric check that does not need the MIB loaded:

    ```bash
    snmpget -v2c -c public localhost .1.3.6.1.4.1.60652.101.1.1.2.0
    ```

    `mdadmVersion.0` (the OID above) returns the agent format version. A value
    of `3` or higher confirms the pass_persist agent is serving the MIB.

The application is auto-discovered: because a `pass_persist` agent has no
`nsExtend` entry, LibreNMS probes `MDADM-MIB::mdadmVersion.0` directly during
discovery and enables the app when the agent answers.

### sudo access

The agent reads most data straight from sysfs, but it calls `sudo -n mdadm
--detail` and `sudo -n mdadm -E` (which need root) to fill in the array
UUID/name, device counts, and per-member superblock UUID/event counters. Because
`snmpd` runs the agent as an unprivileged user, that user needs passwordless
`sudo` for `mdadm`.

Create `/etc/sudoers.d/mdadm` (replace `Debian-snmp` with `snmp` on RHEL-like
systems):

```bash
echo 'Debian-snmp ALL=(root) NOPASSWD: /sbin/mdadm, /usr/sbin/mdadm' | sudo tee /etc/sudoers.d/mdadm
sudo chmod 0440 /etc/sudoers.d/mdadm
```

Without this, the agent still serves the MIB - arrays are populated from sysfs
only, with the enriched fields left blank - and reports a partial failure
(`mdadmError` = 6) whose `mdadmErrorString` reads `sudo mdadm access denied -
install the sudoers rule ...`. The agent detects the denial once and skips
further `sudo` calls for that cycle, so it does not stall on every array.

### Agent options

The agent refreshes its data in-process at most once per `ttl` seconds (default
60), so SNMP polls never block on `mdadm`/sysfs and there is no cache file to
manage. Behaviour is controlled by the optional YAML config
(`/etc/snmp/extension/mdadm.yaml`) and/or command-line flags; CLI flags take
precedence, then the config file, then built-in defaults.

| Config key | CLI flag | Default | Purpose |
|---|---|---|---|
| `ttl` | `--ttl` | `60` | Seconds between in-process data refreshes |
| `log_level` | `--log-level` | `WARNING` | `DEBUG`, `VERBOSE`, `INFO`, `NOTICE`, `WARNING`, `ERROR` |
| `log_file` | `--log-file` | `mdadm.log` beside the script | Agent log path |
| `devices` | - | auto | List of array names to collect, instead of auto-discovering all `/sys/block/*/md`. Empty/unset means auto-discover |

Pass flags through the `pass_persist` line, e.g. to raise the refresh interval:

```conf
pass_persist .1.3.6.1.4.1.60652.101 /usr/local/lib/snmpd/mdadm --ttl 300
```

## Legacy v1/v2 (JSON extend)

The v1 and v2 agents predate the MIB and are served through the NET-SNMP JSON
`extend` mechanism. LibreNMS auto-detects them by falling back to the JSON extend
when no `MDADM-MIB` is served on the host. New installs should use v3; the steps
below are retained for hosts still running the older agent.

1. Install the extension script and create the config/cache directories.

    ```bash
    sudo install -d -m 0755 /usr/local/lib/snmpd /etc/snmp/extension /run/snmp/extension /etc/snmp/snmpd.conf.d
    sudo curl -fsSL https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mdadm/mdadm -o /usr/local/lib/snmpd/mdadm
    sudo chmod 0755 /usr/local/lib/snmpd/mdadm
    ```

2. Create `/etc/snmp/extension/mdadm.yaml`.

    ```bash
    sudo curl -fsSL https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mdadm/mdadm.yaml.example -o /etc/snmp/extension/mdadm.yaml
    ```

3. Add the SNMP extend entry and refresh the cache every 5 minutes.

    ```bash
    echo 'extend mdadm /bin/cat /run/snmp/extension/mdadm' | sudo tee -a /etc/snmp/snmpd.conf.d/librenms.conf
    ```

    === "systemd"

        ```bash
        sudo curl -fsSL https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/common/librenms-snmp-extension@.timer -o /etc/systemd/system/librenms-snmp-extension@.timer
        sudo curl -fsSL https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/common/librenms-snmp-extension@.service -o /etc/systemd/system/librenms-snmp-extension@.service
        sudo install -d -m 0755 /etc/systemd/system/librenms-snmp-extension@mdadm.service.d
        sudo curl -fsSL https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mdadm/systemd-override.conf -o /etc/systemd/system/librenms-snmp-extension@mdadm.service.d/override.conf
        sudo systemctl daemon-reload
        sudo systemctl enable --now librenms-snmp-extension@mdadm.timer
        ```

    === "cron"

        Use `snmp` instead of `Debian-snmp` on RHEL-like systems.

        ```bash
        sudo curl -fsSL https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/mdadm/librenms-snmp-extension-mdadm.cron -o /etc/cron.d/librenms-snmp-extension-mdadm
        ```

4. Ensure `/etc/snmp/snmpd.conf` includes `/etc/snmp/snmpd.conf.d`, then restart
   `snmpd`, and verify the extend output:

    ```bash
    echo 'includeDir /etc/snmp/snmpd.conf.d' | sudo tee -a /etc/snmp/snmpd.conf
    sudo service snmpd restart
    snmpwalk -v2c -c public localhost NET-SNMP-EXTEND-MIB::nsExtendOutputFull."mdadm"
    ```


## Agent Version Support

The feature set depends on the agent version. LibreNMS supports all versions but
selects the transport automatically: v3 over `MDADM-MIB` (pass_persist), v1/v2
over the JSON extend.

### Version 3 (current)

Full support, served over `MDADM-MIB`: array and drive database records,
per-array health/operation/mismatch sensors, per-drive health/error sensors,
sync progress with byte counters and speed limits, and v3 graphs.

### Version 2 (legacy)

Partial support over the JSON extend. Discovery creates database records and
health/operation/drive-presence sensors. Graphs use the legacy RRD format (same
as v1).

| Field | v2 | v3 |
|---|---|---|
| Array name | ✓ | ✓ |
| RAID level | ✓ | ✓ |
| State | ✓ | ✓ |
| Disk counts (total, active, spare, failed, working) | inferred | ✓ |
| Degraded status | ✓ | ✓  |
| Sync action | ✓ | ✓ |
| Sync speed | ✓ | ✓  |
| Sync completion % | ✓  | ✓ |
| Sync byte counters (done/total) | - | ✓ |
| Sync speed min/max | - | ✓ |
| Last sync action | - | ✓ |
| Array size | ✓  | ✓  |
| Array UUID | synthetic (`v2:<name>`) | ✓ |
| User-assigned array name | - | ✓ |
| Metadata version | - | ✓ |
| Consistency policy | - | ✓ |
| Chunk size | - | ✓ |
| Mismatch count | - | ✓ |
| Per-drive slot, size, model, serial | - | ✓ |
| Per-drive state flags and errors | - | ✓ |
| Sensors (health, operation, mismatch, drive health) | partial (no mismatch) | ✓ |
| Error reporting | ✓ (`error` field; 1 = jq missing, 2 = no arrays) | See below |

Disk counts are inferred from the payload rather than reported directly:

- `hotspare_count` is recomputed as `max(0, slave_count − disc_count)`. The agent field can be negative when a drive is physically removed from sysfs before the agent runs.
- `failed_drives` = `len(missing_devices_list)` + removed count, where removed = `max(0, disc_count − slave_count)`.
- `active_devices` = `disc_count − hotspare − failed`, `working_devices` = `disc_count − failed`.
- The `degraded` boolean flag is reflected in the array health sensor (0 = Healthy, 1 = Degraded) but is not stored as a numeric count in the database; the Disk Counts panel omits it for v1/v2 arrays.

**Removed drives:** when a drive is physically removed it disappears from sysfs and is absent from both `device_list` and `missing_devices_list`. LibreNMS detects it via the count difference and marks the drive sensor as **Unknown** on the next poll cycle. The DB record and sensor are cleaned up on the next discovery run.

## Agent Error Codes

Because the v3 agent is a resident `pass_persist` process, a process exit code
never reaches LibreNMS - snmpd would just respawn it. Instead the agent reports
errors **in-band** through `MDADM-MIB::mdadmError` (numeric) and
`mdadmErrorString` (human-readable), and adjusts what it serves accordingly:

- **Cleanup** codes serve *empty* tables, so LibreNMS prunes any sensors and DB
  records left from a previous run.
- **Skip** codes preserve the last good data and only raise the error scalar, so
  a transient problem does not wipe existing sensors.
- **Partial** still serves the data; the error string lists the affected
  arrays/devices.

The legacy v1/v2 extend reports the same numeric values via its JSON `error`
field. LibreNMS acts on them as follows:

| Code | Constant                  | Trigger                                          | Agent serves / LibreNMS action               |
|------|---------------------------|--------------------------------------------------|----------------------------------------------|
| 0    | `EXIT_SUCCESS`            | All arrays collected cleanly                     | Normal processing                            |
| 1    | `EXIT_DEPENDENCY_MISSING` | `mdadm` binary not in `$PATH`                   | Cleanup - empty tables; sensors/DB removed   |
| 2    | `EXIT_NO_ARRAYS`          | Auto-discovery found no arrays                   | Cleanup - empty tables; sensors/DB removed   |
| 3    | `EXIT_PERMISSION_DENIED`  | `/sys/block` unreadable                          | Skip - last good data kept; error flagged    |
| 5    | `EXIT_CONFIG_ERROR`       | Configured device entry missing `name` field     | Skip - last good data kept; error flagged    |
| 6    | `EXIT_PARTIAL_FAILURE`    | Some arrays/devices had read errors, or `sudo mdadm` was denied (data still served) | Normal processing - data present, error string lists what failed |
| 7    | `EXIT_NO_CONFIGURED_DEVICES` | Config listed devices but none exist in sysfs | Cleanup - empty tables; sensors/DB removed   |

Code `4` (`EXIT_OUTPUT_WRITE_FAILURE`) from the one-shot script convention has no
analogue in pass_persist mode - there is no output file - so the agent never
emits it.
