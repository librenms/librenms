## Matrix

For using the Matrix transports, you have to create a room on the Matrix-server.
The provided Auth_token belongs to an user, which is member of this room.
The Message, sent to the matrix-room can be built from the variables defined in
[Template-Syntax](../Templates.md#syntax) but without the 'alert->' prefix.
See API-Transport. The variable ``` $msg ``` is contains the result of
the Alert template.The Matrix-Server URL is cutted before the
beginning of the ``_matrix/client/r0/...`` API-part.

**Example:**

| Config | Example |
| ------ | ------- |
| Matrix-Server URL | <https://matrix.example.com/> |
| Room | !ajPbbPalmVbNuQoBDK:example.com |
| Auth_token: | MDAyYmxvY2F0aW9uI...z1DCn6lz_uOhtW3XRICg |
| Message: | Alert: {{ $msg }} https://librenms.example.com |