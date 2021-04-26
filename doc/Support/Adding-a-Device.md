source: Support/Adding-a-Device.md
path: blob/master/doc/

# Adding Device

You have two options for adding a new device into LibreNMS. You can
add a device via the `cli` or by using the `WebUI`.

## CLI

Using the command line via ssh you can add a new device by changing to
the directory of your LibreNMS install and typing (be sure to put the
correct details).

```bash
./addhost.php yourhostname [community] [v1|v2c] [port] [udp|udp6|tcp|tcp6]
```

As an example, if your device with the name `mydevice.example.com` is
configured to use the community `my_company` using snmp `v2c` then you
would enter:

```bash
./addhost.php mydevice.example.com my_company v2c
```

> Please note that if the community contains special characters such
> as `$` then you will need to wrap it in `'`. I.e: `'Pa$$w0rd'`.

## WebUI

Using the web interface, go to Devices and click Add Device. Enter the
details required for the device that you want to add and then click
'Add Host'. As an example, if your device is configured to use the
community `my_company` using snmp `v2c` then you would enter: SNMP
Port defaults to 161.

By default Hostname will be used for polling data. If you want
to get polling Device data via a specific IP-Address (e.g. Management IP)
fill out the optional field `Overwrite IP` with it's IP-Address.

![Add device](/img/webui_add_device.png)

### Ping Only Device

You can add ping only devices into LibreNMS through the WebUI. When
adding the device switch the SNMP button to "off". Device will be
added into LibreNMS as Ping Only Device and will show ICMP Response Graph.

- Hostname: IP address or DNS name.
- Hardware: Optional you can type in whatever you like.
- OS: Optional this will add the Device's OS Icon.

[How to add ping only devices](https://youtu.be/cjuByubg-uk)

![Ping Only](/img/add-ping-only.png)

If you would like to add devices automatically then you will probably
want to read the [Auto-discovery
Setup](../Extensions/Auto-Discovery.md) guide.

You may also want to add devices programmatically, if so, take a look
at our [API documentation](../API/index.md)
