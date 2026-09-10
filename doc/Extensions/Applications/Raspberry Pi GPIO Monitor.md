## Raspberry Pi GPIO Monitor

This SNMP extend script monitors the IO pins and the sensor modules on
your GPIO header.

### SNMP Extend

1: Install wiringpi on your Raspberry Pi. On a Debian-based system, run:

```bash
sudo apt-get install wiringpi
```

2: Download the script to your Raspberry Pi. 

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/rpigpiomonitor.php
    -O /etc/snmp/rpigpiomonitor.php
    ```

3: (optional) Download the example configuration to your Raspberry Pi. 
   
    ```bash 
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/rpigpiomonitor.ini
    -O /etc/snmp/rpigpiomonitor.ini
    ```

4: Make the script executable: 

    ```bash
    chmod +x /etc/snmp/rpigpiomonitor.php
    ```

5: Create or edit your `rpigpiomonitor.ini` file for your setup.

6: Validate your configuration with `rpigpiomonitor.php -validate`.

7: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend rpigpiomonitor /etc/snmp/rpigpiomonitor.php
    ```

8: Restart snmpd on your Raspberry Pi. If LibreNMS already holds the
Raspberry Pi, do a manual rediscovery.

