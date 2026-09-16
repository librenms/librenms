## Matrix

The Matrix transport needs a room on the Matrix server. The Auth_token
belongs to a user in that room.
The message to the Matrix room uses the variables from
[Template-Syntax](../Templates.md#syntax), without the `alert->`
prefix. This behaviour is the same as in the API transport. The
variable ``` $msg ``` holds the result of the alert template. The
Matrix server URL ends before the ``_matrix/client/r0/...`` API part.

**Example:**

| Config | Example |
| ------ | ------- |
| Matrix-Server URL | <https://matrix.example.com/> |
| Room | !ajPbbPalmVbNuQoBDK:example.com |
| Auth_token: | MDAyYmxvY2F0aW9uI...z1DCn6lz_uOhtW3XRICg |
| Message: | Alert: {{ $msg }} https://librenms.example.com |