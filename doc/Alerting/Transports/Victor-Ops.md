## VictorOps

VictorOps supplies a webhook URL for a simple integration. To get this
URL, log in to your VictorOps account and go to:

Settings -> Integrations -> REST Endpoint -> Enable Integration.

The URL ends with `$routing_key`. Replace this part with a unique name
for the system of the alerts, such as `librenms`. For example:

`https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms`

**Example:**

| Config | Example |
| ------ | ------- |
| Post URL | <https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms> |