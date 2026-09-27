## Mail

The email transport uses the same email configuration as the rest of
LibreNMS. These are its configuration directives with their defaults:

An email attaches each graph of the `@signedGraphTag` directive. In
HTML format, the graphs are embedded. To disable the image
attachments, set `email_attach_graphs` to false.

!!! setting "alerting/email"
```bash
lnms config:set email_html true
lnms config:set email_attach_graphs false
```

**Example:**

| Config | Example |
| ------ | ------- |
| Email | me@example.com |