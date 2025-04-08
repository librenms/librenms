## Alertmanager

Alertmanager is an alert handling software, initially developed for
alert processing sent by Prometheus.

It has built-in functionality for deduplicating, grouping and routing
alerts based on configurable criteria.

LibreNMS uses alert grouping by alert rule, which can produce an array
of alerts of similar content for an array of hosts, whereas
Alertmanager can group them by alert meta, ideally producing one
single notice in case an issue occurs.

It is possible to configure as many label values as required in
Alertmanager Options section. Every label and its value should be
entered as a new line.

Labels can be a fixed string or a dynamic variable from the alert.
To set a dynamic variable your label must start with extra_ then
complete with the name of your label (only characters, figures and
underscore are allowed here). The value must be the name of
the variable you want to get (you can see all the variables in
Alerts->Notifications by clicking on the Details icon of your alert
when it is pending). If the variable's name does not match with an
existing value the label's value will be the string you provided just
as it was a fixed string.

Multiple Alertmanager URLs (comma separated) are supported. Each
URL will be tried and the search will stop at the first success.

Basic HTTP authentication with a username and a password is supported.
If you let those value blank, no authentication will be used.

[Alertmanager Docs](https://prometheus.io/docs/alerting/alertmanager/)

**Example:**

| Config | Example |
| ------ | ------- |
| Alertmanager URL(s)   | http://alertmanager1.example.com,http://alertmanager2.example.com |
| Alertmanager Username | myUsername |
| Alertmanager Password | myPassword |
| Alertmanager Options: | source=librenms <br/> customlabel=value <br/> extra_dynamic_value=variable_name |