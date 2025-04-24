# NGINX

NGINX is a free, open-source, high-performance HTTP server: <https://www.nginx.org/>

It's required to have the following directive in your nginx
configuration responsible for the localhost server:

```nginx
location /nginx-status {
    stub_status on;
    access_log  off;
    allow 127.0.0.1;
    allow ::1;
    deny  all;
}
```
## Agent or SNMP Extend

=== "SNMP Extend"

    1. Download the script onto the desired host.

        ```bash
        wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/nginx -O /etc/snmp/nginx
        ```

    2. Make the script executable

        ```bash
        chmod +x /etc/snmp/nginx
        ```

    3. Edit your snmpd.conf file (usually /etc/snmp/snmpd.conf) and add:

        ```bash
        extend nginx /etc/snmp/nginx
        ```

    4. Restart snmpd on your host

    The application should be auto-discovered as described at the top of
    the page. If it is not, please follow the steps set out under `SNMP
    Extend` heading top of page.

=== "Agent"

    [Install the agent](../Agent-Setup.md) on this device if it isn't already
    and copy the `nginx` script to `/usr/lib/check_mk_agent/local/`

#### SELinux

(Optional) If you have SELinux in Enforcing mode, you must add a module so the script can request /nginx-status:

```bash
cat << EOF > snmpd_nginx.te
module snmpd_nginx 1.0;

require {
        type httpd_t;
        type http_port_t;
        type snmpd_t;
        class tcp_socket name_connect;
}

#============= snmpd_t ==============

allow snmpd_t http_port_t:tcp_socket name_connect;
EOF
checkmodule -M -m -o snmpd_nginx.mod snmpd_nginx.te
semodule_package -o snmpd_nginx.pp -m snmpd_nginx.mod
semodule -i snmpd_nginx.pp
```

