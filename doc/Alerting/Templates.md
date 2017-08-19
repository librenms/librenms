source: Alerting/Templates.md

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

- Hostname of the Device: `%hostname`
- sysName of the Device: `%sysName`
- location of the Device: `%location`
- uptime of the Device (in seconds): `%uptime`
- short uptime of the Device (28d 22h 30m 7s): `%uptime_short`
- long uptime of the Device (28 days, 22h 30m 7s): `%uptime_long`
- description (purpose db field) of the Device: `%description`
- notes of the Device: `%notes`
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

## Testing

It's possible to test your new template before assigning it to a rule. To do so you can run `./scripts/test-template.php`. The script will provide the help 
info when ran without any parameters.

As an example, if you wanted to test template ID 10 against localhost running rule ID 2 then you would run:

`./scripts/test-template.php -t 10 -d -h localhost -r 2`

If the rule is currently alerting for localhost then you will get the full template as expected to see on email, if it's not then you will just see the 
template without any fault information.

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

Conditional formatting example, will display a link to the host in email or just the hostname in any other transport:
```text
{if %transport == mail}<a href="https://my.librenms.install/device/device=%hostname/">%hostname</a>
{else}
%hostname
{/if}
```

Note the use of double-quotes.  Single quotes (`'`) in templates will be escaped (replaced with `\'`) in the output and should therefore be avoided.

## Included

We include a few templates for you to use, these are specific to the type of alert rules you are creating. For example if you create a rule that would alert on BGP sessions then you can 
assign the BGP template to this rule to provide more information.

The included templates apart from the default template are:

  - BGP Sessions
  - Ports
  - Temperature
