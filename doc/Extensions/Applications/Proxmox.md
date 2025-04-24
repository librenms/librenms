# Proxmox


## Install prerequisites

=== "Debian/Ubuntu"

    ```bash
    apt install libpve-apiclient-perl
    ```

## SNMP Extend

2. Download the script onto the desired host

    ```
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/proxmox -O /usr/local/bin/proxmox
    ```

3. Make the script executable

    ```bash
    chmod +x /usr/local/bin/proxmox
    ```

4. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

    ```bash
    extend proxmox /usr/local/bin/proxmox
    ```

5. Note: if your snmpd doesn't run as root, you might have to invoke
   the script using sudo and modify the "extend" line

    ```bash
    extend proxmox /usr/bin/sudo /usr/local/bin/proxmox
    ```

    after, edit your sudo users (usually `visudo`) and add at the bottom:

    ```bash
    Debian-snmp ALL=(ALL) NOPASSWD: /usr/local/bin/proxmox
    ```

6. Restart snmpd on your host