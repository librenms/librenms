source: Developing/os/Test-Units.md
path: blob/master/doc/

# Tests

Tests ensure LibreNMS works as expected, now and in the future.  New
OS should provide as much test data as needed and added test data for
existing OS is welcome.

Saved snmp data can be found in `tests/snmpsim/*.snmprec` and saved
database data can be found in `tests/data/*.json`. Please review this
for any sensitive data **before** submitting.  When replacing data,
make sure it is modified in a consistent manner.

> We utilise [snmpsim](http://snmpsim.sourceforge.net/) to do unit
> testing. For OS discovery, we can mock snmpsim, but for other tests
> you will need it installed and functioning.  We run snmpsim during
> our integration tests, but not by default when running
> `lnms dev:check`.  You can install snmpsim with the
> command `pip3 install snmpsim`.

## Capturing test data

???+ warning "If test data already exists"

    If test data already exists, but is for a different device/configuration with the same OS.  
    Then you should use the `--variant (-v)` option to
    specify a different variant of the OS,  
    this will be tested completely separate from other variants.  
    If there is only one variant, please do not specify one.

### 1. Collect SNMP data

`./scripts/collect-snmp-data.php` is provided to make it easy to
collect data for tests.  Running collect-snmp-data.php with the
`--hostname (-h)` allows you to capture all data used to discover and
poll a device already added to LibreNMS.  Make sure to re-run the
script if you add additional support. Check the command-line help for
more options.


### 2. Save test data

After you have collected snmp data, run `./scripts/save-test-data.php`
with the `--os (-o)` option to dump the post discovery and post poll
database entries to json files. This step requires snmpsim, if you are
having issues, the maintainers may help you generate it from the
snmprec you created in the previous step.

Generally, you will only need to collect data once.
After you have the data you need in the snmprec file, you can just use save-test-data.php to update the database dump (json) after that.


## Running tests

**Note:** To run tests, ensure you have executed
`./scripts/composer_wrapper.php install` from your LibreNMS root
directory. This will read composer.json and install any dependencies required.

After you have saved your test data, you should run
`lnms dev:check` verify they pass.

To run the full suite of tests enable database and snmpsim reliant
tests: `lnms dev:check unit --db --snmpsim`

### Specific OS

`lnms dev:check unit -o osname`

### Specific Module

`lnms dev:check unit -m modulename`

## Using snmpsim for testing

You can run snmpsim to access test data by running

```bash
lnms dev:simulate
```

You may then run snmp queries against it using the os (and variant) as
the community and 127.1.6.1:1161 as the host.

```bash
snmpget -v 2c -c ios_c3560e 127.1.6.1:1161 sysDescr.0
```

## Snmprec format

Snmprec files are simple files that store the snmp data. The data
format is simple with three columns: numeric oid, type code, and
data. Here is an example snippet.

```snmp
1.3.6.1.2.1.1.1.0|4|Pulse Secure,LLC,MAG-2600,8.0R14 (build 41869)
1.3.6.1.2.1.1.2.0|6|1.3.6.1.4.1.12532.254.1.1
```

During testing LibreNMS will use any info in the snmprec file for snmp
calls.  This one provides sysDescr (`.1.3.6.1.2.1.1.1.0`, 4 = Octet
String) and sysObjectID (`.1.3.6.1.2.1.1.2.0`, 6 = Object Identifier),
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

## Example workflow

If the base os (<os>.snmprec) already contains test data for the
module you are testing or that data conflicts with your new data, you
must use a variant to store your test data (-v).

### Add initial detection

1. Add device to LibreNMS. It is generic and device_id = 42
1. Run `./scripts/collect-snmp-data.php -h 42`, initial snmprec will be created
1. [Add initial detection](Initial-Detection.md) for `example-os`
1. Run discovery to make sure it detects properly `./discovery.php -h 42`
1. Add any additional os items like version, hardware, features, or serial.
1. If there is additional snmp data required, run `./scripts/collect-snmp-data.php -h 42`
1. Run `./scripts/save-test-data.php -o example-os` to update the dumped database data.
1. Review data. If you modified the snmprec or code (don't modify json manually) run `./scripts/save-test-data.php -o example-os -m os`
1. Run `lnms dev:check unit --db --snmpsim`
1. If the tests succeed submit a pull request

### Additional module support or test data

1. Add code to support module or support already exists.
1. `./scripts/collect-snmp-data.php -h 42 -m <module>`, this will add
   more data to the snmprec file
1. Review data. If you modified the snmprec (don't modify json
   manually) run `./scripts/save-test-data.php -o example-os -m <module>`
1. Run `lnms dev:check unit --db --snmpsim`
1. If the tests succeed submit a pull request
