# Proxmox

## Install prerequisites

=== "Debian/Ubuntu"

    ```bash
    apt install libpve-apiclient-perl
    ```

## SNMP Extend

2. Download the script onto the host.

    ```
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/proxmox -O /usr/local/bin/proxmox
    ```

3. Make the script executable.

    ```bash
    chmod +x /usr/local/bin/proxmox
    ```

4. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

    ```bash
    extend proxmox /usr/local/bin/proxmox
    ```

5. Note: if your snmpd does not run as root, call
   the script using sudo and modify the "extend" line

    ```bash
    extend proxmox /usr/bin/sudo /usr/local/bin/proxmox
    ```

    after, edit your sudo users (usually `visudo`) and add at the bottom:

    ```bash
    Debian-snmp ALL=(ALL) NOPASSWD: /usr/local/bin/proxmox
    ```

6. Restart snmpd on your host.

## Virtual Machines

With v2 of the script, LibreNMS shows the guests of the node on the Virtual Machines page. It shows QEMU guests and LXC containers.

The vminfo discovery module reads the guests. LibreNMS enables that module for the proxmox OS. Run the discovery of the device to see the

Install the script on each node of a cluster. Each node reports only its own guests.
