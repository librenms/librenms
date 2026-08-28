# Tests

Tests keep LibreNMS correct, now and in the future. A new OS must
supply enough test data. Test data for an existing OS is also welcome.

The saved SNMP data is in `tests/snmpsim/*.snmprec`. The saved database
data is in `tests/data/*.json`. Read this data for sensitive
information **before** you submit it. Replace the data in a consistent
way.

> We use [snmpsim](http://snmpsim.sourceforge.net/) for the unit tests.
> For OS discovery, we can mock snmpsim. The other tests need an
> installed and working snmpsim. We run snmpsim in our integration
> tests. `lnms dev:check` does not run it by default. To install
> snmpsim, run `pip3 install snmpsim`.

## Capturing test data

???+ warning "If test data already exists"

> Test data can already exist for a different device or configuration
> with the same OS. In that case, use the `--variant (-v)` option to
> give a different variant of the OS. LibreNMS tests each variant
> separately. With only one variant, do not give a variant name.

### 1. Collect SNMP data

`lnms dev:collect-snmprec` collects the data for the tests. Run
`dev:collect-snmprec` with `<device> --variant ''`. It then captures
all the discovery data and the polling data of a device in LibreNMS.
Run the command again after you add more support. For more options,
read the command line help.

### 2. Save test data

After the collection of the SNMP data, run
`lnms dev:generate-test-data <os>` with the `--variant ''` option. It
dumps the database entries after discovery and after polling into JSON
files. This step needs snmpsim. If you have a problem, the maintainers
can generate the files from your snmprec file.

You usually collect the data one time. After the snmprec file holds
your data, use `lnms dev:generate-test-data` to update the JSON database dump.

## Running tests

**Note:** before the tests, run `./scripts/composer_wrapper.php install`
in your LibreNMS root directory. This command reads `composer.json` and
installs the necessary dependencies.

After you save your test data, run `lnms dev:check` to test it.

For the full test suite, enable the database tests and the snmpsim
tests: `lnms dev:check unit --db --snmpsim`

### Specific OS

`lnms dev:check unit -o osname`

### Test an OS, but only discovery and polling modules (exluding OS detection)
`lnms dev:check unit --os osname --os-modules-only`


### Specific Module

`lnms dev:check unit -m modulename`

### Test all modules for all os and stop on failure
`lnms dev:check unit --db -snmpsim --os-modules-only -f`

## Using snmpsim for testing

This command runs snmpsim with the test data:

```bash
lnms dev:simulate
```

You can then run SNMP queries against it. Use the OS, and the variant,
as the community. Use 127.1.6.1:1161 as the host.

```bash
snmpget -v 2c -c ios_c3560e 127.1.6.1:1161 sysDescr.0
```

## Simulate specific device from test data

Add or update a device with the name "snmpsim" in your install. Then
point it to a specific snmprec file.

```bash
lnms dev:simulate ios_2960x
```

Then run `lnms device:discover snmpsim -vv` and
`lnms device:poll snmpsim -vv`. These commands discover and poll the
simulated device.

## Snmprec format

An snmprec file stores the SNMP data. The format has three columns: the
numeric OID, the type code, and the data. This is an example:

```snmp
1.3.6.1.2.1.1.1.0|4|Pulse Secure,LLC,MAG-2600,8.0R14 (build 41869)
1.3.6.1.2.1.1.2.0|6|1.3.6.1.4.1.12532.254.1.1
```

In a test, LibreNMS uses the data of the snmprec file for the SNMP
calls. This example gives sysDescr (`.1.3.6.1.2.1.1.1.0`, 4 = Octet
String) and sysObjectID (`.1.3.6.1.2.1.1.2.0`, 6 = Object Identifier),
This data is the minimum for a new snmprec file.

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

Use a hex encoded string (4x) for each string with a line return.

## New discovery/poller modules

A new discovery module or poller module defines its database capture
parameters in `/tests/module_tables.yaml`.

## Example workflow

If the base os (<os>.snmprec) already contains test data for the
module you are testing or that data conflicts with your new data, you
must use a variant to store your test data (-v <variant>).

### Add initial detection

1. Add device to LibreNMS. It is generic and device_id = 42
1. Run `lnms dev:collect-snmprec 42 --variant ''`. It creates the first snmprec file
1. [Add initial detection](Initial-Detection.md) for `example-os`
1. Run discovery to make sure it detects properly `lnms device:discover -vv 42`
1. Add any additional os items like version, hardware, features, or serial.
1. If there is additional snmp data required, run
   `lnms dev:collect-snmprec 42 --variant ''`
1. Run `lnms dev:generate-test-data example-os` to update the
   dumped database data.
1. Review data. If you modified the snmprec or code (do not modify the json
   manually) run `lnms dev:generate-test-data example-os -m os --variant ''`
1. Run `lnms dev:check unit --db --snmpsim`
1. If the tests succeed submit a pull request

### Additional module support or test data

1. Add code to support module or support already exists.
1. Run `lnms dev:collect-snmprec 42 --variant '' -m <module>`. It adds
   more data to the snmprec file
1. Review data. If you modified the snmprec (do not modify the json
   manually) run `lnms dev:generate-test-data example-os --variant '' -m <module>`
1. Run `lnms dev:check unit --db --snmpsim`
1. If the tests succeed submit a pull request

## JSON Application Test Writing Using ./scripts/json-app-tool.php

1. First get a good example of the JSON output over SNMP
   extend in question.
1. Read the help via `./scripts/json-app-tool.php -h`.
1. Generate the SNMPrec data via `./scripts/json-app-tool.php -a
   appName -s > ./tests/snmpsim/linux_appName-v1.snmprec`. If the
   SNMP extend name OID differs from the application name, add the -S flag
   to override it.
1. Generate the test JSON data via `./scripts/json-app-tool.php -a
   appName -t > ./tests/data/linux_appName-v1.json`.
1. Update the generated './tests/data/linux_appName-v1.json' making
   sure that all the expected metrics are present. This step assumes that
   LibreNMS collapses and uses everything under .data in the JSON.

In a test run, LibreNMS can fail to detect the app. If the app name and
the SNMP extend name OID differ, make sure that -S is correct. Also
make sure that `includes/discovery/applications.inc.php` holds the
update.
