## Sensu

The Sensu transport will POST an
[Event](https://docs.sensu.io/sensu-go/latest/reference/events/) to the
[Agent API](https://docs.sensu.io/sensu-go/latest/reference/agent/#create-monitoring-events-using-the-agent-api)
upon an alert being generated.

It will be categorised (ok, warning or critical), and if you configure the
alert to send recovery notifications, Sensu will also clear the alert
automatically. No configuration is required - as long as you are running the
Sensu Agent on your poller with the HTTP socket enabled on tcp/3031, LibreNMS
will start generating Sensu events as soon as you create the transport.

Acknowledging alerts within LibreNMS is not directly supported, but an
annotation (`acknowledged`) is set, so a mutator or silence, or even the
handler could be written to look for it directly in the handler. There is also
an annotation (`generated-by`) set, to allow you to treat LibreNMS events
differently from agent events.

The 'shortname' option is a simple way to reduce the length of device names in
configs. It replaces the last 3 domain components with single letters (e.g.
websrv08.dc4.eu.corp.example.net gets shortened to websrv08.dc4.eu.cen).

### Limitations

- Only a single namespace is supported
- Sensu will reject rules with special characters - the Transport will attempt
to fix up rule names, but it's best to stick to letters, numbers and spaces
- The transport only deals in absolutes - it ignores the got worse/got better
/changed states
- The agent will buffer alerts, but LibreNMS will not - if your agent is
offline, alerts will be dropped
- There is no backchannel between Sensu and LibreNMS - if you make changes in
Sensu to LibreNMS alerts, they'll be lost on the next event (silences will work)

**Example:**

| Config          | Example               |
| --------------- | --------------------- |
| Sensu Endpoint  | http://localhost:3031 |
| Sensu Namespace | eu-west               |
| Check Prefix    | lnms                  |
| Source Key      | hostname              |