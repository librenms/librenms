source: Developing/os/Test-Units.md

We have a testing unit for new OS', please ensure you add a test for any new OS' or updates to existing OS discovery.

All that you need to do is create an snmprec file in tests/snmpsim with the proper name. If adding the first test for
this os, simply use the os name `pulse.snmprec` for example.  If you need to add multiple test files, you can add an
underscore after the os name followed by a description, typically a model name.  For example: `pulse_mag2600.snmprec`.
You can copy `skel.snmprec` to your intended name and fill in the data to make things a little easier.

We utilise [snmpsim](http://snmpsim.sourceforge.net/) to do unit testing for OS discovery. For this to work you need
to supply an snmprec file. This is pretty simple and using pulse as the example again this would look like:

`tests/snmpsim/pulse_mag2600.snmprec`
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

Common OIDs used in discovery:

| String OID                          | Numeric OID                 |
| ----------------------------------- | --------------------------- |
| SNMPv2-MIB::sysDescr.0              | 1.3.6.1.2.1.1.1.0           |
| SNMPv2-MIB::sysObjectID.0           | 1.3.6.1.2.1.1.2.0           |
| ENTITY-MIB::entPhysicalDescr.1      | 1.3.6.1.2.1.47.1.1.1.1.2.1  |
| ENTITY-MIB::entPhysicalMfgName.1    | 1.3.6.1.2.1.47.1.1.1.1.12.1 |

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

You can run `./scripts/pre-commit.php -u` to run the unit tests to check your code.

If you would like to run tests locally against a full snmpsim instance, run `./scripts/pre-commit.php -u --snmpsim`.
