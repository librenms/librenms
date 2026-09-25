---
name: add-os-support
description: "Add or improve network device OS support in LibreNMS. Use this skill whenever adding support for a new operating system, device, vendor, or hardware model; adding health sensors (temperature, fan speed, voltage, current, power, state), mempools, processors, or wireless metrics; adding vendor MIBs; or generating and verifying OS test fixtures (.snmprec and test data JSON). Triggers on mentions of: adding an OS, new device support, MIB definitions, OS detection, OS discovery, sysObjectID, sysDescr, snmprec, or dev:generate-test-data."
---

# Adding or Updating Device OS Support in LibreNMS

Follow these instructions to add a new OS or update existing OS support in LibreNMS.

## Dual-Purpose Workflows

This skill handles two primary workflows:

### Workflow 1: Add a New OS
Use this workflow when LibreNMS does not yet support the operating system.
1. Add vendor MIBs to `mibs/<vendor>/` (name equals module definition, no extension).
2. Create detection YAML in `resources/definitions/os_detection/<os>.yaml`.
3. Create discovery YAML in `resources/definitions/os_discovery/<os>.yaml`.
4. Add SVG icons to `html/images/os/<os>.svg` and `html/images/logos/<os>.svg`.
5. Capture real SNMP test data in `tests/snmpsim/<os>.snmprec`.
6. Generate test data JSON with `./lnms dev:generate-test-data <os> --variant=""`.
7. Verify all tests pass with `./lnms dev:check unit -o <os>`.

### Workflow 2: Update an Existing OS
Use this workflow when you add sensors, processors, mempools, or support for a new hardware model to an existing OS.
1. Inspect existing definitions in `resources/definitions/os_discovery/<os>.yaml`.
2. Add new sensor, processor, or mempool definitions in YAML.
3. If new hardware models use different MIB OIDs:
   - Check if discovery probe walks naturally find the OID.
   - Use `skip_values` with identical indexes when defining alternative definitions of the same physical sensor.
4. Add vendor MIBs to `mibs/<vendor>/` if needed.
5. Update test fixtures:
   - To update existing base OS modules: `./lnms dev:generate-test-data <os> -m <module>`.
   - To add a new hardware variant: record `tests/snmpsim/<os>_<variant>.snmprec` and generate with `./lnms dev:generate-test-data <os> --variant="<variant>"`.
6. Run fast verification with `./lnms dev:check unit -o <os> --os-modules-only`.

---

## Core Rules

1. **OS Name Syntax**:
   - Use only lowercase letters, digits, and hyphens (`/^[a-z0-9\-]+$/`). Do not use uppercase letters, underscores, or spaces.
   - Prefer the actual OS name over marketing names if known (for example, `ftos`, `routeros`, `ironware`).
2. **OS Scope**: If multiple devices use the same MIB (or mostly the same MIBs), they belong to the same OS. Do not create an OS for each hardware model. Discovery must handle model differences.
3. **YAML Over PHP**: Always use YAML definitions for discovery. Only use PHP classes when YAML cannot do the task.
4. **OS Detection**:
   - Use `sysObjectID` (prefix match) or `sysDescr` (substring match).
   - Do not use `snmp_get` in OS detection if possible. It sends network queries that slow all device discoveries.
5. **Defaults for `icon` and `mib_dir`**:
   - The OS name is included in the MIB search path by default (`mibs/<os>`). Do not add `mib_dir: <os>`. Only add `mib_dir:` if the MIB directory differs from the OS name.
   - `icon:` defaults to the OS name. Do not add `icon:` if it matches the OS name. Only set `icon:` if it differs from the OS name.
6. **No Disabled Modules in OS Definition**:
   - Do not set discovery or poller modules to `false` in OS definitions. That is what discovery is for. Discovery automatically probes and detects supported features.
7. **OID and Value Format**:
   - Always prefer textual OIDs over numeric OIDs in discovery files (for example, `ENTITY-MIB::entPhysicalName`).
   - The sensors module only supports textual OIDs for `oid:` and `value:`. Never use numeric OIDs for `oid:` or `value:` in sensor definitions.
   - You can use scalar values in the sensors module instead of only tables.
   - For SNMP tables: `oid:` must be the table (for example, `ENTITY-SENSOR-MIB::entPhySensorTable`), and `value:` must be the column in that table (for example, `ENTITY-SENSOR-MIB::entPhySensorValue`).
   - Do not use `*Entry` OID names for `oid:`. Use `*Table` instead.
   - For scalar sensors: specify the scalar OID in `oid:` (for example, `MY-MIB::myScalar.0`) and omit `value:` (because `value:` matches `oid:`).
8. **Sensor Indexing & Options**:
   - Avoid `options:`. Define settings explicitly on each sensor entry in `data:`. It is better to be explicit.
   - Omit `index:` unless auto-generated table indexes collide. LibreNMS uses the SNMP table index `$index` automatically.
   - Let discovery probe walks to detect whether the device supports an OID.
   - When you use `skip_values` with identical indexes, use it to define alternative definitions of the exact same sensor (for example, different status or unit mappings), rather than to support different models.
9. **MIB Files**:
   - Check thoroughly for existing vendor MIBs first.
   - Include vendor MIBs verbatim as provided by the vendor. Do not make any modifications to MIB files.
   - If no MIB exists (check thoroughly), but you have detailed SNMP information, you can create a MIB from the vendor's SNMP specification documentation.
   - Save vendor MIBs in `mibs/<vendor>/`.
   - Name the file to match the MIB module definition line exactly (for example, `MYVENDOR-SYSTEM-MIB`). Do not add a file extension.
   - Do not include standard RFC MIBs in vendor directories.
   - Include only the MIBs that LibreNMS uses.
10. **Mandatory Real Test Data**:
    - Every OS must include test data (`tests/snmpsim/<os>.snmprec` and `tests/data/<os>.json`).
    - Do not fabricate `snmprec` files. You must only capture `snmprec` files from an actual device or convert them from `snmpwalk` output from an actual device.
    - Do not break the data when you sanitize it.
    - Tests must pass before you finish.

---

## File Locations

| File Type | Path | Notes |
| --- | --- | --- |
| OS Detection | `resources/definitions/os_detection/<os>.yaml` | Matches device during discovery |
| OS Discovery | `resources/definitions/os_discovery/<os>.yaml` | Extracts metadata, sensors, CPU, memory |
| Vendor MIBs | `mibs/<vendor>/<MIB-NAME>` | File name equals MIB module, no extension |
| OS Icon | `html/images/os/<os>.svg` | Square SVG, 32x32 px, no padding |
| OS Logo | `html/images/logos/<os>.svg` | Optional wide SVG logo |
| SNMP Recording | `tests/snmpsim/<os>.snmprec` | SNMP test data from a real device |
| Test Data JSON | `tests/data/<os>.json` | Expected database dump |

---

## Step 1: Add Vendor MIBs

1. Check thoroughly if a MIB exists for the device (check the vendor website, support downloads, device firmware, or MIB repositories).
2. If vendor MIBs exist:
   - Include vendor MIBs verbatim as provided by the vendor. Do not make any modifications to MIB files.
   - Identify the module name inside the MIB file: `<MODULE-NAME> DEFINITIONS ::= BEGIN`.
   - Save the file as `mibs/<vendor>/<MODULE-NAME>` without any extension (such as `.mib` or `.txt`).
   - Do not add standard RFC MIBs (for example, `IF-MIB`, `ENTITY-MIB`, `HOST-RESOURCES-MIB`).
   - Add only the MIB files that the OS definition uses.
3. If no MIB exists (check thoroughly):
   - If you have detailed SNMP information from the vendor's SNMP specification documentation, you can create a MIB from the vendor documentation.
   - Write standard SMIv2 MIB syntax with valid module headers, imports, object identifiers, and object types.
   - Save the file as `mibs/<vendor>/<MODULE-NAME>` without any file extension.

---

## Step 2: Define OS Detection

Create `resources/definitions/os_detection/<os>.yaml`.

### Detection Example

```yaml
os: acme
text: 'Acme OS'
type: network
over:
    - { graph: device_bits, text: 'Device Traffic' }
    - { graph: device_processor, text: 'CPU Usage' }
    - { graph: device_mempool, text: 'Memory Usage' }
discovery:
    - sysObjectID:
        - .1.3.6.1.4.1.99999.1.
```

### Default Values and Rules

- **Icon**: `icon:` defaults to the OS name (`html/images/os/<os>.svg`). Do not set `icon:` if it matches the OS name. Only set `icon:` if it differs from the OS name (for example, `icon: cisco` when sharing another vendor's icon).
- **MIB Directory**: LibreNMS includes `mibs/<os>` in the MIB search path by default. Do not add `mib_dir: <os>`. Only set `mib_dir:` if the directory differs from the OS name (for example, `mib_dir: cisco`).
- **No Disabled Modules**: Do not set `discovery_modules` or `poller_modules` to `false` in the OS definition. That is what discovery is for. Discovery dynamically tests whether the device supports each module.

### Detection Logic

- Root items in `discovery:` use logical OR.
- Keys inside an item (`sysObjectID`, `sysDescr`) use logical AND.
- Values under a key use logical OR.
- `sysObjectID` tests if the device sysObjectID starts with the string.
- `sysDescr` tests if the device sysDescr contains the string.
- Use `sysDescr_except` or `sysObjectID_except` to reject specific matches.
- Avoid `snmpget:` in `discovery:`. Only use it as a last resort when `sysObjectID` and `sysDescr` are identical across different operating systems.

### Valid Device Types

Use one of these valid device types:
- `network`
- `wireless`
- `power`
- `appliance`
- `server`
- `storage`
- `firewall`
- `environment`
- `loadbalancer`
- `management`
- `collaboration`
- `printer`
- `workstation`

---

## Step 3: Define OS Discovery

Create `resources/definitions/os_discovery/<os>.yaml`.

### 1. OS Metadata (Hardware, Version, Serial)

```yaml
modules:
    os:
        hardware: ACME-SYSTEM-MIB::acmeHardwareModel.0
        version: ACME-SYSTEM-MIB::acmeSoftwareVersion.0
        serial: ACME-SYSTEM-MIB::acmeSerialNumber.0
```

You can also use regex on `sysDescr`:
```yaml
modules:
    os:
        sysDescr_regex: '/Acme OS (?<version>\S+), Hardware: (?<hardware>[\w-]+), S\/N: (?<serial>\S+)/'
```

### 2. Processors

```yaml
modules:
    processors:
        data:
            -
                oid: ACME-SYSTEM-MIB::acmeCpuTable
                value: ACME-SYSTEM-MIB::acmeCpuUtilization
                num_oid: '.1.3.6.1.4.1.99999.2.1.1.2.{{ $index }}'
                descr: 'CPU {{ $index }}'
                type: acme-switch
```

For scalar processors:
```yaml
modules:
    processors:
        data:
            -
                oid: ACME-SYSTEM-MIB::acmeCpuUtil.0
                num_oid: '.1.3.6.1.4.1.99999.2.1.0'
                descr: 'Processor'
                type: acme-switch
```

### 3. Mempools

Provide at least two of these keys: `total`, `used`, `free`, `percent_used`.

```yaml
modules:
    mempools:
        data:
            -
                total: ACME-SYSTEM-MIB::acmeMemoryTotal.0
                used: ACME-SYSTEM-MIB::acmeMemoryUsed.0
                precision: 1024
                descr: 'Main Memory'
```

### 4. Health Sensors

Supported sensor classes include:
`temperature`, `fanspeed`, `voltage`, `current`, `power`, `state`, `humidity`, `frequency`, `dbm`.

#### Rules for Sensors
- **Scalars Supported**: You can use scalar values in the sensors module instead of only tables.
- **Textual OIDs Only**: Always prefer textual OIDs over numeric OIDs in discovery files. The sensors module only supports textual OIDs for `oid:` and `value:`.
- **Avoid `options:`**: Avoid `options:` at the class level. Define settings (such as `divisor:`, `multiplier:`, or `skip_value_lt:`) explicitly on each sensor entry in `data:`. It is better to be explicit.

#### Table-Based Sensors
If sensors use an SNMP table, `oid:` must be the table and `value:` must be the column in that table. Do not use `*Entry` names for `oid:`.

```yaml
modules:
    sensors:
        temperature:
            data:
                -
                    oid: ACME-SYSTEM-MIB::acmeTempTable
                    value: ACME-SYSTEM-MIB::acmeTempValue
                    num_oid: '.1.3.6.1.4.1.99999.3.1.1.2.{{ $index }}'
                    descr: '{{ ACME-SYSTEM-MIB::acmeTempDescr }}'
                    high_limit: ACME-SYSTEM-MIB::acmeTempHighLimit
                    warn_limit: ACME-SYSTEM-MIB::acmeTempWarnLimit
```

#### Scalar Sensors
If the sensor is a scalar OID, specify the scalar OID in `oid:` and omit `value:`. Omit `value:` whenever it matches `oid:`.

```yaml
modules:
    sensors:
        temperature:
            data:
                -
                    oid: ACME-SYSTEM-MIB::acmeChassisTemp.0
                    num_oid: '.1.3.6.1.4.1.99999.3.2.0'
                    descr: 'Chassis Temperature'
```

### 5. Sensor Indexing and Collision Rules

The composite key for a sensor in the database is:
`$poller_type-$sensor_class-$device_id-$sensor_type-$sensor_index`

- **Omit `index:` by default**:
  When omitted, LibreNMS uses the SNMP table index `$index` (or `0` for scalars). Keep definitions clean.
- **When to specify `index:`**:
  If you have two different tables in the same sensor class that produce identical row indexes (for example, row 1 in inlet temps and row 1 in outlet temps), they will collide. In this case, prefix the index:
  ```yaml
  index: 'inlet.{{ $index }}'
  ```
- **When colliding indexes are desired**:
  When you define alternative OIDs for the same sensor across different models or firmware versions using `skip_values`, use the same index (or omit `index:` in both). Only the matching definition discovers the sensor, and both models keep a consistent sensor index and RRD path.

### 6. State Sensors

Always set `state_name` and provide `generic` mapping:
- `generic: 0` = OK / Normal
- `generic: 1` = Warning
- `generic: 2` = Critical / Error
- `generic: 3` = Unknown

```yaml
modules:
    sensors:
        state:
            data:
                -
                    oid: ACME-SYSTEM-MIB::acmePsuTable
                    value: ACME-SYSTEM-MIB::acmePsuStatus
                    num_oid: '.1.3.6.1.4.1.99999.4.1.1.2.{{ $index }}'
                    descr: 'PSU {{ $index }}'
                    state_name: acmePsuStatus
                    states:
                        - { value: 1, descr: 'normal', generic: 0 }
                        - { value: 2, descr: 'warning', generic: 1 }
                        - { value: 3, descr: 'faulty', generic: 2 }
```

### 7. Filter Rows with `skip_values`

Use `skip_values` to ignore unpopulated sensors or select model-specific definitions:

```yaml
skip_values:
    -
        oid: ACME-SYSTEM-MIB::acmeTempStatus
        op: '!='
        value: 1
    -
        device: hardware
        op: 'starts'
        value: 'Model-X'
```

Supported `op` values:
`=`, `!=`, `==`, `!==`, `<`, `<=`, `>`, `>=`, `starts`, `ends`, `contains`, `regex`, `in_array`, `not_in_array`, `exists`.

> **Caution**: Do not skip valid `0` readings (such as 0 RPM on a failed fan). Discovery runs daily; skipping 0 can delete a failed sensor. Only skip when the physical hardware is absent.

---

## Step 4: Add Icons

Save SVG files:
- OS Icon: `html/images/os/<os>.svg` (Square, 32x32 px, no margins)
- OS Logo: `html/images/logos/<os>.svg` (Optional, wide format)

---

## Step 5: Create Test Data (Mandatory)

Tests verify OS detection and module polling. Every OS must have test files.

### 1. SNMP Recording (`tests/snmpsim/<os>.snmprec`)

Do not fabricate `snmprec` files. You must only capture `snmprec` files from an actual device or convert them from `snmpwalk` output from an actual device.

Methods to acquire `.snmprec` data:
1. Capture from a live device in LibreNMS:
   ```bash
   ./lnms dev:collect-snmprec <device_id> --variant=""
   ```
2. Capture directly from the actual device with snmpsim:
   ```bash
   snmprec.py --agent-udpv4-endpoint=<ip>:161 --community=<community> --output-file=tests/snmpsim/<os>.snmprec
   ```
3. Convert `snmpwalk` output from the actual device:
   Convert walk output into `<numeric_oid>|<type_code>|<value>` format using snmpsim tools or scripts.

> **Important**: Sanitize the recording to remove real credentials, SNMP community strings, public IP addresses, and private names. Do not break the data format or SNMP types when sanitizing. Add all OIDs that your OS discovery and polling definitions query.

### 2. Generate Test Data JSON (`tests/data/<os>.json`)

To create the JSON fixture for a new base OS:
```bash
./lnms dev:generate-test-data <os> --variant=""
```
> **Note**: You must pass `--variant=""` when creating a new fixture for the first time. Without it, the tool searches only for existing fixtures and will exit with an error.

For an OS variant:
- SNMP file: `tests/snmpsim/<os>_<variant>.snmprec` (variant must be lowercase)
- Generate command:
  ```bash
  ./lnms dev:generate-test-data <os> --variant="<variant>"
  ```

Do not edit `tests/data/<os>.json` manually. Always use `dev:generate-test-data` to update it.

---

## Step 6: Verify Tests

Run the test suite for the OS:
```bash
./lnms dev:check unit -o <os>
```

This command runs:
1. `OSDiscoveryTest`: Verifies OS name validity and detection against `.snmprec`.
2. `OSModulesTest`: Simulates the device with snmpsim and verifies discovered data against the JSON fixture.

You can also run specific unit tests:
```bash
./lnms test --filter=OSDiscoveryTest
./lnms test --filter=OSModulesTest
```

If you make changes to YAML discovery files during testing, clear the cache:
```bash
./lnms config:clear
```

All tests must pass before the task is complete.
