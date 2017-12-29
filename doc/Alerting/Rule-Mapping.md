source: Alerting/Rule-Mapping.md

# Alert Rule Mapping
Alert Rule Mapping can be found in the WebUI. In the Nav bar click on Alerts then Rule Mapping.
In LibreNMS you can create alert rules and map those alert rules to devices or you can create device groups and map alert rules to those groups.
This could be useful for alerts rules that you don't want to be checked against devices that may not match with your alert rule or may give you false positive etc.

Example: Alert Rule Mapping
![Example Rule Mapping](/img/example-alert-rule-mapping.png)


In this example we have an alert rule that checks HPE iLo Power Supply Failure. You probably don't want this alert rule being checked on none HPE iLo devices.  So in the picture below you can see the Alert rule is mapped to a Device group that was created just for HPE iLo devices.

Example: HPE iLo Rule map
![Example Rule Mapping](/img/example-hpe-rule-map.png)


### You have two options when mapping the alert rule.
* First option: you can map alert rule to one device.
* Second option: you create a device group and group all your devices together. [Link to Device Groups](../Extensions/Device-Groups.md)
