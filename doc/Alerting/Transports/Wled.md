## WLED

This transport sets WLED presets for alerts.

The transport needs the hostname or IP address of the WLED instance. It
also needs the IDs of the presets. The ID of a preset is the number
next to its name in the WLED web interface.

An empty warning, critical, or recovery field ignores that severity or
state.

The warning, critical, and recovery fields together can give an
unpredictable result. Use this transport only for specific alerts and
hosts, because it sends a small amount of information.

**Examples:**

Set the preset on WLED at 10.1.2.3 to 1 for a warning and to 3 for a recovery.

| Config   | Example  |
|----------|----------|
| Host     | 10.1.2.3 |
| Warning  | 1        |
| Critical |          |
| Recovery | 3        |

Set the preset on WLED at 10.1.2.3 to 2 for a critical alert and to 3 for a recovery.

| Config   | Example  |
|----------|----------|
| Host     | 10.1.2.3 |
| Warning  |          |
| Critical | 2        |
| Recovery | 3        |

Set the preset on WLED at 10.1.2.3 to 1 for a warning and to 2 for a critical alert.

| Config   | Example  |
|----------|----------|
| Host     | 10.1.2.3 |
| Warning  | 1        |
| Critical | 2        |
| Recovery |          |

Set the preset on WLED at 10.1.2.3 to 2 for a critical alert.

| Config   | Example  |
|----------|----------|
| Host     | 10.1.2.3 |
| Warning  |          |
| Critical | 2        |
| Recovery |          |
