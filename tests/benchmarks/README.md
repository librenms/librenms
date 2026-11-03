# Discovery insert benchmark

Run from the repository root with the development Composer dependencies installed
and a disposable test database configured with `DB_TEST_*`:

```sh
DBTEST=1 php artisan migrate --env=testing --database=testing
DBTEST=1 php tests/benchmarks/sync-models.php --dataset=arp --rows=1000,10000,50000 --runs=3 --mode=both
DBTEST=1 php tests/benchmarks/sync-models.php --dataset=fdb --rows=1000,10000,50000 --runs=3 --mode=both
```

The script selects the `testing` connection and disables Debugbar before bootstrapping
the application. It resets Ignition's query history between samples and after
fixture construction, so bindings from earlier samples are not retained. The
normal query recorder remains active during both timed strategies.
Each sample constructs a device and a fresh dataset, times reconciliation and
insertion, verifies the stored row count, then rolls back. Fixture construction
and rollback are outside the timer. Output includes individual samples, medians,
INSERT statement counts, largest binding count, and process memory peaks.
It does not measure SNMP walks, network latency, disk durability, or a complete
discovery run. Timings are descriptive, rather than assertions in the test suite.

For ARP, `save` runs the existing `syncModels()` lifecycle and `bulk` runs
`syncModelsInBulk()`, both with the normal progress observer installed and output
discarded. For FDB, `save` reproduces the legacy per-row raw INSERT loop for an
all-new VLAN; `bulk` runs the extracted FDB synchronizer. No artificial query
latency is injected. Both strategies use the same connection and data.

## Synchronization audit

`syncModels()` is also used by callers that need generated IDs, saved state, or
model side effects. It keeps its original return and save behavior. The common
reconciliation loop still deduplicates discovered keys with the last value
winning, updates the first existing row, deletes duplicate existing rows, and
deletes missing rows through model methods. Protected attributes, group filters,
polling-method synchronization and skipped-VRF filtering remain in place.

| Callers | Reason to retain normal model saves |
| --- | --- |
| MPLS, QoS, VLANs, VRFs, sensor state associations | Saved IDs or instances feed subsequent work. |
| BGP | Creation state controls automatic discovery; polled attributes are protected. |
| Sensors, wireless sensors, storage, mempools, STP, transceivers, VMs | Observers or model-specific behavior must run. |
| IPv4/IPv6 addresses through ports | Intermediate port relationships are supplied by discovery. |
| Other `syncModels()` callers | Their contract is unchanged; raw insertion requires explicit opt-in. |

ARP and IPv6 neighbor discovery discard the synchronization return and construct
fresh models. Their models opt into bulk insertion; they have no custom
persistence methods, custom event maps, unique-ID generators, or owner touches.
The new API returns `void`: inserted inputs receive no generated IDs or fake
saved state. Callers requiring saved instances must use `syncModels()`.
Timestamps and the relationship foreign key are prepared before insertion.

Only a plain `HasMany` relation is eligible. Pending relationship attributes,
inverse relations, mixed model classes/connections/tables, existing instances,
explicit primary keys, custom event dispatchers, additional creation/save
listeners (including wildcards), and customized progress-observer bindings
retain the ordinary Eloquent save path. Updates and deletions always use it.
The standard observer's creation progress is emitted after each successful
chunk; arbitrary model events are not synthesized around raw SQL.

FDB uses a separate legacy path. Its synchronization is extracted for testing:
known ports are not replaced by zero, changed ports update in place, observed
entries refresh `updated_at`, new entries get both timestamps and default to port
zero, and absent entries remain for daily cleanup.

The shared raw-row helper streams at most 1,000 rows per statement, also bounds
bindings and estimated bytes, and uses the supplied query's connection, table,
prefix and grammar. MySQL/MariaDB packet budgeting reads the writer's session
limit. Adjacent rows with different column sets flush separately so omitted
defaults stay omitted; column order is normalized without reordering rows.
Errors propagate, progress follows successful statements, and no new enclosing
transaction is introduced. Earlier chunks retain the caller's existing
transaction semantics. No upsert, ignored errors, ID-range inference or
`INSERT RETURNING` is used.


## Local measurements

Windows x64, PHP 8.4.26, MariaDB 10.11.14, native PDO prepares, three samples
per case; medians in seconds. These are synthetic, all-new datasets on a local
test database, rather than a device or full discovery timing.

| Dataset | Rows | Per-row seconds | Bulk seconds | INSERTs |
| --- | ---: | ---: | ---: | ---: |
| ARP | 1,000 | 0.379 | 0.028 | 1,000 → 1 |
| ARP | 10,000 | 4.014 | 0.277 | 10,000 → 10 |
| ARP | 50,000 | 18.021 | 1.421 | 50,000 → 50 |
| FDB | 1,000 | 0.260 | 0.015 | 1,000 → 1 |
| FDB | 10,000 | 2.174 | 0.131 | 10,000 → 10 |
| FDB | 50,000 | 11.093 | 0.760 | 50,000 → 50 |

At 50,000 ARP rows, all three bulk samples peaked at 134 MiB including input
models, compared with 130 MiB for the ordinary save path. The native PDO
statements used at most 5,000 ARP or 6,000 FDB bindings. SQLite compatibility is
covered by the regression tests. MariaDB 10.3, MySQL and other PHP versions were
not executed locally.
