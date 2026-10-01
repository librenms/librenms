## Nagios Compatible

The Nagios transport writes to a FIFO at the given location. It uses
the Nagios format. Other alerting systems can then work with LibreNMS.
One example is [Flapjack](http://flapjack.io).

**Example:**

| Config | Example |
| ------ | ------- |
| Nagios FIFO | /path/to/my.fifo |