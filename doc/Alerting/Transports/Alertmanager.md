## Alertmanager

Alertmanager is alert handling software. The first version processed
the alerts of Prometheus. Alertmanager removes duplicates, groups
alerts, and routes alerts on configurable criteria.

LibreNMS groups the alerts by alert rule. This method can give an array
of similar alerts for an array of hosts. Alertmanager groups the alerts
by alert metadata. It therefore gives one notice for one problem.

You can configure any number of label values in the Alertmanager
Options section. Put each label and its value on a new line.

A label is a fixed string or a dynamic variable from the alert and its
faults. For a dynamic variable, set the value of the label to the name
of the variable. To see all the variables, open Alerts->Notifications
and click the Details icon of a pending alert.

A label with the prefix `dyn_` is absent from the transport message
when the alert data holds no matching value. A label without this
prefix is always present. Without a match, it keeps its fixed string
value. A label with the prefix `stc_` is static. It never takes a
substituted value.

Use the separate Alertmanager Annotations field for values such as runbook and
dashboard links. Enter one `name=value` per line. Values may be fixed text or
contain alert fields using `{{ $field }}`; SimpleTemplate filters such as
`url_encode` are supported. An annotation whose value resolves to an empty
string is omitted. Annotations do not change the alert's label identity or
grouping. For example:

```text
runbook={{ $proc }}
dashboard=https://grafana.example.com/d/device?var-device={{ $hostname|url_encode }}
```

You can give several Alertmanager URLs in a comma separated list.
LibreNMS tries all of them. An Alertmanager cluster then removes the
duplicate alerts.

Basic HTTP authentication with a username and a password is available.
An empty username and password disable the authentication.

For Alertmanager endpoints that require mutual TLS, set the paths to a
client certificate and its private key. Both fields must be set. LibreNMS
sends the certificate with each request when multiple URLs are configured.

Firing alerts include an `endsAt` 20 minutes in the future. Each delivery
refreshes that time; a recovered alert uses an `endsAt` in the past so
Alertmanager resolves it promptly. Configure the alert rule to repeat
within 20 minutes while the condition remains active.

[Alertmanager Docs](https://prometheus.io/docs/alerting/alertmanager/)

**Example:**

| Config | Example |
| ------ | ------- |
| Alertmanager URL(s)   | http://alertmanager1.example.com,http://alertmanager2.example.com |
| Alertmanager Username | myUsername |
| Alertmanager Password | myPassword |
| Alertmanager Client Certificate | /etc/ssl/client/tls.crt |
| Alertmanager Client Key | /etc/ssl/client/tls.key |
| Alertmanager Options: | source=librenms <br/> customlabel=value <br/> extra_dynamic_value=variable_name |
| Alertmanager Annotations: | runbook={{ $proc }} <br/> owner=Network Team |
