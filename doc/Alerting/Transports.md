# Transports

Transports are located within `LibreNMS/Alert/Transport/` and can be
configured within the WebUI under Alerts -> Alert Transports.

Contacts will be gathered automatically and passed to the configured transports.
By default the Contacts will be only gathered when the alert triggers
and will ignore future changes in contacts for the incident.
If you want contacts to be re-gathered before each dispatch, please
set 'Updates to contact email addresses not honored' to Off in the WebUI.

The contacts will always include the `SysContact` defined in the
Device's SNMP configuration and also every LibreNMS user that has at
least `read`-permissions on the entity that is to be alerted.

At the moment LibreNMS only supports Port or Device permissions.

You can exclude the `SysContact` by toggling 'Issue alerts to sysContact'.

To include users that have `Global-Read`, `Administrator` or
`Normal-User` permissions it is required to toggle the options:

- Issue alerts to admins.
- Issue alerts to read only users
- Issue alerts to normal users.

## Using a Proxy

[Proxy Configuration](../Support/Configuration.md#proxy-support)

## Using a AMQP based Transport

You need to install an additional php module : `bcmath`
