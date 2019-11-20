source: Extensions/Device-Groups.md
path: blob/master/doc/

LibreNMS supports grouping your devices together in much the same way
as you can configure alerts. This document will hopefully help you get
started.

# Dynamic Groups

## Rule Editor

The rule is based on the MySQL structure your data is in. Such as __tablename.columnname__.
If you already know the entity you want, you can browse around inside
MySQL using `show tables` and `desc <tablename>`.

As a working example and a common question, let's assume you want to
group devices by hostname. If your hostname format is
dcX.[devicetype].example.com. You would use the field
`devices.hostname`.

If you want to group them by device type, you would add a rule for
routers of `devices.hostname` endswith `rtr.example.com`.

If you want to group them by DC, you could use the rule
`devices.hostname` regex `dc1\..*\.example\.com` (Don't forget to
escape periods in the regex)

# Static Groups

You can create static groups (and convert dynamic groups to static) to
put specific devices in a group. Just select static as the type and
select the devices you want in the group.

![Device Groups](/img/device_groups.png)

You can now select this group from the Devices -> All Devices link in
the navigation at the top. You can also use the group to map alert
rules to by creating an alert mapping
`Overview -> Alerts -> Rule Mapping`.
