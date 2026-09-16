# Rules

The simplest test of an alert rule against a device uses the web
interface. Open the device, click the cog to edit it, then select
Capture. On the new screen, choose Alerts and click run.

The output shows each alert of this device. For each alert, it gives
the rule name, the rule, the MySQL query, and the match result.

Read [Device Troubleshooting](../Support/Device-Troubleshooting.md).

---

## Transports

To test your transports, force an active alert to run. The test ignores
the interval value and the delay value.

Run `./scripts/test-alert.php`. This script accepts `-r` for the rule
id, `-h` for the device id or hostname, and `-d` for debug.

---

## Templates

You can test a new template before you assign it to a rule. Run
`./scripts/test-template.php`. Without a parameter, the script shows
its help information.

For example, to test template ID 10 against localhost with rule ID 2,
run this command:

`./scripts/test-template.php -t 10 -d -h localhost -r 2`

If the rule alerts for localhost, the output shows the full template,
as in the email. If the rule does not alert, the output shows the
template without fault information.
