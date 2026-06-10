## Nagios Compatible

The nagios transport will feed a FIFO at the defined location with the
same format that nagios would. This allows you to use other alerting
systems with LibreNMS, for example [Flapjack](http://flapjack.io).

**Example:**

| Config | Example |
| ------ | ------- |
| Nagios FIFO | /path/to/my.fifo |