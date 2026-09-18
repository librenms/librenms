# Initial OS Detection and Discovery

This guide explains how to configure basic OS detection and initial metadata discovery for a device in LibreNMS.

### OS Detection

OS detection matches the operating system of a newly discovered device. Detection normally uses `sysObjectID` or `sysDescr`.

> **Important**: Avoid using `snmpget` in OS detection. It sends additional SNMP queries that make detection slower for all devices across your network.

First create the new OS file `resources/definitions/os_detection/pulse.yaml`.
This example works:

```yaml
os: pulse
text: 'Pulse Secure'
type: firewall
over:
    - { graph: device_bits, text: 'Device Traffic' }
    - { graph: device_processor, text: 'CPU Usage' }
    - { graph: device_mempool, text: 'Memory Usage' }
discovery:
    - sysObjectID:
        - .1.3.6.1.4.1.12532.
```

`icon`: defaults to the OS name (`html/images/os/<os>.svg`). Do not set `icon` if it matches the OS name. Only set `icon` if it differs from the OS name (for example, `icon: cisco` when sharing another vendor's icon).

`over`: a list of the graphs in the device header bar. These are the
mini graphs at the top right.

`discovery`: this example detects the new OS with sysObjectID. This
method is the preferred one. Other options are available:

- `sysObjectID` the preferred operator. It tests whether the sysObjectID
  starts with one of the strings under this item
- `sysDescr` use it with sysObjectID where necessary. It tests whether
  the sysDescr holds one of the strings under this item
- `sysObjectID_regex` do not use this operator. It tests the
  sysObjectID against the regular expressions under this item
- `sysDescr_regex` do not use this operator. It tests the sysDescr
  against the regular expressions under this item
- `snmpget` use it only when no other method works. It gets an OID and
  compares it to a value.
```yaml
discovery:
    -
      snmpget:
        - oid: MIB-NAME::someoid
        - op: <["=","!=","==","!==","<=",">=","<",">","starts","ends","contains","regex","not_starts","not_ends","not_contains","not_regex","in_array","not_in_array","exists"]>
        - value: <'string' | boolean>
```
- `_except` add it to any operator above to exclude an element. For
  example:

```yaml
discovery:
    -
      sysObjectID:
          - .1.3.6.1.4.1.12532.
      sysDescr_except:
          - 'Not some pulse'
```

`group`: it puts several operating systems into one group. For example,
ios, nx-os, and iosxr are in the group `cisco`.

`bad_ifXEntry`: a list of the models without ifXEntry support. LibreNMS
then ignores ifXEntry on these models:

```yaml
 bad_ifXEntry:
     - cisco1941
     - cisco886Va
     - cisco2811
```

`mib_dir`: the OS name is included in the MIB search path by default (`mibs/<os>`).
Do not add `mib_dir: <os>`. Only set `mib_dir` if the directory differs from
the OS name (for example, `mib_dir: juniper`). Give only one directory. An array is not valid.

```yaml
mib_dir: juniper
```

Do not set modules to `false` in the OS definition. That is what discovery is for.
Discovery dynamically tests whether the device supports each module. Only use
`discovery_modules` or `poller_modules` to enable optional modules that are disabled
globally by default:

```yaml
discovery_modules:
     cisco-cef: true
     slas: true
```

##### Discovery Logic

PHP converts the YAML to an array. Take this YAML:

```yaml
discovery:
  - sysObjectID: foo
  -
    sysDescr: [ snafu, exodar ]
    sysObjectID: bar

```

The discovery array in PHP is:

```php
[
     [
       "sysObjectID" => "foo",
     ],
     [
       "sysDescr" => [
         "snafu",
         "exodar",
       ],
       "sysObjectID" => "bar",
     ]
]
```

The discovery logic is:

1. One of the first level items must match
1. ALL of the second level items must match (sysObjectID, sysDescr)
1. One of the third level items (foo, [snafu,exodar], bar) must match

In the example above:

- `sysObjectID: foo, sysDescr: ANYTHING` matches
- `sysObjectID: bar, sysDescr: ANYTHING` does not match
- `sysObjectID: bar, sysDescr: exodar` matches
- `sysObjectID: bar, sysDescr: snafu` matches

#### OS discovery

OS discovery also collects standard data about the OS. Give this data
in the discovery YAML file
`resources/definitions/os_discovery/<os>.yaml`. For a more complex
collection, use `LibreNMS/OS/<os>.php`.

- `version` the OS version of the device.
- `hardware` the hardware version of the device. An example is 'WS-C3560X-24T-S'.
- `features` the features of the device, for example a list of the
  enabled software features.
- `serial` the main serial number of the device.

##### Yaml based OS discovery

- `sysDescr_regex` it applies one or more regular expressions to the
  sysDescr and extracts the named groups. This data has the lowest
  precedence
- `<field>` one or more OIDs of the data. LibreNMS uses the first
  response that is not empty
- `<field>_regex` it extracts the value from the OID data. It must use
  a named group
- `<field>_template` it combines several OID results into a final
  string value. LibreNMS trims the result
- `<field>_replace` an array of replacements in the form
  ['search regex', 'replace'], or a regular expression to remove
- `hardware_mib` the MIB that converts the sysObjectID to the hardware.
  `hardware_regex` can then process the result

```yaml
modules:
    os:
        sysDescr_regex: '/(?<hardware>MSM\S+) .* Serial number (?<serial>\S+) - Firmware version (?<version>\S+)/'
        features: UPS-MIB::upsIdentAttachedDevices.0
        hardware:
            - ENTITY-MIB::entPhysicalName.1
            - ENTITY-MIB::entPhysicalHardwareRev.1
        hardware_template: '{{ ENTITY-MIB::entPhysicalName.1 }} {{ ENTITY-MIB::entPhysicalHardwareRev.1 }}'
        serial: ENTITY-MIB::entPhysicalSerialNum.1
        version: ENTITY-MIB::entPhysicalSoftwareRev.1
        version_regex: '/V(?<version>.*)/'
```

##### PHP based OS discovery

```php
public function discoverOS(\App\Models\Device $device): void
{
    $response = SnmpQuery::next(['NAS-MIB::enclosureModel', 'NAS-MIB::enclosureSerialNum', 'ENTITY-MIB::entPhysicalFirmwareRev']);
    $device->version = $response->value('ENTITY-MIB::entPhysicalFirmwareRev');
    $device->hardware = $response->value('NAS-MIB::enclosureModel');
    $device->serial = $response->value('NAS-MIB::enclosureSerialNum');
}
```

### MIBs

Check thoroughly for existing vendor MIBs first (check vendor websites,
support downloads, device firmware, or MIB repositories).

If vendor MIBs exist:

- Include vendor MIBs verbatim as provided by the vendor. Do not make any
  modifications to MIB files.
- Put the MIBs in a vendor directory (`mibs/<vendor>/`). The OS name is
  included in the MIB search path by default (`mibs/<os>`). Only set `mib_dir`
  in the OS detection file if the directory differs from the OS name.
- Name each file to match the MIB module definition line exactly (for
  example, `MYVENDOR-SYSTEM-MIB`). Do not add a file extension (such as
  `.mib` or `.txt`).
- Do not include standard RFC MIBs in vendor directories.
- Add only the MIBs that LibreNMS uses. Do not add unused vendor MIBs.

If no MIB exists (check thoroughly):

- If you have detailed SNMP information from the vendor's SNMP specification
  documentation, you can create a MIB from that documentation.
- Write standard SMIv2 MIB syntax with valid module headers, imports, object
  identifiers, and object types.
- Save the file as `mibs/<vendor>/<MODULE-NAME>` without any file extension.

### Icon and Logo

Use an SVG image where possible. An SVG image scales and looks correct
on a HiDPI screen. Without an SVG image, use a png image.

Create an SVG image of the icon and of the logo. Legacy PNG bitmaps
also work, but they look bad on a HiDPI screen.

- A vector image must have no padding.
- The file must not be larger than 20 Kb. Simplify the paths to make a
  large file smaller.
- Use plain SVG without gzip compression.
- The SVG root element must hold only viewBox. It must have no length
  attribute and no width attribute.

##### Icon

- Save the icon SVG to `html/images/os/$os.svg`.
- An icon must look correct at 32x32 px.
- We prefer a square icon to a full logo with text.
- Remove the small ornaments that are not visible at a width of 32 px,
  such as ® or ™.

##### Logo

- Save the logo SVG to `html/images/logos/$os.svg`.
- A logo can have any dimension. It is usually wide and holds the
  company name.
- Without a logo, LibreNMS uses the icon.

##### Hints

Hints for [Inkscape](https://inkscape.org/):

- Open a PDF file or an EPS file to extract the logo.
- Ungroup the elements to isolate the logo.
- Use `Path -> Simplify` to simplify paths of large files.
- Use `File -> Document Properties… -> Resize page to content…` to remove padding.
- Use `File -> Clean up document` to remove unused gradients, patterns, or markers.
- Use `File -> Save As -> Plain SVG` to save the final image.

An optimization of the SVG can reduce the file size to less than 20 %.
[SVG Optimizer](https://github.com/svg/svgo) does this work. An [online
version](https://jakearchibald.github.io/svgomg/) is also available.

#### The final check

Discovery

```bash
lnms device:discover -vv HOSTNAME
```

Polling

```bash
lnms device:poll HOSTNAME
```

All the collected values then appear in LibreNMS.

Note: after several changes to the discovery files of the OS, the cache
can hold an earlier edit. If the final check gives an unexpected
result, remove the cache file first:

```bash
lnms config:clear
```
