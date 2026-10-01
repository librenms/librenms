# Transports

The transports are in `LibreNMS/Alert/Transport/`. You can configure
them in the web interface under Alerts -> Alert Transports.

LibreNMS collects the contacts, that is the email addresses, and sends
them to the configured transports. By default, LibreNMS collects the
contacts at the trigger of the alert. It then ignores each later change
to the contacts of that incident. To collect the contacts again before
each dispatch, use this setting:

!!! setting "alerting/general"
    ```bash
    lnms config:set alert.fixed-contacts false
    ```

The contacts always include the `SysContact` from the SNMP
configuration of the device. They also include each LibreNMS user with
at least `read` permission on the entity of the alert.

LibreNMS supports port permissions and device permissions only.

## Using a Proxy

[Proxy Configuration](../Support/Configuration.md#proxy-support)

## Using a AMQP based Transport

You must install the additional PHP module `bcmath`.
