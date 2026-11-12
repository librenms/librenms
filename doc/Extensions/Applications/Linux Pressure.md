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

## Cgroups

Per cgroup pressure can also be graphed. This requires cgroup v2. Add
`-c` to the extend to report every cgroup, or `-r` with a regexp to only
report the cgroups matching it. `-r` may be used more than once and is
matched against the path relative to `/sys/fs/cgroup`.

For only the systemd system services...

```bash
extend linux_pressure /etc/snmp/linux_pressure -b -r '^system\.slice/[^/]+\.service$'
```

Or for every service, including template instances and user services...

```bash
extend linux_pressure /etc/snmp/linux_pressure -b -r '\.service$'
```

Each cgroup gets it own set of RRDs, so `-r` is very much suggested over
`-c`. A plain `-c` on a host with containers or lots of short lived
scopes will leave a lot of RRDs behind.

The cgroups show up as a list at the top of the application page.
Selecting one shows the graphs for it instead of the system wide ones. A
cgroup is dropped from the list once it is no longer reported, but its
RRDs are kept.

Anything in a cgroup name other than letters, numbers, and `,._-` becomes
a `_` in the RRD filename. So `foo/bar` and `foo_bar` would end up
sharing RRDs. Cgroups with a name too long to fit in a RRD filename are
skipped.

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

For cgroups only `avg300` is saved, named
`cgroup_$CGROUP_$RESOURCE_$STALL_TYPE_avg300`, with `$CGROUP` being the
path relative to `/sys/fs/cgroup`. So for cron it would be
`cgroup_system.slice/cron.service_memory_full_avg300`.

Metric names are limited to 64 characters, so cgroups with a name over
38 to 42 characters, depending on the resource, will be graphed but not
have metrics.

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

These are all for the system wide pressure. For alerting on cgroups, use
a regex on the metric name, such as `cgroup_.*_memory_full_avg300`.
