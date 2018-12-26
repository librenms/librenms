##1.46
*(2018-12-02)*

A big thank you to the following 38 contributors this last month:

  - murrant (15)
  - laf (15)
  - crazy-max (2)
  - lowinger42 (2)
  - TheGreatDoc (2)
  - Rosiak (2)
  - remyj38 (2)
  - mattie47 (2)
  - VVelox (2)
  - nova-2nd (2)
  - pcw11211 (2)
  - sippe2 (1)
  - FTBZ (1)
  - Zmegolaz (1)
  - vitalisator (1)
  - arjitc (1)
  - TylerSweet (1)
  - takenalias (1)
  - loopodoopo (1)
  - empi89 (1)
  - 2E0PGS (1)
  - lewisvive (1)
  - jozefrebjak (1)
  - alangregory (1)
  - luukp (1)
  - zombah (1)
  - ded7 (1)
  - kkrumm1 (1)
  - alessandro-lion (1)
  - paulocoimbrati (1)
  - theherodied (1)
  - Jellyfrog (1)
  - hexdump0x0200 (1)
  - jasoncheng7115 (1)
  - VirTechSystems (1)
  - PipoCanaja (1)
  - sjtarik (1)
  - dword4 (1)

#### Alerting
* Changed variable name to resolve issue with Gitlab transport ([#9504](https://github.com/librenms/librenms/pull/9504)) - [dword4](https://github.com/dword4)
* Added ability to record traceroutes for devices down due to ICMP ([#9457](https://github.com/librenms/librenms/pull/9457)) - [laf](https://github.com/laf)
* Fixed altering transport mapping in rules clearing all mappings ([#9455](https://github.com/librenms/librenms/pull/9455)) - [laf](https://github.com/laf)
* Show visually in webui + cli when using deprecated templates or transports ([#9413](https://github.com/librenms/librenms/pull/9413)) - [laf](https://github.com/laf)
* Added format field to Telegram Messages ([#9404](https://github.com/librenms/librenms/pull/9404)) - [paulocoimbrati](https://github.com/paulocoimbrati)
* Added support for using Transport name in templates ([#9411](https://github.com/librenms/librenms/pull/9411)) - [laf](https://github.com/laf)

#### Bug
* Changed variable name to resolve issue with Gitlab transport ([#9504](https://github.com/librenms/librenms/pull/9504)) - [dword4](https://github.com/dword4)
* Fixed Procera ports ifIndex and ports added by the poller ([#9384](https://github.com/librenms/librenms/pull/9384)) - [murrant](https://github.com/murrant)
* Fixed os additional information for some that were broke ([#9466](https://github.com/librenms/librenms/pull/9466)) - [murrant](https://github.com/murrant)
* Bug-fix and new features routeros ([#9401](https://github.com/librenms/librenms/pull/9401)) - [takenalias](https://github.com/takenalias)
* Raisecom fix uptime ([#9470](https://github.com/librenms/librenms/pull/9470)) - [vitalisator](https://github.com/vitalisator)
* Fixed altering transport mapping in rules clearing all mappings ([#9455](https://github.com/librenms/librenms/pull/9455)) - [laf](https://github.com/laf)
* Fixed ping.php poller groups setting ([#9447](https://github.com/librenms/librenms/pull/9447)) - [murrant](https://github.com/murrant)
* Ensure the checks for ASA context devices are strict ([#9441](https://github.com/librenms/librenms/pull/9441)) - [laf](https://github.com/laf)
* Fixed delta calculation for bgpPeers_cbgp metrics ([#9431](https://github.com/librenms/librenms/pull/9431)) - [hexdump0x0200](https://github.com/hexdump0x0200)

#### Webui
* Locations UI and editing ([#9480](https://github.com/librenms/librenms/pull/9480)) - [murrant](https://github.com/murrant)
* Fixed do not include alert template text in HTML page ([#9476](https://github.com/librenms/librenms/pull/9476)) - [lowinger42](https://github.com/lowinger42)
* Sort sensors by sensor_descr ([#9478](https://github.com/librenms/librenms/pull/9478)) - [arjitc](https://github.com/arjitc)
* Fixed $speed length in port parser when > 32 characters ([#9479](https://github.com/librenms/librenms/pull/9479)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Filter email options based on backend ([#9461](https://github.com/librenms/librenms/pull/9461)) - [murrant](https://github.com/murrant)
* Added Traffic to the Windows OS overlib graph ([#9445](https://github.com/librenms/librenms/pull/9445)) - [kkrumm1](https://github.com/kkrumm1)
* Disabled page refresh on health sensors pages, autorefresh most tables ([#9386](https://github.com/librenms/librenms/pull/9386)) - [murrant](https://github.com/murrant)
* Show port description and dns name in FDB table ([#9370](https://github.com/librenms/librenms/pull/9370)) - [Jellyfrog](https://github.com/Jellyfrog)
* Added alerts schedule notes into device notes ([#9258](https://github.com/librenms/librenms/pull/9258)) - [remyj38](https://github.com/remyj38)
* Added feature to sort alert schedules by status ([#9257](https://github.com/librenms/librenms/pull/9257)) - [remyj38](https://github.com/remyj38)
* Allow 6 or 12 icons across on server stats ([#9408](https://github.com/librenms/librenms/pull/9408)) - [VirTechSystems](https://github.com/VirTechSystems)
* Updated Edit user page with new Auth system ([#9313](https://github.com/librenms/librenms/pull/9313)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Feature
* Locations UI and editing ([#9480](https://github.com/librenms/librenms/pull/9480)) - [murrant](https://github.com/murrant)
* Syslog name translation ([#9463](https://github.com/librenms/librenms/pull/9463)) - [TylerSweet](https://github.com/TylerSweet)
* Added a tool for working with JSON apps ([#9084](https://github.com/librenms/librenms/pull/9084)) - [VVelox](https://github.com/VVelox)
* Added ability to record traceroutes for devices down due to ICMP ([#9457](https://github.com/librenms/librenms/pull/9457)) - [laf](https://github.com/laf)
* Added ScreenOS Syslog Hook ([#9438](https://github.com/librenms/librenms/pull/9438)) - [nova-2nd](https://github.com/nova-2nd)
* Added Bing geocode ([#9434](https://github.com/librenms/librenms/pull/9434)) - [pcw11211](https://github.com/pcw11211)

#### Device
* Added support for Waystream products ([#9481](https://github.com/librenms/librenms/pull/9481)) - [lowinger42](https://github.com/lowinger42)
* Added Aprisa support ([#9435](https://github.com/librenms/librenms/pull/9435)) - [loopodoopo](https://github.com/loopodoopo)
* Fixed Procera ports ifIndex and ports added by the poller ([#9384](https://github.com/librenms/librenms/pull/9384)) - [murrant](https://github.com/murrant)
* Fixed and added features routeros ([#9401](https://github.com/librenms/librenms/pull/9401)) - [takenalias](https://github.com/takenalias)
* Updated support for Avocent devices ([#9462](https://github.com/librenms/librenms/pull/9462)) - [laf](https://github.com/laf)
* Added support for Firebrick Hardware ([#9403](https://github.com/librenms/librenms/pull/9403)) - [lewisvive](https://github.com/lewisvive)
* Added new sysDescr string for AlliedWare Plus products. ([#9430](https://github.com/librenms/librenms/pull/9430)) - [luukp](https://github.com/luukp)
* Added more sensors for IRD (PBI Digital Decoder) ([#9339](https://github.com/librenms/librenms/pull/9339)) - [jozefrebjak](https://github.com/jozefrebjak)
* Updated detection for AKCP devices ([#9460](https://github.com/librenms/librenms/pull/9460)) - [laf](https://github.com/laf)
* Update allied.yaml ([#9454](https://github.com/librenms/librenms/pull/9454)) - [mattie47](https://github.com/mattie47)
* Updated HiveOS wireless detection ([#9459](https://github.com/librenms/librenms/pull/9459)) - [laf](https://github.com/laf)
* Removed unnecessary model checks ([#9409](https://github.com/librenms/librenms/pull/9409)) - [theherodied](https://github.com/theherodied)
* Improve Junos state sensor discovery ([#9426](https://github.com/librenms/librenms/pull/9426)) - [Rosiak](https://github.com/Rosiak)
* DrayTek OS - Added Hardware and OS Version. ([#9389](https://github.com/librenms/librenms/pull/9389)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Cisco UCS - Add initial state sensor support ([#9335](https://github.com/librenms/librenms/pull/9335)) - [Rosiak](https://github.com/Rosiak)
* New os Eltek WebPower - files + test files ([#9174](https://github.com/librenms/librenms/pull/9174)) - [sippe2](https://github.com/sippe2)
* Broaden DeltaUPS OID to include new devices/firmware ([#9385](https://github.com/librenms/librenms/pull/9385)) - [murrant](https://github.com/murrant)

#### Documentation
* Change group owner for php/session in CentOS install docs ([#9393](https://github.com/librenms/librenms/pull/9393)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Improve documentation for service plugins ([#9414](https://github.com/librenms/librenms/pull/9414)) - [2E0PGS](https://github.com/2E0PGS)
* Added allow ipv6 address localhost nginx-status ([#9458](https://github.com/librenms/librenms/pull/9458)) - [ded7](https://github.com/ded7)
* Update docs for virtual images ([#9456](https://github.com/librenms/librenms/pull/9456)) - [laf](https://github.com/laf)
* Added official docker image installation ([#9398](https://github.com/librenms/librenms/pull/9398)) - [crazy-max](https://github.com/crazy-max)

#### Api
* Added list_links and get_link api calls ([#9444](https://github.com/librenms/librenms/pull/9444)) - [zombah](https://github.com/zombah)


##1.45
*(2018-10-28)*

A big thank you to the following 25 contributors this last month:

  - murrant (14)
  - PipoCanaja (4)
  - laf (3)
  - takenalias (3)
  - JohnSPeach (2)
  - jozefrebjak (2)
  - Jellyfrog (2)
  - TheGreatDoc (2)
  - brianatlarge (2)
  - crazy-max (1)
  - xudonax (1)
  - alangregory (1)
  - VirTechSystems (1)
  - slashdoom (1)
  - angryp (1)
  - sippe2 (1)
  - voxnil (1)
  - kkrumm1 (1)
  - Rosiak (1)
  - sparknsh (1)
  - andyrosen (1)
  - tomarch (1)
  - vitalisator (1)
  - lucianosds (1)
  - acl (1)

#### Documentation
* Updated to use new theme for docs site ([#9320](https://github.com/librenms/librenms/pull/9320)) - [laf](https://github.com/laf)
* Point out the poller module graph. ([#9378](https://github.com/librenms/librenms/pull/9378)) - [murrant](https://github.com/murrant)
* Telegram group support ([#9355](https://github.com/librenms/librenms/pull/9355)) - [lucianosds](https://github.com/lucianosds)
* Added geocode engine configuration information ([#9330](https://github.com/librenms/librenms/pull/9330)) - [brianatlarge](https://github.com/brianatlarge)

#### Device
* MGE UPS support improvement (incl. traps) ([#9301](https://github.com/librenms/librenms/pull/9301)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for 64bits memory pools on CISCO-ENHANCED-MEMPOOL-MIB ([#9353](https://github.com/librenms/librenms/pull/9353)) - [alangregory](https://github.com/alangregory)
* Improved support for Racom Ray radios ([#9279](https://github.com/librenms/librenms/pull/9279)) - [jozefrebjak](https://github.com/jozefrebjak)
* Added support for Mirkrotik Wireless Wire (wAP 60G) ([#9318](https://github.com/librenms/librenms/pull/9318)) - [takenalias](https://github.com/takenalias)
* Added netagent2 3phase support ([#9175](https://github.com/librenms/librenms/pull/9175)) - [sippe2](https://github.com/sippe2)
* Cyberpower extra sensor values ([#9278](https://github.com/librenms/librenms/pull/9278)) - [takenalias](https://github.com/takenalias)
* Improved vCenter discovery ([#9344](https://github.com/librenms/librenms/pull/9344)) - [Rosiak](https://github.com/Rosiak)
* F5 APM current sessions graphing ([#9334](https://github.com/librenms/librenms/pull/9334)) - [JohnSPeach](https://github.com/JohnSPeach)
* Removed apc-ats os and merge sensors into apc ([#9262](https://github.com/librenms/librenms/pull/9262)) - [tomarch](https://github.com/tomarch)
* Added support for LLDP on ALCATEL/NOKIA SR OS ([#9298](https://github.com/librenms/librenms/pull/9298)) - [vitalisator](https://github.com/vitalisator)
* Updated DataDomain MIB, Added DataDomain Storage poller and discovery ([#9270](https://github.com/librenms/librenms/pull/9270)) - [acl](https://github.com/acl)
* Improved processors and mempools support for VRRP ([#9300](https://github.com/librenms/librenms/pull/9300)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Webui
* Graph of overall poller performance ([#9381](https://github.com/librenms/librenms/pull/9381)) - [murrant](https://github.com/murrant)
* Redesign alert template modal ([#9364](https://github.com/librenms/librenms/pull/9364)) - [crazy-max](https://github.com/crazy-max)
* Show device "features" in correct column ([#9366](https://github.com/librenms/librenms/pull/9366)) - [Jellyfrog](https://github.com/Jellyfrog)
* Optimize images ([#9369](https://github.com/librenms/librenms/pull/9369)) - [Jellyfrog](https://github.com/Jellyfrog)
* Reorganise the alert settings page to show what options are deprecated ([#9354](https://github.com/librenms/librenms/pull/9354)) - [laf](https://github.com/laf)
* Fixed field type for processor_usage and _perc_warn ([#9357](https://github.com/librenms/librenms/pull/9357)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Don't check file permissions on every request, handle failures ([#9264](https://github.com/librenms/librenms/pull/9264)) - [murrant](https://github.com/murrant)
* Attempt to make proxy sub-dir -> app no subdir work ([#9317](https://github.com/librenms/librenms/pull/9317)) - [murrant](https://github.com/murrant)
* Oxidized rights enforcement ([#9331](https://github.com/librenms/librenms/pull/9331)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Bug
* Allow F5 APM graphs to display automatically ([#9377](https://github.com/librenms/librenms/pull/9377)) - [JohnSPeach](https://github.com/JohnSPeach)
* Fixed typo in Raspberry Pi sensor detection ([#9368](https://github.com/librenms/librenms/pull/9368)) - [xudonax](https://github.com/xudonax)
* Don't overwrite processor warn percentage ([#9380](https://github.com/librenms/librenms/pull/9380)) - [murrant](https://github.com/murrant)
* Services writing time field to InfluxDB ([#9358](https://github.com/librenms/librenms/pull/9358)) - [slashdoom](https://github.com/slashdoom)
* Attempt to escape services commands properly. ([#9269](https://github.com/librenms/librenms/pull/9269)) - [murrant](https://github.com/murrant)

#### Feature
* Validate Database and PHP time match ([#9373](https://github.com/librenms/librenms/pull/9373)) - [murrant](https://github.com/murrant)
* Add Mapquest API Geocode support ([#9316](https://github.com/librenms/librenms/pull/9316)) - [brianatlarge](https://github.com/brianatlarge)

#### Alerting
* Fixed field type for processor_usage and _perc_warn ([#9357](https://github.com/librenms/librenms/pull/9357)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Telegram group support ([#9355](https://github.com/librenms/librenms/pull/9355)) - [lucianosds](https://github.com/lucianosds)
* Added Twilio SMS as an Alerting Transport ([#9305](https://github.com/librenms/librenms/pull/9305)) - [andyrosen](https://github.com/andyrosen)

#### Security
* Attempt to escape services commands properly. ([#9269](https://github.com/librenms/librenms/pull/9269)) - [murrant](https://github.com/murrant)

##1.44
*(2018-10-17)*

A big thank you to the following 31 contributors this last month:

  - murrant (63)
  - TheGreatDoc (16)
  - laf (14)
  - PipoCanaja (8)
  - Rosiak (4)
  - SaaldjorMike (2)
  - therealshibe (2)
  - mattie47 (2)
  - jozefrebjak (2)
  - Cormoran96 (2)
  - sjtarik (2)
  - dlangille (2)
  - cchance27 (1)
  - crazy-max (1)
  - CFUJoshWeepie (1)
  - trs80 (1)
  - DR3EVR8u8c (1)
  - Atroskelis (1)
  - Leapo (1)
  - vowywowy (1)
  - FTBZ (1)
  - jarischaefer (1)
  - pmusolino-rms (1)
  - crcro (1)
  - vitalisator (1)
  - dupondje (1)
  - Nesousx (1)
  - lug-gh (1)
  - backslash7 (1)
  - cppmonkey (1)
  - ciscoqid (1)

#### Alerting
* Use correct ID for incident resolution in PagerDuty ([#9321](https://github.com/librenms/librenms/pull/9321)) - [laf](https://github.com/laf)
* Fixed bug of alerting by ping.php ([#9311](https://github.com/librenms/librenms/pull/9311)) - [DR3EVR8u8c](https://github.com/DR3EVR8u8c)
* Added ability to set a custom SQL query for alert rules. ([#9094](https://github.com/librenms/librenms/pull/9094)) - [laf](https://github.com/laf)
* Added support for allowing alerts to un-ack ([#9136](https://github.com/librenms/librenms/pull/9136)) - [laf](https://github.com/laf)
* Fixed PagerDuty alert to show rule name + device as summary ([#9213](https://github.com/librenms/librenms/pull/9213)) - [laf](https://github.com/laf)
* Modified timestamp sent to nagios-receiver in order to fix an issue w… ([#9140](https://github.com/librenms/librenms/pull/9140)) - [Nesousx](https://github.com/Nesousx)
* Enable and catch exceptions for PHPMailer to gather error messages. ([#9132](https://github.com/librenms/librenms/pull/9132)) - [ciscoqid](https://github.com/ciscoqid)
* Additional debug output when sending/testing email ([#9120](https://github.com/librenms/librenms/pull/9120)) - [murrant](https://github.com/murrant)

#### Bug
* Use correct ID for incident resolution ([#9321](https://github.com/librenms/librenms/pull/9321)) - [laf](https://github.com/laf)
* Fixed bug of alerting by ping.php ([#9311](https://github.com/librenms/librenms/pull/9311)) - [DR3EVR8u8c](https://github.com/DR3EVR8u8c)
* Bind user before fetching ([#9312](https://github.com/librenms/librenms/pull/9312)) - [murrant](https://github.com/murrant)
* Check if array exists for new alert rules and create if not ([#9303](https://github.com/librenms/librenms/pull/9303)) - [laf](https://github.com/laf)
* Fixed devices state filter when state = 0 ([#9277](https://github.com/librenms/librenms/pull/9277)) - [murrant](https://github.com/murrant)
* Fixed eventlog when the device has been deleted. ([#9276](https://github.com/librenms/librenms/pull/9276)) - [murrant](https://github.com/murrant)
* Config class collides with Config in model namespace ([#9249](https://github.com/librenms/librenms/pull/9249)) - [murrant](https://github.com/murrant)
* Syslog fixes ([#9246](https://github.com/librenms/librenms/pull/9246)) - [murrant](https://github.com/murrant)
* Disable used rules in template map for select them ([#9212](https://github.com/librenms/librenms/pull/9212)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fixed edit dashboard permissions + moved to toastr ([#9236](https://github.com/librenms/librenms/pull/9236)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fixed remember me ([#9233](https://github.com/librenms/librenms/pull/9233)) - [murrant](https://github.com/murrant)
* Fixed PagerDuty alert to show rule name + device as summary ([#9213](https://github.com/librenms/librenms/pull/9213)) - [laf](https://github.com/laf)
* Fixed install.php redirect ([#9224](https://github.com/librenms/librenms/pull/9224)) - [murrant](https://github.com/murrant)
* Allow trusted proxy via APP_TRUSTED_PROXIES ([#9196](https://github.com/librenms/librenms/pull/9196)) - [murrant](https://github.com/murrant)
* Remove api rate limits ([#9211](https://github.com/librenms/librenms/pull/9211)) - [laf](https://github.com/laf)
* Fixed API auth issues ([#9185](https://github.com/librenms/librenms/pull/9185)) - [murrant](https://github.com/murrant)
* Init and refresh the php session each page load ([#9186](https://github.com/librenms/librenms/pull/9186)) - [murrant](https://github.com/murrant)
* Use UTC if date.timezone is not set ([#9181](https://github.com/librenms/librenms/pull/9181)) - [jarischaefer](https://github.com/jarischaefer)
* Fixed devices unpolled check ([#9199](https://github.com/librenms/librenms/pull/9199)) - [murrant](https://github.com/murrant)
* Fixed anonymous bind ([#9195](https://github.com/librenms/librenms/pull/9195)) - [murrant](https://github.com/murrant)
* Fixed auth user level not updated ([#9190](https://github.com/librenms/librenms/pull/9190)) - [murrant](https://github.com/murrant)
* Fixed error in logout ([#9189](https://github.com/librenms/librenms/pull/9189)) - [murrant](https://github.com/murrant)
* Fixed up ldap-authorizer, create non-existent users ([#9192](https://github.com/librenms/librenms/pull/9192)) - [murrant](https://github.com/murrant)
* Ignore dns errors when fetching astext ([#9180](https://github.com/librenms/librenms/pull/9180)) - [murrant](https://github.com/murrant)
* Fixed edit processors/storage/memory search query ([#9172](https://github.com/librenms/librenms/pull/9172)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fixed remove alert rule from template ([#9173](https://github.com/librenms/librenms/pull/9173)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fixed latency over 1s causes db update to fail ([#9157](https://github.com/librenms/librenms/pull/9157)) - [murrant](https://github.com/murrant)
* Fixed PeeringDB module ([#9158](https://github.com/librenms/librenms/pull/9158)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Modified timestamp sent to nagios-receiver in order to fix an issue w… ([#9140](https://github.com/librenms/librenms/pull/9140)) - [Nesousx](https://github.com/Nesousx)
* Fixed edgeswitch temperatures ([#9130](https://github.com/librenms/librenms/pull/9130)) - [murrant](https://github.com/murrant)
* Fixed Total Chassis Power sensor_index for SmartAX MA5603T/MA5683T ([#9115](https://github.com/librenms/librenms/pull/9115)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Don't update ospf stats if there are none. ([#9133](https://github.com/librenms/librenms/pull/9133)) - [murrant](https://github.com/murrant)
* Fixed web installer to allow users to change db creds if we can't connect ([#9126](https://github.com/librenms/librenms/pull/9126)) - [laf](https://github.com/laf)
* Fixed varchar comparision when using numeric and text sensor_index ([#9114](https://github.com/librenms/librenms/pull/9114)) - [TheGreatDoc](https://github.com/TheGreatDoc)

#### Device
* Support for Glass Way EYDFA WDM Optical Amplifier ([#9125](https://github.com/librenms/librenms/pull/9125)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Added support for PBI Digital Decoder ([#9223](https://github.com/librenms/librenms/pull/9223)) - [jozefrebjak](https://github.com/jozefrebjak)
* Added support for Teleste Luminato ([#9061](https://github.com/librenms/librenms/pull/9061)) - [jozefrebjak](https://github.com/jozefrebjak)
* Fixing Sentry 3 and 4 Temperature Sensors ([#9177](https://github.com/librenms/librenms/pull/9177)) - [sjtarik](https://github.com/sjtarik)
* Added specific support for APC Automatic Transfer Switch ([#9221](https://github.com/librenms/librenms/pull/9221)) - [FTBZ](https://github.com/FTBZ)
* Added Allied Telesis oxidized syslog hook support ([#9219](https://github.com/librenms/librenms/pull/9219)) - [mattie47](https://github.com/mattie47)
* Collect VRP Entity details in the Huawei MIB ([#8888](https://github.com/librenms/librenms/pull/8888)) - [PipoCanaja](https://github.com/PipoCanaja)
* Initial support for CXR-Networks Terminal Server ([#9169](https://github.com/librenms/librenms/pull/9169)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fixed ES3510MA os info ([#9150](https://github.com/librenms/librenms/pull/9150)) - [murrant](https://github.com/murrant)
* Edgeswitch fans ([#9162](https://github.com/librenms/librenms/pull/9162)) - [crcro](https://github.com/crcro)
* Added HPE iLO Version Polling ([#9146](https://github.com/librenms/librenms/pull/9146)) - [Rosiak](https://github.com/Rosiak)
* Added OpenBSD Carp State Sensor ([#9143](https://github.com/librenms/librenms/pull/9143)) - [Rosiak](https://github.com/Rosiak)
* Added Comware Power Usage ([#9016](https://github.com/librenms/librenms/pull/9016)) - [Rosiak](https://github.com/Rosiak)
* Added support for EdgeCore ES3510MA ([#9081](https://github.com/librenms/librenms/pull/9081)) - [backslash7](https://github.com/backslash7)
* Fixed Total Chassis Power sensor_index for SmartAX MA5603T/MA5683T ([#9115](https://github.com/librenms/librenms/pull/9115)) - [TheGreatDoc](https://github.com/TheGreatDoc)

#### Webui
* Don't force root url ([#9308](https://github.com/librenms/librenms/pull/9308)) - [murrant](https://github.com/murrant)
* Allow zoom to be decimal and switch zoomSnap to 0.1 ([#9259](https://github.com/librenms/librenms/pull/9259)) - [cchance27](https://github.com/cchance27)
* Don't call to legacy auth to get dashboards. ([#9297](https://github.com/librenms/librenms/pull/9297)) - [murrant](https://github.com/murrant)
* Check if array exists for new alert rules and create if not ([#9303](https://github.com/librenms/librenms/pull/9303)) - [laf](https://github.com/laf)
* Force root url if set by user ([#9266](https://github.com/librenms/librenms/pull/9266)) - [murrant](https://github.com/murrant)
* Fixed devices state filter when state = 0 ([#9277](https://github.com/librenms/librenms/pull/9277)) - [murrant](https://github.com/murrant)
* Keeps the dashboard sessions from expiring. ([#9263](https://github.com/librenms/librenms/pull/9263)) - [murrant](https://github.com/murrant)
* Allow login with GET variables ([#9268](https://github.com/librenms/librenms/pull/9268)) - [murrant](https://github.com/murrant)
* Fixed eventlog when the device has been deleted. ([#9276](https://github.com/librenms/librenms/pull/9276)) - [murrant](https://github.com/murrant)
* Eventlog WebUI/backend update ([#9252](https://github.com/librenms/librenms/pull/9252)) - [murrant](https://github.com/murrant)
* Syslog fixes ([#9246](https://github.com/librenms/librenms/pull/9246)) - [murrant](https://github.com/murrant)
* Disable used rules in template map for select them ([#9212](https://github.com/librenms/librenms/pull/9212)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Update syslog display and backend ([#9228](https://github.com/librenms/librenms/pull/9228)) - [murrant](https://github.com/murrant)
* Fixed edit dashboard permissions + moved to toastr ([#9236](https://github.com/librenms/librenms/pull/9236)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Add support for allowing alerts to un-ack ([#9136](https://github.com/librenms/librenms/pull/9136)) - [laf](https://github.com/laf)
* Fixed remember me ([#9233](https://github.com/librenms/librenms/pull/9233)) - [murrant](https://github.com/murrant)
* Fixed install.php redirect ([#9224](https://github.com/librenms/librenms/pull/9224)) - [murrant](https://github.com/murrant)
* Use of generate_url in Oxidized page ([#9200](https://github.com/librenms/librenms/pull/9200)) - [PipoCanaja](https://github.com/PipoCanaja)
* Allow trusted proxy via APP_TRUSTED_PROXIES ([#9196](https://github.com/librenms/librenms/pull/9196)) - [murrant](https://github.com/murrant)
* Show sensors warnings values + moved to json and toastr ([#9210](https://github.com/librenms/librenms/pull/9210)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Don't output non-fatal errors from legacy web pages. ([#9208](https://github.com/librenms/librenms/pull/9208)) - [murrant](https://github.com/murrant)
* Init and refresh the php session each page load ([#9186](https://github.com/librenms/librenms/pull/9186)) - [murrant](https://github.com/murrant)
* Fixed devices unpolled check ([#9199](https://github.com/librenms/librenms/pull/9199)) - [murrant](https://github.com/murrant)
* Fixed anonymous bind ([#9195](https://github.com/librenms/librenms/pull/9195)) - [murrant](https://github.com/murrant)
* Fixed auth user level not updated ([#9190](https://github.com/librenms/librenms/pull/9190)) - [murrant](https://github.com/murrant)
* Fixed error in logout ([#9189](https://github.com/librenms/librenms/pull/9189)) - [murrant](https://github.com/murrant)
* Fixed up ldap-authorizer, create non-existent users ([#9192](https://github.com/librenms/librenms/pull/9192)) - [murrant](https://github.com/murrant)
* Use Laravel authentication ([#8702](https://github.com/librenms/librenms/pull/8702)) - [murrant](https://github.com/murrant)
* Fixed extra large login images ([#9183](https://github.com/librenms/librenms/pull/9183)) - [murrant](https://github.com/murrant)
* WebGUI Oxidized - Add author+message + refresh button ([#9163](https://github.com/librenms/librenms/pull/9163)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added BGP Peer Descriptions ([#9165](https://github.com/librenms/librenms/pull/9165)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Oxidized Device List: Link to config and refreshDevice Btn ([#9129](https://github.com/librenms/librenms/pull/9129)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fixed PeeringDB module ([#9158](https://github.com/librenms/librenms/pull/9158)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Added support for Rancid GIT ([#9036](https://github.com/librenms/librenms/pull/9036)) - [dupondje](https://github.com/dupondje)
* Fixed web installer to allow users to change db creds if we can't connect ([#9126](https://github.com/librenms/librenms/pull/9126)) - [laf](https://github.com/laf)

#### Documentation
* Doc about third-party installation supports created by the community ([#9274](https://github.com/librenms/librenms/pull/9274)) - [crazy-max](https://github.com/crazy-max)
* Update Applications.MD ([#9280](https://github.com/librenms/librenms/pull/9280)) - [CFUJoshWeepie](https://github.com/CFUJoshWeepie)
* Added .env for subdirectory doc ([#9285](https://github.com/librenms/librenms/pull/9285)) - [murrant](https://github.com/murrant)
* Missing {{ }} around $value in some examples ([#9272](https://github.com/librenms/librenms/pull/9272)) - [Atroskelis](https://github.com/Atroskelis)
* Altered EXIM Download instruction ([#9241](https://github.com/librenms/librenms/pull/9241)) - [cppmonkey](https://github.com/cppmonkey)
* Add universe repo for Ubuntu 18 install docs ([#9238](https://github.com/librenms/librenms/pull/9238)) - [therealshibe](https://github.com/therealshibe)
* Fixed FAQ link and added delayed alerts FAQ ([#9239](https://github.com/librenms/librenms/pull/9239)) - [vowywowy](https://github.com/vowywowy)
* Added Alliedware Plus syslog config docs ([#9220](https://github.com/librenms/librenms/pull/9220)) - [mattie47](https://github.com/mattie47)
* Fixed format for snmpd configuration step ([#9203](https://github.com/librenms/librenms/pull/9203)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Updated example for new alerting engine ([#9193](https://github.com/librenms/librenms/pull/9193)) - [pmusolino-rms](https://github.com/pmusolino-rms)
* Added note to cron file advising not to disable daily.sh ([#9164](https://github.com/librenms/librenms/pull/9164)) - [murrant](https://github.com/murrant)
* Added FAQ on where to update database credentials if they change ([#9127](https://github.com/librenms/librenms/pull/9127)) - [laf](https://github.com/laf)
* Added info for using macros as values ([#9156](https://github.com/librenms/librenms/pull/9156)) - [vitalisator](https://github.com/vitalisator)

#### Feature
* Updated file permissions check ([#9218](https://github.com/librenms/librenms/pull/9218)) - [murrant](https://github.com/murrant)

#### Api
* Remove api rate limits ([#9211](https://github.com/librenms/librenms/pull/9211)) - [laf](https://github.com/laf)
* Fixed API auth issues ([#9185](https://github.com/librenms/librenms/pull/9185)) - [murrant](https://github.com/murrant)
* Use Laravel authentication ([#8702](https://github.com/librenms/librenms/pull/8702)) - [murrant](https://github.com/murrant)

#### Security
* Sanitize data in dashboard add/edit/delete ([#9171](https://github.com/librenms/librenms/pull/9171)) - [murrant](https://github.com/murrant)

###1.43
*(2018-08-30)*

A big thank you to the following 29 contributors this last month:

  - murrant (41)
  - laf (15)
  - TheGreatDoc (9)
  - PipoCanaja (4)
  - VanillaNinjaD (3)
  - VVelox (2)
  - zombah (2)
  - DreadnaughtSec (2)
  - metavrs (2)
  - Evil2000 (2)
  - dsgagi (2)
  - gs-kamnas (1)
  - cppmonkey (1)
  - bonzai86 (1)
  - dupondje (1)
  - Landrash (1)
  - bfarmerjr (1)
  - theherodied (1)
  - willhseitz (1)
  - eastmane (1)
  - MHammett (1)
  - jepke (1)
  - odvolk (1)
  - nickhilliard (1)
  - InsaneSplash (1)
  - tomarch (1)
  - crcro (1)
  - Notre1 (1)
  - LaZyDK (1)

#### Bug
* Fixed url to graphs from date selector ([#9109](https://github.com/librenms/librenms/pull/9109)) - [laf](https://github.com/laf)
* Fixed slack options not showing in the webui ([#9107](https://github.com/librenms/librenms/pull/9107)) - [laf](https://github.com/laf)
* Set ip to null when a device is renamed ([#9112](https://github.com/librenms/librenms/pull/9112)) - [murrant](https://github.com/murrant)
* SNMP v3 auth is no longer checked for case sensitivity + push pass v3 creds to front of queue ([#9102](https://github.com/librenms/librenms/pull/9102)) - [laf](https://github.com/laf)
* Fixed alert notes in templates ([#9093](https://github.com/librenms/librenms/pull/9093)) - [murrant](https://github.com/murrant)
* Fixed sorting on PeeringDB AS Selection table ([#9096](https://github.com/librenms/librenms/pull/9096)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fixed IN db queries ([#9077](https://github.com/librenms/librenms/pull/9077)) - [murrant](https://github.com/murrant)
* Fixed port_id is null in ospf poller ([#9078](https://github.com/librenms/librenms/pull/9078)) - [murrant](https://github.com/murrant)
* Fixed Device->Eventlog to show rows/pages and Syslog hostname filter ([#9060](https://github.com/librenms/librenms/pull/9060)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fixed ldap fetching user_id as string ([#9067](https://github.com/librenms/librenms/pull/9067)) - [murrant](https://github.com/murrant)
* Fixed port customers display ([#9048](https://github.com/librenms/librenms/pull/9048)) - [murrant](https://github.com/murrant)
* Fixed OSPF duplicate DB entries ([#9051](https://github.com/librenms/librenms/pull/9051)) - [murrant](https://github.com/murrant)
* Fixed dbFacile null parameters ([#9031](https://github.com/librenms/librenms/pull/9031)) - [murrant](https://github.com/murrant)
* Added a check for a failed dns query in get_astext() ([#9020](https://github.com/librenms/librenms/pull/9020)) - [murrant](https://github.com/murrant)
* Fixed invalid json in test data ([#9015](https://github.com/librenms/librenms/pull/9015)) - [murrant](https://github.com/murrant)
* Reverted parse_mode in Telegram transport ([#9000](https://github.com/librenms/librenms/pull/9000)) - [laf](https://github.com/laf)
* Linux sensors - check if value is valid before use discovery_sensor ([#8956](https://github.com/librenms/librenms/pull/8956)) - [tomarch](https://github.com/tomarch)
* Remove non-existent PowerNet-MIB OIDs. ([#9005](https://github.com/librenms/librenms/pull/9005)) - [murrant](https://github.com/murrant)
* Fixed incorrect heartbeat for ping rrds in rrdstep.php script ([#9004](https://github.com/librenms/librenms/pull/9004)) - [willhseitz](https://github.com/willhseitz)
* Fixed the display of sysNames within the edit device permissions ([#8986](https://github.com/librenms/librenms/pull/8986)) - [InsaneSplash](https://github.com/InsaneSplash)
* Correct config template for API transport ([#8991](https://github.com/librenms/librenms/pull/8991)) - [gs-kamnas](https://github.com/gs-kamnas)
* Fixed Eventlog search ([#8981](https://github.com/librenms/librenms/pull/8981)) - [TheGreatDoc](https://github.com/TheGreatDoc)

#### Webui
* Fixed url to graphs from date selector ([#9109](https://github.com/librenms/librenms/pull/9109)) - [laf](https://github.com/laf)
* Added dynamic graphs with RrdGraphJS by oetiker ([#9087](https://github.com/librenms/librenms/pull/9087)) - [bonzai86](https://github.com/bonzai86)
* Fixed PeeringDB AS Selection table ([#9096](https://github.com/librenms/librenms/pull/9096)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fixed Device->Eventlog to show rows/pages and Syslog hostname filter ([#9060](https://github.com/librenms/librenms/pull/9060)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fixed port customers display ([#9048](https://github.com/librenms/librenms/pull/9048)) - [murrant](https://github.com/murrant)
* Convert old templates title as well as body ([#9034](https://github.com/librenms/librenms/pull/9034)) - [laf](https://github.com/laf)
* Use rrdtool_escape() for sensors instead of manually padding text ([#9029](https://github.com/librenms/librenms/pull/9029)) - [nickhilliard](https://github.com/nickhilliard)
* Added hiding of disabled ports in graph, device overview and device ports view. ([#9017](https://github.com/librenms/librenms/pull/9017)) - [Evil2000](https://github.com/Evil2000)
* Update Alerts widget - Also sort by timestamp, after sorting/grouping by severity. ([#8977](https://github.com/librenms/librenms/pull/8977)) - [dsgagi](https://github.com/dsgagi)
* Display MAX rrd value in Service Graphs ([#9001](https://github.com/librenms/librenms/pull/9001)) - [PipoCanaja](https://github.com/PipoCanaja)
* Updated Rockstor os and logo svgs ([#9002](https://github.com/librenms/librenms/pull/9002)) - [crcro](https://github.com/crcro)
* Fixed the display of sysNames within the edit device permissions ([#8986](https://github.com/librenms/librenms/pull/8986)) - [InsaneSplash](https://github.com/InsaneSplash)
* 'Disabled' and 'Down' state for devices/links rendered on NetworkMap ([#8926](https://github.com/librenms/librenms/pull/8926)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix Eventlog search ([#8981](https://github.com/librenms/librenms/pull/8981)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Line up ping graph legend ([#8955](https://github.com/librenms/librenms/pull/8955)) - [murrant](https://github.com/murrant)

#### Alerting
* Fixed slack options not showing in the webui ([#9107](https://github.com/librenms/librenms/pull/9107)) - [laf](https://github.com/laf)
* Converted PagerDuty to new transport format ([#9092](https://github.com/librenms/librenms/pull/9092)) - [laf](https://github.com/laf)
* Fixed alert notes for templates ([#9093](https://github.com/librenms/librenms/pull/9093)) - [murrant](https://github.com/murrant)
* New transport modules (Kayako Classic, SMSFeedback) ([#9027](https://github.com/librenms/librenms/pull/9027)) - [odvolk](https://github.com/odvolk)
* Correct config template for API transport ([#8991](https://github.com/librenms/librenms/pull/8991)) - [gs-kamnas](https://github.com/gs-kamnas)

#### Documentation
* Standardized install method for RaspberryPi App ([#9014](https://github.com/librenms/librenms/pull/9014)) - [cppmonkey](https://github.com/cppmonkey)
* SNMP v3 auth is no longer checked for case sensitivity + push pass v3 creds to front of queue ([#9102](https://github.com/librenms/librenms/pull/9102)) - [laf](https://github.com/laf)
* Added installation docs for Ubuntu 18.04 ([#8630](https://github.com/librenms/librenms/pull/8630)) - [bfarmerjr](https://github.com/bfarmerjr)
* Added/Updated collectd information ([#9089](https://github.com/librenms/librenms/pull/9089)) - [theherodied](https://github.com/theherodied)
* Fixed Debian Stretch rrdcached Base_Path ([#8966](https://github.com/librenms/librenms/pull/8966)) - [MHammett](https://github.com/MHammett)
* Extended the templates documentation. ([#9055](https://github.com/librenms/librenms/pull/9055)) - [Evil2000](https://github.com/Evil2000)
* Update validate to check for mysqlnd ([#9043](https://github.com/librenms/librenms/pull/9043)) - [murrant](https://github.com/murrant)
* Snmp configuration docs for vCenter 6.x and ESXi ([#9022](https://github.com/librenms/librenms/pull/9022)) - [DreadnaughtSec](https://github.com/DreadnaughtSec)
* Update ReadMe (postfix) ([#9019](https://github.com/librenms/librenms/pull/9019)) - [DreadnaughtSec](https://github.com/DreadnaughtSec)
* Correct smokeping integration example ([#8997](https://github.com/librenms/librenms/pull/8997)) - [eastmane](https://github.com/eastmane)
* Update Distributed-Poller.md to include daily.sh use ([#8988](https://github.com/librenms/librenms/pull/8988)) - [jepke](https://github.com/jepke)
* Update Fast-Ping-Check.md to include config options ([#8987](https://github.com/librenms/librenms/pull/8987)) - [murrant](https://github.com/murrant)
* Added another hardware setup ([#8983](https://github.com/librenms/librenms/pull/8983)) - [LaZyDK](https://github.com/LaZyDK)

#### Api
* SNMP v3 auth is no longer checked for case sensitivity + push pass v3 creds to front of queue ([#9102](https://github.com/librenms/librenms/pull/9102)) - [laf](https://github.com/laf)
* Additional parameters for list_alerts and list_devices API calls ([#9040](https://github.com/librenms/librenms/pull/9040)) - [dsgagi](https://github.com/dsgagi)

#### Feature
* Added dynamic graphs with RrdGraphJS by oetiker ([#9087](https://github.com/librenms/librenms/pull/9087)) - [bonzai86](https://github.com/bonzai86)
* Added cli options debug and bill_id to poll-billing.php ([#9042](https://github.com/librenms/librenms/pull/9042)) - [murrant](https://github.com/murrant)
* Display user id for auth_test.php -l ([#9066](https://github.com/librenms/librenms/pull/9066)) - [murrant](https://github.com/murrant)
* Convert zfs over to use json_app_get ([#8573](https://github.com/librenms/librenms/pull/8573)) - [VVelox](https://github.com/VVelox)
* Add Juniper Junos syslog notification code ([#9006](https://github.com/librenms/librenms/pull/9006)) - [zombah](https://github.com/zombah)
* Improved SNMPTrap handling ([#8898](https://github.com/librenms/librenms/pull/8898)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Log an event for each Service Status change ([#8968](https://github.com/librenms/librenms/pull/8968)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Device
* Added fanspeed for EdgeSwitch ([#9013](https://github.com/librenms/librenms/pull/9013)) - [dupondje](https://github.com/dupondje)
* Small sysDescr_regex update for improved hiveos-wireless OS Detecttion ([#9046](https://github.com/librenms/librenms/pull/9046)) - [Notre1](https://github.com/Notre1)
* Added basic Support for Arris D5 EdgeQAM ([#9083](https://github.com/librenms/librenms/pull/9083)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Added basic Support for Allworx VoIP Systems ([#9057](https://github.com/librenms/librenms/pull/9057)) - [VanillaNinjaD](https://github.com/VanillaNinjaD)
* Added basic support for IBM i ([#9030](https://github.com/librenms/librenms/pull/9030)) - [VanillaNinjaD](https://github.com/VanillaNinjaD)
* Improved SmartAX OS support. Added CPU & Temperature for each card ([#9023](https://github.com/librenms/librenms/pull/9023)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Cleaned up Trango Apex Lynx OS code and added wireless sensors ([#9026](https://github.com/librenms/librenms/pull/9026)) - [metavrs](https://github.com/metavrs)
* Added support for older Airmux-400 ([#9024](https://github.com/librenms/librenms/pull/9024)) - [VanillaNinjaD](https://github.com/VanillaNinjaD)
* Added support for Huawei iBMC ([#9011](https://github.com/librenms/librenms/pull/9011)) - [PipoCanaja](https://github.com/PipoCanaja)
* Initial detection for SIAE Microelettronica ALFOplus2 microwave radio device ([#8953](https://github.com/librenms/librenms/pull/8953)) - [metavrs](https://github.com/metavrs)
* Update sonus-sbc detection ([#8978](https://github.com/librenms/librenms/pull/8978)) - [murrant](https://github.com/murrant)

#### Enhancement
* Isolate poller and discovery modules ([#9074](https://github.com/librenms/librenms/pull/9074)) - [murrant](https://github.com/murrant)
* Check for incorrect heartbeats in rrdtstep.php script ([#9041](https://github.com/librenms/librenms/pull/9041)) - [murrant](https://github.com/murrant)

#### Security
* Fix xss in deluser ([#9079](https://github.com/librenms/librenms/pull/9079)) - [murrant](https://github.com/murrant)

#### Breaking Changes
* Fixed slack options not showing in the WebUI. This will cause a loss of options ([#9107](https://github.com/librenms/librenms/pull/9107)) - [laf](https://github.com/laf) 

##1.42
*(2018-08-02)*

A big thank you to the following 25 contributors this last month:
  - murrant (20)
  - laf (11)
  - PipoCanaja (5)
  - pheinrichs (5)
  - mattie47 (3)
  - dsgagi (3)
  - TheGreatDoc (3)
  - Rosiak (3)
  - InsaneSplash (3)
  - siegsters (2)
  - MHammett (2)
  - vivia11 (2)
  - crazy-max (2)
  - marcuspink (1)
  - RyanMorash (1)
  - daryl-peterson (1)
  - barajus (1)
  - angryp (1)
  - normic (1)
  - costasd (1)
  - empi89 (1)
  - TheMysteriousX (1)
  - komeda-shinji (1)
  - jozefrebjak (1)
  - asteen-nexcess (1)

#### Device
* VRF support on VRP devices (huawei) ([#8879](https://github.com/librenms/librenms/pull/8879)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add Citrix Netscaler HA sensors and alerts ([#8800](https://github.com/librenms/librenms/pull/8800)) - [siegsters](https://github.com/siegsters)
* Arris CMTS - C4/C4c remamed and Added C3 support ([#8883](https://github.com/librenms/librenms/pull/8883)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Added discovery of serverscheck flooding sensor ([#8923](https://github.com/librenms/librenms/pull/8923)) - [marcuspink](https://github.com/marcuspink)
* Improve DCN Device Support ([#8878](https://github.com/librenms/librenms/pull/8878)) - [Rosiak](https://github.com/Rosiak)
* Added detection for CoreOS devices ([#8899](https://github.com/librenms/librenms/pull/8899)) - [crazy-max](https://github.com/crazy-max)
* Feature : Use IOS POE code for IOSXE ([#8853](https://github.com/librenms/librenms/pull/8853)) - [PipoCanaja](https://github.com/PipoCanaja)
* Improved support for cambium cmm and added cmm4 ([#8737](https://github.com/librenms/librenms/pull/8737)) - [pheinrichs](https://github.com/pheinrichs)
* APC Environmental Manager Support ([#8872](https://github.com/librenms/librenms/pull/8872)) - [Rosiak](https://github.com/Rosiak)
* Allied Telesis Wireless Support ([#8692](https://github.com/librenms/librenms/pull/8692)) - [mattie47](https://github.com/mattie47)
* Removed nobulk option from routeros ([#8846](https://github.com/librenms/librenms/pull/8846)) - [laf](https://github.com/laf)

#### Alerting
* Added new Alert Transports Mapping ([#8660](https://github.com/librenms/librenms/pull/8660)) - [vivia11](https://github.com/vivia11)
* Fixed alert rules that use columns in value ([#8925](https://github.com/librenms/librenms/pull/8925)) - [laf](https://github.com/laf)
* Refactor alert templates to use Laravel Blade templating engine ([#8803](https://github.com/librenms/librenms/pull/8803)) - [laf](https://github.com/laf)

#### Webui
* Corrected display of minigraph when using sysName as hostname ([#8842](https://github.com/librenms/librenms/pull/8842)) - [InsaneSplash](https://github.com/InsaneSplash)
* Custom error page ([#8911](https://github.com/librenms/librenms/pull/8911)) - [murrant](https://github.com/murrant)
* Stop allowing search text to be tagged for select2 ([#8915](https://github.com/librenms/librenms/pull/8915)) - [laf](https://github.com/laf)
* Fix plugin loading ([#8917](https://github.com/librenms/librenms/pull/8917)) - [murrant](https://github.com/murrant)
* Fix errors in vars.inc.php ([#8913](https://github.com/librenms/librenms/pull/8913)) - [murrant](https://github.com/murrant)
* GUI: Fix broken navigation on VRFs Page ([#8889](https://github.com/librenms/librenms/pull/8889)) - [PipoCanaja](https://github.com/PipoCanaja)
* Sort alerts by severity on the Alerts widget ([#8895](https://github.com/librenms/librenms/pull/8895)) - [dsgagi](https://github.com/dsgagi)
* Fix processor usage on edit page ([#8887](https://github.com/librenms/librenms/pull/8887)) - [murrant](https://github.com/murrant)
* Fix up depth column in poller UI ([#8884](https://github.com/librenms/librenms/pull/8884)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Collectd graph bug fix ([#8855](https://github.com/librenms/librenms/pull/8855)) - [komeda-shinji](https://github.com/komeda-shinji)

#### Feature
* Allow ping checks to be ran separately from polling ([#8821](https://github.com/librenms/librenms/pull/8821)) - [murrant](https://github.com/murrant)
* Poll service check only if the associated device is available ([#8757](https://github.com/librenms/librenms/pull/8757)) - [dsgagi](https://github.com/dsgagi)
* Support for HTML tags in TELEGRAM transport ([#8929](https://github.com/librenms/librenms/pull/8929)) - [jozefrebjak](https://github.com/jozefrebjak)
* Asterisk Application support ([#8914](https://github.com/librenms/librenms/pull/8914)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Added new Alert Transports Mapping ([#8660](https://github.com/librenms/librenms/pull/8660)) - [vivia11](https://github.com/vivia11)
* Improved Logging and Debugging ([#8870](https://github.com/librenms/librenms/pull/8870)) - [murrant](https://github.com/murrant)
* Support specifying submodules in poller and discovery for debug ([#8896](https://github.com/librenms/librenms/pull/8896)) - [murrant](https://github.com/murrant)
* Add sysDescr and hardware for oxidized overrides ([#8885](https://github.com/librenms/librenms/pull/8885)) - [empi89](https://github.com/empi89)
* Added support for auto purging deleted ports ([#8861](https://github.com/librenms/librenms/pull/8861)) - [laf](https://github.com/laf)

#### Documentation
* Adds missing hostname parameter ([#8961](https://github.com/librenms/librenms/pull/8961)) - [normic](https://github.com/normic)
* Update RRDCached.md ([#8959](https://github.com/librenms/librenms/pull/8959)) - [MHammett](https://github.com/MHammett)
* Update Smokeping.md ([#8860](https://github.com/librenms/librenms/pull/8860)) - [mattie47](https://github.com/mattie47)

#### Bug
* Fixed whitespace bug in ceraos temperature sensor ([#8948](https://github.com/librenms/librenms/pull/8948)) - [laf](https://github.com/laf)
* Remove testing data ([#8945](https://github.com/librenms/librenms/pull/8945)) - [pheinrichs](https://github.com/pheinrichs)
* Fix typo in clickatell ([#8937](https://github.com/librenms/librenms/pull/8937)) - [pheinrichs](https://github.com/pheinrichs)
* Match interface counter64 OIDs with unsigned bigint ([#8940](https://github.com/librenms/librenms/pull/8940)) - [siegsters](https://github.com/siegsters)
* Fix foldersize() recursion ([#8930](https://github.com/librenms/librenms/pull/8930)) - [murrant](https://github.com/murrant)
* Fixed alert rules that use columns in value ([#8925](https://github.com/librenms/librenms/pull/8925)) - [laf](https://github.com/laf)
* Stop allowing search text to be tagged for select2 ([#8915](https://github.com/librenms/librenms/pull/8915)) - [laf](https://github.com/laf)
* Fix plugin loading ([#8917](https://github.com/librenms/librenms/pull/8917)) - [murrant](https://github.com/murrant)
* Fix arista limits ([#8916](https://github.com/librenms/librenms/pull/8916)) - [murrant](https://github.com/murrant)
* Fix errors in vars.inc.php ([#8913](https://github.com/librenms/librenms/pull/8913)) - [murrant](https://github.com/murrant)
* Fix can_ping_device() logic ([#8906](https://github.com/librenms/librenms/pull/8906)) - [murrant](https://github.com/murrant)
* GUI: Fix broken navigation on VRFs Page ([#8889](https://github.com/librenms/librenms/pull/8889)) - [PipoCanaja](https://github.com/PipoCanaja)
* Don't exit(5) without new devices discovered ([#8893](https://github.com/librenms/librenms/pull/8893)) - [costasd](https://github.com/costasd)
* Fix processor usage on edit page ([#8887](https://github.com/librenms/librenms/pull/8887)) - [murrant](https://github.com/murrant)
* Fix api list devices query for normal users ([#8881](https://github.com/librenms/librenms/pull/8881)) - [murrant](https://github.com/murrant)
* Fix up depth column in poller UI ([#8884](https://github.com/librenms/librenms/pull/8884)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Collectd graph bug fix ([#8855](https://github.com/librenms/librenms/pull/8855)) - [komeda-shinji](https://github.com/komeda-shinji)

#### Api
* Add sysDescr and hardware for oxidized overrides ([#8885](https://github.com/librenms/librenms/pull/8885)) - [empi89](https://github.com/empi89)
* Fix api list devices query for normal users ([#8881](https://github.com/librenms/librenms/pull/8881)) - [murrant](https://github.com/murrant)

##1.41
*(2018-06-30)*

A big thank you to the following 23 contributors this last month:
  - murrant (8)
  - laf (5)
  - vivia11 (3)
  - InsaneSplash (3)
  - isarandi (2)
  - asteen-nexcess (2)
  - f0o (1)
  - salt-lick (1)
  - utelisysadmin (1)
  - TheGreatDoc (1)
  - KlaasT (1)
  - angryp (1)
  - xpatux (1)
  - cron410 (1)
  - skoef (1)
  - centralscrutiniser (1)
  - pheinrichs (1)
  - theherodied (1)
  - mattie47 (1)
  - wiad (1)
  - hanserasmus (1)
  - TomEvin (1)
  - serhatcan (1)

#### Feature
* New python service ([#8455](https://github.com/librenms/librenms/pull/8455)) - [murrant](https://github.com/murrant)
* Add option to ignore blockdevice regex ([#8797](https://github.com/librenms/librenms/pull/8797)) - [f0o](https://github.com/f0o)
* Ability to enable debug output with wrappers ([#8830](https://github.com/librenms/librenms/pull/8830)) - [murrant](https://github.com/murrant)

#### Bug
* Only list polling as overdue when it is 20% over the rrd_step value. ([#8848](https://github.com/librenms/librenms/pull/8848)) - [murrant](https://github.com/murrant)
* Sanitize oxidized geshi html output ([#8847](https://github.com/librenms/librenms/pull/8847)) - [InsaneSplash](https://github.com/InsaneSplash)
* Checking for 'none' as the only device relationship ([#8837](https://github.com/librenms/librenms/pull/8837)) - [salt-lick](https://github.com/salt-lick)
* Fixed incorrect divisor for #8746 ([#8836](https://github.com/librenms/librenms/pull/8836)) - [angryp](https://github.com/angryp)
* HPE ILO power fix ([#8822](https://github.com/librenms/librenms/pull/8822)) - [TomEvin](https://github.com/TomEvin)
* Change VRFs page to group together by RD and vrf_name ([#8799](https://github.com/librenms/librenms/pull/8799)) - [vivia11](https://github.com/vivia11)
* Change max load from 5000 to 50000 ([#8769](https://github.com/librenms/librenms/pull/8769)) - [isarandi](https://github.com/isarandi)
* Fixed missing var declaration for description search in FDB tables ([#8802](https://github.com/librenms/librenms/pull/8802)) - [wiad](https://github.com/wiad)
* Fix storing metrics for SMART ([#8807](https://github.com/librenms/librenms/pull/8807)) - [isarandi](https://github.com/isarandi)

#### Webui
* Sanitize oxidized geshi html output ([#8847](https://github.com/librenms/librenms/pull/8847)) - [InsaneSplash](https://github.com/InsaneSplash)
* Allow the hostname to resolve to the sysName, ie Dynamic DNS ([#8810](https://github.com/librenms/librenms/pull/8810)) - [InsaneSplash](https://github.com/InsaneSplash)
* Disable autocomplete on SNMPv3 settings ([#8833](https://github.com/librenms/librenms/pull/8833)) - [KlaasT](https://github.com/KlaasT)
* Change _POST to vars to get params by URL on FDB search. ([#8838](https://github.com/librenms/librenms/pull/8838)) - [xpatux](https://github.com/xpatux)
* Change VRFs page to group together by RD and vrf_name ([#8799](https://github.com/librenms/librenms/pull/8799)) - [vivia11](https://github.com/vivia11)

#### Alerting
* Revised Pushover title and severity level ([#8844](https://github.com/librenms/librenms/pull/8844)) - [InsaneSplash](https://github.com/InsaneSplash)
* Add Discord transport ([#8748](https://github.com/librenms/librenms/pull/8748)) - [theherodied](https://github.com/theherodied)

#### Device
* Added support for graphing pf related stats for pfsense devices ([#8643](https://github.com/librenms/librenms/pull/8643)) - [utelisysadmin](https://github.com/utelisysadmin)
* Added detection for Netscaler SD-WAN devices ([#8825](https://github.com/librenms/librenms/pull/8825)) - [laf](https://github.com/laf)
* Added SNR Sensor to CMTS Arris ([#8840](https://github.com/librenms/librenms/pull/8840)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* HPE ILO power fix ([#8822](https://github.com/librenms/librenms/pull/8822)) - [TomEvin](https://github.com/TomEvin)
* Improved support for DNOS/FTOS M-Series ([#8749](https://github.com/librenms/librenms/pull/8749)) - [skoef](https://github.com/skoef)
* Adding sensors to omnitron iconverter ([#8806](https://github.com/librenms/librenms/pull/8806)) - [vivia11](https://github.com/vivia11)
* EfficientIP SOLIDserver Detections ([#8773](https://github.com/librenms/librenms/pull/8773)) - [centralscrutiniser](https://github.com/centralscrutiniser)
* Clean up old cambium code / migrate more data to wireless sensors ([#8725](https://github.com/librenms/librenms/pull/8725)) - [pheinrichs](https://github.com/pheinrichs)
* Added Juniper SRX Branch Session Graphing ([#8815](https://github.com/librenms/librenms/pull/8815)) - [asteen-nexcess](https://github.com/asteen-nexcess)

#### Security
* Disable autocomplete on SNMPv3 settings ([#8833](https://github.com/librenms/librenms/pull/8833)) - [KlaasT](https://github.com/KlaasT)

#### Documentation
* Update Applications.md ([#8813](https://github.com/librenms/librenms/pull/8813)) - [mattie47](https://github.com/mattie47)
* Add if label docs to os settings ([#8779](https://github.com/librenms/librenms/pull/8779)) - [murrant](https://github.com/murrant)
* Added OpsGenie integration ([#8786](https://github.com/librenms/librenms/pull/8786)) - [serhatcan](https://github.com/serhatcan)

##1.40
*(2018-05-30)*

A big thank you to the following 24 contributors this last month:
  - murrant (27)
  - laf (15)
  - PipoCanaja (7)
  - theherodied (3)
  - angryp (3)
  - centralscrutiniser (3)
  - vivia11 (2)
  - hanserasmus (2)
  - pheinrichs (2)
  - aldemira (2)
  - mattie47 (2)
  - Rosiak (2)
  - Cormoran96 (2)
  - salt-lick (1)
  - nwautomator (1)
  - remyj38 (1)
  - robje (1)
  - rbax82 (1)
  - TheGreatDoc (1)
  - oranenj (1)
  - k-y (1)
  - gs-kamnas (1)
  - DR3EVR8u8c (1)
  - githubuserx (1)

#### Webui
* Allow Submenus in Plugin Menu by removing the scrollable-menu … ([#8762](https://github.com/librenms/librenms/pull/8762)) - [PipoCanaja](https://github.com/PipoCanaja)
* Change alert rule triggered icon from X to ! ([#8760](https://github.com/librenms/librenms/pull/8760)) - [murrant](https://github.com/murrant)
* Fix the all ports search for fSpeed -> ifSpeed ([#8759](https://github.com/librenms/librenms/pull/8759)) - [laf](https://github.com/laf)
* Fix oxidized configuration fetch for empty group ([#8754](https://github.com/librenms/librenms/pull/8754)) - [oranenj](https://github.com/oranenj)
* Handle database exceptions properly ([#8720](https://github.com/librenms/librenms/pull/8720)) - [murrant](https://github.com/murrant)
* Fix some issues with globals ([#8709](https://github.com/librenms/librenms/pull/8709)) - [murrant](https://github.com/murrant)
* PoE graphs ([#8705](https://github.com/librenms/librenms/pull/8705)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix device inventory page ([#8698](https://github.com/librenms/librenms/pull/8698)) - [murrant](https://github.com/murrant)
* Fix two factor auth ([#8697](https://github.com/librenms/librenms/pull/8697)) - [murrant](https://github.com/murrant)
* Version librenms.js to break cache ([#8696](https://github.com/librenms/librenms/pull/8696)) - [murrant](https://github.com/murrant)
* Don't require mysql 5.5 or newer. ([#8695](https://github.com/librenms/librenms/pull/8695)) - [murrant](https://github.com/murrant)
* Fix missing vlan name on port vlan page ([#8684](https://github.com/librenms/librenms/pull/8684)) - [murrant](https://github.com/murrant)
* Add wireless sensors to about and stats.librenms.org ([#8676](https://github.com/librenms/librenms/pull/8676)) - [murrant](https://github.com/murrant)
* Update for adding responsive ([#8652](https://github.com/librenms/librenms/pull/8652)) - [Cormoran96](https://github.com/Cormoran96)
* Created a page to show all known VMs ([#8640](https://github.com/librenms/librenms/pull/8640)) - [aldemira](https://github.com/aldemira)

#### Security
* Don't leak passwords deep linking to a graph and logging in on Apache httpd ([#8761](https://github.com/librenms/librenms/pull/8761)) - [murrant](https://github.com/murrant)

#### Api
* Map LibreNMS OS' to known Oxidized models ([#8758](https://github.com/librenms/librenms/pull/8758)) - [laf](https://github.com/laf)
* System endpoint, more health graphs ([#8730](https://github.com/librenms/librenms/pull/8730)) - [pheinrichs](https://github.com/pheinrichs)

#### Device
* Use CISCO-VRF-MIB for vrfs on non MPLS cisco devices ([#8756](https://github.com/librenms/librenms/pull/8756)) - [vivia11](https://github.com/vivia11)
* Added SFP sensor discovery for Procurve devices ([#8746](https://github.com/librenms/librenms/pull/8746)) - [angryp](https://github.com/angryp)
* Adding VRP support for FDB table using HUAWEI-L2MAM-MIB ([#8719](https://github.com/librenms/librenms/pull/8719)) - [PipoCanaja](https://github.com/PipoCanaja)
* Update Barracuda Spam Firewall product name with the current name (Barracuda Email Security Gateway) ([#8682](https://github.com/librenms/librenms/pull/8682)) - [salt-lick](https://github.com/salt-lick)
* Update juniper junos chassis definitions ([#8678](https://github.com/librenms/librenms/pull/8678)) - [nwautomator](https://github.com/nwautomator)
* Add Open Access Netspire OS support ([#8674](https://github.com/librenms/librenms/pull/8674)) - [vivia11](https://github.com/vivia11)
* Rename awplus sensor state_name ([#8671](https://github.com/librenms/librenms/pull/8671)) - [mattie47](https://github.com/mattie47)
* Blue Coat device updates ([#8664](https://github.com/librenms/librenms/pull/8664)) - [centralscrutiniser](https://github.com/centralscrutiniser)
* Fix Netguardian OS Polling Variable ([#8655](https://github.com/librenms/librenms/pull/8655)) - [Rosiak](https://github.com/Rosiak)
* Fortinet FortiWeb Basic Support ([#8654](https://github.com/librenms/librenms/pull/8654)) - [Rosiak](https://github.com/Rosiak)
* Update Discovery on Ceragon wireless ([#8647](https://github.com/librenms/librenms/pull/8647)) - [pheinrichs](https://github.com/pheinrichs)
* Added sysObjectID for Canon LBP printer series ([#8644](https://github.com/librenms/librenms/pull/8644)) - [githubuserx](https://github.com/githubuserx)
* Change Dell iDrac from Server to Appliance ([#8642](https://github.com/librenms/librenms/pull/8642)) - [theherodied](https://github.com/theherodied)

#### Bug
* Fix oxidized configuration fetch for empty group ([#8754](https://github.com/librenms/librenms/pull/8754)) - [oranenj](https://github.com/oranenj)
* Bug - Apc - Humidity sensor negative value filtering ([#8743](https://github.com/librenms/librenms/pull/8743)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix Mimosa PtP frequency and power ([#8741](https://github.com/librenms/librenms/pull/8741)) - [murrant](https://github.com/murrant)
* Fix hostname lookup for distributed poller on some platforms ([#8727](https://github.com/librenms/librenms/pull/8727)) - [murrant](https://github.com/murrant)
* Fix install ([#8724](https://github.com/librenms/librenms/pull/8724)) - [murrant](https://github.com/murrant)
* Fix some issues with globals ([#8709](https://github.com/librenms/librenms/pull/8709)) - [murrant](https://github.com/murrant)
* Fix device inventory page ([#8698](https://github.com/librenms/librenms/pull/8698)) - [murrant](https://github.com/murrant)
* Fix two factor auth ([#8697](https://github.com/librenms/librenms/pull/8697)) - [murrant](https://github.com/murrant)
* Version librenms.js to break cache ([#8696](https://github.com/librenms/librenms/pull/8696)) - [murrant](https://github.com/murrant)
* Don't require mysql 5.5 or newer. ([#8695](https://github.com/librenms/librenms/pull/8695)) - [murrant](https://github.com/murrant)
* Ignore plugin hook calling errors ([#8693](https://github.com/librenms/librenms/pull/8693)) - [murrant](https://github.com/murrant)
* Stop Eloquent from loading another DB conn for poller/discovery ([#8691](https://github.com/librenms/librenms/pull/8691)) - [laf](https://github.com/laf)
* Fixed AD when no email address is returned ([#8690](https://github.com/librenms/librenms/pull/8690)) - [laf](https://github.com/laf)
* Fix .env migration unescaped $ in passwords ([#8688](https://github.com/librenms/librenms/pull/8688)) - [murrant](https://github.com/murrant)
* Remove buffering for legacy api ([#8687](https://github.com/librenms/librenms/pull/8687)) - [laf](https://github.com/laf)
* Fix missing vlan name on port vlan page ([#8684](https://github.com/librenms/librenms/pull/8684)) - [murrant](https://github.com/murrant)
* Test plugin menu method should be static. ([#8681](https://github.com/librenms/librenms/pull/8681)) - [murrant](https://github.com/murrant)
* Fix alerting with dynamic contacts for rules made with the new builder ([#8677](https://github.com/librenms/librenms/pull/8677)) - [murrant](https://github.com/murrant)
* Restore OpenBSD version/features/hardware ([#8673](https://github.com/librenms/librenms/pull/8673)) - [murrant](https://github.com/murrant)
* Rename awplus sensor state_name ([#8671](https://github.com/librenms/librenms/pull/8671)) - [mattie47](https://github.com/mattie47)
* MySQL strict mode maintenance scheduling fix. ([#8659](https://github.com/librenms/librenms/pull/8659)) - [angryp](https://github.com/angryp)
* Fix for get_alert API method. ([#8658](https://github.com/librenms/librenms/pull/8658)) - [angryp](https://github.com/angryp)
* Ignore bad DNS config ([#8656](https://github.com/librenms/librenms/pull/8656)) - [murrant](https://github.com/murrant)
* Fix Netguardian OS Polling Variable ([#8655](https://github.com/librenms/librenms/pull/8655)) - [Rosiak](https://github.com/Rosiak)
* Do not fail validation when IPv6 is disabled by unloading the module. ([#8648](https://github.com/librenms/librenms/pull/8648)) - [gs-kamnas](https://github.com/gs-kamnas)
* Add good_if to boss ([#8641](https://github.com/librenms/librenms/pull/8641)) - [murrant](https://github.com/murrant)

#### Documentation
* Update Weathermap.md ([#8747](https://github.com/librenms/librenms/pull/8747)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Updated installation instructions ([#8733](https://github.com/librenms/librenms/pull/8733)) - [hanserasmus](https://github.com/hanserasmus)
* Fixed a few formatting problems on applications.md ([#8716](https://github.com/librenms/librenms/pull/8716)) - [hanserasmus](https://github.com/hanserasmus)
* Updated MySQL entry. ([#8708](https://github.com/librenms/librenms/pull/8708)) - [theherodied](https://github.com/theherodied)
* Update Test-Units.md ([#8707](https://github.com/librenms/librenms/pull/8707)) - [mattie47](https://github.com/mattie47)
* Reordering metrics list on Health-Information doc ([#8653](https://github.com/librenms/librenms/pull/8653)) - [k-y](https://github.com/k-y)
* Update to add acl on list of necessary package ([#8639](https://github.com/librenms/librenms/pull/8639)) - [Cormoran96](https://github.com/Cormoran96)

#### Enhancement
* Added SFP sensor discovery for Procurve devices ([#8746](https://github.com/librenms/librenms/pull/8746)) - [angryp](https://github.com/angryp)

#### Alerting
* Updated variables available for templates ([#8713](https://github.com/librenms/librenms/pull/8713)) - [laf](https://github.com/laf)
* Updated parse_email() to use email_user config value ([#8706](https://github.com/librenms/librenms/pull/8706)) - [laf](https://github.com/laf)
* Added feature to filter delayed alerts on widget ([#8704](https://github.com/librenms/librenms/pull/8704)) - [DR3EVR8u8c](https://github.com/DR3EVR8u8c)
* Fix alerting with dynamic contacts for rules made with the new builder ([#8677](https://github.com/librenms/librenms/pull/8677)) - [murrant](https://github.com/murrant)

#### Feature
* PoE graphs ([#8705](https://github.com/librenms/librenms/pull/8705)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added feature to filter delayed alerts on widget ([#8704](https://github.com/librenms/librenms/pull/8704)) - [DR3EVR8u8c](https://github.com/DR3EVR8u8c)
* Also include adsl stats for vdsl ports ([#8670](https://github.com/librenms/librenms/pull/8670)) - [robje](https://github.com/robje)
* Plugins in the Port page ([#8665](https://github.com/librenms/librenms/pull/8665)) - [PipoCanaja](https://github.com/PipoCanaja)
* Created a page to show all known VMs ([#8640](https://github.com/librenms/librenms/pull/8640)) - [aldemira](https://github.com/aldemira)


## 1.39
*(2018-04-27)*

#### Features
* Added random entropy support for applications ([#8555](https://github.com/librenms/librenms/issues/8555))
* Added Prometheus PushGateway metric support ([#8437](https://github.com/librenms/librenms/issues/8437))
* Added random entropy monitoring ([#8555](https://github.com/librenms/librenms/pull/8555))
* Added support for syslog Oxidized notification for Hauwei VRP devices ([#8562](https://github.com/librenms/librenms/pull/8562))

#### Bugfixes
* Fixed ePMP gps state ([#8575](https://github.com/librenms/librenms/issues/8575))
* Use email_user variable as from name for emails ([#8550](https://github.com/librenms/librenms/pull/8550))
* Apply divisor / multiplier to high / low values ([#8427](https://github.com/librenms/librenms/pull/8427)) 

#### Documentation
* Remove clause of CLA ([#8596](https://github.com/librenms/librenms/issues/8596))
* Updated Oxidized.md to show use of arrays ([#8547](https://github.com/librenms/librenms/issues/8547))

#### Refactoring
* Updated the oxidized script ([#8572](https://github.com/librenms/librenms/issues/8572))
* Allow _perc and _current columns to be string for alert rules ([#8532](https://github.com/librenms/librenms/issues/8532))
* Restrict storage discovery walks to OS ([#8528](https://github.com/librenms/librenms/pull/8528))
* Refactor Database and Config init ([#8527](https://github.com/librenms/librenms/pull/8527))
* Use snmp.version config to allow users to set versions available ([#8512](https://github.com/librenms/librenms/pull/8512))
* Exclude modules from json test data when empty ([#8533](https://github.com/librenms/librenms/pull/8533))
* Update include files for disco/poller to be dynamic ([#8597](https://github.com/librenms/librenms/pull/8597))
* Refactored the oxidized notify script ([#8572](https://github.com/librenms/librenms/pull/8572))
#### Devices
* BDCOM use alternative MIBS ([#8610](https://github.com/librenms/librenms/issues/8610))
* Fixed polling for state sensor on ict-pdu ([#8558](https://github.com/librenms/librenms/issues/8558))
* Added detection for AeroHive AP130 Wireless. ([#8543](https://github.com/librenms/librenms/issues/8543))
* Added WTI-MPC OS and mib ([#8492](https://github.com/librenms/librenms/issues/8492))
* Added Dell Rack PDU support ([#8498](https://github.com/librenms/librenms/pull/8498))
* Added AeroHive Wireless + sensors support ([#8500](https://github.com/librenms/librenms/pull/8500)) ([#8520](https://github.com/librenms/librenms/pull/8520))
* Added detection for 2N Helio IP devices ([#8490](https://github.com/librenms/librenms/pull/8490))
* Added additional wireless stats + CPU detection for Ruckus Wireless devices ([#8503](https://github.com/librenms/librenms/pull/8503)) ([#8529](https://github.com/librenms/librenms/pull/8529))
* Added Last Mile CTM support ([#8478](https://github.com/librenms/librenms/pull/8478))
* Added basic Ubiquiti LTU airFiber support ([#8521](https://github.com/librenms/librenms/pull/8521))
* Added Tegile support (OS and Storage) ([#8464](https://github.com/librenms/librenms/pull/8464))
* Improved DCN support ([#8531](https://github.com/librenms/librenms/pull/8531))
* Updated Buffalo OS detection ([#8536](https://github.com/librenms/librenms/pull/8536))
* Added additional wireless support for PMP devices ([#8537](https://github.com/librenms/librenms/pull/8537))
* Added support for Dahua NVR ([#8542](https://github.com/librenms/librenms/pull/8542)) ([#8570](https://github.com/librenms/librenms/pull/8570))
* Updated sensors and added more support for FiberHome OS ([#8549](https://github.com/librenms/librenms/pull/8549)) ([#8569](https://github.com/librenms/librenms/pull/8569))
* Updated Cyberpower sensors and os support ([#8551](https://github.com/librenms/librenms/pull/8551))
* Added Mempools and Process support for ArubaOS ([#8548](https://github.com/librenms/librenms/pull/8548))
* Added additional sensor support for FiberHome devices ([#8561](https://github.com/librenms/librenms/pull/8561))
* Added detection for IBM DPI devices ([#8541](https://github.com/librenms/librenms/pull/8541))
* Avaya Avaya ERS and VSP to Extreme VOSS and BOSS ([#8469](https://github.com/librenms/librenms/pull/8469))
* Added detection for HWG Poseidon 4002 ([#8593](https://github.com/librenms/librenms/pull/8593))
* Rewrite state sensors to walk entire table for TIMOS ([#8588](https://github.com/librenms/librenms/pull/8588))
* Added detection for Corero CMS devices ([#8574](https://github.com/librenms/librenms/pull/8574))
* Added detection for Red Lion Sixnet devices ([#8576](https://github.com/librenms/librenms/pull/8576))

#### WebUI
* Update Buffalo logo to new style and svg ([#8539](https://github.com/librenms/librenms/issues/8539))
* Use toastr for alert templates operational messages ([#8499]https://github.com/librenms/librenms/pull/8499) 
* Improved FDB search page ([#8251](https://github.com/librenms/librenms/pull/8251))
* Updated Mikrotik logo ([#8584](https://github.com/librenms/librenms/pull/8584))
* Updated FiberHome logo ([#8601](https://github.com/librenms/librenms/pull/8601)) 
* Updated TPLink logo ([#8613](https://github.com/librenms/librenms/pull/8613))
* Added a 3rd graph to unbound application ([#8616](https://github.com/librenms/librenms/pull/8616))

#### Alerting
* Improved VictorOps messages ([#8502](https://github.com/librenms/librenms/pull/8502))
* Added support for ack notes + alert notes ([#8433](https://github.com/librenms/librenms/pull/8433))

#### API
* Added lat/lng to device(s) API calls ([#8515](https://github.com/librenms/librenms/pull/8515))
* Added support for returning images as base64 ([#8535](https://github.com/librenms/librenms/pull/8535))
---

## 1.38
*(2018-03-29)*

#### Bugfixes
* Restored config items in sql queries ([#8443](https://github.com/librenms/librenms/issues/8443))
* Fixed sysname -> sysName for device dependancy display ([#8343](https://github.com/librenms/librenms/issues/8343))
* MySQL query in alert_rules.json ([#8300](https://github.com/librenms/librenms/issues/8300))
* Change the column type from varchar(255) to TEXT for nagios-plugins perfdata ([#8322](https://github.com/librenms/librenms/issues/8322))
* Fixed etherlike graphs ([#8358](https://github.com/librenms/librenms/issues/8358))
* Fixed HP Proliant state sensors ([#8315](https://github.com/librenms/librenms/issues/8315))
* Change rootPort DB data type ([#8404](https://github.com/librenms/librenms/issues/8404))
* Fixed and improved toner discovery ([#8428](https://github.com/librenms/librenms/issues/8428))

#### Documentation
* RRDCached-Security clearify security ([#8302](https://github.com/librenms/librenms/issues/8302))
* Improved pushover documentation ([#8330](https://github.com/librenms/librenms/issues/8330))
* Updated Graylog and Syslog documentation ([#8396](https://github.com/librenms/librenms/issues/8396))

#### Devices
* Added detection for Cisco SPA devices ([#8446](https://github.com/librenms/librenms/issues/8446))
* Updated moxa-etherdevice hardware/software/version detection
* Added hardware and firmware detection for Hanwha Techwin devices ([#8468](https://github.com/librenms/librenms/issues/8468))
* Added additional detection for Calix B6 ([#8445](https://github.com/librenms/librenms/issues/8445))
* Added DEVELOP Support ([#8153](https://github.com/librenms/librenms/issues/8153))
* Added more Dell iDrac state sensors ([#8254](https://github.com/librenms/librenms/issues/8254))
* Improve Allied Telesis Chassis product support ([#8139](https://github.com/librenms/librenms/issues/8139))
* Added OID for Cisco FTD model 9300 Ref. #8212 ([#8285](https://github.com/librenms/librenms/issues/8285))
* Added more WirelessSensors and processor/mempools support for EWC ([#8294](https://github.com/librenms/librenms/issues/8294))
* Improved Lenovo CNOS Discovery/Logo ([#8332](https://github.com/librenms/librenms/issues/8332))
* Added detection of Axis Network Speaker ([#8336](https://github.com/librenms/librenms/issues/8336))
* Added Nimble storage volume utilization ([#8405](https://github.com/librenms/librenms/issues/8405))
* Added Radwin OS Wireless Sensors ([#8372](https://github.com/librenms/librenms/issues/8372))
* Added contact,relay,outlet discovery for AOS-EMU2 ([#8317](https://github.com/librenms/librenms/issues/8317)) 
* Added support for Moxa EDS-P510A-8PoE ([#8384](https://github.com/librenms/librenms/issues/8384))
* Added detection for Ciena 8700 (SAOS 8) ([#8442](https://github.com/librenms/librenms/issues/8442))
* Added better detection for HiveOS devices ([#8277](https://github.com/librenms/librenms/issues/8277))
* Added detection for Raritan KVM and EMX devices ([#8279](https://github.com/librenms/librenms/issues/8279))
* Improved detection for dlink and dlinkap devices ([#8447](https://github.com/librenms/librenms/issues/8447))
* Added serial number detection to xerox ([#8439](https://github.com/librenms/librenms/issues/8439))
* Added Phybridge OS support ([#8456](https://github.com/librenms/librenms/issues/8456))
* Improved Sentry4 sensor discovery ([#8407](https://github.com/librenms/librenms/issues/8407))
* Added Cambium pmp ap client count discovery ([#8487](https://github.com/librenms/librenms/issues/8487))

#### WebUI
* Added override default device grouping radius ([#8485](https://github.com/librenms/librenms/issues/8485))
* Added auto width and height dimensions parameters for generic image widget ([#8314](https://github.com/librenms/librenms/issues/8314))
* Removed unused JS causing issues with typeahead ([#8307](https://github.com/librenms/librenms/issues/8307))
* S.M.A.R.T visualization improvements ([#8310](https://github.com/librenms/librenms/issues/8310))
* Added Oxidized reload node list button ([#8375](https://github.com/librenms/librenms/issues/8375))
* Added docs link to about page ([#8397](https://github.com/librenms/librenms/issues/8397))
* Active Directory: filter disabled users, allow nested group membership for AD auth ([#8222](https://github.com/librenms/librenms/issues/8222))
* Added borderless fullscreen geographical map, with just the nodes. ([#8337](https://github.com/librenms/librenms/issues/8337))
* Improved LDAP filters for getUserlist and getUserlevel ([#8398](https://github.com/librenms/librenms/issues/8398))
* Added LDAP debug output ([#8434](https://github.com/librenms/librenms/issues/8434))
* LDAP fall back to slow search if memberof is missing ([#8491](https://github.com/librenms/librenms/issues/8491))
* Allow full search on devices page ([#8364](https://github.com/librenms/librenms/issues/8364))

#### Alerting
* Added a irc_alert_short option to only send the alert title ([#8312](https://github.com/librenms/librenms/issues/8312)) 
* Added Philips Hue as transport ([#8320](https://github.com/librenms/librenms/issues/8320)) 
* Added two html alert template examples ([#8360](https://github.com/librenms/librenms/issues/8360))
* Added alert_id to alerts for templates ([#8368](https://github.com/librenms/librenms/issues/8368))
* Added new alert rule builder UI ([#8293](https://github.com/librenms/librenms/issues/8293))
* Added support for disabling recovery notifications ([#8430](https://github.com/librenms/librenms/issues/8430))
* Change the default alert delay to 1m ([#8462](https://github.com/librenms/librenms/issues/8462))

#### Documentation
* Use single quotes in example SNMP community per issue 8342 ([#8348](https://github.com/librenms/librenms/issues/8348))
* Update SNMP-Configuration-Examples.md to add VMWare example ([#8381](https://github.com/librenms/librenms/issues/8381))

#### Misc
* Improve LDAP filter in the getUserlist() function ([#8253](https://github.com/librenms/librenms/issues/8253))
* Created check_graylog.inc.php service file ([#8334](https://github.com/librenms/librenms/issues/8334))
* Created check_haproxy.inc.php service file ([#8412](https://github.com/librenms/librenms/issues/8412))
* Module tests update: per module output, only store modules data that are run ([#8355](https://github.com/librenms/librenms/issues/8355))
* Updated APC PowerNet-MIB ([#8377](https://github.com/librenms/librenms/issues/8377))
* Print mysql errors in debug output ([#8414](https://github.com/librenms/librenms/issues/8414))
* Use custom phpcs ruleset for LibreNMS ([#8418](https://github.com/librenms/librenms/issues/8418))
* Updating the mibs file from vmware ([#8388](https://github.com/librenms/librenms/issues/8388))
* Small improvement to per port polling (speed boost) ([#8431](https://github.com/librenms/librenms/issues/8431))
* Fixed validation sql for primary keys and first columns ([#8453](https://github.com/librenms/librenms/issues/8453))
* Added ifAlias to tag influxdb port data ([#8461](https://github.com/librenms/librenms/issues/8461))
* Bumping CISCO-PRODUCTS-MIB to newest release ([#8483](https://github.com/librenms/librenms/issues/8483))
* Updated and improved manage_bills.php script ([#8467](https://github.com/librenms/librenms/issues/8467))

---

## 1.37
*(2018-02-25)*

#### Features
* Added ironware syslog notify code ([#8268](https://github.com/librenms/librenms/issues/8268))
* Copy all email alerts to default contact ([#8178](https://github.com/librenms/librenms/issues/8178))
* Added GateOne Link ([#8189](https://github.com/librenms/librenms/pull/8189))
* Added ironware syslog notify code ([#8268](https://github.com/librenms/librenms/pull/8268))

#### Bugfixes
* Switch detached head for daily updates ([#8194](https://github.com/librenms/librenms/issues/8194))
* Fixed arp-tables updates ([#8180](https://github.com/librenms/librenms/issues/8180))
* Fixed FusionSwitch portname for sensors ([#8188](https://github.com/librenms/librenms/issues/8188))
* Fixed cbgp peers data on some devices ([#8208](https://github.com/librenms/librenms/pull/8208))
* Awplus fix cpu ([#8215](https://github.com/librenms/librenms/pull/8215))
* Fixed linkify to work with with ip addresses ([#8226](https://github.com/librenms/librenms/pull/8226))
* Changed e-mail validation TLD max length to 18 ([#8236](https://github.com/librenms/librenms/pull/8236))
* Fixed the HTML Purifier config so allowed values work ([#8239](https://github.com/librenms/librenms/pull/8239))
* Fixed issues with new config init ([#8242](https://github.com/librenms/librenms/pull/8242)) 
* Mitigate Cisco IOS 15 Stack State Issue ([#8256](https://github.com/librenms/librenms/pull/8256))
* Fix bgp peers discovery cleanup ([#8263](https://github.com/librenms/librenms/pull/8263))
* Fixed Dell N1548 CPU graphs ([#8280](https://github.com/librenms/librenms/pull/8280))

#### Documentation
* Changed err-msg to message for API docs ([#8182](https://github.com/librenms/librenms/issues/8182))
* Fix for syslog-ng config in the docs ([#8160](https://github.com/librenms/librenms/issues/8160))

#### Refactoring
* Removed Unused Billing PDF reports ([#8235](https://github.com/librenms/librenms/issues/8235))
* Update awplus to new processor discovery ([#8204](https://github.com/librenms/librenms/issues/8204))
* Major Processors rewrite ([#8066](https://github.com/librenms/librenms/issues/8066))
* Remove composer deps from the repository ([#6920](https://github.com/librenms/librenms/pull/6920))
* Share code between all mysql based authorizers ([#8174](https://github.com/librenms/librenms/pull/8174))
* Use more secure password hashes ([#8213](https://github.com/librenms/librenms/pull/8213))
* Adding invalid hostnames is no longer fatal ([#8233](https://github.com/librenms/librenms/pull/8233))
* Correct unix socket handling to match poller-wrapper ([#8214](https://github.com/librenms/librenms/pull/8214))
* Move config loading into the Config class ([#8100](https://github.com/librenms/librenms/pull/8100))
* Generic snmp_translate() function ([#8156](https://github.com/librenms/librenms/pull/8156))

#### Devices
* Added support for ScreenOS arp tables ([#8273](https://github.com/librenms/librenms/issues/8273))
* Added support for OpenBSD PF states ([#8211](https://github.com/librenms/librenms/issues/8211))
* Added support for F5 GTM ([#8161](https://github.com/librenms/librenms/issues/8161))
* Added support for Airos CPU, GPS, and Temp ([#8203](https://github.com/librenms/librenms/pull/8203)) 
* Updated support for XOS X480 and X650 desc and tests ([#8206](https://github.com/librenms/librenms/pull/8206))
* Updated Cisco Processor support ([#8216](https://github.com/librenms/librenms/pull/8216))
* Added OPNsense support ([#8199](https://github.com/librenms/librenms/pull/8199))
* Updated StoneOs support ([#8155](https://github.com/librenms/librenms/pull/8155))
* Added Allied Telesis Environmental Monitoring ([#8140](https://github.com/librenms/librenms/pull/8140))
* Update support for Brocade G620 description ([#8250](https://github.com/librenms/librenms/pull/8250)) 
* Added CeraOS wireless sensors ([#8167](https://github.com/librenms/librenms/pull/8167))

#### WebUI
* Updated jquery_lazyload ([#8287](https://github.com/librenms/librenms/issues/8287))
* Updated datetime lib ([#8288](https://github.com/librenms/librenms/pull/8288))
* Allow sorting by toner and searching ([#8247](https://github.com/librenms/librenms/issues/8247))
* Updated vis js and moment js ([#8240](https://github.com/librenms/librenms/issues/8240))
* Added sysNames to pulldowns and the main page ([#8137](https://github.com/librenms/librenms/issues/8137))
* Adding invalid hostnames is no longer fatal. ([#8233](https://github.com/librenms/librenms/issues/8233))
* Allow administrators to edit devices sysName ([#8149](https://github.com/librenms/librenms/pull/8149))
* Allow frameborder attribute in notes widget ([#8219](https://github.com/librenms/librenms/pull/8219))
* Updated vis js and moment js ([#8240](https://github.com/librenms/librenms/pull/8240))

#### API
* Added Billing Graphs & Data ([#8245](https://github.com/librenms/librenms/issues/8245))
* Added History to Billing API Functions ([#8209](https://github.com/librenms/librenms/pull/8209))

#### Alerting
* Case insensitive alert rule searching ([#8231](https://github.com/librenms/librenms/pull/8231))
* Updated email subject for failed updates to include hostname ([#8258](https://github.com/librenms/librenms/pull/8258))
* Set Content-type header in the API transport if the text to be sent is valid JSON ([#8282](https://github.com/librenms/librenms/pull/8282))
---

## 1.36
*(2018-01-30)*

#### Features
* Added powerdns dnsdist application ([#7987](https://github.com/librenms/librenms/issues/7987))
* Added ZFS support for FreeBSD ([#7938](https://github.com/librenms/librenms/pull/7938))
* Added snmp_getnext_multio() function ([#8052](https://github.com/librenms/librenms/pull/8052))
* Clear OS defs cache on poller/discovery debug ([#8061](https://github.com/librenms/librenms/pull/8061))
* Script to rename mibs to proper names ([#8068](https://github.com/librenms/librenms/pull/8068))

#### Bugfixes
* daily.sh Updated to resolve release version issues ([#8172](https://github.com/librenms/librenms/issues/8172))
* Make consistent with other authorisers ([#8151](https://github.com/librenms/librenms/issues/8151))
* Pushover message fix to enable templates ([#8118](https://github.com/librenms/librenms/issues/8118))
* Bgp-peers junos empty afi-safi names ([#8083](https://github.com/librenms/librenms/issues/8083))
* Fixed the default fping config + changed variable name ([#8060](https://github.com/librenms/librenms/issues/8060))
* Override -H for check_load service. Fixed backslash escaping. ([#8020](https://github.com/librenms/librenms/issues/8020))
* Fix can't find CPQHOST-MIB ([#8024](https://github.com/librenms/librenms/issues/8024))
* Bgp-peers add missing os junos safi values ([#8018](https://github.com/librenms/librenms/issues/8018))
* Cisco-vrf discovery for device os junos ([#8009](https://github.com/librenms/librenms/issues/8009))
* Stop stripping html tages from snmp creds ([#7951](https://github.com/librenms/librenms/pull/7951))
* Fixed bugs in bgp-peers poller ([#7976](https://github.com/librenms/librenms/pull/7976))
* Fix mark all unread notifications ([#7999](https://github.com/librenms/librenms/pull/7999))
* Fix deletion of services + select query ([#8030](https://github.com/librenms/librenms/pull/8030))
* Moved sysDescr snmp call to snmp_get_multi_oid() in core poller ([#8062](https://github.com/librenms/librenms/pull/8062))
* Fixed bad queries for top device widget ([#8105](https://github.com/librenms/librenms/pull/8105))
* Stop including disabled ports in traffic overview ([#8107](https://github.com/librenms/librenms/pull/8107))
* Specify table name for list_devices API call ([#8132](https://github.com/librenms/librenms/pull/8132))
* Fixed Palo Alto HA alert rule ([#8138](https://github.com/librenms/librenms/pull/8138))
* Fixed arp table duplicates ([#8147](https://github.com/librenms/librenms/pull/8147))
* Fixed normal user access to performance tab ([#8150](https://github.com/librenms/librenms/pull/8150))
* Changed is_file() to rrdtool_check_rrd_exists() ([#8152](https://github.com/librenms/librenms/pull/8152))

#### Documentation
* Emphasize snmpsim is required for most tests ([#8059](https://github.com/librenms/librenms/issues/8059))
* Doc api fixes ([#7950](https://github.com/librenms/librenms/issues/7950))
* Added ports purge to cleanup options ([#7970](https://github.com/librenms/librenms/pull/7970))

#### Refactoring
* Moved routeros sensors to yaml + fixed divisors ([#7946](https://github.com/librenms/librenms/issues/7946))
* Remove all old style snmp calls ([#7924](https://github.com/librenms/librenms/issues/7924))
* Fail2ban update and removal of FW checking ([#7936](https://github.com/librenms/librenms/pull/7936))
* Refactor freeradius appplication metrics ([#8002](https://github.com/librenms/librenms/pull/8002))
* Move ignore_storage call to discover_storage ([#7973](https://github.com/librenms/librenms/pull/7973))
* Updated poller/discovery to use numeric sysObjectID ([#7922](https://github.com/librenms/librenms/pull/7922))
* Move installs to php53 branch for php version < 5.6.4 ([#8042](https://github.com/librenms/librenms/pull/8042))
* Rename cisco-vrf to vrf and enabled junos ([#8033](https://github.com/librenms/librenms/pull/8033))
* Updated BGP Peers ([#7972](https://github.com/librenms/librenms/pull/7972))

#### Devices
* Added Support for cambium PTP 300, 500, 600 and 800 ([#7998](https://github.com/librenms/librenms/issues/7998))
* Added support for ArbOS ([#8055](https://github.com/librenms/librenms/issues/8055))
* Added processor and mempools for EdgeCore switch ([#7850](https://github.com/librenms/librenms/issues/7850))
* Updated Cisco ACS Detection ([#8013](https://github.com/librenms/librenms/issues/8013))
* Added Rittal IT Chiller / Carel pCOweb card ([#7826](https://github.com/librenms/librenms/issues/7826))
* Added cyberpower RMCARD 202 Support ([#7964](https://github.com/librenms/librenms/issues/7964))
* Added additional detection for CyberPower devices ([#7931](https://github.com/librenms/librenms/issues/7931))
* Added further detection for Sophos UTM devices ([#7953](https://github.com/librenms/librenms/issues/7953))
* Added Cambium cnPilot Support ([#7898](https://github.com/librenms/librenms/issues/7898))
* Added more sensor support for RouterOS (Mikrotik) + Test data ([#7930](https://github.com/librenms/librenms/issues/7930))
* Added Checkpoint Gaia Sensor ([#8088](https://github.com/librenms/librenms/issues/8088))
* Added Dell iDRAC Global System Sensor ([#8012](https://github.com/librenms/librenms/pull/8012))
* Added CheckPoint SecurePlatform support ([#8000](https://github.com/librenms/librenms/pull/8000))
* Fixed AirOS version and Hardware ([#8034](https://github.com/librenms/librenms/pull/8034))
* Fixed UniFi AP hardware and firmware revision ([#8005](https://github.com/librenms/librenms/pull/8005))
* Added Hillstone StoneOS detection ([#7982](https://github.com/librenms/librenms/pull/7982))
* Added Allied Telesis CPU support ([#8111](https://github.com/librenms/librenms/pull/8111))
* Updated legacy Allied Telesis hardware support ([#8071](https://github.com/librenms/librenms/pull/8071))
* Added EER and Water Florw Rate for Rittal Chillers ([#8104](https://github.com/librenms/librenms/pull/8104))
* Added memory and temp polling for Dlink ([#8076](https://github.com/librenms/librenms/pull/8076))
* Added Hardware/Version/Serial support for Synology ([#8087](https://github.com/librenms/librenms/pull/8087))
* Updated hardware for certain awplus devices ([#8123](https://github.com/librenms/librenms/pull/8123))
* Updated FortiManager support ([#8102](https://github.com/librenms/librenms/pull/8102))
* Added suspended state for panos ([#8125](https://github.com/librenms/librenms/pull/8125))

#### WebUI
* Added back devices sort by status ([#8103](https://github.com/librenms/librenms/issues/8103))
* Small graylog cleanup ([#8057](https://github.com/librenms/librenms/issues/8057))
* Small eventlog cleanup ([#8056](https://github.com/librenms/librenms/issues/8056))
* Added OpenWRT icon ([#8054](https://github.com/librenms/librenms/issues/8054))
* Devices header cleanup ([#8039](https://github.com/librenms/librenms/issues/8039))
* Added validation for Oxidized API URL config ([#7978](https://github.com/librenms/librenms/issues/7978))
* Wireless pages refresh ([#7836](https://github.com/librenms/librenms/issues/7836))
* HPMSM added CPU,Memory to Overview ([#7949](https://github.com/librenms/librenms/issues/7949))
* Added Single server details widget ([#7923](https://github.com/librenms/librenms/issues/7923))
* Improved Services page ([#7628](https://github.com/librenms/librenms/pull/7628))
* Improved Alerts page ([#7765](https://github.com/librenms/librenms/pull/7628))
* Improved Eventlog page ([#7793](https://github.com/librenms/librenms/pull/7793))
* Improved Syslog page ([#7796](https://github.com/librenms/librenms/pull/7796))
* Improved Devices page ([#7809](https://github.com/librenms/librenms/pull/7809))
* Improved Ports page ([#7827](https://github.com/librenms/librenms/pull/7827))
* Improved Graylog page ([#7832](https://github.com/librenms/librenms/pull/7832))
* Improved Health / Sensors page ([#7834](https://github.com/librenms/librenms/pull/7834))
* Improved Wireless page ([#7836](https://github.com/librenms/librenms/pull/7836))
* Improved Applications page ([#7994](https://github.com/librenms/librenms/pull/7994))
* Added Oxidized config validator ([#7983](https://github.com/librenms/librenms/pull/7983))
* Added settings validations ([#8037](https://github.com/librenms/librenms/pull/8037))

#### API
* Added add_service_for_host endpoint to API ([#8113](https://github.com/librenms/librenms/issues/8113))
* Added dependency info for devices/device api calls ([#8058](https://github.com/librenms/librenms/issues/8058))
* Added 404 fallback for bad / invalid calls ([#7952](https://github.com/librenms/librenms/pull/7952))
* Added routing and resources API calls ([#8017](https://github.com/librenms/librenms/pull/8017))

#### Alerting
* Updated Pushover to use templates ([#8118](https://github.com/librenms/librenms/pull/8118))

---

## 1.35
*(2017-12-20)*

#### Features
* Generic discovery and poller tests ([#7873](https://github.com/librenms/librenms/issues/7873))
* FreeRADIUS application monitoring ([#7818](https://github.com/librenms/librenms/issues/7818))
* Save application metrics to db for alerting ([#7828](https://github.com/librenms/librenms/issues/7828))
* Added Entity State polling ([#7625](https://github.com/librenms/librenms/issues/7625))
* Added manage_bills.php script to scripts directory ([#7633](https://github.com/librenms/librenms/issues/7633)) 
* Added Host dependencies support for alerting ([#7332](https://github.com/librenms/librenms/issues/7332))

#### Bugfixes
* Agent tries to insert processes data that is too long ([#7891](https://github.com/librenms/librenms/issues/7891))
* Remove faulty memcached code (not related to distributed polling) ([#7881](https://github.com/librenms/librenms/issues/7881))
* Re-added peeringdb back into daily.sh ([#7884](https://github.com/librenms/librenms/issues/7884))
* Don't work around bad implementations in snmpwalk_group ([#7876](https://github.com/librenms/librenms/issues/7876))
* Some graphs broke due to stacked graphs, remove that change ([#7848](https://github.com/librenms/librenms/issues/7848))
* Fixed UPS time remaining in Mikrotik RouterOs ([#7803](https://github.com/librenms/librenms/issues/7803))
* Fixed get_all_ports() for api not returning ports on admin tokens ([#7829](https://github.com/librenms/librenms/issues/7829))
* Validate ifHighSpeed is > 0 in selected port polling ([#7885](https://github.com/librenms/librenms/issues/7885))
* Added user level to getUser for LDAP authentication ([#7896](https://github.com/librenms/librenms/issues/7896)) 
* ipmi sensors sending the wrong tags to influxdb ([#7906](https://github.com/librenms/librenms/issues/7906))

#### Documentation
* Minor fix to device sensors doc ([#7874](https://github.com/librenms/librenms/issues/7874))
* Create device sensor help doc. ([#7868](https://github.com/librenms/librenms/issues/7868))
* Updated sensor state doc ([#7822](https://github.com/librenms/librenms/issues/7822))
* Build new Cleanup Options doc ([#7798](https://github.com/librenms/librenms/issues/7798))

#### Refactoring
* Stop sending emails to "NOC" for default_email target ([#7917](https://github.com/librenms/librenms/issues/7917))
* Refactor alert transports to classes ([#7844](https://github.com/librenms/librenms/issues/7844))
* Update all applications to store metrics ([#7853](https://github.com/librenms/librenms/issues/7853)) 

#### Devices
* Added state sensor support for HPE MSA devices ([#7808](https://github.com/librenms/librenms/issues/7808))
* Added temp and humidity sesors + serial for websensor (renamed from cometsystem-p85xx) ([#7854](https://github.com/librenms/librenms/issues/7854))
* Added Mikrotik POE sensors ([#7883](https://github.com/librenms/librenms/issues/7883))
* Added Mikrotik LLDP discovery ([#7901](https://github.com/librenms/librenms/issues/7901))
* Update wireless sensors for Ray and Alcoma devices ([#7820](https://github.com/librenms/librenms/issues/7820))
* Added support EdgeCore ECS4120-28T ([#7880](https://github.com/librenms/librenms/issues/7880))
* Added Junos dwdm interface sensor support ([#7714](https://github.com/librenms/librenms/issues/7714))
* Added detection for Cisco FTD devices ([#7887](https://github.com/librenms/librenms/issues/7887))

#### WebUI
* Timezone support for graylog ([#7799](https://github.com/librenms/librenms/issues/7799))
* Added support for stacked graphs ([#7725](https://github.com/librenms/librenms/issues/7725))
* Added ability to mark all notifications as read ([#7489](https://github.com/librenms/librenms/issues/7489))
* Disabled page refresh on Add services page ([#7804](https://github.com/librenms/librenms/issues/7804))
* Added diskusage to top devices widget ([#7903](https://github.com/librenms/librenms/issues/7903))

#### API
* Added Retrieve BGP sessions by ID ([#7825](https://github.com/librenms/librenms/issues/7825))
* Fixed disabling tokens ([#7833](https://github.com/librenms/librenms/issues/7833))
* Added support for wireless sensors ([#7846](https://github.com/librenms/librenms/issues/7846))
* Added API method to rename devices ([#7895](https://github.com/librenms/librenms/issues/7895)) 

#### Alerting
* Validate email addresses used in alerting ([#7830](https://github.com/librenms/librenms/issues/7836))
* Added generic alerting for state sensors ([#7812](https://github.com/librenms/librenms/issues/7812))

---

## 1.34
*(2017-11-26)*

#### Features
* Added additional sensors for ups-nut
* Track rrdtool time for poller ([#7706](https://github.com/librenms/librenms/issues/7706))
* Implement snmp_getnext() ([#7678](https://github.com/librenms/librenms/issues/7678))
* LDAP auth update: alerts, api, remember me ([#7335](https://github.com/librenms/librenms/issues/7335))
* Support a wider range of link speeds in network maps ([#7533](https://github.com/librenms/librenms/issues/7533))
* Allow snmpget in os discovery yaml ([#7587](https://github.com/librenms/librenms/issues/7587))
* Check cli timezone from the validation webpage ([#7648](https://github.com/librenms/librenms/issues/7648))
* Selected ports polling per OS ([#7674](https://github.com/librenms/librenms/issues/7674))
* Added purge-port scipt to allow deleting of ports from the CLI. ([#7528](https://github.com/librenms/librenms/issues/7528))
* Extra fping checks in validation ([#7651](https://github.com/librenms/librenms/pull/7651))
* Added support for setting php memory_limit in config.php ([#7704](https://github.com/librenms/librenms/pull/7704))
* Script to collect port polling data and compare full walk vs selective port polling ([#7626](https://github.com/librenms/librenms/pull/7626))
* Allow discovery to check if devices are down before skipping ([#7780](https://github.com/librenms/librenms/pull/7780))

#### Bugfixes
* Fixed precache data ([#7782](https://github.com/librenms/librenms/issues/7782))
* Authentication on CentOS6 ([#7771](https://github.com/librenms/librenms/issues/7771))
* Fixed empty group query for devices ([#7760](https://github.com/librenms/librenms/issues/7760))
* Notification read count doesn't decrement in menu bar ([#7750](https://github.com/librenms/librenms/issues/7750))
* Do not add invalid Cisco senors seen in IOS 15.6(3)M1 ([#7629](https://github.com/librenms/librenms/issues/7629))
* Revert to two gets for sysDescr and sysObjectID ([#7741](https://github.com/librenms/librenms/issues/7741))
* Fixed Brocade ironware processor precision ([#7730](https://github.com/librenms/librenms/issues/7730))
* Basic input validation for screen width and height ([#7713](https://github.com/librenms/librenms/issues/7713))
* Better sanity checks of fping options.
* Devices detected as ibmtl or generic ([#7618](https://github.com/librenms/librenms/issues/7618))
* Added primary key to perf_times to improve DELETE performance on replicas using ROW based replication ([#7493](https://github.com/librenms/librenms/issues/7493))
* Fail isPingable() if fping fails, take 2 ([#7585](https://github.com/librenms/librenms/issues/7585))
* Update ifIndex update on polling when ifIndex is not the port association mode ([#7574](https://github.com/librenms/librenms/issues/7574)) ([#7575](https://github.com/librenms/librenms/issues/7575))
* Fix Services container alignment ([#7583](https://github.com/librenms/librenms/pull/7583))
* Availability-map showed ping devices as warning ([#7592](https://github.com/librenms/librenms/pull/7592))
* discovery.php -h all stops working after ping only device ([#7593](https://github.com/librenms/librenms/pull/7593))
* Improve poller validation ([#7586](https://github.com/librenms/librenms/pull/7586))
* Only show the neighbour tab if there are neighbors to show ([#7591](https://github.com/librenms/librenms/pull/7591))
* Updated hpe iPdu to remove power and fix load values ([#7596](https://github.com/librenms/librenms/pull/7596))
* Function snmp_get delete quotes in snmp query ([#7467](https://github.com/librenms/librenms/pull/7467))
* Better sanity check of hostname when adding device. ([#7582](https://github.com/librenms/librenms/pull/7582))
* github-apply sometimes fails on PRs with lots of commits ([#7604](https://github.com/librenms/librenms/pull/7604))
* PING fails on servers that don't set PATH in cron ([#7603](https://github.com/librenms/librenms/pull/7603))
* Correct sensor ID when removing device. ([#7611](https://github.com/librenms/librenms/pull/7611))
* Fixed status services up/down inverse values ([#7657](https://github.com/librenms/librenms/pull/7657))
* Better validation on callback url for Pagerduty integration ([#7658](https://github.com/librenms/librenms/pull/7658))
* Fixed number comparisons in alerts and device groups ([#7695](https://github.com/librenms/librenms/pull/7695))
* Fallback to snmpgetnext if db is unavailable ([#7698](https://github.com/librenms/librenms/pull/7698))
* Errors with missing posix extension ([#7666](https://github.com/librenms/librenms/pull/7666))
* Fixed component status log ([#7723](https://github.com/librenms/librenms/pull/7723))
* Fixed bgpPeers_cbgp discovery for junos ([#7743](https://github.com/librenms/librenms/pull/7743))
* Fixed incorrect mail validation if ([#7755](https://github.com/librenms/librenms/pull/7755))
* Fixed discovery-wrapper.py discovery.nodes can sometimes be None when not master ([#7747](https://github.com/librenms/librenms/pull/7747))
* Fixed bgp-peers fails when ip parsing fails ([#7773](https://github.com/librenms/librenms/pull/7773))
* Fixed poller-wrapper.py keeps running when a poller takes too long ([#7722](https://github.com/librenms/librenms/pull/7722))
* Use correct entity columns for sensors ([#7775](https://github.com/librenms/librenms/pull/7775))
* Discovery os changes weren't properly reflected if os changed ([#7779](https://github.com/librenms/librenms/pull/7779))

#### Documentation
* Minor changes and fixes to Alert Rules ([#7789](https://github.com/librenms/librenms/issues/7789))
* Added Web UI rename device. ([#7769](https://github.com/librenms/librenms/issues/7769))
* SNMP config for Mac OSX ([#7767](https://github.com/librenms/librenms/issues/7767))
* Continue to reorganize docs ([#7762](https://github.com/librenms/librenms/issues/7762))
* Fix for rule mapping ([#7751](https://github.com/librenms/librenms/issues/7751))
* Added sudo guidelines for Proxmox ([#7739](https://github.com/librenms/librenms/issues/7739))
* Added sudo suggestion for SMART monitoring ([#7738](https://github.com/librenms/librenms/issues/7738))
* VM images recommended user update ([#7737](https://github.com/librenms/librenms/issues/7737))
* Install Docs Update for min PHP ver ([#7630](https://github.com/librenms/librenms/issues/7630))
* Added video link showing how to add ping only device ([#7711](https://github.com/librenms/librenms/issues/7711))
* Fix layout changes to dashboard. ([#7693](https://github.com/librenms/librenms/issues/7693))
* Create new docs on using dashboards ([#7688](https://github.com/librenms/librenms/issues/7688))
* Added Ping Only Device ([#7687](https://github.com/librenms/librenms/issues/7687))
* Added allow graphs without login ([#7675](https://github.com/librenms/librenms/issues/7675))
* Added device-troubleshooting page ([#7638](https://github.com/librenms/librenms/issues/7638))
* Added WebUI to Validate docs ([#7635](https://github.com/librenms/librenms/issues/7635))
* Minor spelling fix for device-troubleshooting ([#7689](https://github.com/librenms/librenms/issues/7689))
* Fix for VM images doc. ([#7740](https://github.com/librenms/librenms/issues/7740))
* Added syslog cleanup option ([#7581](https://github.com/librenms/librenms/issues/7581))
* How to add Weathermaps to Dashboards ([#7636](https://github.com/librenms/librenms/issues/7636))
* Added command to fetch shell script for DHCP Stats ([#7736](https://github.com/librenms/librenms/issues/7736))
* Added ports template to alert rules doc ([#7763](https://github.com/librenms/librenms/issues/7763)) 

#### Refactoring
* Better default temperature sensor limits ([#7754](https://github.com/librenms/librenms/issues/7754))
* Switch to using discovery-wrapper.py by default ([#7661](https://github.com/librenms/librenms/issues/7661))
* Rewrite a bit of the irc bot ([#7667](https://github.com/librenms/librenms/issues/7667))
* Added the Nvidia SM average as app_status ([#7671](https://github.com/librenms/librenms/issues/7671))
* Refactored authorizers to classes ([#7497](https://github.com/librenms/librenms/issues/7497))
* Only update sensor/bgp tables when values are changed ([#7707](https://github.com/librenms/librenms/issues/7707))

#### Devices
* Updated Checkpoint Gaia detection and added storage ([#7656](https://github.com/librenms/librenms/issues/7656))
* Added additional APC rPDU2 Sensors ([#7490](https://github.com/librenms/librenms/issues/7490))
* Initial detection for Arris Cable Modem devices ([#7677](https://github.com/librenms/librenms/issues/7677))
* Removed unused poller/discovery modules for Arista EOS ([#7709](https://github.com/librenms/librenms/issues/7709))
* Update hwg-ste and add hwg-ste-plus support ([#7610](https://github.com/librenms/librenms/issues/7610))
* Added state sensors for Palo Alto Networks firewall ([#7482](https://github.com/librenms/librenms/issues/7482))
* Added additional sensors for logmaster Os
* Added Temp and Humidity support for ServersCheck devices ([#7588](https://github.com/librenms/librenms/issues/7588))
* Added support for Avtech RoomAlert 32E/W and RoomAlert 11E ([#7614](https://github.com/librenms/librenms/issues/7614))
* Added support for Eltek enexus ([#7552](https://github.com/librenms/librenms/issues/7552))
* Added support for Stormshield devices ([#7646](https://github.com/librenms/librenms/issues/7646))
* Added support for Asentria SiteBoss ([#7655](https://github.com/librenms/librenms/issues/7655))
* Added humidity sensors for hwg-ste ([#7728](https://github.com/librenms/librenms/issues/7728))
* Addedd sensor support for UPS NUT devices ([#7622](https://github.com/librenms/librenms/issues/7622))

#### WebUI
* Don't disable the applications check boxes in settings if the discovery module is disabled. ([#7615](https://github.com/librenms/librenms/issues/7615))
* Added toner support in health metrics list ([#7595](https://github.com/librenms/librenms/issues/7595))
* Hide rediscover button for ping only devices ([#7594](https://github.com/librenms/librenms/issues/7594))
* Added Health/sensor view for specific device/ports/port ([#7684](https://github.com/librenms/librenms/issues/7684))
* Updated poll-log page to honour force_ip_to_sysname ([#7712](https://github.com/librenms/librenms/issues/7712))
* Expose pollers link to show total poll time for all devices per poller ([#7699](https://github.com/librenms/librenms/issues/7699))
* Updated sensors overview header to be consistent with others ([#7761](https://github.com/librenms/librenms/issues/7761))
* Allow deletion of dead poller nodes ([#7721](https://github.com/librenms/librenms/issues/7721))

#### API
* Added support for Oxidized asking for a single host ([#7705](https://github.com/librenms/librenms/issues/7705))
* Validate columns parameter against fields in table ([#7717](https://github.com/librenms/librenms/issues/7717))
* Stop list_logs skipping first row ([#7772](https://github.com/librenms/librenms/issues/7772))

#### Alerting
* Added in dell server sensors alert rules to the collection ([#7647](https://github.com/librenms/librenms/issues/7647))

---

## 1.33
*(2017-10-29)*

#### Features
* Support for up/down detection of ping only devices.
* Improve Device Neighbour WebUI ([#7487](https://github.com/librenms/librenms/issues/7487))
* Configurable 95th percentile ([#7442](https://github.com/librenms/librenms/issues/7442))
* Added AD support nested groups (resubmit #7175) ([#7259](https://github.com/librenms/librenms/pull/7259))
* Added configurable 95th percentile for graphs ([#7442](https://github.com/librenms/librenms/pull/7442))
* Added  sysname as filtering group for oxidized ([#7485](https://github.com/librenms/librenms/pull/7485))
* CDP matching incorrect ports ([#7491](https://github.com/librenms/librenms/pull/7491))
* Issue warning notification if php version is less than 5.6.4 ([#7418](https://github.com/librenms/librenms/pull/7418))
* Added Web validation support ([#7474](https://github.com/librenms/librenms/pull/7474))
* Support for up/down detection of ping only devices ([#7323](https://github.com/librenms/librenms/pull/7323))

#### Bugfixes
* rfc1628 state sensor translations ([#7416](https://github.com/librenms/librenms/pull/7416))
* snmpwalk_group tables not using entries ([#7427](https://github.com/librenms/librenms/pull/7427))
* Improve accuracy of is_valid_hostname() ([#7435](https://github.com/librenms/librenms/pull/7435))
* snmp_get_multi returns no data if the oid doesn't contain a period ([#7456](https://github.com/librenms/librenms/pull/7456))
* Fixed clickatell alert transport ([#7446](https://github.com/librenms/librenms/pull/7446))
* Escape sql credentials during install ([#7494](https://github.com/librenms/librenms/pull/7494))
* Fixed OEM ipmi sensors that returns unreadable values ([#7518](https://github.com/librenms/librenms/pull/7518))
* Fixed ospf polling not removing stale data ([#7503](https://github.com/librenms/librenms/pull/7503))
* LLDP discovery change local port resolution ([#7443](https://github.com/librenms/librenms/pull/7443))

#### Documentation
* Include Freeswitch in applications doc ([#7556](https://github.com/librenms/librenms/issues/7556))
* Added more example hardware ([#7542](https://github.com/librenms/librenms/issues/7542))
* Update syslog docs to prevent dates in the future/past ([#7519](https://github.com/librenms/librenms/issues/7519))
* Alerts glues ([#7480](https://github.com/librenms/librenms/issues/7480))
* Improve CentOS 7 and Ubuntu 16 rrdcached installation instructions ([#7473](https://github.com/librenms/librenms/issues/7473))
* Re-organize install docs ([#7424](https://github.com/librenms/librenms/pull/7424))
* Added HipChat V2 WebUI Config Example ([#7486](https://github.com/librenms/librenms/pull/7486))
* Alert rules, added in the alert rules videos ([#7512](https://github.com/librenms/librenms/pull/7512))
* Updated references for ##librenms to discord ([#7523](https://github.com/librenms/librenms/pull/7523))
* Document discovery and poller module enable/disable support ([#7505](https://github.com/librenms/librenms/pull/7505))
* OpenManage including info for windows ([#7534](https://github.com/librenms/librenms/pull/7534))
* Added SSL config for CentOS 7 with Apache ([#7529](https://github.com/librenms/librenms/pull/7529))
* Added Dynamic Configuration UI for Network-Map.md ([#7540](https://github.com/librenms/librenms/pull/7540))
* New doc for weathermap ([#7536](https://github.com/librenms/librenms/pull/7536))

#### Devices
* Always allow empty ifDescr on fortigate ([#7547](https://github.com/librenms/librenms/issues/7547))
* Added temperature sensor to datacom switches. ([#7522](https://github.com/librenms/librenms/issues/7522))
* Added more Procera interfaces ([#7422](https://github.com/librenms/librenms/issues/7422))
* Added firewall graphs for Palo Alto Networks firewall ([#7483](https://github.com/librenms/librenms/issues/7483))
* Added support for Alcoma wireless devices ([#7476](https://github.com/librenms/librenms/issues/7476))
* Added detection for SmartOptics T-Series devices ([#7433](https://github.com/librenms/librenms/issues/7433))
* Added more support for Avocent devices ([#7444](https://github.com/librenms/librenms/issues/7444))
* Added Dlink dap2660 add processors and mempools ([#7428](https://github.com/librenms/librenms/issues/7428))
* Added additional zywall-usg support ([#7405](https://github.com/librenms/librenms/pull/7405))
* Added Dlink dap2660 processors and mempools ([#7428](https://github.com/librenms/librenms/pull/7428))
* Added technicolor TG650S and TG670S ([#7420](https://github.com/librenms/librenms/pull/7420))
* Added support for alternate Equallogic SNMP sysObjectId ([#7394](https://github.com/librenms/librenms/pull/7394))
* Added zyxelnwa storage, mempools and wireless metrics ([#7441](https://github.com/librenms/librenms/pull/7441))
* Added Storage, Memory pools, new status (Array Controller, Logical Drive) for HP ILO4 ([#7436](https://github.com/librenms/librenms/pull/7436))
* Added Support for ApsoluteOS / Defense Pro Hw ([#7440](https://github.com/librenms/librenms/pull/7440))
* Added support for Huawei OceanStor devices ([#7445](https://github.com/librenms/librenms/pull/7445))
* Added detection for Racom OS RAy (#[7466](https://github.com/librenms/librenms/pull/7466)) 
* Improved Zhone MXK Discovery ([#7488](https://github.com/librenms/librenms/pull/7488))
* Added support for EATON-ATS devices ([#7448](https://github.com/librenms/librenms/pull/7448))
* Added support for Alcoma devices ([#7476](https://github.com/librenms/librenms/pull/7476))
* Added support for zywall usg vpn state and flash usage ([#7500](https://github.com/librenms/librenms/pull/7500))
* Added Brocade IronWare interface dBm sensor support ([#7434](https://github.com/librenms/librenms/pull/7434))
* Added Unifi AC HD detection ([#7516](https://github.com/librenms/librenms/pull/7516))
* Added initial detection for netmodule NB1600 ([#7514](https://github.com/librenms/librenms/pull/7514))
* Added support for new Fiberhome OLT Models ([#7499](https://github.com/librenms/librenms/pull/7499))
* Added support for logmaster(ups vendors) os/devices ([#7524](https://github.com/librenms/librenms/pull/7524))
* Added poller modules to ceraos ([#7470](https://github.com/librenms/librenms/pull/7470))
* Added more detection for IgniteNet FusionSwitch ([#7384](https://github.com/librenms/librenms/pull/7384))
* Added Mitel Standard Linux OS Support ([#7513](https://github.com/librenms/librenms/pull/7513))
* Updated Cisco WAP detection and merged in ciscosmblinux OS ([#7447](https://github.com/librenms/librenms/pull/7447))
* Added detection for Proxmox ([#7543](https://github.com/librenms/librenms/pull/7543)) 

#### Alerting
* Added alert rules for RFC1628 UPS to the collection ([#7415](https://github.com/librenms/librenms/pull/7415))
* Added HP iLo and OS-updates rules to the collection ([#7423](https://github.com/librenms/librenms/pull/7423))
* Added more simple rules for the alert collection ([#7430](https://github.com/librenms/librenms/pull/7430))

#### Refactor
* Discovery protocols re-write ([#7380](https://github.com/librenms/librenms/pull/7380))

#### WebUI
* Show only authorized services in availability map ([#7498](https://github.com/librenms/librenms/issues/7498))
* Allow user to display ok/warning/critical alerts only ([#7484](https://github.com/librenms/librenms/issues/7484))

#### Security
* Stop accepting other variables in install that we do not use ([#7511](https://github.com/librenms/librenms/pull/7511))

---

source: General/Changelog.md
## 1.32
*(2017-10-01)*

#### Features
* Added more rules to the collection of alert rules ([#7363](https://github.com/librenms/librenms/issues/763))
* Allow ignore_mount, ignore_mount_string, ignore_mount_regex per OS ([#7304](https://github.com/librenms/librenms/issues/7304))
* Added script to generate config for new OS ([#7161](https://github.com/librenms/librenms/issues/7161))
* Added Syslog hook for ASA support ([#7268](https://github.com/librenms/librenms/issues/7268))

#### Bugfixes
* If session save path is "", php will use /tmp ([#7359](https://github.com/librenms/librenms/issues/7359))
* rfc1628 runtime - allow os quirks ([#7340](https://github.com/librenms/librenms/issues/7340))
* Small error when checking ios for wireless rssi ([#7300](https://github.com/librenms/librenms/issues/7300))
* Use Netscaler vserver full names ([#7279](https://github.com/librenms/librenms/issues/7279))
* Slower hardware can hit the schema update response timeout ([#7296](https://github.com/librenms/librenms/issues/7296))
* Do not issue non-master warning if on the release update channel ([#7297](https://github.com/librenms/librenms/issues/7297))
* Fixed quotes breaking powerdns app data ([#7111](https://github.com/librenms/librenms/issues/7111))
* Updated graph_types table so graph_subtype has no default value ([#7285](https://github.com/librenms/librenms/issues/7285))
* Fixed IPv6 host renaming ([#7275](https://github.com/librenms/librenms/issues/7275))
* Fixed Comware Processor Discovery && Hardware Info ([#7206](https://github.com/librenms/librenms/issues/7206))
* Added Extreme OS mapping to 'gen_rancid' ([#7261](https://github.com/librenms/librenms/issues/7261))
* Reverted previous active directory changes [#7254](https://github.com/librenms/librenms/issues/7254) ([#7257](https://github.com/librenms/librenms/issues/7257))
* Fixed Avtech sensor discovery ([#7244](https://github.com/librenms/librenms/issues/7244))
* Corrected variable for timeout messages in unix-agent.inc.php ([#7246](https://github.com/librenms/librenms/issues/7246))
* Update notification for users with updates disabled ([#7253](https://github.com/librenms/librenms/issues/7253))
* Fixed the empty() vlan detection check ([#7241](https://github.com/librenms/librenms/issues/7241))
* Re-added changes for [#6959](https://github.com/librenms/librenms/issues/6959) removed by accident in [#7128](https://github.com/librenms/librenms/issues/7128) ([#7240](https://github.com/librenms/librenms/issues/7240))
* Issues with Geist Watchdog sensors
* Issues with Geist Watchdog miss-named variable in sensor pre-caching internal humidity and temperature was discovered twice humidity was mis-spelled in yaml discovery temperature and current had incorrect divisor in yaml

#### Documentation
* Added new faq Why would alert un-mute itself? ([#7403](https://github.com/librenms/librenms/issues/7403))
* Added performance suggestion for 1min polling documentation
* Updated Distributed poller doc as rrdcached needs -R to work properly ([#7393](https://github.com/librenms/librenms/issues/7393))
* Updated docs to include installing xml php modules + updated validate ([#7349](https://github.com/librenms/librenms/issues/7349))
* Reorganize authentication documentation ([#7329](https://github.com/librenms/librenms/issues/7329))
* Update RRDCached.md to clarify version for client/server ([#7320](https://github.com/librenms/librenms/issues/7320))
* Elaborated on permission issues with dmidecode for snmp ([#7288](https://github.com/librenms/librenms/issues/7288))
* Update Distributed-Poller.md to remove distributed_poller_host
* Added debug to services.md ([#7238](https://github.com/librenms/librenms/issues/7238))
* Fixed API-Docs Link in webui ([#7242](https://github.com/librenms/librenms/issues/7242))
* Updated Services.md include chmod +x ([#7230](https://github.com/librenms/librenms/issues/7230))

#### Refactoring
* Rewrite is_valid_port() ([#7360](https://github.com/librenms/librenms/issues/7360))
* rfc1628 sensor tidy up ([#7341](https://github.com/librenms/librenms/issues/7341))
* Added detection of vlan name changes ([#7348](https://github.com/librenms/librenms/issues/7348))
* Rewrite is_valid_port() ([#7337](https://github.com/librenms/librenms/issues/7337))
* Use the Config class includes/discovery ([#7299](https://github.com/librenms/librenms/issues/7299))
* Updated ldap auth to allow configurable uidnumber field ([#7302](https://github.com/librenms/librenms/issues/7302))
* Improve yaml state discovery ([#7221](https://github.com/librenms/librenms/issues/7221))
* Added IOS-XR Bundle-Ether shortened/corrected forms ([#7283](https://github.com/librenms/librenms/issues/7283))

#### Devices
* Added basic detection for  hanwha techwin devices ([#7397](https://github.com/librenms/librenms/issues/7397))
* Added sensor detection for APC In Row RD devices ([#7385](https://github.com/librenms/librenms/issues/7385))
* Added better hardware and version identification for SAF ([#7378](https://github.com/librenms/librenms/issues/7378))
* Added basic os for EricssonLG ES switches ([#7289](https://github.com/librenms/librenms/issues/7289))
* Updated Engenius OS detection ([#7365](https://github.com/librenms/librenms/issues/7365))
* Added detection for DPS Telecom NetGuardian ([#7326](https://github.com/librenms/librenms/issues/7326))
* Added support for Alpha FXM UPS devices ([#7324](https://github.com/librenms/librenms/issues/7324))
* Added detection for IgniteNet FusionSwitch devices
* Added support for A10 ACOS devices ([#7327](https://github.com/librenms/librenms/issues/7327))
* Added more detection for Cisco SB devices
* Added support for routeros to pull UPS info
* Added additional detection for Cisco small business switches ([#7317](https://github.com/librenms/librenms/issues/7317))
* Added sensor support for Himoinsa Gensets ([#7315](https://github.com/librenms/librenms/issues/7315))
* Added support for SmartOptics M-Series ([#7314](https://github.com/librenms/librenms/issues/7314))
* Added DHCP Leases Graph for Mikrotik ([#7333](https://github.com/librenms/librenms/issues/7333))
* Added support for Toshiba RemotEye4 devices ([#7312](https://github.com/librenms/librenms/issues/7312))
* Added additional Quanta detection ([#7316](https://github.com/librenms/librenms/issues/7316))
* Added additional detection for Calix devices ([#7325](https://github.com/librenms/librenms/issues/7325))
* Added detection for Himoinsa Gensets ([#7295](https://github.com/librenms/librenms/issues/7295))
* Added detection for ServerChecks ([#7308](https://github.com/librenms/librenms/issues/7308))
* Added support for Saf Integra Access points ([#7292](https://github.com/librenms/librenms/issues/7292))
* Added basic Open-E detection ([#7301](https://github.com/librenms/librenms/issues/7301))
* Updated Arista entity-physical support to use high/low values from device ([#7156](https://github.com/librenms/librenms/issues/7156))
* Added support for Mimosa A5 ([#7287](https://github.com/librenms/librenms/issues/7287))
* Updated state sensor code for Netonix
* Added support for Radware / AlteonOS OS/Mem/Proc ([#7220](https://github.com/librenms/librenms/issues/7220))
* Added support for DragonWave Horizon ([#7264](https://github.com/librenms/librenms/issues/7264))

#### WebUI
* Updated alert rule collection to be table ([#7371](https://github.com/librenms/librenms/issues/7371))
* Show how long a device has been down if it is down ([#7336](https://github.com/librenms/librenms/issues/7336))
* Makes the .availability-label border-radius fit in with the border a bit better
* Added device description to overview page ([#7328](https://github.com/librenms/librenms/issues/7328))
* Greatly reduces application memory leak for dashboard ([#7215](https://github.com/librenms/librenms/issues/7215))

#### API
* Added ability to supports CORS for API ([#7357](https://github.com/librenms/librenms/issues/7357))
* Added simple OSPF API route ([#7298](https://github.com/librenms/librenms/pull/7298))

---

## 1.31
*(2017-08-26)*

#### Features
* Notify about failed updates, block detectable bad updates ([#7188](https://github.com/librenms/librenms/issues/7188))
* Improve install process ([#7223](https://github.com/librenms/librenms/issues/7223))
* Active Directory user in nested groups ([#7175](https://github.com/librenms/librenms/issues/7175))
* sysV init script for the IRC bot ([#7170](https://github.com/librenms/librenms/issues/7170))
* Create librenms-irc.service ([#7087](https://github.com/librenms/librenms/issues/7087))
* Forced OS Cache rebuild for unit tests ([#7163](https://github.com/librenms/librenms/issues/7163))
* New IP parsing classes.  Removes usage of Pear Net_IPv4 and Net_IPv6. ([#7106](https://github.com/librenms/librenms/issues/7106))
* Added support to cisco sensors to link them to ports + macro/docs for alerting ([#6959](https://github.com/librenms/librenms/issues/6959))
* snmp exec support ([#7126](https://github.com/librenms/librenms/issues/7126))

#### Bugfixes
* Updated dump_db_schema() to use default 0 if available ([#7225](https://github.com/librenms/librenms/issues/7225))
* Comware dBm Limits && Comware Sensor Descr ([#7216](https://github.com/librenms/librenms/issues/7216))
* Update gen_rancid.php to the correct arista os name ([#7214](https://github.com/librenms/librenms/issues/7214))
* Use Correct Comware dBm Limits ([#7207](https://github.com/librenms/librenms/issues/7207))
* Correct memory calculation for screenos ([#7191](https://github.com/librenms/librenms/issues/7191))
* Cambium ePMP CPU reporting fix ([#7182](https://github.com/librenms/librenms/issues/7182))
* Send zero for fields without values for graphite ([#7176](https://github.com/librenms/librenms/issues/7176))
* Sanitize metric name before sending via graphite ([#7173](https://github.com/librenms/librenms/issues/7173))
* Fixed dump_db_schema / validate to work with newer versions of MariaDB ([#7162](https://github.com/librenms/librenms/issues/7162))
* Escape sensor_descr_fixed in dBm sensors graph ([#7146](https://github.com/librenms/librenms/issues/7146))
* Fixed issue with column size of ifTrunk ([#7125](https://github.com/librenms/librenms/issues/7125))
* Bug in ipv62snmp function ([#7135](https://github.com/librenms/librenms/issues/7135))
* Fixed Raspberry Pi sensors ([#7131](https://github.com/librenms/librenms/issues/7131))
* Check session directory is writable before install.php ([#7103](https://github.com/librenms/librenms/issues/7103))
* Raritan CPU temperature discovery ([#7130](https://github.com/librenms/librenms/issues/7130))
* Strip " and / from snmpwalk_cache_oid() ([#7063](https://github.com/librenms/librenms/issues/7063))
* Fixed Raspberry Pi sensors support ([#7068](https://github.com/librenms/librenms/issues/7068))
* Added missing get_group_list() to ldap-authorization auth method ([#7102](https://github.com/librenms/librenms/issues/7102))
* Service warning/critical alert rules ([#7105](https://github.com/librenms/librenms/issues/7105))
* Added device status reason to up messages. ([#7085](https://github.com/librenms/librenms/issues/7085))
* Fix string quoting in snmp trim ([#7120](https://github.com/librenms/librenms/issues/7120))
* Fix missed call to removed is_ip function ([#7132](https://github.com/librenms/librenms/issues/7132))
* fix bugs introduced to address-search ([#7138](https://github.com/librenms/librenms/issues/7138))
* Update avaya-ers.inc.php ([#7139](https://github.com/librenms/librenms/issues/7138))
* Fix RPI frequency/voltage sensors ([#7144](https://github.com/librenms/librenms/issues/7144))
* Attempt to fix repeated sql issue we come across ([#7123](https://github.com/librenms/librenms/issues/7123))
* multiple fixes under agentStpSwitchConfigGroup in EdgeSwitch-SWITCHIN ([#7180](https://github.com/librenms/librenms/issues/7180))
* Fixed typo Predicated -> Predicted (2 instances) ([#7222](https://github.com/librenms/librenms/issues/7222))

#### Documentation
* Updated index page for new alerting structure ([#7226](https://github.com/librenms/librenms/issues/7226))
* Updated some old links for alerting ([#7211](https://github.com/librenms/librenms/issues/7211))
* Updated CentOS 7 + Nginx install docs ([#7209](https://github.com/librenms/librenms/issues/7209))
* Update CentOS 7 + Nginx install docs to set SCRIPT_FILENAME ([#7200](https://github.com/librenms/librenms/issues/7200))
* Update Component.md  ([#7196](https://github.com/librenms/librenms/issues/7196))
* Update Two-Factor-Auth formatting ([#7194](https://github.com/librenms/librenms/issues/7194))
* Update IRC-Bot for systemd use  ([#7084](https://github.com/librenms/librenms/issues/7084))
* Updated API docs formatting ([#7187](https://github.com/librenms/librenms/issues/7187))
* Updated alerting docs / formatting ([#7185](https://github.com/librenms/librenms/issues/7185))
* Swap mdx_del_ins with pymdownx.tilde ([#7186](https://github.com/librenms/librenms/issues/7186))
* Centralised the Metric storage docs ([#7109](https://github.com/librenms/librenms/issues/7109))
* Allow host renames with selinux enforcing for CentOS installs ([#7136](https://github.com/librenms/librenms/issues/7136))
* Update Using-Git.md ([#7178](https://github.com/librenms/librenms/issues/7178))

#### Refactoring
* Use anonymous functions for debug error_handler and shutdown_function in index.php ([#7219](https://github.com/librenms/librenms/issues/7219))
* Updated validate.php to only warn users the install is out of date if > 24 hours ([#7208](https://github.com/librenms/librenms/issues/7208))
* Udated edgecos OS polling ([#7149](https://github.com/librenms/librenms/issues/7149))
* Ability to edit default alert template ([#7121](https://github.com/librenms/librenms/issues/7121))
* Replace escapeshellcmd with Purifier in service checks ([#7118](https://github.com/librenms/librenms/issues/7118))
* Use ifName if ifDescr is blank [#7079](https://github.com/librenms/librenms/issues/7079)

#### Devices
* Stop discoverying frequencies on Raritan devices that do not exist + added voltage ([#7195](https://github.com/librenms/librenms/issues/7195))
* Added FDB and ARP support for edgeswitch devices ([#7199](https://github.com/librenms/librenms/issues/7199))
* Added additional sensor support for Sentry4 devices ([#7198](https://github.com/librenms/librenms/issues/7198))
* Added additional vlan support for Juniper devices ([#7203](https://github.com/librenms/librenms/issues/7203))
* Added Kemp LoadMaster Version Info ([#7205](https://github.com/librenms/librenms/issues/7205))
* Updated fans/temp detection for Brocade VDX devices([#7183](https://github.com/librenms/librenms/issues/7183))
* Added further sensor support for Geist Watchdog ([#7143](https://github.com/librenms/librenms/issues/7143))
* Added detection for Hitachi Data Systems SAN ([#7160](https://github.com/librenms/librenms/issues/7160))
* Udated edgecos OS polling to include more models
* Updated AKCP sensorProbe detection ([#7152](https://github.com/librenms/librenms/issues/7152))
* Added additional sensor support for Cisco ONS ([#7096](https://github.com/librenms/librenms/issues/7096))
* Added RSSI Support for Cisco IOS wireless devices ([#7147](https://github.com/librenms/librenms/issues/7147))
* Added support for Gude ETS devices ([#7145](https://github.com/librenms/librenms/issues/7145))
* Added support for Trango Apex Lynx OS ([#7142](https://github.com/librenms/librenms/issues/7142))
* Added dry contact state support for AKCP devices ([#7124](https://github.com/librenms/librenms/issues/7124))
* Added fan and temp sensor state discovery Avaya ERS ([#7134](https://github.com/librenms/librenms/issues/7134))
* Added support for Emerson energy systems ([#7128](https://github.com/librenms/librenms/issues/7128))
* Added detection for Alteon OS ([#7088](https://github.com/librenms/librenms/issues/7088))
* Added additional sensors for Microsemi PowerDsine PoE Switches ([#7114](https://github.com/librenms/librenms/issues/7114))
* Added detection for NEC Univerge devices ([#7108](https://github.com/librenms/librenms/pull/7108))
* Added VLAN discovery support for Avaya ERS devices ([#7098](https://github.com/librenms/librenms/pull/7098)) 
* Added ROS support for state sensors and system temps
* Removed check for switch model or firmware version for Avaya ERS switches
* Updated QNAP to include CPU temps ([#7110](https://github.com/librenms/librenms/pull/7110))
* Added basic VLAN disco support for Avaya-ERS switches ([#7098](https://github.com/librenms/librenms/pull/7098))
* Update ees.yaml to use correct overview graphs ([#7137](https://github.com/librenms/librenms/pull/7137))
* Update edgecos OS polling to include more models ([#7153](https://github.com/librenms/librenms/pull/7153))
* Added Raspbian Logo ([#7201](https://github.com/librenms/librenms/pull/7201))

#### WebUI
* Added ability for users to configure selectable times for graphs  ([#7193](https://github.com/librenms/librenms/issues/7193))
* Updated pi-hole graphs for better grouping ([#7179](https://github.com/librenms/librenms/issues/7179))
* Removed ability to use OR for generating rules ([#7150](https://github.com/librenms/librenms/issues/7150))
* Update avaya-ers to use ifName for displaying ([#7113](https://github.com/librenms/librenms/issues/7113))

#### Security
* Security Patch to deal with reported vulnerabilties ([#7164](https://github.com/librenms/librenms/issues/7164))

---

## 1.30
*(2017-07-27)*

#### Features
* Added script to test alerts ([#7050](https://github.com/librenms/librenms/issues/7050))
* Config helper to simplify config access ([#7066](https://github.com/librenms/librenms/issues/7066))
* Add timeout to AD auth, default is 5s ([#6967](https://github.com/librenms/librenms/issues/6967))
* Ignore web server log files ownership in validate ([#6943](https://github.com/librenms/librenms/issues/6943))
* Added new parallel snmp-scan.py to replace snmp-scan.php ([#6889](https://github.com/librenms/librenms/issues/6889))
* Add a new locking framework that uses flock. ([#6858](https://github.com/librenms/librenms/issues/6858))
* Support fdb table on generic devices ([#6902](https://github.com/librenms/librenms/issues/6902))
* Added support for sensors to be discovered from yaml ([#6859](https://github.com/librenms/librenms/issues/6859))
* Improved install experience ([#6915](https://github.com/librenms/librenms/pull/6915))
* Updated validate to detect lower case tables + added support for checking mariadb 10.2 timestamps ([#6928](https://github.com/librenms/librenms/pull/6928))
* Added support for sending metrics to OpenTSDB ([#7022](https://github.com/librenms/librenms/pull/7022))
* Further improvements and detection added to validate ([#6973](https://github.com/librenms/librenms/pull/6973))
* Added JIRA transport for alerts ([#7040](https://github.com/librenms/librenms/pull/7040))
* Log event if device polling takes too long ([#7065](https://github.com/librenms/librenms/pull/7065))

#### Bugfixes
* Allow discovery of IAP radios on Aruba Virtual Controller
* Netbotz state sensors using wrong value ([#7027](https://github.com/librenms/librenms/issues/7027))
* Fixed Rittal LCP sensor divisors ([#7014](https://github.com/librenms/librenms/issues/7014))
* Set event type alert for alert log entries ([#7013](https://github.com/librenms/librenms/issues/7013))
* Fixed netman voltage and load divisor values ([#6905](https://github.com/librenms/librenms/issues/6905))
* Fixed the index for sentry3 current + updated mibs ([#6911](https://github.com/librenms/librenms/issues/6911))
* Fixed checks for $entPhysicalIndex/$hrDeviceIndex being numeric ([#6907](https://github.com/librenms/librenms/issues/6907))
* Fixed perf_times cleanup so it actually runs ([#6908](https://github.com/librenms/librenms/issues/6908))
* Updated sed commands to allow rrdstep.php to be used to increase and decrease values ([#6941](https://github.com/librenms/librenms/pull/6941))
* Fixed FabOS state sensors ([#6947](https://github.com/librenms/librenms/pull/6947))
* Fixed FDB tables multiple IPs and IPs from other devices adding extra rows ([#6930](https://github.com/librenms/librenms/pull/6930))
* Fixed bug get_graph_by_port_hostname() only searching hostnames ([#6936](https://github.com/librenms/librenms/pull/6936))
* Include state descriptions in eventlog ([#6977](https://github.com/librenms/librenms/pull/6977))
* Eltek Valere initial detection ([#6979](https://github.com/librenms/librenms/pull/6979))
* Fixed all mib errors in base mib directory ([#7002](https://github.com/librenms/librenms/pull/7002))
* Show fatal config.php errors on the web page. ([#7023](https://github.com/librenms/librenms/pull/7023))
* Define standard ups-mib divisors properly ([#6942](https://github.com/librenms/librenms/pull/6942))
* When force adding, use the provided snmp details rather than from $config ([#7004](https://github.com/librenms/librenms/pull/7004))
* Change .htaccess to compensate for Apache bug ([#6971](https://github.com/librenms/librenms/pull/6971))
* Use the correct high/high warn thresholds for junos dbm sensors ([#7056](https://github.com/librenms/librenms/pull/7056))
* Stop loading all oses when we have no db connection ([#7003](https://github.com/librenms/librenms/pull/7003))
* Restore old junos version code as a fallback ([#6945](https://github.com/librenms/librenms/pull/6945))

#### Documentation
* Updated SNMP configuration Documentation  ([#7017](https://github.com/librenms/librenms/issues/7017))
* A couple of small fixes to the dynamic sensor docs ([#6922](https://github.com/librenms/librenms/issues/6922))
* Update Rancid Integration

#### Refactoring
* Use the new locks for schema updates ([#6931](https://github.com/librenms/librenms/issues/6931))
* Finish logic and definition separation for auth ([#6883](https://github.com/librenms/librenms/pull/6883))
* Added ability specify options for sensors yaml discovery ([#6985](https://github.com/librenms/librenms/pull/6985))
* Return more descriptive error when adding duplicate devices on sysName ([#7019](https://github.com/librenms/librenms/pull/7019))

#### Devices
* Added additional PBN detection
* Added more support for APC sensors ([#7039](https://github.com/librenms/librenms/issues/7039))
* Added sensors for Mikrotik using mtxrOpticalTable + updated MIB ([#7037](https://github.com/librenms/librenms/issues/7037))
* Added additional sensors support for HP ILO4 ([#7053](https://github.com/librenms/librenms/issues/7053))
* Added wireless sensors for SAF Tehnika ([#6975](https://github.com/librenms/librenms/issues/6975))
* Added Calix AXOS/E5-16F Detection ([#6926](https://github.com/librenms/librenms/issues/6926))
* Added more sensor support for raritan devices ([#6929](https://github.com/librenms/librenms/issues/6929))
* Added ExtremeWireless support ([#6819](https://github.com/librenms/librenms/pull/6819))
* Added Rittal LCP Liquid Cooling Package ([#6626](https://github.com/librenms/librenms/pull/6626))
* Added Detect for Toshiba Tec e-Studio printers ([#6984](https://github.com/librenms/librenms/pull/6984))
* Added Valere system sensors and os detection ([#6981](https://github.com/librenms/librenms/pull/6981))
* Added Savin printer support ([#6982](https://github.com/librenms/librenms/pull/6982))
* Added sensor support for APC IRRP 100/500 devices ([#7024](https://github.com/librenms/librenms/pull/7024))
* Added additional sensors for APC IRRP100 Air Conditionner series ([#7006](https://github.com/librenms/librenms/pull/7006))
* Added detection for Gestetner printers ([#7038](https://github.com/librenms/librenms/pull/7038))
* Added FDB support for IOS-XE devices ([#7044](https://github.com/librenms/librenms/pull/7044))
* Added detection for Siemens Ruggedcom Switches ([#7052](https://github.com/librenms/librenms/pull/7052))
* Added CiscoSB Port Suspended Status Info ([#7064](https://github.com/librenms/librenms/issues/7064))
* Added CiscoSB DOM Support ([#7072](https://github.com/librenms/librenms/pull/7072))
* Added support for temp and processor discovery on Avaya ERS3500 ([#7070](https://github.com/librenms/librenms/pull/7070))
* Added detection for TSC Barcode printer ([#7074](https://github.com/librenms/librenms/pull/7074))
* Added state sensor for HPE MSL ([#7058](https://github.com/librenms/librenms/pull/7058))
* Added PBN AIMA3000 detection ([#7083](https://github.com/librenms/librenms/pull/7083))
* Updated UBNT Airos type to wireless ([#6867](https://github.com/librenms/librenms/issues/6867))
* Updated IOS-XE detection for 3000 series devices (like 3850) ([#6983](https://github.com/librenms/librenms/issues/6983))
* Updated JunOS os polling to detect version correctly ([#6904](https://github.com/librenms/librenms/issues/6904))
* Updated Radwin detection ([#6918](https://github.com/librenms/librenms/issues/6918))
* Updated Gamatronic ups use sysObjectID for os discovery ([#6940](https://github.com/librenms/librenms/pull/6940))
* Updated HPE MSM Support ([#7026](https://github.com/librenms/librenms/pull/7026))
* Updated powerwalker sensor discovery to use custom mib ([#7020](https://github.com/librenms/librenms/pull/7020))
* Updated Cisco IOS XE Version Parsing ([#7073](https://github.com/librenms/librenms/pull/7073))

#### WebUI
* Facelift for alert templates, also added bootgrid ([#7041](https://github.com/librenms/librenms/issues/7041))
* Set correct button text when editing an alert template ([#6916](https://github.com/librenms/librenms/issues/6916))
* Minor visual changes in schedule maintenance window and its modal ([#6934](https://github.com/librenms/librenms/pull/6934))
* Fixed issues with http-auth when the guest user is created before the intended user ([#7000](https://github.com/librenms/librenms/pull/7000))
* Delhost: Added an empty option for device selection, and a minor db performance fix ([#7018](https://github.com/librenms/librenms/pull/7018))
* Loading speed improvement when viewing syslogs for specific device ([#7062](https://github.com/librenms/librenms/pull/7062))

#### Security
* Enable support for secure cookies ([#6868](https://github.com/librenms/librenms/issues/6868))

#### API
* Added api routes for eventlog, syslog, alertlog, authlog ([#7071](https://github.com/librenms/librenms/pull/7071))

---

## 1.29
*(2017-06-24)*

#### Features
* New snmpwalk_group() function ([#6865](https://github.com/librenms/librenms/issues/6865))
* Added support for passing state to alert templates test 
* Added option to specify transport when testing a template ([#6755](https://github.com/librenms/librenms/issues/6755))
* Added support to use IP addresses for NfSen filenames ([#6824](https://github.com/librenms/librenms/issues/6824))
* Added pi-hole application support ([#6782](https://github.com/librenms/librenms/issues/6782))
* Added some more coloring and make it easier to colorize messages for irc bot ([#6759](https://github.com/librenms/librenms/issues/6759))
* Added syslog auth failure to alert_rules.json ([#6847](https://github.com/librenms/librenms/issues/6847))
* Added support to use IP addresses for NfSen filenames ([#6824](https://github.com/librenms/librenms/issues/6824))
* Added Irc host authentication ([#6757](https://github.com/librenms/librenms/issues/6757))
* Added Syslog hooks for Oxidized integration (and more) ([#6785](https://github.com/librenms/librenms/issues/6785))

#### Bugfixes
* config_to_json.php does not pull in database configuration settings ([#6884](https://github.com/librenms/librenms/issues/6884))
* Updated sysObjectId column in devices table to varchar(128) ([#6832](https://github.com/librenms/librenms/issues/6832))
* Strip " from rPi temp sensor discovery ([#6815](https://github.com/librenms/librenms/issues/6815))
* Check for ifHCInOctets and ifHighSpeed before falling back to if… ([#6777](https://github.com/librenms/librenms/issues/6777))
* Updated Raspberry Pi Temp sensor discovery ([#6804](https://github.com/librenms/librenms/issues/6804))
* Fix bad Cisco dBm discovery on some IOS versions ([#6789](https://github.com/librenms/librenms/issues/6789))
* Ircbot - reformatted strikethrough for recovered alerts ([#6756](https://github.com/librenms/librenms/issues/6756))
* Ensure rrdtool web settings aren't overwrote by defaults ([#6698](https://github.com/librenms/librenms/issues/6698))
* Add column title under device bgp tab ([#6747](https://github.com/librenms/librenms/issues/6747))
* Custom config.php os settings ([#6850](https://github.com/librenms/librenms/issues/6850))
* Fix for syslog-messages from zywall (USG series) ([#6838](https://github.com/librenms/librenms/issues/6838))

#### Documentation
* Reorganised alerting docs + added some clarifications ([#6869](https://github.com/librenms/librenms/issues/6869))
* Update Ubuntu and CentOS nginx install doc with a better nginx config ([#6836](https://github.com/librenms/librenms/issues/6836))
* Added note to configure mod_status for Apache application ([#6810](https://github.com/librenms/librenms/issues/6810))
* Updated ask people to contribute documentation ([#6739](https://github.com/librenms/librenms/issues/6739))
* Reorganize auto-discovery docs and add a little info ([#6875](https://github.com/librenms/librenms/issues/6875))

#### Devices
* Added support for Radwin 5000 Series ([#6876](https://github.com/librenms/librenms/issues/6876))
* Added support for Chatsworth PDU (legacy old pdus not sure model number) ([#6833](https://github.com/librenms/librenms/issues/6833))
* Added detection for Microsemi PowerDsine PoE Midspans ([#6843](https://github.com/librenms/librenms/issues/6843))
* Added additional sensors to Axis camera ([#6827](https://github.com/librenms/librenms/issues/6827))
* Added Quanta lb6m device support ([#6816](https://github.com/librenms/librenms/issues/6816))
* Added hardware and version from AirOS 8.x ([#6802](https://github.com/librenms/librenms/issues/6802))
* Added support for processor and memory for 3com devices ([#6823](https://github.com/librenms/librenms/issues/6823))
* Added state sensors to HP Procurve ([#6814](https://github.com/librenms/librenms/issues/6814))
* Added detection for Atal Ethernetprobe ([#6778](https://github.com/librenms/librenms/issues/6778))
* Updated vmware vcsa hardware/version detection  ([#6783](https://github.com/librenms/librenms/issues/6783))
* Added C.H.I.P. power monitor ([#6763](https://github.com/librenms/librenms/issues/6763))
* Updated cisco-iospri to check for numeric + named ifType and included new cisco mibs ([#6776](https://github.com/librenms/librenms/issues/6776))
* Added detection for Arris C4c ([#6662](https://github.com/librenms/librenms/issues/6662))
* Added Current Connections Graph for Cisco WSA ([#6734](https://github.com/librenms/librenms/issues/6734))
* Added detection for AXIS Audio Appliances ([#6830](https://github.com/librenms/librenms/issues/6830))
* Added basic support for CradlePoint WiPipe Cellular Broadband Routers ([#6695](https://github.com/librenms/librenms/issues/6695))
* Added Avaya VSP Temperature Support ([#6692](https://github.com/librenms/librenms/issues/6692))
* Added support for ADVA FSP150CC and FSP3000R7 Series ([#6696](https://github.com/librenms/librenms/issues/6696))
* Updated Oracle ILOM detection ([#6779](https://github.com/librenms/librenms/issues/6779))
* Added Cisco ASR, Nexus, etc. PSU State sensor ([#6790](https://github.com/librenms/librenms/issues/6790))
* Updated Cisco NX-OS detection ([#6796](https://github.com/librenms/librenms/issues/6796))
* Added more detection for Bintec smart devices ([#6780](https://github.com/librenms/librenms/issues/6780))
* Added support for Schneider PowerLogic ([#6809](https://github.com/librenms/librenms/issues/6809))
* Updated Cisco Unified CM detection and renamed to ucos ([#6813](https://github.com/librenms/librenms/issues/6813))
* Added basic Support for Benu OS ([#6857](https://github.com/librenms/librenms/issues/6857))

#### WebUI
* Added "system name" for the "Services list" ([#6873](https://github.com/librenms/librenms/issues/6873))
* Allow editing and deleting of lapsed alert schedules ([#6878](https://github.com/librenms/librenms/issues/6878))
* Add bootgrid for authlog page, and fix poll-log searchbar layout on smaller screens ([#6805](https://github.com/librenms/librenms/issues/6805))
* Updated all tables to have the same set number of items showing ([#6798](https://github.com/librenms/librenms/issues/6798))
* Allow iframe in notes widget ([#6773](https://github.com/librenms/librenms/issues/6773))
* Load google maps js library only if globe map widget is used
* Added service alert rules ([#6772](https://github.com/librenms/librenms/issues/6772))
* Added syslog auth failure to alert_rules.json ([#6847](https://github.com/librenms/librenms/issues/6847))
* Fixed dashboard slowness with offline browser ([#6718](https://github.com/librenms/librenms/issues/6718))
* Update graphs to use safer RRD check ([#6781](https://github.com/librenms/librenms/issues/6781))
* Populate a sorted device list ([#6781](https://github.com/librenms/librenms/issues/6781))

#### Alerts
* Added elasticsearch transport and docs ([#6797](https://github.com/librenms/librenms/issues/6797))
* Update irc transport to use templates ([#6758](https://github.com/librenms/librenms/issues/6758))

#### API
* Added search by os to list_devices ([#6861](https://github.com/librenms/librenms/issues/6861))

#### Refactor
* Discovery code cleanups ([#6856](https://github.com/librenms/librenms/issues/6856))

---

## 1.28
*(2017-05-28)*

#### Features
* Update Juniper MSS Support ([#6565](https://github.com/librenms/librenms/issues/6565))
* Added ability to whitelist ifDescr values from being ignored with good_if ([#6584](https://github.com/librenms/librenms/issues/6584))
* Added additional Unbound chart for query cache info ([#6574](https://github.com/librenms/librenms/issues/6574))
* Wireless Sensors Overhaul ([#6471](https://github.com/librenms/librenms/pull/6471))
* Updated BIND application ([#6218](https://github.com/librenms/librenms/issues/6218))
* Added script (scripts/test-template.php) to test alert templates ([#6631](https://github.com/librenms/librenms/issues/6631))
* Improve Juniper MSS Support ([#6565](https://github.com/librenms/librenms/issues/6565))

#### Bugfixes
* Added dell to mib_dir for windows / linux ([#6726](https://github.com/librenms/librenms/issues/6726))
* Fix marking invalid ports as deleted in discovery ([#6665](https://github.com/librenms/librenms/issues/6665))
* Improve authentication load time and security ([#6615](https://github.com/librenms/librenms/issues/6615))
* Page/graph load speed: part 1 ([#6611](https://github.com/librenms/librenms/issues/6611))
* Fixed radius debug mode ([#6623](https://github.com/librenms/librenms/issues/6623))
* Actives PRI calls on Cisco can be an array ([#6607](https://github.com/librenms/librenms/issues/6607))
* MySQL app graphs with rrdcached ([#6608](https://github.com/librenms/librenms/issues/6608))
* Fix issue with wireless sensors when there are too many oids ([#6578](https://github.com/librenms/librenms/issues/6578))
* Fix GE UPS voltage factor ([#6558](https://github.com/librenms/librenms/issues/6558))
* Try to fix load for eaton-mgeups ([#6566](https://github.com/librenms/librenms/issues/6566))
* Validate prefer capabilities over suid for fping ([#6644](https://github.com/librenms/librenms/issues/6644))
* When force adding devices with v3, actually store the details ([#6691](https://github.com/librenms/librenms/issues/6691))
* Fixed uptime detection ([#6705](https://github.com/librenms/librenms/issues/6705))

#### Documentation
* Create code of conduct page ([#6640](https://github.com/librenms/librenms/issues/6640))
* Add all current wireless types. ([#6603](https://github.com/librenms/librenms/issues/6603))
* Added seconds is the time unit. ([#6589](https://github.com/librenms/librenms/issues/6589))

#### Refactoring
* Added lock support to ./discovery.php -h new to prevent overlap ([#6568](https://github.com/librenms/librenms/issues/6568))
* OS discovery tests are now dynamic ([#6555](https://github.com/librenms/librenms/issues/6555))
* DB Updates will now file level lock to stop duplicate updates ([#6469](https://github.com/librenms/librenms/issues/6469))
* Increased speed of loading syslog pages ([#6433](https://github.com/librenms/librenms/issues/6433))
* Moved default alert rules into the collection ([#6621](https://github.com/librenms/librenms/issues/6621))
* Modest speedup to database config population ([#6636](https://github.com/librenms/librenms/issues/6636))
* Pretty mysql for alerts breaks regex rules ([#6614](https://github.com/librenms/librenms/issues/6614))
* Updated vlan discovery to support JunOS ([#6597](https://github.com/librenms/librenms/issues/6597))

#### Devices
* Added Wireless Support For Cisco IOS-XE([#6724](https://github.com/librenms/librenms/pull/6724))
* Improve Aerohive Support ([#6721](https://github.com/librenms/librenms/issues/6721))
* Added support for Halon Gateway ([#6716](https://github.com/librenms/librenms/issues/6716))
* Added basic HPE OpenVMS detection ([#6706](https://github.com/librenms/librenms/issues/6706))
* Added additional sensor state sysCmSyncStatusId for F5
* Added more health information for APC units ([#6619](https://github.com/librenms/librenms/issues/6619))
* Updated Lancom LCOS detection ([#6651](https://github.com/librenms/librenms/issues/6651))
* Added 3 Phase APC UPS Support [#2733](https://github.com/librenms/librenms/issues/2733) & [#5504](https://github.com/librenms/librenms/issues/5504) ([#5558](https://github.com/librenms/librenms/issues/5558))
* Added FWSM recognition to PIX OS ([#6569](https://github.com/librenms/librenms/issues/6569))
* Aruba Instant AP wireless sensor support (Freq, NoiseFloor, Power, Util) ([#6564](https://github.com/librenms/librenms/issues/6564))
* Added CPU and Memory pool for BDCom Switchs ([#6523](https://github.com/librenms/librenms/issues/6523))
* Added support for Aruba ClearPass devices ([#6528](https://github.com/librenms/librenms/issues/6528))
* Added support for Cisco's AsyncOS ([#6545](https://github.com/librenms/librenms/issues/6545))
* Added support for AKCP SecurityProbe ([#6550](https://github.com/librenms/librenms/issues/6550))
* Added support for GE UPS (#6549) ([#6553](https://github.com/librenms/librenms/issues/6553))
* Improve Extremeware and XOS detection ([#6554](https://github.com/librenms/librenms/issues/6554))
* Added more sensors for Exalt ExtendAir devices ([#6531](https://github.com/librenms/librenms/issues/6531))
* Added support for Terra sti410C ([#6598](https://github.com/librenms/librenms/issues/6598))
* Make TiMOS detection more generic, rebrand to Nokia ([#6645](https://github.com/librenms/librenms/issues/6645))
* Added HPE RT3000 UPS support ([#6638](https://github.com/librenms/librenms/issues/6638))
* Added Enhance Barracuda NG Firewall Detection ([#6658](https://github.com/librenms/librenms/issues/6658))
* Added support for Geist PDU ([#6646](https://github.com/librenms/librenms/issues/6646))
* Improved Lancom LCOS detection, added LCOS-MIB ([#6651](https://github.com/librenms/librenms/issues/6651))
* Added Basic Cisco SCE Support ([#6666](https://github.com/librenms/librenms/issues/6666))
* Added support for MRV OptiDriver Optical Transport Platform ([#6656](https://github.com/librenms/librenms/issues/6656))
* Update comware version and serial number polling ([#6686](https://github.com/librenms/librenms/issues/6686))
* Added TiMOS temperature and power supply state sensors ([#6657](https://github.com/librenms/librenms/issues/6657))
* Added state support FAN and Power Supply for Avaya VSP ([#6693](https://github.com/librenms/librenms/issues/6693))
* Added detection for Cisco EPC devices ([#6690](https://github.com/librenms/librenms/issues/6690))
* Added Wireless Support For Cisco IOS-XE ([#6724](https://github.com/librenms/librenms/issues/6724))

#### WebUI
* Make login form more mobile-friendly ([#6707](https://github.com/librenms/librenms/issues/6707))
* Updated link to peeringdb to use asn ([#6625](https://github.com/librenms/librenms/issues/6625))
* Disabled settings button for Shared (read) dashboards if you are not the owner ([#6596](https://github.com/librenms/librenms/issues/6596))
* Split apart max and min sensor limits, allows sorting ([#6592](https://github.com/librenms/librenms/issues/6592))
* Load device list for dropdowns using Ajax ([#6557](https://github.com/librenms/librenms/issues/6557))
* Updated remaining display options where we do not show sysName if hostname is IP ([#6585](https://github.com/librenms/librenms/issues/6585))

#### Security
* Remove possibility of xss in Oxidized and RIPE searches ([#6595](https://github.com/librenms/librenms/issues/6595))

#### Alerting
* Added option to enable/disable option for sending alerts to normal users ([#6590](https://github.com/librenms/librenms/issues/6590))
* Added HipChat v2 API + Color Changes ([#6669](https://github.com/librenms/librenms/issues/6669))

---

## 1.27
*(2017-04-29)*

#### Features
* Added sdfsinfo application support ([#6494](https://github.com/librenms/librenms/issues/6494))
* Allow _except suffix in yaml os discovery ([#6444](https://github.com/librenms/librenms/issues/6444))
* Added check_mssql_health.inc.php for service checks ([#6415](https://github.com/librenms/librenms/issues/6415))
* Added rrdtool version check to compare installed version with defined version ([#6381](https://github.com/librenms/librenms/issues/6381))
* Added ability to validate database schema ([#6303](https://github.com/librenms/librenms/issues/6303))
* Support powerdns-recursor SNMP extend ([#6290](https://github.com/librenms/librenms/issues/6290))
* Added cisco-vpdn to poller modules ([#6300](https://github.com/librenms/librenms/issues/6300))
* Support non-standard unix socket ([#5724](https://github.com/librenms/librenms/issues/5724))
* Added multi DB support to the Postgres app ([#6222](https://github.com/librenms/librenms/issues/6222))
* Added opengridscheduler job tracker ([#6419](https://github.com/librenms/librenms/issues/6419))
* Added location map regex replace pattern only ([#6485](https://github.com/librenms/librenms/issues/6485))
* Added nfs-server application ([#6320](https://github.com/librenms/librenms/issues/6320))
* Added support for Active Directory bind user ([#6255](https://github.com/librenms/librenms/pull/6255))

#### Bugfixes
* Actually reload oxidized when we should not when we think we should ([#6515](https://github.com/librenms/librenms/issues/6515))
* Don't run ipmitool without knowing a type  ([#6504](https://github.com/librenms/librenms/issues/6504))
* Updated ipv4/ipv6 discovery to exclude IPs with invalid port_ids ([#6495](https://github.com/librenms/librenms/issues/6495))
* Updated enterasys mempools disco/polling to support multiple ram devices ([#6458](https://github.com/librenms/librenms/issues/6458))
* Service filenames are snipped when longer than 16 characters ([#6459](https://github.com/librenms/librenms/issues/6459))
* Updated use of ifNameDescr() to cleanPort() ([#6454](https://github.com/librenms/librenms/issues/6454))
* Allow line returns in snmprec files with the 4x data type ([#6443](https://github.com/librenms/librenms/issues/6443))
* Update Shebangs and daily.sh for FreeBSD compatibility ([#6413](https://github.com/librenms/librenms/issues/6413))
* Cisco Entity Sensor Threshold's returns 0 ([#6440](https://github.com/librenms/librenms/issues/6440))
* Updated enterasys proc discovery by setting correct index ([#6422](https://github.com/librenms/librenms/issues/6422))
* Allow unit tests without a sql server ([#6398](https://github.com/librenms/librenms/issues/6398))
* Fix broken mysql application polling ([#6317](https://github.com/librenms/librenms/issues/6317))
* Move user preferences dashboard and twofactor out of users table ([#6286](https://github.com/librenms/librenms/issues/6286))
* Fixed CPU/Mem polling for Cyberoam-UTM devices ([#6315](https://github.com/librenms/librenms/issues/6315))
* Fixed F5 ports not using hc counters ([#6294](https://github.com/librenms/librenms/issues/6294))
* Added semicolons in build.sql schema file ([#6284](https://github.com/librenms/librenms/issues/6284))
* Fixed height of widget boxes ([#6282](https://github.com/librenms/librenms/issues/6282))
* Update applications poller to use numeric oid instead of nsExtendOutputFull ([#6277](https://github.com/librenms/librenms/issues/6277))
* Compare existing device ip to host lookup like for like ([#6316](https://github.com/librenms/librenms/issues/6316))
* Fix whitespace display on RRDTool Command ([#6345](https://github.com/librenms/librenms/issues/6345))
* Vlan port mappings not removed ([#6423](https://github.com/librenms/librenms/issues/6423))
* Fix alerts not honouring interval over 5m ([#6438](https://github.com/librenms/librenms/issues/6438))
* Improve CiscoSB polling time ([#6447](https://github.com/librenms/librenms/issues/6447))
* Updated cisco and juniper component macros to exclude disabled sensors ([#6493](https://github.com/librenms/librenms/issues/64649393))
* Added more safety checking into create_state_index() ([#6516](https://github.com/librenms/librenms/issues/6516))
* Fixed inconsistent device discovery ([#6518](https://github.com/librenms/librenms/issues/6518))
* Fixed notifications by email to Active Directory admins ([#6134](https://github.com/librenms/librenms/issues/6134))
* Fixed API token for Active Directory admins ([#6255](https://github.com/librenms/librenms/issues/6255))

#### Documentation
* Added FAQ on what disabled/ignored means for devices
* Updated install docs + perf to support compressing file types and using http/2 ([#6466](https://github.com/librenms/librenms/issues/6466))
* Update install docs to remove deprecated GRANT usage
* Update to remove the old method of signing the CLA ([#6479](https://github.com/librenms/librenms/issues/6479))
* Updated Support-New-OS doc to provide clearer information ([#6492](https://github.com/librenms/librenms/issues/6492))

#### Refactoring
* Use sysDescr to simplify the vyatta detection ([#6455](https://github.com/librenms/librenms/issues/6455))
* Move siklu os detection to yaml ([#6431](https://github.com/librenms/librenms/issues/6431))
* Move rfc1628_compat into os yaml ([#6424](https://github.com/librenms/librenms/issues/6424))
* Move Engenius discovery to yaml ([#6428](https://github.com/librenms/librenms/issues/6428))
* Move cometsystem-p85xx ([#6427](https://github.com/librenms/librenms/issues/6427))
* Update some snmpwalks for ports polling to improve speed ([#6341](https://github.com/librenms/librenms/issues/6341))
* Moved ifLabel -> cleanPort and updated the usage ([#6288](https://github.com/librenms/librenms/issues/6288))
* Update ucd-diskio discovery to use index + descr as unique identifies [#4670](https://github.com/librenms/librenms/issues/4670) ([#6270](https://github.com/librenms/librenms/issues/6270))
* Changed MGE UPS to APC UPS (mgeups -> apc) ([#6260](https://github.com/librenms/librenms/issues/6260))
* Change Cisco UCM category from tele to collaboration ([#6297](https://github.com/librenms/librenms/issues/6297))
* Move aos discovery to yaml ([#6425](https://github.com/librenms/librenms/issues/6425))
* Move the rest of avaya os detection to yaml ([#6426](https://github.com/librenms/librenms/issues/6426))
* Move cometsystem-p85xx to yaml ([#6427](https://github.com/librenms/librenms/issues/6427))
* Move Engenius discovery to yaml ([#6428](https://github.com/librenms/librenms/issues/6428))
* Added 'Video' device group and moved Axis cameras to this group' ([#6397](https://github.com/librenms/librenms/issues/6397))
* Remove unecessary OS checks in proc / mem polling ([#6414](https://github.com/librenms/librenms/issues/6414))
* Only run pre-cache for the current OS ([#6453](https://github.com/librenms/librenms/issues/6453))
* Move ios detection to yaml using new sysDescr_except ([#6460](https://github.com/librenms/librenms/issues/6460))
* Eaton/MGE UPS reorganization ([#6388](https://github.com/librenms/librenms/issues/6388))

#### Devices
* Added more health sensors for c&c power commanders ([#6517](https://github.com/librenms/librenms/issues/6517))
* Added support for Tycon Systems TPDIN units ([#6506](https://github.com/librenms/librenms/issues/6506))
* Added basic detection for Packetflux SiteMonitor ([#6498](https://github.com/librenms/librenms/issues/6498))
* Added detection for Ericsson UPC devices ([#6472](https://github.com/librenms/librenms/issues/6472))
* Added basic detection for Geist Watchdog ([#6467](https://github.com/librenms/librenms/issues/6467))
* Added support for enLogic PDUs ([#6464](https://github.com/librenms/librenms/issues/6464))
* Added support for Eltex OLT devices ([#6457](https://github.com/librenms/librenms/issues/6457))
* Added Etherwan managed switches ([#6488](https://github.com/librenms/librenms/issues/6488))
* Added signal sensor for opengear devices ([#6401](https://github.com/librenms/librenms/issues/6401))
* Added support for Teradici PCoIP card ([#6347](https://github.com/librenms/librenms/issues/6347))
* Added basic support for Omnitron iConverters ([#6336](https://github.com/librenms/librenms/issues/6336))
* Added support for AvediaStream Encoder ([#6306](https://github.com/librenms/librenms/issues/6306))
* Added ArubaOS PowerConnect detection ([#6463](https://github.com/librenms/librenms/issues/6463))
* Added HPE iPDU detection ([#6334](https://github.com/librenms/librenms/issues/6334))
* Moved dnos health disco to powerconnect ([#6331](https://github.com/librenms/librenms/issues/6331))
* Added Nokia (Alcatel-Lucent) SAS-Sx 7210 support ([#6344](https://github.com/librenms/librenms/issues/6344))
* Added Opengear ACM7008 detection ([#6349](https://github.com/librenms/librenms/issues/6349))
* Added detection fro Juniper MSS ([#6335](https://github.com/librenms/librenms/issues/6335))
* Added sensors + additional info for HPE iPDU ([#6382](https://github.com/librenms/librenms/issues/6382))
* Added Basic Ciena (Cyan) Z-Series detection ([#6385](https://github.com/librenms/librenms/issues/6385))
* Added Coriant Network Hardware Page. ([#6187](https://github.com/librenms/librenms/issues/6187))
* Added support for Vanguard ApplicationsWare ([#6387](https://github.com/librenms/librenms/issues/6387))
* Added ICT Digital Power Supply support ([#6369](https://github.com/librenms/librenms/issues/6369))
* Added ICT DC Distribution Panel support ([#6379](https://github.com/librenms/librenms/issues/6379))
* Added more detection for Comware ([#6386](https://github.com/librenms/librenms/issues/6386))
* Added Multi-lane optics on Juniper equipment ([#6377](https://github.com/librenms/librenms/issues/6377))
* Added detection and sensor support for EMC OneFS v8 ([#6416](https://github.com/librenms/librenms/issues/6416))
* Added detection for IgniteNet HeliOS ([#6417](https://github.com/librenms/librenms/issues/6417))
* Added basic detection for Tandberg Magnum tape units ([#6421](https://github.com/librenms/librenms/issues/6421))
* Added detection for Ciena packet switches ([#6462](https://github.com/librenms/librenms/issues/6462))
* Added Cisco SG355-10P support ([#6477](https://github.com/librenms/librenms/issues/6477))
* Added mem/cpu support for TiMOS ([#6483](https://github.com/librenms/librenms/issues/6483))
* Added support for C&C Commander Plus units ([#6478](https://github.com/librenms/librenms/issues/6478))
* Added Equallogic add disk status ([#6497](https://github.com/librenms/librenms/issues/6497))

#### WebUI
* Updated bgp table to use bootstrap properly ([#6406](https://github.com/librenms/librenms/issues/6406))
* Update poller_modules_perf to not show OS disabled module graphs ([#6276](https://github.com/librenms/librenms/issues/6276))
* Select the correct dashboard when there are no defaults. ([#6339](https://github.com/librenms/librenms/issues/6339))
* Fix redirect on login for instances behind reverse proxies ([#6371](https://github.com/librenms/librenms/issues/6371))
* Fixed the display date for the current version ([#6474](https://github.com/librenms/librenms/issues/6474))

#### API
* Allow cidr network searches of the ARP table ([#6378](https://github.com/librenms/librenms/issues/6378))

---

## 1.26
*(2017-03-25)*

#### Features
* Added syslog alert transport ([#6246](https://github.com/librenms/librenms/issues/6246))
* Send collected data to graphite server ([#6201](https://github.com/librenms/librenms/issues/6201))
* Added SMART application support ([#6181](https://github.com/librenms/librenms/issues/6181))
* Peeringdb integration to show the Exchanges and peers for your AS' ([#6178](https://github.com/librenms/librenms/issues/6178))
* Added support for sending alerts to Telegram [#2114](https://github.com/librenms/librenms/issues/2114) ([#6202](https://github.com/librenms/librenms/issues/6202))
* Added pbin.sh to upload text to p.libren.ms ([#6175](https://github.com/librenms/librenms/issues/6175))
* Added better BGP support for Arista ([#6046](https://github.com/librenms/librenms/issues/6046))
* Added rrd step conversion script ([#6081](https://github.com/librenms/librenms/issues/6081))
* Store the username in eventlog for any entries created through the Webui ([#6032](https://github.com/librenms/librenms/issues/6032))
* Added Nvidia GPU  application support ([#6024](https://github.com/librenms/librenms/issues/6024))
* Added Squid application support ([#6011](https://github.com/librenms/librenms/issues/6011))
* Added FreeBSD NFS Client/Server application support ([#6008](https://github.com/librenms/librenms/issues/6008))
* Added get_disks function ([#6058](https://github.com/librenms/librenms/issues/6058))
* Updated Nfsen integration support ([#6003](https://github.com/librenms/librenms/issues/6003))
* Added Basic Oxidized Node List ([#6017](https://github.com/librenms/librenms/issues/6017))
* Added support for dynamic interfaces in ifAlias script ([#6005](https://github.com/librenms/librenms/issues/6005))
* Added support Postfix application ([#6002](https://github.com/librenms/librenms/pull/6002))
* Added Postgres application support ([#6004](https://github.com/librenms/librenms/pull/6004))
* Added ability to show links to fixes for validate ([#6054](https://github.com/librenms/librenms/pull/6054))
* Added FreeBSD NFS Client/Server application support ([#6008](https://github.com/librenms/librenms/pull/6008))
* Added Squid application support ([#6011](https://github.com/librenms/librenms/pull/6011))
* Added Nvidia GPU application support ([#6024](https://github.com/librenms/librenms/pull/6024))
* Added app_state support for applications #5068 ([#6061](https://github.com/librenms/librenms/pull/6061))
* Send default mail when no email destinations found ([#6165](https://github.com/librenms/librenms/pull/6165))
* Added new alert rules to collection ([#6166](https://github.com/librenms/librenms/pull/6166))
* Added SMART app support ([#6181](https://github.com/librenms/librenms/pull/6181))
* Added Application discovery ([#6143](https://github.com/librenms/librenms/pull/6143))
* Added syslog alert transport and docs ([#6246](https://github.com/librenms/librenms/pull/6246))

#### Bugfixes
* Clear out stale alerts ([#6268](https://github.com/librenms/librenms/issues/6268))
* Remove min value for ntp* graphs [#6240](https://github.com/librenms/librenms/issues/6240)
* Alerts that worsen or get better will now record updated info [#4323](https://github.com/librenms/librenms/issues/4323) ([#6203](https://github.com/librenms/librenms/issues/6203))
* Do not show overview graphs when user only has port permissions for device ([#6230](https://github.com/librenms/librenms/issues/6230))
* Yaml files for edgeos and edgeswitch ([#6208](https://github.com/librenms/librenms/issues/6208))
* Fix Liebert humidity and temp sensors [#6196](https://github.com/librenms/librenms/issues/6196) ([#6198](https://github.com/librenms/librenms/issues/6198))
* Graphs $auth check was too strict ([#6195](https://github.com/librenms/librenms/issues/6195))
* Alter the database to set the proper character set and collation ([#6189](https://github.com/librenms/librenms/issues/6189))
* Wrong NetBotz file location ([#6188](https://github.com/librenms/librenms/issues/6188))
* Change rfc1628 'state' (est. runtime and on battery) to runtime ([#6158](https://github.com/librenms/librenms/issues/6158))
* Fix the displaying of alert info for historical alerts [#6092](https://github.com/librenms/librenms/issues/6092) ([#6107](https://github.com/librenms/librenms/issues/6107))
* Record actual sensor value for unix-agent hddtemp [#5904](https://github.com/librenms/librenms/issues/5904) ([#6089](https://github.com/librenms/librenms/issues/6089))
* Ping perf is in milliseconds, not seconds ([#6140](https://github.com/librenms/librenms/issues/6140))
* SVG scaling issues in Internet Explorer ([#6021](https://github.com/librenms/librenms/issues/6021))
* Old / duplicate sensors would never be removed, this is fixed by setting the $type correctly [#6044](https://github.com/librenms/librenms/issues/6044) ([#6079](https://github.com/librenms/librenms/issues/6079))
* Refactor ipoman cache code to use pre-cache in sensors [#5881](https://github.com/librenms/librenms/issues/5881) ([#5983](https://github.com/librenms/librenms/issues/5983))
* Fixed the previous graphs for diskio/bits [#6077](https://github.com/librenms/librenms/issues/6077) ([#6083](https://github.com/librenms/librenms/issues/6083))
* Update OSTicket transport to use the from email address [#5739](https://github.com/librenms/librenms/issues/5739) ([#5927](https://github.com/librenms/librenms/issues/5927))
* Do not try and only include files once when they are needed again! ([#5881](https://github.com/librenms/librenms/issues/5881))
* Correct the use of GetContacts() #5012 ([#6059](https://github.com/librenms/librenms/pull/6059))
* Netonix: properly set default fanspeed limits ([#6144](https://github.com/librenms/librenms/pull/6144))
* Fix Generex load sensor divisor ([#6155](https://github.com/librenms/librenms/pull/6155))
* Sensors not being removed from database ([#6169](https://github.com/librenms/librenms/pull/6169))
* Updated http-auth to work with nginx http auth #6102 ([#6174](https://github.com/librenms/librenms/pull/6174))
* Change rfc1628 'state' (est. runtime and on battery) to runtime ([#6158](https://github.com/librenms/librenms/pull/6158))

#### Documentation
* Renamed the mysql extend script to just mysql ([#6126](https://github.com/librenms/librenms/issues/6126))

#### Refactoring
* Move some DNOS detection to PowerConnect [#6150](https://github.com/librenms/librenms/issues/6150) ([#6206](https://github.com/librenms/librenms/issues/6206))
* Rename check_domain_expire.inc.php to check_domain.inc.php ([#6238](https://github.com/librenms/librenms/issues/6238))
* Further speed improvements to port poller ([#6037](https://github.com/librenms/librenms/issues/6037))

#### Devices
* Added Rx levels on Ubiquiti Airfibre ([#6160](https://github.com/librenms/librenms/issues/6160))
* Added detection for Hirschmann Railswitch [#6161](https://github.com/librenms/librenms/issues/6161) ([#6207](https://github.com/librenms/librenms/issues/6207))
* Support for Netscaler SDX appliances ([#6249](https://github.com/librenms/librenms/issues/6249))
* Added discovery of Cyclades ACS ([#6234](https://github.com/librenms/librenms/issues/6234))
* Added additional sensors for Liebert / Vertiv [#5369](https://github.com/librenms/librenms/issues/5369) ([#6123](https://github.com/librenms/librenms/issues/6123))
* Added state detection for Dell TL4k [#2752](https://github.com/librenms/librenms/issues/2752)
* Added support for Cyberpower PDU ([#6013](https://github.com/librenms/librenms/issues/6013))
* Added support for Digipower PDU ([#6014](https://github.com/librenms/librenms/issues/6014))
* Basic Lantronix UDS support ([#6042](https://github.com/librenms/librenms/issues/6042))
* Added detection for more Dell switches ([#6048](https://github.com/librenms/librenms/issues/6048))
* Added HPE Comware Processor Discovery ([#6029](https://github.com/librenms/librenms/issues/6029))
* Added Basic FortiWLC Support ([#6016](https://github.com/librenms/librenms/issues/6016))
* Added support for F5 Traffic Management Module mempool ([#6076](https://github.com/librenms/librenms/pull/6076))
* Added new Planet switch ([#6085](https://github.com/librenms/librenms/pull/6085))
* Added state detection for Dell TL4k ([#6094](https://github.com/librenms/librenms/pull/6094))
* Added extrahop detection ([#6097](https://github.com/librenms/librenms/pull/6097))
* Updated 3com switch detection ([#6114](https://github.com/librenms/librenms/pull/6114))
* Improved APC NetBotz Support ([#6157](https://github.com/librenms/librenms/pull/6157))
* Added state support for HP servers #5113 ([#6124](https://github.com/librenms/librenms/pull/6124))
* Added Coriant support ([#6026](https://github.com/librenms/librenms/pull/6026))
* Basic Zebra Print Server detection ([#6162](https://github.com/librenms/librenms/pull/6162))
* Added state sensor support for RFC1628 UPS ([#6153](https://github.com/librenms/librenms/pull/6153))
* Added APC NetBotz State Sensor Support ([#6167](https://github.com/librenms/librenms/pull/6167))
* Updated Sonus SBC os detection #6241 ([#6243](https://github.com/librenms/librenms/pull/6243))
* Added discovery of Cyclades ACS 6000 ([#6234](https://github.com/librenms/librenms/pull/6234))

#### WebUI
* Do not show disabled devices in alerts list as they stale [#6213](https://github.com/librenms/librenms/issues/6213) ([#6263](https://github.com/librenms/librenms/issues/6263))
* Create correct link for BGP peers [#5958](https://github.com/librenms/librenms/issues/5958)
* Update device overview to not show hostname when certain conditions match [#5984](https://github.com/librenms/librenms/issues/5984) ([#6091](https://github.com/librenms/librenms/issues/6091))
* Display sysnames/hostnames instead of ip addresses [#4155](https://github.com/librenms/librenms/issues/4155)
* Fix BGP Icon for global search [#6031](https://github.com/librenms/librenms/issues/6031)
* Generex: more helpful overview graphs ([#6154](https://github.com/librenms/librenms/issues/6154))
* Added ability to set warning percentage for CPU and mempools ([#5901](https://github.com/librenms/librenms/pull/5901))
* Stop autorefresh on bill edit page #6182 ([#6193](https://github.com/librenms/librenms/pull/6193))
* Allow remember_token to be null ([#6231](https://github.com/librenms/librenms/pull/6231))
* Set the from / to for graphs in the devices list #6262 ([#6264](https://github.com/librenms/librenms/pull/6264))

#### Security
* Stop multiport_bits_separate graphs for showing regardless of auth [#6101](https://github.com/librenms/librenms/issues/6101) ([#6109](https://github.com/librenms/librenms/issues/6109))

#### API
* Expose ports in API requests for bills ([#6069](https://github.com/librenms/librenms/issues/6069))
* Added new route for multiport bit graphs + asn list_bgp filter ([#6129](https://github.com/librenms/librenms/issues/6129))

---

## 1.25
*(2017-02-26)*

#### Features
* Add fail2ban application support ([#5924](https://github.com/librenms/librenms/issues/5924))
* Add additional service checks ([#5941](https://github.com/librenms/librenms/issues/5941))
* Added phpunit db setup tests ([#5594](https://github.com/librenms/librenms/issues/5594))
* Updated rrdcached stats app to support Fedora/Centos ([#5768](https://github.com/librenms/librenms/issues/5768))
* Added Cisco Spark Transport [#3182](https://github.com/librenms/librenms/issues/3182)
* Rancid config file generator ([#5689](https://github.com/librenms/librenms/issues/5689))
* Added Rocket.Chat transport [#5427](https://github.com/librenms/librenms/issues/5427)
* Added SMSEagle transport [#5989](https://github.com/librenms/librenms/pull/5989)
* Added generic hardware rewrite function
* Collect sysDescr and sysObjectID for stats to improve os detection ([#5510](https://github.com/librenms/librenms/issues/5510))
* Update Debian's guestId for VMware ([#5669](https://github.com/librenms/librenms/issues/5669))
* Allow customisation of rrd step/heartbeat when creating new rrd files ([#5947](https://github.com/librenms/librenms/pull/5947))
* Added ability to output graphs as svg ([#5959](https://github.com/librenms/librenms/pull/5959)) 
* Improve ports polling when ports are still down or marked deleted ([#5805](https://github.com/librenms/librenms/pull/5805)) 

#### Bugfixes
* Syslog, pull out pam program source ([#5942](https://github.com/librenms/librenms/issues/5942))
* Load wifi module for sub10 OS ([#5963](https://github.com/librenms/librenms/issues/5963))
* Show sysName on network map when ip_to_sysname enabled ([#5962](https://github.com/librenms/librenms/issues/5962))
* Exim queue graph ([#5945](https://github.com/librenms/librenms/issues/5945))
* Updated qnap sensor code to be more generic [#5910](https://github.com/librenms/librenms/issues/5910) ([#5925](https://github.com/librenms/librenms/issues/5925))
* Remove the non-functional buttons for non-admins in devices/services ([#5856](https://github.com/librenms/librenms/issues/5856))
* Various variables will all be updated if they are blank [#5811](https://github.com/librenms/librenms/issues/5811) ([#5836](https://github.com/librenms/librenms/issues/5836))
* Patch generic_multi graph to fix legend overflow [#5766](https://github.com/librenms/librenms/issues/5766)
* Update lmsensors temp sensors to support 0c values so they do not get removed [#5363](https://github.com/librenms/librenms/issues/5363) ([#5823](https://github.com/librenms/librenms/issues/5823))
* Update macros with / in to have spaces ([#5741](https://github.com/librenms/librenms/issues/5741))
* Added the service parameter to checks that were missing it ([#5753](https://github.com/librenms/librenms/issues/5753))
* Ignore ports where we only have two entries in the array, this signals bad data [#1366](https://github.com/librenms/librenms/issues/1366) ([#5722](https://github.com/librenms/librenms/issues/5722))
* Fixed system temperature from ipmi descr including a space at the end
* Incorrect hostname in the mouse-over of the services in the availability-map [#5734](https://github.com/librenms/librenms/issues/5734)
* Mono theme panel headers black ([#5705](https://github.com/librenms/librenms/issues/5705))
* Make about page toggle look better for zoomed in browsers [#5219](https://github.com/librenms/librenms/issues/5219) ([#5680](https://github.com/librenms/librenms/issues/5680))
* Ignore toners with values -2 which is unknown [#5637](https://github.com/librenms/librenms/issues/5637) ([#5654](https://github.com/librenms/librenms/issues/5654))
* Check lat/lng are numeric rather than !empty [#5585](https://github.com/librenms/librenms/issues/5585) ([#5657](https://github.com/librenms/librenms/issues/5657))
* Fix device edit health update icons ([#5996](https://github.com/librenms/librenms/issues/5996))
* Service module has conflicted configuration files ([#5903](https://github.com/librenms/librenms/issues/5903))
* addhost.php throw proper exception when database add fails ([#5972](https://github.com/librenms/librenms/pull/5972))
* Fix snmpbulkwalk in ifAlias script ([#5547](https://github.com/librenms/librenms/pull/5688))
* Arista watts to dbm conversion ([#5773](https://github.com/librenms/librenms/pull/5773))
* Poll DCN stats using OIDS ([#5785](https://github.com/librenms/librenms/issues/5785))
* Updated qnap sensor code to be more generic ([#5229](https://github.com/librenms/librenms/issues/5229))

#### Documentation
* Update Applications to use correct link for exim-stats ([#5876](https://github.com/librenms/librenms/issues/5876))
* Added info on using munin scripts [#2916](https://github.com/librenms/librenms/issues/2916) ([#5871](https://github.com/librenms/librenms/issues/5871))
* Configuring  SNMPv3 on Linux
* Updated example for using bad_if_regexp [#1878](https://github.com/librenms/librenms/issues/1878) ([#5825](https://github.com/librenms/librenms/issues/5825))
* Update Oxidized integration to show example of SELinux setup
* Update Graylog docs to clarify ssl and hostname use

#### Refactoring
* Centralise device up/down check and use in disco [#5862](https://github.com/librenms/librenms/issues/5862) ([#5897](https://github.com/librenms/librenms/issues/5897))
* Convert Hikvision discovery to yaml ([#5781](https://github.com/librenms/librenms/issues/5781))
* Various Code Cleanup ([#5777](https://github.com/librenms/librenms/issues/5777))
* Updated storing of sensors data to be used in unix-agent [#5904](https://github.com/librenms/librenms/issues/5904)
* Refactor sensor discovery ([#5550](https://github.com/librenms/librenms/pull/5550))

#### Devices
* Add Eaton UPS Charge Sensor ([#6001](https://github.com/librenms/librenms/issues/6001))
* Added CPU and memory for Entera devices [#5974](https://github.com/librenms/librenms/issues/5974)
* Added SEOS CPU discovery [#5917](https://github.com/librenms/librenms/issues/5917)
* Added further detection for CiscoSB (ex Linksys) devices ([#5922](https://github.com/librenms/librenms/issues/5922))
* Updated ibmnos support for Lenovo branded devices [#5894](https://github.com/librenms/librenms/issues/5894) ([#5920](https://github.com/librenms/librenms/issues/5920))
* Initial discovery for Vubiq Haulpass V60s[#5745](https://github.com/librenms/librenms/issues/5745)
* Added further QNAP Turbo NAS detection [#5229](https://github.com/librenms/librenms/issues/5229) ([#5804](https://github.com/librenms/librenms/issues/5804))
* Added support for Fujitsu NAS devices [#5309](https://github.com/librenms/librenms/issues/5309) ([#5816](https://github.com/librenms/librenms/issues/5816))
* Added proc, mem and sensor support for FabricOS [#5295](https://github.com/librenms/librenms/issues/5295) ([#5815](https://github.com/librenms/librenms/issues/5815))
* Added further support for Zynos / Zyxell devices [#5292](https://github.com/librenms/librenms/issues/5292) ([#5814](https://github.com/librenms/librenms/issues/5814))
* Added more Netgear detection [#5789](https://github.com/librenms/librenms/issues/5789)
* Updated DCN serial/hardware/version detection [#5785](https://github.com/librenms/librenms/issues/5785)
* Add F5 Hardware and S/N detection ([#5797](https://github.com/librenms/librenms/issues/5797))
* Improved Xerox discovery ([#5780](https://github.com/librenms/librenms/issues/5780))
* Improved Mikrotik RouterOS and SwOS detection ([#5772](https://github.com/librenms/librenms/issues/5772))
* Improved Pulse Secure detection ([#5770](https://github.com/librenms/librenms/issues/5770))
* Improved Lancom device detection ([#5758](https://github.com/librenms/librenms/issues/5758))
* improved Brocade Network OS detection ([#5756](https://github.com/librenms/librenms/issues/5756))
* improved Dell PowerConnect discovery ([#5761](https://github.com/librenms/librenms/issues/5761))
* Improved HPE Procurve/OfficeConnect discovery ([#5763](https://github.com/librenms/librenms/issues/5763))
* Improved Zyxel IES detection ([#5751](https://github.com/librenms/librenms/issues/5751))
* Improved Fortinet Fortiswitch detection ([#5747](https://github.com/librenms/librenms/issues/5747))
* Improved Brocade Fabric OS detection ([#5746](https://github.com/librenms/librenms/issues/5746))
* Added support for HPE ILO 4 ([#5726](https://github.com/librenms/librenms/issues/5726))
* Added serial, model and version support for HPE MSL ([#5667](https://github.com/librenms/librenms/issues/5667))
* Added support for Kemp Loadbalancers ([#5668](https://github.com/librenms/librenms/issues/5668))
* Additional TPLink JetStream support ([#5909](https://github.com/librenms/librenms/issues/5909))
* Additional detection for Dasan devices ([#5711](https://github.com/librenms/librenms/issue/5711))
* Added initial support for Meinberg LANTIME OS v6 ([#5719](https://github.com/librenms/librenms/pull/5719))
* Added support for Zyxel XS ([#5730](https://github.com/librenms/librenms/issues/5730))
* Added support for Exterity AvediaPlayer ([#5732](https://github.com/librenms/librenms/pull/5732))
* Added detection for OpenGear ([#5744](https://github.com/librenms/librenms/pull/5744))
* Improved support for TiMOS (Alcatel-Lucent) switches ([#5533](https://github.com/librenms/librenms/issues/5533))
* Improved Raritan detection ([#5771](https://github.com/librenms/librenms/pull/5771))
* Added Kyocera Mita support ([#5782](https://github.com/librenms/librenms/pull/5782))
* Added detection for Toshiba TEC printer's ([#5792](https://github.com/librenms/librenms/pull/5792)) 
* Added support for Cyberoam UTM devices ([#5542](https://github.com/librenms/librenms/issues/5542))
* Improved hardware detection for Xerox ([#5831](https://github.com/librenms/librenms/pull/5831))
* Added further sensor support for APC units ([#2732](https://github.com/librenms/librenms/issues/2732))
* Added detction for Mellanox i5035 infiniband switch ([#5887](https://github.com/librenms/librenms/pull/5887))
* Added detection for Powerconnect M8024-k ([#5905](https://github.com/librenms/librenms/issues/5905))
* Added detection for HPE MSA storage ([#5907](https://github.com/librenms/librenms/pull/5907))

#### WebUI
* Update services pages
* New Cumulus Logo ([#5954](https://github.com/librenms/librenms/issues/5954))
* Added link to APs for alert details [#5878](https://github.com/librenms/librenms/issues/5878) ([#5898](https://github.com/librenms/librenms/issues/5898))
* Set the device logo and cell to have a max width ([#5700](https://github.com/librenms/librenms/issues/5700))
* New eventlog severity classification ([#5830](https://github.com/librenms/librenms/issues/5830))
* Update Zyxel image (os/logos to .svg) ([#5855](https://github.com/librenms/librenms/issues/5855))
* Remove the non-functional buttons for non-admins in services ([#5833](https://github.com/librenms/librenms/issues/5833))
* Remove the ability to activate statistics for non-admins ([#5829](https://github.com/librenms/librenms/issues/5829))
* Add SVG logo/os icon for Generex UPS ([#5827](https://github.com/librenms/librenms/issues/5827))
* urldecode device notes [#5110](https://github.com/librenms/librenms/issues/5110) ([#5824](https://github.com/librenms/librenms/issues/5824))
* Replace Ntp with NTP in Apps menu ([#5791](https://github.com/librenms/librenms/issues/5791))
* Adding text logo to HPE logo ([#5728](https://github.com/librenms/librenms/issues/5728))
* Only show sysName once if force_ip_to_sysname is enabled [#5600](https://github.com/librenms/librenms/issues/5600) ([#5656](https://github.com/librenms/librenms/issues/5656))
* Add $config['title_image'] in doc and use it also for login screen ([#5683](https://github.com/librenms/librenms/issues/5683))
* Update create bill link to list bill or list bills depending on if port exists in bills [#5616](https://github.com/librenms/librenms/issues/5616) ([#5653](https://github.com/librenms/librenms/issues/5653))
* Remove ifIndex for ports list but add debug button to show port info ([#5679](https://github.com/librenms/librenms/pull/5679))

#### API
* Added the ability to list devices by location in the api ([#5693](https://github.com/librenms/librenms/issues/5693))
* IP and Port API additions ([#5784](https://github.com/librenms/librenms/pull/5784))
* Limit get_graph_by_port_hostname() to one port and exclude deleted ([#5936](https://github.com/librenms/librenms/pull/5936))
---

## 1.24
*(2017-01-28)*

#### Features
* Basic Draytek Support ([#5625](https://github.com/librenms/librenms/issues/5625))
* Added additional information to Radwin discovery. ([#5591](https://github.com/librenms/librenms/issues/5591))
* Added Serial number support for Mikrotik Devices ([#5590](https://github.com/librenms/librenms/issues/5590))
* Support large vendor logos ([#5573](https://github.com/librenms/librenms/issues/5573))
* Added pre-commit git script to support failing fast
* Added basic recurring maintenance for alerts [#4480](https://github.com/librenms/librenms/issues/4480)
* Added check for if git executable ([#5444](https://github.com/librenms/librenms/issues/5444))
* Oxidized basic config search ([#5333](https://github.com/librenms/librenms/issues/5333))
* Add support for SVG images ([#5275](https://github.com/librenms/librenms/issues/5275))
* Add mysql failed query logging + fixed queries that break ONLY_FULL_GROUP_BY ([#5327](https://github.com/librenms/librenms/issues/5327))

#### Bugfixes
* Logo scalling to support squarish logos ([#5647](https://github.com/librenms/librenms/issues/5647))
* top-devices widget now will honour for ip to sysName config [#5388](https://github.com/librenms/librenms/issues/5388) ([#5643](https://github.com/librenms/librenms/issues/5643))
* Remove duplicate hostnames in arp search box [#5631](https://github.com/librenms/librenms/issues/5631) ([#5641](https://github.com/librenms/librenms/issues/5641))
* Alert templates designer now fixed [#5636](https://github.com/librenms/librenms/issues/5636) ([#5638](https://github.com/librenms/librenms/issues/5638))
* Update ifAlias script to deal with GRE interfaces ([#5546](https://github.com/librenms/librenms/issues/5546))
* Allow invalid hostnames during discovery when discovery_by_ip enabled [#5525](https://github.com/librenms/librenms/issues/5525)
* Stop creating dashboards when user has a default that no longer exists [#5610](https://github.com/librenms/librenms/issues/5610) ([#5613](https://github.com/librenms/librenms/issues/5613))
* Fix Riverbed optimization polling ([#5622](https://github.com/librenms/librenms/issues/5622))
* Html purify init wasn't done always when it was used ([#5626](https://github.com/librenms/librenms/issues/5626))
* Fixed FreeNAS detection [#5518](https://github.com/librenms/librenms/issues/5518) ([#5608](https://github.com/librenms/librenms/issues/5608))
* Add extra check to Junos DOM discovery ([#5582](https://github.com/librenms/librenms/issues/5582))
* HTML Purifier would create tmp caches within the vendor folder, moved to users tmp dir [#5561](https://github.com/librenms/librenms/issues/5561) ([#5596](https://github.com/librenms/librenms/issues/5596))
* PHP 7.1 function usages with too few parameters ([#5588](https://github.com/librenms/librenms/issues/5588))
* Fixed graphs for services not working ([#5569](https://github.com/librenms/librenms/issues/5569))
* Fix broken netstats ip forward polling ([#5575](https://github.com/librenms/librenms/issues/5575))
* Support hosts added by ipv6 without DNS [#5567](https://github.com/librenms/librenms/issues/5567)
* Changing device type now is persistant ([#5529](https://github.com/librenms/librenms/issues/5529))
* Fixed JunOS bgpPeers_cbgp mistakenly removed + better support for mysql strict mode [#5531](https://github.com/librenms/librenms/issues/5531) ([#5536](https://github.com/librenms/librenms/issues/5536))
* Allow overlib_link to accept a null class [#5522](https://github.com/librenms/librenms/issues/5522)
* Stop flattening config options added in config.php  ([#5493](https://github.com/librenms/librenms/issues/5493))
* Stop flattening config options added in config.php ([#5491](https://github.com/librenms/librenms/issues/5491))
* ospf polling, revert set_numeric use ([#5480](https://github.com/librenms/librenms/issues/5480))
* Updated prestiage detection [#5453](https://github.com/librenms/librenms/issues/5453) ([#5470](https://github.com/librenms/librenms/issues/5470))
* Validate suid is set for fping ([#5474](https://github.com/librenms/librenms/issues/5474))
* Add missing ups-apcups application poller [#5428](https://github.com/librenms/librenms/issues/5428)
* Linux detect by oid too ([#5439](https://github.com/librenms/librenms/issues/5439))
* APC -1 Humidity Sensor Value [#5325](https://github.com/librenms/librenms/issues/5325) ([#5375](https://github.com/librenms/librenms/issues/5375))
* Fix sql errors due to incorrect cef table name [#5362](https://github.com/librenms/librenms/issues/5362)
* Detection blank or unknown device types and update [#5412](https://github.com/librenms/librenms/issues/5412) ([#5414](https://github.com/librenms/librenms/issues/5414))
* Unifi switch detection ([#5407](https://github.com/librenms/librenms/issues/5407))
* Detect device type changes and update [#5271](https://github.com/librenms/librenms/issues/5271) ([#5390](https://github.com/librenms/librenms/issues/5390))
* Typo in IBM icon definition ([#5395](https://github.com/librenms/librenms/issues/5395))
* Don't support unifi clients that don't report data ([#5383](https://github.com/librenms/librenms/issues/5383))
* Fix Oxidized Config Search Output ([#5382](https://github.com/librenms/librenms/issues/5382))
* Added support for autotls in mail transport [#5314](https://github.com/librenms/librenms/issues/5314)
* validate mysql queries ([#5365](https://github.com/librenms/librenms/issues/5365))
* OS type and group not being set ([#5357](https://github.com/librenms/librenms/issues/5357))
* Stop logging when a vm no longer is on the host being polled ([#5346](https://github.com/librenms/librenms/issues/5346))
* Dark/mono logo was incorrect ([#5342](https://github.com/librenms/librenms/issues/5342))
* Specify specific mkdocs version ([#5339](https://github.com/librenms/librenms/issues/5339))
* Correct icon for ciscosb ([#5331](https://github.com/librenms/librenms/issues/5331))
* Correction on addHost function to handle the force_add parameter in api ([#5329](https://github.com/librenms/librenms/issues/5329))
* Mikrotik cpu detection ([#5306](https://github.com/librenms/librenms/issues/5306))
* Do not use generic icon by default ([#5303](https://github.com/librenms/librenms/issues/5303))
* Update jpgraph source file to remove check for imageantialias() [#5282](https://github.com/librenms/librenms/issues/5282) ([#5284](https://github.com/librenms/librenms/issues/5284))
* APC PDU2 Voltage Discovery ([#5276](https://github.com/librenms/librenms/issues/5276))
* Empty mac adds an entry to the arp table ([#5270](https://github.com/librenms/librenms/issues/5270))
* Restrict inventory api calls to the device requested ([#5267](https://github.com/librenms/librenms/issues/5267))

#### Documentation
* Mikrotik SNMP configuration example ([#5628](https://github.com/librenms/librenms/issues/5628))
* Add logrotate config and update install docs ([#5520](https://github.com/librenms/librenms/issues/5520))
* Added an example hardware doc for people to show what they have ([#5532](https://github.com/librenms/librenms/issues/5532))
* Added faq info on realStorageUnits ([#5513](https://github.com/librenms/librenms/issues/5513))
* Update Installation-Ubuntu-1604-Nginx.md to remove default nginx site config
* Updated RRDCached doc for Debain Jessie installation ([#5380](https://github.com/librenms/librenms/issues/5380))
* Updated os update application
* Added more info in to the github issue template ([#5370](https://github.com/librenms/librenms/issues/5370))
* Update Installation-Ubuntu-1604-Nginx.md to correct snmpd.conf location
* Update installation documentation on Ubuntu 16.x and CentOS 7 to use systemd ([#5324](https://github.com/librenms/librenms/issues/5324))
* Update Centos 7 nginx install steps ([#5316](https://github.com/librenms/librenms/issues/5316))
* Added section on smokeping and rrdcached use

#### Refactoring
* Update collectd functions.php to use non-conflict rrd_info function [#5478](https://github.com/librenms/librenms/issues/5478) ([#5642](https://github.com/librenms/librenms/issues/5642))
* Updated some default disco/poller modules to be disabled/enabled ([#5564](https://github.com/librenms/librenms/issues/5564))
* Added config option for database port ([#5517](https://github.com/librenms/librenms/issues/5517))
* Move HTMLPurifier init to init.php so we only create one object. ([#5601](https://github.com/librenms/librenms/issues/5601))
* Disable unused Cisco WAAS modules ([#5574](https://github.com/librenms/librenms/issues/5574))
* Some more os definition changes ([#5527](https://github.com/librenms/librenms/issues/5527))
* Changed Redback to SEOS, and added logo and temperature discovery [#5181](https://github.com/librenms/librenms/issues/5181)
* Move some os from linux and freebsd discovery files to yaml ([#5429](https://github.com/librenms/librenms/issues/5429))
* MySQL strict and query fixes ([#5338](https://github.com/librenms/librenms/issues/5338))
* Sophos discovery to yaml ([#5416](https://github.com/librenms/librenms/issues/5416))
* Move include based discovery after yaml discovery ([#5401](https://github.com/librenms/librenms/issues/5401))
* Moved simple os discovery into yaml config ([#5313](https://github.com/librenms/librenms/issues/5313))
* Move mib based polling into yaml config files ([#5234](https://github.com/librenms/librenms/issues/5234))
* Use Composer to manage php dependencies ([#5216](https://github.com/librenms/librenms/issues/5216))

#### Devices
* Added further support for Canon printers [#5637](https://github.com/librenms/librenms/issues/5637) ([#5650](https://github.com/librenms/librenms/issues/5650))
* Updated generex ups support [#5634](https://github.com/librenms/librenms/issues/5634) ([#5640](https://github.com/librenms/librenms/issues/5640))
* Added detection for Exinda [#5297](https://github.com/librenms/librenms/issues/5297) ([#5605](https://github.com/librenms/librenms/issues/5605))
* Added additional sensor support for PowerWalker devices [#5080](https://github.com/librenms/librenms/issues/5080) ([#5552](https://github.com/librenms/librenms/issues/5552))
* Added support for Brocade 200E ([#5617](https://github.com/librenms/librenms/issues/5617))
* Improve CiscoSB detection [#5511](https://github.com/librenms/librenms/issues/5511)
* Added further detection for DCN devices [#5519](https://github.com/librenms/librenms/issues/5519) ([#5609](https://github.com/librenms/librenms/issues/5609))
* Added support for Zhone MXK devices [#5554](https://github.com/librenms/librenms/issues/5554) ([#5611](https://github.com/librenms/librenms/issues/5611))
* Added more detection for Procurve devices [#5422](https://github.com/librenms/librenms/issues/5422) ([#5607](https://github.com/librenms/librenms/issues/5607))
* Updated detection for Dasan NOS devices [#5359](https://github.com/librenms/librenms/issues/5359) ([#5606](https://github.com/librenms/librenms/issues/5606))
* Added support MGEUPS EX2200 [#3364](https://github.com/librenms/librenms/issues/3364) ([#5602](https://github.com/librenms/librenms/issues/5602))
* Improve Cisco ISE detection ([#5578](https://github.com/librenms/librenms/issues/5578))
* Updated akcp discovery definition [#5396](https://github.com/librenms/librenms/issues/5396) ([#5501](https://github.com/librenms/librenms/issues/5501))
* Add detection for radwin devices
* Update zywall and zyxelnwa detection [#5343](https://github.com/librenms/librenms/issues/5343)
* Added support for Ericsson ES devices [#5195](https://github.com/librenms/librenms/issues/5195) ([#5479](https://github.com/librenms/librenms/issues/5479))
* Add support for DocuPrint M225 ([#5484](https://github.com/librenms/librenms/issues/5484))
* Added Dell B5460dn and B3460dn printer support ([#5482](https://github.com/librenms/librenms/issues/5482))
* Added signal support for RouterOS ([#5498](https://github.com/librenms/librenms/issues/5498))
* Added additional sensor support for Huawei VRP [#4279](https://github.com/librenms/librenms/issues/4279)
* Added loadbalancer information from F5 LTM ([#5205](https://github.com/librenms/librenms/issues/5205))
* APC Environmental monitoring units [#5140](https://github.com/librenms/librenms/issues/5140)
* Add support for KTI switches ([#5413](https://github.com/librenms/librenms/issues/5413))
* Detect all CTC Union devices ([#5489](https://github.com/librenms/librenms/issues/5489))
* Add addition riverbed information [#5170](https://github.com/librenms/librenms/issues/5170)
* Added support for CTC Union devices ([#5402](https://github.com/librenms/librenms/issues/5402))
* Add wifi clients for Deliberant DLB APC Button, DLB APC Button AF and DLB APC 2mi [#5456](https://github.com/librenms/librenms/issues/5456)
* Added Tomato and AsusWRT-Merlin OS [#5254](https://github.com/librenms/librenms/issues/5254) ([#5398](https://github.com/librenms/librenms/issues/5398))
* Detect Fiberhome AN5516-04B
* Improve Checkpoint Discovery ([#5334](https://github.com/librenms/librenms/issues/5334))
* APC in-row coolers
* Added additional detection for Dell UPS ([#5322](https://github.com/librenms/librenms/issues/5322))
* added more support for dasan-nos ([#5298](https://github.com/librenms/librenms/issues/5298))
* Added support for Dasan NOS [#5179](https://github.com/librenms/librenms/issues/5179) + disco change ([#5255](https://github.com/librenms/librenms/issues/5255))
* Edge core OS ECS3510-52T ([#5286](https://github.com/librenms/librenms/issues/5286))
* Basic Dell UPS Support [#5258](https://github.com/librenms/librenms/issues/5258)
* Basic Fujitsu DX Support [#5260](https://github.com/librenms/librenms/issues/5260)

#### WebUI
* Final Font Awesome conversion ([#5652](https://github.com/librenms/librenms/issues/5652))
* Added ?ver=X to LibreNMS style sheets so we can force refreshes in future ([#5651](https://github.com/librenms/librenms/issues/5651))
* New generic os SVG icon ([#5645](https://github.com/librenms/librenms/issues/5645))
* New LibreNMS logo assets ([#5629](https://github.com/librenms/librenms/issues/5629))
* Center device icons.  Keep device actions at two rows ([#5627](https://github.com/librenms/librenms/issues/5627))
* Additional Font Awesome icons ([#5572](https://github.com/librenms/librenms/issues/5572))
* Allows one to view a map of the SNMP location set for a device ([#5495](https://github.com/librenms/librenms/issues/5495))
* Update health menu icons
* Updated icons to use Font Awesome ([#5468](https://github.com/librenms/librenms/issues/5468))
* Allow billing to use un-auth graphs ([#5449](https://github.com/librenms/librenms/issues/5449))
* Update Font Awesome to 4.7.0 ([#5476](https://github.com/librenms/librenms/issues/5476))
* Update add/edit user page to use their instead of his [#5457](https://github.com/librenms/librenms/issues/5457) ([#5460](https://github.com/librenms/librenms/issues/5460))
* Fix Ports Table AdminDown Search ([#5426](https://github.com/librenms/librenms/issues/5426))
* Disabled editing device notes for non-admin users ([#5341](https://github.com/librenms/librenms/issues/5341))
* Small Best Practice Fixes

---

## 1.23
*(2017-01-01)*

#### Features
* Add nagios check_procs support ([#5214](https://github.com/librenms/librenms/issues/5214))
* Added support for sending email notifications to default_contact if updating fails ([#5026](https://github.com/librenms/librenms/issues/5026))
* Enable override of $config values set in includes/definitions.inc.php ([#5096](https://github.com/librenms/librenms/issues/5096))
* Add APC UPS battery replacement status [#5088](https://github.com/librenms/librenms/issues/5088)

#### Bugfixes
* APC PDU2 Voltage Discovery ([#5276](https://github.com/librenms/librenms/issues/5276))
* Empty mac adds an entry to the arp table ([#5270](https://github.com/librenms/librenms/issues/5270))
* Restrict inventory api calls to the device requested ([#5267](https://github.com/librenms/librenms/issues/5267))
* Update any IP fields using inet6_ntop()  [#5207](https://github.com/librenms/librenms/issues/5207)
* Fixed passing of data to load_all_os() function ([#5235](https://github.com/librenms/librenms/issues/5235))
* Support columns filter in get_port_stats_by_port_hostname api call ([#5230](https://github.com/librenms/librenms/issues/5230))
* Restore usage of -i -n in polling ([#5228](https://github.com/librenms/librenms/issues/5228))
* Empty routing menu where only CEF is present ([#5225](https://github.com/librenms/librenms/issues/5225))
* Added service params for check_smtp ([#5223](https://github.com/librenms/librenms/issues/5223))
* Misc warning fixes in mib polling ([#5222](https://github.com/librenms/librenms/issues/5222))
* Added service params for check_imap ([#5213](https://github.com/librenms/librenms/issues/5213))
* Execute commands using the numeric conventions of the C locale. ([#5192](https://github.com/librenms/librenms/issues/5192))
* Remove usage of -CI, it is not allowed for snmpbulkwalk [#5164](https://github.com/librenms/librenms/issues/5164)
* Update F5 fanspeed discovery ([#5200](https://github.com/librenms/librenms/issues/5200))
* Fix state_indexes for state overview sensors ([#5191](https://github.com/librenms/librenms/issues/5191))
* Better Cisco hardware formatting ([#5184](https://github.com/librenms/librenms/issues/5184))
* Cisco hardware name detection ([#5167](https://github.com/librenms/librenms/issues/5167))
* Changed sql query for state sensors on device overview page to ignore null sensor_id ([#5180](https://github.com/librenms/librenms/issues/5180))
* daily.sh install path ([#5152](https://github.com/librenms/librenms/issues/5152))
* Cleanup printing ifAlias ([#4874](https://github.com/librenms/librenms/issues/4874))
* Fixed broken http-auth auth module [#5053](https://github.com/librenms/librenms/issues/5053) ([#5146](https://github.com/librenms/librenms/issues/5146))
* Fix get_port_stats_by_port_hostname() to only return non-deleted ports [#5131](https://github.com/librenms/librenms/issues/5131)
* Stop openbsd using snmpEngineTime ([#5111](https://github.com/librenms/librenms/issues/5111))
* Update raspberrypi sensor discover to check for sensor data ([#5114](https://github.com/librenms/librenms/issues/5114))
* Add check for differently named Cisco Power sensor ([#5119](https://github.com/librenms/librenms/issues/5119))
* Ability to detect Cisco ASA version when polling a security context ([#5098](https://github.com/librenms/librenms/issues/5098))
* Fixed setting userlevel for  LDAP auth [#5090](https://github.com/librenms/librenms/issues/5090)
* Arp-table uses array_column() breaking discovery on php <=5.4 ([#5099](https://github.com/librenms/librenms/issues/5099))
* Allow html but not script, head and html tags in notes widget [#4898](https://github.com/librenms/librenms/issues/4898) ([#5006](https://github.com/librenms/librenms/issues/5006))

#### Documentation
* Updated rrdcached docs to include Ubuntu 16.x ([#5263](https://github.com/librenms/librenms/issues/5263))
* Updated Oxidized.md ([#5224](https://github.com/librenms/librenms/issues/5224))
* Removed mailing list in various places + small improvements to docs ([#5154](https://github.com/librenms/librenms/issues/5154))
* Added Remote monitoring using tinc VPN ([#5122](https://github.com/librenms/librenms/issues/5122))
* Added documentation on securing rrdcached. ([#5093](https://github.com/librenms/librenms/issues/5093))
* Adding how to configure HPE 3PAR to documentation ([#5087](https://github.com/librenms/librenms/issues/5087))
* Fixed example timezones ([#5083](https://github.com/librenms/librenms/issues/5083))

#### Refactoring
* Removed and moved more mibs ([#5232](https://github.com/librenms/librenms/issues/5232))
* Move OS definitions into yaml files ([#5189](https://github.com/librenms/librenms/issues/5189))
* Updated Ups nut support
* Mibs E-G ([#5190](https://github.com/librenms/librenms/issues/5190))
* Moved / deleted mibs A-D ([#5173](https://github.com/librenms/librenms/issues/5173))
* Updated location of mibs starting with S ([#5142](https://github.com/librenms/librenms/issues/5142))
* Update some devices to disable poller/disco modules by default ([#5010](https://github.com/librenms/librenms/issues/5010))
* More Cisco ASA Polling Performance Improvements ([#5104](https://github.com/librenms/librenms/issues/5104))
* Moved mibs T-U (or removed) where possible ([#5013](https://github.com/librenms/librenms/issues/5013))

#### Devices
* Lancom wireless devices ([#5237](https://github.com/librenms/librenms/issues/5237))
* Added additional detection for Cisco WAP 321 [#5172](https://github.com/librenms/librenms/issues/5172) ([#5248](https://github.com/librenms/librenms/issues/5248))
* Added support for TPLink JetStream [#5194](https://github.com/librenms/librenms/issues/5194) ([#5249](https://github.com/librenms/librenms/issues/5249))
* Added HPE MSL support [#5072](https://github.com/librenms/librenms/issues/5072) ([#5239](https://github.com/librenms/librenms/issues/5239))
* Added support for DCN switches [#5031](https://github.com/librenms/librenms/issues/5031) ([#5238](https://github.com/librenms/librenms/issues/5238))
* Added support for Cisco APIC devices ([#5236](https://github.com/librenms/librenms/issues/5236))
* Zyxel ZyWALL Improvement [#5185](https://github.com/librenms/librenms/issues/5185)
* Added CPU detection for Zyxel GS2200-24 ([#5218](https://github.com/librenms/librenms/issues/5218))
* removed all references to 'multimatics' and instead added generex OS
* Added additional support for F5 BigIP LTM objects
* Added additional support for Synology dsm ([#5145](https://github.com/librenms/librenms/issues/5145))
* Add OS Detection support for Alcatel-Lucent/Nokia ESS 7450 Ethernet service switch [#5187](https://github.com/librenms/librenms/issues/5187)
* Added Bluecoat ProxySG Support ([#5165](https://github.com/librenms/librenms/issues/5165))
* Added support for Arris CMTS ([#5143](https://github.com/librenms/librenms/issues/5143))
* Added os Discovery for Brocade NOS V4.X and below. ([#5158](https://github.com/librenms/librenms/issues/5158))
* Added support for Mirth OS [#2639](https://github.com/librenms/librenms/issues/2639)
* Juniper SA support [#4328](https://github.com/librenms/librenms/issues/4328)
* Added support for Zyxel MES3528 ([#5120](https://github.com/librenms/librenms/issues/5120))
* Add more Edge core switches
* Add support for Ubiquiti EdgePoint Switch models ([#5079](https://github.com/librenms/librenms/issues/5079))

#### WebUI
* Standardised all rowCount parameters for tables ([#5067](https://github.com/librenms/librenms/issues/5067))

#### Security
* Update PHPMailer to version 5.2.19 ([#5253](https://github.com/librenms/librenms/issues/5253))

##[2013 Changelog](Changelogs/2013.md)

##[2014 Changelog](Changelogs/2014.md)

##[2015 Changelog](Changelogs/2015.md)

##[2016 Changelog](Changelogs/2016.md)