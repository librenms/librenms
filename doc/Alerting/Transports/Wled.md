## WLED

This enables setting of WLED presets for alerts.

This requires the following information. The hostname/IP of the WLED instance and the IDs
of the presets you will be using. The ID for the preset will be the number by it's name in
the WLED web interface.

Leaving warning, critical, or recovery blank, will mean that severity/state is ignored.

Using warning, critical, and recovery together can lead to unpredicatble
results. Similarly best to only use this for very specific alerts/hosts given this
transport can only communicate limited info.

**Examples:**

Set the preset on WLED at 10.1.2.3 to 1 for warnings and 3 for recoveries.

| Config   | Example  |
|----------|----------|
| Host     | 10.1.2.3 |
| Warning  | 1        |
| Critical |          |
| Recovery | 3        |

Set the preset on WLED at 10.1.2.3 to 2 for criticals and 3 for recoveries.

| Config   | Example  |
|----------|----------|
| Host     | 10.1.2.3 |
| Warning  |          |
| Critical | 2        |
| Recovery | 3        |

Set the preset on WLED at 10.1.2.3 to 1 for warnings and 2 for criticals.

| Config   | Example  |
|----------|----------|
| Host     | 10.1.2.3 |
| Warning  | 1        |
| Critical | 2        |
| Recovery |          |

Set the preset on WLED at 10.1.2.3 to 2 for criticals.

| Config   | Example  |
|----------|----------|
| Host     | 10.1.2.3 |
| Warning  |          |
| Critical | 2        |
| Recovery |          |
