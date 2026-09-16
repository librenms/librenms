## Sensu

At each new alert, the Sensu transport sends an
[Event](https://docs.sensu.io/sensu-go/latest/reference/events/) to the
[Agent API](https://docs.sensu.io/sensu-go/latest/reference/agent/#create-monitoring-events-using-the-agent-api)
with a POST request.

The event has a category: ok, warning, or critical. If the alert sends
recovery notifications, Sensu also clears the alert automatically. No
configuration is necessary. The Sensu agent must run on your poller
with the HTTP socket on tcp/3031. LibreNMS then generates Sensu events
at the creation of the transport.

Sensu does not support an acknowledgement from LibreNMS directly. The
transport sets the annotation `acknowledged`. A mutator, a silence, or
a handler can read this annotation. The transport also sets the
annotation `generated-by`. Use it to separate the LibreNMS events from
the agent events.

The 'shortname' option makes the device names in the configurations
shorter. It replaces the last 3 domain components with single letters.
For example, `websrv08.dc4.eu.corp.example.net` becomes
`websrv08.dc4.eu.cen`.

### Limitations

- The transport supports only one namespace.
- Sensu rejects a rule with a special character. The transport tries to
correct the rule names. Use only letters, numbers, and spaces.
- The transport uses only absolute states. It ignores the worse,
better, and changed states.
- The agent buffers the alerts. LibreNMS does not. If your agent is
offline, the alerts are lost.
- There is no back channel between Sensu and LibreNMS. A change to a
LibreNMS alert in Sensu is lost at the next event. Silences still work.

**Example:**

| Config          | Example               |
| --------------- | --------------------- |
| Sensu Endpoint  | http://localhost:3031 |
| Sensu Namespace | eu-west               |
| Check Prefix    | lnms                  |
| Source Key      | hostname              |