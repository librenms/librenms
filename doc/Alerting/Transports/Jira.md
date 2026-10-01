## JIRA

LibreNMS creates issues on a Jira instance for critical alerts and
warning alerts. It uses the Jira REST API or webhooks. 
Custom fields add the fields after the summary field and the
description field. Your Jira project or issue type can need such
mandatory fields. Give the custom fields in JSON format. LibreNMS uses
HTTP authentication for Jira. It stores the Jira username and password
in clear text in the LibreNMS database.

### REST API
The Jira REST API needs these configuration fields: Jira Open URL, Jira
username, Jira password, Project key, and Issue type.  

> Note: the REST API can only open new tickets.

### Webhooks
The webhooks need these configuration fields: Jira Open URL, Jira Close
URL, Jira username, Jira password, and Webhook ID.

> Note: webhooks give more control over the alerts in Jira. A recovery
> message can go to a different URL than an alert. You can also build
> your own conditional logic from the webhook payload and ID. This logic
> closes an open ticket automatically when the conditions match.


[Jira Issue Types](https://confluence.atlassian.com/adminjiracloud/issue-types-844500742.html)
[Jira Webhooks](https://developer.atlassian.com/cloud/jira/platform/webhooks/)

**Example:**

| Config | Example |
| ------ | ------- |
| Project Key | JIRAPROJECTKEY |
| Issue Type | Myissuetype |
| Open URL | <https://myjira.mysite.com> /  <https://webhook-open-url> |
| Close URL | <https://webhook-close-url>  |
| Jira Username | myjirauser |
| Jira Password | myjirapass |
| Enable webhook | ON/OFF |
| Webhook ID | alert_id |
| Custom Fields | {"components":[{"id":"00001"}], "source": "LibrenNMS"} |