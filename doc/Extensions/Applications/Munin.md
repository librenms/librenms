# Munin

### Agent

1. Install the script to your agent:

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/agent-local/munin -O /usr/lib/check_mk_agent/local/munin
    ```

2. Make the script executable

    ```bash
    chmod +x /usr/lib/check_mk_agent/local/munin
    ```

3. Create the munin scripts dir:

    ```bash
    mkdir -p /usr/share/munin/munin-scripts
    ```

4. Install your munin scripts into the above directory.

To create your own custom munin scripts, please see this example:

```bash
#!/bin/bash
if [ "$1" = "config" ]; then
    echo 'graph_title Some title'
    echo 'graph_args --base 1000 -l 0' #not required
    echo 'graph_vlabel Some label'
    echo 'graph_scale no' #not required, can be yes/no
    echo 'graph_category system' #Choose something meaningful, can be anything
    echo 'graph_info This graph shows something awesome.' #Short desc
    echo 'foobar.label Label for your unit' # Repeat these two lines as much as you like
    echo 'foobar.info Desc for your unit.'
    exit 0
fi
echo -n "foobar.value " $(date +%s) #Populate a value, here unix-timestamp
```