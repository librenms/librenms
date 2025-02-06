## 25.1.0
*(2025-01-16)*

A big thank you to the following 32 contributors this last month:

  - [murrant](https://github.com/murrant) (14)
  - [mpikzink](https://github.com/mpikzink) (12)
  - [PipoCanaja](https://github.com/PipoCanaja) (7)
  - [laf](https://github.com/laf) (6)
  - [jasoncheng7115](https://github.com/jasoncheng7115) (5)
  - [adamsweet](https://github.com/adamsweet) (3)
  - [takyanagida](https://github.com/takyanagida) (2)
  - [btriller](https://github.com/btriller) (1)
  - [slashdoom](https://github.com/slashdoom) (1)
  - [jakejakejakejakejakejake](https://github.com/jakejakejakejakejakejake) (1)
  - [ZPrimed](https://github.com/ZPrimed) (1)
  - [dko-strd](https://github.com/dko-strd) (1)
  - [nickhilliard](https://github.com/nickhilliard) (1)
  - [fbouynot](https://github.com/fbouynot) (1)
  - [trakennedy](https://github.com/trakennedy) (1)
  - [garryshtern](https://github.com/garryshtern) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [TotalGriffLock](https://github.com/TotalGriffLock) (1)
  - [dlangille](https://github.com/dlangille) (1)
  - [systeembeheerder](https://github.com/systeembeheerder) (1)
  - [makriska](https://github.com/makriska) (1)
  - [MelonicOverlord](https://github.com/MelonicOverlord) (1)
  - [r-duran](https://github.com/r-duran) (1)
  - [Martin22](https://github.com/Martin22) (1)
  - [kruczek8989](https://github.com/kruczek8989) (1)
  - [eg2965](https://github.com/eg2965) (1)
  - [rudybroersma](https://github.com/rudybroersma) (1)
  - [rinsekloek](https://github.com/rinsekloek) (1)
  - [JacobErnst98](https://github.com/JacobErnst98) (1)
  - [Calvario](https://github.com/Calvario) (1)
  - [samburney](https://github.com/samburney) (1)
  - [pozar](https://github.com/pozar) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [laf](https://github.com/laf) (41)
  - [murrant](https://github.com/murrant) (17)
  - [PipoCanaja](https://github.com/PipoCanaja) (13)
  - [Jellyfrog](https://github.com/Jellyfrog) (3)
  - [paulgear](https://github.com/paulgear) (1)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)
  - [Taarek](https://github.com/Taarek) (1)
  - [electrocret](https://github.com/electrocret) (1)
  - [dorkmatt](https://github.com/dorkmatt) (1)

#### Breaking Change
* Remove wrong netvision sensors ([#16943](https://github.com/librenms/librenms/pull/16943)) - [mpikzink](https://github.com/mpikzink)
* Add datetime and level to librenms.log ([#16330](https://github.com/librenms/librenms/pull/16330)) - [Calvario](https://github.com/Calvario)

#### Device
* Timos MPLS ignore bad rows ([#16997](https://github.com/librenms/librenms/pull/16997)) - [murrant](https://github.com/murrant)
* Junos bgp non-null fallbacks for columns that are not nullable ([#16993](https://github.com/librenms/librenms/pull/16993)) - [murrant](https://github.com/murrant)
* Fix fs-centec bias thresholds ([#16990](https://github.com/librenms/librenms/pull/16990)) - [murrant](https://github.com/murrant)
* Fix Junos BGP polling ([#16988](https://github.com/librenms/librenms/pull/16988)) - [murrant](https://github.com/murrant)
* Added additional voltage sensor for RouterOS ([#16979](https://github.com/librenms/librenms/pull/16979)) - [laf](https://github.com/laf)
* Horizon Quantum Device Support ([#16970](https://github.com/librenms/librenms/pull/16970)) - [slashdoom](https://github.com/slashdoom)
* Add support for UTAX printers ([#16951](https://github.com/librenms/librenms/pull/16951)) - [dko-strd](https://github.com/dko-strd)
* Stulz wib8000 fixes ([#16948](https://github.com/librenms/librenms/pull/16948)) - [nickhilliard](https://github.com/nickhilliard)
* Fix some issues with aix returning "NULL" ([#16947](https://github.com/librenms/librenms/pull/16947)) - [murrant](https://github.com/murrant)
* Added some additional ip pool sensors ([#16946](https://github.com/librenms/librenms/pull/16946)) - [laf](https://github.com/laf)
* Corrected index for EXOS sensors ([#16928](https://github.com/librenms/librenms/pull/16928)) - [laf](https://github.com/laf)
* New HW revision of 7130L ([#16919](https://github.com/librenms/librenms/pull/16919)) - [garryshtern](https://github.com/garryshtern)
* Added DHCP Count for RouterOS (Mikrotik) ([#16913](https://github.com/librenms/librenms/pull/16913)) - [laf](https://github.com/laf)
* DELL drac: Move the remaining inc.php sensors to YAML ([#16912](https://github.com/librenms/librenms/pull/16912)) - [mpikzink](https://github.com/mpikzink)
* Cisco SIP voice count sensor ([#16902](https://github.com/librenms/librenms/pull/16902)) - [PipoCanaja](https://github.com/PipoCanaja)
* Procurve handle HPE rebrand ([#16897](https://github.com/librenms/librenms/pull/16897)) - [TotalGriffLock](https://github.com/TotalGriffLock)
* Skip creation of "Stack Ring - Redundant" sensor for Cisco StackWise Virtual ([#16890](https://github.com/librenms/librenms/pull/16890)) - [makriska](https://github.com/makriska)
* Added sensor monitoring for IBM 3584 Tape Library ([#16884](https://github.com/librenms/librenms/pull/16884)) - [MelonicOverlord](https://github.com/MelonicOverlord)
* Tachyon - Added wireless interface to ports ([#16867](https://github.com/librenms/librenms/pull/16867)) - [Martin22](https://github.com/Martin22)
* Fix for Cisco Transceivers ([#16856](https://github.com/librenms/librenms/pull/16856)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add new vendor bitstream ([#16850](https://github.com/librenms/librenms/pull/16850)) - [kruczek8989](https://github.com/kruczek8989)
* Additional HPE Procurve Hardware State Data ([#16843](https://github.com/librenms/librenms/pull/16843)) - [eg2965](https://github.com/eg2965)
* Fix for FortiGate discovery - Issue ID #16544 ([#16753](https://github.com/librenms/librenms/pull/16753)) - [rudybroersma](https://github.com/rudybroersma)
* Initial detection with USB port detection ([#16718](https://github.com/librenms/librenms/pull/16718)) - [mpikzink](https://github.com/mpikzink)
* Nokia ISAM added extra context to also snmpwalk the ihub for uplink ports ([#16676](https://github.com/librenms/librenms/pull/16676)) - [rinsekloek](https://github.com/rinsekloek)
* Support for ESPHOME OS ([#16571](https://github.com/librenms/librenms/pull/16571)) - [JacobErnst98](https://github.com/JacobErnst98)
* Add support for Cisco ISA devices ([#16300](https://github.com/librenms/librenms/pull/16300)) - [samburney](https://github.com/samburney)
* Support for Ubiquiti UISP Fiber OLT XGS ([#15742](https://github.com/librenms/librenms/pull/15742)) - [pozar](https://github.com/pozar)

#### Webui
* Fix time intervals sometimes being wrong ([#16995](https://github.com/librenms/librenms/pull/16995)) - [murrant](https://github.com/murrant)
* Fix rrdgraph comment typo ([#16956](https://github.com/librenms/librenms/pull/16956)) - [ZPrimed](https://github.com/ZPrimed)
* Added time period names: threeday, tenday ([#16932](https://github.com/librenms/librenms/pull/16932)) - [takyanagida](https://github.com/takyanagida)
* Fixed port error red flag staying after error correction on FDB table and ARP table ([#16907](https://github.com/librenms/librenms/pull/16907)) - [takyanagida](https://github.com/takyanagida)
* Improve url validation check ([#16900](https://github.com/librenms/librenms/pull/16900)) - [murrant](https://github.com/murrant)
* Fix routes display ([#16898](https://github.com/librenms/librenms/pull/16898)) - [murrant](https://github.com/murrant)

#### Alerting
* Rename Jira Service Managment transport (#16195) ([#16967](https://github.com/librenms/librenms/pull/16967)) - [jakejakejakejakejakejake](https://github.com/jakejakejakejakejakejake)
* Fix Graph problems in Mail ([#16918](https://github.com/librenms/librenms/pull/16918)) - [mpikzink](https://github.com/mpikzink)
* Update queuemanager.py: Single element args tuple breaks alerts.php running ([#16873](https://github.com/librenms/librenms/pull/16873)) - [r-duran](https://github.com/r-duran)

#### Graphs
* Dark mode for the new Sensor graphs ([#16985](https://github.com/librenms/librenms/pull/16985)) - [mpikzink](https://github.com/mpikzink)

#### Snmp Traps
* Add support for Cisco-NS-MIB traps ([#16944](https://github.com/librenms/librenms/pull/16944)) - [adamsweet](https://github.com/adamsweet)
* Add HWG Poseidon-MIB traps ([#16934](https://github.com/librenms/librenms/pull/16934)) - [adamsweet](https://github.com/adamsweet)
* Add Axis camera alarm traps ([#16925](https://github.com/librenms/librenms/pull/16925)) - [adamsweet](https://github.com/adamsweet)

#### Discovery
* Extend STP discovery on Cisco devices + test fix for #15742 ([#16887](https://github.com/librenms/librenms/pull/16887)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Polling
* Ensure ordering of poller modules ([#16929](https://github.com/librenms/librenms/pull/16929)) - [murrant](https://github.com/murrant)

#### Bug
* Fix SLA incomplete snmpwalk replies ([#16939](https://github.com/librenms/librenms/pull/16939)) - [PipoCanaja](https://github.com/PipoCanaja)
* OrderBy snmp_index because qos.title is not unique ([#16938](https://github.com/librenms/librenms/pull/16938)) - [PipoCanaja](https://github.com/PipoCanaja)
* Null strings in Junos Transceivers code ([#16937](https://github.com/librenms/librenms/pull/16937)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Refactor
* Additional type declarations to Eventlog ([#16968](https://github.com/librenms/librenms/pull/16968)) - [mpikzink](https://github.com/mpikzink)
* Cast_number() =\> Number::cast() ([#16963](https://github.com/librenms/librenms/pull/16963)) - [mpikzink](https://github.com/mpikzink)
* Get_dev_attribs($device_id) =\> Use the Model Method ([#16961](https://github.com/librenms/librenms/pull/16961)) - [mpikzink](https://github.com/mpikzink)
* Accesspoint_by_id(x) =\> AccessPoint::find(x) ([#16958](https://github.com/librenms/librenms/pull/16958)) - [mpikzink](https://github.com/mpikzink)
* Refractor some Helpers part2 ([#16935](https://github.com/librenms/librenms/pull/16935)) - [mpikzink](https://github.com/mpikzink)
* Refractor some Helpers ([#16926](https://github.com/librenms/librenms/pull/16926)) - [mpikzink](https://github.com/mpikzink)

#### Documentation
* Update authentication docs ([#16996](https://github.com/librenms/librenms/pull/16996)) - [murrant](https://github.com/murrant)
* Update Install-LibreNMS.md ([#16982](https://github.com/librenms/librenms/pull/16982)) - [btriller](https://github.com/btriller)
* Add php-fpm requirements on Fedora for Applications ([#16933](https://github.com/librenms/librenms/pull/16933)) - [fbouynot](https://github.com/fbouynot)
* Update Dispatcher-Service.md ([#16921](https://github.com/librenms/librenms/pull/16921)) - [trakennedy](https://github.com/trakennedy)
* Update config.php.default ([#16896](https://github.com/librenms/librenms/pull/16896)) - [dlangille](https://github.com/dlangille)
* Update Authentication.md ([#16894](https://github.com/librenms/librenms/pull/16894)) - [systeembeheerder](https://github.com/systeembeheerder)
* Plugin docs udpate ([#16891](https://github.com/librenms/librenms/pull/16891)) - [murrant](https://github.com/murrant)

#### Translation
* Add multiple translation files for zh-TW. ([#16941](https://github.com/librenms/librenms/pull/16941)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Zh-TW components.php ([#16931](https://github.com/librenms/librenms/pull/16931)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Zh-TW port.php ([#16930](https://github.com/librenms/librenms/pull/16930)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Update zh-TW.json ([#16924](https://github.com/librenms/librenms/pull/16924)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Map.php - Traditional Chinese Translation ([#16906](https://github.com/librenms/librenms/pull/16906)) - [jasoncheng7115](https://github.com/jasoncheng7115)

#### Internal Features
* Snmpsim extra check ([#16936](https://github.com/librenms/librenms/pull/16936)) - [murrant](https://github.com/murrant)

#### Dependencies
* Bump tecnickcom/tcpdf from 6.7.7 to 6.8.0 ([#16914](https://github.com/librenms/librenms/pull/16914)) - [dependabot](https://github.com/apps/dependabot)

##[Old Changelogs](https://github.com/librenms/librenms/tree/master/doc/General/Changelogs)
