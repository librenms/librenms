source: Developing/os/Test-Units.md

# Tests

Tests ensure LibreNMS works as expected, now and in the future.  New OS should provide as much test data as needed and
added test data for existing OS is welcome.

Saved snmp data can be found in `tests/snmpsim/*.snmprec` and saved database data can be found in `tests/data/*.json`.
Please review this for any sensitive data **before** submitting.  When replacing data, make sure it is modified in a
consistent manner.

We utilise [snmpsim](http://snmpsim.sourceforge.net/) to do unit testing. For OS discovery, we can mock snmpsim, but
for other tests you will need it installed and functioning.  We run snmpsim during our integration tests, but not by
default when running `./scripts/pre-commit.php`.

## Capturing test data

`./scripts/save-tests-data.php` is provided to make it easy to collect data for tests.  Running save-tests-data.php with
the --hostname (-h) allows you to capture all data used to discover and poll a device already added to LibreNMS.  Make sure to
re-run the script if you add additional support. Check the command-line help for more options.

### OS Variants

If test data already exists, but is for a different device/configuration then you should use the --variant (-v) option to
specify a different variant of the os, this will be tested completely separate from other variants.  If there is only
one variant, please do not specify one.

## Running tests

After you have saved your test data, you should run `./scripts/pre-commit.php -p -u` verify they pass.

If you would like to test the data for a specific OS, use `./scripts/pre-commit.php -p --os osname`.

To run the full suite of tests enable db and snmpsim reliant tests: `./scripts/pre-commit.php --db --snmpsim -p -u`

## Using snmpsim for testing

You can run snmpsim to access test data by running `./scripts/save-tests-data.php --snmpsim`

You may then run snmp queries against it using the os (and variant) as the community and 127.1.6.1:1161 as the host.
```
snmpget -v 2c -c ios_c3560e 127.1.6.1:1161 sysDescr.0
```

## Snmprec format

Snmprec files are simple files that store the snmp data. The data format is simple with three columns: numeric oid, type
code, and data. Here is an example snippet.

```
1.3.6.1.2.1.1.1.0|4|Pulse Secure,LLC,MAG-2600,8.0R14 (build 41869)
1.3.6.1.2.1.1.2.0|6|1.3.6.1.4.1.12532.254.1.1
```

During testing LibreNMS will use any info in the snmprec file for snmp calls.  This one provides
sysDescr (`.1.3.6.1.2.1.1.1.0`, 4 = Octet String) and sysObjectID (`.1.3.6.1.2.1.1.2.0`, 6 = Object Identifier),
 which is the minimum that should be provided for new snmprec files.

To look up the numeric OID and type of an string OID with snmptranslate:
```bash
snmptranslate -On -Td SNMPv2-MIB::sysDescr.0
```

List of SNMP data types:

| Type              | Value         |
| ----------------- | ------------- |
| OCTET STRING      | 4             |
| HEX STRING        | 4x            |
| Integer32         | 2             |
| NULL              | 5             |
| OBJECT IDENTIFIER | 6             |
| IpAddress         | 64            |
| Counter32         | 65            |
| Gauge32           | 66            |
| TimeTicks         | 67            |
| Opaque            | 68            |
| Counter64         | 70            |

Hex encoded strings (4x) should be used for any strings that contain line returns.

## New discovery/poller modules

New discovery or poller modules should define database capture parameters in `/tests/module_tables.yaml`.
