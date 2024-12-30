# Redis

Script to monitor your Redis Server

## Agent or SNMP Extend

=== "SNMP Extend"

    1. Download the script onto the desired host
 
        ```bash
        wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/redis.py -O /etc/snmp/redis.py
        ```

    2. Make the script executable

        ```bash
        chmod +x /etc/snmp/redis.py
        ```

    3. Edit your snmpd.conf file (usually `/etc/snmp/snmpd.conf`) and add:
    
    ```bash
    extend redis /etc/snmp/redis.py
    ```

    ### SELINUX

    (Optional) If you have SELinux in Enforcing mode, you must add a module so the script can get redis informations and write them:

    ```
    cat << EOF > snmpd_redis.te
    module snmpd_redis 1.0;

    require {
            type tmp_t;
            type redis_port_t;
            type snmpd_t;
            class tcp_socket name_connect;
            class dir { add_name write };
    }

    #============= snmpd_t ==============

    allow snmpd_t redis_port_t:tcp_socket name_connect;
    allow snmpd_t tmp_t:dir { write add_name };
    EOF
    checkmodule -M -m -o snmpd_redis.mod snmpd_redis.te
    semodule_package -o snmpd_redis.pp -m snmpd_redis.mod
    semodule -i snmpd_redis.pp
    ```

=== "Agent"

    [Install the agent](../Agent-Setup.md) on this device if it isn't already
    and copy the `redis` script to `/usr/lib/check_mk_agent/local/`
