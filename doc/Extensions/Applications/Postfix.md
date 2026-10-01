
## Postfix

### SNMP Extend

1. Copy the shell script, postfix-queues, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/postfix-queues -O /etc/snmp/postfix-queues
    ```

2. Copy the Perl script, postfixdetailed, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/postfixdetailed -O /etc/snmp/postfixdetailed
    ```

3. Make both scripts executable

    ```bash
    chmod +x /etc/snmp/postfixdetailed /etc/snmp/postfix-queues
    ```

4. Edit your `snmpd.conf` file and add:

    ```bash
    extend mailq /etc/snmp/postfix-queues
    extend postfixdetailed /etc/snmp/postfixdetailed
    ```

5. Restart snmpd.

    ```bash
    sudo systemctl restart snmpd
    ```

6. Install pflogsumm for your OS.

7. Make sure the cache file in `/etc/snmp/postfixdetailed` is some place
that snmpd can write too. This file is used for tracking changes
between various values between each time it is called by snmpd. Also
make sure the path for pflogsumm is correct.

8. Run `/etc/snmp/postfixdetailed` to create the initial cache file so
the first value is then correct. 

!!! note 
    that each time `/etc/snmp/postfixdetailed` is ran, the cache file is
    updated, so if this happens in between LibreNMS doing it then the
    values are then wrong for that polling period.

LibreNMS discovers the application automatically, as described at the
top of the page. If the discovery fails, do the steps under the `SNMP
Extend` heading at the top of the page.

!!! note Redhat
    If using RHEL for your postfix server, `qshape` must be installed manually as it is not officially supported. CentOs 6 rpms seem to work without issues.