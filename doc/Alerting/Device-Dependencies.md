source: Alerting/Device-Dependencies.md
path: blob/master/doc/

# Device Dependencies

It is possible to set one or more parents for a device. The aim for
that is, if all parent devices are down, alert contacts will not
receive redundant alerts for dependent devices. This is very useful
when you have an outage, say in a branch office, where normally you'd
receive hundreds of alerts, but when this is properly configured,
you'd only receive an alert for the parent hosts.

There are three ways to configure this feature. First one is from
general settings of a device. The other two can be done in the 'Device
Dependencies' item under 'Devices' menu. In this page, you can see all
devices and with its parents. Clicking on the 'bin' icon will clear
the dependency setting. Clicking on the 'pen' icon will let you edit
or change the current setting for chosen device. There's also a
'Manage Device Dependencies' button on the top. This will let you set
parents for multiple devices at once.

For an intro on getting started with Device Dependencies, take a look
at our [Youtube video](https://www.youtube.com/watch?v=KMAarVS9QQ8)
