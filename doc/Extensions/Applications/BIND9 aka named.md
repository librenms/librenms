# BIND9 aka named

### 1. Configure Cache file

Create stats file with appropriate permissions:

```bash
touch /var/cache/bind/stats
chown bind:bind /var/cache/bind/stats
```
Change `user:group` to the user and group that's running bind/named.

### 2. Bind/named configuration:

```json
options {
    ...
    statistics-file "/var/cache/bind/stats";
    zone-statistics yes;
    ...
};
```

### 3. Restart your bind9/named after changing the configuration.

```bash
sudo systemctl restart bind
```

### 4. Verify that everything works
You can verify by executing `rndc stats && cat /var/cache/bind/stats`.

!!! failure
    In case you get a `Permission Denied` error, make sure you changed the ownership correctly.

!!! note
    Also be aware that this file is appended to each time `rndc stats` is called. Given this it is suggested you setup file rotation for it. Alternatively you can also set zero_stats to 1 in the config.

### 6. Install Prerequisites

The script for this also requires the Perl module `File::ReadBackwards`.

=== "FreeBSD"
    ```bash
    pkg install p5-File-ReadBackwards
    ```
=== "CentOS/RedHat"
    ```bash
    yum install perl-File-ReadBackwards
    ```
=== "Debian/Ubuntu"
    ```bash
    sudo apt-get install libfile-readbackwards-perl
    ```

If it is not available, it can be installed by `cpan -i File::ReadBackwards`.

### 7. You may possibly need to configure the agent/extend script as well.

The config file's path defaults to the same path as the script, but
with .config appended. So if the script is located at
`/etc/snmp/bind`, the config file will be
`/etc/snmp/bind.config`. Alternatively you can also specify a config
via `-c $file`.

Anything starting with a # is comment. The format for variables are
$variable=$value. Empty lines are ignored. Spaces and tabs at either
the start or end of a line are ignored.

Content of an example /etc/snmp/bind.config . Please edit with your
own settings.

```
rndc = The path to rndc. Default: /usr/bin/env rndc
call_rndc = A 0/1 boolean on whether or not to call rndc stats.
    Suggest to set to 0 if using netdata. Default: 1
stats_file = The path to the named stats file. Default: /var/cache/bind/stats
agent = A 0/1 boolean for if this is being used as a LibreNMS
    agent or not. Default: 0
zero_stats = A 0/1 boolean for if the stats file should be zeroed
    first. Default: 0 (1 if guessed)
```

If you want to guess at the configuration, call the script with `-g`
and it will print out what it thinks it should be.

## Configure Agent or Extend

=== "SNMP Extend"

    1. Copy the bind shell script, to the desired host.

        ```bash
        wget https://github.com/librenms/librenms-agent/raw/master/snmp/bind -O /etc/snmp/bind
        ```

    2. Make the script executable

        ```bash
        chmod +x /etc/snmp/bind
        ```

    3. Edit your snmpd.conf file and add:

        ```bash
        extend bind /etc/snmp/bind
        ```

    4. Restart snmpd on the host in question.

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.

=== "Agent"

    1. [Install the agent](../Agent-Setup.md)) on this device if it isn't
    
    2. Download the script onto the desired host:

        ```bash
        wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/bind -O /usr/lib/check_mk_agent/local/bind
        ```

    3. Make the script executable

        ```bash
        chmod +x /usr/lib/check_mk_agent/local/bind
        ```

    4. Set the variable 'agent' to '1' in the config.

