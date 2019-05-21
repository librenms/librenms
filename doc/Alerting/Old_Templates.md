source: Alerting/Templates.md
path: blob/master/doc/

# Templates

Templates can be assigned to a single or a group of rules and can contain any kind of text. There is also a default template which is used for any rule that isn't associated with a template. This template can be found under `Alert Templates` page and can be edited. It also has an option revert it back to its default content. 

The template-parser understands `if` and `foreach` controls and replaces certain placeholders with information gathered about the alert.

## Syntax

Controls:

- if-else (Else can be omitted):
`{if %placeholder == value}Some Text{else}Other Text{/if}`
- foreach-loop:
`{foreach %faults}Key: %key<br/>Value: %value{/foreach}`

Placeholders:

Placeholders are special variables that if used within the template will be replaced with the relevant data, I.e:

`The device %hostname has been up for %uptime seconds` would result in the following `The device localhost has been up for 30344 seconds`.

- Device ID: `%device_id`
- Hostname of the Device: `%hostname`
- sysName of the Device: `%sysName`
- sysDescr of the Device: `%sysDescr`
- hardware of the Device: `%hardware`
- Software version of the Device: `%version` 
- location of the Device: `%location`
- uptime of the Device (in seconds): `%uptime`
- short uptime of the Device (28d 22h 30m 7s): `%uptime_short`
- long uptime of the Device (28 days, 22h 30m 7s): `%uptime_long`
- description (purpose db field) of the Device: `%description`
- notes of the Device: `%notes`
- notes of the alert: `%alert_notes`
- ping timestamp (if icmp enabled): `%ping_timestamp`
- ping loss (if icmp enabled): `%ping_loss`
- ping min (if icmp enabled): `%ping_min`
- ping max (if icmp enabled): `%ping_max`
- ping avg (if icmp enabled): `%ping_avg`
- Title for the Alert: `%title`
- Time Elapsed, Only available on recovery (`%state == 0`): `%elapsed`
- Alert-ID: `%id`
- Unique-ID: `%uid`
- Faults, Only available on alert (`%state != 0`), must be iterated in a foreach (`{foreach %faults}`). Holds all available information about the Fault, accessible in the format `%value.Column`, for example: `%value.ifDescr`. Special field `%value.string` has most Identification-information (IDs, Names, Descrs) as single string, this is the equivalent of the default used.
- State: `%state`
- Severity: `%severity`
- Rule: `%rule`
- Rule-Name: `%name`
- Timestamp: `%timestamp`
- Transport name: `%transport`

- Contacts, must be iterated in a foreach, `%key` holds email and `%value` holds name: `%contacts`

Placeholders can be used within the subjects for templates as well although %faults is most likely going to be worthless.

> NOTE: Placeholder names which are contained within another need to be ordered correctly. As an example:

```text
Limit: %value.sensor_limit / %value.sensor_limit_low
```

Should be done as:

```text
Limit: %value.sensor_limit_low / %value.sensor_limit
```

The Default Template is a 'one-size-fit-all'. We highly recommend defining your own templates for your rules to include more specific information.

## Examples

Default Template:
```text
%title
Severity: %severity
{if %state == 0}Time elapsed: %elapsed{/if}
Timestamp: %timestamp
Unique-ID: %uid
Rule: {if %name}%name{else}%rule{/if}
{if %faults}Faults:
{foreach %faults}  #%key: %value.string{/foreach}{/if}
Alert sent to: {foreach %contacts}%value <%key> {/foreach}
```
Ports Utilization Template:
```text
%title
Device Name: %hostname
Severity: %severity
{if %state == 0}Time elapsed: %elapsed{/if}
Timestamp: %timestamp
Rule: {if %name}%name{else}%rule{/if}
{foreach %faults}
Physical Interface: %value.ifDescr
Interface Description: %value.ifAlias
Interface Speed: {calc (%value.ifSpeed/1000000000)} Gbs
Inbound Utilization: {calc ((%value.ifInOctets_rate*8)/%value.ifSpeed)*100}%
Outbound Utilization: {calc ((%value.ifOutOctets_rate*8)/%value.ifSpeed)*100}%
{/foreach}
```

Storage:
```text

%title

Device Name: %hostname
Severity: %severity 
Uptime: %uptime_short
{if %state == 0}Time elapsed: %elapsed{/if}
Timestamp: %timestamp
Location: %location
Description: %description
Features: %features
Purpose: %purpose
Notes: %notes

Server: %sysName {foreach %faults}Mount Point: %value.storage_descr Percent Utilized: %value.storage_perc{/foreach}
```

Temperature Sensors:
```text

%title

Device Name: %hostname
Severity: %severity 
Timestamp: %timestamp
Uptime: %uptime_short
{if %state == 0}Time elapsed: %elapsed{/if}
Location: %location
Description: %description
Features: %features
Purpose: %purpose
Notes: %notes

Rule: {if %name}%name{else}%rule{/if}
{if %faults}Faults:
{foreach %faults}
#%key: Temperature: %value.sensor_current°C
** {calc(%value.sensor_current-%value.sensor_limit)}°C over limit
Previous Measurement: %value.sensor_prev°C
High Temperature Limit: %value.sensor_limit°C
{/foreach}
{/if}
```

Value Sensors:
```text

%title

Device Name: %hostname
Severity: %severity 
Timestamp: %timestamp
Uptime: %uptime_short
{if %state == 0}Time elapsed: %elapsed{/if}
Location: %location
Description: %description
Features: %features
Purpose: %purpose
Notes: %notes

Rule: {if %name}%name{else}%rule{/if}
{if %faults}Faults:
{foreach %faults}
#%key: Sensor%value.sensor_current
** {calc(%value.sensor_current-%value.sensor_limit)}over limit
Previous Measurement: %value.sensor_prev
Limit: %value.sensor_limit
{/foreach}
{/if}
```

Memory Alert:
```text
%title

Device Name: %hostname
Severity: %severity 
Uptime: %uptime_short
{if %state == 0}Time elapsed: %elapsed{/if}
Timestamp: %timestamp
Location: %location
Description: %description
Notes: %notes

Server: %hostname {foreach %faults}
Memory Description: %value.mempool_descr 
Percent Utilized: %value.mempool_perc{/foreach}
```


Conditional formatting example, will display a link to the host in email or just the hostname in any other transport:
```text
{if %transport == mail}<a href="https://my.librenms.install/device/device=%hostname/">%hostname</a>
{else}
%hostname
{/if}
```

Note the use of double-quotes.  Single quotes (`'`) in templates will be escaped (replaced with `\'`) in the output and should therefore be avoided.

## Examples HTML

Note: To use HTML emails you must set HTML email to Yes in the WebUI under Global Settings > Alerting Settings > Email transport > Use HTML emails

Note: To include Graphs you must enable unauthorized graphs in config.php. Allow_unauth_graphs_cidr is optional, but more secure.
```
$config['allow_unauth_graphs_cidr'] = array('127.0.0.1/32');  
$config['allow_unauth_graphs'] = true;
```

Service Alert:
```
<div style="font-family:Helvetica;">
<h2>{if %state == 1}<span style="color:red;">%severity{/if}
{if %state == 2}<span style="color:goldenrod;">acknowledged{/if}</span>
{if %state == 3}<span style="color:green;">recovering{/if}</span>
{if %state == 0}<span style="color:green;">recovered{/if}</span>
</h2>
<b>Host:</b> %hostname<br>
<b>Duration:</b> %elapsed<br>
<br>

{if %faults}                                                                                       
{foreach %faults}<b>%value.service_desc - %value.service_type</b><br>                                         
%value.service_message<br>
<br>                                                                                         
{/foreach}                                                                                                             
{/if}
</div>
```

Processor Alert with Graph:
```
%title <br>
Severity: %severity  <br>
{if %state == 0}Time elapsed: %elapsed{/if}
Timestamp: %timestamp <br>
Alert-ID: %id <br>
Rule: {if %name}%name{else}%rule{/if} <br>
{if %faults}Faults:
{foreach %faults}
#%key: %value.string <br>
{/foreach}
{if %faults}<b>Faults:</b><br>
{foreach %faults}<img src="https://server/graph.php?device=%value.device_id&type=device_processor&width=459&height=213&lazy_w=552&from=end-72h%22/%3E<br>
https://server/graphs/id=%value.device_id/type=device_processor/<br>
{/foreach}
Template: CPU alert <br>
{/if}
{/if}
```

## Included

We include a few templates for you to use, these are specific to the type of alert rules you are creating. For example if you create a rule that would alert on BGP sessions then you can 
assign the BGP template to this rule to provide more information.

The included templates apart from the default template are:

  - BGP Sessions
  - Ports
  - Temperature
