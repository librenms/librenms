## Kayako Classic

LibreNMS sends alerts to the Kayako Classic API. Kayako converts them
to tickets. This module needs the REST API feature in Kayako Classic
and a configured email account in LibreNMS. To enable the REST API, go
to:

AdminCP -> REST API -> Settings -> Enable API (Yes)

You also need the department id and a user email. The department id
sends the ticket to the correct department. The user email becomes the
ticket author. To find the department id, open the department name on
the departments list page in Admin CP. The number at the end of the URL
is the id. For example:
<http://servicedesk.example.com/admin/Base/Department/Edit/17>. Department
ID is 17.

The connection to the service desk also needs the API URL, the API key,
and the API secret.

[Kayako REST API Docs](https://classic.kayako.com/article/1502-kayako-rest-api)

**Example:**

| Config | Example |
| ------ | ------- |
| Kayako URL | <http://servicedesk.example.com/api/> |
| Kayako API Key | 8cc02f38-7465-4a0c-8730-bb3af122167b |
| Kayako API Secret | Y2NhZDIxNDMtNjVkMi0wYzE0LWExYTUtZGUwMjJiZDI0ZWEzMmRhOGNiYWMtNTU2YS0yODk0LTA1MTEtN2VhN2YzYzgzZjk5 |
| Kayako Department | 1 |