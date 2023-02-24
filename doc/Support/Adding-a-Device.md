# Adding Device

You have two options for adding a new device into LibreNMS. You can
add a device via the `cli` or by using the `WebUI`.

## Via WebUI

Using the web interface, go to Devices and click Add Device. Enter the
details required for the device that you want to add and then click
'Add Host'. As an example, if your device is configured to use the
community `my_company` using snmp `v2c` then you would enter: SNMP
Port defaults to 161.

By default Hostname will be used for polling data. If you want
to get polling Device data via a specific IP-Address (e.g. Management IP)
fill out the optional field `Overwrite IP` with it's IP-Address.

![Add device](/img/webui_add_device.png)

## Via CLI

Using the command line via ssh you can add a new device by changing to
the directory of your LibreNMS install and typing (be sure to put the
correct details).

```bash
./lnms device:add yourhostname [--v1|--v2c] [-c yourSNMPcommunity]
```

You can use `./lnms device:add --help` for a list of available options and defaults.

As an example, if your device with the name `mydevice.example.com` is
configured to use the community `my_company` using snmp `v2c` then you
would enter:

```bash
./lnms device:add --v2c -c my_company mydevice.example.com
```

> Please note that if the community contains special characters such
> as `$` then you will need to wrap it in `'`. I.e: `'Pa$$w0rd'`.

## Ping Only Device

You can add ping only devices into LibreNMS through the WebUI or CLI. When
adding the device switch the SNMP button to "off". Device will be
added into LibreNMS as Ping Only Device and will show ICMP Response Graph.

- Hostname: IP address or DNS name.
- Hardware: Optional you can type in whatever you like.
- OS: Optional this will add the Device's OS Icon.

Via CLI this is done with `./lnms device:add [-P|--ping-only] yourhostname`

![Ping Only](/img/add-ping-only.png)

A How-to video can be found here: [How to add ping only devices](https://youtu.be/cjuByubg-uk)

## Automatic Discovery and API

If you would like to add devices automatically then you will probably
want to read the [Auto-discovery
Setup](../Extensions/Auto-Discovery.md) guide.

You may also want to add devices programmatically, if so, take a look
at our [API documentation](../API/index.md)
