## GLPI

The GLPI transport creates a ticket in GLPI whenever an alert is raised.

 - For each alert type on a device, a ticket is created.
 - If multiple alerts of the same type are raised, follow-ups are added to the existing ticket.
 - If the existing ticket is closed, it will create another ticket.

The user identified by the user token will be set as the creator and the requester of the ticket. If a device with the same name exists in GLPI, it will be linked to the ticket.

To set it up:
 - **User token**: Go to User preferences > API in GLPI.
 - **App token**: Go to Configuration > General > API in GLPI.

**Example:**

| Config | Example |
| ------ | ------- |
| GLPI API URL | <http://localhost/glpi/apirest.php> |
| User Token | A1b2C3d4E5f6G7h8I9j0K1l2M3n4O5p6Q7r8S9t0 |
| App Token | Z9y8X7w6V5u4T3s2R1q0P9o8N7m6L5k4J3i2H1g |
