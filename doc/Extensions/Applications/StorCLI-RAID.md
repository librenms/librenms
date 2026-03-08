# StorCLI RAID (storraid)

Monitors MegaRAID RAID controllers via the StorCLI/storcli64 utility.

Collects:

- Controller health, firmware version, memory, temperature & BBU/CacheVault status
- Virtual disk (RAID array) state and rebuild progress
- Physical disk state, error counts, S.M.A.R.T alerts, predictive failure and temperature

Tested against storcli 007.3603.0000.0000 on:

- AVAGO MegaRAID SAS 9361-8i (RAID controller with VDs and PDs)
- SAS9300-8e (HBA mode, no PDs, no VDs — handled gracefully)

## How it works

storcli64 takes 4–6 seconds to poll all controllers and disks. Calling the agent
directly from snmpd would block every SNMP walk, causing LibreNMS graph gaps and
SNMP timeouts.

Instead, a **cron job** runs the agent every 5 minutes (offset 1 minute ahead of the
LibreNMS poller) and writes the result to a file. 
Snmpd serves that file via `cat` — the response is instantaneous.

```
cron (every 5 min)                  snmpd
  └─ /etc/snmp/storraid.py       ┌─ extend storraid /bin/cat ...
       ├─ runs storcli calls     │ (instant file read)
       └─ writes JSON to file ───┘
```

Data is at most ~1 minutes old, in sync with the LibreNMS default 300 s polling
interval.

## SNMP Extend

### 1. Install StorCLI on the monitored host

Download `storcli64` from the [Broadcom support site](https://www.broadcom.com/support/download-search) and install it:

```bash
cp storcli64 /usr/local/sbin/storcli64
chmod +x /usr/local/sbin/storcli64
storcli64 /call show   # verify it works
```

### 2. Install the agent script

```bash
cp storraid.py /etc/snmp/storraid.py
chmod +x /etc/snmp/storraid.py

# Test as root — should print a JSON envelope
/etc/snmp/storraid.py | base64 --decode | zcat
```

Expected output (abbreviated):

```json
{"error": 0, "errorString": "", "version": "1", "data": {
  "version": "1", "application": "storraid", "timestamp": [...],
}}
```

### 3. Set up the cron job

Create `/etc/cron.d/storraid`:

```
# StorCLI RAID monitor — runs every 5 minutes, at minute 4 of each window.
# The schedule 4,9,14,...,59 fires once per 5-minute block (12 times/hour),
# always 1 minute ahead of LibreNMS's default */5 poller so data is fresh
# on every poll. Using an explicit list instead of "4/5" ensures this works
# on all cron implementations (Vixie cron, cronie, dcron, etc.).

4,9,14,19,24,29,34,39,44,49,54,59 * * * * root /etc/snmp/storraid.py > storraid.tmp; mv -f storraid.tmp /var/run/storraid.json
```

The atomic write (`tmp` + `mv`) ensures snmpd never reads a partial file mid-write.

Seed the output file immediately (don't wait for the first cron tick):

```bash
/etc/snmp/storraid.py > /var/run/storraid.json
```

### 4. Configure snmpd

Add to `/etc/snmp/snmpd.conf`:

```
extend storraid /bin/cat /var/run/storraid.json
```

> **Note:** The agent itself runs as root via cron and writes to `/var/run/`.
> No sudo configuration is needed for snmpd.

Restart snmpd:

```bash
systemctl restart snmpd
```

Verify the extend is reachable and responds instantly(could be run from librenms):

```bash
time snmpwalk -v2c -c COMMUNITY HOST  .1.3.6.1.4.1.8072.1.3.2
```

### 5. Enable in LibreNMS and poll

Enable the **StorCLI RAID** application on the device via the Apps tab, then poll:

```bash
sudo -u librenms /opt/librenms/lnms device:poll -m applications HOSTNAME
```

Expected poller output:

```
storraid: OK — X controllers, Y VDs, Z PDs, severity=0
```

## Graphs

The following graphs are available on the device Apps tab and the global Apps page:

| Graph | Description |
|-------|-------------|
| Component Status | OK/Warn/Crit counts for controllers, VDs and PDs over time |
| Temperatures | ROC temperature per controller and drive temperature per physical disk |
| Physical Disk Errors | Media errors, other errors and predictive failure count per disk |

## Severity Reference

| Severity | Value | Meaning |
|----------|-------|---------|
| OK | 0 | Healthy |
| WARN | 1 | Degraded / attention needed |
| CRIT | 2 | Failed / data at risk |

### Controllers & BBU/CacheVault

| State | Severity |
|-------|----------|
| Optimal | OK |
| Partially Degraded / Needs Attention | WARN |
| Degraded / Failed / Offline | CRIT |
| BBU Learning / Charging / Absent | WARN |
| BBU Failed / Degraded | CRIT |

### Virtual Disks

| State | Severity |
|-------|----------|
| Optl (Optimal) / Check / BkgdI | OK |
| Pdgd (Partially Degraded) / Recov / Init | WARN |
| Dgrd (Degraded) / Offln (Offline) | CRIT |

### Physical Disks

| State | Severity |
|-------|----------|
| Onln / UGood / HSP / GHS / DHS / Cfgd / Shld | OK |
| Rbld (Rebuilding) | WARN |
| Offln / Failed / Missing | CRIT |
| Any media errors > 0 | CRIT |
| S.M.A.R.T alert active | CRIT |
| Other errors > 5 or predictive failure flagged | WARN |

## Troubleshooting

**Agent returns no output or error:**

```bash
# Run manually as root
/etc/snmp/storraid.py

# Check the output file exists and is recent
ls -la /var/run/storraid.json
```

**storcli not found:**

```bash
find / -name "storcli*" -o -name "MegaCli*" 2>/dev/null
```

Add the path to `STORCLI_PATHS` at the top of `/etc/snmp/storraid.py`.

**Graphs not appearing:**

Run a manual poll to populate RRDs:

```bash
sudo -u librenms /opt/librenms/lnms device:poll -m applications HOSTNAME 
```

**Output file not updating:**

```bash
# Check cron daemon is running
systemctl status cron

# Run agent manually to test
/etc/snmp/storraid.py > /var/run/storraid.json && echo OK
```
