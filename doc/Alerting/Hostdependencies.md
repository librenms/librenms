source: Alerting/Hostdepencies.md

# Host Dependencies

It is possible to set a parent for a device. The aim for that is, when a parent device is down, alert contacts will not receive redundant alerts. This is very useful when you have an outage, say in a branch office, where normally you'd receive hundreds of e-mails, but when this is properly configured, you'd only receive an alert for the parent host.

There are three ways to configure this feature. First one is from general settings of a device. The other two can be done in the 'Host Dependencies' item under 'Devices' menu. In this page, you can see all devices and with its parents. Clicking on the 'bin' icon will clear the dependency setting. Clicking on the 'pen' icon will let you edit or change the current setting for chosen device. There's also a 'Manage Host Dependencies' button on the top. This will let you set a parent for multiple devices at once. 
