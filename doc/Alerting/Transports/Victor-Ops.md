## VictorOps

VictorOps provide a webHook url to make integration extremely
simple. To get the URL required login to your VictorOps  account and go to:

Settings -> Integrations -> REST Endpoint -> Enable Integration.

The URL provided will have $routing_key at the end, you need to change
this to something that is unique to the system  sending the alerts
such as librenms. I.e:

`https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms`

**Example:**

| Config | Example |
| ------ | ------- |
| Post URL | <https://alert.victorops.com/integrations/generic/20132414/alert/2f974ce1-08fc-4dg8-a4f4-9aee6cf35c98/librenms> |