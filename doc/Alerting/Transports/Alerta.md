## Alerta

The [Alerta](https://alerta.io) monitoring system is used to consolidate, de-duplicate, and visualise alerts from multiple monitoring sources in one place.

LibreNMS can send alerts to Alerta using the Alerta API transport.

This transport sends LibreNMS alerts to Alerta using the configured API endpoint, API key, origin, environment, alert severity, and recovery severity.

### Example

| Config | Example |
| ------ | ------- |
| API Endpoint | `http://alerta.example.com/api/alert` |
| API key | API key with `write:alert` permission |
| Origin | `LibreNMS` |
| Environment | `Production` |
| Alert state | `critical` |
| Recover state | `cleared` |
| Group by sysContact | Disabled |
| Alerta Debug | Disabled |

### Configuration options

| Option | Description |
| ------ | ----------- |
| API Endpoint | The Alerta API alert endpoint. For example: `http://alerta.example.com/api/alert`. |
| API key | The Alerta API key used by LibreNMS. The key requires permission to create/update alerts. |
| Origin | The monitoring source name sent to Alerta. This is also used as the Alerta `resource` value. |
| Environment | The Alerta environment value, for example `Production`, `Development`, or another environment allowed by your Alerta configuration. |
| Alert state | The Alerta severity sent when the LibreNMS alert is active. |
| Recover state | The Alerta severity sent when the LibreNMS alert recovers. |
| Group by sysContact | Optional. When enabled, the Alerta `group` field is set to the device `sysContact` value. When disabled, the original LibreNMS behaviour is used and the alert rule name is sent as the Alerta `group`. |
| Alerta Debug | Optional. Adds extra troubleshooting information to the Alerta payload. This should normally remain disabled. |

### Alert grouping

By default, LibreNMS sends the Alerta `group` field using the alert rule name.

This keeps the original LibreNMS Alerta transport behaviour.

The optional **Group by sysContact** setting can be enabled if you want Alerta alerts to be grouped by the device `sysContact` value instead.

If **Group by sysContact** is enabled but the device has no `sysContact` value, LibreNMS falls back to the alert rule name.

!!! note
    Changing **Group by sysContact** affects newly-created Alerta alerts.

    Existing or re-opened Alerta alerts may keep their previous group because Alerta de-duplicates and re-opens alerts using the alert identity, not the group field.

### Per-fault Alerta events

For LibreNMS alerts containing multiple fault rows, the Alerta transport sends one Alerta event per fault row.

This allows each individual fault to be tracked separately in Alerta while still belonging to the same LibreNMS alert rule.

For example, if a LibreNMS alert rule matches multiple interfaces, sensors, or services, each matching fault can be sent as its own Alerta event.

### Event identity and recovery matching

The transport builds a stable per-fault event signature so that repeated alerts and recoveries match the correct Alerta event.

The generated signature is used to keep the same active fault matched to the same Alerta alert across repeated notifications and recovery notifications.

The MD5 hash used by the transport is only a compact fingerprint for fault uniqueness and recovery matching. It is not used for security.

### Alert text and description

The transport re-renders the LibreNMS alert template for each individual fault row.

This means the Alerta alert text/description follows the LibreNMS alert template configured for the alert rule.

If no rendered template body is available, the transport falls back to available fault or alert information.

### Recovery behaviour

When a LibreNMS alert recovers, the transport sends the configured **Recover state** severity to Alerta.

The transport keeps a short-lived cache of active fault signatures so it can clear faults that were previously active but are no longer present in the current LibreNMS alert data.

### Debug mode

The **Alerta Debug** option adds extra troubleshooting fields to the payload sent to Alerta.

This can help inspect the LibreNMS alert data and the individual fault data being sent to Alerta.

Debug mode should normally remain disabled because it can add large payload fields to Alerta.
