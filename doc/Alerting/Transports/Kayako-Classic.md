## Kayako Classic

LibreNMS can send alerts to Kayako Classic API which are then
converted to tickets. To use this module, you need REST API feature
enabled in Kayako Classic and configured email account at LibreNMS. To
enable this, do this:

AdminCP -> REST API -> Settings -> Enable API (Yes)

Also you need to know the department id to provide tickets to
appropriate department and a user email to provide, which is used as
ticket author.  To get department id: navigate to appropriate
department name at the departments list page in Admin CP and watch the
number at the end of url. Example:
<http://servicedesk.example.com/admin/Base/Department/Edit/17>. Department
ID is 17

As a requirement, you have to know API Url, API Key and API Secret to
connect to servicedesk

[Kayako REST API Docs](https://classic.kayako.com/article/1502-kayako-rest-api)

**Example:**

| Config | Example |
| ------ | ------- |
| Kayako URL | <http://servicedesk.example.com/api/> |
| Kayako API Key | 8cc02f38-7465-4a0c-8730-bb3af122167b |
| Kayako API Secret | Y2NhZDIxNDMtNjVkMi0wYzE0LWExYTUtZGUwMjJiZDI0ZWEzMmRhOGNiYWMtNTU2YS0yODk0LTA1MTEtN2VhN2YzYzgzZjk5 |
| Kayako Department | 1 |