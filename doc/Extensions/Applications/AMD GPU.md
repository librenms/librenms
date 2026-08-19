# AMD GPU

A small shell script that serves the power and clock of the AMD GPUs in a host
as an SNMP table.

These two never reach SNMP on their own. The net-snmp `lmSensors` MIB module
maps only the temperature, fan and voltage features of libsensors, so watts and
hertz are given no index at all and `lmMiscSensorsTable` stays empty. GPU
temperature and voltages continue to arrive through `LM-SENSORS-MIB` as before;
this script adds only what is missing.

Values are discovered as ordinary sensors and appear under Health:

| Sensor | Class | Source |
| --- | --- | --- |
| Power | power | `power1_input` (instantaneous) |
| Power (average) | power | `power1_average` |
| Clock | frequency | `freq1_input` |

Each card is one row of the table and a sensor group of its own, identified by
PCI address and, where the card is known to the libdrm ids table, by product
name.

### SNMP Pass Persist

1. Copy the shell script, amdgpu, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/amdgpu -O /etc/snmp/amdgpu
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/amdgpu
    ```

3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

    ```bash
    pass_persist .1.3.6.1.4.1.60652.101 /etc/snmp/amdgpu
    ```

4. Restart snmpd on your host

    ```bash
    systemctl restart snmpd
    ```

The sensors should be auto-discovered on the next discovery run.

The table is described by `LIBRENMS-AMDGPU-MIB`, which ships with LibreNMS and
does not have to be installed on the monitored host. Loading it where you walk
from gives the columns their names:

```bash
snmpwalk <various options depending on your setup> -M +/opt/librenms/mibs/librenms \
    -m LIBRENMS-AMDGPU-MIB <host> LIBRENMS-AMDGPU-MIB::amdGpuTable
```

An empty walk has two causes. Either snmpd is not running the script — check
that it is executable and that snmpd has been restarted since the line was
added — or no card in the host publishes any of the three values, in which case
the loop under Requirements below prints nothing either.

### Requirements

The `amdgpu` kernel driver must be loaded. Which of the three values a card
exposes under its hwmon node depends on the model and on the SMU firmware; a
card that publishes only some of them yields only those sensors. To see what
yours offers:

```bash
for dev in /sys/bus/pci/drivers/amdgpu/*:*:*.*; do
    grep . "$dev"/hwmon/hwmon*/{power1_average,power1_input,freq1_input} 2>/dev/null
done
```

A value the card does not publish has no cell in the table at all — that OID
answers `noSuchInstance` — and simply yields no sensor, rather than hiding the
rest of the card. On APUs `power1_average` is not always available, since the
SMU may decline it while idle.

No ROCm or `amd-smi` installation is needed — the script reads the kernel
interface directly. Those sysfs files are world readable, so the script needs
no privileges for the data; it only has to be readable and executable by the
user running snmpd, which is what keeping it in `/etc/snmp` gives you.

### Product names

Names come from the libdrm ids table, by default `/usr/share/libdrm/amdgpu.ids`,
matched on PCI device id and revision. A card the table does not list — a recent
one, or a system with an older libdrm — is identified by its PCI address alone.

To use a different table, pass `AMDGPU_IDS` through the pass_persist line:

```bash
pass_persist .1.3.6.1.4.1.60652.101 /usr/bin/env AMDGPU_IDS=/path/to/amdgpu.ids /etc/snmp/amdgpu
```

Note that the revision matters: the same device id can be several products, for
example device `1900` revision `C5` is a Radeon 780M while revision `C6` is a
760M.

### Multiple cards

The PCI address of the card indexes the table, so the OID of a sensor carries
the address of the card it reads. A card added, removed or unbound from the
driver therefore leaves the readings of the others where they are, and two
identical cards in one host stay apart.

### Thresholds

No limits are set on discovery, so LibreNMS guesses them from the first reading
— for the frequency class that is the reading ±5%. This is not a meaningful
range for a GPU core clock, which is designed to move between a few hundred
megahertz at idle and its boost ceiling under load, so whichever end the first
reading lands on, the other will trip.

No ceiling is set here because it belongs to the card, not to the sensor: the
same driver serves parts whose boost differs by a factor of ten. Adjust the
limits per sensor if you want an alert, or clear them and read the graph.
