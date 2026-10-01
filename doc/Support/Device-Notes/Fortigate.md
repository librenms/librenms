To collect the port IP information and the routing information from a Fortigate, disable the append-index feature. This feature adds the VDOM to the index and therefore breaks the standard MIBs.
```
config system snmp sysinfo
    set append-index disable
end
```
https://docs.fortinet.com/document/fortigate/7.2.0/new-features/742119/enabling-the-index-extension-7-2-8
