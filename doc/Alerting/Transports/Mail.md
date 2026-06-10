## Mail

The E-Mail transports uses the same email-configuration as the rest of LibreNMS.
As a small reminder, here is its configuration directives including defaults:

Emails will attach all graphs included with the @signedGraphTag directive.
If the email format is set to html, they will be embedded.
To disable attaching images, set email_attach_graphs to false.

!!! setting "alerting/email"
```bash
lnms config:set email_html true
lnms config:set email_attach_graphs false
```

**Example:**

| Config | Example |
| ------ | ------- |
| Email | me@example.com |