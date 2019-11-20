source: Alerting/Testing.md
path: blob/master/doc/

### Rules

The simplest way of testing if an alert rule will match a device is by
going to the device, clicking edit (the cog), select Capture. From
this new screen choose Alerts and click run.

The output will cycle through all alerts applicable to this device and
show you the Rule name, rule, MySQL query and if the rule matches.

See [Device Troubleshooting](../Support/Device-Troubleshooting.md)

---

### Transports

You can test your transports by forcing an actual active alert to run
regardless of the interval or delay values.

`./scripts/test-alert.php`. This script accepts -r for the rule id, -h
for the device id or hostname and -d for debug.

---

### Templates

It's possible to test your new template before assigning it to a
rule. To do so you can run `./scripts/test-template.php`. The script
will provide the help info when ran without any parameters.

As an example, if you wanted to test template ID 10 against localhost
running rule ID 2 then you would run:

`./scripts/test-template.php -t 10 -d -h localhost -r 2`

If the rule is currently alerting for localhost then you will get the
full template as expected to see on email, if it's not then you will
just see the template without any fault information.
