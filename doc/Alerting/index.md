source: Alerting/index.md
path: blob/master/doc/

# About

LibreNMS includes a highly customizable alerting system.
The system requires a set of user-defined rules to evaluate the
situation of each device, port, service or any other entity.

> You can configure all options for alerting and transports via the
> WebUI, config options in this document are crossed out but left for reference.

Table of Content:

- [Introduction](Introduction.md)
- [Rules](Rules.md)
  - [Syntax](Rules.md#syntax)
  - [Options](Rules.md#options)
  - [Examples](Rules.md#examples)
  - [Procedure](Rules.md#procedure)
- [Templates](Templates.md)
  - [Syntax](Templates.md#syntax)
  - [Testing](Templates.md#testing)
  - [Examples](Templates.md#examples)
  - [Included](Templates.md#included)
- [Transports](Transports.md)
  - [E-Mail](Transports.md#e-mail)
  - [API](Transports.md#api)
  - [Browser Push](Transports.md#browser-push)
  - [Nagios-Compatible](Transports.md#nagios-compatible)
  - [IRC](Transports.md#irc)
  - [Slack](Transports.md#slack)
  - [Rocket.chat](Transports.md#rocketchat)
  - [HipChat](Transports.md#hipchat)
  - [PagerDuty](Transports.md#pagerduty)
  - [Pushover](Transports.md#pushover)
  - [Boxcar](Transports.md#boxcar)
  - [Telegram](Transports.md#telegram)
  - [Pushbullet](Transports.md#pushbullet)
  - [Clickatell](Transports.md#clickatell)
  - [PlaySMS](Transports.md#playsms)
  - [VictorOps](Transports.md#victorops)
  - [Canopsis](Transports.md#canopsis)
  - [osTicket](Transports.md#osticket)
  - [Microsoft Teams](Transports.md#microsoftteams)
  - [Cisco Spark](Transports.md#ciscospark)
  - [SMSEagle](Transports.md#smseagle)
  - [Syslog](Transports.md#syslog)
  - [Elasticsearch](Transports.md#elasticsearch)
  - [Jira](Transports.md#jira)
- [Entities](Entities.md)
  - [Devices](Entities.md#devices)
  - [BGP Peers](Entities.md#bgppeers)
  - [IPSec Tunnels](Entities.md#ipsec)
  - [Memory Pools](Entities.md#entity-mempools)
  - [Ports](Entities.md#entity-ports)
  - [Processors](Entities.md#entity-processors)
  - [Storage](Entities.md#entity-storage)
  - [Sensors](Entities.md#entity-sensors)
- [Macros](Macros.md)
  - [Device](Macros.md#macros-device)
  - [Port](Macros.md#macros-port)
  - [Time](Macros.md#macros-time)
  - [Sensors](Macros.md#macros-sensors)
  - [Misc](Macros.md#macros-misc)
- [Device Dependencies](Device-Dependencies.md)
