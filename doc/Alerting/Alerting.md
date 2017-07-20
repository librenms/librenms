source: Alerting/Alerting.md
Table of Content:

- [About](#about)
- [Rules](Rules.md)
    - [Syntax](Rules.md#rules-syntax)
    - [Options](Rules.md#extra)
    - [Examples](Rules.md#rules-examples)
    - [Procedure](Rules.md#rules-procedure)
- [Templates](Templates.md)
    - [Syntax](Templates.md#templates-syntax)
    - [Testing](Templates.md#templates-testing)
    - [Examples](Templates.md#templates-examples)
    - [Included](Templates.md#templates-included)
- [Transports](Transports.md)
    - [E-Mail](Transports.md#transports-email)
    - [API](Transports.md#transports-api)
    - [Nagios-Compatible](Transports.md#transports-nagios)
    - [IRC](Transports.md#transports-irc)
    - [Slack](Transports.md#transports-slack)
    - [Rocket.chat](Transports.md#transports-rocket)
    - [HipChat](Transports.md#transports-hipchat)
    - [PagerDuty](Transports.md#transports-pagerduty)
    - [Pushover](Transports.md#transports-pushover)
    - [Boxcar](#transports-boxcar)
    - [Telegram](Transports.md#transports-telegram)
    - [Pushbullet](Transports.md#transports-pushbullet)
    - [Clickatell](Transports.md#transports-clickatell)
    - [PlaySMS](Transports.md#transports-playsms)
    - [VictorOps](Transports.md#transports-victorops)
    - [Canopsis](Transports.md#transports-canopsis)
    - [osTicket](Transports.md#transports-osticket)
    - [Microsoft Teams](Transports.md#transports-msteams)
    - [Cisco Spark](Transports.md#transports-ciscospark)
    - [SMSEagle](Transports.md#transports-smseagle)
    - [Syslog](Transports.md#transports-syslog)
    - [Elasticsearch](Transports.md#transports-elasticsearch)
    - [Jira](Transports.md#transports-jira)
    
- [Entities](Entities.md)
    - [Devices](Entities.md#entity-devices)
    - [BGP Peers](Entities.md#entity-bgppeers)
    - [IPSec Tunnels](Entities.md#entity-ipsec)
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


# <a name="about">About</a>

LibreNMS includes a highly customizable alerting system.
The system requires a set of user-defined rules to evaluate the situation of each device, port, service or any other entity.

> You can configure all options for alerting and transports via the WebUI, config options in this document are crossed out but left for reference.
