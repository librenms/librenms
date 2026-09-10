## GLPI

The GLPI transport creates a ticket in GLPI for each raised alert.

 - For each alert type on a device, a ticket is created.
  - If more alerts of the same type occur, GLPI adds follow-ups to the existing ticket.
  - If the existing ticket is closed, the transport creates another ticket.

The user of the user token becomes the creator and the requester of
the ticket. If GLPI holds a device with the same name, the ticket links
to that device.

To set up the transport:
 - **User token**: Go to User preferences > API in GLPI.
 - **App token**: Go to Configuration > General > API in GLPI.

**Example:**

| Config | Example |
| ------ | ------- |
| GLPI API URL | <http://localhost/glpi/apirest.php> |
| User Token | A1b2C3d4E5f6G7h8I9j0K1l2M3n4O5p6Q7r8S9t0 |
| App Token | Z9y8X7w6V5u4T3s2R1q0P9o8N7m6L5k4J3i2H1g |
