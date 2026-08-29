## Nvidia GPU

### SNMP Extend

1. Copy the shell script, nvidia, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/nvidia -O /etc/snmp/nvidia
    ```

2. Make the script executable.

    ```bash
    chmod +x /etc/snmp/nvidia
    ```

3. Edit your `snmpd.conf` file and add:

    ```bash
    extend nvidia /etc/snmp/nvidia
    ```

4. Restart snmpd on your host.

    ```bash
    sudo systemctl restart snmpd
    ```

5. Make sure that `nvidia-smi` is installed. The Nvidia driver usually
   installs it.

    The GPU numbers on the graphs match the numbers of nvidia-smi
    sees them as being.

    For questions about what the various values are/mean, please see the
    nvidia-smi man file under the section covering dmon.

