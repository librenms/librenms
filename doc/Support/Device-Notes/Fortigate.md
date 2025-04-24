To gather Port IP info & routing info for Fortigates, disable the append-index feature.  This feature appends VDOM to the index, breaking standard MIBs.
```
config system snmp sysinfo
    set append-index disable
end
```
https://docs.fortinet.com/document/fortigate/7.2.0/new-features/742119/enabling-the-index-extension-7-2-8
