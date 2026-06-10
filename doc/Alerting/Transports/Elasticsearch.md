## Elasticsearch

You can have LibreNMS send alerts to an elasticsearch database. Each
fault will be sent as a separate document.

**Example:**

| Config | Example |
| ------ | ------- |
| Host | 127.0.0.1 |
| Port | 9200 |
| Index Pattern | \l\i\b\r\e\n\m\s-Y.m.d |