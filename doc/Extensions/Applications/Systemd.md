# Systemd

The systemd application polls systemd and scrapes systemd units' load, activation, and sub states.

### SNMP Extend

1. Copy the python script, systemd.py, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/systemd.py -O /etc/snmp/systemd.py
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/systemd.py
    ```

3. Edit your snmpd.conf file and add:

    ```bash
    extend systemd /etc/snmp/systemd.py
    ```

4. (Optional) Create a /etc/snmp/systemd.json file and specify:
    1. "systemctl_cmd" - String path to the systemctl binary [Default: "/usr/bin/systemctl"]
    2. "include_inactive_units" - True/False string to include inactive units in results [Default: "False"]
```
{
    "systemctl_cmd": "/bin/systemctl",
    "include_inactive_units": "True"
}
```

5. (Optional) If you have SELinux in Enforcing mode, you must add a module so the script can access systemd state:

```bash
cat << EOF > snmpd_systemctl.te
module snmpd_systemctl 1.0;

require {
        type snmpd_t;
        type systemd_systemctl_exec_t;
        type init_t;
        class file { execute execute_no_trans map open read };
        class unix_stream_socket connectto;
        class system status;
}

#============= snmpd_t ==============
allow snmpd_t init_t:system status;
allow snmpd_t init_t:unix_stream_socket connectto;
allow snmpd_t systemd_systemctl_exec_t:file { execute execute_no_trans map open read };
EOF
checkmodule -M -m -o snmpd_systemctl.mod snmpd_systemctl.te
semodule_package -o snmpd_systemctl.pp -m snmpd_systemctl.mod
semodule -i snmpd_systemctl.pp
```

6. Restart snmpd.

    ```bash
    sudo systemctl restart snmpd
    ```
