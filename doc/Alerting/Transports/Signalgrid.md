# Signalgrid

Signalgrid can be used to send LibreNMS alerts as push notifications to iOS and Android devices.

## Configuration

Create a Signalgrid alert transport in LibreNMS and provide the following values:

| Config | Example |
| --- | --- |
| Client Key | `your-client-key` |
| Channel | `your-channel` |

## Severity mapping

LibreNMS alert severities are mapped to Signalgrid notification types as follows:

| LibreNMS | Signalgrid |
| --- | --- |
| Critical | CRIT |
| Warning | WARN |
| Recovered | SUCCESS |
| Other | INFO |

## Signalgrid setup

Create or select a channel in Signalgrid and copy its Client Key and Channel into the LibreNMS transport configuration.

For more information, see the Signalgrid documentation:

<https://docs.signalgrid.co/>
