
# OS Updates

A small shell script that checks your system package manager for any
available updates. Supports `apt-get`/`pacman`/`yum`/`zypper` package
managers.

For pacman users automatically refreshing the database, it is
recommended you use an alternative database location
`--dbpath=/var/lib/pacman/checkupdate`

### Agent or SNMP Extend

=== "SNMP Extend"

    1. Download the script onto the desired host.

        ```bash
        wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/osupdate -O /etc/snmp/osupdate
        ```

    2. Make the script executable

        ```bash
        chmod +x /etc/snmp/osupdate
        ```

    3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:

        ```bash
        extend osupdate /etc/snmp/osupdate
        ```

    4. Restart snmpd on your host

        ```bash
        sudo systemctl restart snmpd
        ```

    !!! note "Debian/Ubuntu Apt"
        The apt-get depends on an updated package index. There are several ways to have your system run `apt-get update` automatically. 
        
        The easiest is to create `/etc/apt/apt.conf.d/10periodic` and pasting the following in it: 
        
        ```bash
        APT::Periodic::Update-Package-Lists "1"; 
        ```

        If you have `apticron`, `cron-apt` or `apt-listchanges` installed and configured, chances are that packages are already updated periodically .

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.

=== "Agent"

    [Install the agent](../Agent-Setup.md) on this device if it isn't already
    and copy the `osupdate` script to `/usr/lib/check_mk_agent/local/`

    Then uncomment the line towards the top marked to be uncommented if
    using it as a agent.

