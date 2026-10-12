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

The Alertmanager Templated Labels field adds labels whose values come from a
template, which the Alertmanager Options field does not do. Enter one
`name=value` per line. Values may be fixed text or contain alert fields using
`{{ $field }}`, with SimpleTemplate filters such as `replace`. Surrounding
whitespace is removed from both the name and the value, and a label whose value
resolves to an empty string is omitted entirely. A templated label overrides an
Alertmanager Options label of the same name.

This routes one transport for every rule. For example, to map the rule severity
onto a numeric priority your Alertmanager configuration can match on:

```text
team=net
priority={{ $severity|replace('critical','3')|replace('warning','4')|replace('ok','') }}
```

An alert rule severity is always `ok`, `warning` or `critical`, so a template
should handle all three. Mapping one of them to an empty string drops the label
rather than sending an unmapped value such as `priority=ok`.

A label is part of the alert's identity: Alertmanager fingerprints an alert on
its label set. Template only values that are stable for the lifetime of the
alert, such as a severity or a device property. A reading that moves between
polls gives every new value its own fingerprint, so one alert becomes many; put
such a value in an annotation instead. For the same reason a templated label
must resolve identically on the firing and the recovery delivery, which rules
out fields that differ between the two, such as `title`, `elapsed` and `id`.

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
| Alertmanager Templated Labels: | team=net <br/> priority={{ $severity\|replace('critical','3') }} |
| Alertmanager Annotations: | runbook={{ $proc }} <br/> owner=Network Team |
