## JIRA

You can have LibreNMS create issues on a Jira instance for critical and warning
 alerts using either the Jira REST API or webhooks. 
Custom fields allow you to add any required fields beyond summary and description
 fields in case mandatory fields are required by your Jira project/issue type 
 configuration. Custom fields are defined in JSON format but ustom fields allow 
 you to add any required fields beyond summary and description fields in case 
 mandatory fields are required by your Jira project/issue type configuration. 
 Custom fields are defined in JSON format. Currently http authentication is used 
 to access Jira and Jira username and password will be stored as cleartext in the 
 LibreNMS database.

### REST API
The config fields that need to set for Jira REST API are: Jira Open URL, Jira username, 
Jira password, Project key, and issue type.  

> Note: REST API is that it is only able to open new tickets.

### Webhooks
The config fields that need to set for webhooks are: Jira Open URL, Jira Close URL,
 Jira username, Jira password and webhook ID.

> Note: Webhooks allow more control over how alerts are handled in Jira. With webhooks, 
> recovery messages can be sent to a different URL than alerts. Additionally, a custom 
> conditional logic can be built using the webhook payload and ID to automatically close 
> an open ticket if predefined conditions are met.


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
| Custom Fileds | {"components":[{"id":"00001"}], "source": "LibrenNMS"} |