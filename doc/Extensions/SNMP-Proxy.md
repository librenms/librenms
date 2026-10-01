# SNMP Proxy

Some machines are not directly available for monitoring. For these
machines, use [SNMPD
Proxy](http://www.net-snmp.org/wiki/index.php/Snmpd_proxy). A reachable
SNMPD then forwards the requests to the unreachable SNMPD.

## Example configuration

This example polls 'unreachable.example.com' through

'hereweare.example.com'. Use this configuration.

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

You can now poll the community 'private' on 'unreachable.example.com'
through the community 'unreachable' on the host 'hereweare.example.com'.
Note: the requests on 'unreachable.example.com' come from
'hereweare.example.com', not from your poller.
