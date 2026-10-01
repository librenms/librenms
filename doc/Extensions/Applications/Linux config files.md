# Linux config files

`linux_config_files` monitors the configuration files of a Linux
distribution. It uses the configuration management system of that
distribution. It supports ONLY RPM-BASED SYSTEMS (Fedora and RHEL) with
the rpmconf tool. The application counts the configuration files that
are out of sync. It then graphs this count.

Fedora/RHEL: rpmconf analyses the rpm configuration files with the RPM
Package Manager. It reports each new configuration file standard of an
upgraded or downgraded package. rpmconf usually gives a diff of the
current configuration file against the new standard configuration file.
The administrator then installs the new configuration file or keeps the
old one.

### SNMP Extend

1. Copy the python script, linux_config_files.py, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/linux_config_files.py -O /etc/snmp/linux_config_files.py
    ```

2. Make the script executable.

    ```bash
    chmod +x /etc/snmp/linux_config_files.py
    ```

3. Edit your `snmpd.conf` file and add:

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
