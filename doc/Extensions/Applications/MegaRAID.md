## MegaRAID

This software from Broadcom/LSI let you monitor MegaRAID controller.
This is agent for snmpd.

1. Download the [external software](https://docs.broadcom.com/docs/1211132411799) and follow the included install instructions.

2. Add the following line to your `snmpd.conf` file (usually `/etc/snmp/snmpd.conf`)

    ```bash
    pass .1.3.6.1.4.1.3582 /usr/sbin/lsi_mrdsnmpmain
    ```

3. Restart snmpd on your host