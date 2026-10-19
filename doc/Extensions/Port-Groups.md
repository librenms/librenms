# Grouping Ports

Port groups collect ports from any number of devices, so they can be
graphed together and referenced from alert rules. Manage them from
Ports -> Manage Groups in the top navigation.

A port group is either **static** or **dynamic**.

## Static Groups

A static group holds the specific ports you assign to it. Assign ports
from the group's edit page, from the Port Settings tab of a device, or
through the [API](../API/Port_Groups.md#add_port_group).

## Dynamic Groups

A dynamic group defines its members with rules, built with the same
query builder used by [device groups](Device-Groups.md) and the
alerting system. Every port matching the rules is a member of the
group; membership is recalculated automatically.

Rules may reference columns of the `ports` table as well as the
`devices` table, so a group can match on port attributes, device
attributes, or both:

- `ports.ifAlias` `begins with` `uplink` collects every port whose
  description starts with `uplink`.
- `devices.type` `equal` `firewall` collects all ports of your
  firewalls.

The members of a dynamic group are managed by its rules: ports cannot
be manually assigned to or removed from it, and requests to do so
through the API are rejected.

Membership is updated for a device each time it is polled, so a change
(for example an updated port description) is reflected after the next
poll of that device. A full re-sync of every dynamic group also runs
during daily maintenance to repair any drift.

You can convert a dynamic group to a static group by editing it and
selecting `static` as the type: the current members are kept and can
then be adjusted manually. Converting a static group to dynamic
replaces its members with the ports matched by the rules.
