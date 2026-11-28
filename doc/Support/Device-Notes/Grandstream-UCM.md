source: Support/Device-Notes/Grandstream-UCM.md

# Grandstream UCM monitoring

## Supported devices

The `grandstream-ucm` definition detects the UCM63xx family using the generic
Net-SNMP sysObjectID together with an anchored `Linux UCM63xx` sysDescr match.
The vendor documents this SNMP interface for UCM63xx firmware **1.0.29.19 or newer**.
Detection and polling have been exercised on two UCM6308 devices running
firmware 1.0.33.42, in addition to the original 1.0.33.32 recording.

The [Grandstream IP PBX SNMP guide](https://documentation.grandstream.com/knowledge-base/ip-pbx-snmp-guide/)
also lists SoftwareUCM and GCC PBX. They are not automatically included in this
definition: sharing some OIDs does not establish a matching sysDescr or a complete
device recording. The guide does not list UCM62xx or UCM6510. Those models are
therefore deliberately excluded until equivalent SNMP support and a real recording
are available. This is a support boundary, not a claim that every older model lacks
all SNMP capabilities.

## Trunk states and evidence

`sTrunksStatus` is an OCTET STRING. The MIB provides no enumeration and no
guarantee that a finite list covers every firmware response. Numeric sensor values
are internal LibreNMS identifiers, not integer values supplied by the device.

| Text | Value | Generic state | Evidence |
| --- | ---: | --- | --- |
| Registered | 0 | OK | Real SNMP recording, current device polling and historical SNMP producer messages |
| Reachable | 1 | OK | Real SNMP recording, current device polling and historical SNMP producer messages |
| Available | 2 | OK | Vendor dashboard documentation for analog trunks; retained for compatibility, not independently captured on SNMP |
| Disabled | 3 | Unknown | Administrative state; retained for compatibility, not independently captured on this OID |
| Unmonitored | 4 | Unknown | Vendor documentation identifies disabled heartbeat monitoring; not independently captured on SNMP |
| Unregistered | 5 | Critical | Historical UCM6308 SNMP producer messages, corroborated by LibreNMS state transitions |
| Unreachable | 6 | Critical | Historical UCM6308 SNMP producer messages, corroborated by LibreNMS state transitions |
| Unknown | 7 | Unknown | Explicit fallback for missing or unrecognized responses; not a claim about firmware enumeration |

The [UCM630x manual](https://www.grandstream.com/hubfs/Product_Documentation/UCM6300_usermanual.pdf)
describes dashboard states, which must not be confused with a complete SNMP string
contract. Likewise, the [SIP trunks guide](https://documentation2.grandstream.com/knowledge-base/sip-trunks-guide/)
describes `Rejected` as a registration failure but does not guarantee that exact
string is returned by this SNMP OID. Such responses are safely represented by
Unknown until their wire representation is confirmed.

Historical observations are retained as anonymized **unit-test inputs** under
`tests/fixtures/grandstream-ucm/`. They are not presented as a historical full-device
SNMP capture. The Registered and Reachable `.snmprec` files are actual device
recordings, with private identity fields sanitized. The firmware version at the
time of historical log entries was not independently recovered.

## Failure handling and history

Discovery and polling normalize case and surrounding whitespace. Unknown text,
numeric-looking strings and missing values explicitly become Unknown before generic
numeric extraction. The existing state identifiers remain unchanged so previously
recorded histories keep their meaning.

When a trunk-table request fails with a transport or command error, discovery keeps
existing active trunk sensors that are missing from the partial response and records
them as Unknown. Returned rows retain their reported values. A successful empty walk
still permits normal cleanup of deliberately removed trunks. A row whose status
column is missing remains present as Unknown.

Unknown has generic state 3; notification behavior follows the installation's alert
rules. It is not silently classified as Registered or OK.

The overview contains CPU and memory graphs. Individual call sensors remain
available in Health, but the overview does not combine concurrent calls and the
rates calculated from cumulative counters into one graph.

## Verification

Use a disposable test database for fixture generation and database-backed tests.
Generate expected JSON from the real recordings with `dev:generate-test-data`;
do not synthesize failure recordings from historical log strings.

The device tests cover OS detection, real-recording discovery and polling, known
historical statuses, unknown and missing values, failed and partial table walks,
intentional trunk removal, preserved sensor identity and the UCM63xx family boundary.
The test inputs for unknown strings and transport failures are explicit mocks.
