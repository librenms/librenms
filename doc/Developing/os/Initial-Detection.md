source: Developing/os/Initial-Detection.md
path: blob/master/doc/

This document will provide the information you should need to add
basic detection for a new OS.

### Discovery

Discovery is now all done by yaml files, you do not and should not
create a php file for discovery.

Create the new OS file which should be called
`includes/definitions/pulse.yaml`. Here is a working example:

```yaml
os: pulse
text: 'Pulse Secure'
type: firewall
icon: pulse
over:
    - { graph: device_bits, text: 'Device Traffic' }
    - { graph: device_processor, text: 'CPU Usage' }
    - { graph: device_mempool, text: 'Memory Usage' }
discovery:
    - sysObjectID:
        - .1.3.6.1.4.1.12532.
```

`over`: This is a list of the graphs which will be shown within the
device header bar (mini graphs top right).

`discovery`: Here we are detecting this new OS using sysObjectID, this
is the preferred method for detection.  Other options are available:

- `sysObjectID` The preferred operator. Checks if the sysObjectID
  starts with one of the strings under this item
- `sysDescr` Use this in addition to sysObjectID if required. Check
  that the sysDescr contains one of the strings under this item
- `sysObjectID_regex` Please avoid use of this. Checks if the
  sysObjectID matches one of the regex statements under this item
- `sysDescr_regex` Please avoid use of this. Checks if the sysDescr
  matches one of the regex statements under this item
- `snmpget` Do not use this unless none of the other methods
  work. Fetch an oid and compare it against a value.
- `_except` You can add this to any of the above to exclude that
  element. As an example:

```yaml
discovery:
    -
      sysObjectID:
          - .1.3.6.1.4.1.12532.
      sysDescr_except:
          - 'Not some pulse'
```

`group`: You can group certain OS' together by using group, for
instance ios, nx-os, iosxr are all within a group called cisco.

`bad_ifXEntry`: This is a list of models for which to tell LibreNMS
that the device doesn't support ifXEntry and to ignore it:

```yaml
 bad_ifXEntry:
     - cisco1941
     - cisco886Va
     - cisco2811
```

`mib_dir`: You can use this to specify an additional directory to
look in for MIBs. An array is not accepted, only one directory may be specified.

```yaml
mib_dir: juniper
```

`poller_modules`: This is a list of poller modules to either enable
(1) or disable (0). Check `misc/config_definitions.json` to see which
modules are enabled/disabled by default.

```yaml
poller_modules:
    cisco-ace-serverfarms: false
    cisco-ace-loadbalancer: false
```

`discovery_modules`: This is the list of discovery modules to either
enable (1) or disable (0). Check `misc/config_definitions.json` to see
which modules are enabled/disabled by default.

```yaml
discovery_modules:
     cisco-cef: true
     cisco-sla: true
     cisco-mac-accounting: false
```

##### Discovery Logic

YAML is converted to an array in PHP.  Consider the following YAML:

```yaml
discovery:
  - sysObjectID: foo
  -
    sysDescr: [ snafu, exodar ]
    sysObjectID: bar

```

This is how the discovery array would look in PHP:

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

The logic for the discovery is as follows:

1. One of the first level items must match
1. ALL of the second level items must match (sysObjectID, sysDescr)
1. One of the third level items (foo, [snafu,exodar], bar) must match

So, considering the example:

- `sysObjectID: foo, sysDescr: ANYTHING` matches
- `sysObjectID: bar, sysDescr: ANYTHING` does not match
- `sysObjectID: bar, sysDescr: exodar` matches
- `sysObjectID: bar, sysDescr: snafu` matches

#### OS discovery

OS discovery collects additional standardized data about the OS.  These are specified in
the discovery yaml `includes/definitions/discovery/<os>.yaml` or `LibreNMS/OS/<os>.php` if
more complex collection is required.

- `version` The version of the OS running on the device.
- `hardware` The hardware version for the device. For example: 'WS-C3560X-24T-S'
- `features` Features for the device, for example a list of enabled software features.
- `serial` The main serial number of the device.

##### Yaml based OS discovery

- `sysDescr_regex` apply a regex or list of regexes to the sysDescr to extract named groups, this data has the lowest precedence
- `<field>` specify an oid or list of oids to attempt to pull the data from, the first non-empty response will be used
- `<field>_regex` parse the value out of the returned oid data, must use a named group
- `<field>_template` combine multiple oid results together to create a final string value.  The result is trimmed.
- `hardware_mib` MIB used to translate sysObjectID to get hardware. hardware_regex can process the result.

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
    $info = snmp_getnext_multi($this->getDeviceArray(), ['enclosureModel', 'enclosureSerialNum', 'entPhysicalFirmwareRev'], '-OQUs', 'NAS-MIB:ENTITY-MIB');
    $device->version = $info['entPhysicalFirmwareRev'];
    $device->hardware = $info['enclosureModel'];
    $device->serial = $info['enclosureSerialNum'];
}
```

### MIBs

If the device has MIBs available and you use it in the detection then you can add these in. It is highly
recommended that you add mibs to a vendor specific directory. For instance HP mibs are in `mibs/hp`. Please
 ensure that these directories are specified in the yaml detection file, see `mib_dir` above.

### Icon and Logo

It is highly recommended to use SVG images where possible, these scale and provide a nice visual image for users
with HiDPI screens. If you can't find SVG images then please use png.

Create an SVG image of the icon and logo.  Legacy PNG bitmaps are also supported but look bad on HiDPI.

- A vector image should not contain padding.
- The file should not be larger than 20 Kb. Simplify paths to reduce large files.
- Use plain SVG without gzip compression.
- The SVG root element must not contain length and width attributes, only viewBox.

##### Icon

- Save the icon SVG to `html/images/os/$os.svg`.
- Icons should look good when viewed at 32x32 px.
- Square icons are preferred to full logos with text.
- Remove small ornaments that are almost not visible when displayed with 32px width (e.g. ® or ™).

##### Logo

- Save the logo SVG to `html/images/logos/$os.svg`.
- Logos can be any dimension, but often are wide and contain the company name.
- If a logo is not present, the icon will be used.

##### Hints

Hints for [Inkscape](https://inkscape.org/):

- You can open a PDF or EPS to extract the logo.
- Ungroup elements to isolate the logo.
- Use `Path -> Simplify` to simplify paths of large files.
- Use `File -> Document Properties… -> Resize page to content…` to remove padding.
- Use `File -> Clean up document` to remove unused gradients, patterns, or markers.
- Use `File -> Save As -> Plain SVG` to save the final image.

By optimizing the SVG you can shrink the file size in some cases to less than 20 %.
[SVG Optimizer](https://github.com/svg/svgo) does a great job. There
is also an [online version](https://jakearchibald.github.io/svgomg/).

#### The final check

Discovery

```bash
./discovery.php -d -h HOSTNAME
```

Polling

```bash
./poller.php -h HOSTNAME
```

At this step we should see all the values retrieved in LibreNMS.

Note: If you have made a number of changes to either the OS's
Discovery files, it's possible earlier edits have been cached. As
such, if you do not get expected behaviour when completing the final
check above, try removing the cache file first:

```bash
rm -f cache/os_defs.cache
```
