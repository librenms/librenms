# NGINX

NGINX is a free, open-source, high-performance HTTP server: <https://www.nginx.org/>

Your nginx configuration needs this directive
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

    1. Download the script onto the host.

        ```bash
        wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/nginx -O /etc/snmp/nginx
        ```

    2. Make the script executable.

        ```bash
        chmod +x /etc/snmp/nginx
        ```

    3. Edit your `snmpd.conf` file, usually `/etc/snmp/snmpd.conf`, and add:

        ```bash
        extend nginx /etc/snmp/nginx
        ```

    4. Restart snmpd on your host.

    LibreNMS discovers the application automatically, as described at
    the top of the page. If the discovery fails, do the steps under the
    `SNMP Extend` heading at the top of the page.

=== "Agent"

    If this device has no agent, [install the agent](../Agent-Setup.md)
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

