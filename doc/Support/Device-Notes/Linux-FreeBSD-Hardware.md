# Linux and FreeBSD Hardware

The `linux_entity` and `freebsd_entity` scripts from librenms-agent, plus
`generic_entity` for other unix systems, provide hardware info via
ENTITY-MIB, ENTITY-SENSOR-MIB, and ENTITY-STATE-MIB, which net-snmp does
not implement on its own. They run as a snmpd `pass_persist`.

This shows up in LibreNMS as...

- Transceivers :: SFP, QSFP, and the like on the Ports page and in the
  port's Transceiver view, with their temperature, voltage, bias, and Tx
  and Rx power.
- Sensors :: hwmon temperatures, fans, voltages, current, and power on
  Linux, or the coretemp, amdtemp, pchtherm, and ACPI temperatures on
  FreeBSD, plus RAPL CPU power on Linux, batteries on both, BMC sensors
  via IPMI, and disk temperatures from the smart extend. These are under
  Health.
- Inventory :: the chassis, motherboard, CPUs, memory, disks, and
  transceivers, with vendor, model, and serial, on the Inventory tab.
- State :: transceiver alarm and warning flags, loss of signal, and Tx
  fault, battery and AC state, disk SMART health, and whether any BMC
  sensor is past a threshold, also on the Inventory tab.

Each script works in two parts. Ran from cron as root, it gathers
everything and writes a cache. Ran by snmpd, it answers from the cache, so
snmpd never waits on slow tools such as `ethtool -m`.

## Linux

1. Install the requirements.

    ```bash
    apt-get install ethtool libjson-perl libfile-slurp-perl
    ```

    For `--ipmi`, also install `freeipmi-tools`.

2. Download the script and make it executable.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/linux_entity -O /etc/snmp/extends/linux_entity
    chmod +x /etc/snmp/extends/linux_entity
    ```

3. Add a cron job, picking the flags wanted from [Flags](#flags).

    ```bash
    # /etc/cron.d/linux_entity
    */5 * * * * root /etc/snmp/extends/linux_entity -u --hwmon --rapl --power --inventory --state
    ```

4. Add these to `/etc/snmp/snmpd.conf`. The .131 one is only needed with
   `--state` and the .9.9.91 one with `--thresholds`.

    ```bash
    pass_persist .1.3.6.1.2.1.47 /etc/snmp/extends/linux_entity -p .1.3.6.1.2.1.47
    pass_persist .1.3.6.1.2.1.99 /etc/snmp/extends/linux_entity -p .1.3.6.1.2.1.99
    pass_persist .1.3.6.1.2.1.131 /etc/snmp/extends/linux_entity -p .1.3.6.1.2.1.131
    pass_persist .1.3.6.1.4.1.9.9.91 /etc/snmp/extends/linux_entity -p .1.3.6.1.4.1.9.9.91
    ```

5. Run it once so the cache exists, then restart snmpd.

    ```bash
    /etc/snmp/extends/linux_entity -u --hwmon --rapl --power --inventory --state
    systemctl restart snmpd
    ```

### LM-SENSORS-MIB

If snmpd was built with lm-sensors support, it already serves the hwmon
temperatures, fans, and voltages under LM-SENSORS-MIB, so `--hwmon` reports
them a second time.

The better fix is hiding LM-SENSORS-MIB and letting `--hwmon` replace it,
as it names sensors by chip and label, keeps the same indexes between
boots, and adds current and power. For the view named `all` from the
[example snmpd.conf](../SNMP-Configuration-Examples.md#linux-snmpd-v2),
add this after its `view all included .1` line...

```bash
view all    excluded  .1.3.6.1.4.1.2021.13.16
```

For a different view, use its name instead. A `rouser` or `rocommunity`
line without `-V` has no view, so one needs to be made and given to it
with `-V`. The old lmsensors sensors go away and the hwmon ones are new,
so their history does not carry over.

If snmpd.conf can not be changed, leave out the kinds LM-SENSORS-MIB
already covers instead with `--hwmon-kinds curr,power,humidity`.

## FreeBSD

This needs net-snmp from ports, `net-mgmt/net-snmp`. `bsnmpd` from base
does not support `pass_persist`.

1. Install the requirements. dmidecode is only needed for the CPUs and
   memory with `--inventory`, and freeipmi for `--ipmi`.

    ```bash
    pkg install p5-JSON p5-File-Slurp dmidecode freeipmi
    ```

    `--ipmi` also needs ipmi(4) loaded, which `ipmi_load="YES"` in
    `/boot/loader.conf` does at boot.

2. Download the script and make it executable.

    ```bash
    mkdir -p /usr/local/etc/snmp/extends
    fetch -o /usr/local/etc/snmp/extends/freebsd_entity https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/freebsd_entity
    chmod +x /usr/local/etc/snmp/extends/freebsd_entity
    ```

3. Add a cron job to `/etc/crontab`, picking the flags wanted from
   [Flags](#flags).

    ```bash
    */5 * * * * root /usr/local/etc/snmp/extends/freebsd_entity -u --sensors --power --inventory --state
    ```

4. Add these to `/usr/local/etc/snmp/snmpd.conf`. The .131 one is only
   needed with `--state` and the .9.9.91 one with `--thresholds`.

    ```bash
    pass_persist .1.3.6.1.2.1.47 /usr/local/etc/snmp/extends/freebsd_entity -p .1.3.6.1.2.1.47
    pass_persist .1.3.6.1.2.1.99 /usr/local/etc/snmp/extends/freebsd_entity -p .1.3.6.1.2.1.99
    pass_persist .1.3.6.1.2.1.131 /usr/local/etc/snmp/extends/freebsd_entity -p .1.3.6.1.2.1.131
    pass_persist .1.3.6.1.4.1.9.9.91 /usr/local/etc/snmp/extends/freebsd_entity -p .1.3.6.1.4.1.9.9.91
    ```

5. Run it once so the cache exists, then restart snmpd.

    ```bash
    /usr/local/etc/snmp/extends/freebsd_entity -u --sensors --power --inventory --state
    service snmpd restart
    ```

On hosts with lots of jails or VMs, `-I '^(epair|tap|vnet)'` skips checking
their interfaces for transceivers.

## Other Unix

For the likes of OpenBSD, NetBSD, and illumos, `generic_entity` provides
the inventory from dmidecode, including power supplies, the disks and
their temperatures from the [smart](../../Extensions/Applications/SMART.md)
extend, and the BMC via freeipmi. It lacks transceivers and the OS
specific sensors. Like the others, it needs net-snmp for `pass_persist`.

1. Install Perl's JSON and File::Slurp, plus dmidecode and freeipmi.

2. Download the script and make it executable.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/generic_entity -O /etc/snmp/extends/generic_entity
    chmod +x /etc/snmp/extends/generic_entity
    ```

3. Add a cron job.

    ```bash
    */5 * * * * root /etc/snmp/extends/generic_entity -u --dmi --smart --ipmi --state
    ```

4. Add the same `pass_persist` lines as for Linux, with `generic_entity`
   in place of `linux_entity`.

5. Run it once so the cache exists, then restart snmpd.

For `--smart`, set `useSN=0` in the smart extend's config. Disks are then
keyed by name, so a replaced disk keeps its history. With `useSN=1` they
are keyed by serial, so a replaced disk shows up as a new one.

The entity-state module is only enabled by default for Linux and FreeBSD,
so for others it needs turning on under Device -> Settings -> Modules.

## Flags

Transceivers are always checked. The rest are added with these. Run the
script with `--help` for the details.

- `--hwmon` :: Linux. Sensors from `/sys/class/hwmon`.
- `--hwmon-kinds` :: Linux. Only these hwmon kinds, comma separated, out of
  temp, in, curr, power, fan, and humidity. Implies `--hwmon`.
- `--hwmon-skip-disks` :: Linux. Skip the NVMe and drivetemp hwmon
  devices, so `--smart` provides all the disk temperatures. Implies
  `--hwmon`.
- `--rapl` :: Linux. CPU package, core, and DRAM power. Shows up from the
  second run, as it is the average since the last one.
- `--sensors` :: FreeBSD. Temperatures from sysctl.
- `--power` :: Batteries and AC.
- `--inventory` :: Motherboard, CPUs, memory, and disks.
- `--smart [cache]` :: Merge in the disk details, health, and temperatures
  from the [smart](../../Extensions/Applications/SMART.md) extend's cache,
  `/var/cache/smart` by default. Also adds disks only it can see, such as
  those behind a RAID controller. On Linux, disks `--hwmon` has a
  temperature for keep that one. Implies `--inventory`.
- `--state` :: ENTITY-STATE-MIB state for transceivers, power supplies,
  disks, and the BMC.
- `--ipmi` :: BMC temperatures, voltages, current, power, and fans via
  `ipmi-sensors` from freeipmi.
- `--thresholds` :: The `--ipmi` thresholds and `--smart` disk temperature
  limits, as the entSensorThresholdTable from CISCO-ENTITY-SENSOR-MIB.
- `-i` and `-I` :: Regexps of interfaces to check or skip for transceivers.
- `--dmi` :: generic_entity. Chassis, motherboard, CPUs, memory, and power
  supplies from dmidecode.
- `-d` :: The cache directory, defaulting to `/var/cache/` plus the
  script's name.

## Checking It

On the host, this should list the inventory...

```bash
snmpwalk -v2c -c public localhost .1.3.6.1.2.1.47.1.1.1.1.7
```

Then rediscover the device so it shows up without waiting for the next
discovery run.

```bash
lnms device:discover yourhost -m entity-physical,transceivers,sensors,entity-state
```

## Limits

- Thresholds :: ENTITY-SENSOR-MIB has nowhere to put them, so sensors get
  the LibreNMS default limits. `--thresholds` serves the IPMI and disk
  ones via CISCO-ENTITY-SENSOR-MIB, but LibreNMS only reads that for Cisco
  devices, so it does not use them yet. The transceiver and hwmon ones are
  not served.
- Transceiver details :: Wavelength, connector, and distance are not
  shown, as ENTITY-MIB has nowhere to put them.
- Indexes :: The cache directory has `state.json`, which keeps the
  indexes the same between runs. Deleting it renumbers everything, which
  loses the sensor history.
- State :: The entity-state module is enabled for Linux and FreeBSD. It
  can be turned off per device under Device -> Settings -> Modules.
