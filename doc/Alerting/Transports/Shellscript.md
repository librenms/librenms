## Shellscript

Run a shell command. The command must be accessible by the main librenms process as well as the dispatcher.

*Using variables in your command can be a security risk*


**Example:**

| Config | Example |
| ------ | ------- |
| Command | sample_script.sh {{ $status }} |


sample_script.sh
```
#!/bin/sh

## Active alert, activate the alert beacon!
test $1 == 1 && beacon_control -t 5 red strobe

## Alert recovered, green light
test $1 == 0 && beacon_control -t 5 green
```