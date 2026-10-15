# Junos NAT Pool

Optional reference for the `junos-nat-pool` poller module. Nothing in the code
depends on this file; keep, trim or delete it freely.

The module polls `jnxJsSrcNatStatsTable` (`.1.3.6.1.4.1.2636.3.39.1.7.1.1.4.1`)
on Juniper SRX devices and stores one RRD per NAT pool.

Enable it with:

```bash
lnms config:set poller_modules.junos-nat-pool true
```

The port and address graphs are selected with `?pool=<name>&addr_type=<value>`
in the graph URL.

## Design notes

**Two passes.** SNMP table walks are column-major: every row of column 1, then
every row of column 2, and so on. The address family in column 2 is therefore
not necessarily seen before columns 5 and 6 for the same row. The first pass
buckets every column value by its raw row index. The second pass decodes the
pool name, reads the family from column 2 and aggregates.

**Address family comes from column 2.** The index of `jnxJsSrcNatStatsEntry`
contains an address type segment, but on a real production SRX that segment was
`0` on every row, while column 2 (`jnxJsNatSrcXlatedAddrType`) reported `1`
(ipv4) for the same rows. Column 2 is the only reliable source. Keying on the
index segment would put every pool under one address type.

**Pools are keyed on `(name, addr_type)`.** The table is indexed on pool name,
address type and address, so a v4 and a v6 pool with the same name are separate
entries. Aggregating on name alone would merge them into one RRD. The raw
`addr_type` integer is used in the aggregation key and the RRD filename. It is
mapped to `v4`/`v6` only for debug output and graph titles.

**Pools in different routing instances are indistinguishable.** Neither
`jnxJsSrcNatStatsTable` nor `jnxJsNatPoolTable` has a routing-instance or
logical-system component in its index, and they expose no subnet or prefix
field. Same-name pools in different routing instances are merged. This is a MIB
limitation, not something the poller can fix.

**Only pool-based source NAT is covered.** The table is indexed on the pool
name, so a rule with no pool object (interface-based source NAT, "Action:
interface") has no row at all. A real interface-NAT rule with more than 14
million translation hits never appeared in the walk of a production SRX.
Covering it would need a different table.

**Some columns are absent on some devices.** Columns 7, 8 and 9
(`ports_avail`, `addr_avail`, `addr_inuse`) were not returned by the SRX used
for testing. They are stored as 0 when missing. Columns 5 and 6 were identical
there because all pools were `withPAT`.
