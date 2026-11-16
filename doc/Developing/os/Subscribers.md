# Subscribers

This document describes how to add subscriber support for a BNG, BRAS or
PPPoE/IPoE concentrator. Devices with subscribers get a Subscribers tab
with the number of sessions that are up, per session type, for the device
and per access port, and the RADIUS servers the device uses.

The `subscribers` module does the storing, graphing and cleanup. An OS only
returns the counts it reads from the device.

## Subscriber sessions

Implement `LibreNMS\Interfaces\Discovery\SubscriberDiscovery` and return a
`SubscriberCount` for every count the device reports:

| Field        | Value                                                                    |
| ------------ | ------------------------------------------------------------------------ |
| port_id      | `null` for the device, or the port_id of the access port                 |
| session_type | one of the session types below                                           |
| sessions     | the number of sessions that are up                                       |

| Session type | Description                                                             |
| ------------ | ----------------------------------------------------------------------- |
| total        | The device total, only when the device reports one                      |
| pppoe        | PPPoE sessions                                                          |
| ppp          | PPP sessions that are not known to be PPPoE                             |
| ipoe         | IP sessions, DHCP or IP triggered (IPoE)                                |
| l2tp         | L2TP sessions                                                           |
| other        | Any other session type                                                  |

```php
use App\Models\SubscriberCount;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Interfaces\Discovery\SubscriberDiscovery;

class Example extends OS implements SubscriberDiscovery
{
    public function discoverSubscribers(): ?Collection
    {
        try {
            $sessions = SnmpQuery::get('EXAMPLE-MIB::exPppoeSessions.0')->throwIfConnectionError()->value();
        } catch (SnmpException) {
            return null; // the device did not answer, the counts are kept
        }

        return collect([
            new SubscriberCount(['port_id' => null, 'session_type' => 'pppoe', 'sessions' => (int) $sessions]),
        ]);
    }
}
```

`discoverSubscribers()` is called at discovery and at every poll, so only
read aggregated counters, a few gets or walks. Do not walk a table with a row
per session.

The module takes care of the rest:

- Unknown session types are stored as `other`.
- Counts with the same port and session type are added, so a vendor type can
  map several of its own session types to one session type.
- A port `total` is dropped when the port also has session types (the total
  of a port is the sum of its session types).
- A device that only reports 0 is not a subscriber device, many devices
  answer 0 without a PPP server. Once there were sessions the counts are kept,
  so a drop to 0 is seen.
- Polling sets counts the device no longer reports to 0, discovery removes them.
- The device counts are graphed, port counts are stored without graphs, a BNG
  may have a port per customer.

## RADIUS servers

Devices with subscribers can also report their RADIUS authentication servers.
For devices that support the standard RADIUS-AUTH-CLIENT-MIB, use the trait:

```php
use LibreNMS\Interfaces\Discovery\RadiusServerDiscovery;
use LibreNMS\OS\Traits\RadiusAuthClientMib;

class Example extends OS implements SubscriberDiscovery, RadiusServerDiscovery
{
    use RadiusAuthClientMib;
}
```

Other devices implement `discoverRadiusServers()` and return a `RadiusServer`
per server. Leave counters the device does not report `null`, they are shown
as not available. `round_trip_time` is in milliseconds.

RADIUS servers are only discovered for devices with subscribers, other
devices may use RADIUS for logins or 802.1X.

## Tests

Add test data with subscriber counts, see [Unit Tests](Test-Units.md). The
`iosxr_bng` and `iosxe_bng` test data are examples.
