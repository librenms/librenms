## Linux NFS Server

The [generalized NFS support](NFS.md) replaces this application.

Export the NFS stats from as server.

### SNMP Extend

1. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add :

    !!! note
        find out where cat is located using : `which cat`

    ```bash
    extend nfs-server /bin/cat /proc/net/rpc/nfsd
    ```

2. reload snmpd service to activate the configuration

    ```bash
    sudo systemctl reload snmpd
    ```