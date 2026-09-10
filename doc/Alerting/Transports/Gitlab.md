## GitLab

LibreNMS creates issues for warning alerts and critical alerts. It
sets only the title and the description. The authentication with GitLab
uses a personal access token. LibreNMS stores this token in clear text.

**Example:**

| Config | Example |
| ------ | ------- |
| Host | <http://gitlab.host.tld> |
| Project ID | 1 |
| Personal Access Token | AbCdEf12345 |