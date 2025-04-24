# Linux config files

`linux_config_files` is an application intended to monitor a Linux distribution's configuration files via that distribution's configuration management tool/system.  At this time, ONLY RPM-based (Fedora/RHEL) SYSTEMS ARE SUPPORTED utilizing the rpmconf tool.  The `linux_config_files` application collects and graphs the total count of configuration files that are out of sync and graphs that number.

Fedora/RHEL: Rpmconf is a utility that analyzes rpm configuration files using the RPM Package Manager.  Rpmconf reports when a new configuration file standard has been issued for an upgraded/downgraded piece of software.  Typically, rpmconf is used to provide a diff of the current configuration file versus the new, standard configuration file.  The administrator can then choose to install the new configuration file or keep the old one.

### SNMP Extend

1. Copy the python script, linux_config_files.py, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/linux_config_files.py -O /etc/snmp/linux_config_files.py
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/linux_config_files.py
    ```

3. Edit your snmpd.conf file and add:

    ```bash
    extend linux_config_files /etc/snmp/linux_config_files.py
    ```

4. (Optional on an RPM-based distribution) Create a /etc/snmp/linux_config_files.json file and specify the following:

    ```json
    {
        "pkg_system": "rpm",
        "pkg_tool_cmd": "/bin/rpmconf",
    }
    ```

    | Parameter        | Description                                | Default Value |
    | ----------------- | ------------------------------------------ | ------------- |
    | pkg_system       | String designating the distribution name,    | "rpm"         |
    | pkg_tool_cmd      | String path to the package tool binary    | "/sbin/rpmconf"|

5. Restart snmpd.

    ```bash
    sudo systemctl restart snmpd
    ```
