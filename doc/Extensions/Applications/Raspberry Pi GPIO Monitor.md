## Raspberry Pi GPIO Monitor

SNMP extend script to monitor your IO pins or sensor modules connected to your GPIO header.

### SNMP Extend

1: Make sure you have wiringpi installed on your Raspberry Pi. In Debian-based systems for example you can achieve this by issuing:

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

5: Create or edit your `rpigpiomonitor.ini` file according to your needs.

6: Check your configuration with `rpigpiomonitor.php -validate`

7: Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend rpigpiomonitor /etc/snmp/rpigpiomonitor.php
    ```

8: Restart snmpd on your Raspberry Pi and, if your Raspberry Pi is already present in LibreNMS, perform a manual rediscover.

