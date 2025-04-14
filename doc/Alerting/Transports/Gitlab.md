## GitLab

LibreNMS will create issues for warning and critical level alerts
however only title and description are set. Uses Personal access
tokens to authenticate with GitLab and will store the token in cleartext.

**Example:**

| Config | Example |
| ------ | ------- |
| Host | <http://gitlab.host.tld> |
| Project ID | 1 |
| Personal Access Token | AbCdEf12345 |