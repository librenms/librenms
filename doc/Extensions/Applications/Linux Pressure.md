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

Per cgroup pressure can also be graphed. This requires cgroup v2.

- -c :: Report every cgroup under `/sys/fs/cgroup`.

- -r $REGEXP :: Only report the cgroups matching the Perl regexp. It
  is matched against the path relative to `/sys/fs/cgroup`, such as
  `system.slice/cron.service`. May be given more than once, in which
  case a cgroup matching any of them is reported. Implies `-c`.

### Why -r matters

Use `-r`. A plain `-c` is only sane on a host with a small, fixed set
of cgroups.

Each reported cgroup costs up to seven RRDs, seven alerting metrics, and
a line on every comparison graph. Worse, cgroups that go away are not
cleaned up. Their RRDs stay on disk until removed by hand. Every
cgroup that ever shows up costs this, and lots of cgroups are short
lived...

- session-*.scope :: A new one for every login.

- run-*.scope and run-*.service :: Made by `systemd-run` and anything
  that uses it.

- user@*.service :: The user manager, which has its own tree of
  per user services and scopes under it.

- Containers :: Docker, Podman, LXC, and Kubernetes cgroups are named
  after the container or pod ID, which changes every time the
  container is recreated.

On a host running containers or with users logging in, `-c` grows the
RRD directory forever and fills the graphs with dead cgroups.

`-c` also reports the parent cgroups, such as `system.slice` and
`user.slice`. A parent includes the pressure of everything under it,
so these are mostly noise next to the cgroups under them.

### Picking a regexp

The regexp is not anchored, so `cron` matches anything with `cron`
anywhere in the path. Use `^` and `$`, and escape the dots.

To see what cgroups exist...

```bash
find /sys/fs/cgroup -name cpu.pressure -printf '%h\n' | sed 's|^/sys/fs/cgroup/||'
```

To check what a regexp matches, run the script without `-b` and look
at the keys...

```bash
/etc/snmp/linux_pressure -r '^system\.slice/[^/]+\.service$' | jq -r '.data.cgroups | keys[]'
```

Some starting points...

- ^system\.slice/[^/]+\.service$ :: The systemd system services. A
  good default for most hosts.

- ^system\.slice/(nginx|postgresql@[^/]+)\.service$ :: Only the
  services of interest. Template instances, such as
  `postgresql@16-main.service`, need the `@` part matched.

- ^[^/]+\.slice$ :: Only the top level slices, such as `system.slice`,
  `user.slice`, and `machine.slice`. A cheap overview of where pressure
  is coming from.

Avoid patterns like `\.service$` or `\.scope$` without a `^system\.slice/`
in front, as they will pick up the user and transient cgroups above.

For example, the extend line for the systemd system services...

```bash
extend linux_pressure /etc/snmp/linux_pressure -b -r '^system\.slice/[^/]+\.service$'
```

A regexp that fails to compile is reported back as the error for the
application.

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
