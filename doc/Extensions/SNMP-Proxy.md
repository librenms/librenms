source: Extensions/SNMP-Proxy.md
path: blob/master/doc/

# SNMP Proxy

If you have machines that you want to monitor but are not reachable
directly, you can use [SNMPD
Proxy](http://www.net-snmp.org/wiki/index.php/Snmpd_proxy). This will
use the reachable SNMPD to proxy requests to the unreachable SNMPD.

## Example configuration

We want to poll 'unreachable.example.com' via

'hereweare.example.com'. Use the following config:

On 'hereweare.example.com':

```
        view all included .1
        com2sec -Cn ctx_unreachable readonly <poller-ip> unreachable
        access MyROGroup ctx_unreachable any noauth prefix all none none
        proxy -Cn ctx_unreachable -v 2c -c private unreachable.example.com  .1.3
```

On 'unreachable.example.com':

```
        view all included .1                               80
        com2sec readonly <hereweare.example.com ip address> private
        group MyROGroup v1 readonly
        group MyROGroup v2c readonly
        group MyROGroup usm readonly
        access MyROGroup "" any noauth exact all none none
```

You can now poll community 'private' on
'unreachable.example.com' via community 'unreachable' on host
'hereweare.example.com'. Please note that requests on
'unreachable.example.com' will be coming from
'hereweare.example.com', not your poller.
