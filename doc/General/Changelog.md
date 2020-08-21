## 1.66
*(2020-07-30)*

A big thank you to the following 28 contributors this last month:

  - [murrant](https://github.com/murrant) (17)
  - [Jellyfrog](https://github.com/Jellyfrog) (8)
  - [SourceDoctor](https://github.com/SourceDoctor) (5)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (4)
  - [peelman](https://github.com/peelman) (4)
  - [cppmonkey](https://github.com/cppmonkey) (2)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (2)
  - [arrmo](https://github.com/arrmo) (2)
  - [seros1521](https://github.com/seros1521) (2)
  - [hanserasmus](https://github.com/hanserasmus) (2)
  - [NotARobotDude](https://github.com/NotARobotDude) (1)
  - [allwaysoft](https://github.com/allwaysoft) (1)
  - [Oirbsiu](https://github.com/Oirbsiu) (1)
  - [penfold1972](https://github.com/penfold1972) (1)
  - [cwispy](https://github.com/cwispy) (1)
  - [hrtrd](https://github.com/hrtrd) (1)
  - [louis-oui](https://github.com/louis-oui) (1)
  - [ppasserini](https://github.com/ppasserini) (1)
  - [kleinem86](https://github.com/kleinem86) (1)
  - [javichumellamo](https://github.com/javichumellamo) (1)
  - [CirnoT](https://github.com/CirnoT) (1)
  - [awein](https://github.com/awein) (1)
  - [Wooboy](https://github.com/Wooboy) (1)
  - [AnaelMobilia](https://github.com/AnaelMobilia) (1)
  - [twelch24](https://github.com/twelch24) (1)
  - [hp197](https://github.com/hp197) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [craig-nokia](https://github.com/craig-nokia) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (28)
  - [murrant](https://github.com/murrant) (26)
  - [SourceDoctor](https://github.com/SourceDoctor) (11)
  - [githonk](https://github.com/githonk) (1)
  - [kkrumm1](https://github.com/kkrumm1) (1)
  - [laf](https://github.com/laf) (1)

#### Security
* Add permission support to Oxidized config search ([#11928](https://github.com/librenms/librenms/pull/11928)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix sql injection ([#11923](https://github.com/librenms/librenms/pull/11923)) - [murrant](https://github.com/murrant)
* Fix SQL injections in ajax_table.php ([#11920](https://github.com/librenms/librenms/pull/11920)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sanitize graph title input ([#11919](https://github.com/librenms/librenms/pull/11919)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add more types to settings page ([#11918](https://github.com/librenms/librenms/pull/11918)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix settings access ([#11915](https://github.com/librenms/librenms/pull/11915)) - [murrant](https://github.com/murrant)

#### Device
* Fix USW detection again ([#11978](https://github.com/librenms/librenms/pull/11978)) - [murrant](https://github.com/murrant)
* Add Canon iPF series ([#11959](https://github.com/librenms/librenms/pull/11959)) - [Wooboy](https://github.com/Wooboy)
* Added basic EndRun support ([#11932](https://github.com/librenms/librenms/pull/11932)) - [hanserasmus](https://github.com/hanserasmus)
* QNAP NAS - Added state rules to collection ([#11931](https://github.com/librenms/librenms/pull/11931)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* QNAP NAS - Added RAID states for each volume. ([#11930](https://github.com/librenms/librenms/pull/11930)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* CBQoS improvement ([#11926](https://github.com/librenms/librenms/pull/11926)) - [seros1521](https://github.com/seros1521)
* Dell included a typo in their sysDescr ([#11917](https://github.com/librenms/librenms/pull/11917)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Added support for Fortinet FortiVoice devices ([#11914](https://github.com/librenms/librenms/pull/11914)) - [cppmonkey](https://github.com/cppmonkey)
* Support NetMan 204 ([#11913](https://github.com/librenms/librenms/pull/11913)) - [javichumellamo](https://github.com/javichumellamo)
* Added support for Eltek SmartPack2 Touch ([#11909](https://github.com/librenms/librenms/pull/11909)) - [cppmonkey](https://github.com/cppmonkey)
* Add Divisors to Eaton Matrix sensors ([#11906](https://github.com/librenms/librenms/pull/11906)) - [peelman](https://github.com/peelman)
* Add support SNR Memory pool, dBm, voltage, current, fan speed and status ([#11888](https://github.com/librenms/librenms/pull/11888)) - [hrtrd](https://github.com/hrtrd)
* Add support for Aten PE8216 PDU ([#11887](https://github.com/librenms/librenms/pull/11887)) - [cwispy](https://github.com/cwispy)
* Ciena SDS ([#11857](https://github.com/librenms/librenms/pull/11857)) - [penfold1972](https://github.com/penfold1972)
* Cisco PW: Correct interface names in response to the SNMP query of cpwVcName ([#11851](https://github.com/librenms/librenms/pull/11851)) - [Oirbsiu](https://github.com/Oirbsiu)
* Initial Release, wireless sensor support for Openwrt ([#11768](https://github.com/librenms/librenms/pull/11768)) - [arrmo](https://github.com/arrmo)
* OS detection for Dell PowerVault MD arrays ([#11509](https://github.com/librenms/librenms/pull/11509)) - [TheMysteriousX](https://github.com/TheMysteriousX)

#### Webui
* Fix encoded html entities in page title ([#11979](https://github.com/librenms/librenms/pull/11979)) - [murrant](https://github.com/murrant)
* Fix netflow links ([#11971](https://github.com/librenms/librenms/pull/11971)) - [murrant](https://github.com/murrant)
* Convert blade to panel component ([#11957](https://github.com/librenms/librenms/pull/11957)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix wrong str::finish argument order ([#11955](https://github.com/librenms/librenms/pull/11955)) - [hp197](https://github.com/hp197)
* Show SysName in Availability Widget ([#11953](https://github.com/librenms/librenms/pull/11953)) - [SourceDoctor](https://github.com/SourceDoctor)
* Convert device notes to blade ([#11952](https://github.com/librenms/librenms/pull/11952)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sort Neighbors by ifName, not bei ifAlias ([#11951](https://github.com/librenms/librenms/pull/11951)) - [SourceDoctor](https://github.com/SourceDoctor)
* Make sure base_url always ends with / ([#11949](https://github.com/librenms/librenms/pull/11949)) - [murrant](https://github.com/murrant)
* Change text from black to white availability-map ([#11946](https://github.com/librenms/librenms/pull/11946)) - [NotARobotDude](https://github.com/NotARobotDude)
* Show Location on Poller Log ([#11945](https://github.com/librenms/librenms/pull/11945)) - [SourceDoctor](https://github.com/SourceDoctor)
* LLDP Neighbour List - alphabetic sort ([#11944](https://github.com/librenms/librenms/pull/11944)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix missing Munin controller tab definition ([#11943](https://github.com/librenms/librenms/pull/11943)) - [CirnoT](https://github.com/CirnoT)
* Correct link for Rule, Location (Alerts, and several other pages) ([#11937](https://github.com/librenms/librenms/pull/11937)) - [arrmo](https://github.com/arrmo)
* Enhance Alert History Table View ([#11936](https://github.com/librenms/librenms/pull/11936)) - [SourceDoctor](https://github.com/SourceDoctor)
* Black legend text in dark theme ([#11933](https://github.com/librenms/librenms/pull/11933)) - [twelch24](https://github.com/twelch24)
* Fix users that set a non-array for cors ([#11921](https://github.com/librenms/librenms/pull/11921)) - [murrant](https://github.com/murrant)
* Restore device alert tab ([#11897](https://github.com/librenms/librenms/pull/11897)) - [murrant](https://github.com/murrant)
* Laravel 7.x Shift ([#11676](https://github.com/librenms/librenms/pull/11676)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Graphs
* Remove legacy code and fix missing device graphs ([#11950](https://github.com/librenms/librenms/pull/11950)) - [murrant](https://github.com/murrant)
* Refresh device_bits graph; align column headers, use wider descriptio… ([#11922](https://github.com/librenms/librenms/pull/11922)) - [peelman](https://github.com/peelman)
* Fix graphing with generic_v3_multiline_float ([#11916](https://github.com/librenms/librenms/pull/11916)) - [awein](https://github.com/awein)
* Mult graphs bits spacing ([#11907](https://github.com/librenms/librenms/pull/11907)) - [peelman](https://github.com/peelman)

#### Api
* CORS settings in webui ([#11912](https://github.com/librenms/librenms/pull/11912)) - [murrant](https://github.com/murrant)

#### Alerting
* Added Proxy support for Api Transport ([#11968](https://github.com/librenms/librenms/pull/11968)) - [kleinem86](https://github.com/kleinem86)
* Add alert rule error on invert map selected but no selection in device, group or location list ([#11894](https://github.com/librenms/librenms/pull/11894)) - [louis-oui](https://github.com/louis-oui)

#### Discovery
* Less strict sysName matching for neighbor discovery ([#11804](https://github.com/librenms/librenms/pull/11804)) - [seros1521](https://github.com/seros1521)

#### Polling
* Fix bug when timeout exceeded ([#11934](https://github.com/librenms/librenms/pull/11934)) - [murrant](https://github.com/murrant)
* Selected Port Polling, only try to optimize polling if enabled by global setting ([#11908](https://github.com/librenms/librenms/pull/11908)) - [peelman](https://github.com/peelman)
* Added check for incorrect ifConnectorPresent truth values, if invalid… ([#11634](https://github.com/librenms/librenms/pull/11634)) - [craig-nokia](https://github.com/craig-nokia)

#### Bug
* Fix latency.blade.php to show smokeping integration ([#11980](https://github.com/librenms/librenms/pull/11980)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Dispatch Service Fix maintenance issues ([#11973](https://github.com/librenms/librenms/pull/11973)) - [murrant](https://github.com/murrant)
* Fix .env path in daily.sh ([#11972](https://github.com/librenms/librenms/pull/11972)) - [murrant](https://github.com/murrant)
* Fix QNAP os polling ([#11938](https://github.com/librenms/librenms/pull/11938)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fix error about ob not started sometimes in migration ([#11927](https://github.com/librenms/librenms/pull/11927)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Refactor
* Simpler Python requirements check ([#11939](https://github.com/librenms/librenms/pull/11939)) - [murrant](https://github.com/murrant)

#### Documentation
* Update RRDCached.md ([#11967](https://github.com/librenms/librenms/pull/11967)) - [hanserasmus](https://github.com/hanserasmus)
* Update Smokeping.md ([#11956](https://github.com/librenms/librenms/pull/11956)) - [AnaelMobilia](https://github.com/AnaelMobilia)
* Fix missing doc OpenWRT ([#11924](https://github.com/librenms/librenms/pull/11924)) - [murrant](https://github.com/murrant)

#### Translation
* Simplify Chinese lang Translation ([#11905](https://github.com/librenms/librenms/pull/11905)) - [allwaysoft](https://github.com/allwaysoft)
* Update italian translation ([#11901](https://github.com/librenms/librenms/pull/11901)) - [ppasserini](https://github.com/ppasserini)

#### Tests
* Fix route tests ([#11898](https://github.com/librenms/librenms/pull/11898)) - [murrant](https://github.com/murrant)

#### Dependencies
* Bump lodash from 4.17.15 to 4.17.19 ([#11942](https://github.com/librenms/librenms/pull/11942)) - [dependabot](https://github.com/apps/dependabot)


## 1.65
*(2020-07-03)*

A big thank you to the following 42 contributors this last month:

  - [murrant](https://github.com/murrant) (46)
  - [SourceDoctor](https://github.com/SourceDoctor) (24)
  - [PipoCanaja](https://github.com/PipoCanaja) (5)
  - [Jellyfrog](https://github.com/Jellyfrog) (5)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (4)
  - [Negatifff](https://github.com/Negatifff) (4)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (2)
  - [tamirhad](https://github.com/tamirhad) (2)
  - [willhseitz](https://github.com/willhseitz) (2)
  - [AnaelMobilia](https://github.com/AnaelMobilia) (2)
  - [yon2004](https://github.com/yon2004) (2)
  - [pepperoni-pi](https://github.com/pepperoni-pi) (2)
  - [jasoncheng7115](https://github.com/jasoncheng7115) (1)
  - [ppasserini](https://github.com/ppasserini) (1)
  - [ajsiersema](https://github.com/ajsiersema) (1)
  - [ZoLuSs](https://github.com/ZoLuSs) (1)
  - [cjwbath](https://github.com/cjwbath) (1)
  - [joshuabaird](https://github.com/joshuabaird) (1)
  - [louis-oui](https://github.com/louis-oui) (1)
  - [footstep86](https://github.com/footstep86) (1)
  - [yac01](https://github.com/yac01) (1)
  - [robje](https://github.com/robje) (1)
  - [ryanheffernan](https://github.com/ryanheffernan) (1)
  - [karrots](https://github.com/karrots) (1)
  - [Martin22](https://github.com/Martin22) (1)
  - [dupondje](https://github.com/dupondje) (1)
  - [opalivan](https://github.com/opalivan) (1)
  - [arrmo](https://github.com/arrmo) (1)
  - [moisseev](https://github.com/moisseev) (1)
  - [XxPatrickxX](https://github.com/XxPatrickxX) (1)
  - [kuhball](https://github.com/kuhball) (1)
  - [rkandilarov](https://github.com/rkandilarov) (1)
  - [hanserasmus](https://github.com/hanserasmus) (1)
  - [systeembeheer-rtvu](https://github.com/systeembeheer-rtvu) (1)
  - [slashdoom](https://github.com/slashdoom) (1)
  - [gardar](https://github.com/gardar) (1)
  - [vitalisator](https://github.com/vitalisator) (1)
  - [sorano](https://github.com/sorano) (1)
  - [Derova](https://github.com/Derova) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [Munzy](https://github.com/Munzy) (1)
  - [nepeat](https://github.com/nepeat) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (48)
  - [SourceDoctor](https://github.com/SourceDoctor) (27)
  - [Jellyfrog](https://github.com/Jellyfrog) (20)
  - [PipoCanaja](https://github.com/PipoCanaja) (11)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (6)
  - [kkrumm1](https://github.com/kkrumm1) (6)
  - [f0o](https://github.com/f0o) (3)
  - [laf](https://github.com/laf) (2)
  - [LEV82](https://github.com/LEV82) (1)
  - [hanserasmus](https://github.com/hanserasmus) (1)
  - [sorano](https://github.com/sorano) (1)

#### Feature
* Devices List: Sort downtime and uptime ([#11829](https://github.com/librenms/librenms/pull/11829)) - [murrant](https://github.com/murrant)
* Skip_value can check OID existance ([#11822](https://github.com/librenms/librenms/pull/11822)) - [PipoCanaja](https://github.com/PipoCanaja)
* New Web Installer ([#11810](https://github.com/librenms/librenms/pull/11810)) - [murrant](https://github.com/murrant)
* Device Availability Calculation ([#11784](https://github.com/librenms/librenms/pull/11784)) - [SourceDoctor](https://github.com/SourceDoctor)
* Dispatcher Service settings ([#11760](https://github.com/librenms/librenms/pull/11760)) - [murrant](https://github.com/murrant)
* Improve migration to release update channel ([#11669](https://github.com/librenms/librenms/pull/11669)) - [murrant](https://github.com/murrant)

#### Security
* Prevent unauthorized access to device graphs ([#11878](https://github.com/librenms/librenms/pull/11878)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Device
* Remove the last node from the arbor sysObjectId ([#11890](https://github.com/librenms/librenms/pull/11890)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Move qnap to yaml discovery + extended discovery ([#11882](https://github.com/librenms/librenms/pull/11882)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Added support for Integra E radios ([#11871](https://github.com/librenms/librenms/pull/11871)) - [Derova](https://github.com/Derova)
* Add Riello Netman 204 support ([#11856](https://github.com/librenms/librenms/pull/11856)) - [sorano](https://github.com/sorano)
* Cirpack states mapping ([#11855](https://github.com/librenms/librenms/pull/11855)) - [vitalisator](https://github.com/vitalisator)
* Jacarta interSeptor support (environmental monitoring device) ([#11826](https://github.com/librenms/librenms/pull/11826)) - [systeembeheer-rtvu](https://github.com/systeembeheer-rtvu)
* Make IPv6 discovery work on JunOS ([#11825](https://github.com/librenms/librenms/pull/11825)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Gigamon GigaVUE basic support ([#11824](https://github.com/librenms/librenms/pull/11824)) - [hanserasmus](https://github.com/hanserasmus)
* Add new hardware model for Eltek/enexus ([#11809](https://github.com/librenms/librenms/pull/11809)) - [willhseitz](https://github.com/willhseitz)
* Arista sometimes returns NaN value when polling ([#11800](https://github.com/librenms/librenms/pull/11800)) - [tamirhad](https://github.com/tamirhad)
* ArubaOS-CX Identification ([#11792](https://github.com/librenms/librenms/pull/11792)) - [XxPatrickxX](https://github.com/XxPatrickxX)
* Add Edgecore ES3526XA OID ([#11791](https://github.com/librenms/librenms/pull/11791)) - [moisseev](https://github.com/moisseev)
* Add Packetlight PL2000 support ([#11782](https://github.com/librenms/librenms/pull/11782)) - [opalivan](https://github.com/opalivan)
* Add Edgecore ECS4100-28T OID ([#11778](https://github.com/librenms/librenms/pull/11778)) - [Negatifff](https://github.com/Negatifff)
* Imcopower - Fixed state in imcopower-big ([#11774](https://github.com/librenms/librenms/pull/11774)) - [Martin22](https://github.com/Martin22)
* VRP FDB table correctly parsed on some CE switches ([#11766](https://github.com/librenms/librenms/pull/11766)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add APC Cooler detection ([#11764](https://github.com/librenms/librenms/pull/11764)) - [karrots](https://github.com/karrots)
* Workaround for bad entSensorPrecision values ([#11757](https://github.com/librenms/librenms/pull/11757)) - [ryanheffernan](https://github.com/ryanheffernan)
* VRP with new Discovery model for hw/serial etc ([#11756](https://github.com/librenms/librenms/pull/11756)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix Samsung Printer ([#11752](https://github.com/librenms/librenms/pull/11752)) - [murrant](https://github.com/murrant)
* Added Arista SN ([#11737](https://github.com/librenms/librenms/pull/11737)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* F5 LTM bandwidth controller ([#11728](https://github.com/librenms/librenms/pull/11728)) - [yac01](https://github.com/yac01)
* Fix missing PoE port graphs for Cisco Catalyst 9K ([#11698](https://github.com/librenms/librenms/pull/11698)) - [ajsiersema](https://github.com/ajsiersema)
* Add OS support for Aviat WTM ([#11654](https://github.com/librenms/librenms/pull/11654)) - [joshuabaird](https://github.com/joshuabaird)
* New OS: DHCPatriot (dhcpatriot) ([#11472](https://github.com/librenms/librenms/pull/11472)) - [pepperoni-pi](https://github.com/pepperoni-pi)
* Arista VRF discovery support ([#11421](https://github.com/librenms/librenms/pull/11421)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Webui
* Edit maintenance schedule, handle timezone properly ([#11889](https://github.com/librenms/librenms/pull/11889)) - [murrant](https://github.com/murrant)
* Fix devices missing from graph view ([#11886](https://github.com/librenms/librenms/pull/11886)) - [murrant](https://github.com/murrant)
* Auth AD URL Setting in Web UI ([#11884](https://github.com/librenms/librenms/pull/11884)) - [Munzy](https://github.com/Munzy)
* Fix some str_i_contains() usages ([#11877](https://github.com/librenms/librenms/pull/11877)) - [murrant](https://github.com/murrant)
* Settings geocode lookup ([#11875](https://github.com/librenms/librenms/pull/11875)) - [murrant](https://github.com/murrant)
* Fix last th min-width in manage device groups table ([#11860](https://github.com/librenms/librenms/pull/11860)) - [Negatifff](https://github.com/Negatifff)
* Fix Cisco Crossbar overview missing ([#11839](https://github.com/librenms/librenms/pull/11839)) - [murrant](https://github.com/murrant)
* Allow device url by hostname ([#11831](https://github.com/librenms/librenms/pull/11831)) - [murrant](https://github.com/murrant)
* 404 when device does not exist ([#11830](https://github.com/librenms/librenms/pull/11830)) - [murrant](https://github.com/murrant)
* Device Maintenance configurable duration ([#11821](https://github.com/librenms/librenms/pull/11821)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix external auth auto-login ([#11813](https://github.com/librenms/librenms/pull/11813)) - [murrant](https://github.com/murrant)
* Fix poller settings display ([#11801](https://github.com/librenms/librenms/pull/11801)) - [murrant](https://github.com/murrant)
* Fix smokeping slave not found causing error ([#11799](https://github.com/librenms/librenms/pull/11799)) - [murrant](https://github.com/murrant)
* Add device groups in overview tab ([#11796](https://github.com/librenms/librenms/pull/11796)) - [Negatifff](https://github.com/Negatifff)
* Fixed typo in function htmlspecialchars within snmp edit ([#11794](https://github.com/librenms/librenms/pull/11794)) - [kuhball](https://github.com/kuhball)
* Fix 2 latency tab bugs ([#11787](https://github.com/librenms/librenms/pull/11787)) - [murrant](https://github.com/murrant)
* Alert Template - sort Alert Rules alphabetic ([#11786](https://github.com/librenms/librenms/pull/11786)) - [SourceDoctor](https://github.com/SourceDoctor)
* Lighter RRD graph colors ([#11759](https://github.com/librenms/librenms/pull/11759)) - [willhseitz](https://github.com/willhseitz)
* Windows Device Overlib equal to Linux ([#11730](https://github.com/librenms/librenms/pull/11730)) - [SourceDoctor](https://github.com/SourceDoctor)
* Widget hide-show search Field ([#11729](https://github.com/librenms/librenms/pull/11729)) - [SourceDoctor](https://github.com/SourceDoctor)
* Don't call clean() on the inputs to the SNMP settings form ([#11709](https://github.com/librenms/librenms/pull/11709)) - [cjwbath](https://github.com/cjwbath)
* Eventlog Application Alert in it's Severity Colour ([#11660](https://github.com/librenms/librenms/pull/11660)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix availability map downtime ([#11638](https://github.com/librenms/librenms/pull/11638)) - [louis-oui](https://github.com/louis-oui)
* Added ability to use JSON directly in Msteams Transport ([#11129](https://github.com/librenms/librenms/pull/11129)) - [pepperoni-pi](https://github.com/pepperoni-pi)

#### Graphs
* RRD Float Precision Customization ([#11853](https://github.com/librenms/librenms/pull/11853)) - [SourceDoctor](https://github.com/SourceDoctor)
* Application Puppet Agent RRD Runtime Graph fix ([#11837](https://github.com/librenms/librenms/pull/11837)) - [SourceDoctor](https://github.com/SourceDoctor)
* Show full description text on Asterisk RRDs ([#11742](https://github.com/librenms/librenms/pull/11742)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Snmp Traps
* Added UPS-MIB Trap On Battery (upsTraps.0.1) ([#11776](https://github.com/librenms/librenms/pull/11776)) - [TheGreatDoc](https://github.com/TheGreatDoc)

#### Applications
* Application DHCP Upgrade ([#11661](https://github.com/librenms/librenms/pull/11661)) - [SourceDoctor](https://github.com/SourceDoctor)
* Added discovery for ups-nut status ([#11606](https://github.com/librenms/librenms/pull/11606)) - [yon2004](https://github.com/yon2004)
* Apps - backupninja ([#11010](https://github.com/librenms/librenms/pull/11010)) - [AnaelMobilia](https://github.com/AnaelMobilia)

#### Alerting
* Fix recurring maintenance days ([#11863](https://github.com/librenms/librenms/pull/11863)) - [murrant](https://github.com/murrant)
* UPS Alert Rule Fix ([#11836](https://github.com/librenms/librenms/pull/11836)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix alert last modified timestamps ([#11833](https://github.com/librenms/librenms/pull/11833)) - [murrant](https://github.com/murrant)
* Make the Discord transport more formatted for Discord. ([#11461](https://github.com/librenms/librenms/pull/11461)) - [nepeat](https://github.com/nepeat)
* Maintenance Windows: recurring now works overnight ([#11389](https://github.com/librenms/librenms/pull/11389)) - [murrant](https://github.com/murrant)

#### Polling
* Fix process details on newer versions of windows checkmk agent ([#11840](https://github.com/librenms/librenms/pull/11840)) - [gardar](https://github.com/gardar)
* Fix performance issue in loadbalancers module ([#11771](https://github.com/librenms/librenms/pull/11771)) - [tamirhad](https://github.com/tamirhad)
* Implemented a generic approach for ifHighSpeed values that cannot be … ([#11504](https://github.com/librenms/librenms/pull/11504)) - [footstep86](https://github.com/footstep86)

#### Rancid
* Fix Rancid GIT ([#11795](https://github.com/librenms/librenms/pull/11795)) - [dupondje](https://github.com/dupondje)

#### Bug
* Python 3.4 is the minimum requirement ([#11880](https://github.com/librenms/librenms/pull/11880)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix unauthenticated graphs ([#11879](https://github.com/librenms/librenms/pull/11879)) - [murrant](https://github.com/murrant)
* Make fping work when fping6 is not present ([#11868](https://github.com/librenms/librenms/pull/11868)) - [murrant](https://github.com/murrant)
* Hotfix CustomOID visibility ([#11861](https://github.com/librenms/librenms/pull/11861)) - [SourceDoctor](https://github.com/SourceDoctor)
* Restore SQL debug output ([#11832](https://github.com/librenms/librenms/pull/11832)) - [murrant](https://github.com/murrant)
* Ping Perf fix while running Squid ([#11823](https://github.com/librenms/librenms/pull/11823)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix pip3 validation ([#11788](https://github.com/librenms/librenms/pull/11788)) - [murrant](https://github.com/murrant)
* Validate autoload first ([#11785](https://github.com/librenms/librenms/pull/11785)) - [murrant](https://github.com/murrant)
* Change github-remove shebang to python3 ([#11753](https://github.com/librenms/librenms/pull/11753)) - [murrant](https://github.com/murrant)
* Syslog -\> Added colour priority to the label column ([#11607](https://github.com/librenms/librenms/pull/11607)) - [TheGreatDoc](https://github.com/TheGreatDoc)

#### Refactor
* PHP session is no longer required ([#11870](https://github.com/librenms/librenms/pull/11870)) - [murrant](https://github.com/murrant)
* Fix validation and other issues when config.php is missing ([#11867](https://github.com/librenms/librenms/pull/11867)) - [murrant](https://github.com/murrant)
* Reformat OS yaml so it is easier to read ([#11862](https://github.com/librenms/librenms/pull/11862)) - [murrant](https://github.com/murrant)
* Auth middleware refinement ([#11767](https://github.com/librenms/librenms/pull/11767)) - [murrant](https://github.com/murrant)

#### Cleanup
* Cleanup functions.inc.php ([#11835](https://github.com/librenms/librenms/pull/11835)) - [SourceDoctor](https://github.com/SourceDoctor)
* Change Units on Noise Floor ([#11790](https://github.com/librenms/librenms/pull/11790)) - [arrmo](https://github.com/arrmo)

#### Documentation
* Install change all tabs ([#11876](https://github.com/librenms/librenms/pull/11876)) - [murrant](https://github.com/murrant)
* Update Services.md ([#11834](https://github.com/librenms/librenms/pull/11834)) - [slashdoom](https://github.com/slashdoom)
* Bare Dashboard Option ([#11818](https://github.com/librenms/librenms/pull/11818)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix typo on Debian install documentation ([#11816](https://github.com/librenms/librenms/pull/11816)) - [AnaelMobilia](https://github.com/AnaelMobilia)
* Added SNMP v3 configuration example for Mikrotik/ROS ([#11802](https://github.com/librenms/librenms/pull/11802)) - [rkandilarov](https://github.com/rkandilarov)
* Clearer python-memcached info ([#11772](https://github.com/librenms/librenms/pull/11772)) - [murrant](https://github.com/murrant)
* Update and Consolidate Install docs Ubuntu 20.04 and CentOS 8 ([#11762](https://github.com/librenms/librenms/pull/11762)) - [murrant](https://github.com/murrant)
* Application sudo correction ([#11741](https://github.com/librenms/librenms/pull/11741)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix Application Sudo Example ([#11707](https://github.com/librenms/librenms/pull/11707)) - [ZoLuSs](https://github.com/ZoLuSs)
* Update RRDCached Documentation ([#11516](https://github.com/librenms/librenms/pull/11516)) - [SourceDoctor](https://github.com/SourceDoctor)
* Doc debian 10 updates ([#11488](https://github.com/librenms/librenms/pull/11488)) - [robje](https://github.com/robje)

#### Translation
* Italian translation ([#11775](https://github.com/librenms/librenms/pull/11775)) - [ppasserini](https://github.com/ppasserini)
* Update zh-tw language ([#11664](https://github.com/librenms/librenms/pull/11664)) - [jasoncheng7115](https://github.com/jasoncheng7115)

#### Tests
* Fix sqlite test failure ([#11847](https://github.com/librenms/librenms/pull/11847)) - [murrant](https://github.com/murrant)

#### Misc
* Drop validation: group has write access ([#11873](https://github.com/librenms/librenms/pull/11873)) - [murrant](https://github.com/murrant)
* Lnms script should not be owned or ran by root ([#11848](https://github.com/librenms/librenms/pull/11848)) - [murrant](https://github.com/murrant)
* Debug pass-through exceptions for Ignition ([#11773](https://github.com/librenms/librenms/pull/11773)) - [murrant](https://github.com/murrant)

#### Dependencies
* Bump websocket-extensions from 0.1.3 to 0.1.4 ([#11874](https://github.com/librenms/librenms/pull/11874)) - [dependabot](https://github.com/apps/dependabot)
* Update PHP dependencies ([#11846](https://github.com/librenms/librenms/pull/11846)) - [murrant](https://github.com/murrant)
* Replace laravel-vue-i18n-generator ([#11815](https://github.com/librenms/librenms/pull/11815)) - [Jellyfrog](https://github.com/Jellyfrog)


## 1.64
*(2020-05-31)*

A big thank you to the following 56 contributors this last month:

  - [murrant](https://github.com/murrant) (56)
  - [SourceDoctor](https://github.com/SourceDoctor) (22)
  - [PipoCanaja](https://github.com/PipoCanaja) (13)
  - [hanserasmus](https://github.com/hanserasmus) (5)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (5)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (5)
  - [arjitc](https://github.com/arjitc) (4)
  - [arrmo](https://github.com/arrmo) (4)
  - [footstep86](https://github.com/footstep86) (4)
  - [spencerryan](https://github.com/spencerryan) (3)
  - [Jellyfrog](https://github.com/Jellyfrog) (3)
  - [jozefrebjak](https://github.com/jozefrebjak) (3)
  - [joseUPV](https://github.com/joseUPV) (2)
  - [vitalisator](https://github.com/vitalisator) (2)
  - [gardar](https://github.com/gardar) (2)
  - [moisseev](https://github.com/moisseev) (2)
  - [bakerds](https://github.com/bakerds) (2)
  - [facuxt](https://github.com/facuxt) (2)
  - [gcotone](https://github.com/gcotone) (1)
  - [crazy-max](https://github.com/crazy-max) (1)
  - [hachpai](https://github.com/hachpai) (1)
  - [VirTechSystems](https://github.com/VirTechSystems) (1)
  - [PelNet](https://github.com/PelNet) (1)
  - [dsgagi](https://github.com/dsgagi) (1)
  - [dagbdagb](https://github.com/dagbdagb) (1)
  - [stylersnico](https://github.com/stylersnico) (1)
  - [karlshea](https://github.com/karlshea) (1)
  - [ospfbgp](https://github.com/ospfbgp) (1)
  - [LaZyDK](https://github.com/LaZyDK) (1)
  - [Munzy](https://github.com/Munzy) (1)
  - [damonreed](https://github.com/damonreed) (1)
  - [Duffyx](https://github.com/Duffyx) (1)
  - [nimrof](https://github.com/nimrof) (1)
  - [Butterscup](https://github.com/Butterscup) (1)
  - [louis-oui](https://github.com/louis-oui) (1)
  - [ProTofik](https://github.com/ProTofik) (1)
  - [mattosem](https://github.com/mattosem) (1)
  - [jp-asdf](https://github.com/jp-asdf) (1)
  - [evheros](https://github.com/evheros) (1)
  - [f0o](https://github.com/f0o) (1)
  - [ajsiersema](https://github.com/ajsiersema) (1)
  - [Negatifff](https://github.com/Negatifff) (1)
  - [dupondje](https://github.com/dupondje) (1)
  - [mathieu-oui](https://github.com/mathieu-oui) (1)
  - [cjwbath](https://github.com/cjwbath) (1)
  - [olivluca](https://github.com/olivluca) (1)
  - [craig-nokia](https://github.com/craig-nokia) (1)
  - [h-barnhart](https://github.com/h-barnhart) (1)
  - [jonasblomq](https://github.com/jonasblomq) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [KingJ](https://github.com/KingJ) (1)
  - [cliffalbert](https://github.com/cliffalbert) (1)
  - [lazyb0nes](https://github.com/lazyb0nes) (1)
  - [bukowski12](https://github.com/bukowski12) (1)
  - [loopodoopo](https://github.com/loopodoopo) (1)
  - [deajan](https://github.com/deajan) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (84)
  - [SourceDoctor](https://github.com/SourceDoctor) (32)
  - [Jellyfrog](https://github.com/Jellyfrog) (20)
  - [PipoCanaja](https://github.com/PipoCanaja) (14)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (10)
  - [kkrumm1](https://github.com/kkrumm1) (2)
  - [laf](https://github.com/laf) (2)
  - [Npeca75](https://github.com/Npeca75) (1)
  - [f0o](https://github.com/f0o) (1)
  - [dwiesner](https://github.com/dwiesner) (1)

#### Feature
* Maintenance Mode via Device Settings ([#11649](https://github.com/librenms/librenms/pull/11649)) - [SourceDoctor](https://github.com/SourceDoctor)
* Added link to smokeping interface and added smokeping options to global settings ([#11610](https://github.com/librenms/librenms/pull/11610)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Drop PHP 71 & Python2 support ([#11531](https://github.com/librenms/librenms/pull/11531)) - [murrant](https://github.com/murrant)
* Implement OS specific information discovery ([#11446](https://github.com/librenms/librenms/pull/11446)) - [murrant](https://github.com/murrant)
* Device page dropdown hero button, Performance -\> Latency ([#11328](https://github.com/librenms/librenms/pull/11328)) - [murrant](https://github.com/murrant)

#### Security
* Fix port permissions ([#11560](https://github.com/librenms/librenms/pull/11560)) - [murrant](https://github.com/murrant)

#### Device
* New Device: PowerTek/BladeShelter PDU support ([#11731](https://github.com/librenms/librenms/pull/11731)) - [mattosem](https://github.com/mattosem)
* Add new sysobjectid for Arbor ArbOS TMS appliances ([#11711](https://github.com/librenms/librenms/pull/11711)) - [jp-asdf](https://github.com/jp-asdf)
* Fixed Cisco ASA Lan2Lan typo ([#11704](https://github.com/librenms/librenms/pull/11704)) - [evheros](https://github.com/evheros)
* Added TPLINK vlans support ([#11697](https://github.com/librenms/librenms/pull/11697)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added OID for EdgeCore models: ES 3528M-SFP, ES 4612, ES 3526 XA, ECS… ([#11690](https://github.com/librenms/librenms/pull/11690)) - [Negatifff](https://github.com/Negatifff)
* Fix mini graphs with PanOS ([#11681](https://github.com/librenms/librenms/pull/11681)) - [mathieu-oui](https://github.com/mathieu-oui)
* New Device: Paradyne SHDSL modems ([#11679](https://github.com/librenms/librenms/pull/11679)) - [PipoCanaja](https://github.com/PipoCanaja)
* New corner cases for Huawei VRP BGP ([#11663](https://github.com/librenms/librenms/pull/11663)) - [PipoCanaja](https://github.com/PipoCanaja)
* Updated Ciena Waveserver MIBs and fixed interface naming ([#11646](https://github.com/librenms/librenms/pull/11646)) - [bakerds](https://github.com/bakerds)
* Fix unit state sensor oid for aos6 ([#11639](https://github.com/librenms/librenms/pull/11639)) - [joseUPV](https://github.com/joseUPV)
* Support for Ciena service delivery switch family ([#11636](https://github.com/librenms/librenms/pull/11636)) - [bakerds](https://github.com/bakerds)
* Added FortiAuthenticator support ([#11633](https://github.com/librenms/librenms/pull/11633)) - [footstep86](https://github.com/footstep86)
* FortiWeb CPU and Memory ([#11632](https://github.com/librenms/librenms/pull/11632)) - [footstep86](https://github.com/footstep86)
* Extend processor polling to Dell Powerconnect N1100 and N1500 series ([#11631](https://github.com/librenms/librenms/pull/11631)) - [KingJ](https://github.com/KingJ)
* Riedo E3Meter PDU ([#11624](https://github.com/librenms/librenms/pull/11624)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add WAN (3/4g-LTE) support on Huawei AR family ([#11619](https://github.com/librenms/librenms/pull/11619)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add Papouch Quido Device ([#11605](https://github.com/librenms/librenms/pull/11605)) - [bukowski12](https://github.com/bukowski12)
* Vendor Dantherm ([#11603](https://github.com/librenms/librenms/pull/11603)) - [loopodoopo](https://github.com/loopodoopo)
* Added FortiSandbox support ([#11593](https://github.com/librenms/librenms/pull/11593)) - [footstep86](https://github.com/footstep86)
* Correct options for IBMC version polling ([#11587](https://github.com/librenms/librenms/pull/11587)) - [PipoCanaja](https://github.com/PipoCanaja)
* Device - Correct SysObjectID for RIEDO Concentrator ([#11573](https://github.com/librenms/librenms/pull/11573)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add serial for QTECH QSW-3400 ([#11569](https://github.com/librenms/librenms/pull/11569)) - [moisseev](https://github.com/moisseev)
* Fixed issues with IMCO Power ([#11559](https://github.com/librenms/librenms/pull/11559)) - [jozefrebjak](https://github.com/jozefrebjak)
* New OS: QTECH ([#11556](https://github.com/librenms/librenms/pull/11556)) - [moisseev](https://github.com/moisseev)
* Move packet journey counters from linux to packetjourney ([#11550](https://github.com/librenms/librenms/pull/11550)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Fix cisco sensor thresholds ([#11547](https://github.com/librenms/librenms/pull/11547)) - [dsgagi](https://github.com/dsgagi)
* Restore applications in TrueNAS ([#11546](https://github.com/librenms/librenms/pull/11546)) - [karlshea](https://github.com/karlshea)
* Fix Papouch detection too generic ([#11540](https://github.com/librenms/librenms/pull/11540)) - [murrant](https://github.com/murrant)
* Updates to Brother Printer ([#11532](https://github.com/librenms/librenms/pull/11532)) - [arrmo](https://github.com/arrmo)
* OS Detection for Mobileiron Core, Sentry appliances ([#11510](https://github.com/librenms/librenms/pull/11510)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* AOS Advanced Support. ([#11500](https://github.com/librenms/librenms/pull/11500)) - [joseUPV](https://github.com/joseUPV)
* SIAE Radio: add additional sensors and data ([#11498](https://github.com/librenms/librenms/pull/11498)) - [murrant](https://github.com/murrant)
* Fix lcos PHP 7.4 incompatible code ([#11497](https://github.com/librenms/librenms/pull/11497)) - [murrant](https://github.com/murrant)
* Added bgpPeerDescr for Arista OS ([#11495](https://github.com/librenms/librenms/pull/11495)) - [damonreed](https://github.com/damonreed)
* Support FreeNAS/TrueNAS 11.3 ([#11494](https://github.com/librenms/librenms/pull/11494)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* MPLS updates and bugfixing ([#11468](https://github.com/librenms/librenms/pull/11468)) - [vitalisator](https://github.com/vitalisator)
* Airos 8.x.x no long supports AirMaxQuality ([#11400](https://github.com/librenms/librenms/pull/11400)) - [facuxt](https://github.com/facuxt)
* Added discovery and graphing for JunOS (SRX) RPM probes ([#11187](https://github.com/librenms/librenms/pull/11187)) - [PelNet](https://github.com/PelNet)
* Added FortiMail support ([#10895](https://github.com/librenms/librenms/pull/10895)) - [footstep86](https://github.com/footstep86)

#### Webui
* Fixing API Creation Error Message ([#11745](https://github.com/librenms/librenms/pull/11745)) - [SourceDoctor](https://github.com/SourceDoctor)
* Show full description text on MySQL RRDs ([#11738](https://github.com/librenms/librenms/pull/11738)) - [SourceDoctor](https://github.com/SourceDoctor)
* Maximum Execution Time Exceeded show error ([#11720](https://github.com/librenms/librenms/pull/11720)) - [murrant](https://github.com/murrant)
* Enumerate Alert Level ([#11652](https://github.com/librenms/librenms/pull/11652)) - [SourceDoctor](https://github.com/SourceDoctor)
* Show Laravel version in about ([#11641](https://github.com/librenms/librenms/pull/11641)) - [murrant](https://github.com/murrant)
* Fix various issues with loading os definitions ([#11640](https://github.com/librenms/librenms/pull/11640)) - [murrant](https://github.com/murrant)
* Application State Icons ([#11630](https://github.com/librenms/librenms/pull/11630)) - [SourceDoctor](https://github.com/SourceDoctor)
* Handle exception about unserializable route cache ([#11625](https://github.com/librenms/librenms/pull/11625)) - [murrant](https://github.com/murrant)
* Fixed realtime graph http get spam ([#11616](https://github.com/librenms/librenms/pull/11616)) - [Butterscup](https://github.com/Butterscup)
* Disable auto-refresh for notifications and alert history ([#11589](https://github.com/librenms/librenms/pull/11589)) - [louis-oui](https://github.com/louis-oui)
* Sort Health table alphabetic ([#11586](https://github.com/librenms/librenms/pull/11586)) - [SourceDoctor](https://github.com/SourceDoctor)
* Only Show in TopInterfaces Devices which are up ([#11578](https://github.com/librenms/librenms/pull/11578)) - [SourceDoctor](https://github.com/SourceDoctor)
* Natural Sort Applications by Display Name ([#11577](https://github.com/librenms/librenms/pull/11577)) - [SourceDoctor](https://github.com/SourceDoctor)
* Application DisplayName in Overview Hover ([#11576](https://github.com/librenms/librenms/pull/11576)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add acknowledgment notes to eventlog ([#11575](https://github.com/librenms/librenms/pull/11575)) - [vitalisator](https://github.com/vitalisator)
* Fix vmhost device page link ([#11553](https://github.com/librenms/librenms/pull/11553)) - [murrant](https://github.com/murrant)
* Correct OS Overlib in Inventory ([#11551](https://github.com/librenms/librenms/pull/11551)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix poller deletion ([#11549](https://github.com/librenms/librenms/pull/11549)) - [murrant](https://github.com/murrant)
* Fix smokeping wo integration ([#11548](https://github.com/librenms/librenms/pull/11548)) - [murrant](https://github.com/murrant)
* Show Hardware Details on Network Adapters in Device - Inventory ([#11545](https://github.com/librenms/librenms/pull/11545)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix display of device modules ([#11543](https://github.com/librenms/librenms/pull/11543)) - [ospfbgp](https://github.com/ospfbgp)
* Addhost (web) snmp v3 not used first ([#11536](https://github.com/librenms/librenms/pull/11536)) - [nimrof](https://github.com/nimrof)
* Fix apps overview link ([#11535](https://github.com/librenms/librenms/pull/11535)) - [murrant](https://github.com/murrant)
* Sort Top Devices widget descending by default ([#11534](https://github.com/librenms/librenms/pull/11534)) - [murrant](https://github.com/murrant)
* Update device health settings Table UI ([#11529](https://github.com/librenms/librenms/pull/11529)) - [arjitc](https://github.com/arjitc)
* Fix errors for some devices loading components ([#11527](https://github.com/librenms/librenms/pull/11527)) - [murrant](https://github.com/murrant)
* Fix new bill search input ([#11524](https://github.com/librenms/librenms/pull/11524)) - [murrant](https://github.com/murrant)
* Equalize BootstrapSwitch Style on Device Edit to LibreNMS Standard ([#11513](https://github.com/librenms/librenms/pull/11513)) - [arjitc](https://github.com/arjitc)
* Limit port, seconds, retries, max repeats and max OIDs to numeric type ([#11512](https://github.com/librenms/librenms/pull/11512)) - [arjitc](https://github.com/arjitc)
* Put all the stats into the optionbar ([#11501](https://github.com/librenms/librenms/pull/11501)) - [arjitc](https://github.com/arjitc)
* Login form footer center align ([#11499](https://github.com/librenms/librenms/pull/11499)) - [jozefrebjak](https://github.com/jozefrebjak)
* WebUI - Improved "dark" theme ([#11417](https://github.com/librenms/librenms/pull/11417)) - [facuxt](https://github.com/facuxt)

#### Snmp Traps
* OSPF SNMP Trap Handlers ([#11647](https://github.com/librenms/librenms/pull/11647)) - [h-barnhart](https://github.com/h-barnhart)
* Added warmStart trap handler ([#11583](https://github.com/librenms/librenms/pull/11583)) - [jozefrebjak](https://github.com/jozefrebjak)

#### Applications
* Add support for windows librenms(check_mk) agent ([#11691](https://github.com/librenms/librenms/pull/11691)) - [gardar](https://github.com/gardar)
* Application - Redis ([#11612](https://github.com/librenms/librenms/pull/11612)) - [SourceDoctor](https://github.com/SourceDoctor)
* Adjust RRDCached application event graph ([#11528](https://github.com/librenms/librenms/pull/11528)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix RRDCached Application Socket Address in Poller ([#11525](https://github.com/librenms/librenms/pull/11525)) - [SourceDoctor](https://github.com/SourceDoctor)
* String Nicement for RRDCached - also alphabetic sorting of array ([#11517](https://github.com/librenms/librenms/pull/11517)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Api
* Enabling general search for ports, devices, and more ([#11571](https://github.com/librenms/librenms/pull/11571)) - [hachpai](https://github.com/hachpai)

#### Alerting
* Fix another mysql error in isMaintenance() ([#11746](https://github.com/librenms/librenms/pull/11746)) - [arrmo](https://github.com/arrmo)
* Address Incorrect DATE value in isUnderMaintenance ([#11736](https://github.com/librenms/librenms/pull/11736)) - [arrmo](https://github.com/arrmo)
* Do not delay alert recovery notifications ([#11555](https://github.com/librenms/librenms/pull/11555)) - [spencerryan](https://github.com/spencerryan)
* Send Device group membership to Pagerduty ([#11522](https://github.com/librenms/librenms/pull/11522)) - [spencerryan](https://github.com/spencerryan)
* Allow manual configuration of Pagerduty Integration Key ([#11519](https://github.com/librenms/librenms/pull/11519)) - [spencerryan](https://github.com/spencerryan)
* Copy and modify the syslog transport for sending alerts to Splunk in an easy to parse format ([#11176](https://github.com/librenms/librenms/pull/11176)) - [VirTechSystems](https://github.com/VirTechSystems)

#### Discovery
* Fix sensor type/class confusion ([#11608](https://github.com/librenms/librenms/pull/11608)) - [PipoCanaja](https://github.com/PipoCanaja)
* Improvement for matching LLDP neighbors with known hosts. ([#11445](https://github.com/librenms/librenms/pull/11445)) - [dagbdagb](https://github.com/dagbdagb)
* Migrate Python scripts to Python 3 ([#10759](https://github.com/librenms/librenms/pull/10759)) - [deajan](https://github.com/deajan)

#### Polling
* Fix wireless sensor polling unit display ([#11748](https://github.com/librenms/librenms/pull/11748)) - [murrant](https://github.com/murrant)
* Fix poller enabling graphs for display ([#11743](https://github.com/librenms/librenms/pull/11743)) - [murrant](https://github.com/murrant)
* Fix dynamic group membership rule for devices.status equal 0 ([#11699](https://github.com/librenms/librenms/pull/11699)) - [ajsiersema](https://github.com/ajsiersema)
* Fix - Seperate default poller id and distributed poller groups ([#11584](https://github.com/librenms/librenms/pull/11584)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix double escaping ([#11503](https://github.com/librenms/librenms/pull/11503)) - [gcotone](https://github.com/gcotone)

#### Rancid
* Add Edgemax support to rancid script ([#11687](https://github.com/librenms/librenms/pull/11687)) - [dupondje](https://github.com/dupondje)
* Support for allied telesis and cisco wlc ([#11617](https://github.com/librenms/librenms/pull/11617)) - [cliffalbert](https://github.com/cliffalbert)

#### Bug
* Fix MySQL App sorts display ([#11740](https://github.com/librenms/librenms/pull/11740)) - [SourceDoctor](https://github.com/SourceDoctor)
* Remove int width from db schema validation (MySQL 8) ([#11725](https://github.com/librenms/librenms/pull/11725)) - [arrmo](https://github.com/arrmo)
* Fix CSRF Token in Latency tab ([#11703](https://github.com/librenms/librenms/pull/11703)) - [f0o](https://github.com/f0o)
* Fix some python3 wrapper connection issues ([#11693](https://github.com/librenms/librenms/pull/11693)) - [murrant](https://github.com/murrant)
* Fix the PANOS HA state check alert rule from collection ([#11657](https://github.com/librenms/librenms/pull/11657)) - [cjwbath](https://github.com/cjwbath)
* Fix inconsistent escapes (slashes in sysLocation) ([#11637](https://github.com/librenms/librenms/pull/11637)) - [murrant](https://github.com/murrant)
* Fix "improper label name" in Prometheus datastore ([#11602](https://github.com/librenms/librenms/pull/11602)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Filter on sensor class as expected and documented ([#11592](https://github.com/librenms/librenms/pull/11592)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix python always install as user ([#11562](https://github.com/librenms/librenms/pull/11562)) - [murrant](https://github.com/murrant)
* Fix default uptime warning to 86400 seconds ([#11507](https://github.com/librenms/librenms/pull/11507)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Refactor
* Clean mempools code to avoid unnecessary snmpget ([#11678](https://github.com/librenms/librenms/pull/11678)) - [PipoCanaja](https://github.com/PipoCanaja)
* Enumerate AlertState ([#11665](https://github.com/librenms/librenms/pull/11665)) - [SourceDoctor](https://github.com/SourceDoctor)
* Python wrapper sql cleanup ([#11628](https://github.com/librenms/librenms/pull/11628)) - [murrant](https://github.com/murrant)
* Python3 only for snmp-scan.py ([#11623](https://github.com/librenms/librenms/pull/11623)) - [murrant](https://github.com/murrant)
* Laravel 6.x ([#11397](https://github.com/librenms/librenms/pull/11397)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Documentation
* Update rrdtool setting explanation ([#11724](https://github.com/librenms/librenms/pull/11724)) - [hanserasmus](https://github.com/hanserasmus)
* Update distributed poller docs intro ([#11721](https://github.com/librenms/librenms/pull/11721)) - [murrant](https://github.com/murrant)
* Hardware examples SourceDoctor ([#11680](https://github.com/librenms/librenms/pull/11680)) - [SourceDoctor](https://github.com/SourceDoctor)
* Applications Corrected wrong path in documentation ([#11675](https://github.com/librenms/librenms/pull/11675)) - [ProTofik](https://github.com/ProTofik)
* Document update for distributed poller ([#11655](https://github.com/librenms/librenms/pull/11655)) - [craig-nokia](https://github.com/craig-nokia)
* Fix Fast-Ping-Check.md to include information about RRDCached ([#11645](https://github.com/librenms/librenms/pull/11645)) - [jonasblomq](https://github.com/jonasblomq)
* Update Example-Hardware-Setup.md ([#11611](https://github.com/librenms/librenms/pull/11611)) - [lazyb0nes](https://github.com/lazyb0nes)
* Removed reference to deprecated poller-service.py ([#11598](https://github.com/librenms/librenms/pull/11598)) - [hanserasmus](https://github.com/hanserasmus)
* Added example for the alerta transport ([#11596](https://github.com/librenms/librenms/pull/11596)) - [olivluca](https://github.com/olivluca)
* Add missing python3 modules from repo, remove pip ([#11594](https://github.com/librenms/librenms/pull/11594)) - [gardar](https://github.com/gardar)
* Correct sensor ignore documentation ([#11591](https://github.com/librenms/librenms/pull/11591)) - [PipoCanaja](https://github.com/PipoCanaja)
* Typo Fix ([#11588](https://github.com/librenms/librenms/pull/11588)) - [Munzy](https://github.com/Munzy)
* Update Docker installation doc ([#11579](https://github.com/librenms/librenms/pull/11579)) - [crazy-max](https://github.com/crazy-max)
* Add python3 and pip3 to installs ([#11566](https://github.com/librenms/librenms/pull/11566)) - [hanserasmus](https://github.com/hanserasmus)
* Added python3 for new installs ([#11564](https://github.com/librenms/librenms/pull/11564)) - [hanserasmus](https://github.com/hanserasmus)
* Added note about having same APP_KEY value ([#11521](https://github.com/librenms/librenms/pull/11521)) - [hanserasmus](https://github.com/hanserasmus)
* Updated Index to add the doc for Debian 10 ([#11515](https://github.com/librenms/librenms/pull/11515)) - [stylersnico](https://github.com/stylersnico)
* Update Installation-Ubuntu-1804-Nginx.md ([#11466](https://github.com/librenms/librenms/pull/11466)) - [LaZyDK](https://github.com/LaZyDK)

#### Tests
* Testing cleanup ([#11677](https://github.com/librenms/librenms/pull/11677)) - [murrant](https://github.com/murrant)
* Rewrite development helper to lnms dev:check ([#11650](https://github.com/librenms/librenms/pull/11650)) - [murrant](https://github.com/murrant)
* Fix test behaviour with PHP 7.4 on Fedora 32 ([#11648](https://github.com/librenms/librenms/pull/11648)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Speed up tests by skipping pings ([#11642](https://github.com/librenms/librenms/pull/11642)) - [murrant](https://github.com/murrant)
* Update PHPUnit to 8.x ([#11635](https://github.com/librenms/librenms/pull/11635)) - [Jellyfrog](https://github.com/Jellyfrog)
* Os_schema is missing a module ([#11511](https://github.com/librenms/librenms/pull/11511)) - [TheMysteriousX](https://github.com/TheMysteriousX)

#### Misc
* Check python3 and pip3 versions match ([#11739](https://github.com/librenms/librenms/pull/11739)) - [murrant](https://github.com/murrant)
* Check lnms running user ([#11726](https://github.com/librenms/librenms/pull/11726)) - [murrant](https://github.com/murrant)
* Make migrations work in SQLite ([#11643](https://github.com/librenms/librenms/pull/11643)) - [murrant](https://github.com/murrant)
* Validate PHP version mismatch ([#11621](https://github.com/librenms/librenms/pull/11621)) - [murrant](https://github.com/murrant)
* Setuptools is required for python dependency check ([#11600](https://github.com/librenms/librenms/pull/11600)) - [SourceDoctor](https://github.com/SourceDoctor)
* Hide python user warning if deps met at system level ([#11590](https://github.com/librenms/librenms/pull/11590)) - [murrant](https://github.com/murrant)
* Send python 3 version to stats.librenms.org ([#11568](https://github.com/librenms/librenms/pull/11568)) - [murrant](https://github.com/murrant)
* Add python validation fix suggestions ([#11563](https://github.com/librenms/librenms/pull/11563)) - [murrant](https://github.com/murrant)
* Added ignored devices to be pinged by smokeping ([#11557](https://github.com/librenms/librenms/pull/11557)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Python3 Module Dependency Check ([#11544](https://github.com/librenms/librenms/pull/11544)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix socket logic in services-wrapper.py ([#11523](https://github.com/librenms/librenms/pull/11523)) - [Duffyx](https://github.com/Duffyx)
* Install python requirements during daily ([#11486](https://github.com/librenms/librenms/pull/11486)) - [murrant](https://github.com/murrant)
* Run artisan optimize after composer install ([#11465](https://github.com/librenms/librenms/pull/11465)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Dependencies
* Bump jquery from 3.4.1 to 3.5.0 ([#11644](https://github.com/librenms/librenms/pull/11644)) - [dependabot](https://github.com/apps/dependabot)


## 1.63
*(2020-04-27)*

A big thank you to the following 41 contributors this last month:

  - [murrant](https://github.com/murrant) (31)
  - [SourceDoctor](https://github.com/SourceDoctor) (12)
  - [Jellyfrog](https://github.com/Jellyfrog) (11)
  - [PipoCanaja](https://github.com/PipoCanaja) (7)
  - [Martin22](https://github.com/Martin22) (5)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (5)
  - [hanserasmus](https://github.com/hanserasmus) (4)
  - [willhseitz](https://github.com/willhseitz) (4)
  - [dneto82](https://github.com/dneto82) (3)
  - [kedare](https://github.com/kedare) (3)
  - [jozefrebjak](https://github.com/jozefrebjak) (3)
  - [stylersnico](https://github.com/stylersnico) (2)
  - [priiduonu](https://github.com/priiduonu) (2)
  - [h-barnhart](https://github.com/h-barnhart) (2)
  - [nimrof](https://github.com/nimrof) (2)
  - [mathieu-oui](https://github.com/mathieu-oui) (1)
  - [AnaelMobilia](https://github.com/AnaelMobilia) (1)
  - [frenchie](https://github.com/frenchie) (1)
  - [noaheroufus](https://github.com/noaheroufus) (1)
  - [TFujiwara](https://github.com/TFujiwara) (1)
  - [vsessink](https://github.com/vsessink) (1)
  - [dGs-](https://github.com/dGs-) (1)
  - [robwilkes](https://github.com/robwilkes) (1)
  - [vitalisator](https://github.com/vitalisator) (1)
  - [jviersel](https://github.com/jviersel) (1)
  - [FingerlessGlov3s](https://github.com/FingerlessGlov3s) (1)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (1)
  - [jepke](https://github.com/jepke) (1)
  - [Alex131089](https://github.com/Alex131089) (1)
  - [nwautomator](https://github.com/nwautomator) (1)
  - [danislav](https://github.com/danislav) (1)
  - [monrad](https://github.com/monrad) (1)
  - [gonzocrazy](https://github.com/gonzocrazy) (1)
  - [arjitc](https://github.com/arjitc) (1)
  - [robje](https://github.com/robje) (1)
  - [arrmo](https://github.com/arrmo) (1)
  - [AltiUP](https://github.com/AltiUP) (1)
  - [realgreef](https://github.com/realgreef) (1)
  - [Zaxmy](https://github.com/Zaxmy) (1)
  - [Cormoran96](https://github.com/Cormoran96) (1)
  - [TvL2386](https://github.com/TvL2386) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (44)
  - [Jellyfrog](https://github.com/Jellyfrog) (31)
  - [SourceDoctor](https://github.com/SourceDoctor) (23)
  - [PipoCanaja](https://github.com/PipoCanaja) (19)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (5)
  - [laf](https://github.com/laf) (3)
  - [kkrumm1](https://github.com/kkrumm1) (1)
  - [craig-nokia](https://github.com/craig-nokia) (1)

#### Feature
* Global search MAC Address ([#11434](https://github.com/librenms/librenms/pull/11434)) - [SourceDoctor](https://github.com/SourceDoctor)
* Config CLI improvements ([#11430](https://github.com/librenms/librenms/pull/11430)) - [murrant](https://github.com/murrant)
* Implement watchdog to librenms-service ([#11353](https://github.com/librenms/librenms/pull/11353)) - [willhseitz](https://github.com/willhseitz)

#### Device
* Opengear add mempool discovery+polling ([#11491](https://github.com/librenms/librenms/pull/11491)) - [gonzocrazy](https://github.com/gonzocrazy)
* Detection, sensors and zpool/dataset usage for FreeNAS, TrueNAS ([#11474](https://github.com/librenms/librenms/pull/11474)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* OS detection and sensors for Dell Compellent ([#11467](https://github.com/librenms/librenms/pull/11467)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Add OS detection for Greenbone appliances ([#11464](https://github.com/librenms/librenms/pull/11464)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Raspberry Pi frequency sensors discovery ([#11460](https://github.com/librenms/librenms/pull/11460)) - [priiduonu](https://github.com/priiduonu)
* Added Cisco Firepower 1010 ([#11449](https://github.com/librenms/librenms/pull/11449)) - [realgreef](https://github.com/realgreef)
* Add OS version to asuswrt-merlin ([#11438](https://github.com/librenms/librenms/pull/11438)) - [arrmo](https://github.com/arrmo)
* Added support for Peplink Pepwave & FusionHub ([#11432](https://github.com/librenms/librenms/pull/11432)) - [jozefrebjak](https://github.com/jozefrebjak)
* Extend Netvision Socomec UPS cards for RFC1628 ([#11426](https://github.com/librenms/librenms/pull/11426)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add support for Advantech switches ([#11415](https://github.com/librenms/librenms/pull/11415)) - [monrad](https://github.com/monrad)
* CyberPower SNMP Traps ([#11403](https://github.com/librenms/librenms/pull/11403)) - [h-barnhart](https://github.com/h-barnhart)
* Classify as unix for additional sensors ([#11390](https://github.com/librenms/librenms/pull/11390)) - [nwautomator](https://github.com/nwautomator)
* IMCO Power - Added skip values for older devices without temperature sensor support. ([#11387](https://github.com/librenms/librenms/pull/11387)) - [Martin22](https://github.com/Martin22)
* Ubiquiti Airfiber LTU many more sensors ([#11382](https://github.com/librenms/librenms/pull/11382)) - [jepke](https://github.com/jepke)
* Fix routing engine discovery on standalone JunOS devices ([#11381](https://github.com/librenms/librenms/pull/11381)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Add support Ray3 ([#11374](https://github.com/librenms/librenms/pull/11374)) - [Martin22](https://github.com/Martin22)
* OPNsense  poller better reporting of Version and Platform ([#11350](https://github.com/librenms/librenms/pull/11350)) - [FingerlessGlov3s](https://github.com/FingerlessGlov3s)
* Mikrotik - Updated MIB file and added Wireless Quality ([#11347](https://github.com/librenms/librenms/pull/11347)) - [Martin22](https://github.com/Martin22)
* Fix PanOS Sessions count and add Vsys and other sensors ([#11341](https://github.com/librenms/librenms/pull/11341)) - [mathieu-oui](https://github.com/mathieu-oui)
* Added hardware detection for FreeBSD ([#11313](https://github.com/librenms/librenms/pull/11313)) - [frenchie](https://github.com/frenchie)
* Add support for Ascom IPBS ([#11308](https://github.com/librenms/librenms/pull/11308)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added Support for AirConsole Servers ([#11302](https://github.com/librenms/librenms/pull/11302)) - [jozefrebjak](https://github.com/jozefrebjak)
* New device IMCO POWER ([#11296](https://github.com/librenms/librenms/pull/11296)) - [Martin22](https://github.com/Martin22)
* APC NetBotz 200 - doesn't add not available temperature sensors ([#11259](https://github.com/librenms/librenms/pull/11259)) - [dGs-](https://github.com/dGs-)
* IPv6 support for BGP peers in VRP devices ([#11243](https://github.com/librenms/librenms/pull/11243)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for RIEDO data concentrator ([#11237](https://github.com/librenms/librenms/pull/11237)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for ICT Modular Power System and ICT Sine Wave Inverter ([#11182](https://github.com/librenms/librenms/pull/11182)) - [noaheroufus](https://github.com/noaheroufus)
* Fix for Nokia 7705 SAR ([#11021](https://github.com/librenms/librenms/pull/11021)) - [vitalisator](https://github.com/vitalisator)
* Added sensors for outlets on Schleifenbauer devices. ([#10949](https://github.com/librenms/librenms/pull/10949)) - [jviersel](https://github.com/jviersel)

#### Webui
* Fix duplicate type="submit" ([#11493](https://github.com/librenms/librenms/pull/11493)) - [nimrof](https://github.com/nimrof)
* Fixed a small typo ([#11487](https://github.com/librenms/librenms/pull/11487)) - [hanserasmus](https://github.com/hanserasmus)
* Restore click on Dashboard menu entry ([#11478](https://github.com/librenms/librenms/pull/11478)) - [murrant](https://github.com/murrant)
* Fix - Show OS specific Mouseover in Alert Widget ([#11457](https://github.com/librenms/librenms/pull/11457)) - [SourceDoctor](https://github.com/SourceDoctor)
* Toggle dashboard editor without refresh ([#11455](https://github.com/librenms/librenms/pull/11455)) - [murrant](https://github.com/murrant)
* Fix sensor link ([#11454](https://github.com/librenms/librenms/pull/11454)) - [murrant](https://github.com/murrant)
* Don't access the database too soon in Device model boot ([#11453](https://github.com/librenms/librenms/pull/11453)) - [murrant](https://github.com/murrant)
* Fix devices filter os ([#11443](https://github.com/librenms/librenms/pull/11443)) - [murrant](https://github.com/murrant)
* Prevent breaking of interface name into 2 words/parts ([#11420](https://github.com/librenms/librenms/pull/11420)) - [arjitc](https://github.com/arjitc)
* Fix dashboard size ([#11405](https://github.com/librenms/librenms/pull/11405)) - [murrant](https://github.com/murrant)
* Update on OS only logo. ([#11399](https://github.com/librenms/librenms/pull/11399)) - [dneto82](https://github.com/dneto82)
* Updated panos.svg ([#11398](https://github.com/librenms/librenms/pull/11398)) - [dneto82](https://github.com/dneto82)
* Fix global search by IP ([#11395](https://github.com/librenms/librenms/pull/11395)) - [murrant](https://github.com/murrant)
* Clarify that you can use IP or hostname to add a device ([#11393](https://github.com/librenms/librenms/pull/11393)) - [murrant](https://github.com/murrant)
* Widget Eventlog Sensors Link and Mouseover functionality ([#11380](https://github.com/librenms/librenms/pull/11380)) - [SourceDoctor](https://github.com/SourceDoctor)
* Convert alert modals to blade ([#11373](https://github.com/librenms/librenms/pull/11373)) - [Jellyfrog](https://github.com/Jellyfrog)
* Display custom types as in the config file (with ucwords) ([#11367](https://github.com/librenms/librenms/pull/11367)) - [murrant](https://github.com/murrant)
* Geo map: check if lat/long exist for each device with link ([#11366](https://github.com/librenms/librenms/pull/11366)) - [willhseitz](https://github.com/willhseitz)
* Fix shared dashboards missing users ([#11365](https://github.com/librenms/librenms/pull/11365)) - [murrant](https://github.com/murrant)
* Protect against plugins that leak output ([#11364](https://github.com/librenms/librenms/pull/11364)) - [murrant](https://github.com/murrant)
* Toggle visibilty of Dashboard Editor ([#11321](https://github.com/librenms/librenms/pull/11321)) - [SourceDoctor](https://github.com/SourceDoctor)
* Honoring config option `force_ip_to_sysname` in bill creation and editing ([#10382](https://github.com/librenms/librenms/pull/10382)) - [TvL2386](https://github.com/TvL2386)

#### Applications
* Change stats file location (Bind9) ([#11439](https://github.com/librenms/librenms/pull/11439)) - [AltiUP](https://github.com/AltiUP)
* Rrdcached Socket Check fix ([#11372](https://github.com/librenms/librenms/pull/11372)) - [SourceDoctor](https://github.com/SourceDoctor)
* Get configured Rrdcached Socket from config ([#11351](https://github.com/librenms/librenms/pull/11351)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix memcached always reporting "ERROR" for app_state ([#10739](https://github.com/librenms/librenms/pull/10739)) - [willhseitz](https://github.com/willhseitz)

#### Api
* BGP API: fix search by ipv6 when using compressed addresses ([#11394](https://github.com/librenms/librenms/pull/11394)) - [kedare](https://github.com/kedare)
* BGP API: Allow to filter by local and remote peer address. ([#11340](https://github.com/librenms/librenms/pull/11340)) - [kedare](https://github.com/kedare)

#### Alerting
* PagerDuty Transport Improvement ([#11459](https://github.com/librenms/librenms/pull/11459)) - [h-barnhart](https://github.com/h-barnhart)
* Add support for sending events to Sensu ([#11383](https://github.com/librenms/librenms/pull/11383)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Adding Column ifSpeed_prev, ifHighSpeed_prev to Ports Table ([#11348](https://github.com/librenms/librenms/pull/11348)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add transport for Smsmode (SMS provider) ([#11332](https://github.com/librenms/librenms/pull/11332)) - [AnaelMobilia](https://github.com/AnaelMobilia)

#### Billing
* Fix billing graphs: work around jpgraph bug ([#11425](https://github.com/librenms/librenms/pull/11425)) - [murrant](https://github.com/murrant)

#### Discovery
* Cisco sensor discovery fixes ([#11077](https://github.com/librenms/librenms/pull/11077)) - [robwilkes](https://github.com/robwilkes)

#### Polling
* BGP Polling: Add error code management ([#11424](https://github.com/librenms/librenms/pull/11424)) - [kedare](https://github.com/kedare)
* Fix device creation using overwrited ip ([#11388](https://github.com/librenms/librenms/pull/11388)) - [Alex131089](https://github.com/Alex131089)
* Fix adsl graphs ([#11379](https://github.com/librenms/librenms/pull/11379)) - [murrant](https://github.com/murrant)
* Fix rrd format issues for asterisk and cipsec-tunnels ([#11375](https://github.com/librenms/librenms/pull/11375)) - [murrant](https://github.com/murrant)
* Patch unix agent ([#11312](https://github.com/librenms/librenms/pull/11312)) - [TFujiwara](https://github.com/TFujiwara)
* Fixed race conditions in distributed poller setup ([#11307](https://github.com/librenms/librenms/pull/11307)) - [vsessink](https://github.com/vsessink)
* Fix 10853 os specific syslocation ([#11082](https://github.com/librenms/librenms/pull/11082)) - [willhseitz](https://github.com/willhseitz)

#### Bug
* Customer graphs: fix evaluation order so unauthenticated access works ([#11485](https://github.com/librenms/librenms/pull/11485)) - [Zaxmy](https://github.com/Zaxmy)
* Fix case sensitiv translations ([#11463](https://github.com/librenms/librenms/pull/11463)) - [Jellyfrog](https://github.com/Jellyfrog)
* Missed one jpgraph string ([#11447](https://github.com/librenms/librenms/pull/11447)) - [murrant](https://github.com/murrant)
* Alert rule in, not_in remove ([#11437](https://github.com/librenms/librenms/pull/11437)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix a copy-n-paste error ([#11429](https://github.com/librenms/librenms/pull/11429)) - [robje](https://github.com/robje)
* Fix - Read the db_port as integer ([#11392](https://github.com/librenms/librenms/pull/11392)) - [danislav](https://github.com/danislav)
* Fix dashboard html escaping and javascript redirect ([#11370](https://github.com/librenms/librenms/pull/11370)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Refactor
* Cleanup unused code ([#11391](https://github.com/librenms/librenms/pull/11391)) - [SourceDoctor](https://github.com/SourceDoctor)
* Delete ports via eloquent event ([#11354](https://github.com/librenms/librenms/pull/11354)) - [murrant](https://github.com/murrant)
* Convert overview page to laravel ([#10757](https://github.com/librenms/librenms/pull/10757)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Cleanup
* Update php-codesniffer ([#11368](https://github.com/librenms/librenms/pull/11368)) - [murrant](https://github.com/murrant)

#### Documentation
* Add ASP sms provider ([#11489](https://github.com/librenms/librenms/pull/11489)) - [Cormoran96](https://github.com/Cormoran96)
* Created installation for Debian 10 ([#11481](https://github.com/librenms/librenms/pull/11481)) - [stylersnico](https://github.com/stylersnico)
* Add php-opcache settings to Performance Tuning ([#11452](https://github.com/librenms/librenms/pull/11452)) - [hanserasmus](https://github.com/hanserasmus)
* Updated selected-port-polling explanation ([#11435](https://github.com/librenms/librenms/pull/11435)) - [hanserasmus](https://github.com/hanserasmus)
* Update Images.md ([#11418](https://github.com/librenms/librenms/pull/11418)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fix RRDTune.md ([#11369](https://github.com/librenms/librenms/pull/11369)) - [jozefrebjak](https://github.com/jozefrebjak)

#### Tests
* Addhost tests ([#11385](https://github.com/librenms/librenms/pull/11385)) - [nimrof](https://github.com/nimrof)

#### Misc
* Set PHP 7.2.5 as minimum supported version ([#11470](https://github.com/librenms/librenms/pull/11470)) - [Jellyfrog](https://github.com/Jellyfrog)
* Show full path to ini file for timezone validation failures ([#11444](https://github.com/librenms/librenms/pull/11444)) - [murrant](https://github.com/murrant)
* Global setting better naming ([#11412](https://github.com/librenms/librenms/pull/11412)) - [SourceDoctor](https://github.com/SourceDoctor)
* Updated MIB from Palo Alto ([#11402](https://github.com/librenms/librenms/pull/11402)) - [dneto82](https://github.com/dneto82)
* Update PHP dependencies ([#11377](https://github.com/librenms/librenms/pull/11377)) - [murrant](https://github.com/murrant)


## 1.62
*(2020-03-31)*

A big thank you to the following 34 contributors this last month:

  - [murrant](https://github.com/murrant) (17)
  - [SourceDoctor](https://github.com/SourceDoctor) (15)
  - [PipoCanaja](https://github.com/PipoCanaja) (7)
  - [dagbdagb](https://github.com/dagbdagb) (4)
  - [danislav](https://github.com/danislav) (3)
  - [mpikzink](https://github.com/mpikzink) (3)
  - [cjwbath](https://github.com/cjwbath) (3)
  - [kedare](https://github.com/kedare) (3)
  - [ghost](https://github.com/ghost) (2)
  - [dlangille](https://github.com/dlangille) (2)
  - [Munzy](https://github.com/Munzy) (2)
  - [jozefrebjak](https://github.com/jozefrebjak) (2)
  - [Urth](https://github.com/Urth) (2)
  - [joseUPV](https://github.com/joseUPV) (1)
  - [hanserasmus](https://github.com/hanserasmus) (1)
  - [josephtingiris](https://github.com/josephtingiris) (1)
  - [FingerlessGlov3s](https://github.com/FingerlessGlov3s) (1)
  - [fhlmbrg](https://github.com/fhlmbrg) (1)
  - [h-barnhart](https://github.com/h-barnhart) (1)
  - [shepherdjay](https://github.com/shepherdjay) (1)
  - [Chewza](https://github.com/Chewza) (1)
  - [ospfbgp](https://github.com/ospfbgp) (1)
  - [jepke](https://github.com/jepke) (1)
  - [ekoyle](https://github.com/ekoyle) (1)
  - [pobradovic08](https://github.com/pobradovic08) (1)
  - [dGs-](https://github.com/dGs-) (1)
  - [nickhilliard](https://github.com/nickhilliard) (1)
  - [raphaelyancey](https://github.com/raphaelyancey) (1)
  - [rkojedzinszky](https://github.com/rkojedzinszky) (1)
  - [lucadefazio](https://github.com/lucadefazio) (1)
  - [fusedsynapse](https://github.com/fusedsynapse) (1)
  - [jp-asdf](https://github.com/jp-asdf) (1)
  - [chrisocalypse](https://github.com/chrisocalypse) (1)
  - [crcro](https://github.com/crcro) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [SourceDoctor](https://github.com/SourceDoctor) (26)
  - [PipoCanaja](https://github.com/PipoCanaja) (24)
  - [murrant](https://github.com/murrant) (22)
  - [kkrumm1](https://github.com/kkrumm1) (8)
  - [Jellyfrog](https://github.com/Jellyfrog) (6)
  - [vdchuyen](https://github.com/vdchuyen) (1)
  - [sp1rr3](https://github.com/sp1rr3) (1)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (1)
  - [laf](https://github.com/laf) (1)

#### Feature
* Geographical map: Show network links between locations ([#11269](https://github.com/librenms/librenms/pull/11269)) - [kedare](https://github.com/kedare)
* Extra VLAN mapping in bridge FDB module to fix ProCurve ([#11230](https://github.com/librenms/librenms/pull/11230)) - [cjwbath](https://github.com/cjwbath)

#### Device
* Add support for VOSS switches VSP-7400-48Y-8C and VSP-4900-48P ([#11360](https://github.com/librenms/librenms/pull/11360)) - [ospfbgp](https://github.com/ospfbgp)
* Changed USV bypass state from alert to warning ([#11356](https://github.com/librenms/librenms/pull/11356)) - [mpikzink](https://github.com/mpikzink)
* Cisco-Remote-Access-Monitor ([#11355](https://github.com/librenms/librenms/pull/11355)) - [mpikzink](https://github.com/mpikzink)
* Added support for Cisco Firepower FTD 4140 ([#11345](https://github.com/librenms/librenms/pull/11345)) - [chrisocalypse](https://github.com/chrisocalypse)
* Create MAS-MIB-SMIV2-MIB ([#11342](https://github.com/librenms/librenms/pull/11342)) - [jp-asdf](https://github.com/jp-asdf)
* Sonus became Ribbon Communications - update logos ([#11339](https://github.com/librenms/librenms/pull/11339)) - [cjwbath](https://github.com/cjwbath)
* Added cisco firepower threat defense 1120 ([#11336](https://github.com/librenms/librenms/pull/11336)) - [lucadefazio](https://github.com/lucadefazio)
* Ubiquiti Edgepower OS definition ([#11315](https://github.com/librenms/librenms/pull/11315)) - [jepke](https://github.com/jepke)
* Fixed issue with reporting of Cisco ASA Remote Sessions. rev2 ([#11286](https://github.com/librenms/librenms/pull/11286)) - [dagbdagb](https://github.com/dagbdagb)
* Update Sophos detection ([#11275](https://github.com/librenms/librenms/pull/11275)) - [murrant](https://github.com/murrant)
* Add support for Cisco SX350X-24 ([#11272](https://github.com/librenms/librenms/pull/11272)) - [Chewza](https://github.com/Chewza)
* Add device support for SilverPeak ([#11270](https://github.com/librenms/librenms/pull/11270)) - [shepherdjay](https://github.com/shepherdjay)
* Update OPNsense definition due to incorrect OID on second detection method ([#11265](https://github.com/librenms/librenms/pull/11265)) - [FingerlessGlov3s](https://github.com/FingerlessGlov3s)
* Added additional Cisco FirePOWER device ID's for 2100 series ([#11260](https://github.com/librenms/librenms/pull/11260)) - [ghost](https://github.com/ghost)
* Fix "inteno" and "quanta" OS collision ([#11238](https://github.com/librenms/librenms/pull/11238)) - [PipoCanaja](https://github.com/PipoCanaja)
* Collect average server CPU usage on Huawei RH servers (IBMC) ([#11236](https://github.com/librenms/librenms/pull/11236)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for Inteno devices ([#11229](https://github.com/librenms/librenms/pull/11229)) - [PipoCanaja](https://github.com/PipoCanaja)
* Store OLD-CISCO-xxx-MIB files in their directory ([#11228](https://github.com/librenms/librenms/pull/11228)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add Device: Innovaphone ([#11225](https://github.com/librenms/librenms/pull/11225)) - [mpikzink](https://github.com/mpikzink)

#### Webui
* QueryBuilder Filter alphabetic sorting ([#11358](https://github.com/librenms/librenms/pull/11358)) - [SourceDoctor](https://github.com/SourceDoctor)
* Don't show deleted ports in device overview ([#11344](https://github.com/librenms/librenms/pull/11344)) - [murrant](https://github.com/murrant)
* Top Interfaces, exclude invalid ports ([#11338](https://github.com/librenms/librenms/pull/11338)) - [murrant](https://github.com/murrant)
* Fix invalid paths introduced in librenms/librenms#9883 ([#11337](https://github.com/librenms/librenms/pull/11337)) - [fusedsynapse](https://github.com/fusedsynapse)
* Global Settings - force_ip_to_sysname, force_hostname_to_sysname ([#11335](https://github.com/librenms/librenms/pull/11335)) - [SourceDoctor](https://github.com/SourceDoctor)
* Use format_hostname in dependencies list ([#11333](https://github.com/librenms/librenms/pull/11333)) - [PipoCanaja](https://github.com/PipoCanaja)
* Better map link scaling algorithm ([#11329](https://github.com/librenms/librenms/pull/11329)) - [kedare](https://github.com/kedare)
* Widget - TopInterface, TopDevice - device popup missing os name ([#11325](https://github.com/librenms/librenms/pull/11325)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix js warning in settings, update js deps ([#11324](https://github.com/librenms/librenms/pull/11324)) - [murrant](https://github.com/murrant)
* Replace AES by SHA for authalgo ([#11314](https://github.com/librenms/librenms/pull/11314)) - [dGs-](https://github.com/dGs-)
* Widget Fix - Server Stats - show DisplayName instead Device ID ([#11301](https://github.com/librenms/librenms/pull/11301)) - [SourceDoctor](https://github.com/SourceDoctor)
* Widget - show selected DeviceGroup in Title ([#11299](https://github.com/librenms/librenms/pull/11299)) - [SourceDoctor](https://github.com/SourceDoctor)
* Builder View Fix for Collection Selector ([#11290](https://github.com/librenms/librenms/pull/11290)) - [SourceDoctor](https://github.com/SourceDoctor)
* Form csrf missing in custom_oid edit ([#11282](https://github.com/librenms/librenms/pull/11282)) - [danislav](https://github.com/danislav)
* Sort Settings by translated names ([#11280](https://github.com/librenms/librenms/pull/11280)) - [murrant](https://github.com/murrant)
* Rewrite Poller Management to Blade/Eloquent ([#11277](https://github.com/librenms/librenms/pull/11277)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add datastore settings to the Web UI ([#11266](https://github.com/librenms/librenms/pull/11266)) - [murrant](https://github.com/murrant)
* Show the plugin's name in the title rather than just "Plugin" ([#11258](https://github.com/librenms/librenms/pull/11258)) - [cjwbath](https://github.com/cjwbath)
* Global Search - IPv4, IPv6 ([#11257](https://github.com/librenms/librenms/pull/11257)) - [SourceDoctor](https://github.com/SourceDoctor)
* Extended unbound monitoring ([#11255](https://github.com/librenms/librenms/pull/11255)) - [hanserasmus](https://github.com/hanserasmus)

#### Graphs
* Fix graph previous with rrdgraph_real_percentile ([#11306](https://github.com/librenms/librenms/pull/11306)) - [murrant](https://github.com/murrant)
* Graph CPU Steal and IO Wait ([#11235](https://github.com/librenms/librenms/pull/11235)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Applications
* Fix issue when field name is longer than allowed ([#11349](https://github.com/librenms/librenms/pull/11349)) - [murrant](https://github.com/murrant)
* Mailcow-dockerized postfix stats ([#11058](https://github.com/librenms/librenms/pull/11058)) - [crcro](https://github.com/crcro)

#### Api
* Fix bills api percentage calculation with cdr/quota of zero ([#11352](https://github.com/librenms/librenms/pull/11352)) - [Urth](https://github.com/Urth)

#### Alerting
* Alert Rule - add Operator 'in' and 'not in' ([#11327](https://github.com/librenms/librenms/pull/11327)) - [SourceDoctor](https://github.com/SourceDoctor)
* Alert Collection Rule - APC UPS Diagnostics Test Result ([#11292](https://github.com/librenms/librenms/pull/11292)) - [SourceDoctor](https://github.com/SourceDoctor)
* Alert Collection Rule fix for APC on Battery Power ([#11291](https://github.com/librenms/librenms/pull/11291)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Billing
* Fix bills api output for period=previous ([#11295](https://github.com/librenms/librenms/pull/11295)) - [Urth](https://github.com/Urth)

#### Discovery
* Fix autodiscovery vs discovery on LLDP/xDP links ([#11189](https://github.com/librenms/librenms/pull/11189)) - [kedare](https://github.com/kedare)

#### Polling
* Fixed Netscaler vserver rrd ds name ([#11304](https://github.com/librenms/librenms/pull/11304)) - [pobradovic08](https://github.com/pobradovic08)
* Refactor Datastores to allow future improvements. OpenTSDB Tags. ([#11283](https://github.com/librenms/librenms/pull/11283)) - [murrant](https://github.com/murrant)
* Fallback to default Poller Group on delete ([#11278](https://github.com/librenms/librenms/pull/11278)) - [SourceDoctor](https://github.com/SourceDoctor)
* Upgrade UCD Mibs (Others) ([#11253](https://github.com/librenms/librenms/pull/11253)) - [Munzy](https://github.com/Munzy)
* Update Linux SNMPD to support ssCpuRawSteal ([#11252](https://github.com/librenms/librenms/pull/11252)) - [Munzy](https://github.com/Munzy)
* Changed poller member validation step and message ([#11239](https://github.com/librenms/librenms/pull/11239)) - [joseUPV](https://github.com/joseUPV)

#### Bug
* Add primary key to device_graphs ([#11331](https://github.com/librenms/librenms/pull/11331)) - [rkojedzinszky](https://github.com/rkojedzinszky)
* Stop using {} for arrays ([#11319](https://github.com/librenms/librenms/pull/11319)) - [dlangille](https://github.com/dlangille)
* Only check depedencies once in validate.php ([#11316](https://github.com/librenms/librenms/pull/11316)) - [murrant](https://github.com/murrant)
* Fix get_rrd_dir() function ([#11310](https://github.com/librenms/librenms/pull/11310)) - [murrant](https://github.com/murrant)
* Fix show rrdtool command ([#11305](https://github.com/librenms/librenms/pull/11305)) - [murrant](https://github.com/murrant)
* Fix custom oids not being added to RRD after other RRD cleanups ([#11300](https://github.com/librenms/librenms/pull/11300)) - [ekoyle](https://github.com/ekoyle)
* Fix db_port config in services-wrapper ([#11284](https://github.com/librenms/librenms/pull/11284)) - [danislav](https://github.com/danislav)
* Fix - Crash in Device Group on some Operator ([#11250](https://github.com/librenms/librenms/pull/11250)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix error when deleting alert maintenance schedules ([#11232](https://github.com/librenms/librenms/pull/11232)) - [josephtingiris](https://github.com/josephtingiris)

#### Cleanup
* Symfony requires php-dom extension ([#11320](https://github.com/librenms/librenms/pull/11320)) - [nickhilliard](https://github.com/nickhilliard)

#### Documentation
* Update SNMP-Configuration-Examples.md ([#11334](https://github.com/librenms/librenms/pull/11334)) - [dagbdagb](https://github.com/dagbdagb)
* Update SMART extension documentation ([#11330](https://github.com/librenms/librenms/pull/11330)) - [raphaelyancey](https://github.com/raphaelyancey)
* Update Performance.md ([#11298](https://github.com/librenms/librenms/pull/11298)) - [dagbdagb](https://github.com/dagbdagb)
* Update Dispatcher-Service.md ([#11297](https://github.com/librenms/librenms/pull/11297)) - [dagbdagb](https://github.com/dagbdagb)
* Fix dockerized postfix app wrong url ([#11285](https://github.com/librenms/librenms/pull/11285)) - [jozefrebjak](https://github.com/jozefrebjak)
* Add quotes around the word mail ([#11267](https://github.com/librenms/librenms/pull/11267)) - [danislav](https://github.com/danislav)
* Update SNMP-Trap-Handler.md ([#11263](https://github.com/librenms/librenms/pull/11263)) - [jozefrebjak](https://github.com/jozefrebjak)
* Fix includes/defaults.inc.php references ([#11249](https://github.com/librenms/librenms/pull/11249)) - [dlangille](https://github.com/dlangille)
* Update SSL-Configuration.md ([#11223](https://github.com/librenms/librenms/pull/11223)) - [fhlmbrg](https://github.com/fhlmbrg)
* Health Sensor Advanced Discovery Example ([#11179](https://github.com/librenms/librenms/pull/11179)) - [h-barnhart](https://github.com/h-barnhart)

#### Translation
* French translation update ([#11293](https://github.com/librenms/librenms/pull/11293)) - [PipoCanaja](https://github.com/PipoCanaja)


## 1.61
*(2020-03-01)*

A big thank you to the following 21 contributors this last month:

  - [SourceDoctor](https://github.com/SourceDoctor) (22)
  - [PipoCanaja](https://github.com/PipoCanaja) (15)
  - [josephtingiris](https://github.com/josephtingiris) (9)
  - [murrant](https://github.com/murrant) (8)
  - [mpikzink](https://github.com/mpikzink) (7)
  - [Jellyfrog](https://github.com/Jellyfrog) (4)
  - [gabrielRojasNew](https://github.com/gabrielRojasNew) (4)
  - [joseUPV](https://github.com/joseUPV) (3)
  - [kedare](https://github.com/kedare) (3)
  - [kkrumm1](https://github.com/kkrumm1) (2)
  - [h-barnhart](https://github.com/h-barnhart) (1)
  - [FingerlessGlov3s](https://github.com/FingerlessGlov3s) (1)
  - [danislav](https://github.com/danislav) (1)
  - [joretapoo](https://github.com/joretapoo) (1)
  - [wikro](https://github.com/wikro) (1)
  - [pepperoni-pi](https://github.com/pepperoni-pi) (1)
  - [gcotone](https://github.com/gcotone) (1)
  - [lehuizi](https://github.com/lehuizi) (1)
  - [Pluppo](https://github.com/Pluppo) (1)
  - [Atroskelis](https://github.com/Atroskelis) (1)
  - [jasoncheng7115](https://github.com/jasoncheng7115) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [kkrumm1](https://github.com/kkrumm1) (24)
  - [SourceDoctor](https://github.com/SourceDoctor) (21)
  - [PipoCanaja](https://github.com/PipoCanaja) (20)
  - [Jellyfrog](https://github.com/Jellyfrog) (17)
  - [murrant](https://github.com/murrant) (14)
  - [laf](https://github.com/laf) (7)
  - [louis-oui](https://github.com/louis-oui) (1)
  - [arrmo](https://github.com/arrmo) (1)

#### Feature
* Option to default open Location Map on Device View ([#11167](https://github.com/librenms/librenms/pull/11167)) - [SourceDoctor](https://github.com/SourceDoctor)
* Community 10946, Option -r for ping.php ([#11161](https://github.com/librenms/librenms/pull/11161)) - [danislav](https://github.com/danislav)
* Extra VLAN mapping in AOS specific FDB module ([#11145](https://github.com/librenms/librenms/pull/11145)) - [joseUPV](https://github.com/joseUPV)
* Puppet Agent Monitoring ([#10827](https://github.com/librenms/librenms/pull/10827)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Device
* Aruba cluster userfriendly read ([#11217](https://github.com/librenms/librenms/pull/11217)) - [mpikzink](https://github.com/mpikzink)
* IOSXE ignore macSecControlledIF and macSecUncontrolledIF ifTypes ([#11214](https://github.com/librenms/librenms/pull/11214)) - [pepperoni-pi](https://github.com/pepperoni-pi)
* Additional OPNsense detection ([#11196](https://github.com/librenms/librenms/pull/11196)) - [FingerlessGlov3s](https://github.com/FingerlessGlov3s)
* Added Cambium PTP670 ([#11186](https://github.com/librenms/librenms/pull/11186)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add basic support for PICA8 devices ([#11185](https://github.com/librenms/librenms/pull/11185)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add the serial to aruba-instant devices ([#11184](https://github.com/librenms/librenms/pull/11184)) - [mpikzink](https://github.com/mpikzink)
* Huawei MIB updates ([#11181](https://github.com/librenms/librenms/pull/11181)) - [PipoCanaja](https://github.com/PipoCanaja)
* Adva FSP150cc Health Sensors ([#11168](https://github.com/librenms/librenms/pull/11168)) - [h-barnhart](https://github.com/h-barnhart)
* Add support for reading advertised BGP prefixes ([#11147](https://github.com/librenms/librenms/pull/11147)) - [kedare](https://github.com/kedare)
* Bug - Correct RFC1213 route discovery ([#11144](https://github.com/librenms/librenms/pull/11144)) - [PipoCanaja](https://github.com/PipoCanaja)
* Aruba Controller -\>Correct AP Power value ([#11122](https://github.com/librenms/librenms/pull/11122)) - [joseUPV](https://github.com/joseUPV)
* Support for Teldat routers ([#11118](https://github.com/librenms/librenms/pull/11118)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add serial to Epson printer ([#11111](https://github.com/librenms/librenms/pull/11111)) - [mpikzink](https://github.com/mpikzink)
* Add Janitza Power Sensors ([#11110](https://github.com/librenms/librenms/pull/11110)) - [mpikzink](https://github.com/mpikzink)
* Add Serialnumber to HP und Konica printers ([#11106](https://github.com/librenms/librenms/pull/11106)) - [mpikzink](https://github.com/mpikzink)
* Adding support for Cisco SB SX550X-24F switch ([#11098](https://github.com/librenms/librenms/pull/11098)) - [Pluppo](https://github.com/Pluppo)
* Added rittal-cmc (LCP Plus) ([#11091](https://github.com/librenms/librenms/pull/11091)) - [mpikzink](https://github.com/mpikzink)
* Improve Huawei VRP bgp discovery ([#11054](https://github.com/librenms/librenms/pull/11054)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Webui
* Running Alerts colorized depending on to their severity ([#11210](https://github.com/librenms/librenms/pull/11210)) - [SourceDoctor](https://github.com/SourceDoctor)
* Dashboard Widget Availability - show Hostname ([#11208](https://github.com/librenms/librenms/pull/11208)) - [SourceDoctor](https://github.com/SourceDoctor)
* Move storage module settings to discovery tab ([#11206](https://github.com/librenms/librenms/pull/11206)) - [murrant](https://github.com/murrant)
* Update JS deps ([#11203](https://github.com/librenms/librenms/pull/11203)) - [murrant](https://github.com/murrant)
* Fix global settings array validate messages ([#11199](https://github.com/librenms/librenms/pull/11199)) - [murrant](https://github.com/murrant)
* Global Settings - Uptime Warning ([#11198](https://github.com/librenms/librenms/pull/11198)) - [SourceDoctor](https://github.com/SourceDoctor)
* Global Settings - Mountpoint ignore options ([#11197](https://github.com/librenms/librenms/pull/11197)) - [SourceDoctor](https://github.com/SourceDoctor)
* Global search by IP address ([#11165](https://github.com/librenms/librenms/pull/11165)) - [josephtingiris](https://github.com/josephtingiris)
* Add Size column to edit Storage Settings table ([#11164](https://github.com/librenms/librenms/pull/11164)) - [josephtingiris](https://github.com/josephtingiris)
* Equalize default Poller Group Naming ([#11156](https://github.com/librenms/librenms/pull/11156)) - [SourceDoctor](https://github.com/SourceDoctor)
* Configurable Alert Rule default settings ([#11152](https://github.com/librenms/librenms/pull/11152)) - [SourceDoctor](https://github.com/SourceDoctor)
* Change Device, Group, Location - Order ([#11141](https://github.com/librenms/librenms/pull/11141)) - [SourceDoctor](https://github.com/SourceDoctor)
* Alert Rule label fix ([#11137](https://github.com/librenms/librenms/pull/11137)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix android-chrome icons ([#11136](https://github.com/librenms/librenms/pull/11136)) - [josephtingiris](https://github.com/josephtingiris)
* Update devices last_ping along with device_perf ([#11117](https://github.com/librenms/librenms/pull/11117)) - [josephtingiris](https://github.com/josephtingiris)
* Renovated Alert Rules ([#11115](https://github.com/librenms/librenms/pull/11115)) - [josephtingiris](https://github.com/josephtingiris)
* Fix inventory page hostname/sysname and default generate_device_link behaviour ([#11114](https://github.com/librenms/librenms/pull/11114)) - [kedare](https://github.com/kedare)
* Hide "devices added" for devices that existed before #11104 ([#11107](https://github.com/librenms/librenms/pull/11107)) - [josephtingiris](https://github.com/josephtingiris)
* Add a space for times like '1 minute' ([#11105](https://github.com/librenms/librenms/pull/11105)) - [josephtingiris](https://github.com/josephtingiris)
* Show when a device was added and last discovered ([#11104](https://github.com/librenms/librenms/pull/11104)) - [josephtingiris](https://github.com/josephtingiris)
* Speed up canAccessDevice/devicesForUser ([#10992](https://github.com/librenms/librenms/pull/10992)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Api
* BGP API: Add filter by remote ASN ([#11204](https://github.com/librenms/librenms/pull/11204)) - [kedare](https://github.com/kedare)
* Add alert_rule filtering to API ROUTE "list_alerts" ([#11109](https://github.com/librenms/librenms/pull/11109)) - [gcotone](https://github.com/gcotone)
* Added add and remove parents for device from the V0 API ([#11100](https://github.com/librenms/librenms/pull/11100)) - [gabrielRojasNew](https://github.com/gabrielRojasNew)
* Created add, edit, remove location and edit, remove services to the v… ([#11080](https://github.com/librenms/librenms/pull/11080)) - [gabrielRojasNew](https://github.com/gabrielRojasNew)

#### Alerting
* Copy existing Alert Rule ([#11195](https://github.com/librenms/librenms/pull/11195)) - [SourceDoctor](https://github.com/SourceDoctor)
* More verbose Alert Detail Fallback ([#11153](https://github.com/librenms/librenms/pull/11153)) - [SourceDoctor](https://github.com/SourceDoctor)
* Alert Rule default update ([#11143](https://github.com/librenms/librenms/pull/11143)) - [SourceDoctor](https://github.com/SourceDoctor)
* Ping Latency Check to Alert Collection ([#11139](https://github.com/librenms/librenms/pull/11139)) - [SourceDoctor](https://github.com/SourceDoctor)
* Location based Alert Rule ([#11128](https://github.com/librenms/librenms/pull/11128)) - [SourceDoctor](https://github.com/SourceDoctor)
* Default alert rules remove Devices up/down ([#11124](https://github.com/librenms/librenms/pull/11124)) - [kkrumm1](https://github.com/kkrumm1)

#### Discovery
* MPLS Route discovery - Wrong variable assignation order ([#11103](https://github.com/librenms/librenms/pull/11103)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Polling
* Reduce DB polling while getting SNMP data ([#11162](https://github.com/librenms/librenms/pull/11162)) - [SourceDoctor](https://github.com/SourceDoctor)
* Mark assigned default Poller Group ([#11112](https://github.com/librenms/librenms/pull/11112)) - [SourceDoctor](https://github.com/SourceDoctor)
* Change Poller time validation ([#11108](https://github.com/librenms/librenms/pull/11108)) - [joseUPV](https://github.com/joseUPV)

#### Bug
* Device Page: Remove csrf token from url when updating url ([#11180](https://github.com/librenms/librenms/pull/11180)) - [murrant](https://github.com/murrant)
* Space on type like "OOB Management" cause issue on smokeping, edit ge… ([#11160](https://github.com/librenms/librenms/pull/11160)) - [joretapoo](https://github.com/joretapoo)
* Invalidate 'Wrong Type' snmp_walk() data ([#11159](https://github.com/librenms/librenms/pull/11159)) - [josephtingiris](https://github.com/josephtingiris)
* Fix invalid dnos test data ([#11158](https://github.com/librenms/librenms/pull/11158)) - [murrant](https://github.com/murrant)
* Fix SSO Auth test ([#11155](https://github.com/librenms/librenms/pull/11155)) - [murrant](https://github.com/murrant)
* PHP hashes are case sensitive. ([#11151](https://github.com/librenms/librenms/pull/11151)) - [wikro](https://github.com/wikro)
* Change ups-nut sensors ([#11113](https://github.com/librenms/librenms/pull/11113)) - [mpikzink](https://github.com/mpikzink)
* Fixed unix-agent polling bug where the called method can't be found ([#11102](https://github.com/librenms/librenms/pull/11102)) - [lehuizi](https://github.com/lehuizi)

#### Documentation
* Correct docs menu name ([#11207](https://github.com/librenms/librenms/pull/11207)) - [Jellyfrog](https://github.com/Jellyfrog)
* Added documentation to edit, delete services and locations ([#11193](https://github.com/librenms/librenms/pull/11193)) - [gabrielRojasNew](https://github.com/gabrielRojasNew)
* Added steps to split DB off to its own server ([#11130](https://github.com/librenms/librenms/pull/11130)) - [SourceDoctor](https://github.com/SourceDoctor)
* Added yum-config-manager to Remi install ([#11033](https://github.com/librenms/librenms/pull/11033)) - [Atroskelis](https://github.com/Atroskelis)

#### Translation
* Update settings.php translation string ([#10928](https://github.com/librenms/librenms/pull/10928)) - [jasoncheng7115](https://github.com/jasoncheng7115)

#### Misc
* Validate that php sockets is available ([#11177](https://github.com/librenms/librenms/pull/11177)) - [murrant](https://github.com/murrant)


## 1.60
*(2020-02-04)*

A big thank you to the following 33 contributors this last month:

  - [SourceDoctor](https://github.com/SourceDoctor) (19)
  - [louis-oui](https://github.com/louis-oui) (12)
  - [josephtingiris](https://github.com/josephtingiris) (11)
  - [martijn-schmidt](https://github.com/martijn-schmidt) (3)
  - [vitalisator](https://github.com/vitalisator) (2)
  - [murrant](https://github.com/murrant) (2)
  - [h-barnhart](https://github.com/h-barnhart) (2)
  - [joshuabaird](https://github.com/joshuabaird) (2)
  - [LEV82](https://github.com/LEV82) (2)
  - [cjwbath](https://github.com/cjwbath) (2)
  - [arrmo](https://github.com/arrmo) (2)
  - [nistorj](https://github.com/nistorj) (1)
  - [kkrumm1](https://github.com/kkrumm1) (1)
  - [AnaelMobilia](https://github.com/AnaelMobilia) (1)
  - [clmcavaney](https://github.com/clmcavaney) (1)
  - [arjitc](https://github.com/arjitc) (1)
  - [dagbdagb](https://github.com/dagbdagb) (1)
  - [Jellyfrog](https://github.com/Jellyfrog) (1)
  - [klui2k1](https://github.com/klui2k1) (1)
  - [ccperilla](https://github.com/ccperilla) (1)
  - [laf](https://github.com/laf) (1)
  - [craig-nokia](https://github.com/craig-nokia) (1)
  - [JohnSPeach](https://github.com/JohnSPeach) (1)
  - [zombah](https://github.com/zombah) (1)
  - [opalivan](https://github.com/opalivan) (1)
  - [mpikzink](https://github.com/mpikzink) (1)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (1)
  - [HostIRE](https://github.com/HostIRE) (1)
  - [willhseitz](https://github.com/willhseitz) (1)
  - [SpaceDump](https://github.com/SpaceDump) (1)
  - [xorrkaz](https://github.com/xorrkaz) (1)
  - [ajsiersema](https://github.com/ajsiersema) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [kkrumm1](https://github.com/kkrumm1) (45)
  - [PipoCanaja](https://github.com/PipoCanaja) (32)
  - [Jellyfrog](https://github.com/Jellyfrog) (18)
  - [laf](https://github.com/laf) (3)
  - [murrant](https://github.com/murrant) (3)

#### Feature
* Maintenance Mode for a complete Location ([#11089](https://github.com/librenms/librenms/pull/11089)) - [SourceDoctor](https://github.com/SourceDoctor)
* Alternate Poller IP instead of Hostname ([#10981](https://github.com/librenms/librenms/pull/10981)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Device
* Fixed missing PoE graphs for Cisco devices ([#11087](https://github.com/librenms/librenms/pull/11087)) - [ajsiersema](https://github.com/ajsiersema)
* Fix Air Fiber port stats ([#11079](https://github.com/librenms/librenms/pull/11079)) - [murrant](https://github.com/murrant)
* Added OS definition and discovery for SIAE Alfo80HD ([#11063](https://github.com/librenms/librenms/pull/11063)) - [HostIRE](https://github.com/HostIRE)
* Added WUT Humidity and Temperature Sensors ([#11053](https://github.com/librenms/librenms/pull/11053)) - [mpikzink](https://github.com/mpikzink)
* Create CISCO-RESILIENT-ETHERNET-PROTOCOL-MIB ([#11052](https://github.com/librenms/librenms/pull/11052)) - [opalivan](https://github.com/opalivan)
* Minor updates for dd-wrt, clarify snmp source better ([#11051](https://github.com/librenms/librenms/pull/11051)) - [arrmo](https://github.com/arrmo)
* Add Zyxel MGS-3712 Sensors ([#11050](https://github.com/librenms/librenms/pull/11050)) - [vitalisator](https://github.com/vitalisator)
* Update IOS-XR with NCS-5500 support ([#11044](https://github.com/librenms/librenms/pull/11044)) - [zombah](https://github.com/zombah)
* Add sensors to Infoblox discovery (nios.yaml) ([#11043](https://github.com/librenms/librenms/pull/11043)) - [JohnSPeach](https://github.com/JohnSPeach)
* Update timos.inc.php ([#11040](https://github.com/librenms/librenms/pull/11040)) - [craig-nokia](https://github.com/craig-nokia)
* Fixed issue with SNMP contexts using vlan 1002-1005 on IOS devices ([#11031](https://github.com/librenms/librenms/pull/11031)) - [nistorj](https://github.com/nistorj)
* Create TRIPPLITE-PRODUCTS ([#11028](https://github.com/librenms/librenms/pull/11028)) - [arjitc](https://github.com/arjitc)
* Add support for TRENDnet switch ([#11007](https://github.com/librenms/librenms/pull/11007)) - [arrmo](https://github.com/arrmo)
* Add ifotec definition file ([#11005](https://github.com/librenms/librenms/pull/11005)) - [AnaelMobilia](https://github.com/AnaelMobilia)
* Correct preg_match() pattern on line 5 ([#10999](https://github.com/librenms/librenms/pull/10999)) - [josephtingiris](https://github.com/josephtingiris)
* Fix Siklu Voltage Sensor ([#10997](https://github.com/librenms/librenms/pull/10997)) - [joshuabaird](https://github.com/joshuabaird)
* Add state sensor for Packetflux Sitemonitor Switch Input ([#10994](https://github.com/librenms/librenms/pull/10994)) - [joshuabaird](https://github.com/joshuabaird)
* Add Lancom OAP-321 Wireless AP ([#10982](https://github.com/librenms/librenms/pull/10982)) - [vitalisator](https://github.com/vitalisator)
* Update IronWare sensors & bgp-peers discovery, allow skip_values to target a specific index appended to the OID ([#10941](https://github.com/librenms/librenms/pull/10941)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* FDB for ArubaOS ([#10940](https://github.com/librenms/librenms/pull/10940)) - [klui2k1](https://github.com/klui2k1)
* Extend FS switches support, handle lowerLayerDown ifOperStatus ([#10904](https://github.com/librenms/librenms/pull/10904)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Webui
* Mark Devices in Maintenance Mode ([#11092](https://github.com/librenms/librenms/pull/11092)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix missing ACK & Notes modals on device alert page ([#11076](https://github.com/librenms/librenms/pull/11076)) - [josephtingiris](https://github.com/josephtingiris)
* Poller Group Management - Device Count ([#11073](https://github.com/librenms/librenms/pull/11073)) - [SourceDoctor](https://github.com/SourceDoctor)
* Order Poller Group by Name ([#11072](https://github.com/librenms/librenms/pull/11072)) - [SourceDoctor](https://github.com/SourceDoctor)
* Change Poller Group on Device Settings ([#11071](https://github.com/librenms/librenms/pull/11071)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fixed device SNMP edit form (and better feedback) ([#11068](https://github.com/librenms/librenms/pull/11068)) - [josephtingiris](https://github.com/josephtingiris)
* Oxidized GUI tweaks ([#11066](https://github.com/librenms/librenms/pull/11066)) - [cjwbath](https://github.com/cjwbath)
* Add aggregate totals to multiport_bits graph, similar to port_bits ([#11065](https://github.com/librenms/librenms/pull/11065)) - [willhseitz](https://github.com/willhseitz)
* Format the Device Module Naming like in global Settings ([#11061](https://github.com/librenms/librenms/pull/11061)) - [SourceDoctor](https://github.com/SourceDoctor)
* Allow findOsImage() to also use the first two words of $feature ([#11049](https://github.com/librenms/librenms/pull/11049)) - [josephtingiris](https://github.com/josephtingiris)
* Highlight Device Dependency Path to Dependency Root Device(s) ([#11025](https://github.com/librenms/librenms/pull/11025)) - [SourceDoctor](https://github.com/SourceDoctor)
* Support of "disable alerting" in availability map and device summary widget ([#11022](https://github.com/librenms/librenms/pull/11022)) - [louis-oui](https://github.com/louis-oui)
* Highlight isolated Devices (Devices with no Dependencies) ([#11018](https://github.com/librenms/librenms/pull/11018)) - [SourceDoctor](https://github.com/SourceDoctor)
* Clarify doc and webui for ignore tag on devices, ports, components and services ([#11016](https://github.com/librenms/librenms/pull/11016)) - [louis-oui](https://github.com/louis-oui)
* "Disable alerting" on device disables alert rules check (not just alert transport) ([#11015](https://github.com/librenms/librenms/pull/11015)) - [louis-oui](https://github.com/louis-oui)
* Clarify disable, ignore and disable all alerts in device edit section ([#11011](https://github.com/librenms/librenms/pull/11011)) - [louis-oui](https://github.com/louis-oui)
* Add Blade directives for common Url functions ([#10995](https://github.com/librenms/librenms/pull/10995)) - [Jellyfrog](https://github.com/Jellyfrog)
* Alertlog stats dashboard widget ([#10967](https://github.com/librenms/librenms/pull/10967)) - [louis-oui](https://github.com/louis-oui)
* Add alert rule option to invert devices and groups "map to" list ([#10954](https://github.com/librenms/librenms/pull/10954)) - [louis-oui](https://github.com/louis-oui)
* Graphing Device Dependency ([#10916](https://github.com/librenms/librenms/pull/10916)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add 'alert history' widget for dashboard ([#10901](https://github.com/librenms/librenms/pull/10901)) - [louis-oui](https://github.com/louis-oui)
* Discovery Module and Poller Module configuration via Global Settings Web GUI ([#10854](https://github.com/librenms/librenms/pull/10854)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Snmp Traps
* VMWare Guest State Traps and UI ([#11035](https://github.com/librenms/librenms/pull/11035)) - [h-barnhart](https://github.com/h-barnhart)
* SNMP Traps for Juniper Power Supplies ([#10965](https://github.com/librenms/librenms/pull/10965)) - [h-barnhart](https://github.com/h-barnhart)

#### Applications
* Show Alert Detail for Applications ([#11088](https://github.com/librenms/librenms/pull/11088)) - [SourceDoctor](https://github.com/SourceDoctor)
* Asterisk app: add IAX2 peer graphs ([#11078](https://github.com/librenms/librenms/pull/11078)) - [josephtingiris](https://github.com/josephtingiris)
* PureFTPd Application ([#11048](https://github.com/librenms/librenms/pull/11048)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Alerting
* Remove also Associations by deleting Scheduled Maintenance ([#11093](https://github.com/librenms/librenms/pull/11093)) - [SourceDoctor](https://github.com/SourceDoctor)
* Remove redundant data from Alert fallback detail ([#11081](https://github.com/librenms/librenms/pull/11081)) - [SourceDoctor](https://github.com/SourceDoctor)
* Cisco Spark Do not strip tags when markdown is in use. ([#11075](https://github.com/librenms/librenms/pull/11075)) - [xorrkaz](https://github.com/xorrkaz)
* Added fix for escaping underscore while using Markdown ([#11070](https://github.com/librenms/librenms/pull/11070)) - [SpaceDump](https://github.com/SpaceDump)
* Add alert rule option to invert devices and groups "map to" list ([#11038](https://github.com/librenms/librenms/pull/11038)) - [louis-oui](https://github.com/louis-oui)
* Do not update alert timestamp when updating a triggered alert ([#10907](https://github.com/librenms/librenms/pull/10907)) - [louis-oui](https://github.com/louis-oui)

#### Billing
* SNMP counters validation ([#11037](https://github.com/librenms/librenms/pull/11037)) - [ccperilla](https://github.com/ccperilla)

#### Discovery
* Don't overwrite real port ids with zeros in the FDB ([#11041](https://github.com/librenms/librenms/pull/11041)) - [cjwbath](https://github.com/cjwbath)
* Add bad_ifoperstatus for filtering interfaces having a status for example 'notPresent' ([#10977](https://github.com/librenms/librenms/pull/10977)) - [LEV82](https://github.com/LEV82)

#### Polling
* Don't dnslookup if overwrite IP is configured ([#11084](https://github.com/librenms/librenms/pull/11084)) - [SourceDoctor](https://github.com/SourceDoctor)
* Optimize DB update of ports and ports_statistics tables when polling ([#10792](https://github.com/librenms/librenms/pull/10792)) - [louis-oui](https://github.com/louis-oui)

#### Bug
* Revert ironware BGP-peers changes from PR #10941 ([#11096](https://github.com/librenms/librenms/pull/11096)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* Fix snmptranslate exception ([#11085](https://github.com/librenms/librenms/pull/11085)) - [josephtingiris](https://github.com/josephtingiris)
* Updated test data for arubaos, fs-switch, ifotec, lcos, siklu and trendnet, to pass Travis tests ([#11067](https://github.com/librenms/librenms/pull/11067)) - [LEV82](https://github.com/LEV82)
* Fix FatalThrowableError in forgetAttrib() ([#11064](https://github.com/librenms/librenms/pull/11064)) - [josephtingiris](https://github.com/josephtingiris)
* Fix SQL constraint violation, 'port_id' cannot be null ([#11055](https://github.com/librenms/librenms/pull/11055)) - [josephtingiris](https://github.com/josephtingiris)
* Fix alerts not displayed ([#11034](https://github.com/librenms/librenms/pull/11034)) - [louis-oui](https://github.com/louis-oui)
* Fix & amend broken $num_ports query on line 83 ([#11013](https://github.com/librenms/librenms/pull/11013)) - [josephtingiris](https://github.com/josephtingiris)
* Bugfix the snmpsim collector: use the correct mibdir ([#11003](https://github.com/librenms/librenms/pull/11003)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* In rrdtool_escape(), fix invalid length ([#11001](https://github.com/librenms/librenms/pull/11001)) - [josephtingiris](https://github.com/josephtingiris)

#### Refactor
* Centralize Application Display Naming ([#11047](https://github.com/librenms/librenms/pull/11047)) - [SourceDoctor](https://github.com/SourceDoctor)
* Remove uneeded table header ([#11029](https://github.com/librenms/librenms/pull/11029)) - [SourceDoctor](https://github.com/SourceDoctor)
* Always update widget seeds when db update ([#10917](https://github.com/librenms/librenms/pull/10917)) - [louis-oui](https://github.com/louis-oui)

#### Cleanup
* Fixing PHP incompatibility issue with PHP 7.4 ([#11030](https://github.com/librenms/librenms/pull/11030)) - [clmcavaney](https://github.com/clmcavaney)

#### Documentation
* SELinux fix for syslog-ng with LibreNMS on Centos ([#11014](https://github.com/librenms/librenms/pull/11014)) - [dagbdagb](https://github.com/dagbdagb)

#### Translation
* Correct 'shortend' misspelling ([#11000](https://github.com/librenms/librenms/pull/11000)) - [josephtingiris](https://github.com/josephtingiris)


## 1.59
*(2020-01-04)*

A big thank you to the following 29 contributors this last month:

  - SourceDoctor (15)
  - PipoCanaja (11)
  - louis-oui (6)
  - hrtrd (4)
  - Jellyfrog (3)
  - cjwbath (3)
  - djamp42 (2)
  - murrant (2)
  - Derova (2)
  - CharlesMAtkinson (2)
  - vitalisator (2)
  - kedare (1)
  - dlehman83 (1)
  - willhseitz (1)
  - Munzy (1)
  - ProTofik (1)
  - theochita (1)
  - computman007 (1)
  - jozefrebjak (1)
  - dsgagi (1)
  - seros1521 (1)
  - kamils85 (1)
  - jviersel (1)
  - achrstl (1)
  - ajsiersema (1)
  - jayceeemperador (1)
  - dlangille (1)
  - kkrumm1 (1)
  - dbuschjr (1)

#### Feature
* Custom OID polling and graphing ([#10945](https://github.com/librenms/librenms/pull/10945)) - [louis-oui](https://github.com/louis-oui)

#### Device
* Update epmp.yaml ([#10989](https://github.com/librenms/librenms/pull/10989)) - [dbuschjr](https://github.com/dbuschjr)
* Mpls Path Visualization ([#10936](https://github.com/librenms/librenms/pull/10936)) - [vitalisator](https://github.com/vitalisator)
* Support new os GWD ([#10978](https://github.com/librenms/librenms/pull/10978)) - [hrtrd](https://github.com/hrtrd)
* Barracuda Web Application Firewall basic support ([#10970](https://github.com/librenms/librenms/pull/10970)) - [jayceeemperador](https://github.com/jayceeemperador)
* Support new os C-DATA ([#10975](https://github.com/librenms/librenms/pull/10975)) - [hrtrd](https://github.com/hrtrd)
* Ixia ANUE basic support ([#10946](https://github.com/librenms/librenms/pull/10946)) - [PipoCanaja](https://github.com/PipoCanaja)
* Support new os SNR ([#10704](https://github.com/librenms/librenms/pull/10704)) - [hrtrd](https://github.com/hrtrd)
* Categorise Cisco WLC as wireless, like the other controller-based wireless platforms ([#10953](https://github.com/librenms/librenms/pull/10953)) - [cjwbath](https://github.com/cjwbath)
* Rewrite Microsemi Midspan Power Sensors with mib ([#10959](https://github.com/librenms/librenms/pull/10959)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added Huawei UPS2000 support ([#10831](https://github.com/librenms/librenms/pull/10831)) - [PipoCanaja](https://github.com/PipoCanaja)
* Cisco Viptela basic support ([#10947](https://github.com/librenms/librenms/pull/10947)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix raisecom fan speed sensor limits ([#10930](https://github.com/librenms/librenms/pull/10930)) - [vitalisator](https://github.com/vitalisator)
* Added support for additional Edge-Core ECS devices ([#10924](https://github.com/librenms/librenms/pull/10924)) - [kamils85](https://github.com/kamils85)
* Added iBoot PDU (dataprobe) support ([#10898](https://github.com/librenms/librenms/pull/10898)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add queues to Barracuda Email Security GW ([#10915](https://github.com/librenms/librenms/pull/10915)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added detection of Edge-Core ECS4100 ([#10801](https://github.com/librenms/librenms/pull/10801)) - [jozefrebjak](https://github.com/jozefrebjak)
* Add EdgeSwitch 10XP definition ([#10909](https://github.com/librenms/librenms/pull/10909)) - [computman007](https://github.com/computman007)
* Updated Aruba ClearPass appliance detection for new sysObjectId ([#10892](https://github.com/librenms/librenms/pull/10892)) - [cjwbath](https://github.com/cjwbath)
* IES OS discovery, removed trailing "-" from sysdescr ([#10897](https://github.com/librenms/librenms/pull/10897)) - [djamp42](https://github.com/djamp42)

#### Webui
* Device group based access ([#10568](https://github.com/librenms/librenms/pull/10568)) - [Jellyfrog](https://github.com/Jellyfrog)
* Dont't show ' - ' on ping only Devices in Availability Widget ([#10988](https://github.com/librenms/librenms/pull/10988)) - [SourceDoctor](https://github.com/SourceDoctor)
* User Enabled State as Icon ([#10984](https://github.com/librenms/librenms/pull/10984)) - [SourceDoctor](https://github.com/SourceDoctor)
* Highlight Nodes on Network map ([#10943](https://github.com/librenms/librenms/pull/10943)) - [SourceDoctor](https://github.com/SourceDoctor)
* Capcity -\> Capacity ([#10974](https://github.com/librenms/librenms/pull/10974)) - [dlangille](https://github.com/dlangille)
* Fix Device Mouseover View ([#10962](https://github.com/librenms/librenms/pull/10962)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add alert history in device section ([#10972](https://github.com/librenms/librenms/pull/10972)) - [louis-oui](https://github.com/louis-oui)
* Use sysName instead of hostname on AJAX search and new billing form ([#10951](https://github.com/librenms/librenms/pull/10951)) - [kedare](https://github.com/kedare)
* Add severity filter to webui alert history ([#10918](https://github.com/librenms/librenms/pull/10918)) - [louis-oui](https://github.com/louis-oui)
* Protocol filter for Routing Table View ([#10933](https://github.com/librenms/librenms/pull/10933)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix for device config file identification ([#10942](https://github.com/librenms/librenms/pull/10942)) - [jviersel](https://github.com/jviersel)
* Fix icon for bgp search ([#10931](https://github.com/librenms/librenms/pull/10931)) - [PipoCanaja](https://github.com/PipoCanaja)
* Devices Unpolled Warning ([#10903](https://github.com/librenms/librenms/pull/10903)) - [Munzy](https://github.com/Munzy)
* Show Date from last discovery and last poll ([#10876](https://github.com/librenms/librenms/pull/10876)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Graphs
* Use format_hostname for graph default title ([#10891](https://github.com/librenms/librenms/pull/10891)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Api
* Update oxidized model mapping ([#10966](https://github.com/librenms/librenms/pull/10966)) - [seros1521](https://github.com/seros1521)
* Adds API to fetch all inventory items for a given device ([#10885](https://github.com/librenms/librenms/pull/10885)) - [ajsiersema](https://github.com/ajsiersema)
* API Call to trigger Device Discovery ([#10861](https://github.com/librenms/librenms/pull/10861)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add ability to fetch oxidized device config via the librenms API ([#10913](https://github.com/librenms/librenms/pull/10913)) - [theochita](https://github.com/theochita)

#### Alerting
* Get Colors from getColorState function for Alert Transport Rocket ([#10955](https://github.com/librenms/librenms/pull/10955)) - [SourceDoctor](https://github.com/SourceDoctor)
* Get Colors from getColorState function for Alert Transport Mattermost ([#10956](https://github.com/librenms/librenms/pull/10956)) - [SourceDoctor](https://github.com/SourceDoctor)
* Get Colors from getColorState function for Alert Transport Slack ([#10957](https://github.com/librenms/librenms/pull/10957)) - [SourceDoctor](https://github.com/SourceDoctor)
* Move Alert State Color Definition to config_defintion ([#10958](https://github.com/librenms/librenms/pull/10958)) - [SourceDoctor](https://github.com/SourceDoctor)
* Port usage perc CI/CD fix ([#10935](https://github.com/librenms/librenms/pull/10935)) - [louis-oui](https://github.com/louis-oui)
* More appropriate state colours for MS Teams alerts ([#10911](https://github.com/librenms/librenms/pull/10911)) - [cjwbath](https://github.com/cjwbath)
* Fixed port_usage_perc macro to match max(in,out) ([#10932](https://github.com/librenms/librenms/pull/10932)) - [louis-oui](https://github.com/louis-oui)
* Update alert rules with backticks ([#10902](https://github.com/librenms/librenms/pull/10902)) - [willhseitz](https://github.com/willhseitz)

#### Discovery
* Improve ports-fdb discovery sql queries ([#10883](https://github.com/librenms/librenms/pull/10883)) - [murrant](https://github.com/murrant)

#### Bug
* Fix alertlog sql after b361710148dc2d90d4df94e036d273e763e01521 ([#10991](https://github.com/librenms/librenms/pull/10991)) - [Jellyfrog](https://github.com/Jellyfrog)
* Regression fixes ([#10976](https://github.com/librenms/librenms/pull/10976)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix Capture Debug page symfony timeout error ([#10926](https://github.com/librenms/librenms/pull/10926)) - [dsgagi](https://github.com/dsgagi)
* Fix alert icons wrongly defaults to blue info level ([#10906](https://github.com/librenms/librenms/pull/10906)) - [louis-oui](https://github.com/louis-oui)

#### Refactor
* Globalize getColorForState function ([#10944](https://github.com/librenms/librenms/pull/10944)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Cleanup
* Centralize redundant discovery SQL Query ([#10875](https://github.com/librenms/librenms/pull/10875)) - [SourceDoctor](https://github.com/SourceDoctor)
* Typo VDSL2-LINE-MIB ([#10914](https://github.com/librenms/librenms/pull/10914)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Documentation
* Update alert transport doc. ([#10963](https://github.com/librenms/librenms/pull/10963)) - [kkrumm1](https://github.com/kkrumm1)
* Clarify Configuration.md ([#10960](https://github.com/librenms/librenms/pull/10960)) - [CharlesMAtkinson](https://github.com/CharlesMAtkinson)
* RouterOS 6.x ([#10961](https://github.com/librenms/librenms/pull/10961)) - [CharlesMAtkinson](https://github.com/CharlesMAtkinson)
* Update Oxidized.md ([#10864](https://github.com/librenms/librenms/pull/10864)) - [achrstl](https://github.com/achrstl)
* Updated Spelling ([#10921](https://github.com/librenms/librenms/pull/10921)) - [Derova](https://github.com/Derova)
* Removed typo from the code ([#10912](https://github.com/librenms/librenms/pull/10912)) - [ProTofik](https://github.com/ProTofik)
* Fix missing documentation parts for Seafile ([#10905](https://github.com/librenms/librenms/pull/10905)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Misc
* Fix spelling ([#10987](https://github.com/librenms/librenms/pull/10987)) - [dlehman83](https://github.com/dlehman83)


## 1.58
*(2019-11-24)*

A big thank you to the following 32 contributors this last month:

  - PipoCanaja (19)
  - murrant (18)
  - SourceDoctor (15)
  - louis-oui (9)
  - Jellyfrog (4)
  - vitalisator (3)
  - rj-taylor (3)
  - jasoncheng7115 (2)
  - gdepeyrot (2)
  - joseUPV (2)
  - GramThanos (1)
  - opalivan (1)
  - BrianSidebotham (1)
  - arrmo (1)
  - dlesel (1)
  - seros1521 (1)
  - jozefrebjak (1)
  - mendoza-conicet (1)
  - willhseitz (1)
  - MattWSL (1)
  - evheros (1)
  - kadecole (1)
  - joretapoo (1)
  - laf (1)
  - Cormoran96 (1)
  - nomyownnet (1)
  - thecityofguanyu (1)
  - isarandi (1)
  - erotel (1)
  - corsoblaster (1)
  - andreasmalta (1)
  - hanserasmus (1)

#### Feature
* Trigger Device Rediscovery for a device group ([#10832](https://github.com/librenms/librenms/pull/10832)) - [SourceDoctor](https://github.com/SourceDoctor)
* Allow user activation/deactivation (MySQL auth) ([#10511](https://github.com/librenms/librenms/pull/10511)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Security
* Fix restricted application access for normal user ([#10802](https://github.com/librenms/librenms/pull/10802)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Device
* Added basic Ubiquoss PON support ([#10828](https://github.com/librenms/librenms/pull/10828)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added basic Zyxel GS-4012F support ([#10829](https://github.com/librenms/librenms/pull/10829)) - [PipoCanaja](https://github.com/PipoCanaja)
* Extend Dell Laser printer support for S5830dn and similar ([#10878](https://github.com/librenms/librenms/pull/10878)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fixed Cisco Catalyst9x00 support ([#10862](https://github.com/librenms/librenms/pull/10862)) - [PipoCanaja](https://github.com/PipoCanaja)
* Extend discovery of Konica printers ([#10806](https://github.com/librenms/librenms/pull/10806)) - [andreasmalta](https://github.com/andreasmalta)
* Add support for ZyXEL IES4206/5206/5212/6217 MSANs ([#10789](https://github.com/librenms/librenms/pull/10789)) - [vitalisator](https://github.com/vitalisator)
* Update Alcatel mibs to v8 ([#10857](https://github.com/librenms/librenms/pull/10857)) - [joseUPV](https://github.com/joseUPV)
* Added support EdgeCore ECS2100-10T ([#10843](https://github.com/librenms/librenms/pull/10843)) - [erotel](https://github.com/erotel)
* Added hardware and software version for ASR9906 ([#10826](https://github.com/librenms/librenms/pull/10826)) - [nomyownnet](https://github.com/nomyownnet)
* Add support for ZyXEL IES-5005 and IES-5106 DSLAMs ([#10804](https://github.com/librenms/librenms/pull/10804)) - [vitalisator](https://github.com/vitalisator)
* Brocade switches - Added names on fiberchannel ports ([#10737](https://github.com/librenms/librenms/pull/10737)) - [evheros](https://github.com/evheros)
* Added support for Mikrotik LTE Modem ([#10805](https://github.com/librenms/librenms/pull/10805)) - [jozefrebjak](https://github.com/jozefrebjak)
* Added AIX file system, prefer over hrstorage for AIX ([#10588](https://github.com/librenms/librenms/pull/10588)) - [dlesel](https://github.com/dlesel)
* Add support for HP PDU Management Module ([#10784](https://github.com/librenms/librenms/pull/10784)) - [Jellyfrog](https://github.com/Jellyfrog)
* Added device support for Chatsworth-PDU ([#10769](https://github.com/librenms/librenms/pull/10769)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added device support for Zyxel AnyOS ([#10770](https://github.com/librenms/librenms/pull/10770)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added basic device support for Infinera PON 7090 platform ([#10771](https://github.com/librenms/librenms/pull/10771)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for Nexans Switches ([#10772](https://github.com/librenms/librenms/pull/10772)) - [PipoCanaja](https://github.com/PipoCanaja)
* Allow all models of Asentria SiteBoss ([#10746](https://github.com/librenms/librenms/pull/10746)) - [willhseitz](https://github.com/willhseitz)
* Fixed Tomato (router firmware OS), 'no bulk' enabled ([#10775](https://github.com/librenms/librenms/pull/10775)) - [arrmo](https://github.com/arrmo)
* Better detection of old 3Com StackSwitches ([#10736](https://github.com/librenms/librenms/pull/10736)) - [joseUPV](https://github.com/joseUPV)
* Avoid state collisions between 'dell' and 'drac' OSes ([#10539](https://github.com/librenms/librenms/pull/10539)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Webui
* Add minimum password length setting ([#10867](https://github.com/librenms/librenms/pull/10867)) - [murrant](https://github.com/murrant)
* Graphical alerts update (text/icon alignment) ([#10856](https://github.com/librenms/librenms/pull/10856)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix webui settings - AD bind at wrong place ([#10874](https://github.com/librenms/librenms/pull/10874)) - [louis-oui](https://github.com/louis-oui)
* Fix static device group updated message ([#10841](https://github.com/librenms/librenms/pull/10841)) - [murrant](https://github.com/murrant)
* Deactivate 'Delete' button in Device Dependencies page when necessary ([#10852](https://github.com/librenms/librenms/pull/10852)) - [SourceDoctor](https://github.com/SourceDoctor)
* Custom favicon issue ([#10847](https://github.com/librenms/librenms/pull/10847)) - [corsoblaster](https://github.com/corsoblaster)
* Added support for routing table collection in discovery ([#10182](https://github.com/librenms/librenms/pull/10182)) - [PipoCanaja](https://github.com/PipoCanaja)
* Allow user specific themes ([#10799](https://github.com/librenms/librenms/pull/10799)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix some IE issues with settings page ([#10819](https://github.com/librenms/librenms/pull/10819)) - [murrant](https://github.com/murrant)
* Alphabetic order of app overview ([#10825](https://github.com/librenms/librenms/pull/10825)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix maps display only one link if multiple links are present between … ([#10818](https://github.com/librenms/librenms/pull/10818)) - [louis-oui](https://github.com/louis-oui)
* Clarify wording of disable/alert device settings and dependency between the two settings ([#10809](https://github.com/librenms/librenms/pull/10809)) - [gdepeyrot](https://github.com/gdepeyrot)
* Show logged in user in title menu ([#10800](https://github.com/librenms/librenms/pull/10800)) - [SourceDoctor](https://github.com/SourceDoctor)
* Device List: add metrics icons tooltip ([#10811](https://github.com/librenms/librenms/pull/10811)) - [kadecole](https://github.com/kadecole)
* UI bug fix - Scroll disabled on "new rule from collection" modal ([#10796](https://github.com/librenms/librenms/pull/10796)) - [GramThanos](https://github.com/GramThanos)
* Prevent sessions from expiring ([#10798](https://github.com/librenms/librenms/pull/10798)) - [murrant](https://github.com/murrant)
* Comparison fix in slas.inc.php ([#10812](https://github.com/librenms/librenms/pull/10812)) - [seros1521](https://github.com/seros1521)
* Unbundle javascript language files ([#10788](https://github.com/librenms/librenms/pull/10788)) - [murrant](https://github.com/murrant)
* Fix email_auto_tls toggle ([#10785](https://github.com/librenms/librenms/pull/10785)) - [murrant](https://github.com/murrant)
* Fix missing values for recurring alert state ([#10793](https://github.com/librenms/librenms/pull/10793)) - [SourceDoctor](https://github.com/SourceDoctor)
* Bootstrapping checkboxes, cont. ([#10782](https://github.com/librenms/librenms/pull/10782)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add a deprecated warning to the front pages ([#10783](https://github.com/librenms/librenms/pull/10783)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix oxidized url setting validator ([#10766](https://github.com/librenms/librenms/pull/10766)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix settings search ([#10777](https://github.com/librenms/librenms/pull/10777)) - [murrant](https://github.com/murrant)
* Bootstrap checkboxes ([#10749](https://github.com/librenms/librenms/pull/10749)) - [SourceDoctor](https://github.com/SourceDoctor)
* Replaced WorldMap zoom with text field to enable more fine grained zooming by steps of 0.1 ([#10753](https://github.com/librenms/librenms/pull/10753)) - [gdepeyrot](https://github.com/gdepeyrot)
* Fix asset urls on settings page ([#10765](https://github.com/librenms/librenms/pull/10765)) - [murrant](https://github.com/murrant)
* Settings.php key fix ([#10774](https://github.com/librenms/librenms/pull/10774)) - [SourceDoctor](https://github.com/SourceDoctor)
* Adding more default values to config_definitions ([#10756](https://github.com/librenms/librenms/pull/10756)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix broken relationship of local Service ID with SDP Service ID ([#10713](https://github.com/librenms/librenms/pull/10713)) - [vitalisator](https://github.com/vitalisator)
* Fix mydomain setting regex ([#10762](https://github.com/librenms/librenms/pull/10762)) - [murrant](https://github.com/murrant)

#### Authentication
* LDAP Add option to authenticate user independtly of OU ([#10873](https://github.com/librenms/librenms/pull/10873)) - [louis-oui](https://github.com/louis-oui)
* Fix LDAP slow login and unable to login ([#10872](https://github.com/librenms/librenms/pull/10872)) - [louis-oui](https://github.com/louis-oui)
* Fix auth_test script does not do ldap bind ([#10865](https://github.com/librenms/librenms/pull/10865)) - [louis-oui](https://github.com/louis-oui)

#### Applications
* Added certificate file validity check for services (#10354) ([#10354](https://github.com/librenms/librenms/pull/10354)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix mdadm sync speed title description ([#10773](https://github.com/librenms/librenms/pull/10773)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Api
* Add interval extra var on API add_rule and edit_rule ([#10814](https://github.com/librenms/librenms/pull/10814)) - [joretapoo](https://github.com/joretapoo)
* Removal of IP address validation for ip field in add_service_for_host API call ([#10810](https://github.com/librenms/librenms/pull/10810)) - [MattWSL](https://github.com/MattWSL)
* List_arp search by MAC ([#10803](https://github.com/librenms/librenms/pull/10803)) - [murrant](https://github.com/murrant)
* Create device groups via API ([#10791](https://github.com/librenms/librenms/pull/10791)) - [BrianSidebotham](https://github.com/BrianSidebotham)
* Fix API arp cidr search ([#10780](https://github.com/librenms/librenms/pull/10780)) - [murrant](https://github.com/murrant)

#### Alerting
* Do not purge alert_log table entries that have a matching active alert in alerts table ([#10744](https://github.com/librenms/librenms/pull/10744)) - [louis-oui](https://github.com/louis-oui)
* Add Headers and body to API Transports ([#10614](https://github.com/librenms/librenms/pull/10614)) - [mendoza-conicet](https://github.com/mendoza-conicet)

#### Polling
* Perf optimisation for xDSL mib polling ([#10815](https://github.com/librenms/librenms/pull/10815)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Bug
* Fix syslog prune when dbFetchRow() returns array ([#10850](https://github.com/librenms/librenms/pull/10850)) - [rj-taylor](https://github.com/rj-taylor)
* Fixes Zyxel MIB product definition ([#10824](https://github.com/librenms/librenms/pull/10824)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix snmp v3 when set via ui ([#10797](https://github.com/librenms/librenms/pull/10797)) - [murrant](https://github.com/murrant)
* Fix invalid check for device-\>isUnderMaintenance() in worldmap ([#10778](https://github.com/librenms/librenms/pull/10778)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Refactor
* Update device cache ([#10795](https://github.com/librenms/librenms/pull/10795)) - [murrant](https://github.com/murrant)

#### Cleanup
* Prevent syslog table purge from spamming daily.log ([#10851](https://github.com/librenms/librenms/pull/10851)) - [rj-taylor](https://github.com/rj-taylor)

#### Documentation
* Documentation link fixes ([#10848](https://github.com/librenms/librenms/pull/10848)) - [rj-taylor](https://github.com/rj-taylor)
* Deleted yum package listed twice ([#10758](https://github.com/librenms/librenms/pull/10758)) - [hanserasmus](https://github.com/hanserasmus)

#### Translation
* German translation for settings page ([#10764](https://github.com/librenms/librenms/pull/10764)) - [SourceDoctor](https://github.com/SourceDoctor)
* Translation correction ([#10821](https://github.com/librenms/librenms/pull/10821)) - [Cormoran96](https://github.com/Cormoran96)
* Update zh-TW Translate settings strings ([#10816](https://github.com/librenms/librenms/pull/10816)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Translation generation command ([#10794](https://github.com/librenms/librenms/pull/10794)) - [murrant](https://github.com/murrant)
* Translate settings strings to zh-TW ([#10716](https://github.com/librenms/librenms/pull/10716)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Translation of settings in French ([#10763](https://github.com/librenms/librenms/pull/10763)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Misc
* Update dependencies ([#10830](https://github.com/librenms/librenms/pull/10830)) - [murrant](https://github.com/murrant)
* Add check_oracle service include file and make 'packet_loss_' macro alerts not use radio buttons ([#10807](https://github.com/librenms/librenms/pull/10807)) - [thecityofguanyu](https://github.com/thecityofguanyu)


## 1.57
*(2019-10-28)*

A big thank you to the following 29 contributors this last month:

  - murrant (30)
  - Jellyfrog (7)
  - joseUPV (4)
  - vitalisator (3)
  - justinh-rahb (3)
  - jozefrebjak (3)
  - SourceDoctor (3)
  - AnaelMobilia (2)
  - deajan (2)
  - pobradovic08 (2)
  - wgroenewold (2)
  - dGs- (2)
  - theister-xan (2)
  - PipoCanaja (2)
  - vdchuyen (1)
  - sk4mi (1)
  - DreadnaughtSec (1)
  - pedjaj (1)
  - hrtrd (1)
  - louis-oui (1)
  - theochita (1)
  - hanserasmus (1)
  - sorano (1)
  - p-a-b (1)
  - ngohoa211 (1)
  - evheros (1)
  - robertobru (1)
  - ubnt-tim (1)
  - bewing (1)

#### Feature
* Add lnms commands to get and set config settings ([#10534](https://github.com/librenms/librenms/pull/10534)) - [murrant](https://github.com/murrant)

#### Device
* Nokia ISAM add mempool_perc_warn level ([#10722](https://github.com/librenms/librenms/pull/10722)) - [vitalisator](https://github.com/vitalisator)
* Removed double Stack Temperature Discovery and Fixed typos in dbm discovery for Dell Switches ([#10630](https://github.com/librenms/librenms/pull/10630)) - [evheros](https://github.com/evheros)
* Add new discovery patterns in edgeswitch.yaml ([#10515](https://github.com/librenms/librenms/pull/10515)) - [ubnt-tim](https://github.com/ubnt-tim)
* Added support for drac power and current polling ([#10634](https://github.com/librenms/librenms/pull/10634)) - [theister-xan](https://github.com/theister-xan)
* Support cellular RSSI on IOS XE ([#10726](https://github.com/librenms/librenms/pull/10726)) - [murrant](https://github.com/murrant)
* Add CISCO-IF-EXTENSION port stats for IOS/IOS-XE ([#10644](https://github.com/librenms/librenms/pull/10644)) - [pobradovic08](https://github.com/pobradovic08)
* Added Cisco SGE OS Detection ([#10697](https://github.com/librenms/librenms/pull/10697)) - [joseUPV](https://github.com/joseUPV)
* Better Support of Alcatel Switches ([#10672](https://github.com/librenms/librenms/pull/10672)) - [joseUPV](https://github.com/joseUPV)
* Update riello.yaml to add the oid for the Netman 204 boards as in issue #10576 ([#10725](https://github.com/librenms/librenms/pull/10725)) - [robertobru](https://github.com/robertobru)
* Added NetApp E2700 Discovery ([#10668](https://github.com/librenms/librenms/pull/10668)) - [jozefrebjak](https://github.com/jozefrebjak)
* Add support for Illustra network cameras ([#10721](https://github.com/librenms/librenms/pull/10721)) - [justinh-rahb](https://github.com/justinh-rahb)
* Update Transition Networks support ([#10714](https://github.com/librenms/librenms/pull/10714)) - [justinh-rahb](https://github.com/justinh-rahb)
* Add sysObjectID for Riello netman 204 firmware 02.17 ([#10641](https://github.com/librenms/librenms/pull/10641)) - [sorano](https://github.com/sorano)
* Improved RARITAN PDU/PDU2 MIB (current, power) and external env. sensors ([#10616](https://github.com/librenms/librenms/pull/10616)) - [theister-xan](https://github.com/theister-xan)
* Corrected OS detection of Nortel Baystack 3510 ([#10689](https://github.com/librenms/librenms/pull/10689)) - [joseUPV](https://github.com/joseUPV)
* Rename RutOS 2xx pinState state sensor to avoid conflict ([#10675](https://github.com/librenms/librenms/pull/10675)) - [murrant](https://github.com/murrant)
* Add support RedLion N-Tron 714FX6 industrial switch ([#10617](https://github.com/librenms/librenms/pull/10617)) - [pedjaj](https://github.com/pedjaj)
* Better Model detection of Old 3Com devices ([#10660](https://github.com/librenms/librenms/pull/10660)) - [joseUPV](https://github.com/joseUPV)
* Added Teltonika Rutos-2xx wireless sensors ([#10646](https://github.com/librenms/librenms/pull/10646)) - [jozefrebjak](https://github.com/jozefrebjak)
* Add Cisco QFP module ([#10637](https://github.com/librenms/librenms/pull/10637)) - [pobradovic08](https://github.com/pobradovic08)

#### Webui
* Add Graylog settings ([#10740](https://github.com/librenms/librenms/pull/10740)) - [murrant](https://github.com/murrant)
* Remove $_SESSION usage, except install ([#10745](https://github.com/librenms/librenms/pull/10745)) - [murrant](https://github.com/murrant)
* Fix broken Graylog link ([#10742](https://github.com/librenms/librenms/pull/10742)) - [vitalisator](https://github.com/vitalisator)
* Use Mix built in cache busting ([#10733](https://github.com/librenms/librenms/pull/10733)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix the menu that filters out erroring ports ([#10680](https://github.com/librenms/librenms/pull/10680)) - [p-a-b](https://github.com/p-a-b)
* Fix service overview ([#10709](https://github.com/librenms/librenms/pull/10709)) - [murrant](https://github.com/murrant)
* Add distributed polling config settings ([#10711](https://github.com/librenms/librenms/pull/10711)) - [murrant](https://github.com/murrant)
* Restore base_url base tag ([#10705](https://github.com/librenms/librenms/pull/10705)) - [murrant](https://github.com/murrant)
* Consolidate configuration settings and implement dynamic webui settings ([#9809](https://github.com/librenms/librenms/pull/9809)) - [murrant](https://github.com/murrant)
* Alphabetic sorting of  global settings view ([#10678](https://github.com/librenms/librenms/pull/10678)) - [SourceDoctor](https://github.com/SourceDoctor)
* Sort app overview graphs by hostname ([#10663](https://github.com/librenms/librenms/pull/10663)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Applications
* Seafile Server Monitoring ([#10465](https://github.com/librenms/librenms/pull/10465)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Api
* Add hostname search for list_devices function ([#10652](https://github.com/librenms/librenms/pull/10652)) - [sk4mi](https://github.com/sk4mi)
* Implement Oxidized "config search" as an API ([#10648](https://github.com/librenms/librenms/pull/10648)) - [theochita](https://github.com/theochita)

#### Alerting
* Alerts - Add features + serial in alerts-\> table ([#10747](https://github.com/librenms/librenms/pull/10747)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix incorrect url in Alerta transport ([#10684](https://github.com/librenms/librenms/pull/10684)) - [ngohoa211](https://github.com/ngohoa211)
* Update Slack.php add emoji and username support ([#10695](https://github.com/librenms/librenms/pull/10695)) - [hrtrd](https://github.com/hrtrd)
* When sending email alerts use CRLF for windows clients ([#10563](https://github.com/librenms/librenms/pull/10563)) - [murrant](https://github.com/murrant)

#### Discovery
* If dot1qVlanCurrentTable doesn't exist try dot1qVlanStaticTable ([#10262](https://github.com/librenms/librenms/pull/10262)) - [dGs-](https://github.com/dGs-)
* Do not update mempool warning % every discovery ([#10647](https://github.com/librenms/librenms/pull/10647)) - [murrant](https://github.com/murrant)
* Add discovery of mempool percent warning limit ([#10618](https://github.com/librenms/librenms/pull/10618)) - [vitalisator](https://github.com/vitalisator)

#### Polling
* Update includes/polling/os/unix.inc.php to detect aarch64/ARM64/ARMv8 ([#10698](https://github.com/librenms/librenms/pull/10698)) - [justinh-rahb](https://github.com/justinh-rahb)
* Fix duplicate ping response causing false down ([#10692](https://github.com/librenms/librenms/pull/10692)) - [murrant](https://github.com/murrant)
* Add redis sentinel support to dispatcher service ([#10598](https://github.com/librenms/librenms/pull/10598)) - [bewing](https://github.com/bewing)

#### Bug
* Remove default 'temp_dir' value '/tmp' ([#10754](https://github.com/librenms/librenms/pull/10754)) - [deajan](https://github.com/deajan)
* Typo in routes/web.php ([#10750](https://github.com/librenms/librenms/pull/10750)) - [PipoCanaja](https://github.com/PipoCanaja)
* Strip backslash return from snmp_get extend ([#10724](https://github.com/librenms/librenms/pull/10724)) - [vdchuyen](https://github.com/vdchuyen)
* Fix issue when non-existent plugin is enabled ([#10699](https://github.com/librenms/librenms/pull/10699)) - [murrant](https://github.com/murrant)
* Allow temp_dir to be correctly set in LibreNMS\Config ([#10654](https://github.com/librenms/librenms/pull/10654)) - [deajan](https://github.com/deajan)
* Fix device_groups in alert/group builder ([#10643](https://github.com/librenms/librenms/pull/10643)) - [murrant](https://github.com/murrant)

#### Refactor
* Device Url: return directly if user doesn't have access ([#10730](https://github.com/librenms/librenms/pull/10730)) - [Jellyfrog](https://github.com/Jellyfrog)
* Validate config schema, add types to all ([#10723](https://github.com/librenms/librenms/pull/10723)) - [murrant](https://github.com/murrant)
* Refactor tests ([#10625](https://github.com/librenms/librenms/pull/10625)) - [murrant](https://github.com/murrant)
* Removed the ksort block as it was not working ([#10674](https://github.com/librenms/librenms/pull/10674)) - [dGs-](https://github.com/dGs-)
* Store config data serialized ([#10651](https://github.com/librenms/librenms/pull/10651)) - [murrant](https://github.com/murrant)

#### Cleanup
* Always sort indexes in dump_db_schema ([#10732](https://github.com/librenms/librenms/pull/10732)) - [Jellyfrog](https://github.com/Jellyfrog)
* Remove function report_this_text() ([#10728](https://github.com/librenms/librenms/pull/10728)) - [Jellyfrog](https://github.com/Jellyfrog)
* Markdown linting of docs ([#10595](https://github.com/librenms/librenms/pull/10595)) - [Jellyfrog](https://github.com/Jellyfrog)
* Optimize docs picture size ([#10657](https://github.com/librenms/librenms/pull/10657)) - [Jellyfrog](https://github.com/Jellyfrog)
* Optimize logo sizes ([#10656](https://github.com/librenms/librenms/pull/10656)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Documentation
* Add steps to store smokeping rrd in librenms folder ([#10676](https://github.com/librenms/librenms/pull/10676)) - [AnaelMobilia](https://github.com/AnaelMobilia)
* Update manual to right path ([#10727](https://github.com/librenms/librenms/pull/10727)) - [wgroenewold](https://github.com/wgroenewold)
* Add memcached to DS-docs ([#10715](https://github.com/librenms/librenms/pull/10715)) - [hanserasmus](https://github.com/hanserasmus)
* Include setting nfdump binary path in NfSen docs ([#10707](https://github.com/librenms/librenms/pull/10707)) - [jozefrebjak](https://github.com/jozefrebjak)
* Smokeping config syntax fix ([#10710](https://github.com/librenms/librenms/pull/10710)) - [wgroenewold](https://github.com/wgroenewold)
* Remove update=0 in distributed poller docs ([#10681](https://github.com/librenms/librenms/pull/10681)) - [murrant](https://github.com/murrant)
* Improve migration informations ([#10673](https://github.com/librenms/librenms/pull/10673)) - [AnaelMobilia](https://github.com/AnaelMobilia)
* Update SNMP-Configuration-Examples.md ([#10662](https://github.com/librenms/librenms/pull/10662)) - [DreadnaughtSec](https://github.com/DreadnaughtSec)
* Fix CentOS PHP install docs ([#10645](https://github.com/librenms/librenms/pull/10645)) - [murrant](https://github.com/murrant)


## 1.56
*(2019-09-30)*

A big thank you to the following 35 contributors this last month:

  - pobradovic08 (5)
  - vitalisator (5)
  - SourceDoctor (4)
  - jasoncheng7115 (4)
  - murrant (3)
  - Bounzz (2)
  - bestlong (2)
  - Jellyfrog (2)
  - wilreichert (1)
  - VVelox (1)
  - dsgagi (1)
  - nwautomator (1)
  - nistorj (1)
  - rsys-dev (1)
  - hanserasmus (1)
  - lfkeitel (1)
  - erotel (1)
  - garysteers (1)
  - takenalias (1)
  - feuerrot (1)
  - deajan (1)
  - Rosiak (1)
  - SniperVegeta (1)
  - jozefrebjak (1)
  - PipoCanaja (1)
  - Derova (1)
  - seros1521 (1)
  - brownowski (1)
  - fbourqui (1)
  - dGs- (1)
  - YisroelTech (1)
  - CameronMunroe (1)
  - soto2080 (1)
  - Cormoran96 (1)
  - FTBZ (1)

#### Device
* Added Cisco QFP processor ([#10622](https://github.com/librenms/librenms/pull/10622)) - [pobradovic08](https://github.com/pobradovic08)
* Add EdgeCore ECS4110-28T Support ([#10525](https://github.com/librenms/librenms/pull/10525)) - [soto2080](https://github.com/soto2080)
* Support VIOS which use a different string: ([#10623](https://github.com/librenms/librenms/pull/10623)) - [fbourqui](https://github.com/fbourqui)
* Added CISCO-ENTITY-QFP-MIB MIB ([#10621](https://github.com/librenms/librenms/pull/10621)) - [pobradovic08](https://github.com/pobradovic08)
* AeroHive OS Wirelless Noise Floor Fix ([#10608](https://github.com/librenms/librenms/pull/10608)) - [jozefrebjak](https://github.com/jozefrebjak)
* Skip notPresent ports from state sensors ([#10545](https://github.com/librenms/librenms/pull/10545)) - [Rosiak](https://github.com/Rosiak)
* Eltek eNexus sensor improvements ([#10591](https://github.com/librenms/librenms/pull/10591)) - [vitalisator](https://github.com/vitalisator)
* Add more Vigor series device support ([#10562](https://github.com/librenms/librenms/pull/10562)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Fix Ericsson MINI LINK wifi sensors ([#10566](https://github.com/librenms/librenms/pull/10566)) - [erotel](https://github.com/erotel)
* Added Additional support for Dell CMC ([#10571](https://github.com/librenms/librenms/pull/10571)) - [takenalias](https://github.com/takenalias)
* Added basic state details for Infoblox NIOS ([#10572](https://github.com/librenms/librenms/pull/10572)) - [FTBZ](https://github.com/FTBZ)

#### Webui
* Device overview - group utilization per processor type ([#10626](https://github.com/librenms/librenms/pull/10626)) - [pobradovic08](https://github.com/pobradovic08)
* Fix LibreNMS Logo SVG missing i sometimes ([#10632](https://github.com/librenms/librenms/pull/10632)) - [YisroelTech](https://github.com/YisroelTech)
* Allow OS to define config highlighting (pfSense) ([#10392](https://github.com/librenms/librenms/pull/10392)) - [brownowski](https://github.com/brownowski)
* List ungrouped devices on group management page ([#10527](https://github.com/librenms/librenms/pull/10527)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix wrong formatting of runtime sensor value ([#10606](https://github.com/librenms/librenms/pull/10606)) - [vitalisator](https://github.com/vitalisator)
* Fix missing to-parameter in dashboard graph widget link ([#10600](https://github.com/librenms/librenms/pull/10600)) - [feuerrot](https://github.com/feuerrot)
* Use more distinct HTML div element ids when listing alerts in tables ([#10587](https://github.com/librenms/librenms/pull/10587)) - [dsgagi](https://github.com/dsgagi)
* Move Authlog to Laravel ([#10559](https://github.com/librenms/librenms/pull/10559)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Graphs
* Storage RRD - descriptionlength fix - percent column fix ([#10607](https://github.com/librenms/librenms/pull/10607)) - [SourceDoctor](https://github.com/SourceDoctor)
* Extended 'port previous' graphs ([#10556](https://github.com/librenms/librenms/pull/10556)) - [SniperVegeta](https://github.com/SniperVegeta)

#### Alerting
* Add procedure URL to alert templates ([#10609](https://github.com/librenms/librenms/pull/10609)) - [seros1521](https://github.com/seros1521)
* Alert template clean up  "\r\n\n" ([#10541](https://github.com/librenms/librenms/pull/10541)) - [bestlong](https://github.com/bestlong)
* Add LINE Notify Alert Transport. ([#10495](https://github.com/librenms/librenms/pull/10495)) - [bestlong](https://github.com/bestlong)
* Catch exceptions generated by alert transports ([#10565](https://github.com/librenms/librenms/pull/10565)) - [lfkeitel](https://github.com/lfkeitel)

#### Discovery
* Do not discover dbm sensors on shutdown ports ([#10610](https://github.com/librenms/librenms/pull/10610)) - [vitalisator](https://github.com/vitalisator)
* Sensors sometime not clean up ([#10611](https://github.com/librenms/librenms/pull/10611)) - [vitalisator](https://github.com/vitalisator)
* Fixed foreach loop throwing errors when no vlans present ([#10599](https://github.com/librenms/librenms/pull/10599)) - [nistorj](https://github.com/nistorj)

#### Polling
* Add support for per-OS SNMP max repeaters configuration file setting. ([#10628](https://github.com/librenms/librenms/pull/10628)) - [nwautomator](https://github.com/nwautomator)
* MPLS only poll if records exist ([#10523](https://github.com/librenms/librenms/pull/10523)) - [murrant](https://github.com/murrant)

#### Bug
* Remove ambiguity in Component filter query ([#10638](https://github.com/librenms/librenms/pull/10638)) - [pobradovic08](https://github.com/pobradovic08)
* Replaced description by ifAlias to fix the sort on Description ([#10633](https://github.com/librenms/librenms/pull/10633)) - [dGs-](https://github.com/dGs-)
* Fix creating device groups issue ([#10521](https://github.com/librenms/librenms/pull/10521)) - [murrant](https://github.com/murrant)
* Web UI: Fix port URLs on minimaps page ([#10619](https://github.com/librenms/librenms/pull/10619)) - [pobradovic08](https://github.com/pobradovic08)
* Fix timeout typo in apiclients ([#10615](https://github.com/librenms/librenms/pull/10615)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add index to notifications_attribs to resolve slow page load on MySQL 5.7 ([#10589](https://github.com/librenms/librenms/pull/10589)) - [wilreichert](https://github.com/wilreichert)
* Geographical map fix for "normal user" ([#10590](https://github.com/librenms/librenms/pull/10590)) - [SourceDoctor](https://github.com/SourceDoctor)
* Graylog fix query with multiple items ([#10583](https://github.com/librenms/librenms/pull/10583)) - [rsys-dev](https://github.com/rsys-dev)

#### Documentation
* Add example for EdgeOs Ubiquiti ([#10639](https://github.com/librenms/librenms/pull/10639)) - [Cormoran96](https://github.com/Cormoran96)
* Asterisk Doc Improvements ([#10631](https://github.com/librenms/librenms/pull/10631)) - [CameronMunroe](https://github.com/CameronMunroe)
* Dispatcher Service: Documentation Typo ([#10620](https://github.com/librenms/librenms/pull/10620)) - [Derova](https://github.com/Derova)
* Improve CentOS 7 install instructions ([#10477](https://github.com/librenms/librenms/pull/10477)) - [deajan](https://github.com/deajan)
* More documentation clean up ([#10577](https://github.com/librenms/librenms/pull/10577)) - [VVelox](https://github.com/VVelox)
* Fix documentation TOC ([#10580](https://github.com/librenms/librenms/pull/10580)) - [murrant](https://github.com/murrant)
* Added linear prediction how-to ([#10581](https://github.com/librenms/librenms/pull/10581)) - [hanserasmus](https://github.com/hanserasmus)
* Updated Fast-Ping-Check documentation for distributed pollers ([#10575](https://github.com/librenms/librenms/pull/10575)) - [garysteers](https://github.com/garysteers)

#### Translation
* French translation minor update ([#10640](https://github.com/librenms/librenms/pull/10640)) - [PipoCanaja](https://github.com/PipoCanaja)
* Adding german language support ([#10584](https://github.com/librenms/librenms/pull/10584)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add Graylog severity translation ([#10593](https://github.com/librenms/librenms/pull/10593)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Modify the syslog widget can be translate ([#10594](https://github.com/librenms/librenms/pull/10594)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* French translation ([#10586](https://github.com/librenms/librenms/pull/10586)) - [Bounzz](https://github.com/Bounzz)
* Update zh-TW.json for authlog page ([#10579](https://github.com/librenms/librenms/pull/10579)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Update fr.json ([#10578](https://github.com/librenms/librenms/pull/10578)) - [Bounzz](https://github.com/Bounzz)


## 1.55
*(2019-09-02)*

A big thank you to the following 31 contributors this last month:

  - murrant (16)
  - PipoCanaja (9)
  - Jellyfrog (4)
  - SniperVegeta (3)
  - SourceDoctor (3)
  - hanserasmus (3)
  - deajan (2)
  - garysteers (2)
  - jasoncheng7115 (2)
  - rsys-dev (2)
  - VVelox (2)
  - TvL2386 (2)
  - voipmeister (1)
  - dsgagi (1)
  - jvit (1)
  - frankmcc (1)
  - FTBZ (1)
  - h-barnhart (1)
  - arrmo (1)
  - nsn-amagruder (1)
  - SteFletcher (1)
  - soto2080 (1)
  - opalivan (1)
  - erotel (1)
  - martijn-schmidt (1)
  - XioNoX (1)
  - Serazio (1)
  - rdezavalia (1)
  - BrianSidebotham (1)
  - fbourqui (1)
  - Munzy (1)

#### Feature
* Added Graylog to device overview and log level filter mechanism ([#10509](https://github.com/librenms/librenms/pull/10509)) - [rsys-dev](https://github.com/rsys-dev)
* Allow adding custom quick links to device navigation ([#10403](https://github.com/librenms/librenms/pull/10403)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Simple linear port graph prediction ([#10520](https://github.com/librenms/librenms/pull/10520)) - [murrant](https://github.com/murrant)
* Allow filtering of Health sensor discovery ([#10485](https://github.com/librenms/librenms/pull/10485)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Security
* Fix html injection in user fields ([#10535](https://github.com/librenms/librenms/pull/10535)) - [murrant](https://github.com/murrant)
* Update the docs to reflect various updates to SNMP/local bits ([#10507](https://github.com/librenms/librenms/pull/10507)) - [VVelox](https://github.com/VVelox)

#### Device
* Added AIX detection running std snmpd or net-snmp ([#10569](https://github.com/librenms/librenms/pull/10569)) - [fbourqui](https://github.com/fbourqui)
* Added more DELL switches in order to get proper CPU stats ([#10529](https://github.com/librenms/librenms/pull/10529)) - [rdezavalia](https://github.com/rdezavalia)
* Fixed Junos port/vlan relationships for els and non-els based software ([#10321](https://github.com/librenms/librenms/pull/10321)) - [Serazio](https://github.com/Serazio)
* Add serial/model/version polling for Sentry4 MIB ([#10432](https://github.com/librenms/librenms/pull/10432)) - [XioNoX](https://github.com/XioNoX)
* Convert opengear to YAML-based discovery, add some new sensors, add test data ([#10553](https://github.com/librenms/librenms/pull/10553)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* Added support Ericsson MINI-LINK ([#10546](https://github.com/librenms/librenms/pull/10546)) - [erotel](https://github.com/erotel)
* NXOS can build FDB table too ([#10522](https://github.com/librenms/librenms/pull/10522)) - [soto2080](https://github.com/soto2080)
* Reduce discovery snmp load of Cisco VTP vlans module ([#10510](https://github.com/librenms/librenms/pull/10510)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added new device - Exagrid ([#10496](https://github.com/librenms/librenms/pull/10496)) - [nsn-amagruder](https://github.com/nsn-amagruder)
* Added support for OS dd-wrt ([#10500](https://github.com/librenms/librenms/pull/10500)) - [arrmo](https://github.com/arrmo)
* Watchguard Fireware is FirewareOS in oxidized ([#10494](https://github.com/librenms/librenms/pull/10494)) - [deajan](https://github.com/deajan)
* Fixed missing FW and Serials in Dlink ([#10481](https://github.com/librenms/librenms/pull/10481)) - [hanserasmus](https://github.com/hanserasmus)
* Added definition for Cisco SB SG250X ([#10472](https://github.com/librenms/librenms/pull/10472)) - [PipoCanaja](https://github.com/PipoCanaja)
* Extended RecoveryOS Definition ([#10475](https://github.com/librenms/librenms/pull/10475)) - [h-barnhart](https://github.com/h-barnhart)

#### Webui
* Show a hint that trend line exists ([#10573](https://github.com/librenms/librenms/pull/10573)) - [murrant](https://github.com/murrant)
* Convert About page to Laravel ([#10551](https://github.com/librenms/librenms/pull/10551)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sort Devices and Groups in Alert Rules 'map to' droplist ([#10530](https://github.com/librenms/librenms/pull/10530)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fixed menu links that used to redirect to # ([#10540](https://github.com/librenms/librenms/pull/10540)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix alert log status ([#10524](https://github.com/librenms/librenms/pull/10524)) - [SniperVegeta](https://github.com/SniperVegeta)
* Fix public status location ([#10526](https://github.com/librenms/librenms/pull/10526)) - [murrant](https://github.com/murrant)
* Fix Syslog widget ([#10516](https://github.com/librenms/librenms/pull/10516)) - [murrant](https://github.com/murrant)
* Add device group filter to widgets ([#9692](https://github.com/librenms/librenms/pull/9692)) - [Jellyfrog](https://github.com/Jellyfrog)
* Nfdump support for with NFSen ([#10376](https://github.com/librenms/librenms/pull/10376)) - [VVelox](https://github.com/VVelox)
* Laravel 5.8 and updated dependencies ([#10489](https://github.com/librenms/librenms/pull/10489)) - [murrant](https://github.com/murrant)
* Add an option to hide Location column in Alerts widget ([#10482](https://github.com/librenms/librenms/pull/10482)) - [dsgagi](https://github.com/dsgagi)
* Update services.inc.php ([#10486](https://github.com/librenms/librenms/pull/10486)) - [SniperVegeta](https://github.com/SniperVegeta)
* Check PHP version first ([#10473](https://github.com/librenms/librenms/pull/10473)) - [murrant](https://github.com/murrant)
* Add resources/views/menu/ to .gitignore ([#10479](https://github.com/librenms/librenms/pull/10479)) - [frankmcc](https://github.com/frankmcc)

#### Graphs
* Fix some graphs having the wrong timeframe ([#10554](https://github.com/librenms/librenms/pull/10554)) - [murrant](https://github.com/murrant)
* Added Cisco-voice IP graphs ([#10538](https://github.com/librenms/librenms/pull/10538)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fixed unauth graphs not working ([#10483](https://github.com/librenms/librenms/pull/10483)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Applications
* Smart -power on hour -  view fix ([#10466](https://github.com/librenms/librenms/pull/10466)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Api
* Ports API: Workaround for ifNames with slashes ([#10502](https://github.com/librenms/librenms/pull/10502)) - [murrant](https://github.com/murrant)
* Allowed device_ids as INT or as STRING ([#10536](https://github.com/librenms/librenms/pull/10536)) - [TvL2386](https://github.com/TvL2386)
* Move API routing to Laravel ([#10457](https://github.com/librenms/librenms/pull/10457)) - [murrant](https://github.com/murrant)

#### Alerting
* Include alert ID in alert templates ([#10552](https://github.com/librenms/librenms/pull/10552)) - [SniperVegeta](https://github.com/SniperVegeta)

#### Discovery
* Allow num_oid to use OCTET STRING indexes ([#10410](https://github.com/librenms/librenms/pull/10410)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Polling
* Add inbit and outbit rate to auxiliary datastores. ([#10512](https://github.com/librenms/librenms/pull/10512)) - [SteFletcher](https://github.com/SteFletcher)

#### Bug
* Fix testing fping output when the LibreNMS user doesn't have a valid … ([#10567](https://github.com/librenms/librenms/pull/10567)) - [BrianSidebotham](https://github.com/BrianSidebotham)
* Fix Graylog level -1 display ([#10560](https://github.com/librenms/librenms/pull/10560)) - [murrant](https://github.com/murrant)
* Use PHP sys_temp_dir by default ([#10428](https://github.com/librenms/librenms/pull/10428)) - [deajan](https://github.com/deajan)
* Fix ipv6_network_id query for `null` context_name ([#10544](https://github.com/librenms/librenms/pull/10544)) - [garysteers](https://github.com/garysteers)
* Fixed Incorrect device match in Graylog ([#10501](https://github.com/librenms/librenms/pull/10501)) - [rsys-dev](https://github.com/rsys-dev)
* Fix .env with number symbols ([#10497](https://github.com/librenms/librenms/pull/10497)) - [murrant](https://github.com/murrant)

#### Documentation
* Update FAQ.md ([#10513](https://github.com/librenms/librenms/pull/10513)) - [hanserasmus](https://github.com/hanserasmus)
* Update Applications.md for mysql ([#10549](https://github.com/librenms/librenms/pull/10549)) - [opalivan](https://github.com/opalivan)
* Fixes the invalid json of the example curl statement ([#10537](https://github.com/librenms/librenms/pull/10537)) - [TvL2386](https://github.com/TvL2386)
* Update Installation-CentOS-7-Apache.md ([#10504](https://github.com/librenms/librenms/pull/10504)) - [hanserasmus](https://github.com/hanserasmus)
* Typo fix, minor textual changes in support docs ([#10499](https://github.com/librenms/librenms/pull/10499)) - [voipmeister](https://github.com/voipmeister)
* Fix installation instructions for Ubuntu-1804 ([#10488](https://github.com/librenms/librenms/pull/10488)) - [jvit](https://github.com/jvit)
* Oxidized - Recover a configuration of a disabled/removed device ([#10469](https://github.com/librenms/librenms/pull/10469)) - [FTBZ](https://github.com/FTBZ)
* Missing an I from Input ([#10474](https://github.com/librenms/librenms/pull/10474)) - [Munzy](https://github.com/Munzy)

#### Translation
* Update zh-TW.json for about page ([#10558](https://github.com/librenms/librenms/pull/10558)) - [jasoncheng7115](https://github.com/jasoncheng7115)

#### Misc
* Automatically cleanup plugin-table from removed plugins ([#10533](https://github.com/librenms/librenms/pull/10533)) - [SourceDoctor](https://github.com/SourceDoctor)
* Remove legacy auth usage of $_SESSION ([#10491](https://github.com/librenms/librenms/pull/10491)) - [murrant](https://github.com/murrant)


## 1.54
*(2019-07-28)*

A big thank you to the following 32 contributors this last month:

  - murrant (7)
  - CirnoT (6)
  - PipoCanaja (5)
  - jozefrebjak (5)
  - h-barnhart (2)
  - vitalisator (2)
  - VVelox (2)
  - N-Mi (2)
  - rsys-dev (2)
  - arrmo (2)
  - jasoncheng7115 (1)
  - xorrkaz (1)
  - tgregory86 (1)
  - sthen (1)
  - steffann (1)
  - sajanp (1)
  - SourceDoctor (1)
  - ospfbgp (1)
  - awarre (1)
  - filippog (1)
  - Serazio (1)
  - bergroth (1)
  - rmedlyn (1)
  - bestlong (1)
  - djamp42 (1)
  - VirTechSystems (1)
  - MinePlugins (1)
  - ig0rb (1)
  - ifred16 (1)
  - martijn-schmidt (1)
  - Derova (1)
  - abuzze (1)

#### Feature
* MPLS Services ([#10421](https://github.com/librenms/librenms/pull/10421)) - [vitalisator](https://github.com/vitalisator)
* Graylog entry matching device if source is not hostname or primary ip ([#10458](https://github.com/librenms/librenms/pull/10458)) - [rsys-dev](https://github.com/rsys-dev)
* Allow filtering of getUserlist LDAP function ([#10399](https://github.com/librenms/librenms/pull/10399)) - [ifred16](https://github.com/ifred16)

#### Breaking Change
* Refactor Api transport to use Guzzle (and new variables syntax) ([#10070](https://github.com/librenms/librenms/pull/10070)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Security
* Enable CSRF protection ([#10447](https://github.com/librenms/librenms/pull/10447)) - [murrant](https://github.com/murrant)

#### Device
* Add OpenWrt OS support (discovery, poller) ([#10454](https://github.com/librenms/librenms/pull/10454)) - [arrmo](https://github.com/arrmo)
* Temperature limits from MIB ([#10326](https://github.com/librenms/librenms/pull/10326)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* FS.net pdu ([#10424](https://github.com/librenms/librenms/pull/10424)) - [ig0rb](https://github.com/ig0rb)
* Added Support For Teltonika RUT2XX Devices ([#10358](https://github.com/librenms/librenms/pull/10358)) - [jozefrebjak](https://github.com/jozefrebjak)
* Fix NetAgent II battery voltage ([#10427](https://github.com/librenms/librenms/pull/10427)) - [CirnoT](https://github.com/CirnoT)
* Add phase2 name to fortigate IPSEC sensor. ([#10423](https://github.com/librenms/librenms/pull/10423)) - [VirTechSystems](https://github.com/VirTechSystems)
* Add svg image for Roku ([#10448](https://github.com/librenms/librenms/pull/10448)) - [arrmo](https://github.com/arrmo)
* Added VRP SFPs thresholds and map entPhysical to ifIndexes ([#10363](https://github.com/librenms/librenms/pull/10363)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added F5 ssl tps, global server/client connection rate and active connections ([#9883](https://github.com/librenms/librenms/pull/9883)) - [Serazio](https://github.com/Serazio)
* Updating RAPID-CITY mib for Extreme VSP ([#10406](https://github.com/librenms/librenms/pull/10406)) - [ospfbgp](https://github.com/ospfbgp)
* Update $rancid_map For Mikrotik ([#10426](https://github.com/librenms/librenms/pull/10426)) - [sajanp](https://github.com/sajanp)
* New Sensor Data, Nokia Subscriber Statistics ([#10422](https://github.com/librenms/librenms/pull/10422)) - [vitalisator](https://github.com/vitalisator)
* Added detection for ZTE zxdsl devices ([#10344](https://github.com/librenms/librenms/pull/10344)) - [rsys-dev](https://github.com/rsys-dev)
* Added Support for Unitrends Backup ([#10411](https://github.com/librenms/librenms/pull/10411)) - [jozefrebjak](https://github.com/jozefrebjak)
* Added VPN sensors for Fortinet Fortigate ([#10384](https://github.com/librenms/librenms/pull/10384)) - [jozefrebjak](https://github.com/jozefrebjak)

#### Webui
* Fix Google maps lat/lon query string ([#10463](https://github.com/librenms/librenms/pull/10463)) - [filippog](https://github.com/filippog)
* Fix 2fa enable ([#10462](https://github.com/librenms/librenms/pull/10462)) - [murrant](https://github.com/murrant)
* Add more detail to webgui alerts ([#10388](https://github.com/librenms/librenms/pull/10388)) - [djamp42](https://github.com/djamp42)
* Fix Nan value broken maps network graph ([#10408](https://github.com/librenms/librenms/pull/10408)) - [bestlong](https://github.com/bestlong)
* Fix structure of network_map_legend default ([#10429](https://github.com/librenms/librenms/pull/10429)) - [rmedlyn](https://github.com/rmedlyn)
* Smoking outgoing graphs fix ([#10415](https://github.com/librenms/librenms/pull/10415)) - [steffann](https://github.com/steffann)
* Set Service Ignore and Disabled in UI ([#10334](https://github.com/librenms/librenms/pull/10334)) - [h-barnhart](https://github.com/h-barnhart)
* Replace color indicator near uptime counter with colored text and change color of status indicator to black on disabled devices instead of gray (matches availablity map with show ignored/disabled enabled) ([#10372](https://github.com/librenms/librenms/pull/10372)) - [CirnoT](https://github.com/CirnoT)
* Ignore disabled components in component widget ([#10369](https://github.com/librenms/librenms/pull/10369)) - [abuzze](https://github.com/abuzze)

#### Alerting
* Fix alert and template test scripts ([#10464](https://github.com/librenms/librenms/pull/10464)) - [murrant](https://github.com/murrant)
* Alert Subsys to OOP and SNMPTraps trigger Alert Rules ([#9765](https://github.com/librenms/librenms/pull/9765)) - [h-barnhart](https://github.com/h-barnhart)
* Add support for using Markdown for the Ciscospark transport ([#10442](https://github.com/librenms/librenms/pull/10442)) - [xorrkaz](https://github.com/xorrkaz)

#### Billing
* Don't display INF% for percentage of used transfer when billing is CDR ([#10446](https://github.com/librenms/librenms/pull/10446)) - [CirnoT](https://github.com/CirnoT)
* Properly format 95th CDR as SI Mbps in billing ([#10444](https://github.com/librenms/librenms/pull/10444)) - [CirnoT](https://github.com/CirnoT)
* Fix missing background on progress-bar for 95th bills ([#10443](https://github.com/librenms/librenms/pull/10443)) - [CirnoT](https://github.com/CirnoT)

#### Discovery
* Added support for DGD and BER on newer infinera-groove FW ([#10435](https://github.com/librenms/librenms/pull/10435)) - [bergroth](https://github.com/bergroth)
* Ensure that sysName is trimmed on discovery ([#10434](https://github.com/librenms/librenms/pull/10434)) - [CirnoT](https://github.com/CirnoT)
* Added skip_values operator and documentation ([#10419](https://github.com/librenms/librenms/pull/10419)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Bug
* Typo in Spelling prevented new services from being added. ([#10420](https://github.com/librenms/librenms/pull/10420)) - [tgregory86](https://github.com/tgregory86)
* Fix doc test after changelog split ([#10412](https://github.com/librenms/librenms/pull/10412)) - [N-Mi](https://github.com/N-Mi)

#### Documentation
* Fix docs build ([#10461](https://github.com/librenms/librenms/pull/10461)) - [murrant](https://github.com/murrant)
* Fixed broken links ([#10459](https://github.com/librenms/librenms/pull/10459)) - [Derova](https://github.com/Derova)
* Transport API - Doc for placeholders ([#10416](https://github.com/librenms/librenms/pull/10416)) - [PipoCanaja](https://github.com/PipoCanaja)
* Mistake in url API ([#10455](https://github.com/librenms/librenms/pull/10455)) - [MinePlugins](https://github.com/MinePlugins)
* More md linting ([#10371](https://github.com/librenms/librenms/pull/10371)) - [VVelox](https://github.com/VVelox)
* Fixed typo: Administartor ([#10437](https://github.com/librenms/librenms/pull/10437)) - [awarre](https://github.com/awarre)
* Mdadm application documentation ([#10430](https://github.com/librenms/librenms/pull/10430)) - [SourceDoctor](https://github.com/SourceDoctor)
* Make Applications.md more lint happy and add a section on sudo at the top ([#10367](https://github.com/librenms/librenms/pull/10367)) - [VVelox](https://github.com/VVelox)
* Split 2017 and 2018 changelogs ([#10404](https://github.com/librenms/librenms/pull/10404)) - [N-Mi](https://github.com/N-Mi)
* Update Smokeping.md ([#10407](https://github.com/librenms/librenms/pull/10407)) - [jozefrebjak](https://github.com/jozefrebjak)
* Update Portactivity Applications.md ([#10394](https://github.com/librenms/librenms/pull/10394)) - [jozefrebjak](https://github.com/jozefrebjak)

#### Translation
* Make Disabled/Shutdown to be translatable ([#10398](https://github.com/librenms/librenms/pull/10398)) - [jasoncheng7115](https://github.com/jasoncheng7115)

#### Misc
* Avoid unnecessary net-snmp long options for version info ([#10405](https://github.com/librenms/librenms/pull/10405)) - [sthen](https://github.com/sthen)


## 1.53
*(2019-07-01)*

A big thank you to the following 28 contributors this last month:

  - murrant (32)
  - jozefrebjak (4)
  - SourceDoctor (4)
  - jasoncheng7115 (4)
  - PipoCanaja (4)
  - ckforum (3)
  - VVelox (3)
  - vitalisator (3)
  - djamp42 (3)
  - martijn-schmidt (3)
  - llarian0 (2)
  - JoshWeepie (2)
  - kkrumm1 (2)
  - TheGreatDoc (1)
  - funzoneq (1)
  - CirnoT (1)
  - TheMysteriousX (1)
  - Marlinc (1)
  - daniviga (1)
  - rkislov (1)
  - StackOverBuffer (1)
  - janyksteenbeek (1)
  - p4k8 (1)
  - Cormoran96 (1)
  - tvcabomz (1)
  - SniperVegeta (1)
  - N-Mi (1)
  - mjducharme (1)

#### Feature
* Allow sysName to be specified in lnms device:add for ping only ([#10381](https://github.com/librenms/librenms/pull/10381)) - [murrant](https://github.com/murrant)
* Rewritten device groups (including static) ([#10295](https://github.com/librenms/librenms/pull/10295)) - [murrant](https://github.com/murrant)
* Add MPLS Support ([#10263](https://github.com/librenms/librenms/pull/10263)) - [vitalisator](https://github.com/vitalisator)
* Added aggregate config option to Billing 95th percentile calculations ([#10202](https://github.com/librenms/librenms/pull/10202)) - [llarian0](https://github.com/llarian0)

#### Device
* Create sensors.php and wireless.php for zh-TW ([#10368](https://github.com/librenms/librenms/pull/10368)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Nokia bgp admin status fix ([#10370](https://github.com/librenms/librenms/pull/10370)) - [vitalisator](https://github.com/vitalisator)
* WIP - Added VRP SFPs thresholds and map entPhysical to ifIndexes ([#10355](https://github.com/librenms/librenms/pull/10355)) - [PipoCanaja](https://github.com/PipoCanaja)
* Aruba IAP: Fix Radio State Sensor ([#10335](https://github.com/librenms/librenms/pull/10335)) - [kkrumm1](https://github.com/kkrumm1)
* Add raisecom os version, hardware and serial data ([#10336](https://github.com/librenms/librenms/pull/10336)) - [vitalisator](https://github.com/vitalisator)
* Add power sensor for eaton ups ([#10306](https://github.com/librenms/librenms/pull/10306)) - [StackOverBuffer](https://github.com/StackOverBuffer)
* Support for Fibernet XMUX4+ ([#10331](https://github.com/librenms/librenms/pull/10331)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Extend the Dell drac module to report CMC status and sensors ([#10310](https://github.com/librenms/librenms/pull/10310)) - [daniviga](https://github.com/daniviga)
* Cisco UCOS Version and Feature Fix ([#10307](https://github.com/librenms/librenms/pull/10307)) - [djamp42](https://github.com/djamp42)
* Add Ipoman power consumption support ([#10244](https://github.com/librenms/librenms/pull/10244)) - [Marlinc](https://github.com/Marlinc)
* CIMC Version Fix ([#10284](https://github.com/librenms/librenms/pull/10284)) - [djamp42](https://github.com/djamp42)
* Add a 'session' sensor for PanOS, SRX5800 Flow Accounting ([#8857](https://github.com/librenms/librenms/pull/8857)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Convert OS mrv-od to yaml, discover more sensors, discover entity-physical ([#10266](https://github.com/librenms/librenms/pull/10266)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* Icotera support for 6400 and 6800 series. ([#9755](https://github.com/librenms/librenms/pull/9755)) - [funzoneq](https://github.com/funzoneq)
* Added detection and sensors for Huawei SMU device ([#10267](https://github.com/librenms/librenms/pull/10267)) - [jozefrebjak](https://github.com/jozefrebjak)

#### Webui
* Use sensor labels for overview/inventory pages, refactor some html-page related code ([#10287](https://github.com/librenms/librenms/pull/10287)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* Add custom title to alert widget ([#10373](https://github.com/librenms/librenms/pull/10373)) - [djamp42](https://github.com/djamp42)
* Add 2 css class graph - minigraph for bg color and other ([#10318](https://github.com/librenms/librenms/pull/10318)) - [ckforum](https://github.com/ckforum)
* Improve UI of filter menu ([#10348](https://github.com/librenms/librenms/pull/10348)) - [JoshWeepie](https://github.com/JoshWeepie)
* Sort Device Applications in Optionbar alphabetic ([#10324](https://github.com/librenms/librenms/pull/10324)) - [SourceDoctor](https://github.com/SourceDoctor)
* Update VMWare guest IDs ([#10338](https://github.com/librenms/librenms/pull/10338)) - [murrant](https://github.com/murrant)
* Fix language select for new languages ([#10323](https://github.com/librenms/librenms/pull/10323)) - [murrant](https://github.com/murrant)
* Fix smokeping graphs ([#10317](https://github.com/librenms/librenms/pull/10317)) - [murrant](https://github.com/murrant)
* Change \<h2\>\</h1\> ? by span and class like the other ([#10305](https://github.com/librenms/librenms/pull/10305)) - [ckforum](https://github.com/ckforum)
* Add overlib link css class for changing background color - Update Url.php ([#10300](https://github.com/librenms/librenms/pull/10300)) - [ckforum](https://github.com/ckforum)
* WEB UI Changed color of Non Unicast Packets ([#10289](https://github.com/librenms/librenms/pull/10289)) - [jozefrebjak](https://github.com/jozefrebjak)
* Restore vminfo menu ([#10303](https://github.com/librenms/librenms/pull/10303)) - [murrant](https://github.com/murrant)
* Fix global service count showing on device overview ([#10301](https://github.com/librenms/librenms/pull/10301)) - [murrant](https://github.com/murrant)
* User Management: use url helpers ([#10288](https://github.com/librenms/librenms/pull/10288)) - [murrant](https://github.com/murrant)
* Only allow mysql auth type to add users ([#10283](https://github.com/librenms/librenms/pull/10283)) - [murrant](https://github.com/murrant)
* Don't show warning when ignored device is online (pingable) ([#10286](https://github.com/librenms/librenms/pull/10286)) - [CirnoT](https://github.com/CirnoT)
* Store language select name in translation files ([#10272](https://github.com/librenms/librenms/pull/10272)) - [murrant](https://github.com/murrant)
* Fixed Quick Graphs bug w/ Aggregate 95th code ([#10269](https://github.com/librenms/librenms/pull/10269)) - [llarian0](https://github.com/llarian0)

#### Graphs
* Fix atuc chan curr tx rate/atur chan curr tx rate ([#10383](https://github.com/librenms/librenms/pull/10383)) - [SniperVegeta](https://github.com/SniperVegeta)
* Support relative time for graphs again ([#10359](https://github.com/librenms/librenms/pull/10359)) - [murrant](https://github.com/murrant)
* Mdadm rrd graph fix ([#10312](https://github.com/librenms/librenms/pull/10312)) - [SourceDoctor](https://github.com/SourceDoctor)
* Removed broken POE graphing code ([#10188](https://github.com/librenms/librenms/pull/10188)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Applications
* Sort arrays before storing them in a component ([#10329](https://github.com/librenms/librenms/pull/10329)) - [VVelox](https://github.com/VVelox)
* Smart application database update fix ([#10378](https://github.com/librenms/librenms/pull/10378)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix an issue where the order may be random when adding values to the UPS-APC RRD ([#10375](https://github.com/librenms/librenms/pull/10375)) - [VVelox](https://github.com/VVelox)
* Enhance smart to show power_on_hours also ([#10261](https://github.com/librenms/librenms/pull/10261)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Alerting
* Fix Dummy alert transport ([#10379](https://github.com/librenms/librenms/pull/10379)) - [murrant](https://github.com/murrant)
* Don't include time macros in field list ([#10299](https://github.com/librenms/librenms/pull/10299)) - [murrant](https://github.com/murrant)

#### Security
* Sanitize graph input ([#10276](https://github.com/librenms/librenms/pull/10276)) - [murrant](https://github.com/murrant)
* Sanitize report name in pdf.php ([#10270](https://github.com/librenms/librenms/pull/10270)) - [murrant](https://github.com/murrant)

#### Bug
* Fix broken updates ([#10380](https://github.com/librenms/librenms/pull/10380)) - [murrant](https://github.com/murrant)
* Fix mysql bug in cisco-sla module ([#10357](https://github.com/librenms/librenms/pull/10357)) - [tvcabomz](https://github.com/tvcabomz)
* Check correct path for config in the installer ([#10333](https://github.com/librenms/librenms/pull/10333)) - [janyksteenbeek](https://github.com/janyksteenbeek)
* Fix hytera_h2f bug ([#10281](https://github.com/librenms/librenms/pull/10281)) - [murrant](https://github.com/murrant)
* Fix for RouterOS LLDP discovery ([#10265](https://github.com/librenms/librenms/pull/10265)) - [mjducharme](https://github.com/mjducharme)

#### Documentation
* Fix markdown in Changelog ([#10387](https://github.com/librenms/librenms/pull/10387)) - [N-Mi](https://github.com/N-Mi)
* Update and fix link for Migrating from Observium ([#10377](https://github.com/librenms/librenms/pull/10377)) - [kkrumm1](https://github.com/kkrumm1)
* Go through making lots of the docs more lint happy ([#10342](https://github.com/librenms/librenms/pull/10342)) - [VVelox](https://github.com/VVelox)
* Remove guessed limits for some health sensors, documentation for sensor classes ([#10327](https://github.com/librenms/librenms/pull/10327)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* Fix multiple typos in SNMP Trap doc ([#10343](https://github.com/librenms/librenms/pull/10343)) - [JoshWeepie](https://github.com/JoshWeepie)
* Split install steps into git clone and composer install ([#10249](https://github.com/librenms/librenms/pull/10249)) - [murrant](https://github.com/murrant)
* Example SNMP Trap handler class ([#10311](https://github.com/librenms/librenms/pull/10311)) - [jozefrebjak](https://github.com/jozefrebjak)
* Added configs of huawei devices into syslog.md ([#10309](https://github.com/librenms/librenms/pull/10309)) - [jozefrebjak](https://github.com/jozefrebjak)

#### Translation
* Update zh-TW ([#10391](https://github.com/librenms/librenms/pull/10391)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Update zh-TW ([#10361](https://github.com/librenms/librenms/pull/10361)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* French language support ([#10277](https://github.com/librenms/librenms/pull/10277)) - [Cormoran96](https://github.com/Cormoran96)
* Traditional Chinese language support ([#10178](https://github.com/librenms/librenms/pull/10178)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Russian language update ([#10319](https://github.com/librenms/librenms/pull/10319)) - [rkislov](https://github.com/rkislov)
* Ukrainian language support ([#10328](https://github.com/librenms/librenms/pull/10328)) - [p4k8](https://github.com/p4k8)
* Enable menu translation ([#10298](https://github.com/librenms/librenms/pull/10298)) - [murrant](https://github.com/murrant)

#### Misc
* Use Config class instead of global ([#10339](https://github.com/librenms/librenms/pull/10339)) - [murrant](https://github.com/murrant)
* Update dependencies ([#10325](https://github.com/librenms/librenms/pull/10325)) - [murrant](https://github.com/murrant)
* Warn maintenance tasks are disabled ([#10273](https://github.com/librenms/librenms/pull/10273)) - [murrant](https://github.com/murrant)
* Dispatcher Service: Remove duplicate polling complete message ([#10290](https://github.com/librenms/librenms/pull/10290)) - [murrant](https://github.com/murrant)


## 1.52
*(2019-05-27)*

A big thank you to the following 28 contributors this last month:

  - murrant (30)
  - CirnoT (13)
  - h-barnhart (4)
  - PipoCanaja (4)
  - twilley (3)
  - pobradovic08 (3)
  - corsoblaster (2)
  - spencerbutler (2)
  - kmpanilla (2)
  - jozefrebjak (2)
  - slashdoom (2)
  - marvink87 (1)
  - davidmnelson (1)
  - Anthony25 (1)
  - supertylerc (1)
  - rkislov (1)
  - LeoWinterDE (1)
  - sparkkraps (1)
  - vitalisator (1)
  - SourceDoctor (1)
  - dsgagi (1)
  - VirTechSystems (1)
  - efelon (1)
  - deesel (1)
  - thomseddon (1)
  - mjducharme (1)
  - daniviga (1)
  - JoshWeepie (1)

#### Feature
* User configurable locale (language) ([#10204](https://github.com/librenms/librenms/pull/10204)) - [murrant](https://github.com/murrant)
* LLDP Discovery by IP ([#10130](https://github.com/librenms/librenms/pull/10130)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added additional generic SNMP trap handlers ([#10177](https://github.com/librenms/librenms/pull/10177)) - [CirnoT](https://github.com/CirnoT)

#### Device
* Fix power consumption detection on Dell servers ([#10250](https://github.com/librenms/librenms/pull/10250)) - [daniviga](https://github.com/daniviga)
* Updated OS (ftd) for Cisco FirePOWER devices ([#10046](https://github.com/librenms/librenms/pull/10046)) - [spencerbutler](https://github.com/spencerbutler)
* Update to planetos for ISG-* models ([#10152](https://github.com/librenms/librenms/pull/10152)) - [kmpanilla](https://github.com/kmpanilla)
* ArubaOS - fix client count polling, add ap count polling. ([#10231](https://github.com/librenms/librenms/pull/10231)) - [twilley](https://github.com/twilley)
* Added support for East iStars UPS (os: istars) ([#10041](https://github.com/librenms/librenms/pull/10041)) - [spencerbutler](https://github.com/spencerbutler)
* Collect Appliance serial number from Sophos device ([#10210](https://github.com/librenms/librenms/pull/10210)) - [corsoblaster](https://github.com/corsoblaster)
* Fix Siklu Version/Serial ([#10235](https://github.com/librenms/librenms/pull/10235)) - [kmpanilla](https://github.com/kmpanilla)
* Added Wireless discovery to Huawei Vrp ([#9516](https://github.com/librenms/librenms/pull/9516)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add support for ZyNOS MGS switches ([#10234](https://github.com/librenms/librenms/pull/10234)) - [vitalisator](https://github.com/vitalisator)
* Initial detection for huawei gpon MDU ([#9733](https://github.com/librenms/librenms/pull/9733)) - [jozefrebjak](https://github.com/jozefrebjak)
* Replace Cyberoam with Sophos (icon and logo) ([#10213](https://github.com/librenms/librenms/pull/10213)) - [corsoblaster](https://github.com/corsoblaster)
* NEW OS Aruba Instant ([#9954](https://github.com/librenms/librenms/pull/9954)) - [twilley](https://github.com/twilley)
* Fix Raspberry Pi frequency, voltage and state sensors discovery ([#10176](https://github.com/librenms/librenms/pull/10176)) - [CirnoT](https://github.com/CirnoT)
* Add support for PoE state sensor on Mikrotik devices ([#10201](https://github.com/librenms/librenms/pull/10201)) - [CirnoT](https://github.com/CirnoT)
* Fortigate sessions sensors ([#10183](https://github.com/librenms/librenms/pull/10183)) - [marvink87](https://github.com/marvink87)
* Modified adva port label to use ifname ([#10165](https://github.com/librenms/librenms/pull/10165)) - [h-barnhart](https://github.com/h-barnhart)
* Fixed Avtech discovery ([#10163](https://github.com/librenms/librenms/pull/10163)) - [murrant](https://github.com/murrant)
* Added support for APC PDU outlet state sensors ([#10166](https://github.com/librenms/librenms/pull/10166)) - [CirnoT](https://github.com/CirnoT)
* Add support for Transition NIDs ([#9729](https://github.com/librenms/librenms/pull/9729)) - [JoshWeepie](https://github.com/JoshWeepie)

#### Webui
* Fix can't set poller group on ping only device ([#10260](https://github.com/librenms/librenms/pull/10260)) - [murrant](https://github.com/murrant)
* Netscaler vsvr - fixed wrong table colspan ([#10246](https://github.com/librenms/librenms/pull/10246)) - [pobradovic08](https://github.com/pobradovic08)
* Fix device groups showing multiple times ([#10247](https://github.com/librenms/librenms/pull/10247)) - [murrant](https://github.com/murrant)
* Use Laravel url helpers to improve functionality without dns name ([#10227](https://github.com/librenms/librenms/pull/10227)) - [murrant](https://github.com/murrant)
* Netscaler vservers table update ([#10103](https://github.com/librenms/librenms/pull/10103)) - [pobradovic08](https://github.com/pobradovic08)
* Try to make port counts match user expectations ([#10230](https://github.com/librenms/librenms/pull/10230)) - [murrant](https://github.com/murrant)
* Disable browser autocomplete dropdown for global search ([#10233](https://github.com/librenms/librenms/pull/10233)) - [murrant](https://github.com/murrant)
* Order device group menu by name ([#10216](https://github.com/librenms/librenms/pull/10216)) - [VirTechSystems](https://github.com/VirTechSystems)
* OSPF display improvements ([#10206](https://github.com/librenms/librenms/pull/10206)) - [dsgagi](https://github.com/dsgagi)
* Better services graphing support ([#10185](https://github.com/librenms/librenms/pull/10185)) - [CirnoT](https://github.com/CirnoT)
* Fix state sensors on device health page showing always as OK (green) ([#10200](https://github.com/librenms/librenms/pull/10200)) - [CirnoT](https://github.com/CirnoT)
* Replace legacy menu with new Blade generated one ([#10173](https://github.com/librenms/librenms/pull/10173)) - [murrant](https://github.com/murrant)
* Move container to page in blade tempates ([#10195](https://github.com/librenms/librenms/pull/10195)) - [murrant](https://github.com/murrant)
* Realtime graph handle snmp server caching ([#10113](https://github.com/librenms/librenms/pull/10113)) - [murrant](https://github.com/murrant)
* Specify graph format from GET param ([#10118](https://github.com/librenms/librenms/pull/10118)) - [Anthony25](https://github.com/Anthony25)
* Fix sensors on health table always showing as good ([#10171](https://github.com/librenms/librenms/pull/10171)) - [CirnoT](https://github.com/CirnoT)
* Russian language support ([#10137](https://github.com/librenms/librenms/pull/10137)) - [rkislov](https://github.com/rkislov)
* Handle edge case in graph view for Munin plugins ([#10127](https://github.com/librenms/librenms/pull/10127)) - [CirnoT](https://github.com/CirnoT)

#### Snmp Traps
* Juniper BGP4 Trap Handler update values in DB ([#10180](https://github.com/librenms/librenms/pull/10180)) - [h-barnhart](https://github.com/h-barnhart)
* SNMP Trap Handlers for Ruckus Wireless ([#10175](https://github.com/librenms/librenms/pull/10175)) - [h-barnhart](https://github.com/h-barnhart)
* Update bridge STP trap handlers to log events under stp type ([#10192](https://github.com/librenms/librenms/pull/10192)) - [CirnoT](https://github.com/CirnoT)
* Juniper SNMP Trap Handlers ([#10136](https://github.com/librenms/librenms/pull/10136)) - [h-barnhart](https://github.com/h-barnhart)
* Add failed user login trap for Netgear switches ([#10161](https://github.com/librenms/librenms/pull/10161)) - [CirnoT](https://github.com/CirnoT)
* Support for APC PDU Outlet traps ([#10162](https://github.com/librenms/librenms/pull/10162)) - [CirnoT](https://github.com/CirnoT)

#### Api
* Fix location missing from API device list ([#10215](https://github.com/librenms/librenms/pull/10215)) - [murrant](https://github.com/murrant)
* API Fix error when no fdb are found ([#10125](https://github.com/librenms/librenms/pull/10125)) - [murrant](https://github.com/murrant)
* Update legacy_api_v0.php ([#10209](https://github.com/librenms/librenms/pull/10209)) - [sparkkraps](https://github.com/sparkkraps)

#### Alerting
* Fix alert follow up for custom queries ([#10253](https://github.com/librenms/librenms/pull/10253)) - [thomseddon](https://github.com/thomseddon)
* SMS Eagle reduce chances of user mis-configuration ([#10223](https://github.com/librenms/librenms/pull/10223)) - [murrant](https://github.com/murrant)
* Cast alert ID to string for PD API ([#10186](https://github.com/librenms/librenms/pull/10186)) - [supertylerc](https://github.com/supertylerc)

#### Bug
* Fixed Cisco MAC accounting discovery, polling and HTML templates. ([#10158](https://github.com/librenms/librenms/pull/10158)) - [deesel](https://github.com/deesel)
* Prevent fail2ban from filling eventlog on every poll ([#10225](https://github.com/librenms/librenms/pull/10225)) - [efelon](https://github.com/efelon)
* Ignore empty IPv4 addresses in discovery on buggy devices ([#10198](https://github.com/librenms/librenms/pull/10198)) - [CirnoT](https://github.com/CirnoT)
* Netstats-udp, tcp and ip rrd's not updating ([#10197](https://github.com/librenms/librenms/pull/10197)) - [slashdoom](https://github.com/slashdoom)
* Fix MySQL error were prepared statement contains too many placeholders ([#10153](https://github.com/librenms/librenms/pull/10153)) - [davidmnelson](https://github.com/davidmnelson)
* Fix debug message ([#10189](https://github.com/librenms/librenms/pull/10189)) - [murrant](https://github.com/murrant)

#### Documentation
* Fix incorrect file path in custom-graph.md ([#10238](https://github.com/librenms/librenms/pull/10238)) - [jozefrebjak](https://github.com/jozefrebjak)
* Instructions for logstash ([#10252](https://github.com/librenms/librenms/pull/10252)) - [mjducharme](https://github.com/mjducharme)
* Fix broken links and anchors ([#10194](https://github.com/librenms/librenms/pull/10194)) - [pobradovic08](https://github.com/pobradovic08)
* Fix alerting docs redirect ([#10193](https://github.com/librenms/librenms/pull/10193)) - [murrant](https://github.com/murrant)
* Add 'software-properties-common' ([#10092](https://github.com/librenms/librenms/pull/10092)) - [LeoWinterDE](https://github.com/LeoWinterDE)


## 1.51
*(2019-04-29)*

A big thank you to the following 29 contributors this last month:

  - murrant (19)
  - PipoCanaja (8)
  - CirnoT (7)
  - spencerbutler (5)
  - laf (4)
  - pobradovic08 (3)
  - TheGreatDoc (3)
  - h-barnhart (2)
  - twilley (2)
  - djamp42 (2)
  - smiles1969 (2)
  - VVelox (1)
  - kmpanilla (1)
  - martijn-schmidt (1)
  - petracvv (1)
  - cppmonkey (1)
  - tigerdjohnson (1)
  - Slushnas (1)
  - tim427 (1)
  - n0taz (1)
  - zombah (1)
  - andrewimeson (1)
  - tomarch (1)
  - mattie47 (1)
  - dmeiser (1)
  - neszt (1)
  - priiduonu (1)
  - vitalisator (1)
  - longchihang (1)

#### Device
* Serial number and more robust OS version for Netgear switches ([#10164](https://github.com/librenms/librenms/pull/10164)) - [CirnoT](https://github.com/CirnoT)
* Extended sensors for Timos devices ([#10160](https://github.com/librenms/librenms/pull/10160)) - [vitalisator](https://github.com/vitalisator)
* Added support for Zmtel greenpacket devices (os: zmtel) ([#10067](https://github.com/librenms/librenms/pull/10067)) - [spencerbutler](https://github.com/spencerbutler)
* Get and display the image patch version on Huawei VRP devices ([#10099](https://github.com/librenms/librenms/pull/10099)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added packetlight support ([#10131](https://github.com/librenms/librenms/pull/10131)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fortinet SNMP Trap Handlers ([#10148](https://github.com/librenms/librenms/pull/10148)) - [h-barnhart](https://github.com/h-barnhart)
* Adva SNMP Trap Handlers ([#10094](https://github.com/librenms/librenms/pull/10094)) - [h-barnhart](https://github.com/h-barnhart)
* Add PSU state to Netgear discovery definition ([#10150](https://github.com/librenms/librenms/pull/10150)) - [CirnoT](https://github.com/CirnoT)
* Add memory pool for Netgear switches ([#10146](https://github.com/librenms/librenms/pull/10146)) - [CirnoT](https://github.com/CirnoT)
* Updates to planetos.yaml for additional models ([#10149](https://github.com/librenms/librenms/pull/10149)) - [kmpanilla](https://github.com/kmpanilla)
* Add discovery ObjectID for Barracuda NGFW ([#10102](https://github.com/librenms/librenms/pull/10102)) - [pobradovic08](https://github.com/pobradovic08)
* Added support dellNet devices ([#10016](https://github.com/librenms/librenms/pull/10016)) - [spencerbutler](https://github.com/spencerbutler)
* Improved Linksys support, including POE ([#10075](https://github.com/librenms/librenms/pull/10075)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added Cisco Small business bootloader + HW version ([#10043](https://github.com/librenms/librenms/pull/10043)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added detection for ESW 5xx series of Cisco SB devices ([#10077](https://github.com/librenms/librenms/pull/10077)) - [laf](https://github.com/laf)
* Extended card support for adva_fsp150 family ([#10049](https://github.com/librenms/librenms/pull/10049)) - [PipoCanaja](https://github.com/PipoCanaja)
* Updated RegEx string for correct definitions for Proxmox 4.x nodes ([#10048](https://github.com/librenms/librenms/pull/10048)) - [n0taz](https://github.com/n0taz)
* Improve Huawei BGP polling + BGP webui & graphs patches ([#10010](https://github.com/librenms/librenms/pull/10010)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for Liebert PDUs ([#10062](https://github.com/librenms/librenms/pull/10062)) - [spencerbutler](https://github.com/spencerbutler)
* Added initial support for teltonika ([#10059](https://github.com/librenms/librenms/pull/10059)) - [tomarch](https://github.com/tomarch)
* Added support for TP-LINK T1600G-52TS ([#9983](https://github.com/librenms/librenms/pull/9983)) - [spencerbutler](https://github.com/spencerbutler)
* Fix aruba-controller polling ([#10071](https://github.com/librenms/librenms/pull/10071)) - [twilley](https://github.com/twilley)
* Added device WISI Tangram ([#10039](https://github.com/librenms/librenms/pull/10039)) - [djamp42](https://github.com/djamp42)
* Added PPPoE Sessions Graph for Mikrotik ([#10056](https://github.com/librenms/librenms/pull/10056)) - [neszt](https://github.com/neszt)
* Fix gw-eydfa detection ([#10052](https://github.com/librenms/librenms/pull/10052)) - [murrant](https://github.com/murrant)
* Added support for IONODES video encoders ([#10031](https://github.com/librenms/librenms/pull/10031)) - [priiduonu](https://github.com/priiduonu)
* Added support for DELLEMC-OS10-PRODUCTS-MIB (os: dell-os10) ([#10011](https://github.com/librenms/librenms/pull/10011)) - [spencerbutler](https://github.com/spencerbutler)

#### Bug
* Store IPv4 networks as network address and fix address search page showing networks not addresses ([#10144](https://github.com/librenms/librenms/pull/10144)) - [CirnoT](https://github.com/CirnoT)
* Fix filter by device and interface type on IP address search page ([#10143](https://github.com/librenms/librenms/pull/10143)) - [CirnoT](https://github.com/CirnoT)
* Fix services with scripts inheriting DS from previous service on detail view ([#10142](https://github.com/librenms/librenms/pull/10142)) - [CirnoT](https://github.com/CirnoT)
* Fix call to shortDisplayName on null in MuninPluginController ([#10126](https://github.com/librenms/librenms/pull/10126)) - [CirnoT](https://github.com/CirnoT)
* Fix install.php can't find config.php ([#10129](https://github.com/librenms/librenms/pull/10129)) - [murrant](https://github.com/murrant)
* Fix call to isUnderMaintenance() on null ([#10090](https://github.com/librenms/librenms/pull/10090)) - [murrant](https://github.com/murrant)
* Don't require db for config_to_json.php ([#10100](https://github.com/librenms/librenms/pull/10100)) - [murrant](https://github.com/murrant)
* Bug - Nasty user_func vs divisor-multiplier issue ([#10122](https://github.com/librenms/librenms/pull/10122)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix to display minimum values in uptime graphs. ([#10078](https://github.com/librenms/librenms/pull/10078)) - [Slushnas](https://github.com/Slushnas)
* Fix notification creation ([#10058](https://github.com/librenms/librenms/pull/10058)) - [murrant](https://github.com/murrant)
* Fixed fail2ban jails eventlog spam ([#10061](https://github.com/librenms/librenms/pull/10061)) - [murrant](https://github.com/murrant)

#### Webui
* Easily setting font colors for RRD graphs is now possible ([#10083](https://github.com/librenms/librenms/pull/10083)) - [VVelox](https://github.com/VVelox)
* New User Management ([#9348](https://github.com/librenms/librenms/pull/9348)) - [murrant](https://github.com/murrant)
* Display number of connections for ASA on over ([#10106](https://github.com/librenms/librenms/pull/10106)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix to display minimum values in uptime graphs. ([#10078](https://github.com/librenms/librenms/pull/10078)) - [Slushnas](https://github.com/Slushnas)
* Refactored Nvidia Application ([#10037](https://github.com/librenms/librenms/pull/10037)) - [tim427](https://github.com/tim427)
* Improve Huawei BGP polling + BGP webui & graphs patches ([#10010](https://github.com/librenms/librenms/pull/10010)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix notification creation ([#10058](https://github.com/librenms/librenms/pull/10058)) - [murrant](https://github.com/murrant)
* Add SysName to Oxidized view ([#10012](https://github.com/librenms/librenms/pull/10012)) - [smiles1969](https://github.com/smiles1969)

#### Documentation
* Updated Code-Structure.md ([#10156](https://github.com/librenms/librenms/pull/10156)) - [pobradovic08](https://github.com/pobradovic08)
* Fix formatting ([#10135](https://github.com/librenms/librenms/pull/10135)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fix paths of custom graph examples in Custom-Graphs.md ([#10128](https://github.com/librenms/librenms/pull/10128)) - [pobradovic08](https://github.com/pobradovic08)
* Update Example-Hardware-Setup.md ([#10115](https://github.com/librenms/librenms/pull/10115)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Update Templates.md ([#10120](https://github.com/librenms/librenms/pull/10120)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Example Hardware - corrected markup ([#10117](https://github.com/librenms/librenms/pull/10117)) - [cppmonkey](https://github.com/cppmonkey)
* Minor word usage corrections ([#10073](https://github.com/librenms/librenms/pull/10073)) - [andrewimeson](https://github.com/andrewimeson)
* Update Smokeping.md ([#10064](https://github.com/librenms/librenms/pull/10064)) - [mattie47](https://github.com/mattie47)
* Update SNMP-Configuration-Examples ([#10063](https://github.com/librenms/librenms/pull/10063)) - [dmeiser](https://github.com/dmeiser)
* LibreNMS python service doc updates ([#10044](https://github.com/librenms/librenms/pull/10044)) - [murrant](https://github.com/murrant)

#### Api
* Allow Add_Device API to set sysName ([#10124](https://github.com/librenms/librenms/pull/10124)) - [djamp42](https://github.com/djamp42)
* Fix api *log date format ([#10133](https://github.com/librenms/librenms/pull/10133)) - [murrant](https://github.com/murrant)
* Fix arp api network query ([#10085](https://github.com/librenms/librenms/pull/10085)) - [murrant](https://github.com/murrant)
* Fixed a duplicate route name in the api ([#10082](https://github.com/librenms/librenms/pull/10082)) - [laf](https://github.com/laf)
* Add API call to list all ports FDB ([#10020](https://github.com/librenms/librenms/pull/10020)) - [zombah](https://github.com/zombah)
* Added slms => zhoneolt mapping for Oxidized model ([#10068](https://github.com/librenms/librenms/pull/10068)) - [laf](https://github.com/laf)

#### Alerting
* Relax validation for smseagle hostname ([#10141](https://github.com/librenms/librenms/pull/10141)) - [petracvv](https://github.com/petracvv)
* Removed legacy code from transports ([#10081](https://github.com/librenms/librenms/pull/10081)) - [laf](https://github.com/laf)

#### Security
* Fix unescaped variables in ajax_search.php ([#10088](https://github.com/librenms/librenms/pull/10088)) - [murrant](https://github.com/murrant)
* Security fix: unauthorized access ([#10091](https://github.com/librenms/librenms/pull/10091)) - [murrant](https://github.com/murrant)

#### Feature
* Update json error message to show how to debug. ([#9998](https://github.com/librenms/librenms/pull/9998)) - [murrant](https://github.com/murrant)


## 1.50
*(2019-03-31)*

A big thank you to the following 31 contributors this last month:

  - murrant (37)
  - PipoCanaja (26)
  - spencerbutler (6)
  - amigne (5)
  - vitalisator (4)
  - djamp42 (3)
  - cppmonkey (3)
  - TheGreatDoc (3)
  - nickhilliard (2)
  - llcoolkm (2)
  - JoshWeepie (1)
  - fjwcash (1)
  - VVelox (1)
  - sorano (1)
  - kkrumm1 (1)
  - rmedlyn (1)
  - florianbeer (1)
  - cliffalbert (1)
  - jozefrebjak (1)
  - zombah (1)
  - kiwibrew (1)
  - network-guy (1)
  - rmagomedov (1)
  - priiduonu (1)
  - ospfbgp (1)
  - jasoncheng7115 (1)
  - GitStoph (1)
  - shward (1)
  - koocotte (1)
  - dracmic (1)
  - sjtarik (1)

#### Feature
* Printer paper tray status and error states ([#9859](https://github.com/librenms/librenms/pull/9859)) - [sjtarik](https://github.com/sjtarik)
* Added support for custom storage warning percentage ([#9975](https://github.com/librenms/librenms/pull/9975)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Added support for ciscosb to gen_rancid.php (cisco-sb) ([#9940](https://github.com/librenms/librenms/pull/9940)) - [cliffalbert](https://github.com/cliffalbert)
* Access to "sub" index (when OID has multiple indexes) ([#9893](https://github.com/librenms/librenms/pull/9893)) - [PipoCanaja](https://github.com/PipoCanaja)
* Optional automatic sensor limits ([#9973](https://github.com/librenms/librenms/pull/9973)) - [amigne](https://github.com/amigne)

#### Bug
* Fix database validation for MySQL 8 ([#9923](https://github.com/librenms/librenms/pull/9923)) - [llcoolkm](https://github.com/llcoolkm)
* Fixed device missing from traps/new Log::event() ([#9963](https://github.com/librenms/librenms/pull/9963)) - [murrant](https://github.com/murrant)
* Fixed Cisco OTV (array)cast issue creating empty adj. ([#9968](https://github.com/librenms/librenms/pull/9968)) - [PipoCanaja](https://github.com/PipoCanaja)
* Improved support for Dantel Webmon ([#9977](https://github.com/librenms/librenms/pull/9977)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fixed inconsistent \<h3\> closed by \</div\> ([#9982](https://github.com/librenms/librenms/pull/9982)) - [amigne](https://github.com/amigne)
* Fixed install not connecting to DB ([#9984](https://github.com/librenms/librenms/pull/9984)) - [murrant](https://github.com/murrant)
* Fixed mysql table engine validation ([#9989](https://github.com/librenms/librenms/pull/9989)) - [murrant](https://github.com/murrant)
* Fixed device list down devices that have never been polled ([#9994](https://github.com/librenms/librenms/pull/9994)) - [murrant](https://github.com/murrant)
* Fixed world map when location has been deleted that a device still references ([#9997](https://github.com/librenms/librenms/pull/9997)) - [murrant](https://github.com/murrant)
* Fixed device group queries again ([#10000](https://github.com/librenms/librenms/pull/10000)) - [murrant](https://github.com/murrant)
* Fixed transport options when edited on Windows ([#10001](https://github.com/librenms/librenms/pull/10001)) - [murrant](https://github.com/murrant)
* Fixed issue with new permissions code ([#10004](https://github.com/librenms/librenms/pull/10004)) - [murrant](https://github.com/murrant)
* Fixed bill permissions ([#10005](https://github.com/librenms/librenms/pull/10005)) - [murrant](https://github.com/murrant)
* Fixed an issue with bills with no data ([#10009](https://github.com/librenms/librenms/pull/10009)) - [murrant](https://github.com/murrant)
* Fixed error when user doesn't exist ([#10023](https://github.com/librenms/librenms/pull/10023)) - [murrant](https://github.com/murrant)
* Top ports widget: Work around bad data in the database ([#10024](https://github.com/librenms/librenms/pull/10024)) - [murrant](https://github.com/murrant)
* Properly set the component information when polling ([#10017](https://github.com/librenms/librenms/pull/10017)) - [VVelox](https://github.com/VVelox)
* Removed an errant character in cambium definitions ([#9996](https://github.com/librenms/librenms/pull/9996)) - [spencerbutler](https://github.com/spencerbutler)
* Fixed cisco temperature limit on discovery ([#9985](https://github.com/librenms/librenms/pull/9985)) - [amigne](https://github.com/amigne)
* Patch fix sql error on gengroupsql ([#9929](https://github.com/librenms/librenms/pull/9929)) - [dracmic](https://github.com/dracmic)
* Work around issue with Weathermaps ([#10033](https://github.com/librenms/librenms/pull/10033)) - [murrant](https://github.com/murrant)
* Typo in routeros YAML discovery ([#9903](https://github.com/librenms/librenms/pull/9903)) - [PipoCanaja](https://github.com/PipoCanaja)
* Don't enable secure cookies when they won't work ([#9971](https://github.com/librenms/librenms/pull/9971)) - [murrant](https://github.com/murrant)
* Do not use $sensor[sensor_limit] if not available ([#9978](https://github.com/librenms/librenms/pull/9978)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix an issue that could block install ([#9958](https://github.com/librenms/librenms/pull/9958)) - [murrant](https://github.com/murrant)
* Handle missing fields a little more gracefully ([#9919](https://github.com/librenms/librenms/pull/9919)) - [murrant](https://github.com/murrant)
* Fix ldap/ad auth anon-bind ([#9905](https://github.com/librenms/librenms/pull/9905)) - [murrant](https://github.com/murrant)
* Replace misplaced nokia/OSPFV3-MIB with a newer version to right place ([#9907](https://github.com/librenms/librenms/pull/9907)) - [vitalisator](https://github.com/vitalisator)
* Fixes to composer_wrapper proxy handling ([#9819](https://github.com/librenms/librenms/pull/9819)) - [murrant](https://github.com/murrant)
* Allow admins to add/remove/create sticky notifications ([#9865](https://github.com/librenms/librenms/pull/9865)) - [cppmonkey](https://github.com/cppmonkey)

#### Device
* Improve VRP stack state discovery with one member only to avoid unnecessary alarms ([#9925](https://github.com/librenms/librenms/pull/9925)) - [PipoCanaja](https://github.com/PipoCanaja)
* Improved Riello UPS support with RFC 1628 support ([#9962](https://github.com/librenms/librenms/pull/9962)) - [PipoCanaja](https://github.com/PipoCanaja)
* Improved support for Dantel Webmon ([#9977](https://github.com/librenms/librenms/pull/9977)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added Arris Apex support ([#10006](https://github.com/librenms/librenms/pull/10006)) - [djamp42](https://github.com/djamp42)
* Added Panduit Eagle-I (was Sinetica) support ([#10014](https://github.com/librenms/librenms/pull/10014)) - [PipoCanaja](https://github.com/PipoCanaja)
* Eltek Valere: Group sensors by shelf ([#10040](https://github.com/librenms/librenms/pull/10040)) - [murrant](https://github.com/murrant)
* Added support for APC AP9810 zone contacts ([#9967](https://github.com/librenms/librenms/pull/9967)) - [cppmonkey](https://github.com/cppmonkey)
* Added support for HikVision-DS Cameras ([#9980](https://github.com/librenms/librenms/pull/9980)) - [spencerbutler](https://github.com/spencerbutler)
* Added support for EDFA ([#9912](https://github.com/librenms/librenms/pull/9912)) - [jozefrebjak](https://github.com/jozefrebjak)
* Added support for Orvaldi UPS ([#10021](https://github.com/librenms/librenms/pull/10021)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for Ruijie Networks ([#10026](https://github.com/librenms/librenms/pull/10026)) - [spencerbutler](https://github.com/spencerbutler)
* Extend support for all emerson products ([#10018](https://github.com/librenms/librenms/pull/10018)) - [PipoCanaja](https://github.com/PipoCanaja)
* New Device: ATS - Automatic Transfer Switch ([#9889](https://github.com/librenms/librenms/pull/9889)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Add Device - Cisco Sat Receivers ([#9899](https://github.com/librenms/librenms/pull/9899)) - [djamp42](https://github.com/djamp42)
* Add Device Arris DSR-4410MD Sat Receiver ([#9943](https://github.com/librenms/librenms/pull/9943)) - [djamp42](https://github.com/djamp42)
* Added Med(5m) and High(15m) Utilization sensors for Cambium  APs (os: pmp) ([#9995](https://github.com/librenms/librenms/pull/9995)) - [spencerbutler](https://github.com/spencerbutler)
* Grandstream basic support ([#9906](https://github.com/librenms/librenms/pull/9906)) - [PipoCanaja](https://github.com/PipoCanaja)
* Dell UPS enable rfc 1628 support ([#9961](https://github.com/librenms/librenms/pull/9961)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for Cisco Small Business WAP371-a-k9 ([#9974](https://github.com/librenms/librenms/pull/9974)) - [spencerbutler](https://github.com/spencerbutler)
* Dantel WebMon Device Support ([#9767](https://github.com/librenms/librenms/pull/9767)) - [network-guy](https://github.com/network-guy)
* Added new OS - sensatronics-em1 ([#9960](https://github.com/librenms/librenms/pull/9960)) - [spencerbutler](https://github.com/spencerbutler)
* Eaton pdu extension ([#9947](https://github.com/librenms/librenms/pull/9947)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix Aruba Instant wireless sensors ([#9936](https://github.com/librenms/librenms/pull/9936)) - [murrant](https://github.com/murrant)
* Added Ciena Waveserver ([#9930](https://github.com/librenms/librenms/pull/9930)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add Junos VirtuallChassis ports polling ([#9879](https://github.com/librenms/librenms/pull/9879)) - [rmagomedov](https://github.com/rmagomedov)
* Add support for Moxa EDS-G512E-8PoE ([#9857](https://github.com/librenms/librenms/pull/9857)) - [priiduonu](https://github.com/priiduonu)
* Improved Infinera Groove discovery ([#9913](https://github.com/librenms/librenms/pull/9913)) - [nickhilliard](https://github.com/nickhilliard)
* Add support for power supply on Extreme BOSS switches ([#9898](https://github.com/librenms/librenms/pull/9898)) - [ospfbgp](https://github.com/ospfbgp)
* Add support for cirpack soft switch ([#9914](https://github.com/librenms/librenms/pull/9914)) - [vitalisator](https://github.com/vitalisator)
* Added VRP stack member and ports discovery ([#9891](https://github.com/librenms/librenms/pull/9891)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added Patton SmartNode OS discovery and polling ([#9901](https://github.com/librenms/librenms/pull/9901)) - [PipoCanaja](https://github.com/PipoCanaja)
* Meraki MS: add serial number ([#9768](https://github.com/librenms/librenms/pull/9768)) - [GitStoph](https://github.com/GitStoph)
* Add data scrubbing state to Synology RAID status ([#9661](https://github.com/librenms/librenms/pull/9661)) - [florianbeer](https://github.com/florianbeer)
* Add support for Infinera-Coriant Groove ([#9843](https://github.com/librenms/librenms/pull/9843)) - [nickhilliard](https://github.com/nickhilliard)

#### Webui
* Allowed more characters in graph legend for interface names ([#9926](https://github.com/librenms/librenms/pull/9926)) - [PipoCanaja](https://github.com/PipoCanaja)
* Corrected active_count for Alert icon color ([#9933](https://github.com/librenms/librenms/pull/9933)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fixed inconsistent \<h3\> closed by \</div\> ([#9982](https://github.com/librenms/librenms/pull/9982)) - [amigne](https://github.com/amigne)
* Fixed world map when location has been deleted that a device still references ([#9997](https://github.com/librenms/librenms/pull/9997)) - [murrant](https://github.com/murrant)
* Improved Health limits display ([#10007](https://github.com/librenms/librenms/pull/10007)) - [amigne](https://github.com/amigne)
* Added Panduit Eagle-I (was Sinetica) support ([#10014](https://github.com/librenms/librenms/pull/10014)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fixed missing pipe in ports page menu ([#10025](https://github.com/librenms/librenms/pull/10025)) - [JoshWeepie](https://github.com/JoshWeepie)
* Added location to alert hostname tooltip ([#9991](https://github.com/librenms/librenms/pull/9991)) - [rmedlyn](https://github.com/rmedlyn)
* Beautify port health ([#9981](https://github.com/librenms/librenms/pull/9981)) - [amigne](https://github.com/amigne)
* Added a blue theme ([#9970](https://github.com/librenms/librenms/pull/9970)) - [PipoCanaja](https://github.com/PipoCanaja)
* Display Up/Down time in Device List ([#9951](https://github.com/librenms/librenms/pull/9951)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add Alert-transports to Laravel menu blade ([#9946](https://github.com/librenms/librenms/pull/9946)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix graphs for ASA announcing all interfaces in type l2vlan (by default filtered) ([#9849](https://github.com/librenms/librenms/pull/9849)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix widgets page with MySQL 8 ([#9922](https://github.com/librenms/librenms/pull/9922)) - [llcoolkm](https://github.com/llcoolkm)
* Fixed sysName can't display because newline character ([#9921](https://github.com/librenms/librenms/pull/9921)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Allow admins to add/remove/create sticky notifications ([#9865](https://github.com/librenms/librenms/pull/9865)) - [cppmonkey](https://github.com/cppmonkey)

#### Alerting
* Added HTML transport example for ms teams ([#9969](https://github.com/librenms/librenms/pull/9969)) - [sorano](https://github.com/sorano)
* Fixed device group queries again ([#10000](https://github.com/librenms/librenms/pull/10000)) - [murrant](https://github.com/murrant)
* Fixed transport options when edited on Windows ([#10001](https://github.com/librenms/librenms/pull/10001)) - [murrant](https://github.com/murrant)
* Update documentation to reflect matching behaviour ([#9955](https://github.com/librenms/librenms/pull/9955)) - [kiwibrew](https://github.com/kiwibrew)

#### Documentation
* Added HTML transport example for ms teams ([#9969](https://github.com/librenms/librenms/pull/9969)) - [sorano](https://github.com/sorano)
* Fixed path in Smokeping conf file ([#10045](https://github.com/librenms/librenms/pull/10045)) - [fjwcash](https://github.com/fjwcash)
* Update Fast-Ping-Check.md ([#10022](https://github.com/librenms/librenms/pull/10022)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Update documentation to reflect matching behaviour ([#9955](https://github.com/librenms/librenms/pull/9955)) - [kiwibrew](https://github.com/kiwibrew)
* Update Getting-Started.md ([#9976](https://github.com/librenms/librenms/pull/9976)) - [PipoCanaja](https://github.com/PipoCanaja)
* Documentation for setting a development environment ([#9944](https://github.com/librenms/librenms/pull/9944)) - [murrant](https://github.com/murrant)
* Add new template syntax into the FAQ, pointing to the documentation ([#9942](https://github.com/librenms/librenms/pull/9942)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add Debian instruction ([#9788](https://github.com/librenms/librenms/pull/9788)) - [koocotte](https://github.com/koocotte)
* Update Web UI debug FAQ ([#9816](https://github.com/librenms/librenms/pull/9816)) - [murrant](https://github.com/murrant)

#### Security
* Update dependencies ([#10002](https://github.com/librenms/librenms/pull/10002)) - [murrant](https://github.com/murrant)
* Prevent credentials from being leaked in backtrace in some instances ([#9817](https://github.com/librenms/librenms/pull/9817)) - [murrant](https://github.com/murrant)

#### Api
* Added resources/sensors api call to list all sensors ([#9837](https://github.com/librenms/librenms/pull/9837)) - [zombah](https://github.com/zombah)


## 1.49
*(2019-03-03)*

A big thank you to the following 36 contributors this last month:

  - murrant (30)
  - PipoCanaja (8)
  - TheGreatDoc (4)
  - cppmonkey (4)
  - SirMaple (3)
  - vitalisator (3)
  - Rosiak (2)
  - ipptac (2)
  - jozefrebjak (2)
  - djamp42 (2)
  - laf (2)
  - angryp (2)
  - crcro (2)
  - githubuserx (1)
  - TylerSweet (1)
  - aylham (1)
  - pobradovic08 (1)
  - felici (1)
  - ospfbgp (1)
  - Cormoran96 (1)
  - Anthony25 (1)
  - hlmtre (1)
  - kkrumm1 (1)
  - zombah (1)
  - TakeMeNL (1)
  - tharbakim (1)
  - mhzgh (1)
  - fake-name (1)
  - paraselene92 (1)
  - mbwall (1)
  - InsaneSplash (1)
  - sjtarik (1)
  - CoMMyz (1)
  - martijn-schmidt (1)
  - esundberg (1)
  - dsmfool (1)

#### Documentation
* Updated JumpCloud authentication example ([#9722](https://github.com/librenms/librenms/pull/9722)) - [dsmfool](https://github.com/dsmfool)
* Additional info for Postgres application ([#9791](https://github.com/librenms/librenms/pull/9791)) - [SirMaple](https://github.com/SirMaple)
* Update Example-Hardware-Setup.md ([#9897](https://github.com/librenms/librenms/pull/9897)) - [cppmonkey](https://github.com/cppmonkey)
* Fix link missing parenthesis ([#9895](https://github.com/librenms/librenms/pull/9895)) - [githubuserx](https://github.com/githubuserx)
* Added new install to example hardware setup ([#9872](https://github.com/librenms/librenms/pull/9872)) - [SirMaple](https://github.com/SirMaple)
* Clarification in server migration procedure ([#9848](https://github.com/librenms/librenms/pull/9848)) - [hlmtre](https://github.com/hlmtre)
* Update the bug report link in README.md ([#9850](https://github.com/librenms/librenms/pull/9850)) - [kkrumm1](https://github.com/kkrumm1)
* Remove extra semicolon from documentation. ([#9833](https://github.com/librenms/librenms/pull/9833)) - [tharbakim](https://github.com/tharbakim)
* Change minimum PHP version in install docs ([#9820](https://github.com/librenms/librenms/pull/9820)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Install docs, note proxy config ([#9720](https://github.com/librenms/librenms/pull/9720)) - [mhzgh](https://github.com/mhzgh)
* Fix Smokeping setup instructions so they actually work ([#9731](https://github.com/librenms/librenms/pull/9731)) - [fake-name](https://github.com/fake-name)
* Fix a link in support document ([#9808](https://github.com/librenms/librenms/pull/9808)) - [paraselene92](https://github.com/paraselene92)
* Update Poller Support.md ([#9769](https://github.com/librenms/librenms/pull/9769)) - [TheGreatDoc](https://github.com/TheGreatDoc)

#### Device
* Ruckus Wireless updates (ZD/SZ/Unleashed/Hotzone) ([#9727](https://github.com/librenms/librenms/pull/9727)) - [djamp42](https://github.com/djamp42)
* Fix Arista interface bias current divisor ([#9728](https://github.com/librenms/librenms/pull/9728)) - [Rosiak](https://github.com/Rosiak)
* Added power and fan sensors to VRP ([#9838](https://github.com/librenms/librenms/pull/9838)) - [jozefrebjak](https://github.com/jozefrebjak)
* [fix] edgeswitch v1.9 os detection ([#9868](https://github.com/librenms/librenms/pull/9868)) - [crcro](https://github.com/crcro)
* Add support for sagemcom ([#9835](https://github.com/librenms/librenms/pull/9835)) - [vitalisator](https://github.com/vitalisator)
* Add fan description to hwEntityFanState ([#9863](https://github.com/librenms/librenms/pull/9863)) - [PipoCanaja](https://github.com/PipoCanaja)
* [feat] edgeos hardware info ([#9867](https://github.com/librenms/librenms/pull/9867)) - [crcro](https://github.com/crcro)
* Added support for Alpha Comp@s ([#9871](https://github.com/librenms/librenms/pull/9871)) - [cppmonkey](https://github.com/cppmonkey)
* Discovery rcChasPowerSupplyOperStatus for Extreme/Avaya voss.inc.php stop working ([#9878](https://github.com/librenms/librenms/pull/9878)) - [ospfbgp](https://github.com/ospfbgp)
* Added Perle OS Support for IOLAN SCS ([#9866](https://github.com/librenms/librenms/pull/9866)) - [esundberg](https://github.com/esundberg)
* Netscaler SDWAN appliance - Serial# + Version and state sensors ([#9834](https://github.com/librenms/librenms/pull/9834)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for Protelevision DVB-T Transmitter ([#9648](https://github.com/librenms/librenms/pull/9648)) - [jozefrebjak](https://github.com/jozefrebjak)
* Netgear m5300 health sensors ([#9744](https://github.com/librenms/librenms/pull/9744)) - [cppmonkey](https://github.com/cppmonkey)
* Additional TPLink JetStream mem/cpu support ([#9829](https://github.com/librenms/librenms/pull/9829)) - [TakeMeNL](https://github.com/TakeMeNL)
* MAIPU MyPowerOS: CPU, Memory, expanded detection ([#9825](https://github.com/librenms/librenms/pull/9825)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Fix Junos CPU Discovery ([#9467](https://github.com/librenms/librenms/pull/9467)) - [Rosiak](https://github.com/Rosiak)
* Add support for Nokia ISAM ([#9793](https://github.com/librenms/librenms/pull/9793)) - [vitalisator](https://github.com/vitalisator)
* Updating pfSense Logo ([#9828](https://github.com/librenms/librenms/pull/9828)) - [SirMaple](https://github.com/SirMaple)
* Expand Cyberpower OS detection ([#9802](https://github.com/librenms/librenms/pull/9802)) - [murrant](https://github.com/murrant)
* Add osmc icon ([#9810](https://github.com/librenms/librenms/pull/9810)) - [murrant](https://github.com/murrant)
* Workaround of bad SNMP implementation in EDS device. ([#9801](https://github.com/librenms/librenms/pull/9801)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added Virtual Chassis Member Role Sensor ([#9783](https://github.com/librenms/librenms/pull/9783)) - [ipptac](https://github.com/ipptac)
* FS.COM (Fiberstore) 'GBN' and 'SWITCH' devices support ([#9734](https://github.com/librenms/librenms/pull/9734)) - [PipoCanaja](https://github.com/PipoCanaja)
* Support for DKT Comega FTTx devices ([#9732](https://github.com/librenms/librenms/pull/9732)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for EDS 1Wire devices ([#9740](https://github.com/librenms/librenms/pull/9740)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix regression in VC hardware detection for JunOS ([#9772](https://github.com/librenms/librenms/pull/9772)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* New Device: Bluecat Networks appliances ([#9758](https://github.com/librenms/librenms/pull/9758)) - [ipptac](https://github.com/ipptac)

#### Alerting
* Fix Arista interface bias current divisor ([#9728](https://github.com/librenms/librenms/pull/9728)) - [Rosiak](https://github.com/Rosiak)
* LibreNMS/Alert/Transport/Elasticsearch - Send timestamp with timezone ([#9757](https://github.com/librenms/librenms/pull/9757)) - [pobradovic08](https://github.com/pobradovic08)
* Canopsis transport ([#9795](https://github.com/librenms/librenms/pull/9795)) - [aylham](https://github.com/aylham)
* Alertmanager fix 2 ([#9860](https://github.com/librenms/librenms/pull/9860)) - [angryp](https://github.com/angryp)
* Update Mattermost transport with configurable author_name ([#9759](https://github.com/librenms/librenms/pull/9759)) - [zombah](https://github.com/zombah)
* Restore alert template converter for a while longer ([#9845](https://github.com/librenms/librenms/pull/9845)) - [murrant](https://github.com/murrant)
* Removed legacy transports and templates code ([#9646](https://github.com/librenms/librenms/pull/9646)) - [laf](https://github.com/laf)
* Fix cisco compenent down macro ([#9805](https://github.com/librenms/librenms/pull/9805)) - [murrant](https://github.com/murrant)
* Fixed Alertmanager transport ([#9807](https://github.com/librenms/librenms/pull/9807)) - [angryp](https://github.com/angryp)
* Msteams consistent title ([#9774](https://github.com/librenms/librenms/pull/9774)) - [InsaneSplash](https://github.com/InsaneSplash)
* BGP Session down rule: add conditions for bgp admin status = stop ([#9773](https://github.com/librenms/librenms/pull/9773)) - [Cormoran96](https://github.com/Cormoran96)

#### Bug
* More 278 fixes mysql 5.7 does not like the variable names ([#9766](https://github.com/librenms/librenms/pull/9766)) - [murrant](https://github.com/murrant)
* Update to PHPMailer 6.0 (PHP 7.3 support) ([#9818](https://github.com/librenms/librenms/pull/9818)) - [felici](https://github.com/felici)
* Syslog hostname translation broken ([#9839](https://github.com/librenms/librenms/pull/9839)) - [TylerSweet](https://github.com/TylerSweet)
* Fix incorrect icon sometimes ([#9887](https://github.com/librenms/librenms/pull/9887)) - [murrant](https://github.com/murrant)
* Try to fix some of the Component code ([#9888](https://github.com/librenms/librenms/pull/9888)) - [murrant](https://github.com/murrant)
* Discovery rcChasPowerSupplyOperStatus for Extreme/Avaya voss.inc.php stop working ([#9878](https://github.com/librenms/librenms/pull/9878)) - [ospfbgp](https://github.com/ospfbgp)
* Misplaced mib file when adding FS.COM support ([#9886](https://github.com/librenms/librenms/pull/9886)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fixed validator for rrd folder permissions ([#9869](https://github.com/librenms/librenms/pull/9869)) - [cppmonkey](https://github.com/cppmonkey)
* Fix MyPowerOS mempools ([#9861](https://github.com/librenms/librenms/pull/9861)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Email should be optional in lnms user:add command ([#9841](https://github.com/librenms/librenms/pull/9841)) - [murrant](https://github.com/murrant)
* Fix logging too much ([#9824](https://github.com/librenms/librenms/pull/9824)) - [murrant](https://github.com/murrant)
* Fix for failed sql query during db dump ([#9815](https://github.com/librenms/librenms/pull/9815)) - [murrant](https://github.com/murrant)
* Fix cisco compenent down macro ([#9805](https://github.com/librenms/librenms/pull/9805)) - [murrant](https://github.com/murrant)
* Discard local changes to git based vendor packages ([#9813](https://github.com/librenms/librenms/pull/9813)) - [murrant](https://github.com/murrant)
* Fixed Alertmanager transport ([#9807](https://github.com/librenms/librenms/pull/9807)) - [angryp](https://github.com/angryp)
* Fix php version notification removal ([#9797](https://github.com/librenms/librenms/pull/9797)) - [murrant](https://github.com/murrant)
* Fix php version notification removal ([#9796](https://github.com/librenms/librenms/pull/9796)) - [murrant](https://github.com/murrant)
* Handle unexpected os when loading yaml ([#9790](https://github.com/librenms/librenms/pull/9790)) - [murrant](https://github.com/murrant)
* Fix alert log api ([#9792](https://github.com/librenms/librenms/pull/9792)) - [murrant](https://github.com/murrant)
* Fix schema validation and os def cache invalidation ([#9789](https://github.com/librenms/librenms/pull/9789)) - [murrant](https://github.com/murrant)
* Restore gitignore file contents ([#9784](https://github.com/librenms/librenms/pull/9784)) - [murrant](https://github.com/murrant)
* Port exists check in eventlog ([#9778](https://github.com/librenms/librenms/pull/9778)) - [CoMMyz](https://github.com/CoMMyz)
* Migration fixes ([#9776](https://github.com/librenms/librenms/pull/9776)) - [murrant](https://github.com/murrant)
* Prevent error with multiple proxmox ([#9770](https://github.com/librenms/librenms/pull/9770)) - [murrant](https://github.com/murrant)

#### Webui
* Fix debug display of rrdtool command ([#9846](https://github.com/librenms/librenms/pull/9846)) - [Anthony25](https://github.com/Anthony25)
* Ignore disabled ports in minigraph view ([#9737](https://github.com/librenms/librenms/pull/9737)) - [mbwall](https://github.com/mbwall)

#### Feature
* Improved Exception handling ([#9844](https://github.com/librenms/librenms/pull/9844)) - [murrant](https://github.com/murrant)
* FDB table with history capabilities ([#9804](https://github.com/librenms/librenms/pull/9804)) - [PipoCanaja](https://github.com/PipoCanaja)
* Php artisan serve and dusk testing ([#9422](https://github.com/librenms/librenms/pull/9422)) - [murrant](https://github.com/murrant)
* Lnms user:add command ([#9830](https://github.com/librenms/librenms/pull/9830)) - [murrant](https://github.com/murrant)

#### Api
* Fix alert log api ([#9792](https://github.com/librenms/librenms/pull/9792)) - [murrant](https://github.com/murrant)


## 1.48
*(2019-01-28)*

A big thank you to the following 31 contributors this last month:

  - murrant (59)
  - PipoCanaja (7)
  - vitalisator (3)
  - laf (3)
  - gpant (2)
  - Swashy (2)
  - JoshWeepie (2)
  - kkrumm1 (2)
  - djamp42 (2)
  - dharpster (1)
  - martijn-schmidt (1)
  - emestee (1)
  - marvink87 (1)
  - GitStoph (1)
  - sjtarik (1)
  - TheGreatDoc (1)
  - acl (1)
  - sanegaming (1)
  - evheros (1)
  - kiwibrew (1)
  - nova-2nd (1)
  - pheinrichs (1)
  - amtypaldos (1)
  - Kal42 (1)
  - angryp (1)
  - mikecentola (1)
  - jozefrebjak (1)
  - tim427 (1)
  - twelch24 (1)
  - zoc (1)
  - cppmonkey (1)

#### Alerting
* Added Mattermost Alert Transport ([#9749](https://github.com/librenms/librenms/pull/9749)) - [gpant](https://github.com/gpant)
* Added Alerta Alert Transport ([#9673](https://github.com/librenms/librenms/pull/9673)) - [GitStoph](https://github.com/GitStoph)
* Fix first column validate ([#9683](https://github.com/librenms/librenms/pull/9683)) - [murrant](https://github.com/murrant)
* Alert Rules: fix for critical and warning rule ([#9688](https://github.com/librenms/librenms/pull/9688)) - [kkrumm1](https://github.com/kkrumm1)
* Added Alertmanager transport ([#9637](https://github.com/librenms/librenms/pull/9637)) - [angryp](https://github.com/angryp)
* Fix wireless sensor edit messages and collection alert rule ([#9624](https://github.com/librenms/librenms/pull/9624)) - [murrant](https://github.com/murrant)
* Alert schedule refactor ([#9514](https://github.com/librenms/librenms/pull/9514)) - [murrant](https://github.com/murrant)

#### Feature
* Added Mattermost Alert Transport ([#9749](https://github.com/librenms/librenms/pull/9749)) - [gpant](https://github.com/gpant)
* Pressing enter on global search goes to the first result ([#9587](https://github.com/librenms/librenms/pull/9587)) - [murrant](https://github.com/murrant)
* Lnms bash completion ([#9747](https://github.com/librenms/librenms/pull/9747)) - [murrant](https://github.com/murrant)
* Hook many commands into the lnms script ([#9699](https://github.com/librenms/librenms/pull/9699)) - [murrant](https://github.com/murrant)
* Add lnms command ([#9619](https://github.com/librenms/librenms/pull/9619)) - [murrant](https://github.com/murrant)
* Allow grouping of sensors ([#9606](https://github.com/librenms/librenms/pull/9606)) - [murrant](https://github.com/murrant)
* Validate database constraints ([#9670](https://github.com/librenms/librenms/pull/9670)) - [murrant](https://github.com/murrant)

#### Bug
* Fix Exception in GraylogAPI.php ([#9617](https://github.com/librenms/librenms/pull/9617)) - [zoc](https://github.com/zoc)
* Fix devices page search & OS loading ([#9752](https://github.com/librenms/librenms/pull/9752)) - [murrant](https://github.com/murrant)
* Revert "Mattermost Alert Transport" ([#9743](https://github.com/librenms/librenms/pull/9743)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Validate default config seeder ([#9723](https://github.com/librenms/librenms/pull/9723)) - [murrant](https://github.com/murrant)
* Fix missing config setting in DB seed alert.transports.mail ([#9721](https://github.com/librenms/librenms/pull/9721)) - [murrant](https://github.com/murrant)
* Fix sql schema 278 ([#9715](https://github.com/librenms/librenms/pull/9715)) - [murrant](https://github.com/murrant)
* Alert Rules: fix for critical and warning rule ([#9688](https://github.com/librenms/librenms/pull/9688)) - [kkrumm1](https://github.com/kkrumm1)
* Fix if discover overwritting fields ([#9643](https://github.com/librenms/librenms/pull/9643)) - [evheros](https://github.com/evheros)
* Fix validate error ([#9700](https://github.com/librenms/librenms/pull/9700)) - [murrant](https://github.com/murrant)
* Update Mimosa.php ([#9695](https://github.com/librenms/librenms/pull/9695)) - [kiwibrew](https://github.com/kiwibrew)
* Rename librenms script to lnms ([#9696](https://github.com/librenms/librenms/pull/9696)) - [murrant](https://github.com/murrant)
* Seeds can run on existing ([#9689](https://github.com/librenms/librenms/pull/9689)) - [murrant](https://github.com/murrant)
* Run both legacy schema and migrations in the same run if needed ([#9686](https://github.com/librenms/librenms/pull/9686)) - [murrant](https://github.com/murrant)
* Fix dbSchema 1000 skipping schema files ([#9685](https://github.com/librenms/librenms/pull/9685)) - [murrant](https://github.com/murrant)
* Removed NO_AUTO_CREATE_USER from mysql strict to support MySQL 8 ([#9668](https://github.com/librenms/librenms/pull/9668)) - [laf](https://github.com/laf)
* Increase snmp execution time limit to 20 minutes ([#9639](https://github.com/librenms/librenms/pull/9639)) - [murrant](https://github.com/murrant)
* Add group to sensor 1st discovery + template syntax {{ $xxx }} ([#9667](https://github.com/librenms/librenms/pull/9667)) - [PipoCanaja](https://github.com/PipoCanaja)
* Order by support for availability map widget ([#9663](https://github.com/librenms/librenms/pull/9663)) - [murrant](https://github.com/murrant)
* Remove broken routeros signal sensor ([#9650](https://github.com/librenms/librenms/pull/9650)) - [murrant](https://github.com/murrant)
* Fix sentry3 voltage sensors ([#9649](https://github.com/librenms/librenms/pull/9649)) - [murrant](https://github.com/murrant)
* Fix wireless sensor edit messages and collection alert rule ([#9624](https://github.com/librenms/librenms/pull/9624)) - [murrant](https://github.com/murrant)
* Fix poller.php with missing -h ([#9621](https://github.com/librenms/librenms/pull/9621)) - [murrant](https://github.com/murrant)
* Fix Infoblox NIOS graphs ([#9620](https://github.com/librenms/librenms/pull/9620)) - [murrant](https://github.com/murrant)
* Fix .gitingore "changed" files with github-remove script ([#9616](https://github.com/librenms/librenms/pull/9616)) - [murrant](https://github.com/murrant)
* Fix orphaned dashboards ([#9590](https://github.com/librenms/librenms/pull/9590)) - [murrant](https://github.com/murrant)

#### Webui
* Pressing enter on global search goes to the first result ([#9587](https://github.com/librenms/librenms/pull/9587)) - [murrant](https://github.com/murrant)
* Fix devices page search & OS loading ([#9752](https://github.com/librenms/librenms/pull/9752)) - [murrant](https://github.com/murrant)
* Rewrite devices page backend (and a little frontend) ([#9726](https://github.com/librenms/librenms/pull/9726)) - [murrant](https://github.com/murrant)
* Handle db update errors better in the installer ([#9701](https://github.com/librenms/librenms/pull/9701)) - [murrant](https://github.com/murrant)
* Improve display for 802.1X NAC ([#9706](https://github.com/librenms/librenms/pull/9706)) - [PipoCanaja](https://github.com/PipoCanaja)
* Refactor FDB Tables to Laravel ([#9669](https://github.com/librenms/librenms/pull/9669)) - [murrant](https://github.com/murrant)
* Better IP exists feedback when adding a device ([#9697](https://github.com/librenms/librenms/pull/9697)) - [murrant](https://github.com/murrant)
* Order by support for availability map widget ([#9663](https://github.com/librenms/librenms/pull/9663)) - [murrant](https://github.com/murrant)
* Sort device select by hostname ([#9607](https://github.com/librenms/librenms/pull/9607)) - [murrant](https://github.com/murrant)
* Alert schedule refactor ([#9514](https://github.com/librenms/librenms/pull/9514)) - [murrant](https://github.com/murrant)
* Removed Legacy Transport UI https://t.libren.ms/deprecation-alerting ([#9552](https://github.com/librenms/librenms/pull/9552)) - [laf](https://github.com/laf)

#### Device
* Adding temp/humidity sensors for raritan px2 ([#9719](https://github.com/librenms/librenms/pull/9719)) - [sjtarik](https://github.com/sjtarik)
* FreeBSD mempools Fix ([#9659](https://github.com/librenms/librenms/pull/9659)) - [vitalisator](https://github.com/vitalisator)
* Adding Sonicwall SMA 400 support ([#9555](https://github.com/librenms/librenms/pull/9555)) - [marvink87](https://github.com/marvink87)
* Support for Alcatel OmniPCX ([#9375](https://github.com/librenms/librenms/pull/9375)) - [PipoCanaja](https://github.com/PipoCanaja)
* Improve index check for compatibility with different versions of the DDOS ([#9698](https://github.com/librenms/librenms/pull/9698)) - [acl](https://github.com/acl)
* Eltek Valere more sensors ([#9713](https://github.com/librenms/librenms/pull/9713)) - [murrant](https://github.com/murrant)
* New OS: Schleifenbauer, entity-physical improvements, various html page fixes ([#9471](https://github.com/librenms/librenms/pull/9471)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* Huawei iBMC absent state grey ([#9691](https://github.com/librenms/librenms/pull/9691)) - [murrant](https://github.com/murrant)
* Nokia vrf bgp ([#9622](https://github.com/librenms/librenms/pull/9622)) - [vitalisator](https://github.com/vitalisator)
* Implement NAC data polling ([#9592](https://github.com/librenms/librenms/pull/9592)) - [PipoCanaja](https://github.com/PipoCanaja)
* Cable Modem Graphs for TopVision OS ([#9679](https://github.com/librenms/librenms/pull/9679)) - [djamp42](https://github.com/djamp42)
* Merge ethernetprobe2 into akcp and improve akcp ([#9465](https://github.com/librenms/librenms/pull/9465)) - [murrant](https://github.com/murrant)
* Sorting States using groups ([#9666](https://github.com/librenms/librenms/pull/9666)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added ignore mount point effects macOS ([#9652](https://github.com/librenms/librenms/pull/9652)) - [kkrumm1](https://github.com/kkrumm1)
* EATON-MGEUPS: Added sensors for temperature and humidity from … ([#9647](https://github.com/librenms/librenms/pull/9647)) - [Kal42](https://github.com/Kal42)
* Remove broken routeros signal sensor ([#9650](https://github.com/librenms/librenms/pull/9650)) - [murrant](https://github.com/murrant)
* Fix sentry3 voltage sensors ([#9649](https://github.com/librenms/librenms/pull/9649)) - [murrant](https://github.com/murrant)
* Fix some issues with sensor limits ([#9638](https://github.com/librenms/librenms/pull/9638)) - [murrant](https://github.com/murrant)
* Added support for Vigintos Modulator & Ampiflier ([#9488](https://github.com/librenms/librenms/pull/9488)) - [jozefrebjak](https://github.com/jozefrebjak)
* Device Support for Pegasus ([#9641](https://github.com/librenms/librenms/pull/9641)) - [vitalisator](https://github.com/vitalisator)
* TopVision CMTS ([#9627](https://github.com/librenms/librenms/pull/9627)) - [djamp42](https://github.com/djamp42)
* Fix Infoblox NIOS graphs ([#9620](https://github.com/librenms/librenms/pull/9620)) - [murrant](https://github.com/murrant)
* ZyXEL Telemetry - XGS4600-32F ([#9599](https://github.com/librenms/librenms/pull/9599)) - [cppmonkey](https://github.com/cppmonkey)

#### Security
* Rewrite devices page backend (and a little frontend) ([#9726](https://github.com/librenms/librenms/pull/9726)) - [murrant](https://github.com/murrant)
* Rewrite netcmd and ripe whois tools ([#9724](https://github.com/librenms/librenms/pull/9724)) - [murrant](https://github.com/murrant)
* Update dependencies ([#9657](https://github.com/librenms/librenms/pull/9657)) - [murrant](https://github.com/murrant)
* Refactor FDB Tables to Laravel ([#9669](https://github.com/librenms/librenms/pull/9669)) - [murrant](https://github.com/murrant)

#### Documentation
* Update docs on mkdocs ([#9631](https://github.com/librenms/librenms/pull/9631)) - [emestee](https://github.com/emestee)
* New OS: Suggest -d in discovery.php to clear cache ([#9602](https://github.com/librenms/librenms/pull/9602)) - [murrant](https://github.com/murrant)
* FAQ LibreNMS and MIBs ([#9664](https://github.com/librenms/librenms/pull/9664)) - [murrant](https://github.com/murrant)
* Update Fast-Ping-Check.md ([#9705](https://github.com/librenms/librenms/pull/9705)) - [sanegaming](https://github.com/sanegaming)
* Update poller_modules documentation for NAC ([#9704](https://github.com/librenms/librenms/pull/9704)) - [PipoCanaja](https://github.com/PipoCanaja)
* Docs:Update SNMP-Trap-Handler.md ([#9654](https://github.com/librenms/librenms/pull/9654)) - [JoshWeepie](https://github.com/JoshWeepie)
* Oxidized JunOS syslog hook documentation fix ([#9676](https://github.com/librenms/librenms/pull/9676)) - [nova-2nd](https://github.com/nova-2nd)
* Reference config options for new beta poller service ([#9644](https://github.com/librenms/librenms/pull/9644)) - [Swashy](https://github.com/Swashy)


---


##[2013 Changelog](Changelogs/2013.md)

##[2014 Changelog](Changelogs/2014.md)

##[2015 Changelog](Changelogs/2015.md)

##[2016 Changelog](Changelogs/2016.md)

##[2017 Changelog](Changelogs/2017.md)

##[2018 Changelog](Changelogs/2018.md)
