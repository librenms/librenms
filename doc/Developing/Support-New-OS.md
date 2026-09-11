# Adding or Updating Device OS Support

This guide explains how to add support for a new operating system or update existing OS support in LibreNMS.

AI agents and automated tools can refer to the dedicated skill:
[.agents/skills/add-os-support/SKILL.md](file:///.agents/skills/add-os-support/SKILL.md).

---

## Quick Navigation

Select your task below to jump to the relevant section:

| Task | Primary File Location | Guide Link |
| --- | --- | --- |
| **Add a New OS** | End-to-end steps | [Workflow 1: Add a New OS](#workflow-1-add-a-new-os) |
| **Update an Existing OS** | Add sensors, CPUs, or models | [Workflow 2: Update an Existing OS](#workflow-2-update-an-existing-os) |
| **Device Detection** | `resources/definitions/os_detection/<os>.yaml` | [Initial Detection](os/Initial-Detection.md) |
| **Hardware & Metadata** | `resources/definitions/os_discovery/<os>.yaml` | [OS Discovery](os/Initial-Detection.md#os-discovery) |
| **Processors & Memory** | `resources/definitions/os_discovery/<os>.yaml` | [Memory & CPU](os/Mem-CPU-Information.md) |
| **Health Sensors** | `resources/definitions/os_discovery/<os>.yaml` | [Health Sensors](os/Health-Information.md) |
| **Wireless Metrics** | `LibreNMS/OS/<Os>.php` | [Wireless Sensors](os/Wireless-Sensors.md) |
| **Vendor MIBs** | `mibs/<vendor>/` | [Adding MIBs](os/Initial-Detection.md#mibs) |
| **Icons & Logos** | `html/images/os/` & `html/images/logos/` | [Icons and Logos](os/Initial-Detection.md#icon-and-logo) |
| **Testing & Fixtures** | `tests/snmpsim/` & `tests/data/` | [Unit Tests](os/Test-Units.md) |

---

## Workflows

### Workflow 1: Add a New OS

Use this workflow when LibreNMS does not yet support the target operating system:

1. **Vendor MIBs**:
   - Check if LibreNMS already includes the vendor MIB.
   - If not, save vendor MIB files in `mibs/<vendor>/`. Name each file to match the MIB module definition line, with no file extension.
   - See [Adding MIBs](os/Initial-Detection.md#mibs).
2. **OS Detection**:
   - Create `resources/definitions/os_detection/<os>.yaml`.
   - Match the device using `sysObjectID` (prefix match) and `sysDescr` (substring match).
   - See [Initial Detection](os/Initial-Detection.md).
3. **OS Discovery**:
   - Create `resources/definitions/os_discovery/<os>.yaml`.
   - Define system metadata (`version`, `hardware`, `serial`).
   - Add [CPU and Memory](os/Mem-CPU-Information.md) monitoring.
   - Add [Health Sensors](os/Health-Information.md) (temperature, fan, voltage, state, power).
4. **Icons**:
   - Add a 32x32 px square SVG icon to `html/images/os/<os>.svg`.
   - Optionally add a wide logo to `html/images/logos/<os>.svg`.
   - See [Icons and Logos](os/Initial-Detection.md#icon-and-logo).
5. **Test Fixtures (Mandatory)**:
   - Capture a real SNMP recording: `tests/snmpsim/<os>.snmprec`.
   - Generate expected test data JSON: `./lnms dev:generate-test-data <os> --variant=""`.
   - Verify tests pass: `./lnms dev:check unit -o <os>`.
   - See [Unit Tests](os/Test-Units.md).

### Workflow 2: Update an Existing OS

Use this workflow to add sensors, processors, mempools, or support for a new hardware model to an existing OS:

1. **Inspect Existing Definitions**:
   - Review `resources/definitions/os_discovery/<os>.yaml` for current configurations.
2. **Add or Update Definitions**:
   - Add new sensors, processors, or memory pools in YAML.
   - For alternative definitions of the exact same sensor, keep identical indexes with `skip_values`.
   - Add any missing vendor MIBs to `mibs/<vendor>/`.
3. **Update Test Fixtures**:
   - To update existing base module test data: `./lnms dev:generate-test-data <os> -m <module>`.
   - To add a new hardware variant: capture `tests/snmpsim/<os>_<variant>.snmprec` and run `./lnms dev:generate-test-data <os> --variant="<variant>"`.
4. **Verify**:
   - Run module verification: `./lnms dev:check unit -o <os> --os-modules-only`.
   - See [Unit Tests](os/Test-Units.md).

---

## Core Rules & Conventions

### Naming & Scope
- **OS Name**: Use only lowercase letters, digits, and hyphens (`/^[a-z0-9\-]+$/`). Prefer actual OS names over marketing names if known (for example, `ftos`, `routeros`).
- **OS Scope**: If multiple devices share the same MIBs, they belong to the same OS. Do not create a separate OS for each hardware model. Discovery handles model differences.

### Detection & Discovery
- **YAML Discovery**: Always use YAML discovery (`resources/definitions/os_discovery/<os>.yaml`). Only use PHP classes when YAML cannot do the task.
- **OS Detection**: Match with `sysObjectID` or `sysDescr`. Avoid `snmp_get` in detection because it slows discovery for all devices.
- **Defaults for `icon` and `mib_dir`**:
  - `mibs/<os>` is included in the MIB search path by default. Do not add `mib_dir: <os>`. Only set `mib_dir:` if the directory differs from the OS name.
  - `icon:` defaults to the OS name. Do not set `icon:` if it matches the OS name; only set it if it differs.
- **Do Not Disable Modules**: Do not set `discovery_modules` or `poller_modules` to `false` in the OS definition. Discovery dynamically probes and detects supported features.

### OIDs & Sensors
- **Textual OIDs**: Always prefer textual OIDs over numeric OIDs in discovery files (for example, `ENTITY-MIB::entPhysicalName`). The sensors module only supports textual OIDs for `oid:` and `value:`.
- **Scalars Supported**: You can use scalar values in the sensors module instead of only tables.
- **Tables vs. Scalars**:
  - For SNMP tables: `oid:` must be the table (for example, `ENTITY-SENSOR-MIB::entPhySensorTable`), and `value:` must be the column in that table (for example, `ENTITY-SENSOR-MIB::entPhySensorValue`). Do not use `*Entry` for `oid:`; use `*Table` instead.
  - For scalar sensors: specify the scalar OID in `oid:` (including instance `.0`), and omit `value:` (since it matches `oid:`).
- **Avoid `options:`**: Define settings explicitly on each sensor entry in `data:`. It is better to be explicit.
- **Sensor Indexing**: Omit `index:` unless auto-generated table row indexes collide. Let discovery probe walks detect supported OIDs. When providing alternative definitions via `skip_values`, identical indexes are desired.

### Vendor MIBs
- **Check Thoroughly First**: Check vendor websites, support portals, firmware packages, or MIB repositories.
- **Verbatim Inclusion**: Include vendor MIBs verbatim as provided by the vendor. Do not make any modifications to MIB files.
- **Missing MIBs**: If no MIB exists after thorough checking, but detailed SNMP specifications are available from the vendor, you can create a standard SMIv2 MIB from that documentation.
- **Placement**: Save in `mibs/<vendor>/`. Name the file to match the MIB module definition line exactly, without a file extension. Do not include standard RFC MIBs in vendor directories.

### Testing & Verification
- **Real Device Data Only**: Every OS must include test fixtures (`tests/snmpsim/<os>.snmprec` and `tests/data/<os>.json`).
- **No Fabricated Files**: Do not fabricate `snmprec` files. Only capture them from an actual device or convert them from `snmpwalk` output from an actual device.
- **Sanitization**: Sanitize recordings to remove private IPs, passwords, and community strings. Do not break SNMP data types or OID structures during sanitization.
- **Fixture Generation**: For new base fixtures, pass `--variant=""`: `./lnms dev:generate-test-data <os> --variant=""`.
- **Run Tests**: Run `./lnms dev:check unit -o <os>`. All tests must pass before submitting a pull request.

---

## Detailed Topic Guides

For detailed options, syntax, and examples, refer to the individual guides:

- **[Initial Detection](os/Initial-Detection.md)**: Device detection matching, MIB directories, and icon guidelines.
- **[Health & Sensor Information](os/Health-Information.md)**: Temperature, fan, voltage, power, and state sensors (table and scalar).
- **[Memory & CPU Information](os/Mem-CPU-Information.md)**: YAML processor and mempool definitions.
- **[Wireless Sensors](os/Wireless-Sensors.md)**: Wireless interfaces and client metrics.
- **[Custom Graphs](os/Custom-Graphs.md)**: Custom graphing and device metrics.
- **[Unit Tests](os/Test-Units.md)**: SNMP simulation, recording, fixture generation, and test execution.
- **[Optional OS Settings](os/Settings.md)**: Interface filtering, bad ifXEntry handling, and config overrides.

---

## Interactive Helper Script

An interactive script can create basic definitions from an existing device:

```bash
./scripts/new-os.php -h 101 -o test-os -t network -v cisco
```

This script is in pre-beta. It adds basic sensors, but it does not add state sensors. Report problems on [Discord](https://t.libren.ms/discord).
