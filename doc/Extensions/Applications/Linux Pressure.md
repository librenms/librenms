source: Extensions/Applications/Linux Pressure.md
path: blob/master/doc/

# Linux Pressure

Graphs the Linux pressure stall information (PSI) found under
`/proc/pressure`, which is how much time tasks spend stalled waiting on
CPU, IO, memory, or IRQ handling.

The kernel needs to be built with `CONFIG_PSI`. If it was also built
with `CONFIG_PSI_DEFAULT_DISABLED`, it needs booted with `psi=1` on the
kernel command line. IRQ pressure requires a 6.1 or newer kernel built
with `CONFIG_IRQ_TIME_ACCOUNTING`.

## SNMP Extend

1. Download the script and make it executable.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/linux_pressure -O /etc/snmp/linux_pressure
    chmod +x /etc/snmp/linux_pressure
    ```

2. Install the requirements.

    === "Debian/Ubuntu"

        ```bash
        apt-get install libjson-perl libmime-base64-perl libfile-slurp-perl
        ```

    === "Generic"

        ```bash
        cpanm JSON MIME::Base64 File::Slurp
        ```

3. Edit your snmpd.conf file and add:

    ```bash
    extend linux_pressure /etc/snmp/linux_pressure -b
    ```

    Or if you would rather run it from cron...

    ```bash
    # cron
    */5 * * * * root /etc/snmp/linux_pressure -b -q -o /var/cache/linux_pressure.json.snmp

    # snmpd.conf
    extend linux_pressure /bin/cat /var/cache/linux_pressure.json.snmp
    ```

4. Restart snmpd on your host.

The application should be auto-discovered as described at the top of
the page. If it is not, please follow the steps set out under `SNMP
Extend` heading top of page.

## Metrics

The averages are saved as metrics for use with alerting, named
`system_$RESOURCE_$STALL_TYPE_$AVERAGE`. So the 300 second average of
full memory pressure is `system_memory_full_avg300`.

- $RESOURCE :: `cpu`, `io`, `memory`, or `irq`.

- $STALL_TYPE :: `some` or `full`. IRQ only has `full`, and CPU only
  has `full` on 5.13 and newer kernels.

- $AVERAGE :: `avg10`, `avg60`, or `avg300`.

All of them are a percent, 0 to 100. Metrics for anything the kernel
does not report will not be present.

With the default five minute polling, `avg300` is the one to alert on.
The shorter averages only cover the last few seconds before each poll.

## Alerting

The alert rule collection has the rules below. Go to *Alerts -> Alert
Rules* and click *Create rule from collection* to add them. The
thresholds are starting points and will likely want tuned, as what is
normal varies a lot with workload.

| Rule                                      | Severity |
|-------------------------------------------|----------|
| Linux Pressure: CPU some avg300 >= 60%    | warning  |
| Linux Pressure: IO some avg300 >= 40%     | warning  |
| Linux Pressure: IO full avg300 >= 20%     | critical |
| Linux Pressure: memory some avg300 >= 20% | warning  |
| Linux Pressure: memory full avg300 >= 10% | critical |
