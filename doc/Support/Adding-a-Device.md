source: Support/Adding-a-Device.md

You have two options for adding a new device into LibreNMS. You can add a device via the `cli` or by using the 
`WebUI`.

### CLI

Using the command line via ssh you can add a new device by changing to the directory of your LibreNMS install and typing (be sure to put the correct details).

```bash
./addhost.php [community] [v1|v2c] [port] [udp|udp6|tcp|tcp6]
```

As an example, if your device is configured to use the community `my_company` using snmp `v2c` then you would enter:

```bash
./addhost.php my_company v2c
```

> Please note that if the community contains special characters such as `$` then you will need to wrap it in `'`. I.e: `'Pa$$w0rd'`.

### WebUI

Using the web interface, go to Devices and click Add Device. Enter the details required for the device that you want to add and then click 'Add Host'.
As an example, if your device is configured to use the community `my_company` using snmp `v2c` then you would enter:

![Add device](/img/webui_add_device.png)

If you would like to add devices automatically then you will probably want to read the [Auto-discovery Setup](/Extensions/Auto-Discovery.md) guide.

You may also want to add devices programatically, if so, take a look at our [API documentation](/API/#function-add_device)