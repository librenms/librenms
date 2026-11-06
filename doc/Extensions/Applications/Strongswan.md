source: Extensions/Applications/Strongswan.md
path: blob/master/doc/

# strongSwan / IPsec

Per-connection IPsec metrics for strongSwan-based hosts (e.g. OPNsense/pfSense, Linux
strongSwan). For every connection it graphs:

- Inbound / outbound throughput (bits/s) and packets/s
- Tunnel state (1 = an IKE SA is established, 0 = down)
- Number of installed child SAs
- Re-establishment rate (IKE SA re-negotiations — surfaces flapping tunnels)

Plus global daemon counters: IKE/child rekeys per second and error rates (`invalid`
messages, `invalid SPI`).

Connections are labelled with their human description and peer, e.g.
*"Example Partner A (192.0.2.10)"*. This works for OPNsense's legacy
"Tunnel Settings" (`con<N>`) and the new "Connections" model (UUID names) as well as plain
Linux strongSwan.

> The per-SA byte/packet counters in `swanctl --list-sas` reset on every rekey (typically
> hourly). The extend script keeps a small state file and emits **monotonic cumulative
> counters** so LibreNMS DERIVE datasets produce clean, gap-free rates.

## SNMP Extend

This application uses the [JSON SNMP extend](
http://docs.librenms.org/Developing/os/Test-Units/#json-snmp-extend) format. It requires
Net-SNMP (on OPNsense install the **`os-net-snmp`** plugin — the default FreeBSD `bsnmpd`
does not support `extend`).

1. Copy the `strongswan` script from the
   [librenms-agent](https://github.com/librenms/librenms-agent) repository to the host
   and make it executable:

   ```bash
   wget https://github.com/librenms/librenms-agent/raw/master/snmp/strongswan -O /etc/snmp/strongswan
   chmod +x /etc/snmp/strongswan
   ```

   The script must run as a user able to call `swanctl` (root on most setups) and to write
   its small state file (default `/var/lib/librenms-strongswan-state.json`).

2. Add to your `snmpd.conf`:

   ```
   extend strongswan /etc/snmp/strongswan
   ```

   On OPNsense, add the extend under **Services → Net-SNMP** (or its `config.xml`).

3. Restart snmpd and verify:

   ```bash
   snmpwalk -v2c -c <community> localhost 'NET-SNMP-EXTEND-MIB::nsExtendOutputFull."strongswan"'
   ```

LibreNMS auto-discovers the application on the next discovery run. The extend **must** be
named `strongswan` (it must match the application name).
