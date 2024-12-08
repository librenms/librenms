## 24.11.0
*(2024-11-20)*

A big thank you to the following 20 contributors this last month:

  - [eskyuu](https://github.com/eskyuu) (15)
  - [murrant](https://github.com/murrant) (13)
  - [laf](https://github.com/laf) (6)
  - [mpikzink](https://github.com/mpikzink) (5)
  - [thundersin](https://github.com/thundersin) (2)
  - [Calvario](https://github.com/Calvario) (2)
  - [rudybroersma](https://github.com/rudybroersma) (1)
  - [chunned](https://github.com/chunned) (1)
  - [netravnen](https://github.com/netravnen) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [opalivan](https://github.com/opalivan) (1)
  - [dracoling](https://github.com/dracoling) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [peejaychilds](https://github.com/peejaychilds) (1)
  - [kkrumm1](https://github.com/kkrumm1) (1)
  - [JeevesTuxis](https://github.com/JeevesTuxis) (1)
  - [jiannelli](https://github.com/jiannelli) (1)
  - [Jellyfrog](https://github.com/Jellyfrog) (1)
  - [Npeca75](https://github.com/Npeca75) (1)
  - [kaustubh6199](https://github.com/kaustubh6199) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [laf](https://github.com/laf) (23)
  - [murrant](https://github.com/murrant) (19)
  - [Jellyfrog](https://github.com/Jellyfrog) (10)
  - [PipoCanaja](https://github.com/PipoCanaja) (3)
  - [eskyuu](https://github.com/eskyuu) (1)

#### Security
* Fix various display name xss ([#16723](https://github.com/librenms/librenms/pull/16723)) - [murrant](https://github.com/murrant)
* Fix custom ports xss ([#16722](https://github.com/librenms/librenms/pull/16722)) - [murrant](https://github.com/murrant)
* Fix XSS on ports page ([#16721](https://github.com/librenms/librenms/pull/16721)) - [murrant](https://github.com/murrant)
* Additional XSS fixes ([#16660](https://github.com/librenms/librenms/pull/16660)) - [murrant](https://github.com/murrant)

#### Device
* Discovery fix for issue 16544 for ArubaOS-CX ([#16739](https://github.com/librenms/librenms/pull/16739)) - [rudybroersma](https://github.com/rudybroersma)
* VRP Transceivers, type + distance details ([#16724](https://github.com/librenms/librenms/pull/16724)) - [PipoCanaja](https://github.com/PipoCanaja)
* Adva-fsp150cp new OS ([#16720](https://github.com/librenms/librenms/pull/16720)) - [opalivan](https://github.com/opalivan)
* Add Power sensors ([#16708](https://github.com/librenms/librenms/pull/16708)) - [mpikzink](https://github.com/mpikzink)
* Netvision RFC: Add Socomec Hardware ([#16707](https://github.com/librenms/librenms/pull/16707)) - [mpikzink](https://github.com/mpikzink)
* Convert state sensors from inc.php to yaml ([#16704](https://github.com/librenms/librenms/pull/16704)) - [mpikzink](https://github.com/mpikzink)
* Windows nullable checks ([#16702](https://github.com/librenms/librenms/pull/16702)) - [murrant](https://github.com/murrant)
* Convert connection counts to sensors ([#16700](https://github.com/librenms/librenms/pull/16700)) - [murrant](https://github.com/murrant)
* Adding sensors for nokia dwdm coherent optical ports ([#16699](https://github.com/librenms/librenms/pull/16699)) - [thundersin](https://github.com/thundersin)
* Don't detect PoE Power if no Power is available ([#16698](https://github.com/librenms/librenms/pull/16698)) - [mpikzink](https://github.com/mpikzink)
* Don't detect stack state if stacking is disabled ([#16696](https://github.com/librenms/librenms/pull/16696)) - [mpikzink](https://github.com/mpikzink)
* Truenas storage fix ([#16684](https://github.com/librenms/librenms/pull/16684)) - [eskyuu](https://github.com/eskyuu)
* Use ifIndex for TPLINK LLDP neighbour lookup first ([#16682](https://github.com/librenms/librenms/pull/16682)) - [eskyuu](https://github.com/eskyuu)
* Fix Stack Topology alert Update procurve.yaml ([#16673](https://github.com/librenms/librenms/pull/16673)) - [kkrumm1](https://github.com/kkrumm1)
* Procurve transceiver ([#16672](https://github.com/librenms/librenms/pull/16672)) - [murrant](https://github.com/murrant)
* The unbound app creates RRD's incorrectly, with 'DERIVE'. ([#16671](https://github.com/librenms/librenms/pull/16671)) - [JeevesTuxis](https://github.com/JeevesTuxis)
* Fortigate - Add SSL VPN Sensor tunnel name ([#16656](https://github.com/librenms/librenms/pull/16656)) - [Calvario](https://github.com/Calvario)
* Support for TrueNAS-SCALE (new os) ([#16655](https://github.com/librenms/librenms/pull/16655)) - [jiannelli](https://github.com/jiannelli)
* [transceivers] Eltex MES23xx Transceiver support ([#16536](https://github.com/librenms/librenms/pull/16536)) - [Npeca75](https://github.com/Npeca75)

#### Webui
* Remove breaking qualifyColumn statement ([#16716](https://github.com/librenms/librenms/pull/16716)) - [dracoling](https://github.com/dracoling)
* Convert device neighbour blade to use components for device and port map pop-ups ([#16681](https://github.com/librenms/librenms/pull/16681)) - [eskyuu](https://github.com/eskyuu)
* Port down list now matches the menu ([#16430](https://github.com/librenms/librenms/pull/16430)) - [laf](https://github.com/laf)

#### Alerting
* Attempt to fix legacy email alerts ([#16730](https://github.com/librenms/librenms/pull/16730)) - [murrant](https://github.com/murrant)
* Add lnms maintenance cleanup command and fix alert rule UI (delete) generating orphans ([#16331](https://github.com/librenms/librenms/pull/16331)) - [Calvario](https://github.com/Calvario)

#### Maps
* Fix a bug in the custom map viewer if the device image is invalid ([#16694](https://github.com/librenms/librenms/pull/16694)) - [eskyuu](https://github.com/eskyuu)
* Fix for saveMapSettings() when creating a new map ([#16688](https://github.com/librenms/librenms/pull/16688)) - [eskyuu](https://github.com/eskyuu)
* Bugfix for setting dependency map selected border width ([#16685](https://github.com/librenms/librenms/pull/16685)) - [eskyuu](https://github.com/eskyuu)
* Map performance ([#16670](https://github.com/librenms/librenms/pull/16670)) - [eskyuu](https://github.com/eskyuu)
* Add a new edge option to select the position of the edge text ([#16669](https://github.com/librenms/librenms/pull/16669)) - [eskyuu](https://github.com/eskyuu)
* Add screenshot mode option to custom map widget ([#16668](https://github.com/librenms/librenms/pull/16668)) - [eskyuu](https://github.com/eskyuu)
* Add multi-select support to custom map editor ([#16659](https://github.com/librenms/librenms/pull/16659)) - [eskyuu](https://github.com/eskyuu)
* Update to current version of vis.js ([#16657](https://github.com/librenms/librenms/pull/16657)) - [eskyuu](https://github.com/eskyuu)
* Add new config option for dependency map vis options ([#16643](https://github.com/librenms/librenms/pull/16643)) - [eskyuu](https://github.com/eskyuu)
* Custom Map device images ([#16538](https://github.com/librenms/librenms/pull/16538)) - [eskyuu](https://github.com/eskyuu)

#### Api
* Fix updating port notes via the API ([#16424](https://github.com/librenms/librenms/pull/16424)) - [laf](https://github.com/laf)

#### Settings
* Task Scheduler Configuration ([#16356](https://github.com/librenms/librenms/pull/16356)) - [eskyuu](https://github.com/eskyuu)

#### Discovery
* Changing chromatic dispersion sensor measurement unit to ps/nm ([#16697](https://github.com/librenms/librenms/pull/16697)) - [thundersin](https://github.com/thundersin)
* VRP Transceivers Support ([#16480](https://github.com/librenms/librenms/pull/16480)) - [kaustubh6199](https://github.com/kaustubh6199)

#### Bug
* Fix sensor reset count null ([#16727](https://github.com/librenms/librenms/pull/16727)) - [murrant](https://github.com/murrant)
* Fix edge graph pop-up on edge maps ([#16693](https://github.com/librenms/librenms/pull/16693)) - [eskyuu](https://github.com/eskyuu)
* Dont migrate views in 2021_02_09_122930_migrate_to_utf8mb4 ([#16687](https://github.com/librenms/librenms/pull/16687)) - [peejaychilds](https://github.com/peejaychilds)

#### Documentation
* Fix hyperlink to install doc ([#16733](https://github.com/librenms/librenms/pull/16733)) - [chunned](https://github.com/chunned)

#### Translation
* Updated Russian translations ([#16736](https://github.com/librenms/librenms/pull/16736)) - [murrant](https://github.com/murrant)

#### Tests
* Start testing PHP 8.3 ([#16570](https://github.com/librenms/librenms/pull/16570)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Misc
* Allow underscores in hostnames ([#16683](https://github.com/librenms/librenms/pull/16683)) - [laf](https://github.com/laf)
* Updated github apply/remove to clear route and view cache ([#16470](https://github.com/librenms/librenms/pull/16470)) - [laf](https://github.com/laf)

#### Mibs
* Update MIKROTIK-MIB ([#16725](https://github.com/librenms/librenms/pull/16725)) - [netravnen](https://github.com/netravnen)

#### Dependencies
* Updated daily.sh to check for php 8.2 minimum ([#16734](https://github.com/librenms/librenms/pull/16734)) - [laf](https://github.com/laf)
* Bump symfony/http-client from 6.4.14 to 6.4.15 ([#16709](https://github.com/librenms/librenms/pull/16709)) - [dependabot](https://github.com/apps/dependabot)
* Dependency Updates ([#16695](https://github.com/librenms/librenms/pull/16695)) - [murrant](https://github.com/murrant)


## 24.10.0
*(2024-11-05)*

A big thank you to the following 26 contributors this last month:

  - [murrant](https://github.com/murrant) (36)
  - [VVelox](https://github.com/VVelox) (30)
  - [eskyuu](https://github.com/eskyuu) (15)
  - [laf](https://github.com/laf) (13)
  - [Calvario](https://github.com/Calvario) (5)
  - [jiannelli](https://github.com/jiannelli) (5)
  - [dependabot](https://github.com/apps/dependabot) (2)
  - [alwold](https://github.com/alwold) (2)
  - [bnerickson](https://github.com/bnerickson) (2)
  - [TridTech](https://github.com/TridTech) (2)
  - [lx1ge](https://github.com/lx1ge) (1)
  - [mengy-yu](https://github.com/mengy-yu) (1)
  - [vhuk](https://github.com/vhuk) (1)
  - [jkahk](https://github.com/jkahk) (1)
  - [netravnen](https://github.com/netravnen) (1)
  - [robje](https://github.com/robje) (1)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (1)
  - [JacobErnst98](https://github.com/JacobErnst98) (1)
  - [barhom](https://github.com/barhom) (1)
  - [Jellyfrog](https://github.com/Jellyfrog) (1)
  - [lukeofthetauri](https://github.com/lukeofthetauri) (1)
  - [davburns](https://github.com/davburns) (1)
  - [thundersin](https://github.com/thundersin) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [SaneiSaya](https://github.com/SaneiSaya) (1)
  - [martinvenes](https://github.com/martinvenes) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (45)
  - [laf](https://github.com/laf) (40)
  - [Jellyfrog](https://github.com/Jellyfrog) (30)
  - [electrocret](https://github.com/electrocret) (4)
  - [ottorei](https://github.com/ottorei) (3)
  - [eskyuu](https://github.com/eskyuu) (2)
  - [VVelox](https://github.com/VVelox) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)

#### Security
* Fix device dependency xss ([#16648](https://github.com/librenms/librenms/pull/16648)) - [murrant](https://github.com/murrant)
* Fix xss in netmap ([#16640](https://github.com/librenms/librenms/pull/16640)) - [murrant](https://github.com/murrant)
* Fix availability map xss ([#16632](https://github.com/librenms/librenms/pull/16632)) - [murrant](https://github.com/murrant)
* Fix XSS in customoid ([#16629](https://github.com/librenms/librenms/pull/16629)) - [murrant](https://github.com/murrant)
* Fix xss in report_this ([#16613](https://github.com/librenms/librenms/pull/16613)) - [murrant](https://github.com/murrant)
* App page update for Wireguard to escape stuff ([#16611](https://github.com/librenms/librenms/pull/16611)) - [VVelox](https://github.com/VVelox)
* Update app page for ZFS to escape stuff ([#16610](https://github.com/librenms/librenms/pull/16610)) - [VVelox](https://github.com/VVelox)
* App page update for Poudriere to escape stuff ([#16606](https://github.com/librenms/librenms/pull/16606)) - [VVelox](https://github.com/VVelox)
* Update app page for OSLV Monitor to escape stuff ([#16605](https://github.com/librenms/librenms/pull/16605)) - [VVelox](https://github.com/VVelox)
* Update app page for Postgres to escape stuff ([#16604](https://github.com/librenms/librenms/pull/16604)) - [VVelox](https://github.com/VVelox)
* Update app page for Opensearch to escape stuff ([#16603](https://github.com/librenms/librenms/pull/16603)) - [VVelox](https://github.com/VVelox)
* Update app page for Mojo CAPE Submit to escape stuff ([#16602](https://github.com/librenms/librenms/pull/16602)) - [VVelox](https://github.com/VVelox)
* App update for HV Monitor to escape stuff ([#16601](https://github.com/librenms/librenms/pull/16601)) - [VVelox](https://github.com/VVelox)
* Update Fail2ban app page to escape stuff ([#16600](https://github.com/librenms/librenms/pull/16600)) - [VVelox](https://github.com/VVelox)
* App page update for HTTP access log combined to escape stuff ([#16599](https://github.com/librenms/librenms/pull/16599)) - [VVelox](https://github.com/VVelox)
* App update for chronyd to escape stuff ([#16598](https://github.com/librenms/librenms/pull/16598)) - [VVelox](https://github.com/VVelox)
* Update app pages for CAPEv2 and Sneck to escape stuff ([#16597](https://github.com/librenms/librenms/pull/16597)) - [VVelox](https://github.com/VVelox)
* Fixed xss in services overview for device ([#16587](https://github.com/librenms/librenms/pull/16587)) - [laf](https://github.com/laf)
* Additional executable configuration item sanitation ([#16583](https://github.com/librenms/librenms/pull/16583)) - [murrant](https://github.com/murrant)
* Escape rrd hostname more ([#16578](https://github.com/librenms/librenms/pull/16578)) - [murrant](https://github.com/murrant)
* Block invalid hostnames ([#16577](https://github.com/librenms/librenms/pull/16577)) - [murrant](https://github.com/murrant)
* Fix services page xss ([#16576](https://github.com/librenms/librenms/pull/16576)) - [murrant](https://github.com/murrant)
* Fix device display name xss vulnerabilities ([#16575](https://github.com/librenms/librenms/pull/16575)) - [murrant](https://github.com/murrant)
* Fixed XSS in Wireless and Health pages ([#16569](https://github.com/librenms/librenms/pull/16569)) - [laf](https://github.com/laf)
* Fixed XSS issue with Device overview page and overwrite_ip ([#16567](https://github.com/librenms/librenms/pull/16567)) - [laf](https://github.com/laf)
* Fix XSS in port edit secion ([#16566](https://github.com/librenms/librenms/pull/16566)) - [laf](https://github.com/laf)
* Fixed XSS in device hostname for Capture ([#16565](https://github.com/librenms/librenms/pull/16565)) - [laf](https://github.com/laf)
* Filter ExamplePlugin output ([#16562](https://github.com/librenms/librenms/pull/16562)) - [murrant](https://github.com/murrant)
* Resolved XSS issue in bill_name value on user screen ([#16560](https://github.com/librenms/librenms/pull/16560)) - [laf](https://github.com/laf)
* Moved the API token to be generated server side to resolve XSS ([#16558](https://github.com/librenms/librenms/pull/16558)) - [laf](https://github.com/laf)

#### Device
* Add Procurve stack monitoring ([#16625](https://github.com/librenms/librenms/pull/16625)) - [lx1ge](https://github.com/lx1ge)
* Added support for Cisco 1200 series devices ([#16588](https://github.com/librenms/librenms/pull/16588)) - [laf](https://github.com/laf)
* Update webpower-smart2.yaml ([#16573](https://github.com/librenms/librenms/pull/16573)) - [mengy-yu](https://github.com/mengy-yu)
* Added build number for Win 11 24H2. ([#16561](https://github.com/librenms/librenms/pull/16561)) - [vhuk](https://github.com/vhuk)
* Teltonika trb500 added ([#16556](https://github.com/librenms/librenms/pull/16556)) - [jkahk](https://github.com/jkahk)
* Fs nmu correct properties ([#16550](https://github.com/librenms/librenms/pull/16550)) - [robje](https://github.com/robje)
* Added Teracom support ([#16549](https://github.com/librenms/librenms/pull/16549)) - [laf](https://github.com/laf)
* Fix fs-centec transceiver bias ([#16547](https://github.com/librenms/librenms/pull/16547)) - [murrant](https://github.com/murrant)
* F5 Loadbalancers, use full path for includes ([#16519](https://github.com/librenms/librenms/pull/16519)) - [Calvario](https://github.com/Calvario)
* Add PoE usage for Procurve switches. ([#16515](https://github.com/librenms/librenms/pull/16515)) - [TridTech](https://github.com/TridTech)
* Added Support for Cisco 3140 Security Appliance ([#16512](https://github.com/librenms/librenms/pull/16512)) - [lukeofthetauri](https://github.com/lukeofthetauri)
* Dell sensor fix variable leaking ([#16511](https://github.com/librenms/librenms/pull/16511)) - [TridTech](https://github.com/TridTech)
* F5 Loadbalancers, use full path for includes ([#16505](https://github.com/librenms/librenms/pull/16505)) - [Calvario](https://github.com/Calvario)
* Fixed sensor discovery issue for serverscheck os ([#16499](https://github.com/librenms/librenms/pull/16499)) - [laf](https://github.com/laf)
* Add temperature sensors for RoomAlert3S ([#16496](https://github.com/librenms/librenms/pull/16496)) - [davburns](https://github.com/davburns)
* Update infinera-groove.inc.php ([#16490](https://github.com/librenms/librenms/pull/16490)) - [thundersin](https://github.com/thundersin)
* Updated Serverscheck temp sensor discovery ([#16488](https://github.com/librenms/librenms/pull/16488)) - [laf](https://github.com/laf)
* FS centec switch transceiver temperature not showing ([#16469](https://github.com/librenms/librenms/pull/16469)) - [murrant](https://github.com/murrant)
* OcNOS transceiver tx not visible ([#16468](https://github.com/librenms/librenms/pull/16468)) - [murrant](https://github.com/murrant)
* Improve OcNOS port breakout detection ([#16466](https://github.com/librenms/librenms/pull/16466)) - [murrant](https://github.com/murrant)
* OcNOS transceiver temperature display fix ([#16460](https://github.com/librenms/librenms/pull/16460)) - [murrant](https://github.com/murrant)
* Update eltek-webpower.yaml ([#16188](https://github.com/librenms/librenms/pull/16188)) - [martinvenes](https://github.com/martinvenes)

#### Webui
* Add nbsp to fix display of cog icon in device toolbar ([#16634](https://github.com/librenms/librenms/pull/16634)) - [eskyuu](https://github.com/eskyuu)
* ARP search trim MAC search phrase ([#16626](https://github.com/librenms/librenms/pull/16626)) - [murrant](https://github.com/murrant)
* Fixed a display issue with the menu ([#16546](https://github.com/librenms/librenms/pull/16546)) - [eskyuu](https://github.com/eskyuu)
* Popover fixes on transports page ([#16527](https://github.com/librenms/librenms/pull/16527)) - [alwold](https://github.com/alwold)
* Fix all devices menu when no device types exist ([#16521](https://github.com/librenms/librenms/pull/16521)) - [jiannelli](https://github.com/jiannelli)
* Eager load relationships (Performance) ([#16503](https://github.com/librenms/librenms/pull/16503)) - [murrant](https://github.com/murrant)
* Improve device ports loading speed ([#16500](https://github.com/librenms/librenms/pull/16500)) - [murrant](https://github.com/murrant)
* Dark theme improvements (aesthetics and readability) ([#16486](https://github.com/librenms/librenms/pull/16486)) - [jiannelli](https://github.com/jiannelli)
* Device overview: direct transceiver link ([#16485](https://github.com/librenms/librenms/pull/16485)) - [murrant](https://github.com/murrant)
* Fix popup javascript ([#16459](https://github.com/librenms/librenms/pull/16459)) - [murrant](https://github.com/murrant)

#### Alerting
* Fix browser push alert too large ([#16633](https://github.com/librenms/librenms/pull/16633)) - [murrant](https://github.com/murrant)
* MS Teams: use AdaptiveCard for JSON tests ([#16630](https://github.com/librenms/librenms/pull/16630)) - [murrant](https://github.com/murrant)
* Throw exception when mail delivery fails ([#16591](https://github.com/librenms/librenms/pull/16591)) - [alwold](https://github.com/alwold)
* Add Gotify Implementation ([#16553](https://github.com/librenms/librenms/pull/16553)) - [netravnen](https://github.com/netravnen)
* Add some more alert rules for checking for smart self test failures ([#16494](https://github.com/librenms/librenms/pull/16494)) - [VVelox](https://github.com/VVelox)
* Discord Transport Fix: 'Error: Invalid Field' When 'Fields to Embed' Left Empty ([#16439](https://github.com/librenms/librenms/pull/16439)) - [jiannelli](https://github.com/jiannelli)
* Created IBM On Call Manager Alert Transport ([#16395](https://github.com/librenms/librenms/pull/16395)) - [SaneiSaya](https://github.com/SaneiSaya)

#### Graphs
* Fix graph row responsive ([#16618](https://github.com/librenms/librenms/pull/16618)) - [murrant](https://github.com/murrant)

#### Maps
* Fix MapDataController::linkUseColour return ([#16649](https://github.com/librenms/librenms/pull/16649)) - [murrant](https://github.com/murrant)
* Use the standard menu option for all map page refreshes ([#16644](https://github.com/librenms/librenms/pull/16644)) - [eskyuu](https://github.com/eskyuu)
* Fix worldmap widget up/down filtering ([#16641](https://github.com/librenms/librenms/pull/16641)) - [murrant](https://github.com/murrant)
* Fixes for netmaps following the update ([#16638](https://github.com/librenms/librenms/pull/16638)) - [eskyuu](https://github.com/eskyuu)
* Fix map maintenance missing ([#16627](https://github.com/librenms/librenms/pull/16627)) - [murrant](https://github.com/murrant)
* Custom Map widget unselected ([#16616](https://github.com/librenms/librenms/pull/16616)) - [murrant](https://github.com/murrant)
* Added missing form fields from the modal and added JS fix ([#16615](https://github.com/librenms/librenms/pull/16615)) - [eskyuu](https://github.com/eskyuu)
* Added a white background to custom map labels to make them more readable ([#16574](https://github.com/librenms/librenms/pull/16574)) - [eskyuu](https://github.com/eskyuu)
* Custom Map vis.js options ([#16535](https://github.com/librenms/librenms/pull/16535)) - [eskyuu](https://github.com/eskyuu)
* Custom Map legend configuration ([#16534](https://github.com/librenms/librenms/pull/16534)) - [eskyuu](https://github.com/eskyuu)
* Custom Map line fixed width option ([#16533](https://github.com/librenms/librenms/pull/16533)) - [eskyuu](https://github.com/eskyuu)
* Indicate when a linked custom map has down devices ([#16518](https://github.com/librenms/librenms/pull/16518)) - [laf](https://github.com/laf)
* Make device text and links dark red when the connected device is offline ([#16477](https://github.com/librenms/librenms/pull/16477)) - [eskyuu](https://github.com/eskyuu)
* Added a widget for custom maps ([#16454](https://github.com/librenms/librenms/pull/16454)) - [eskyuu](https://github.com/eskyuu)
* Refactor javascript in custom maps ([#16450](https://github.com/librenms/librenms/pull/16450)) - [eskyuu](https://github.com/eskyuu)
* Refactor all map pages to be Laravel pages with AJAX data refresh ([#15567](https://github.com/librenms/librenms/pull/15567)) - [eskyuu](https://github.com/eskyuu)

#### Applications
* Update app page for Suricata to escape stuff ([#16608](https://github.com/librenms/librenms/pull/16608)) - [VVelox](https://github.com/VVelox)
* ZFS app update, adding zpool io stats and  error stats ([#16551](https://github.com/librenms/librenms/pull/16551)) - [VVelox](https://github.com/VVelox)
* Add missing graphs for the poudriere app page ([#16495](https://github.com/librenms/librenms/pull/16495)) - [VVelox](https://github.com/VVelox)
* OS Level Virtualization Monitor ([#16269](https://github.com/librenms/librenms/pull/16269)) - [VVelox](https://github.com/VVelox)
* HTTP Access Log Combined monitoring ([#16247](https://github.com/librenms/librenms/pull/16247)) - [VVelox](https://github.com/VVelox)

#### Api
* Add API endpoint to retrieve port FDB MAC addresses ([#16520](https://github.com/librenms/librenms/pull/16520)) - [barhom](https://github.com/barhom)

#### Discovery
* Avoid DivisionByZeroError ([#16464](https://github.com/librenms/librenms/pull/16464)) - [Calvario](https://github.com/Calvario)

#### Authentication
* Avoid a redirect loop if we have a login error ([#16366](https://github.com/librenms/librenms/pull/16366)) - [Calvario](https://github.com/Calvario)

#### Bug
* Catch RRD Startup Exception ([#16646](https://github.com/librenms/librenms/pull/16646)) - [murrant](https://github.com/murrant)
* Catch more mail exceptions ([#16645](https://github.com/librenms/librenms/pull/16645)) - [murrant](https://github.com/murrant)
* Fix arp search where like ([#16622](https://github.com/librenms/librenms/pull/16622)) - [murrant](https://github.com/murrant)
* Graylog handle empty API response ([#16617](https://github.com/librenms/librenms/pull/16617)) - [murrant](https://github.com/murrant)
* Corrected the use of the MIB name TRIPPLITE-12X ([#16609](https://github.com/librenms/librenms/pull/16609)) - [laf](https://github.com/laf)
* Fixing chronyd application log message variable bug/typo. ([#16543](https://github.com/librenms/librenms/pull/16543)) - [bnerickson](https://github.com/bnerickson)
* Fix IPv6 BGP discovery ([#16542](https://github.com/librenms/librenms/pull/16542)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Stop services from being checked when device is offline and service does not have an IP set ([#16497](https://github.com/librenms/librenms/pull/16497)) - [eskyuu](https://github.com/eskyuu)
* Snmpsim setup: fix python exe name ([#16492](https://github.com/librenms/librenms/pull/16492)) - [murrant](https://github.com/murrant)
* VLANs global page, missing changes ([#16484](https://github.com/librenms/librenms/pull/16484)) - [murrant](https://github.com/murrant)
* Webui - fix exception in Oxidized page date handling ([#16475](https://github.com/librenms/librenms/pull/16475)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add circular loop detection to MaxDepth ([#15579](https://github.com/librenms/librenms/pull/15579)) - [eskyuu](https://github.com/eskyuu)

#### Documentation
* Update install docs for OSLV::Monitor as it is now in the FreeBSD pkg repo as p5-OSLV-Monitor ([#16612](https://github.com/librenms/librenms/pull/16612)) - [VVelox](https://github.com/VVelox)
* Update App docs to add generic cpanm instructions for all perl stuff ([#16596](https://github.com/librenms/librenms/pull/16596)) - [VVelox](https://github.com/VVelox)
* Update App docs for Suricata for installing available depends via pkgs for FreeBSD and Debian ([#16595](https://github.com/librenms/librenms/pull/16595)) - [VVelox](https://github.com/VVelox)
* Update App docs for SMART for Debian depends ([#16594](https://github.com/librenms/librenms/pull/16594)) - [VVelox](https://github.com/VVelox)
* Update App docs for log size monitor for Debian, adding depends available via apt ([#16593](https://github.com/librenms/librenms/pull/16593)) - [VVelox](https://github.com/VVelox)
* Update App docs for HV Monitor depends install ([#16592](https://github.com/librenms/librenms/pull/16592)) - [VVelox](https://github.com/VVelox)
* Update App docs for Sagan for also installing depends ([#16590](https://github.com/librenms/librenms/pull/16590)) - [VVelox](https://github.com/VVelox)
* Update App docs for the newest version of the privoxy extend ([#16589](https://github.com/librenms/librenms/pull/16589)) - [VVelox](https://github.com/VVelox)
* Update App docs for linux_softnet_stat as it no longer needs Gzip::Faster ([#16586](https://github.com/librenms/librenms/pull/16586)) - [VVelox](https://github.com/VVelox)
* Update App docs for Monitoring::Sneck some ([#16585](https://github.com/librenms/librenms/pull/16585)) - [VVelox](https://github.com/VVelox)
* Updating http_access_log_combined Application Documentation w/SELinux Instructions ([#16555](https://github.com/librenms/librenms/pull/16555)) - [bnerickson](https://github.com/bnerickson)
* Add depends install instruction for nfs extend and update selinux info ([#16539](https://github.com/librenms/librenms/pull/16539)) - [VVelox](https://github.com/VVelox)
* Update Test-Units.md to reflect PR #12531 requiring the -v flag in scripts/collect-snmp-data.php. ([#16528](https://github.com/librenms/librenms/pull/16528)) - [JacobErnst98](https://github.com/JacobErnst98)
* Sensor state support improvements (state translations with string/numeric values and others) ([#16522](https://github.com/librenms/librenms/pull/16522)) - [jiannelli](https://github.com/jiannelli)
* Update list of supported OSes ([#16516](https://github.com/librenms/librenms/pull/16516)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update docs for Opensearch for the newest version of the extend ([#16482](https://github.com/librenms/librenms/pull/16482)) - [VVelox](https://github.com/VVelox)

#### Misc
* Typo in debug ([#16545](https://github.com/librenms/librenms/pull/16545)) - [Calvario](https://github.com/Calvario)

#### Dependencies
* Bump elliptic from 6.5.7 to 6.6.0 ([#16619](https://github.com/librenms/librenms/pull/16619)) - [dependabot](https://github.com/apps/dependabot)
* Bump cookie and express ([#16530](https://github.com/librenms/librenms/pull/16530)) - [dependabot](https://github.com/apps/dependabot)


## 24.9.0
*(2024-09-29)*

A big thank you to the following 27 contributors this last month:

  - [murrant](https://github.com/murrant) (55)
  - [PipoCanaja](https://github.com/PipoCanaja) (6)
  - [laf](https://github.com/laf) (6)
  - [dependabot](https://github.com/apps/dependabot) (5)
  - [Calvario](https://github.com/Calvario) (4)
  - [eskyuu](https://github.com/eskyuu) (3)
  - [Jellyfrog](https://github.com/Jellyfrog) (3)
  - [opalivan](https://github.com/opalivan) (2)
  - [mcook55](https://github.com/mcook55) (2)
  - [jayceeemperador](https://github.com/jayceeemperador) (2)
  - [electrocret](https://github.com/electrocret) (2)
  - [MarlinMr](https://github.com/MarlinMr) (2)
  - [dasdromedar](https://github.com/dasdromedar) (1)
  - [lhwolfarth](https://github.com/lhwolfarth) (1)
  - [zippanto](https://github.com/zippanto) (1)
  - [f0o](https://github.com/f0o) (1)
  - [dagbdagb](https://github.com/dagbdagb) (1)
  - [ShaneMcC](https://github.com/ShaneMcC) (1)
  - [Martin22](https://github.com/Martin22) (1)
  - [jcamos](https://github.com/jcamos) (1)
  - [makriska](https://github.com/makriska) (1)
  - [mehdiMj-ir](https://github.com/mehdiMj-ir) (1)
  - [bonzo81](https://github.com/bonzo81) (1)
  - [gdepeyrot](https://github.com/gdepeyrot) (1)
  - [ervin09](https://github.com/ervin09) (1)
  - [VVelox](https://github.com/VVelox) (1)
  - [descilla](https://github.com/descilla) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (32)
  - [murrant](https://github.com/murrant) (21)
  - [PipoCanaja](https://github.com/PipoCanaja) (16)
  - [laf](https://github.com/laf) (14)
  - [ottorei](https://github.com/ottorei) (1)
  - [f0o](https://github.com/f0o) (1)
  - [freddy36](https://github.com/freddy36) (1)

#### Feature
* LLDP Discovery - LldpRemPortId convert to string when in HEX ([#16438](https://github.com/librenms/librenms/pull/16438)) - [lhwolfarth](https://github.com/lhwolfarth)
* Improved module controls ([#16372](https://github.com/librenms/librenms/pull/16372)) - [murrant](https://github.com/murrant)
* Plugin Update ([#16291](https://github.com/librenms/librenms/pull/16291)) - [murrant](https://github.com/murrant)
* Transceiver Support ([#16165](https://github.com/librenms/librenms/pull/16165)) - [murrant](https://github.com/murrant)

#### Security
* Sanitize custom map SVGs ([#16448](https://github.com/librenms/librenms/pull/16448)) - [murrant](https://github.com/murrant)
* Fix device dependencies xss ([#16447](https://github.com/librenms/librenms/pull/16447)) - [murrant](https://github.com/murrant)
* Fix alert template creation xss ([#16446](https://github.com/librenms/librenms/pull/16446)) - [murrant](https://github.com/murrant)
* Fix potential xss in edit alert transport ([#16445](https://github.com/librenms/librenms/pull/16445)) - [murrant](https://github.com/murrant)
* Alert transport details xss ([#16444](https://github.com/librenms/librenms/pull/16444)) - [murrant](https://github.com/murrant)
* Fix alert rule name stored XSS ([#16443](https://github.com/librenms/librenms/pull/16443)) - [murrant](https://github.com/murrant)
* Fix device group stored XSS ([#16442](https://github.com/librenms/librenms/pull/16442)) - [murrant](https://github.com/murrant)

#### Device
* Transceivers - Extend to more Cisco Containers ([#16456](https://github.com/librenms/librenms/pull/16456)) - [PipoCanaja](https://github.com/PipoCanaja)
* APC - runtime discovery, apply divisor ([#16441](https://github.com/librenms/librenms/pull/16441)) - [PipoCanaja](https://github.com/PipoCanaja)
* Updated APC sensors to include upsAdvTestDiagnosticsResults state ([#16435](https://github.com/librenms/librenms/pull/16435)) - [laf](https://github.com/laf)
* Adva 150CC - Exclude nemihubshelf (150CM) ([#16426](https://github.com/librenms/librenms/pull/16426)) - [opalivan](https://github.com/opalivan)
* Fix AdvaOSA OS naming ([#16425](https://github.com/librenms/librenms/pull/16425)) - [opalivan](https://github.com/opalivan)
* Added support for Siteboss360 appliances ([#16422](https://github.com/librenms/librenms/pull/16422)) - [mcook55](https://github.com/mcook55)
* Add support for Argus Rectifier CXRC appliances ([#16418](https://github.com/librenms/librenms/pull/16418)) - [mcook55](https://github.com/mcook55)
* FS centec: disable bulk for vlans ([#16417](https://github.com/librenms/librenms/pull/16417)) - [murrant](https://github.com/murrant)
* Extension of support for IMCO LS/PS backup power sources. ([#16377](https://github.com/librenms/librenms/pull/16377)) - [Martin22](https://github.com/Martin22)
* Add Support for Cisco ISE SNS-3595-K9 ([#16376](https://github.com/librenms/librenms/pull/16376)) - [jayceeemperador](https://github.com/jayceeemperador)
* More cisco state fixes ([#16369](https://github.com/librenms/librenms/pull/16369)) - [murrant](https://github.com/murrant)
* Fix some issues with cisco entity sensors ([#16365](https://github.com/librenms/librenms/pull/16365)) - [murrant](https://github.com/murrant)
* Fix Cisco entity-sensor ([#16351](https://github.com/librenms/librenms/pull/16351)) - [murrant](https://github.com/murrant)
* Awplus sensor fixes ([#16348](https://github.com/librenms/librenms/pull/16348)) - [murrant](https://github.com/murrant)
* Cisco C9800 Wireless Controller AP Count Support ([#16342](https://github.com/librenms/librenms/pull/16342)) - [jayceeemperador](https://github.com/jayceeemperador)
* OcNOS Add AS7712-32X inventory port mapping ([#16332](https://github.com/librenms/librenms/pull/16332)) - [murrant](https://github.com/murrant)
* OcNOS Inventory support ([#16320](https://github.com/librenms/librenms/pull/16320)) - [murrant](https://github.com/murrant)
* Device - infortrend - add temperature sensor ([#16316](https://github.com/librenms/librenms/pull/16316)) - [ervin09](https://github.com/ervin09)
* Remove SmartOptics Skip Values ([#16284](https://github.com/librenms/librenms/pull/16284)) - [electrocret](https://github.com/electrocret)

#### Webui
* Format Oxidized update time ([#16455](https://github.com/librenms/librenms/pull/16455)) - [dasdromedar](https://github.com/dasdromedar)
* Use 2 decimal places for bps numbers ([#16451](https://github.com/librenms/librenms/pull/16451)) - [eskyuu](https://github.com/eskyuu)
* Improve reset to default button on settings page ([#16436](https://github.com/librenms/librenms/pull/16436)) - [laf](https://github.com/laf)
* Stop showing bills that do not exist ([#16423](https://github.com/librenms/librenms/pull/16423)) - [laf](https://github.com/laf)
* Updated from values for port graphs to be valid ([#16416](https://github.com/librenms/librenms/pull/16416)) - [laf](https://github.com/laf)
* Global VLAN ports page ([#16415](https://github.com/librenms/librenms/pull/16415)) - [murrant](https://github.com/murrant)
* Fix normal users UI widget AlertLog Stats ([#16363](https://github.com/librenms/librenms/pull/16363)) - [Calvario](https://github.com/Calvario)
* Fix bad health sensor labels in the ui ([#16350](https://github.com/librenms/librenms/pull/16350)) - [murrant](https://github.com/murrant)
* Fix ignore check for F5 component polling and Web UI ([#16329](https://github.com/librenms/librenms/pull/16329)) - [bonzo81](https://github.com/bonzo81)
* Use Device Displayname for AlertRules ([#16322](https://github.com/librenms/librenms/pull/16322)) - [electrocret](https://github.com/electrocret)
* Fix unaligned Leaflet Awesome marker icons ([#16321](https://github.com/librenms/librenms/pull/16321)) - [gdepeyrot](https://github.com/gdepeyrot)

#### Alerting
* Fix alert -1 count ([#16359](https://github.com/librenms/librenms/pull/16359)) - [Calvario](https://github.com/Calvario)
* Critical alerts now notify ([#16355](https://github.com/librenms/librenms/pull/16355)) - [jcamos](https://github.com/jcamos)
* Fix alert detail count missing (try2) ([#16309](https://github.com/librenms/librenms/pull/16309)) - [murrant](https://github.com/murrant)
* Work around alert with bad data ([#16287](https://github.com/librenms/librenms/pull/16287)) - [murrant](https://github.com/murrant)

#### Graphs
* Fix for wrong graph being referenced ([#16400](https://github.com/librenms/librenms/pull/16400)) - [dagbdagb](https://github.com/dagbdagb)

#### Applications
* Poudriere support ([#16229](https://github.com/librenms/librenms/pull/16229)) - [VVelox](https://github.com/VVelox)
* Updated nvidia poller app: handle slightly changed nvidia-smi output … ([#16158](https://github.com/librenms/librenms/pull/16158)) - [descilla](https://github.com/descilla)

#### Api
* Reject API device_add force add that are missing snmp info ([#16314](https://github.com/librenms/librenms/pull/16314)) - [murrant](https://github.com/murrant)

#### Discovery
* Fixed the issues with JetStream lldp discovery ([#16414](https://github.com/librenms/librenms/pull/16414)) - [laf](https://github.com/laf)
* Fix sensor discover when device_id is omitted ([#16389](https://github.com/librenms/librenms/pull/16389)) - [murrant](https://github.com/murrant)
* Only post to eventlog when specific columns change ([#16370](https://github.com/librenms/librenms/pull/16370)) - [murrant](https://github.com/murrant)
* Entity Physical discovery: Rewrite to modern style ([#16289](https://github.com/librenms/librenms/pull/16289)) - [murrant](https://github.com/murrant)

#### Bug
* Fix double escaping sysname in device dependencies ([#16458](https://github.com/librenms/librenms/pull/16458)) - [murrant](https://github.com/murrant)
* Fix custom sensors logic not being loaded in some cases ([#16433](https://github.com/librenms/librenms/pull/16433)) - [zippanto](https://github.com/zippanto)
* Fix smokeping generator for TCP transport ([#16421](https://github.com/librenms/librenms/pull/16421)) - [f0o](https://github.com/f0o)
* Fix module tests ([#16397](https://github.com/librenms/librenms/pull/16397)) - [murrant](https://github.com/murrant)
* Save guessed limits ([#16396](https://github.com/librenms/librenms/pull/16396)) - [murrant](https://github.com/murrant)
* Fix sensor state translations ([#16393](https://github.com/librenms/librenms/pull/16393)) - [murrant](https://github.com/murrant)
* Fix detecting stacks in unstacked switches. ([#16384](https://github.com/librenms/librenms/pull/16384)) - [ShaneMcC](https://github.com/ShaneMcC)
* Availability calculations, handle bad data ([#16368](https://github.com/librenms/librenms/pull/16368)) - [murrant](https://github.com/murrant)
* Fix rrd show command ([#16357](https://github.com/librenms/librenms/pull/16357)) - [murrant](https://github.com/murrant)
* Ignore entity-sensor invalid sensors ([#16347](https://github.com/librenms/librenms/pull/16347)) - [murrant](https://github.com/murrant)
* Fix handling of zero value for entSensorThresholdValue for dbm cisco sensor ([#16336](https://github.com/librenms/librenms/pull/16336)) - [makriska](https://github.com/makriska)
* Fix numeric value out of range for _rate ([#16325](https://github.com/librenms/librenms/pull/16325)) - [Calvario](https://github.com/Calvario)
* Fix misc Cisco polling errors ([#16307](https://github.com/librenms/librenms/pull/16307)) - [murrant](https://github.com/murrant)
* Fix sodium_compat 32bit ([#16303](https://github.com/librenms/librenms/pull/16303)) - [murrant](https://github.com/murrant)
* Allow syslog hooks to be set by lnms config:set ([#16302](https://github.com/librenms/librenms/pull/16302)) - [murrant](https://github.com/murrant)
* Fix setting the device wrong field in module tests ([#16296](https://github.com/librenms/librenms/pull/16296)) - [murrant](https://github.com/murrant)
* Bug - VRP - fix OutOfRange QueryException + missing key ([#16290](https://github.com/librenms/librenms/pull/16290)) - [PipoCanaja](https://github.com/PipoCanaja)
* Bug - Component "error" length issue ([#15918](https://github.com/librenms/librenms/pull/15918)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Refactor
* Sensors move can skip and output into singleton ([#16392](https://github.com/librenms/librenms/pull/16392)) - [murrant](https://github.com/murrant)
* Sensors remove reliance on global variable ([#16344](https://github.com/librenms/librenms/pull/16344)) - [murrant](https://github.com/murrant)
* Poll device job ([#16306](https://github.com/librenms/librenms/pull/16306)) - [murrant](https://github.com/murrant)

#### Cleanup
* Remove internal usages of config_to_json.php ([#16388](https://github.com/librenms/librenms/pull/16388)) - [murrant](https://github.com/murrant)
* Remove legacy db config ([#16385](https://github.com/librenms/librenms/pull/16385)) - [murrant](https://github.com/murrant)
* Updated ping command to explicitly use sync when dispatching job ([#16346](https://github.com/librenms/librenms/pull/16346)) - [eskyuu](https://github.com/eskyuu)
* Remove global $var access functions ([#16345](https://github.com/librenms/librenms/pull/16345)) - [murrant](https://github.com/murrant)
* Replace echo with Log calls in OS code ([#16310](https://github.com/librenms/librenms/pull/16310)) - [murrant](https://github.com/murrant)
* Remove echo from modern modules ([#16308](https://github.com/librenms/librenms/pull/16308)) - [murrant](https://github.com/murrant)
* Remove CIMC custom inventory code ([#16305](https://github.com/librenms/librenms/pull/16305)) - [murrant](https://github.com/murrant)
* Remove c6kxbar ([#16304](https://github.com/librenms/librenms/pull/16304)) - [murrant](https://github.com/murrant)
* Plugins v2 cleanup ([#16298](https://github.com/librenms/librenms/pull/16298)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Documentation
* Bump minimum PHP version to 8.2 ([#16413](https://github.com/librenms/librenms/pull/16413)) - [Jellyfrog](https://github.com/Jellyfrog)
* Updated documentation navigation and options to tidy things up ([#16409](https://github.com/librenms/librenms/pull/16409)) - [laf](https://github.com/laf)
* Python3-command-runner is only available in Ubuntu 24.04 ([#16390](https://github.com/librenms/librenms/pull/16390)) - [murrant](https://github.com/murrant)
* Add full python dependencies to Ubuntu 22.04 and 24.04 install docs ([#16354](https://github.com/librenms/librenms/pull/16354)) - [murrant](https://github.com/murrant)
* Update RRDCached Tune version ([#16352](https://github.com/librenms/librenms/pull/16352)) - [Calvario](https://github.com/Calvario)
* Add Ubuntu 24.04 installation method ([#16334](https://github.com/librenms/librenms/pull/16334)) - [mehdiMj-ir](https://github.com/mehdiMj-ir)
* VisJS: fix link ([#16315](https://github.com/librenms/librenms/pull/16315)) - [MarlinMr](https://github.com/MarlinMr)
* Remove indentation causing faulty rendering ([#16301](https://github.com/librenms/librenms/pull/16301)) - [MarlinMr](https://github.com/MarlinMr)

#### Tests
* Improve Rrd datastore test ([#16353](https://github.com/librenms/librenms/pull/16353)) - [murrant](https://github.com/murrant)
* Use the already installed chromedriver ([#16341](https://github.com/librenms/librenms/pull/16341)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Misc
* Cli show string enums in snmp commands ([#16317](https://github.com/librenms/librenms/pull/16317)) - [murrant](https://github.com/murrant)

#### Internal Features
* Save-test-data.php improvements ([#16367](https://github.com/librenms/librenms/pull/16367)) - [murrant](https://github.com/murrant)

#### Dependencies
* Bump send and express ([#16406](https://github.com/librenms/librenms/pull/16406)) - [dependabot](https://github.com/apps/dependabot)
* Bump serve-static and express ([#16405](https://github.com/librenms/librenms/pull/16405)) - [dependabot](https://github.com/apps/dependabot)
* Bump webpack from 5.91.0 to 5.94.0 ([#16338](https://github.com/librenms/librenms/pull/16338)) - [dependabot](https://github.com/apps/dependabot)
* Bump elliptic from 6.5.5 to 6.5.7 ([#16293](https://github.com/librenms/librenms/pull/16293)) - [dependabot](https://github.com/apps/dependabot)
* Bump axios from 1.6.8 to 1.7.4 ([#16292](https://github.com/librenms/librenms/pull/16292)) - [dependabot](https://github.com/apps/dependabot)


## 24.9.0
*(2024-09-29)*

A big thank you to the following 27 contributors this last month:

  - [murrant](https://github.com/murrant) (55)
  - [PipoCanaja](https://github.com/PipoCanaja) (6)
  - [laf](https://github.com/laf) (6)
  - [dependabot](https://github.com/apps/dependabot) (5)
  - [Calvario](https://github.com/Calvario) (4)
  - [eskyuu](https://github.com/eskyuu) (3)
  - [Jellyfrog](https://github.com/Jellyfrog) (3)
  - [opalivan](https://github.com/opalivan) (2)
  - [mcook55](https://github.com/mcook55) (2)
  - [jayceeemperador](https://github.com/jayceeemperador) (2)
  - [electrocret](https://github.com/electrocret) (2)
  - [MarlinMr](https://github.com/MarlinMr) (2)
  - [dasdromedar](https://github.com/dasdromedar) (1)
  - [lhwolfarth](https://github.com/lhwolfarth) (1)
  - [zippanto](https://github.com/zippanto) (1)
  - [f0o](https://github.com/f0o) (1)
  - [dagbdagb](https://github.com/dagbdagb) (1)
  - [ShaneMcC](https://github.com/ShaneMcC) (1)
  - [Martin22](https://github.com/Martin22) (1)
  - [jcamos](https://github.com/jcamos) (1)
  - [makriska](https://github.com/makriska) (1)
  - [mehdiMj-ir](https://github.com/mehdiMj-ir) (1)
  - [bonzo81](https://github.com/bonzo81) (1)
  - [gdepeyrot](https://github.com/gdepeyrot) (1)
  - [ervin09](https://github.com/ervin09) (1)
  - [VVelox](https://github.com/VVelox) (1)
  - [descilla](https://github.com/descilla) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (32)
  - [murrant](https://github.com/murrant) (21)
  - [PipoCanaja](https://github.com/PipoCanaja) (16)
  - [laf](https://github.com/laf) (14)
  - [ottorei](https://github.com/ottorei) (1)
  - [f0o](https://github.com/f0o) (1)
  - [freddy36](https://github.com/freddy36) (1)

#### Feature
* LLDP Discovery - LldpRemPortId convert to string when in HEX ([#16438](https://github.com/librenms/librenms/pull/16438)) - [lhwolfarth](https://github.com/lhwolfarth)
* Improved module controls ([#16372](https://github.com/librenms/librenms/pull/16372)) - [murrant](https://github.com/murrant)
* Plugin Update ([#16291](https://github.com/librenms/librenms/pull/16291)) - [murrant](https://github.com/murrant)
* Transceiver Support ([#16165](https://github.com/librenms/librenms/pull/16165)) - [murrant](https://github.com/murrant)

#### Security
* Sanitize custom map SVGs ([#16448](https://github.com/librenms/librenms/pull/16448)) - [murrant](https://github.com/murrant)
* Fix device dependencies xss ([#16447](https://github.com/librenms/librenms/pull/16447)) - [murrant](https://github.com/murrant)
* Fix alert template creation xss ([#16446](https://github.com/librenms/librenms/pull/16446)) - [murrant](https://github.com/murrant)
* Fix potential xss in edit alert transport ([#16445](https://github.com/librenms/librenms/pull/16445)) - [murrant](https://github.com/murrant)
* Alert transport details xss ([#16444](https://github.com/librenms/librenms/pull/16444)) - [murrant](https://github.com/murrant)
* Fix alert rule name stored XSS ([#16443](https://github.com/librenms/librenms/pull/16443)) - [murrant](https://github.com/murrant)
* Fix device group stored XSS ([#16442](https://github.com/librenms/librenms/pull/16442)) - [murrant](https://github.com/murrant)

#### Device
* Transceivers - Extend to more Cisco Containers ([#16456](https://github.com/librenms/librenms/pull/16456)) - [PipoCanaja](https://github.com/PipoCanaja)
* APC - runtime discovery, apply divisor ([#16441](https://github.com/librenms/librenms/pull/16441)) - [PipoCanaja](https://github.com/PipoCanaja)
* Updated APC sensors to include upsAdvTestDiagnosticsResults state ([#16435](https://github.com/librenms/librenms/pull/16435)) - [laf](https://github.com/laf)
* Adva 150CC - Exclude nemihubshelf (150CM) ([#16426](https://github.com/librenms/librenms/pull/16426)) - [opalivan](https://github.com/opalivan)
* Fix AdvaOSA OS naming ([#16425](https://github.com/librenms/librenms/pull/16425)) - [opalivan](https://github.com/opalivan)
* Added support for Siteboss360 appliances ([#16422](https://github.com/librenms/librenms/pull/16422)) - [mcook55](https://github.com/mcook55)
* Add support for Argus Rectifier CXRC appliances ([#16418](https://github.com/librenms/librenms/pull/16418)) - [mcook55](https://github.com/mcook55)
* FS centec: disable bulk for vlans ([#16417](https://github.com/librenms/librenms/pull/16417)) - [murrant](https://github.com/murrant)
* Extension of support for IMCO LS/PS backup power sources. ([#16377](https://github.com/librenms/librenms/pull/16377)) - [Martin22](https://github.com/Martin22)
* Add Support for Cisco ISE SNS-3595-K9 ([#16376](https://github.com/librenms/librenms/pull/16376)) - [jayceeemperador](https://github.com/jayceeemperador)
* More cisco state fixes ([#16369](https://github.com/librenms/librenms/pull/16369)) - [murrant](https://github.com/murrant)
* Fix some issues with cisco entity sensors ([#16365](https://github.com/librenms/librenms/pull/16365)) - [murrant](https://github.com/murrant)
* Fix Cisco entity-sensor ([#16351](https://github.com/librenms/librenms/pull/16351)) - [murrant](https://github.com/murrant)
* Awplus sensor fixes ([#16348](https://github.com/librenms/librenms/pull/16348)) - [murrant](https://github.com/murrant)
* Cisco C9800 Wireless Controller AP Count Support ([#16342](https://github.com/librenms/librenms/pull/16342)) - [jayceeemperador](https://github.com/jayceeemperador)
* OcNOS Add AS7712-32X inventory port mapping ([#16332](https://github.com/librenms/librenms/pull/16332)) - [murrant](https://github.com/murrant)
* OcNOS Inventory support ([#16320](https://github.com/librenms/librenms/pull/16320)) - [murrant](https://github.com/murrant)
* Device - infortrend - add temperature sensor ([#16316](https://github.com/librenms/librenms/pull/16316)) - [ervin09](https://github.com/ervin09)
* Remove SmartOptics Skip Values ([#16284](https://github.com/librenms/librenms/pull/16284)) - [electrocret](https://github.com/electrocret)

#### Webui
* Format Oxidized update time ([#16455](https://github.com/librenms/librenms/pull/16455)) - [dasdromedar](https://github.com/dasdromedar)
* Use 2 decimal places for bps numbers ([#16451](https://github.com/librenms/librenms/pull/16451)) - [eskyuu](https://github.com/eskyuu)
* Improve reset to default button on settings page ([#16436](https://github.com/librenms/librenms/pull/16436)) - [laf](https://github.com/laf)
* Stop showing bills that do not exist ([#16423](https://github.com/librenms/librenms/pull/16423)) - [laf](https://github.com/laf)
* Updated from values for port graphs to be valid ([#16416](https://github.com/librenms/librenms/pull/16416)) - [laf](https://github.com/laf)
* Global VLAN ports page ([#16415](https://github.com/librenms/librenms/pull/16415)) - [murrant](https://github.com/murrant)
* Fix normal users UI widget AlertLog Stats ([#16363](https://github.com/librenms/librenms/pull/16363)) - [Calvario](https://github.com/Calvario)
* Fix bad health sensor labels in the ui ([#16350](https://github.com/librenms/librenms/pull/16350)) - [murrant](https://github.com/murrant)
* Fix ignore check for F5 component polling and Web UI ([#16329](https://github.com/librenms/librenms/pull/16329)) - [bonzo81](https://github.com/bonzo81)
* Use Device Displayname for AlertRules ([#16322](https://github.com/librenms/librenms/pull/16322)) - [electrocret](https://github.com/electrocret)
* Fix unaligned Leaflet Awesome marker icons ([#16321](https://github.com/librenms/librenms/pull/16321)) - [gdepeyrot](https://github.com/gdepeyrot)

#### Alerting
* Fix alert -1 count ([#16359](https://github.com/librenms/librenms/pull/16359)) - [Calvario](https://github.com/Calvario)
* Critical alerts now notify ([#16355](https://github.com/librenms/librenms/pull/16355)) - [jcamos](https://github.com/jcamos)
* Fix alert detail count missing (try2) ([#16309](https://github.com/librenms/librenms/pull/16309)) - [murrant](https://github.com/murrant)
* Work around alert with bad data ([#16287](https://github.com/librenms/librenms/pull/16287)) - [murrant](https://github.com/murrant)

#### Graphs
* Fix for wrong graph being referenced ([#16400](https://github.com/librenms/librenms/pull/16400)) - [dagbdagb](https://github.com/dagbdagb)

#### Applications
* Poudriere support ([#16229](https://github.com/librenms/librenms/pull/16229)) - [VVelox](https://github.com/VVelox)
* Updated nvidia poller app: handle slightly changed nvidia-smi output … ([#16158](https://github.com/librenms/librenms/pull/16158)) - [descilla](https://github.com/descilla)

#### Api
* Reject API device_add force add that are missing snmp info ([#16314](https://github.com/librenms/librenms/pull/16314)) - [murrant](https://github.com/murrant)

#### Discovery
* Fixed the issues with JetStream lldp discovery ([#16414](https://github.com/librenms/librenms/pull/16414)) - [laf](https://github.com/laf)
* Fix sensor discover when device_id is omitted ([#16389](https://github.com/librenms/librenms/pull/16389)) - [murrant](https://github.com/murrant)
* Only post to eventlog when specific columns change ([#16370](https://github.com/librenms/librenms/pull/16370)) - [murrant](https://github.com/murrant)
* Entity Physical discovery: Rewrite to modern style ([#16289](https://github.com/librenms/librenms/pull/16289)) - [murrant](https://github.com/murrant)

#### Bug
* Fix double escaping sysname in device dependencies ([#16458](https://github.com/librenms/librenms/pull/16458)) - [murrant](https://github.com/murrant)
* Fix custom sensors logic not being loaded in some cases ([#16433](https://github.com/librenms/librenms/pull/16433)) - [zippanto](https://github.com/zippanto)
* Fix smokeping generator for TCP transport ([#16421](https://github.com/librenms/librenms/pull/16421)) - [f0o](https://github.com/f0o)
* Fix module tests ([#16397](https://github.com/librenms/librenms/pull/16397)) - [murrant](https://github.com/murrant)
* Save guessed limits ([#16396](https://github.com/librenms/librenms/pull/16396)) - [murrant](https://github.com/murrant)
* Fix sensor state translations ([#16393](https://github.com/librenms/librenms/pull/16393)) - [murrant](https://github.com/murrant)
* Fix detecting stacks in unstacked switches. ([#16384](https://github.com/librenms/librenms/pull/16384)) - [ShaneMcC](https://github.com/ShaneMcC)
* Availability calculations, handle bad data ([#16368](https://github.com/librenms/librenms/pull/16368)) - [murrant](https://github.com/murrant)
* Fix rrd show command ([#16357](https://github.com/librenms/librenms/pull/16357)) - [murrant](https://github.com/murrant)
* Ignore entity-sensor invalid sensors ([#16347](https://github.com/librenms/librenms/pull/16347)) - [murrant](https://github.com/murrant)
* Fix handling of zero value for entSensorThresholdValue for dbm cisco sensor ([#16336](https://github.com/librenms/librenms/pull/16336)) - [makriska](https://github.com/makriska)
* Fix numeric value out of range for _rate ([#16325](https://github.com/librenms/librenms/pull/16325)) - [Calvario](https://github.com/Calvario)
* Fix misc Cisco polling errors ([#16307](https://github.com/librenms/librenms/pull/16307)) - [murrant](https://github.com/murrant)
* Fix sodium_compat 32bit ([#16303](https://github.com/librenms/librenms/pull/16303)) - [murrant](https://github.com/murrant)
* Allow syslog hooks to be set by lnms config:set ([#16302](https://github.com/librenms/librenms/pull/16302)) - [murrant](https://github.com/murrant)
* Fix setting the device wrong field in module tests ([#16296](https://github.com/librenms/librenms/pull/16296)) - [murrant](https://github.com/murrant)
* Bug - VRP - fix OutOfRange QueryException + missing key ([#16290](https://github.com/librenms/librenms/pull/16290)) - [PipoCanaja](https://github.com/PipoCanaja)
* Bug - Component "error" length issue ([#15918](https://github.com/librenms/librenms/pull/15918)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Refactor
* Sensors move can skip and output into singleton ([#16392](https://github.com/librenms/librenms/pull/16392)) - [murrant](https://github.com/murrant)
* Sensors remove reliance on global variable ([#16344](https://github.com/librenms/librenms/pull/16344)) - [murrant](https://github.com/murrant)
* Poll device job ([#16306](https://github.com/librenms/librenms/pull/16306)) - [murrant](https://github.com/murrant)

#### Cleanup
* Remove internal usages of config_to_json.php ([#16388](https://github.com/librenms/librenms/pull/16388)) - [murrant](https://github.com/murrant)
* Remove legacy db config ([#16385](https://github.com/librenms/librenms/pull/16385)) - [murrant](https://github.com/murrant)
* Updated ping command to explicitly use sync when dispatching job ([#16346](https://github.com/librenms/librenms/pull/16346)) - [eskyuu](https://github.com/eskyuu)
* Remove global $var access functions ([#16345](https://github.com/librenms/librenms/pull/16345)) - [murrant](https://github.com/murrant)
* Replace echo with Log calls in OS code ([#16310](https://github.com/librenms/librenms/pull/16310)) - [murrant](https://github.com/murrant)
* Remove echo from modern modules ([#16308](https://github.com/librenms/librenms/pull/16308)) - [murrant](https://github.com/murrant)
* Remove CIMC custom inventory code ([#16305](https://github.com/librenms/librenms/pull/16305)) - [murrant](https://github.com/murrant)
* Remove c6kxbar ([#16304](https://github.com/librenms/librenms/pull/16304)) - [murrant](https://github.com/murrant)
* Plugins v2 cleanup ([#16298](https://github.com/librenms/librenms/pull/16298)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Documentation
* Bump minimum PHP version to 8.2 ([#16413](https://github.com/librenms/librenms/pull/16413)) - [Jellyfrog](https://github.com/Jellyfrog)
* Updated documentation navigation and options to tidy things up ([#16409](https://github.com/librenms/librenms/pull/16409)) - [laf](https://github.com/laf)
* Python3-command-runner is only available in Ubuntu 24.04 ([#16390](https://github.com/librenms/librenms/pull/16390)) - [murrant](https://github.com/murrant)
* Add full python dependencies to Ubuntu 22.04 and 24.04 install docs ([#16354](https://github.com/librenms/librenms/pull/16354)) - [murrant](https://github.com/murrant)
* Update RRDCached Tune version ([#16352](https://github.com/librenms/librenms/pull/16352)) - [Calvario](https://github.com/Calvario)
* Add Ubuntu 24.04 installation method ([#16334](https://github.com/librenms/librenms/pull/16334)) - [mehdiMj-ir](https://github.com/mehdiMj-ir)
* VisJS: fix link ([#16315](https://github.com/librenms/librenms/pull/16315)) - [MarlinMr](https://github.com/MarlinMr)
* Remove indentation causing faulty rendering ([#16301](https://github.com/librenms/librenms/pull/16301)) - [MarlinMr](https://github.com/MarlinMr)

#### Tests
* Improve Rrd datastore test ([#16353](https://github.com/librenms/librenms/pull/16353)) - [murrant](https://github.com/murrant)
* Use the already installed chromedriver ([#16341](https://github.com/librenms/librenms/pull/16341)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Misc
* Cli show string enums in snmp commands ([#16317](https://github.com/librenms/librenms/pull/16317)) - [murrant](https://github.com/murrant)

#### Internal Features
* Save-test-data.php improvements ([#16367](https://github.com/librenms/librenms/pull/16367)) - [murrant](https://github.com/murrant)

#### Dependencies
* Bump send and express ([#16406](https://github.com/librenms/librenms/pull/16406)) - [dependabot](https://github.com/apps/dependabot)
* Bump serve-static and express ([#16405](https://github.com/librenms/librenms/pull/16405)) - [dependabot](https://github.com/apps/dependabot)
* Bump webpack from 5.91.0 to 5.94.0 ([#16338](https://github.com/librenms/librenms/pull/16338)) - [dependabot](https://github.com/apps/dependabot)
* Bump elliptic from 6.5.5 to 6.5.7 ([#16293](https://github.com/librenms/librenms/pull/16293)) - [dependabot](https://github.com/apps/dependabot)
* Bump axios from 1.6.8 to 1.7.4 ([#16292](https://github.com/librenms/librenms/pull/16292)) - [dependabot](https://github.com/apps/dependabot)


## 24.8.0
*(2024-08-15)*

A big thank you to the following 19 contributors this last month:

  - [murrant](https://github.com/murrant) (18)
  - [PipoCanaja](https://github.com/PipoCanaja) (5)
  - [Npeca75](https://github.com/Npeca75) (2)
  - [Jellyfrog](https://github.com/Jellyfrog) (2)
  - [nicolasberens](https://github.com/nicolasberens) (2)
  - [electrocret](https://github.com/electrocret) (2)
  - [dethmetaljeff](https://github.com/dethmetaljeff) (2)
  - [xorrkaz](https://github.com/xorrkaz) (2)
  - [rudybroersma](https://github.com/rudybroersma) (2)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [ethan-bmn](https://github.com/ethan-bmn) (1)
  - [suom1](https://github.com/suom1) (1)
  - [hatboxen](https://github.com/hatboxen) (1)
  - [freddy36](https://github.com/freddy36) (1)
  - [Ferris-0815](https://github.com/Ferris-0815) (1)
  - [mib1185](https://github.com/mib1185) (1)
  - [ervin09](https://github.com/ervin09) (1)
  - [x0ul](https://github.com/x0ul) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (11)
  - [Jellyfrog](https://github.com/Jellyfrog) (10)
  - [PipoCanaja](https://github.com/PipoCanaja) (7)
  - [electrocret](https://github.com/electrocret) (4)
  - [f0o](https://github.com/f0o) (1)
  - [VVelox](https://github.com/VVelox) (1)

#### Breaking Change
* Fix Port Channel ([#16227](https://github.com/librenms/librenms/pull/16227)) - [murrant](https://github.com/murrant)

#### Device
* Bug - Fix CISCO-BGP4-MIB logic ([#16260](https://github.com/librenms/librenms/pull/16260)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add support for GUDE Expert Sensor Box ([#16257](https://github.com/librenms/librenms/pull/16257)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add skip_values to iosxr hsrp ([#16251](https://github.com/librenms/librenms/pull/16251)) - [electrocret](https://github.com/electrocret)
* Improve Fiberstore S3900 series support ([#16225](https://github.com/librenms/librenms/pull/16225)) - [freddy36](https://github.com/freddy36)
* Add support for FortiNet FortiExtender ([#16219](https://github.com/librenms/librenms/pull/16219)) - [rudybroersma](https://github.com/rudybroersma)
* F5-Loadbalancer module to support an expiration check of the installed certificates ([#16217](https://github.com/librenms/librenms/pull/16217)) - [Ferris-0815](https://github.com/Ferris-0815)
* Add value 0 to HP Physical Drive Status (meaning no disk is inserted) ([#16211](https://github.com/librenms/librenms/pull/16211)) - [rudybroersma](https://github.com/rudybroersma)
* Tripplite console server ([#16156](https://github.com/librenms/librenms/pull/16156)) - [nicolasberens](https://github.com/nicolasberens)
* Device - Adding support to Infortrend DS3016 ([#16070](https://github.com/librenms/librenms/pull/16070)) - [ervin09](https://github.com/ervin09)
* Device - Added Baicells Atom OD04 CPE support ([#14838](https://github.com/librenms/librenms/pull/14838)) - [x0ul](https://github.com/x0ul)

#### Webui
* [webui] sort vlan tooltip by vlanid ([#16266](https://github.com/librenms/librenms/pull/16266)) - [Npeca75](https://github.com/Npeca75)
* Add Servicename to Alert Detail ([#16249](https://github.com/librenms/librenms/pull/16249)) - [electrocret](https://github.com/electrocret)
* Update graph timezone data ([#16244](https://github.com/librenms/librenms/pull/16244)) - [murrant](https://github.com/murrant)
* Fix custom map default settings error ([#16236](https://github.com/librenms/librenms/pull/16236)) - [murrant](https://github.com/murrant)
* Add link on alert-rules page to display active alerts for rule ([#16232](https://github.com/librenms/librenms/pull/16232)) - [dethmetaljeff](https://github.com/dethmetaljeff)
* Custom map defaults ([#16212](https://github.com/librenms/librenms/pull/16212)) - [murrant](https://github.com/murrant)
* Make also the total in and out interface errors selectable on the ports list ([#16073](https://github.com/librenms/librenms/pull/16073)) - [mib1185](https://github.com/mib1185)

#### Alerting
* Add bgp peer description to alert_detail ([#16233](https://github.com/librenms/librenms/pull/16233)) - [dethmetaljeff](https://github.com/dethmetaljeff)

#### Api
* Fix list_arp API ([#16243](https://github.com/librenms/librenms/pull/16243)) - [murrant](https://github.com/murrant)

#### Discovery
* Discovery, make sure where is set ([#16237](https://github.com/librenms/librenms/pull/16237)) - [murrant](https://github.com/murrant)
* Discovery - LLDPv2 support extension, and discovery-protocols tests ([#16113](https://github.com/librenms/librenms/pull/16113)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Polling
* Nac polling improvement ([#16265](https://github.com/librenms/librenms/pull/16265)) - [murrant](https://github.com/murrant)
* Fix poller wrapper debug option ([#16214](https://github.com/librenms/librenms/pull/16214)) - [murrant](https://github.com/murrant)

#### Authentication
* Set `default_role` when registering instead of at every login ([#16235](https://github.com/librenms/librenms/pull/16235)) - [suom1](https://github.com/suom1)

#### Bug
* Fix alert bug when key missing ([#16281](https://github.com/librenms/librenms/pull/16281)) - [murrant](https://github.com/murrant)
* Remove file differing by case only ([#16280](https://github.com/librenms/librenms/pull/16280)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Fix runtime cache ([#16272](https://github.com/librenms/librenms/pull/16272)) - [murrant](https://github.com/murrant)
* Bug - Fixing 'cisco-pw' cpwVcMplsPeerLdpID ([#16268](https://github.com/librenms/librenms/pull/16268)) - [PipoCanaja](https://github.com/PipoCanaja)
* [webui] fix port_row.blade generate vlan link ([#16256](https://github.com/librenms/librenms/pull/16256)) - [Npeca75](https://github.com/Npeca75)
* Bug - services - fix splitting of perfdata ([#16255](https://github.com/librenms/librenms/pull/16255)) - [nicolasberens](https://github.com/nicolasberens)
* Cleanup - Ensure percentage is calculated out of positive values only ([#16250](https://github.com/librenms/librenms/pull/16250)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix error from MikroTik routers when updating BGP peer info ([#16224](https://github.com/librenms/librenms/pull/16224)) - [xorrkaz](https://github.com/xorrkaz)
* Fix snmpsim in CI ([#16213](https://github.com/librenms/librenms/pull/16213)) - [murrant](https://github.com/murrant)

#### Refactor
* Refactor SnmpResponse mapTable ([#16238](https://github.com/librenms/librenms/pull/16238)) - [murrant](https://github.com/murrant)

#### Cleanup
* Mark addhost.php as deprecated ([#16283](https://github.com/librenms/librenms/pull/16283)) - [murrant](https://github.com/murrant)
* Validate.php proper exit code ([#16274](https://github.com/librenms/librenms/pull/16274)) - [murrant](https://github.com/murrant)
* Remove FILTER_SANITIZE_STRING ([#16264](https://github.com/librenms/librenms/pull/16264)) - [murrant](https://github.com/murrant)

#### Documentation
* Update Devices.md ([#16252](https://github.com/librenms/librenms/pull/16252)) - [ethan-bmn](https://github.com/ethan-bmn)
* Docs Update: Large Scale LibreNMS Deployment Example ([#16226](https://github.com/librenms/librenms/pull/16226)) - [hatboxen](https://github.com/hatboxen)

#### Misc
* Add support for Prometheus pushgateway basic auth ([#16230](https://github.com/librenms/librenms/pull/16230)) - [xorrkaz](https://github.com/xorrkaz)

#### Internal Features
* Improve Snmpsim usage to ease testing ([#15471](https://github.com/librenms/librenms/pull/15471)) - [murrant](https://github.com/murrant)

#### Dependencies
* Update PHP dependencies ([#16263](https://github.com/librenms/librenms/pull/16263)) - [murrant](https://github.com/murrant)
* Bump postcss from 7.0.39 to 8.4.40 ([#16262](https://github.com/librenms/librenms/pull/16262)) - [dependabot](https://github.com/apps/dependabot)


## 24.7.0
*(2024-07-17)*

A big thank you to the following 25 contributors this last month:

  - [murrant](https://github.com/murrant) (28)
  - [freddy36](https://github.com/freddy36) (6)
  - [VVelox](https://github.com/VVelox) (5)
  - [rudybroersma](https://github.com/rudybroersma) (2)
  - [nicolasberens](https://github.com/nicolasberens) (2)
  - [electrocret](https://github.com/electrocret) (2)
  - [slashdoom](https://github.com/slashdoom) (2)
  - [dependabot](https://github.com/apps/dependabot) (2)
  - [fabriciotm](https://github.com/fabriciotm) (1)
  - [TridTech](https://github.com/TridTech) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [Walkablenormal](https://github.com/Walkablenormal) (1)
  - [jediblair](https://github.com/jediblair) (1)
  - [westerterp](https://github.com/westerterp) (1)
  - [Npeca75](https://github.com/Npeca75) (1)
  - [kanokc](https://github.com/kanokc) (1)
  - [dennypage](https://github.com/dennypage) (1)
  - [normand-hue](https://github.com/normand-hue) (1)
  - [peejaychilds](https://github.com/peejaychilds) (1)
  - [bonzo81](https://github.com/bonzo81) (1)
  - [schnobbc](https://github.com/schnobbc) (1)
  - [dasdromedar](https://github.com/dasdromedar) (1)
  - [VoipTelCH](https://github.com/VoipTelCH) (1)
  - [f7naz](https://github.com/f7naz) (1)
  - [jepke](https://github.com/jepke) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (26)
  - [PipoCanaja](https://github.com/PipoCanaja) (10)
  - [Jellyfrog](https://github.com/Jellyfrog) (5)
  - [electrocret](https://github.com/electrocret) (3)
  - [ottorei](https://github.com/ottorei) (2)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)

#### Feature
* Lnms snmp:translate always show textual and numeric translations ([#16187](https://github.com/librenms/librenms/pull/16187)) - [murrant](https://github.com/murrant)
* InfluxDBv2 allow filter by group and disable debug by default ([#16186](https://github.com/librenms/librenms/pull/16186)) - [Walkablenormal](https://github.com/Walkablenormal)

#### Device
* Fix FortiGate Cluster Sync status ([#16206](https://github.com/librenms/librenms/pull/16206)) - [rudybroersma](https://github.com/rudybroersma)
* Add transceiver threshold support ([#16203](https://github.com/librenms/librenms/pull/16203)) - [freddy36](https://github.com/freddy36)
* Add NAC to Arubaos-CX ([#16194](https://github.com/librenms/librenms/pull/16194)) - [TridTech](https://github.com/TridTech)
* Add Hardware and frmware detection for moxa P510 ([#16185](https://github.com/librenms/librenms/pull/16185)) - [nicolasberens](https://github.com/nicolasberens)
* Fix up the Siteboss571 discovery yaml to split pdnOutputCurrentValue and pdnMainCurrentValue indexes ([#16181](https://github.com/librenms/librenms/pull/16181)) - [jediblair](https://github.com/jediblair)
* Adjust Line Nominal default limits ([#16180](https://github.com/librenms/librenms/pull/16180)) - [freddy36](https://github.com/freddy36)
* Add more Synology disk health info ([#16178](https://github.com/librenms/librenms/pull/16178)) - [westerterp](https://github.com/westerterp)
* Add support for Fiberstore branded Centec switches ([#16175](https://github.com/librenms/librenms/pull/16175)) - [freddy36](https://github.com/freddy36)
* Fix php issue in cisco ntp code ([#16172](https://github.com/librenms/librenms/pull/16172)) - [murrant](https://github.com/murrant)
* Allow for AXIS Panoramic cameras such as the P4707 ([#16166](https://github.com/librenms/librenms/pull/16166)) - [dennypage](https://github.com/dennypage)
* Add support for Fiberstore branded BDCOM switches ([#16162](https://github.com/librenms/librenms/pull/16162)) - [freddy36](https://github.com/freddy36)
* Add transceiver monitoring ([#16160](https://github.com/librenms/librenms/pull/16160)) - [freddy36](https://github.com/freddy36)
* Add support for more Cisco FTD devices ([#16150](https://github.com/librenms/librenms/pull/16150)) - [normand-hue](https://github.com/normand-hue)
* ArubaOS - Addtional support to poll Active VPN sessions ([#16137](https://github.com/librenms/librenms/pull/16137)) - [schnobbc](https://github.com/schnobbc)
* Update eaton-sc200.yaml ([#16133](https://github.com/librenms/librenms/pull/16133)) - [dasdromedar](https://github.com/dasdromedar)
* Update axis detection ([#16130](https://github.com/librenms/librenms/pull/16130)) - [nicolasberens](https://github.com/nicolasberens)
* New OS broadworks / broadsoft ([#16078](https://github.com/librenms/librenms/pull/16078)) - [jepke](https://github.com/jepke)

#### Webui
* Maps - Keep edge black when link is 0 bps ([#16192](https://github.com/librenms/librenms/pull/16192)) - [PipoCanaja](https://github.com/PipoCanaja)
* [webui] Ports: correct sorting order when using ifName ([#16170](https://github.com/librenms/librenms/pull/16170)) - [Npeca75](https://github.com/Npeca75)
* Handle missing device when linking ([#16164](https://github.com/librenms/librenms/pull/16164)) - [murrant](https://github.com/murrant)
* WebUI - Dark mode menu fix ([#16152](https://github.com/librenms/librenms/pull/16152)) - [slashdoom](https://github.com/slashdoom)
* Fix port link device missing ([#16151](https://github.com/librenms/librenms/pull/16151)) - [murrant](https://github.com/murrant)
* Port link component easier graphs ([#16147](https://github.com/librenms/librenms/pull/16147)) - [murrant](https://github.com/murrant)
* Fix graph row lazy loading ([#16145](https://github.com/librenms/librenms/pull/16145)) - [murrant](https://github.com/murrant)
* Left align text for dashboard widgets ([#16138](https://github.com/librenms/librenms/pull/16138)) - [bonzo81](https://github.com/bonzo81)
* Device Ports settings ([#16132](https://github.com/librenms/librenms/pull/16132)) - [murrant](https://github.com/murrant)
* Change port pagination default to 32 ([#16131](https://github.com/librenms/librenms/pull/16131)) - [murrant](https://github.com/murrant)
* Ports UI update ([#16115](https://github.com/librenms/librenms/pull/16115)) - [murrant](https://github.com/murrant)

#### Alerting
* Alertmanager, Striptag Dynamic Variables! ([#16141](https://github.com/librenms/librenms/pull/16141)) - [electrocret](https://github.com/electrocret)

#### Snmp Traps
* SnmpTrap Handler for Cisco IOS LDP Session UP and DOWN ([#16107](https://github.com/librenms/librenms/pull/16107)) - [f7naz](https://github.com/f7naz)

#### Applications
* Add missing graphs for NFS app page ([#16197](https://github.com/librenms/librenms/pull/16197)) - [VVelox](https://github.com/VVelox)
* Extend update for wireguard, correct is_int to is_numeric for polling purposes, and clean up the app page ([#16182](https://github.com/librenms/librenms/pull/16182)) - [VVelox](https://github.com/VVelox)
* PHP-FPM app update to handle multiple pools ([#16122](https://github.com/librenms/librenms/pull/16122)) - [VVelox](https://github.com/VVelox)
* Add some alert template items for CAPEv2 ([#16077](https://github.com/librenms/librenms/pull/16077)) - [VVelox](https://github.com/VVelox)
* Add generic and improved NFS support with initial support for both FreeBSD and Linux ([#15906](https://github.com/librenms/librenms/pull/15906)) - [VVelox](https://github.com/VVelox)

#### Api
* Convert list_arp API to Eloquent ([#16111](https://github.com/librenms/librenms/pull/16111)) - [murrant](https://github.com/murrant)
* Fixed wrong column and parameter used when deleting a location via API ([#16109](https://github.com/librenms/librenms/pull/16109)) - [VoipTelCH](https://github.com/VoipTelCH)

#### Authentication
* Handle ad/ldap authorizer search error ([#16139](https://github.com/librenms/librenms/pull/16139)) - [murrant](https://github.com/murrant)

#### Bug
* Fix null in sensors discovery ([#16201](https://github.com/librenms/librenms/pull/16201)) - [murrant](https://github.com/murrant)
* Fix incorrect get_class call ([#16179](https://github.com/librenms/librenms/pull/16179)) - [murrant](https://github.com/murrant)
* Fix some testing issues ([#16174](https://github.com/librenms/librenms/pull/16174)) - [murrant](https://github.com/murrant)
* BGP integer fields fix ([#16173](https://github.com/librenms/librenms/pull/16173)) - [murrant](https://github.com/murrant)
* Fix for lnms snmp:translate ([#16159](https://github.com/librenms/librenms/pull/16159)) - [murrant](https://github.com/murrant)

#### Documentation
* Changelog cleanup ([#16154](https://github.com/librenms/librenms/pull/16154)) - [murrant](https://github.com/murrant)
* Fortigate append-index doc ([#16153](https://github.com/librenms/librenms/pull/16153)) - [electrocret](https://github.com/electrocret)
* Clarify okta claim configuration requirement ([#16142](https://github.com/librenms/librenms/pull/16142)) - [peejaychilds](https://github.com/peejaychilds)
* [DOC] - Update doc/API/DeviceGroups.md ([#16140](https://github.com/librenms/librenms/pull/16140)) - [slashdoom](https://github.com/slashdoom)

#### Translation
* Support to Brazilian Portuguese ([#16209](https://github.com/librenms/librenms/pull/16209)) - [fabriciotm](https://github.com/fabriciotm)

#### Tests
* Add entity physical test data ([#16183](https://github.com/librenms/librenms/pull/16183)) - [murrant](https://github.com/murrant)
* Add support for snmpsim-lextudio ([#16161](https://github.com/librenms/librenms/pull/16161)) - [freddy36](https://github.com/freddy36)

#### Misc
* Change entPhysical table column defaults ([#16199](https://github.com/librenms/librenms/pull/16199)) - [murrant](https://github.com/murrant)

#### Internal Features
* SnmpQuery default improvements ([#16204](https://github.com/librenms/librenms/pull/16204)) - [murrant](https://github.com/murrant)

#### Mibs
* Radwin MIB update ([#16200](https://github.com/librenms/librenms/pull/16200)) - [murrant](https://github.com/murrant)
* Misc Junos MIB updates ([#16171](https://github.com/librenms/librenms/pull/16171)) - [murrant](https://github.com/murrant)

#### Dependencies
* Bump tecnickcom/tcpdf from 6.7.4 to 6.7.5 ([#16148](https://github.com/librenms/librenms/pull/16148)) - [dependabot](https://github.com/apps/dependabot)
* Bump ws from 8.17.0 to 8.17.1 ([#16143](https://github.com/librenms/librenms/pull/16143)) - [dependabot](https://github.com/apps/dependabot)


## 24.6.0
*(2024-06-16)*

A big thank you to the following 20 contributors this last month:

  - [murrant](https://github.com/murrant) (13)
  - [VVelox](https://github.com/VVelox) (7)
  - [PipoCanaja](https://github.com/PipoCanaja) (3)
  - [dependabot](https://github.com/apps/dependabot) (2)
  - [Npeca75](https://github.com/Npeca75) (2)
  - [ashwath129](https://github.com/ashwath129) (2)
  - [electrocret](https://github.com/electrocret) (2)
  - [scamp](https://github.com/scamp) (1)
  - [nicolasberens](https://github.com/nicolasberens) (1)
  - [sorano](https://github.com/sorano) (1)
  - [GonBlank](https://github.com/GonBlank) (1)
  - [jepke](https://github.com/jepke) (1)
  - [EinGlasVollKakao](https://github.com/EinGlasVollKakao) (1)
  - [Cougar](https://github.com/Cougar) (1)
  - [whitej46](https://github.com/whitej46) (1)
  - [cadirol](https://github.com/cadirol) (1)
  - [santiag0z](https://github.com/santiag0z) (1)
  - [rons4](https://github.com/rons4) (1)
  - [freddy36](https://github.com/freddy36) (1)
  - [cjsoftuk](https://github.com/cjsoftuk) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (18)
  - [Jellyfrog](https://github.com/Jellyfrog) (12)
  - [electrocret](https://github.com/electrocret) (9)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)

#### Feature
* ESRI ArcGIS geo map support ([#16059](https://github.com/librenms/librenms/pull/16059)) - [murrant](https://github.com/murrant)

#### Device
* Update Dell MIBs ([#16120](https://github.com/librenms/librenms/pull/16120)) - [murrant](https://github.com/murrant)
* Add TI-G102i (.46) and TI-PG1284i (.34) ([#16099](https://github.com/librenms/librenms/pull/16099)) - [nicolasberens](https://github.com/nicolasberens)
* Add "Bullet Camera" in Axis discovery. ([#16098](https://github.com/librenms/librenms/pull/16098)) - [sorano](https://github.com/sorano)
* [vlans] Add VLANs information to Huawei VRP os ([#16089](https://github.com/librenms/librenms/pull/16089)) - [Npeca75](https://github.com/Npeca75)
* Cisco Catalyst 1300 recognition ([#16080](https://github.com/librenms/librenms/pull/16080)) - [jepke](https://github.com/jepke)
* Fix Ruckus Unleashed product ID for OS detection ([#16067](https://github.com/librenms/librenms/pull/16067)) - [Cougar](https://github.com/Cougar)
* Fix error in riverbed ([#16066](https://github.com/librenms/librenms/pull/16066)) - [murrant](https://github.com/murrant)
* Update Hatteras DSLAM name ([#16054](https://github.com/librenms/librenms/pull/16054)) - [cadirol](https://github.com/cadirol)
* Add initial support for socomec-ups ([#16018](https://github.com/librenms/librenms/pull/16018)) - [Npeca75](https://github.com/Npeca75)
* Fix bdcom/pbn neighbour discovery ([#15935](https://github.com/librenms/librenms/pull/15935)) - [freddy36](https://github.com/freddy36)
* Add support for new sensors on Firebrick 9000 models. ([#15842](https://github.com/librenms/librenms/pull/15842)) - [cjsoftuk](https://github.com/cjsoftuk)

#### Webui
* Fix popup toast messages (Remove Flasher) ([#16090](https://github.com/librenms/librenms/pull/16090)) - [murrant](https://github.com/murrant)
* Handle $app_data['disks'] not being set for SMART app page display ([#16087](https://github.com/librenms/librenms/pull/16087)) - [VVelox](https://github.com/VVelox)
* Edit Current Map menu entry ([#16084](https://github.com/librenms/librenms/pull/16084)) - [murrant](https://github.com/murrant)
* Fix device summary widget alignment and dropdown color on dark theme ([#16083](https://github.com/librenms/librenms/pull/16083)) - [GonBlank](https://github.com/GonBlank)
* Fix duplicate maps in relationship ([#16081](https://github.com/librenms/librenms/pull/16081)) - [murrant](https://github.com/murrant)
* Manage Maps limit width ([#16055](https://github.com/librenms/librenms/pull/16055)) - [murrant](https://github.com/murrant)
* Widget hot refresh & worldmap cleanup ([#16053](https://github.com/librenms/librenms/pull/16053)) - [murrant](https://github.com/murrant)
* Align the buttons (Edit and Delete) to the right in Map Management ([#16052](https://github.com/librenms/librenms/pull/16052)) - [santiag0z](https://github.com/santiag0z)

#### Alerting
* AlertOps alert transport ([#16050](https://github.com/librenms/librenms/pull/16050)) - [ashwath129](https://github.com/ashwath129)
* SIGNL4 Alert Transport ([#16037](https://github.com/librenms/librenms/pull/16037)) - [rons4](https://github.com/rons4)

#### Applications
* Fix display of graphs on the multi-server app page for Mojo CAPE Submit ([#16094](https://github.com/librenms/librenms/pull/16094)) - [VVelox](https://github.com/VVelox)
* Two minor fixes for sagan ([#16082](https://github.com/librenms/librenms/pull/16082)) - [VVelox](https://github.com/VVelox)
* Fix path related issues for ss and systemd applications ([#16045](https://github.com/librenms/librenms/pull/16045)) - [VVelox](https://github.com/VVelox)
* Add Suricata 7 support to Suricata ([#16044](https://github.com/librenms/librenms/pull/16044)) - [VVelox](https://github.com/VVelox)

#### Api
* Return error when no device ports found ([#16043](https://github.com/librenms/librenms/pull/16043)) - [murrant](https://github.com/murrant)

#### Settings
* Add nfsen_base to config_definitions.json ([#16065](https://github.com/librenms/librenms/pull/16065)) - [whitej46](https://github.com/whitej46)
* Remove device_perf_purge ([#16057](https://github.com/librenms/librenms/pull/16057)) - [electrocret](https://github.com/electrocret)
* Remove enable_ports_poe ([#16056](https://github.com/librenms/librenms/pull/16056)) - [electrocret](https://github.com/electrocret)

#### Bug
* Bug - Sorting FDB table by devices ([#16116](https://github.com/librenms/librenms/pull/16116)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix typo in device edit page ([#16096](https://github.com/librenms/librenms/pull/16096)) - [murrant](https://github.com/murrant)
* Fix fping bulk ([#16085](https://github.com/librenms/librenms/pull/16085)) - [murrant](https://github.com/murrant)
* Fix duplication of processor entries & limit length of type ([#16075](https://github.com/librenms/librenms/pull/16075)) - [EinGlasVollKakao](https://github.com/EinGlasVollKakao)

#### Refactor
* Rename index_string to str_index_as_numeric ([#15916](https://github.com/librenms/librenms/pull/15916)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Documentation
* Note the suffix/prefix stuff for LDAP auth ([#16091](https://github.com/librenms/librenms/pull/16091)) - [VVelox](https://github.com/VVelox)
* Clean up SMART docs a bit ([#16086](https://github.com/librenms/librenms/pull/16086)) - [VVelox](https://github.com/VVelox)
* Update Transports.md to add documentation for AlertOps ([#16058](https://github.com/librenms/librenms/pull/16058)) - [ashwath129](https://github.com/ashwath129)

#### Misc
* Don't run poller validations when there are no devices ([#16088](https://github.com/librenms/librenms/pull/16088)) - [murrant](https://github.com/murrant)

#### Mibs
* Fix ECS4120 MIB, resolves #16093 ([#16101](https://github.com/librenms/librenms/pull/16101)) - [scamp](https://github.com/scamp)

#### Dependencies
* Bump braces from 3.0.2 to 3.0.3 ([#16105](https://github.com/librenms/librenms/pull/16105)) - [dependabot](https://github.com/apps/dependabot)
* Bump composer/composer from 2.7.1 to 2.7.7 ([#16104](https://github.com/librenms/librenms/pull/16104)) - [dependabot](https://github.com/apps/dependabot)


## 24.5.0
*(2024-05-19)*

A big thank you to the following 23 contributors this last month:

  - [murrant](https://github.com/murrant) (24)
  - [santiag0z](https://github.com/santiag0z) (5)
  - [eskyuu](https://github.com/eskyuu) (3)
  - [sogadm](https://github.com/sogadm) (2)
  - [Jarod2801](https://github.com/Jarod2801) (2)
  - [Pikamander2](https://github.com/Pikamander2) (1)
  - [scamp](https://github.com/scamp) (1)
  - [ottorei](https://github.com/ottorei) (1)
  - [whitej46](https://github.com/whitej46) (1)
  - [sonic45132](https://github.com/sonic45132) (1)
  - [fbouynot](https://github.com/fbouynot) (1)
  - [EinGlasVollKakao](https://github.com/EinGlasVollKakao) (1)
  - [h-barnhart](https://github.com/h-barnhart) (1)
  - [sarabveer](https://github.com/sarabveer) (1)
  - [netravnen](https://github.com/netravnen) (1)
  - [jthiltges](https://github.com/jthiltges) (1)
  - [hatboxen](https://github.com/hatboxen) (1)
  - [electrocret](https://github.com/electrocret) (1)
  - [washcroft](https://github.com/washcroft) (1)
  - [Npeca75](https://github.com/Npeca75) (1)
  - [paulierco](https://github.com/paulierco) (1)
  - [drshawnkwang](https://github.com/drshawnkwang) (1)
  - [systeembeheerder](https://github.com/systeembeheerder) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (22)
  - [Jellyfrog](https://github.com/Jellyfrog) (13)
  - [electrocret](https://github.com/electrocret) (3)
  - [ottorei](https://github.com/ottorei) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)

#### Feature
* Custom Maps: geo map and color backgrounds ([#16020](https://github.com/librenms/librenms/pull/16020)) - [murrant](https://github.com/murrant)
* Show custom maps in device overview ([#15985](https://github.com/librenms/librenms/pull/15985)) - [murrant](https://github.com/murrant)
* New Map Menu ([#15969](https://github.com/librenms/librenms/pull/15969)) - [murrant](https://github.com/murrant)
* Mysql PDO options to support SSL/TLS client communication ([#15832](https://github.com/librenms/librenms/pull/15832)) - [drshawnkwang](https://github.com/drshawnkwang)
* Snmpscan.py output errors and nodns ([#15673](https://github.com/librenms/librenms/pull/15673)) - [murrant](https://github.com/murrant)

#### Breaking Change
* Linux MegaRAID SAS fixes ([#15566](https://github.com/librenms/librenms/pull/15566)) - [eskyuu](https://github.com/eskyuu)

#### Device
* Use null coalescing on Panos.php ([#16019](https://github.com/librenms/librenms/pull/16019)) - [ottorei](https://github.com/ottorei)
* Improved powerwalker sensors ([#15999](https://github.com/librenms/librenms/pull/15999)) - [EinGlasVollKakao](https://github.com/EinGlasVollKakao)
* Added initial support for ULAF+ devices ([#15997](https://github.com/librenms/librenms/pull/15997)) - [Jarod2801](https://github.com/Jarod2801)
* Correct swapped SET and WHERE parameters in bgp-peers/dell-os10.inc.php ([#15983](https://github.com/librenms/librenms/pull/15983)) - [jthiltges](https://github.com/jthiltges)
* Added FibroLAN devices ([#15967](https://github.com/librenms/librenms/pull/15967)) - [Jarod2801](https://github.com/Jarod2801)
* New velocloud devices ([#15958](https://github.com/librenms/librenms/pull/15958)) - [paulierco](https://github.com/paulierco)

#### Webui
* Fix issue loading session preferences ([#16041](https://github.com/librenms/librenms/pull/16041)) - [murrant](https://github.com/murrant)
* Device location map zoom out when location N/A ([#16034](https://github.com/librenms/librenms/pull/16034)) - [murrant](https://github.com/murrant)
* Added read permission test to the custom map model ([#16030](https://github.com/librenms/librenms/pull/16030)) - [eskyuu](https://github.com/eskyuu)
* Do not allow the legend nodes to trigger the node edit modal ([#16026](https://github.com/librenms/librenms/pull/16026)) - [eskyuu](https://github.com/eskyuu)
* Mobile menu full height ([#16011](https://github.com/librenms/librenms/pull/16011)) - [murrant](https://github.com/murrant)
* Map Management: Show Groups ([#16005](https://github.com/librenms/librenms/pull/16005)) - [murrant](https://github.com/murrant)
* Change custom map editor icon ([#16004](https://github.com/librenms/librenms/pull/16004)) - [murrant](https://github.com/murrant)
* Custom Map: Show crosshairs when adding ([#15978](https://github.com/librenms/librenms/pull/15978)) - [murrant](https://github.com/murrant)
* On-demand map menu items ([#15971](https://github.com/librenms/librenms/pull/15971)) - [murrant](https://github.com/murrant)
* Custom Maps: make edit title clickable ([#15965](https://github.com/librenms/librenms/pull/15965)) - [murrant](https://github.com/murrant)
* [webui] sort ports in VLANs blade ([#15960](https://github.com/librenms/librenms/pull/15960)) - [Npeca75](https://github.com/Npeca75)

#### Graphs
* Fix icmp ping y-axis over 1000ms ([#16039](https://github.com/librenms/librenms/pull/16039)) - [murrant](https://github.com/murrant)
* Fix graph_type variable (svg / png) ([#15972](https://github.com/librenms/librenms/pull/15972)) - [washcroft](https://github.com/washcroft)

#### Snmp Traps
* SNMP Traps - Ciena AAA ([#15998](https://github.com/librenms/librenms/pull/15998)) - [h-barnhart](https://github.com/h-barnhart)

#### Bug
* Fix downtime in corner cases ([#16040](https://github.com/librenms/librenms/pull/16040)) - [murrant](https://github.com/murrant)
* Fix WirelessSensor incorrect model ([#16016](https://github.com/librenms/librenms/pull/16016)) - [whitej46](https://github.com/whitej46)
* Merge duplicate toBytes functions ([#15994](https://github.com/librenms/librenms/pull/15994)) - [murrant](https://github.com/murrant)
* Fix systemd graphs using wrong rrd filename variable ([#15988](https://github.com/librenms/librenms/pull/15988)) - [sarabveer](https://github.com/sarabveer)
* Rrd source does not work with rrdcached ([#15974](https://github.com/librenms/librenms/pull/15974)) - [murrant](https://github.com/murrant)
* Git ignore custom map images ([#15966](https://github.com/librenms/librenms/pull/15966)) - [murrant](https://github.com/murrant)
* Packet_loss macros quick fix ([#15961](https://github.com/librenms/librenms/pull/15961)) - [murrant](https://github.com/murrant)

#### Cleanup
* Fix incorrect number of seconds in a day ([#16042](https://github.com/librenms/librenms/pull/16042)) - [Pikamander2](https://github.com/Pikamander2)

#### Documentation
* [DOC] Update Customizing-the-Web-UI.md ([#16025](https://github.com/librenms/librenms/pull/16025)) - [santiag0z](https://github.com/santiag0z)
* [DOC] Install LibreNMS: add Icons ([#16017](https://github.com/librenms/librenms/pull/16017)) - [santiag0z](https://github.com/santiag0z)
* Set httpd_cache_t type to /opt/librenms/cache ([#16000](https://github.com/librenms/librenms/pull/16000)) - [fbouynot](https://github.com/fbouynot)
* Update to Material for MkDocs 8.3.9 -\> 9.5.20 ([#15996](https://github.com/librenms/librenms/pull/15996)) - [santiag0z](https://github.com/santiag0z)
* Update link to LibreNMS origin blog post ([#15981](https://github.com/librenms/librenms/pull/15981)) - [hatboxen](https://github.com/hatboxen)
* Remove poller_name from docs ([#15979](https://github.com/librenms/librenms/pull/15979)) - [electrocret](https://github.com/electrocret)
* Update packet_loss docs ([#15962](https://github.com/librenms/librenms/pull/15962)) - [murrant](https://github.com/murrant)
* Update Dispatcher-Service.md ([#15705](https://github.com/librenms/librenms/pull/15705)) - [systeembeheerder](https://github.com/systeembeheerder)

#### Translation
* Massive changes to the Chinese interface translation. ([#16009](https://github.com/librenms/librenms/pull/16009)) - [sogadm](https://github.com/sogadm)
* Chinese translation fixesChinese translation fixes ([#15991](https://github.com/librenms/librenms/pull/15991)) - [sogadm](https://github.com/sogadm)

#### Tests
* Always run tests ([#16024](https://github.com/librenms/librenms/pull/16024)) - [murrant](https://github.com/murrant)

#### Mibs
* Update MIB for Edge-Core ECS4120-Series ([#16023](https://github.com/librenms/librenms/pull/16023)) - [scamp](https://github.com/scamp)
* Update to latest revision ([#15984](https://github.com/librenms/librenms/pull/15984)) - [netravnen](https://github.com/netravnen)


## 24.4.0
*(2024-04-19)*

A big thank you to the following 18 contributors this last month:

  - [murrant](https://github.com/murrant) (8)
  - [PipoCanaja](https://github.com/PipoCanaja) (4)
  - [xorrkaz](https://github.com/xorrkaz) (2)
  - [moisseev](https://github.com/moisseev) (1)
  - [VVelox](https://github.com/VVelox) (1)
  - [Taarek](https://github.com/Taarek) (1)
  - [Melhuig](https://github.com/Melhuig) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [Lollbrant](https://github.com/Lollbrant) (1)
  - [HolgerHees](https://github.com/HolgerHees) (1)
  - [voileux](https://github.com/voileux) (1)
  - [hvanderheide](https://github.com/hvanderheide) (1)
  - [jasoncheng7115](https://github.com/jasoncheng7115) (1)
  - [h-barnhart](https://github.com/h-barnhart) (1)
  - [Jellyfrog](https://github.com/Jellyfrog) (1)
  - [CTV-2023](https://github.com/CTV-2023) (1)
  - [fbouynot](https://github.com/fbouynot) (1)
  - [OSIRIS-REx](https://github.com/OSIRIS-REx) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (13)
  - [murrant](https://github.com/murrant) (9)
  - [PipoCanaja](https://github.com/PipoCanaja) (8)
  - [electrocret](https://github.com/electrocret) (1)

#### Feature
* Improved Latency graph ([#15940](https://github.com/librenms/librenms/pull/15940)) - [murrant](https://github.com/murrant)

#### Security
* Fix Graph date selector ([#15956](https://github.com/librenms/librenms/pull/15956)) - [murrant](https://github.com/murrant)
* Fix JS injection in Service Templates ([#15954](https://github.com/librenms/librenms/pull/15954)) - [murrant](https://github.com/murrant)
* Fix SQL injection issues in packages search ([#15950](https://github.com/librenms/librenms/pull/15950)) - [murrant](https://github.com/murrant)
* Improve order validation in list_devices ([#15885](https://github.com/librenms/librenms/pull/15885)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Device
* ILO storage: fix malformed snmp data parsing ([#15931](https://github.com/librenms/librenms/pull/15931)) - [HolgerHees](https://github.com/HolgerHees)
* Add Fortigate HA state sensor definition ([#15924](https://github.com/librenms/librenms/pull/15924)) - [hvanderheide](https://github.com/hvanderheide)
* Devices - Ciena RLS 6500 ([#15909](https://github.com/librenms/librenms/pull/15909)) - [h-barnhart](https://github.com/h-barnhart)
* Cumulus mellanox discovery ([#15732](https://github.com/librenms/librenms/pull/15732)) - [fbouynot](https://github.com/fbouynot)
* Added support for new device OS Westermo WeOS ([#15674](https://github.com/librenms/librenms/pull/15674)) - [OSIRIS-REx](https://github.com/OSIRIS-REx)

#### Webui
* Fix null in services ([#15945](https://github.com/librenms/librenms/pull/15945)) - [murrant](https://github.com/murrant)

#### Alerting
* Pretty up Slack formatting. ([#15898](https://github.com/librenms/librenms/pull/15898)) - [xorrkaz](https://github.com/xorrkaz)

#### Graphs
* Fix typo ([#15952](https://github.com/librenms/librenms/pull/15952)) - [Taarek](https://github.com/Taarek)
* Fix graph selection when to/from missing from url ([#15946](https://github.com/librenms/librenms/pull/15946)) - [murrant](https://github.com/murrant)

#### Applications
* For gzip+base64 compressed json, don't call stripslashes ([#15953](https://github.com/librenms/librenms/pull/15953)) - [VVelox](https://github.com/VVelox)
* Fix PDNS recursor error ([#15942](https://github.com/librenms/librenms/pull/15942)) - [murrant](https://github.com/murrant)

#### Api
* Add type property to Device class to update it by API ([#15930](https://github.com/librenms/librenms/pull/15930)) - [voileux](https://github.com/voileux)
* Add support for a maintenance boolean in API results. ([#15904](https://github.com/librenms/librenms/pull/15904)) - [xorrkaz](https://github.com/xorrkaz)

#### Bug
* Skip rrd sources that do not exist ([#15959](https://github.com/librenms/librenms/pull/15959)) - [murrant](https://github.com/murrant)
* Bug - Cisco NAC key error ([#15934](https://github.com/librenms/librenms/pull/15934)) - [PipoCanaja](https://github.com/PipoCanaja)
* Bug - typo for request rate + sanity on numerical not_null values ([#15919](https://github.com/librenms/librenms/pull/15919)) - [PipoCanaja](https://github.com/PipoCanaja)
* Bug - vrp - fix signed-tinyint overloaded with disabled radios ([#15917](https://github.com/librenms/librenms/pull/15917)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Documentation
* Add missing p5-File-Slurp dependency ([#15955](https://github.com/librenms/librenms/pull/15955)) - [moisseev](https://github.com/moisseev)
* Fix "lnms config:set" command syntax ([#15949](https://github.com/librenms/librenms/pull/15949)) - [Melhuig](https://github.com/Melhuig)
* Graylog how to set up non-admin user ([#15938](https://github.com/librenms/librenms/pull/15938)) - [Lollbrant](https://github.com/Lollbrant)
* Documentation - opcache issue on Debian 12 ([#15870](https://github.com/librenms/librenms/pull/15870)) - [CTV-2023](https://github.com/CTV-2023)

#### Translation
* Fix wrong terminology ([#15920](https://github.com/librenms/librenms/pull/15920)) - [jasoncheng7115](https://github.com/jasoncheng7115)

#### Dependencies
* Bump tecnickcom/tcpdf from 6.6.5 to 6.7.4 ([#15948](https://github.com/librenms/librenms/pull/15948)) - [dependabot](https://github.com/apps/dependabot)


## 24.3.0
*(2024-04-01)*

A big thank you to the following 24 contributors this last month:

  - [rpardim](https://github.com/rpardim) (4)
  - [dependabot](https://github.com/apps/dependabot) (3)
  - [electrocret](https://github.com/electrocret) (3)
  - [bionicman](https://github.com/bionicman) (2)
  - [PipoCanaja](https://github.com/PipoCanaja) (2)
  - [eskyuu](https://github.com/eskyuu) (2)
  - [Walkablenormal](https://github.com/Walkablenormal) (2)
  - [bnerickson](https://github.com/bnerickson) (2)
  - [rudybroersma](https://github.com/rudybroersma) (2)
  - [d-k-7](https://github.com/d-k-7) (1)
  - [murrant](https://github.com/murrant) (1)
  - [czarnian](https://github.com/czarnian) (1)
  - [dmbokhan](https://github.com/dmbokhan) (1)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (1)
  - [msaringer](https://github.com/msaringer) (1)
  - [Didr](https://github.com/Didr) (1)
  - [vhuk](https://github.com/vhuk) (1)
  - [Jellyfrog](https://github.com/Jellyfrog) (1)
  - [KingDaveRa](https://github.com/KingDaveRa) (1)
  - [Npeca75](https://github.com/Npeca75) (1)
  - [dethmetaljeff](https://github.com/dethmetaljeff) (1)
  - [blknight88](https://github.com/blknight88) (1)
  - [gunkaaa](https://github.com/gunkaaa) (1)
  - [pjordanovic](https://github.com/pjordanovic) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (25)
  - [electrocret](https://github.com/electrocret) (7)
  - [PipoCanaja](https://github.com/PipoCanaja) (5)
  - [murrant](https://github.com/murrant) (2)
  - [laf](https://github.com/laf) (2)
  - [mpikzink](https://github.com/mpikzink) (1)
  - [VVelox](https://github.com/VVelox) (1)

#### Feature
* Support for InfluxDB V2 API ([#15861](https://github.com/librenms/librenms/pull/15861)) - [Walkablenormal](https://github.com/Walkablenormal)

#### Breaking Change
* Wireguard application graph cleanup and new wireguard interface/global metrics. ([#15847](https://github.com/librenms/librenms/pull/15847)) - [bnerickson](https://github.com/bnerickson)

#### Device
* Fix catos discovery ([#15915](https://github.com/librenms/librenms/pull/15915)) - [d-k-7](https://github.com/d-k-7)
* Add health sensors ([#15910](https://github.com/librenms/librenms/pull/15910)) - [murrant](https://github.com/murrant)
* Add support for Huawei YunShan OS ([#15903](https://github.com/librenms/librenms/pull/15903)) - [czarnian](https://github.com/czarnian)
* Add support for Ubiquiti Unifi USP-RPS device ([#15900](https://github.com/librenms/librenms/pull/15900)) - [bionicman](https://github.com/bionicman)
* Add support for Ubiquiti Unifi LTE devices. ([#15899](https://github.com/librenms/librenms/pull/15899)) - [bionicman](https://github.com/bionicman)
* Checkpoint Gaia PowerSupply state sensor ([#15882](https://github.com/librenms/librenms/pull/15882)) - [rpardim](https://github.com/rpardim)
* Add support for Cisco FTD 3105 ([#15881](https://github.com/librenms/librenms/pull/15881)) - [msaringer](https://github.com/msaringer)
* Fix for Checkpoint Gaia VPN state sensor ([#15878](https://github.com/librenms/librenms/pull/15878)) - [rpardim](https://github.com/rpardim)
* Support for Forcepoint NGFW 6.11 and later ([#15872](https://github.com/librenms/librenms/pull/15872)) - [vhuk](https://github.com/vhuk)
* A10 ACOS version, state and count sensors ([#15871](https://github.com/librenms/librenms/pull/15871)) - [rpardim](https://github.com/rpardim)
* F5 BIG-IP state and count sensors ([#15865](https://github.com/librenms/librenms/pull/15865)) - [rpardim](https://github.com/rpardim)
* Supermicro bmc updates ([#15862](https://github.com/librenms/librenms/pull/15862)) - [dethmetaljeff](https://github.com/dethmetaljeff)
* YAMLized version of previous PR for Ericsson SSR 80xx routers ([#15834](https://github.com/librenms/librenms/pull/15834)) - [rudybroersma](https://github.com/rudybroersma)
* Fix for FortiSwitch RPM/percentage fans ([#15829](https://github.com/librenms/librenms/pull/15829)) - [rudybroersma](https://github.com/rudybroersma)
* Move sentry3 current/voltage/power sensors to YAML ([#15715](https://github.com/librenms/librenms/pull/15715)) - [gunkaaa](https://github.com/gunkaaa)
* Device - EPSON DS-860 + Network Interface Unit DSBXNW1 ([#15420](https://github.com/librenms/librenms/pull/15420)) - [pjordanovic](https://github.com/pjordanovic)

#### Applications
* Systemd Application Code Cleanup and new Systemd Unit State Metrics. ([#15848](https://github.com/librenms/librenms/pull/15848)) - [bnerickson](https://github.com/bnerickson)

#### Discovery
* Bug - Fix OSes 'Junos' and 'Hirschmann' misuse of entPhysicalIndex ([#15886](https://github.com/librenms/librenms/pull/15886)) - [TheMysteriousX](https://github.com/TheMysteriousX)

#### Bug
* Fix Vrf Table ([#15912](https://github.com/librenms/librenms/pull/15912)) - [electrocret](https://github.com/electrocret)
* Fix for explicit timezone selection ([#15890](https://github.com/librenms/librenms/pull/15890)) - [eskyuu](https://github.com/eskyuu)
* Bug - fix extra fields in DB entry create/update ([#15883](https://github.com/librenms/librenms/pull/15883)) - [PipoCanaja](https://github.com/PipoCanaja)
* Remove config_bgp config check in bird2 app ([#15877](https://github.com/librenms/librenms/pull/15877)) - [Didr](https://github.com/Didr)
* Custommap label fixes ([#15875](https://github.com/librenms/librenms/pull/15875)) - [eskyuu](https://github.com/eskyuu)
* [ipv4] fix /32 addresses discovery ([#15863](https://github.com/librenms/librenms/pull/15863)) - [Npeca75](https://github.com/Npeca75)

#### Refactor
* Refactor - remove unused entPhysicalIndex_measured ([#15892](https://github.com/librenms/librenms/pull/15892)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Documentation
* Added additional lines for selinux config to work with RHEL8 ([#15864](https://github.com/librenms/librenms/pull/15864)) - [KingDaveRa](https://github.com/KingDaveRa)
* Fix @signedGraphTag documention ([#15853](https://github.com/librenms/librenms/pull/15853)) - [blknight88](https://github.com/blknight88)

#### Tests
* Bump Github Actions to Node.JS 20. ([#15873](https://github.com/librenms/librenms/pull/15873)) - [Walkablenormal](https://github.com/Walkablenormal)

#### Dependencies
* Bump express from 4.18.2 to 4.19.2 ([#15913](https://github.com/librenms/librenms/pull/15913)) - [dependabot](https://github.com/apps/dependabot)
* Bump webpack-dev-middleware from 5.3.3 to 5.3.4 ([#15907](https://github.com/librenms/librenms/pull/15907)) - [dependabot](https://github.com/apps/dependabot)
* Bump follow-redirects from 1.15.4 to 1.15.6 ([#15897](https://github.com/librenms/librenms/pull/15897)) - [dependabot](https://github.com/apps/dependabot)
* Update dependencies ([#15869](https://github.com/librenms/librenms/pull/15869)) - [Jellyfrog](https://github.com/Jellyfrog)


## 24.2.0
*(2024-02-27)*

A big thank you to the following 46 contributors this last month:

  - [rudybroersma](https://github.com/rudybroersma) (14)
  - [Npeca75](https://github.com/Npeca75) (10)
  - [eskyuu](https://github.com/eskyuu) (6)
  - [electrocret](https://github.com/electrocret) (5)
  - [PipoCanaja](https://github.com/PipoCanaja) (5)
  - [Jellyfrog](https://github.com/Jellyfrog) (5)
  - [vhuk](https://github.com/vhuk) (5)
  - [murrant](https://github.com/murrant) (5)
  - [bnerickson](https://github.com/bnerickson) (3)
  - [fbouynot](https://github.com/fbouynot) (3)
  - [FlyveHest](https://github.com/FlyveHest) (2)
  - [nickhilliard](https://github.com/nickhilliard) (2)
  - [dependabot](https://github.com/apps/dependabot) (2)
  - [richard-ririe](https://github.com/richard-ririe) (2)
  - [laf](https://github.com/laf) (2)
  - [SourceDoctor](https://github.com/SourceDoctor) (2)
  - [VVelox](https://github.com/VVelox) (2)
  - [VoipTelCH](https://github.com/VoipTelCH) (1)
  - [fabriciotm](https://github.com/fabriciotm) (1)
  - [dirkx](https://github.com/dirkx) (1)
  - [swerveshot](https://github.com/swerveshot) (1)
  - [jmesserli](https://github.com/jmesserli) (1)
  - [lrizzi](https://github.com/lrizzi) (1)
  - [Personwho](https://github.com/Personwho) (1)
  - [OSIRIS-REx](https://github.com/OSIRIS-REx) (1)
  - [xorrkaz](https://github.com/xorrkaz) (1)
  - [jcostom](https://github.com/jcostom) (1)
  - [tevkar](https://github.com/tevkar) (1)
  - [descilla](https://github.com/descilla) (1)
  - [arjitc](https://github.com/arjitc) (1)
  - [My-Random-Thoughts](https://github.com/My-Random-Thoughts) (1)
  - [dlangille](https://github.com/dlangille) (1)
  - [blknight88](https://github.com/blknight88) (1)
  - [z0d1ac-RU](https://github.com/z0d1ac-RU) (1)
  - [lferrerfmv](https://github.com/lferrerfmv) (1)
  - [gil-obradors](https://github.com/gil-obradors) (1)
  - [gunkaaa](https://github.com/gunkaaa) (1)
  - [TvL2386](https://github.com/TvL2386) (1)
  - [santiag0z](https://github.com/santiag0z) (1)
  - [EinGlasVollKakao](https://github.com/EinGlasVollKakao) (1)
  - [kakohegyi](https://github.com/kakohegyi) (1)
  - [i4networks](https://github.com/i4networks) (1)
  - [Bierchermuesli](https://github.com/Bierchermuesli) (1)
  - [mhamzak008](https://github.com/mhamzak008) (1)
  - [nicklockhart-fullfibre](https://github.com/nicklockhart-fullfibre) (1)
  - [LoveSkylark](https://github.com/LoveSkylark) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [PipoCanaja](https://github.com/PipoCanaja) (35)
  - [Jellyfrog](https://github.com/Jellyfrog) (30)
  - [electrocret](https://github.com/electrocret) (26)
  - [laf](https://github.com/laf) (21)
  - [murrant](https://github.com/murrant) (11)
  - [mpikzink](https://github.com/mpikzink) (1)
  - [rudybroersma](https://github.com/rudybroersma) (1)
  - [ottorei](https://github.com/ottorei) (1)
  - [vhuk](https://github.com/vhuk) (1)

#### Feature
* Additional custom map features ([#15806](https://github.com/librenms/librenms/pull/15806)) - [eskyuu](https://github.com/eskyuu)
* Add/Remove devices from static devicegroups ([#15775](https://github.com/librenms/librenms/pull/15775)) - [richard-ririe](https://github.com/richard-ririe)
* Option to ignore device status ([#15697](https://github.com/librenms/librenms/pull/15697)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add functionality for custom maps (weathermaps) ([#15633](https://github.com/librenms/librenms/pull/15633)) - [eskyuu](https://github.com/eskyuu)
* Alert Rule Editor: new notes field & SQL field improove ([#15631](https://github.com/librenms/librenms/pull/15631)) - [Bierchermuesli](https://github.com/Bierchermuesli)
* NAC - Improve search in WebUI - Keep Historical data ([#15629](https://github.com/librenms/librenms/pull/15629)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Security
* Fix XSS in default example plugin ([#15711](https://github.com/librenms/librenms/pull/15711)) - [murrant](https://github.com/murrant)

#### Device
* Updated SLA poller for Cisco Nexus 9000 ([#15855](https://github.com/librenms/librenms/pull/15855)) - [FlyveHest](https://github.com/FlyveHest)
* Update geist-watchdog.yaml ([#15851](https://github.com/librenms/librenms/pull/15851)) - [fabriciotm](https://github.com/fabriciotm)
* Correctly identify FS Datacenter Switch N8560-48BC ([#15837](https://github.com/librenms/librenms/pull/15837)) - [rudybroersma](https://github.com/rudybroersma)
* Konica printers additional counters ([#15826](https://github.com/librenms/librenms/pull/15826)) - [Npeca75](https://github.com/Npeca75)
* Add HSRP state sensors for Cisco IOSXE on L3 switches ([#15823](https://github.com/librenms/librenms/pull/15823)) - [rudybroersma](https://github.com/rudybroersma)
* Add HSRP Sensor support for IOSXR ([#15821](https://github.com/librenms/librenms/pull/15821)) - [electrocret](https://github.com/electrocret)
* Add support for Cisco IE1000 ([#15820](https://github.com/librenms/librenms/pull/15820)) - [rudybroersma](https://github.com/rudybroersma)
* Initial support for Eltex mes24xx ([#15816](https://github.com/librenms/librenms/pull/15816)) - [Npeca75](https://github.com/Npeca75)
* Add support for Cadant E6000 ([#15813](https://github.com/librenms/librenms/pull/15813)) - [nickhilliard](https://github.com/nickhilliard)
* Add LRT-C / LCM-B / LRS-D / LCM-B modules to Luminato model ([#15812](https://github.com/librenms/librenms/pull/15812)) - [nickhilliard](https://github.com/nickhilliard)
* Add HSRP state sensors for Cisco IOS on L3 switches ([#15809](https://github.com/librenms/librenms/pull/15809)) - [rudybroersma](https://github.com/rudybroersma)
* [rfc1628] Add UPS Test (battery test) status sensor ([#15802](https://github.com/librenms/librenms/pull/15802)) - [Npeca75](https://github.com/Npeca75)
* Add build 22631 as Windows 11 23H2 ([#15800](https://github.com/librenms/librenms/pull/15800)) - [vhuk](https://github.com/vhuk)
* Zyxel ZynOS PoE Budget sensor support ([#15798](https://github.com/librenms/librenms/pull/15798)) - [rudybroersma](https://github.com/rudybroersma)
* Add Procurve NAC support ([#15794](https://github.com/librenms/librenms/pull/15794)) - [vhuk](https://github.com/vhuk)
* Add ArubaOS-CX VSF state sensor support ([#15793](https://github.com/librenms/librenms/pull/15793)) - [rudybroersma](https://github.com/rudybroersma)
* Support for new os/devices, CTS ([#15790](https://github.com/librenms/librenms/pull/15790)) - [OSIRIS-REx](https://github.com/OSIRIS-REx)
* Support for new Lancom devices ([#15779](https://github.com/librenms/librenms/pull/15779)) - [rudybroersma](https://github.com/rudybroersma)
* Add NAC support for Powerconnect ([#15778](https://github.com/librenms/librenms/pull/15778)) - [vhuk](https://github.com/vhuk)
* Detect UniFi U7 APs as UniFi AP type ([#15776](https://github.com/librenms/librenms/pull/15776)) - [jcostom](https://github.com/jcostom)
* FS.com S5810 Discovery fix ([#15765](https://github.com/librenms/librenms/pull/15765)) - [rudybroersma](https://github.com/rudybroersma)
* Device - webpower smart II snmp UPS card ([#15764](https://github.com/librenms/librenms/pull/15764)) - [Npeca75](https://github.com/Npeca75)
* Support for temp sensors - WUT Thermometers - W57605 and W57614 ([#15757](https://github.com/librenms/librenms/pull/15757)) - [rudybroersma](https://github.com/rudybroersma)
* Initial support for Supermicro BMC ([#15750](https://github.com/librenms/librenms/pull/15750)) - [Npeca75](https://github.com/Npeca75)
* ArubaOS-CX PSU state sensor support & OS and serial detection ([#15738](https://github.com/librenms/librenms/pull/15738)) - [rudybroersma](https://github.com/rudybroersma)
* Add FortiSwitch PSU state sensor support ([#15735](https://github.com/librenms/librenms/pull/15735)) - [rudybroersma](https://github.com/rudybroersma)
* Added support for Dlink dgs-1250-28x ([#15734](https://github.com/librenms/librenms/pull/15734)) - [Npeca75](https://github.com/Npeca75)
* Add FortiGate DHCP Scope usage percentage sensors ([#15727](https://github.com/librenms/librenms/pull/15727)) - [rudybroersma](https://github.com/rudybroersma)
* Added MES 2348B ([#15725](https://github.com/librenms/librenms/pull/15725)) - [z0d1ac-RU](https://github.com/z0d1ac-RU)
* Add FortiGate license status sensors ([#15722](https://github.com/librenms/librenms/pull/15722)) - [rudybroersma](https://github.com/rudybroersma)
* Handle icmpjitter SLA parsing for iosxe ([#15707](https://github.com/librenms/librenms/pull/15707)) - [FlyveHest](https://github.com/FlyveHest)
* Zyxel Wireless Controller OS ( Zyxel NXC series ) ([#15694](https://github.com/librenms/librenms/pull/15694)) - [kakohegyi](https://github.com/kakohegyi)
* Device - fix Counter64 octets value in 32bit column bgpPeerInTotalMessages ([#15621](https://github.com/librenms/librenms/pull/15621)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix tp-link jetstream FDB discovery ([#14321](https://github.com/librenms/librenms/pull/14321)) - [Npeca75](https://github.com/Npeca75)

#### Webui
* Disable Page Refresh on Oxidized Tools Page ([#15831](https://github.com/librenms/librenms/pull/15831)) - [electrocret](https://github.com/electrocret)
* Modify the date selector to use the session timezone ([#15783](https://github.com/librenms/librenms/pull/15783)) - [eskyuu](https://github.com/eskyuu)
* Switch bill_notes input to textarea ([#15749](https://github.com/librenms/librenms/pull/15749)) - [arjitc](https://github.com/arjitc)
* Sort smart app disks by label ([#15686](https://github.com/librenms/librenms/pull/15686)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Alerting
* Add support for Webex max message length. ([#15789](https://github.com/librenms/librenms/pull/15789)) - [xorrkaz](https://github.com/xorrkaz)
* Rename JiraServiceManagement.php to Jiraservicemanagement.php ([#15717](https://github.com/librenms/librenms/pull/15717)) - [gil-obradors](https://github.com/gil-obradors)
* Add JiraServiceManagement Transport ([#15593](https://github.com/librenms/librenms/pull/15593)) - [mhamzak008](https://github.com/mhamzak008)
* Transport - Jira transport rewrite ([#15561](https://github.com/librenms/librenms/pull/15561)) - [LoveSkylark](https://github.com/LoveSkylark)

#### Graphs
* Fixed graphs for icmp service showing PL 4 times ([#15856](https://github.com/librenms/librenms/pull/15856)) - [VoipTelCH](https://github.com/VoipTelCH)
* Socket Statistic Application cleanup and application page graph fixes. ([#15845](https://github.com/librenms/librenms/pull/15845)) - [bnerickson](https://github.com/bnerickson)

#### Applications
* Deliver output for a specific memcached instance ([#15759](https://github.com/librenms/librenms/pull/15759)) - [tevkar](https://github.com/tevkar)
* Update nvidia.inc.php ([#15756](https://github.com/librenms/librenms/pull/15756)) - [descilla](https://github.com/descilla)
* Add BorgBackup monitoring support ([#15591](https://github.com/librenms/librenms/pull/15591)) - [VVelox](https://github.com/VVelox)
* Add dhcp-stats tests and update for v3 of the extend ([#15378](https://github.com/librenms/librenms/pull/15378)) - [VVelox](https://github.com/VVelox)

#### Billing
* Updated bill_data table, alter indexes and add new column ([#15751](https://github.com/librenms/librenms/pull/15751)) - [laf](https://github.com/laf)

#### Api
* Add API endpoints to update and delete Device Groups ([#15774](https://github.com/librenms/librenms/pull/15774)) - [richard-ririe](https://github.com/richard-ririe)
* Add port description API endpoints and documentation ([#15578](https://github.com/librenms/librenms/pull/15578)) - [nicklockhart-fullfibre](https://github.com/nicklockhart-fullfibre)

#### Settings
* Fix twofactor default value ([#15772](https://github.com/librenms/librenms/pull/15772)) - [murrant](https://github.com/murrant)
* Add isis module to os schema ([#15710](https://github.com/librenms/librenms/pull/15710)) - [murrant](https://github.com/murrant)

#### Discovery
* Fall back to IPV6-MIB IPv6 address discovery if IP-MIB IPv6 address discovery doesn't return any valid addresses ([#15714](https://github.com/librenms/librenms/pull/15714)) - [gunkaaa](https://github.com/gunkaaa)

#### Oxidized
* Add PollerGroup as an option for OxidizedMap ([#15696](https://github.com/librenms/librenms/pull/15696)) - [electrocret](https://github.com/electrocret)

#### Bug
* Update Port Real Time Graph error ([#15846](https://github.com/librenms/librenms/pull/15846)) - [electrocret](https://github.com/electrocret)
* [bugfix] Fix json-app-tool.php to work with Oid class. ([#15844](https://github.com/librenms/librenms/pull/15844)) - [bnerickson](https://github.com/bnerickson)
* Fix for linkDown/linkUp ifOperStatus ([#15835](https://github.com/librenms/librenms/pull/15835)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix "Tempurature" Typo ([#15811](https://github.com/librenms/librenms/pull/15811)) - [lrizzi](https://github.com/lrizzi)
* Bug fixes for the custom maps ([#15810](https://github.com/librenms/librenms/pull/15810)) - [eskyuu](https://github.com/eskyuu)
* Remove dumpRawSql() function in AlertUtil.php ([#15803](https://github.com/librenms/librenms/pull/15803)) - [Personwho](https://github.com/Personwho)
* Make all image URLs absolute and fix path for viewer ([#15788](https://github.com/librenms/librenms/pull/15788)) - [eskyuu](https://github.com/eskyuu)
* Prevent ansi colors in key:generate output ([#15773](https://github.com/librenms/librenms/pull/15773)) - [Jellyfrog](https://github.com/Jellyfrog)
* VRP - avoid emptying bgpPeers description at discovery when manually set ([#15713](https://github.com/librenms/librenms/pull/15713)) - [PipoCanaja](https://github.com/PipoCanaja)
* OSPF instances and missing mandatory fields fix attempt ([#15712](https://github.com/librenms/librenms/pull/15712)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fixed typo in misc/alert_rules.json with regards to "Space on ..." alerts ([#15708](https://github.com/librenms/librenms/pull/15708)) - [TvL2386](https://github.com/TvL2386)
* Don't escape leaflet tile url in location edit map ([#15695](https://github.com/librenms/librenms/pull/15695)) - [EinGlasVollKakao](https://github.com/EinGlasVollKakao)
* Show error if "Check Type" field is empty when creating new service template ([#15685](https://github.com/librenms/librenms/pull/15685)) - [vhuk](https://github.com/vhuk)

#### Refactor
* Rewrite ups-nut discovery to SnmpQuery:: ([#15850](https://github.com/librenms/librenms/pull/15850)) - [Npeca75](https://github.com/Npeca75)
* Rewrite lmsensors discovery to SnmpQuery:: ([#15833](https://github.com/librenms/librenms/pull/15833)) - [Npeca75](https://github.com/Npeca75)
* Rewrite ipv4 address discovery to Eloquent ([#15830](https://github.com/librenms/librenms/pull/15830)) - [Npeca75](https://github.com/Npeca75)

#### Documentation
* Applications.md formatting update for better readability. ([#15849](https://github.com/librenms/librenms/pull/15849)) - [bnerickson](https://github.com/bnerickson)
* Update Images.md ([#15824](https://github.com/librenms/librenms/pull/15824)) - [swerveshot](https://github.com/swerveshot)
* More precise OAuth group/role claim information ([#15817](https://github.com/librenms/librenms/pull/15817)) - [jmesserli](https://github.com/jmesserli)
* Add selinux open directory permission for rrdcached in RRDCached.md ([#15755](https://github.com/librenms/librenms/pull/15755)) - [fbouynot](https://github.com/fbouynot)
* Missing dir read permission in sepolicy in RRDCached.md ([#15754](https://github.com/librenms/librenms/pull/15754)) - [fbouynot](https://github.com/fbouynot)
* Update SQL override section after switch to SQL strict mode ([#15736](https://github.com/librenms/librenms/pull/15736)) - [blknight88](https://github.com/blknight88)
* Add CentOS option to SMART dependency install ([#15704](https://github.com/librenms/librenms/pull/15704)) - [fbouynot](https://github.com/fbouynot)

#### Misc
* Add kelvin to celcius conversion ([#15836](https://github.com/librenms/librenms/pull/15836)) - [dirkx](https://github.com/dirkx)

#### Mibs
* Update watchguard MIBs ([#15719](https://github.com/librenms/librenms/pull/15719)) - [lferrerfmv](https://github.com/lferrerfmv)

#### Dependencies
* Bump composer/composer from 2.6.6 to 2.7.0 ([#15808](https://github.com/librenms/librenms/pull/15808)) - [dependabot](https://github.com/apps/dependabot)
* Update PHP dependencies ([#15737](https://github.com/librenms/librenms/pull/15737)) - [murrant](https://github.com/murrant)
* Bump follow-redirects from 1.15.3 to 1.15.4 ([#15724](https://github.com/librenms/librenms/pull/15724)) - [dependabot](https://github.com/apps/dependabot)


## 24.1.0
*(2024-01-07)*

A big thank you to the following 37 contributors this last month:

  - [PipoCanaja](https://github.com/PipoCanaja) (12)
  - [murrant](https://github.com/murrant) (7)
  - [laf](https://github.com/laf) (5)
  - [electrocret](https://github.com/electrocret) (3)
  - [peejaychilds](https://github.com/peejaychilds) (3)
  - [Jellyfrog](https://github.com/Jellyfrog) (2)
  - [vhuk](https://github.com/vhuk) (2)
  - [MittWillson](https://github.com/MittWillson) (2)
  - [Bierchermuesli](https://github.com/Bierchermuesli) (2)
  - [netravnen](https://github.com/netravnen) (1)
  - [iliessens](https://github.com/iliessens) (1)
  - [sarcastic6](https://github.com/sarcastic6) (1)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)
  - [altf4arnold](https://github.com/altf4arnold) (1)
  - [robje](https://github.com/robje) (1)
  - [rudybroersma](https://github.com/rudybroersma) (1)
  - [mtentilucci](https://github.com/mtentilucci) (1)
  - [tuxgasy](https://github.com/tuxgasy) (1)
  - [craig-nokia](https://github.com/craig-nokia) (1)
  - [brianegge](https://github.com/brianegge) (1)
  - [amyjohn000](https://github.com/amyjohn000) (1)
  - [VirTechSystems](https://github.com/VirTechSystems) (1)
  - [atj](https://github.com/atj) (1)
  - [lhwolfarth](https://github.com/lhwolfarth) (1)
  - [bonzo81](https://github.com/bonzo81) (1)
  - [Sweeny42](https://github.com/Sweeny42) (1)
  - [jduke-halls](https://github.com/jduke-halls) (1)
  - [pjordanovic](https://github.com/pjordanovic) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (1)
  - [swiftnode-linden](https://github.com/swiftnode-linden) (1)
  - [cguillaumie](https://github.com/cguillaumie) (1)
  - [luc-ass](https://github.com/luc-ass) (1)
  - [VVelox](https://github.com/VVelox) (1)
  - [Leo-FJ](https://github.com/Leo-FJ) (1)
  - [MaxPecc](https://github.com/MaxPecc) (1)
  - [jerji](https://github.com/jerji) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (20)
  - [murrant](https://github.com/murrant) (16)
  - [PipoCanaja](https://github.com/PipoCanaja) (15)
  - [electrocret](https://github.com/electrocret) (12)
  - [craig-nokia](https://github.com/craig-nokia) (1)
  - [ottorei](https://github.com/ottorei) (1)

#### Device
* Ignore nameless health sensors for Fortigate ([#15678](https://github.com/librenms/librenms/pull/15678)) - [iliessens](https://github.com/iliessens)
* Add support for RoomAlert 32S device ([#15676](https://github.com/librenms/librenms/pull/15676)) - [sarcastic6](https://github.com/sarcastic6)
* Device - Add Cisco REP Segment state sensor ([#15666](https://github.com/librenms/librenms/pull/15666)) - [rudybroersma](https://github.com/rudybroersma)
* Added better support for some HiveOS Wireless devices ([#15661](https://github.com/librenms/librenms/pull/15661)) - [laf](https://github.com/laf)
* Fix HPE iLO CPU Status Sensor Description ([#15660](https://github.com/librenms/librenms/pull/15660)) - [mtentilucci](https://github.com/mtentilucci)
* Fix OcNOS detection for recent firmware versions ([#15642](https://github.com/librenms/librenms/pull/15642)) - [murrant](https://github.com/murrant)
* Add support for Fortinet FortiAPs ([#15641](https://github.com/librenms/librenms/pull/15641)) - [atj](https://github.com/atj)
* Fixing memory scale for datacom-dmos devices ([#15640](https://github.com/librenms/librenms/pull/15640)) - [lhwolfarth](https://github.com/lhwolfarth)
* Bug - Fix Cisco NTP values ([#15639](https://github.com/librenms/librenms/pull/15639)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add support for Forcepoint NGFW 6.10 and older ([#15632](https://github.com/librenms/librenms/pull/15632)) - [vhuk](https://github.com/vhuk)
* Bug - timos MPLS - more poller fixes ([#15624](https://github.com/librenms/librenms/pull/15624)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add memory readings for Draytek OS ([#15618](https://github.com/librenms/librenms/pull/15618)) - [Sweeny42](https://github.com/Sweeny42)
* Updated support for HiveOS Wireless newer models ([#15610](https://github.com/librenms/librenms/pull/15610)) - [laf](https://github.com/laf)
* Add HPE iLO 6 to discovery ([#15607](https://github.com/librenms/librenms/pull/15607)) - [jduke-halls](https://github.com/jduke-halls)
* Incorrect discovery APC Smart-UPS RT 3000 XL 4.1 ( APC Web/SNMP Management Card (AP9619 MB:v4.1.1 PF:v3.9.4) as multi-phase ups ([#15602](https://github.com/librenms/librenms/pull/15602)) - [pjordanovic](https://github.com/pjordanovic)
* Device - McAfee Web Gateway -\> SkyHigh Web Gateway ([#15596](https://github.com/librenms/librenms/pull/15596)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add and extend support for Hirshmann devices ([#15588](https://github.com/librenms/librenms/pull/15588)) - [cguillaumie](https://github.com/cguillaumie)
* Updated regex for HWG STE2 r2 to better detect hardware and software version ([#15573](https://github.com/librenms/librenms/pull/15573)) - [luc-ass](https://github.com/luc-ass)
* Update entity-sensor.inc.php for xos' os ([#15552](https://github.com/librenms/librenms/pull/15552)) - [Leo-FJ](https://github.com/Leo-FJ)
* Added support of new OS for NTP/PTP systems: Meinberg OS, Safran (Orolia), Oscilloquartz (Adva) ([#15453](https://github.com/librenms/librenms/pull/15453)) - [MaxPecc](https://github.com/MaxPecc)
* Zhone health ([#15276](https://github.com/librenms/librenms/pull/15276)) - [jerji](https://github.com/jerji)
* Fix wrong ASN discovery on non-BGP Devices ([#14948](https://github.com/librenms/librenms/pull/14948)) - [Bierchermuesli](https://github.com/Bierchermuesli)

#### Webui
* Clarify In/Out on Ports table. ([#15680](https://github.com/librenms/librenms/pull/15680)) - [electrocret](https://github.com/electrocret)
* WebUI - Filter FDB and ARP tabs in port page if empty ([#15653](https://github.com/librenms/librenms/pull/15653)) - [PipoCanaja](https://github.com/PipoCanaja)
* Update Pushover.php ([#15652](https://github.com/librenms/librenms/pull/15652)) - [brianegge](https://github.com/brianegge)
* Mark old alert email settings as deprecated ([#15650](https://github.com/librenms/librenms/pull/15650)) - [murrant](https://github.com/murrant)
* Add bad port settings to webui ([#15649](https://github.com/librenms/librenms/pull/15649)) - [murrant](https://github.com/murrant)
* Bug - FDB Table - allow empty searchby as well ([#15626](https://github.com/librenms/librenms/pull/15626)) - [PipoCanaja](https://github.com/PipoCanaja)
* Update alertlog query to be more efficient ([#15622](https://github.com/librenms/librenms/pull/15622)) - [laf](https://github.com/laf)
* Add vendor to searchby rules function ([#15619](https://github.com/librenms/librenms/pull/15619)) - [bonzo81](https://github.com/bonzo81)
* Fix grabled characters when oid already UTF-8 ([#15615](https://github.com/librenms/librenms/pull/15615)) - [MittWillson](https://github.com/MittWillson)

#### Graphs
* Change default graph image to SVG ([#15586](https://github.com/librenms/librenms/pull/15586)) - [electrocret](https://github.com/electrocret)

#### Api
* API add_device: Add ping_ping fallback option ([#15637](https://github.com/librenms/librenms/pull/15637)) - [murrant](https://github.com/murrant)
* More filter options for the BGP peer API endpoint ([#15599](https://github.com/librenms/librenms/pull/15599)) - [Bierchermuesli](https://github.com/Bierchermuesli)

#### Discovery
* Set array before use to stop discovery erroring ([#15604](https://github.com/librenms/librenms/pull/15604)) - [laf](https://github.com/laf)

#### Authentication
* Add support for Okta Group claims to set Roles ([#15592](https://github.com/librenms/librenms/pull/15592)) - [peejaychilds](https://github.com/peejaychilds)
* Output Roles in auth_test script ([#15587](https://github.com/librenms/librenms/pull/15587)) - [peejaychilds](https://github.com/peejaychilds)

#### Bug
* Fix Rancid error messages ([#15683](https://github.com/librenms/librenms/pull/15683)) - [vhuk](https://github.com/vhuk)
* Fix smart application parsing ([#15672](https://github.com/librenms/librenms/pull/15672)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix pagination in alert rules page ([#15659](https://github.com/librenms/librenms/pull/15659)) - [tuxgasy](https://github.com/tuxgasy)
* Bug - "null" checks for SAR 7705 release 8.X ([#15657](https://github.com/librenms/librenms/pull/15657)) - [craig-nokia](https://github.com/craig-nokia)
* Bug - missing "use" statement in NTP Cisco ([#15656](https://github.com/librenms/librenms/pull/15656)) - [PipoCanaja](https://github.com/PipoCanaja)
* Bug - TPLink - fix null exception for LLDP discovery WIP ([#15628](https://github.com/librenms/librenms/pull/15628)) - [PipoCanaja](https://github.com/PipoCanaja)
* Bug - bgp-peers error in Timos -\> dbFacile cleanup ([#15620](https://github.com/librenms/librenms/pull/15620)) - [PipoCanaja](https://github.com/PipoCanaja)
* Bug - ADSL ifIndex to port error not handled ([#15617](https://github.com/librenms/librenms/pull/15617)) - [PipoCanaja](https://github.com/PipoCanaja)
* Bug - XDSL adslAtucCurrOutputPwr exception (Cisco CSCvj53634) ([#15614](https://github.com/librenms/librenms/pull/15614)) - [PipoCanaja](https://github.com/PipoCanaja)
* Bug - null checks in Nokia MPLS polling ([#15613](https://github.com/librenms/librenms/pull/15613)) - [PipoCanaja](https://github.com/PipoCanaja)
* Bug - Nokia discovery protocols ([#15606](https://github.com/librenms/librenms/pull/15606)) - [PipoCanaja](https://github.com/PipoCanaja)
* Make vminfo.vmwVmGuestOS wider ([#15595](https://github.com/librenms/librenms/pull/15595)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Fixed state flag causing sql issues in test-template.php ([#15589](https://github.com/librenms/librenms/pull/15589)) - [laf](https://github.com/laf)

#### Documentation
* Add traceroute to the installed packages doc ([#15645](https://github.com/librenms/librenms/pull/15645)) - [VirTechSystems](https://github.com/VirTechSystems)
* Fix documentation formatting ([#15635](https://github.com/librenms/librenms/pull/15635)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix formatting in OAuth-SAML.md ([#15616](https://github.com/librenms/librenms/pull/15616)) - [peejaychilds](https://github.com/peejaychilds)
* Update Debian 12 Installation Instructions. ([#15590](https://github.com/librenms/librenms/pull/15590)) - [swiftnode-linden](https://github.com/swiftnode-linden)
* Add Debian 12 to install docs ([#15559](https://github.com/librenms/librenms/pull/15559)) - [VVelox](https://github.com/VVelox)

#### Misc
* Updating the logo to higher resolution one ([#15669](https://github.com/librenms/librenms/pull/15669)) - [altf4arnold](https://github.com/altf4arnold)
* Update the type of nummonbssid column in the access_points table ([#15647](https://github.com/librenms/librenms/pull/15647)) - [amyjohn000](https://github.com/amyjohn000)
* Fix device format missing display field ([#15623](https://github.com/librenms/librenms/pull/15623)) - [MittWillson](https://github.com/MittWillson)
* Link Model ([#15611](https://github.com/librenms/librenms/pull/15611)) - [murrant](https://github.com/murrant)
* Add space to Oxidized error msg ([#15603](https://github.com/librenms/librenms/pull/15603)) - [electrocret](https://github.com/electrocret)

#### Internal Features
* New utility Number::constrainInteger() ([#15663](https://github.com/librenms/librenms/pull/15663)) - [murrant](https://github.com/murrant)

#### Mibs
* Update MIKROTIK-MIB ([#15690](https://github.com/librenms/librenms/pull/15690)) - [netravnen](https://github.com/netravnen)

#### Dependencies
* Update javascript dependencies ([#15651](https://github.com/librenms/librenms/pull/15651)) - [murrant](https://github.com/murrant)
* Bump phpseclib/phpseclib from 3.0.21 to 3.0.34 ([#15600](https://github.com/librenms/librenms/pull/15600)) - [dependabot](https://github.com/apps/dependabot)

---

##[Old Changelogs](https://github.com/librenms/librenms/tree/master/doc/General/Changelogs)
