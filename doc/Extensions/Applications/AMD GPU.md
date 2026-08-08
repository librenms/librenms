# AMD GPU

A small shell script that exports AMD GPU power and clock from sysfs.

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

Each card is a sensor group of its own, identified by PCI address and, where
the card is known to the libdrm ids table, by product name.

### SNMP Extend

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
    extend amdgpu /etc/snmp/amdgpu
    ```

4. Restart snmpd on your host

The sensors should be auto-discovered on the next discovery run.

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

A value the card does not publish is reported as `U` and simply yields no
sensor, rather than hiding the rest of the card. On APUs `power1_average` is
not always available, since the SMU may decline it while idle.

No ROCm or `amd-smi` installation is needed — the script reads the kernel
interface directly.

### Product names

Names come from the libdrm ids table, by default `/usr/share/libdrm/amdgpu.ids`,
matched on PCI device id and revision. A card the table does not list — a recent
one, or a system with an older libdrm — is identified by its PCI address alone.

To use a different table, pass `AMDGPU_IDS` through the extend line:

```bash
extend amdgpu /usr/bin/env AMDGPU_IDS=/path/to/amdgpu.ids /etc/snmp/amdgpu
```

Note that the revision matters: the same device id can be several products, for
example `15BF` revision `C4` is a Radeon 780M while revision `C5` is a 740M.

### Multiple cards

Cards are emitted in PCI address order, one four-line block each, and sensors
are keyed by that address. If the set of cards changes — one is unbound, added
or removed — the blocks shift and the stored OIDs point at the neighbouring card
until the next discovery run corrects them.

### Thresholds

No limits are set on discovery, so LibreNMS guesses them from the first reading
— for the frequency class that is the reading ±5%. This is not a meaningful
range for a GPU core clock, which is designed to move between a few hundred
megahertz at idle and its boost ceiling under load, so whichever end the first
reading lands on, the other will trip.

No ceiling is set here because it belongs to the card, not to the sensor: the
same driver serves parts whose boost differs by a factor of ten. Adjust the
limits per sensor if you want an alert, or clear them and read the graph.

### Script permissions

The script only reads world-readable files under `/sys`, so it needs no
privileges for the data itself. It does need to be readable and executable by
the user running snmpd. If you keep it where that user cannot reach, run it
through sudo — which takes two changes, not one:

```bash
# /etc/snmp/snmpd.conf
extend amdgpu /usr/bin/sudo /etc/snmp/amdgpu
```

```bash
# /etc/sudoers.d/librenms
Debian-snmp ALL = NOPASSWD: /etc/snmp/amdgpu
```
