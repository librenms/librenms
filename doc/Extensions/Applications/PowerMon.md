# PowerMon

PowerMon tracks the power usage on your host and can report on both consumption and cost, using a python script installed on the host.

[PowerMon consumption graph](/img/example-app-powermon-consumption-02.png)

Currently the script uses one of two methods to determine current power usage:

* ACPI via `libsensors`

* `HP-Health` (HP Proliant servers only)

The ACPI method is quite unreliable as it is usually only implemented by
battery-powered devices, e.g. laptops. YMMV. However, it's possible to support
any method as long as it can return a power value, usually in Watts.

!!! tip
    You can achieve this by adding a method and a function for that method to the script. It should be called by getData() and return a dictionary.

Because the methods are unreliable for all hardware, you need to declare to the script which method to use. The are several options to assist with testing, see the `--help`.

## SNMP Extend

### Initial setup

1. Download the python script onto the host:

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/powermon-snmp.py -O /usr/local/bin/powermon-snmp.py
    ```

2. Make the script executable:

    ```bash
    chmod +x /usr/local/bin/powermon-snmp.py
    ```

3. Edit the script and set the cost per kWh for your supply. You must uncomment
this line for the script to work:
```
vi /usr/local/bin/powermon-snmp.py
#costPerkWh = 0.15
```

4. Choose you method below:

    === "Method 1. sensors"

        * Install dependencies:
        ```
        dnf install lm_sensors
        pip install PySensors
        ```

        * Test the script from the command-line. For example:
        ```
        $ /usr/local/bin/powermon-snmp.py -m sensors -n -p
        {
          "meter": {
            "0": {
              "reading": 0.0
            }
          },
          "psu": {},
          "supply": {
            "rate": 0.15
          },
          "reading": "0.0"
        }
        ```

        If you see a reading of `0.0` it is likely this method is not supported for
        your system. If not, continue.

    === "Method 2. hpasmcli"

        * Obtain the hp-health package for your system. Generally there are
        three options:
            * Standalone package from [HPE Support](https://support.hpe.com/hpsc/swd/public/detail?swItemId=MTX-c0104db95f574ae6be873e2064#tab2)
            * From the HP Management Component Pack (MCP).
            * Included in the [HP Service Pack for Proliant (SPP)](https://support.hpe.com/hpesc/public/docDisplay?docId=emr_na-a00026884en_us)

        * If you've downloaded the standalone package, install it. For example:
        ```
        rpm -ivh hp-health-10.91-1878.11.rhel8.x86_64.rpm
        ```

        * Check the service is running:
        ```
        systemctl status hp-health
        ```

        * Test the script from the command-line. For example:
        ```
        $ /usr/local/bin/powermon-snmp.py -m hpasmcli -n -p
        {
          "meter": {
            "1": {
              "reading": 338.0
            }
          },
          "psu": {
            "1": {
              "present": "Yes",
              "redundant": "No",
              "condition": "Ok",
              "hotplug": "Supported",
              "reading": 315.0
            },
            "2": {
              "present": "Yes",
              "redundant": "No",
              "condition": "FAILED",
              "hotplug": "Supported"
            }
          },
          "supply": {
            "rate": 0.224931
          },
          "reading": 338.0
        }
        ```

        If you see a reading of `0.0` it is likely this method is not supported for
        your system. If not, continue.

### Finishing Up

5. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add the following:
```
extend  powermon   /usr/local/bin/powermon-snmp.py -m hpasmcli
```

    > NOTE: Avoid using other script options in the snmpd config as the results may not be
    > interpreted correctly by LibreNMS.

6. Reload your snmpd service:
```
systemctl reload snmpd
```

7. You're now ready to enable the application in LibreNMS.
