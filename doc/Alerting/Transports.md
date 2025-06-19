# Transports

Transports are located within `LibreNMS/Alert/Transport/` and can be
configured within the WebUI under Alerts -> Alert Transports.

Contacts (email addresses) will be gathered automatically and passed
to the configured transports. By default the Contacts will be only
gathered when the alert triggers and will ignore future changes in
contacts for the incident. If you want contacts to be re-gathered
before each dispatch, please set:

!!! setting "alerting/general"
    ```bash
    lnms config:set alert.fixed-contacts false
    ```

The contacts will always include the `SysContact` defined in the
Device's SNMP configuration and also every LibreNMS user that has at
least `read` permissions on the entity that is to be alerted.

At the moment LibreNMS only supports Port or Device permissions.

## Using a Proxy

[Proxy Configuration](../Support/Configuration.md#proxy-support)

## Using a AMQP based Transport

You need to install an additional php module : `bcmath`
