## 21.6.0
*(2021-06-17)*

A big thank you to the following 23 contributors this last month:

  - [mpikzink](https://github.com/mpikzink) (9)
  - [murrant](https://github.com/murrant) (6)
  - [PipoCanaja](https://github.com/PipoCanaja) (5)
  - [dust241999](https://github.com/dust241999) (3)
  - [mathieu-artic](https://github.com/mathieu-artic) (2)
  - [paulierco](https://github.com/paulierco) (2)
  - [paddy01](https://github.com/paddy01) (2)
  - [andrzejmaczka](https://github.com/andrzejmaczka) (2)
  - [zombah](https://github.com/zombah) (1)
  - [BennyE](https://github.com/BennyE) (1)
  - [Sea-n](https://github.com/Sea-n) (1)
  - [jbronn](https://github.com/jbronn) (1)
  - [d-k-7](https://github.com/d-k-7) (1)
  - [systemcrash](https://github.com/systemcrash) (1)
  - [loopodoopo](https://github.com/loopodoopo) (1)
  - [maesbrisa](https://github.com/maesbrisa) (1)
  - [thomseddon](https://github.com/thomseddon) (1)
  - [cliffalbert](https://github.com/cliffalbert) (1)
  - [Jellyfrog](https://github.com/Jellyfrog) (1)
  - [wolfraider](https://github.com/wolfraider) (1)
  - [rpardim](https://github.com/rpardim) (1)
  - [geg347](https://github.com/geg347) (1)
  - [ottorei](https://github.com/ottorei) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (31)
  - [PipoCanaja](https://github.com/PipoCanaja) (15)
  - [murrant](https://github.com/murrant) (10)
  - [tayyabali785](https://github.com/tayyabali785) (1)

#### Feature
* New module add juniper rpm support + reimplementation of cisco-sla module ([#12799](https://github.com/librenms/librenms/pull/12799)) - [geg347](https://github.com/geg347)
* ISIS-adjacency polling support ([#12461](https://github.com/librenms/librenms/pull/12461)) - [ottorei](https://github.com/ottorei)

#### Device
* Add Serialnumber for some Dell ForceTen devices ([#12960](https://github.com/librenms/librenms/pull/12960)) - [mpikzink](https://github.com/mpikzink)
* 2 more Rittal variants ([#12953](https://github.com/librenms/librenms/pull/12953)) - [mpikzink](https://github.com/mpikzink)
* Added New-OS: Alcatel-Lucent Enterprise Stellar Wireless ([#12952](https://github.com/librenms/librenms/pull/12952)) - [BennyE](https://github.com/BennyE)
* Add SDSL Support for OneAccess routers ([#12948](https://github.com/librenms/librenms/pull/12948)) - [mathieu-artic](https://github.com/mathieu-artic)
* Improve TP-Link JetStream Discovery ([#12946](https://github.com/librenms/librenms/pull/12946)) - [jbronn](https://github.com/jbronn)
* Alcatel-Lucent aos6 and aos7 fdb fix ([#12945](https://github.com/librenms/librenms/pull/12945)) - [paulierco](https://github.com/paulierco)
* Janitza UMG96 ([#12944](https://github.com/librenms/librenms/pull/12944)) - [mpikzink](https://github.com/mpikzink)
* Imporoved raritan pdu support ([#12937](https://github.com/librenms/librenms/pull/12937)) - [d-k-7](https://github.com/d-k-7)
* Alcatel-Lucent Aos7 sensors nobulk ([#12935](https://github.com/librenms/librenms/pull/12935)) - [paulierco](https://github.com/paulierco)
* Add Epson Projector as new OS ([#12928](https://github.com/librenms/librenms/pull/12928)) - [mpikzink](https://github.com/mpikzink)
* Add Barco Clickshare ([#12927](https://github.com/librenms/librenms/pull/12927)) - [mpikzink](https://github.com/mpikzink)
* Fix nokia(TiMOS) memory ([#12925](https://github.com/librenms/librenms/pull/12925)) - [paddy01](https://github.com/paddy01)
* Device - Vertiv-PDU - Issue 11608 ([#12923](https://github.com/librenms/librenms/pull/12923)) - [dust241999](https://github.com/dust241999)
* Adding basic support for Vertiv PDUs and power graphs ([#12908](https://github.com/librenms/librenms/pull/12908)) - [dust241999](https://github.com/dust241999)
* Ciena 6500 ([#12903](https://github.com/librenms/librenms/pull/12903)) - [loopodoopo](https://github.com/loopodoopo)
* Added support for PowerWalker VFI ([#12891](https://github.com/librenms/librenms/pull/12891)) - [andrzejmaczka](https://github.com/andrzejmaczka)
* Initial HAProxy ALOHA support ([#12889](https://github.com/librenms/librenms/pull/12889)) - [Jellyfrog](https://github.com/Jellyfrog)
* HWG WaterLeak sensor support ([#12865](https://github.com/librenms/librenms/pull/12865)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added definition for Riello 204 ([#12861](https://github.com/librenms/librenms/pull/12861)) - [wolfraider](https://github.com/wolfraider)
* Add support for oneaccess router ([#12850](https://github.com/librenms/librenms/pull/12850)) - [mathieu-artic](https://github.com/mathieu-artic)
* Gaia VPN IPSEC discovery ([#12823](https://github.com/librenms/librenms/pull/12823)) - [rpardim](https://github.com/rpardim)

#### Webui
* Human readable database inconsistent error ([#12950](https://github.com/librenms/librenms/pull/12950)) - [murrant](https://github.com/murrant)
* Add TopErrors widget based on ifError_rate ([#12926](https://github.com/librenms/librenms/pull/12926)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix "Sub-directory Support" in small steps ([#12911](https://github.com/librenms/librenms/pull/12911)) - [mpikzink](https://github.com/mpikzink)
* Fix "Sub-directory Support" in small steps ([#12910](https://github.com/librenms/librenms/pull/12910)) - [mpikzink](https://github.com/mpikzink)
* Fix "Sub-directory Support" in small steps ([#12905](https://github.com/librenms/librenms/pull/12905)) - [mpikzink](https://github.com/mpikzink)
* FIX Alert rules: Import from Alert Rule ([#12897](https://github.com/librenms/librenms/pull/12897)) - [andrzejmaczka](https://github.com/andrzejmaczka)
* Fix port down alert toggle ([#12884](https://github.com/librenms/librenms/pull/12884)) - [murrant](https://github.com/murrant)

#### Authentication
* Add number sign to special character handling in ActiveDirectoryAuthorizer ([#12943](https://github.com/librenms/librenms/pull/12943)) - [paddy01](https://github.com/paddy01)

#### Applications
* Fix type error ([#12899](https://github.com/librenms/librenms/pull/12899)) - [murrant](https://github.com/murrant)

#### Api
* Add api call to list OSPF ports ([#12955](https://github.com/librenms/librenms/pull/12955)) - [zombah](https://github.com/zombah)

#### Alerting
* Add Signal CLI transport support ([#12954](https://github.com/librenms/librenms/pull/12954)) - [mpikzink](https://github.com/mpikzink)
* Update device_component_down_junos macro ([#12898](https://github.com/librenms/librenms/pull/12898)) - [thomseddon](https://github.com/thomseddon)

#### Discovery
* LLDP - ifAlias should be last checked in function find_port_id ([#12904](https://github.com/librenms/librenms/pull/12904)) - [PipoCanaja](https://github.com/PipoCanaja)
* LLDP - Extend discovery lldp code to support different subtypes ([#12901](https://github.com/librenms/librenms/pull/12901)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Polling
* Nobulk setting in sensors yaml ([#12833](https://github.com/librenms/librenms/pull/12833)) - [murrant](https://github.com/murrant)

#### Rancid
* Support for fortiswitch in gen_rancid ([#12894](https://github.com/librenms/librenms/pull/12894)) - [cliffalbert](https://github.com/cliffalbert)

#### Refactor
* Filter unwanted data in Routes Ajax reply ([#12847](https://github.com/librenms/librenms/pull/12847)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Documentation
* Improve Markdown syntax in Document ([#12949](https://github.com/librenms/librenms/pull/12949)) - [Sea-n](https://github.com/Sea-n)
* Add info about lnms config:set at top of Configuration page ([#12939](https://github.com/librenms/librenms/pull/12939)) - [murrant](https://github.com/murrant)
* Update docs ([#12919](https://github.com/librenms/librenms/pull/12919)) - [systemcrash](https://github.com/systemcrash)


## 21.5.0
*(2021-05-17)*

A big thank you to the following 28 contributors this last month:

- [murrant](https://github.com/murrant) (34)
- [Jellyfrog](https://github.com/Jellyfrog) (14)
- [PipoCanaja](https://github.com/PipoCanaja) (14)
- [dependabot](https://github.com/apps/dependabot) (7)
- [rpardim](https://github.com/rpardim) (3)
- [wolfraider](https://github.com/wolfraider) (2)
- [TheGracens](https://github.com/TheGracens) (2)
- [si458](https://github.com/si458) (2)
- [mpikzink](https://github.com/mpikzink) (2)
- [Sea-n](https://github.com/Sea-n) (2)
- [Negatifff](https://github.com/Negatifff) (1)
- [backeby](https://github.com/backeby) (1)
- [SanderBlom](https://github.com/SanderBlom) (1)
- [paddy01](https://github.com/paddy01) (1)
- [nightcore500](https://github.com/nightcore500) (1)
- [arrmo](https://github.com/arrmo) (1)
- [bennetgallein](https://github.com/bennetgallein) (1)
- [Torch09](https://github.com/Torch09) (1)
- [m4rkov](https://github.com/m4rkov) (1)
- [Schultz](https://github.com/Schultz) (1)
- [thegreatecheese](https://github.com/thegreatecheese) (1)
- [paulierco](https://github.com/paulierco) (1)
- [rasssta](https://github.com/rasssta) (1)
- [craig-nokia](https://github.com/craig-nokia) (1)
- [dethmetaljeff](https://github.com/dethmetaljeff) (1)
- [djamp42](https://github.com/djamp42) (1)
- [martinberg](https://github.com/martinberg) (1)
- [SourceDoctor](https://github.com/SourceDoctor) (1)

Thanks to maintainers and others that helped with pull requests this month:

- [murrant](https://github.com/murrant) (43)
- [Jellyfrog](https://github.com/Jellyfrog) (42)
- [PipoCanaja](https://github.com/PipoCanaja) (8)
- [SourceDoctor](https://github.com/SourceDoctor) (1)
- [martinberg](https://github.com/martinberg) (1)

#### Feature
* Validate database during the install ([#12867](https://github.com/librenms/librenms/pull/12867)) - [murrant](https://github.com/murrant)
* Collect OUI Database and do OUI lookups ([#12842](https://github.com/librenms/librenms/pull/12842)) - [PipoCanaja](https://github.com/PipoCanaja)
* Show OS definition in  lnms config:get ([#12819](https://github.com/librenms/librenms/pull/12819)) - [murrant](https://github.com/murrant)

#### Security
* Jquery upgrade ([#12802](https://github.com/librenms/librenms/pull/12802)) - [murrant](https://github.com/murrant)
* Oxidized improvements ([#12773](https://github.com/librenms/librenms/pull/12773)) - [murrant](https://github.com/murrant)

#### Device
* Huawei MA5603T ([#12869](https://github.com/librenms/librenms/pull/12869)) - [Negatifff](https://github.com/Negatifff)
* Unifi 5.60.1 sysObjectID changed ([#12862](https://github.com/librenms/librenms/pull/12862)) - [wolfraider](https://github.com/wolfraider)
* Add better Unifi processors, supported on some models ([#12854](https://github.com/librenms/librenms/pull/12854)) - [murrant](https://github.com/murrant)
* Added support for GE MDS devices ([#12834](https://github.com/librenms/librenms/pull/12834)) - [SanderBlom](https://github.com/SanderBlom)
* Tripplite snmp trap handling ([#12832](https://github.com/librenms/librenms/pull/12832)) - [murrant](https://github.com/murrant)
* CyberPower UPS Updates ([#12827](https://github.com/librenms/librenms/pull/12827)) - [arrmo](https://github.com/arrmo)
* Fix for Gaia Storage duplicated ([#12824](https://github.com/librenms/librenms/pull/12824)) - [rpardim](https://github.com/rpardim)
* Checkpoint Gaia Sensor Count ([#12822](https://github.com/librenms/librenms/pull/12822)) - [rpardim](https://github.com/rpardim)
* Gaia SecureXL current status and Management Connected Gateways ([#12821](https://github.com/librenms/librenms/pull/12821)) - [rpardim](https://github.com/rpardim)
* Zywall - HW, Version, Serial and Tests ([#12788](https://github.com/librenms/librenms/pull/12788)) - [PipoCanaja](https://github.com/PipoCanaja)
* Firebrick sensor rework ([#12783](https://github.com/librenms/librenms/pull/12783)) - [murrant](https://github.com/murrant)
* Updated Nexus (nxos) os information and test data ([#12779](https://github.com/librenms/librenms/pull/12779)) - [Torch09](https://github.com/Torch09)
* FabOS sensor fixes and add SFP dBm ([#12777](https://github.com/librenms/librenms/pull/12777)) - [murrant](https://github.com/murrant)
* Vrp - Collect sticky mac addresses in fdb-table ([#12774](https://github.com/librenms/librenms/pull/12774)) - [PipoCanaja](https://github.com/PipoCanaja)
* Pop returned value from snmpwalk_group for lldp on mikrotik routeros ([#12768](https://github.com/librenms/librenms/pull/12768)) - [thegreatecheese](https://github.com/thegreatecheese)
* Adjust Alcatel-Lucent aos7 ([#12766](https://github.com/librenms/librenms/pull/12766)) - [paulierco](https://github.com/paulierco)
* Corrected Chassis Over Temp oid for state indexes ([#12764](https://github.com/librenms/librenms/pull/12764)) - [craig-nokia](https://github.com/craig-nokia)
* Fix outlet sensor indexes overwriting each other when there's more than one infeed ([#12763](https://github.com/librenms/librenms/pull/12763)) - [dethmetaljeff](https://github.com/dethmetaljeff)
* Calix (occamos) b6_316 and Calix (calix) 700 ([#12744](https://github.com/librenms/librenms/pull/12744)) - [PipoCanaja](https://github.com/PipoCanaja)
* Additional data collection for GAIA ([#12713](https://github.com/librenms/librenms/pull/12713)) - [martinberg](https://github.com/martinberg)

#### Webui
* Fix arp-search remote_interface display ([#12871](https://github.com/librenms/librenms/pull/12871)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix empty label in generate_port_link ([#12870](https://github.com/librenms/librenms/pull/12870)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix server stats widget ([#12864](https://github.com/librenms/librenms/pull/12864)) - [murrant](https://github.com/murrant)
* Disable autocomplete for password in login-form ([#12851](https://github.com/librenms/librenms/pull/12851)) - [backeby](https://github.com/backeby)
* Fix top devices widget storage graphs ([#12849](https://github.com/librenms/librenms/pull/12849)) - [murrant](https://github.com/murrant)
* Fixes to Export CSV ([#12830](https://github.com/librenms/librenms/pull/12830)) - [paddy01](https://github.com/paddy01)
* Alert rule delay/interval empty = 0 ([#12804](https://github.com/librenms/librenms/pull/12804)) - [murrant](https://github.com/murrant)
* Webui - Services bootstrap enable + status ([#12736](https://github.com/librenms/librenms/pull/12736)) - [PipoCanaja](https://github.com/PipoCanaja)
* Filter Ports out by Interface Type ([#12590](https://github.com/librenms/librenms/pull/12590)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Graphs
* Fix graph argument issues ([#12868](https://github.com/librenms/librenms/pull/12868)) - [murrant](https://github.com/murrant)
* RRD Graph optimization ([#12735](https://github.com/librenms/librenms/pull/12735)) - [murrant](https://github.com/murrant)

#### Applications
* Add mysql skip slave ([#12826](https://github.com/librenms/librenms/pull/12826)) - [si458](https://github.com/si458)
* Added metric for MySQL Slave Lag (secs) ([#12765](https://github.com/librenms/librenms/pull/12765)) - [rasssta](https://github.com/rasssta)

#### Api
* Fixing consistency across api endpoints ([#12795](https://github.com/librenms/librenms/pull/12795)) - [bennetgallein](https://github.com/bennetgallein)

#### Alerting
* Missing columns in select for $alert-\>serial & $alert-\>features ([#12771](https://github.com/librenms/librenms/pull/12771)) - [PipoCanaja](https://github.com/PipoCanaja)
* Notify if a sensor has been deleted ([#12755](https://github.com/librenms/librenms/pull/12755)) - [TheGracens](https://github.com/TheGracens)

#### Discovery
* Ignore Wrong Type errors in snpm_get and snmp_get_multi_oid ([#12800](https://github.com/librenms/librenms/pull/12800)) - [murrant](https://github.com/murrant)
* Discovery - Compute num_oid to make os development easier ([#12576](https://github.com/librenms/librenms/pull/12576)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Polling
* Fix issue with sensor class case ([#12782](https://github.com/librenms/librenms/pull/12782)) - [murrant](https://github.com/murrant)

#### Bug
* Fix database validations ([#12882](https://github.com/librenms/librenms/pull/12882)) - [murrant](https://github.com/murrant)
* PHP 8 Unit Conversion Fix ([#12857](https://github.com/librenms/librenms/pull/12857)) - [wolfraider](https://github.com/wolfraider)
* Debug and collect-snmp-data.php fixes ([#12837](https://github.com/librenms/librenms/pull/12837)) - [murrant](https://github.com/murrant)
* Fix allow_unauth_graphs ([#12829](https://github.com/librenms/librenms/pull/12829)) - [nightcore500](https://github.com/nightcore500)
* Include variables in default view ([#12818](https://github.com/librenms/librenms/pull/12818)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add support for when group doesnt exist ([#12817](https://github.com/librenms/librenms/pull/12817)) - [Jellyfrog](https://github.com/Jellyfrog)
* Bump tecnickcom/tcpdf dependency to support php 8 ([#12816](https://github.com/librenms/librenms/pull/12816)) - [Jellyfrog](https://github.com/Jellyfrog)
* Prevent error when no alert rules ([#12815](https://github.com/librenms/librenms/pull/12815)) - [Jellyfrog](https://github.com/Jellyfrog)
* Small fix in functions.inc.php for PHP8 ([#12793](https://github.com/librenms/librenms/pull/12793)) - [mpikzink](https://github.com/mpikzink)
* Change printer-supplies rrd name to include the supply_type ([#12792](https://github.com/librenms/librenms/pull/12792)) - [si458](https://github.com/si458)
* Revert "Fix StringBlade errors with a stub file" ([#12776](https://github.com/librenms/librenms/pull/12776)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix auth and crypto select options ([#12769](https://github.com/librenms/librenms/pull/12769)) - [Schultz](https://github.com/Schultz)
* Support X-Forwarded-Proto header ([#12759](https://github.com/librenms/librenms/pull/12759)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix OS sensor array return ([#12694](https://github.com/librenms/librenms/pull/12694)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Refactor
* Remove debug globals ([#12811](https://github.com/librenms/librenms/pull/12811)) - [murrant](https://github.com/murrant)
* Make applications work with rrdcached ([#12807](https://github.com/librenms/librenms/pull/12807)) - [Jellyfrog](https://github.com/Jellyfrog)
* Make docker app work with rrdcached ([#12746](https://github.com/librenms/librenms/pull/12746)) - [djamp42](https://github.com/djamp42)

#### Cleanup
* Misc cleanup ([#12758](https://github.com/librenms/librenms/pull/12758)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Documentation
* Plugins should be called statically ([#12810](https://github.com/librenms/librenms/pull/12810)) - [mpikzink](https://github.com/mpikzink)
* Fix docs custom graph rrd functions ([#12803](https://github.com/librenms/librenms/pull/12803)) - [murrant](https://github.com/murrant)
* Use GitHub instead of Github ([#12781](https://github.com/librenms/librenms/pull/12781)) - [Sea-n](https://github.com/Sea-n)
* Smokeping sub site requires fcgiwrap ([#12775](https://github.com/librenms/librenms/pull/12775)) - [m4rkov](https://github.com/m4rkov)
* Document sub index references ([#12767](https://github.com/librenms/librenms/pull/12767)) - [murrant](https://github.com/murrant)

#### Tests
* Set DBSetupTest timezone to UTC ([#12881](https://github.com/librenms/librenms/pull/12881)) - [murrant](https://github.com/murrant)
* Always test all OS detection. ([#12879](https://github.com/librenms/librenms/pull/12879)) - [murrant](https://github.com/murrant)
* Cache astext in tests to avoid DNS lookup ([#12784](https://github.com/librenms/librenms/pull/12784)) - [Jellyfrog](https://github.com/Jellyfrog)
* Enable PHPStan linter ([#12678](https://github.com/librenms/librenms/pull/12678)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Misc
* Lnms dev:simulate Snmpsim debug output on failure ([#12880](https://github.com/librenms/librenms/pull/12880)) - [murrant](https://github.com/murrant)
* Enable config:set to set variables inside a nested array of settings ([#12772](https://github.com/librenms/librenms/pull/12772)) - [murrant](https://github.com/murrant)

#### Dependencies
* Bump postcss from 8.2.2 to 8.2.10 ([#12858](https://github.com/librenms/librenms/pull/12858)) - [dependabot](https://github.com/apps/dependabot)
* Bump lodash from 4.17.20 to 4.17.21 ([#12848](https://github.com/librenms/librenms/pull/12848)) - [dependabot](https://github.com/apps/dependabot)
* Bump url-parse from 1.4.7 to 1.5.1 ([#12844](https://github.com/librenms/librenms/pull/12844)) - [dependabot](https://github.com/apps/dependabot)
* Bump phpmailer/phpmailer from 6.4.0 to 6.4.1 ([#12831](https://github.com/librenms/librenms/pull/12831)) - [dependabot](https://github.com/apps/dependabot)
* Bump laravel/framework from 8.35.1 to 8.40.0 ([#12814](https://github.com/librenms/librenms/pull/12814)) - [dependabot](https://github.com/apps/dependabot)
* Bump composer/composer from 2.0.11 to 2.0.13 ([#12813](https://github.com/librenms/librenms/pull/12813)) - [dependabot](https://github.com/apps/dependabot)
* Bump rmccue/requests from 1.7.0 to 1.8.0 ([#12812](https://github.com/librenms/librenms/pull/12812)) - [dependabot](https://github.com/apps/dependabot)
* Bump laravel dusk ([#12808](https://github.com/librenms/librenms/pull/12808)) - [Jellyfrog](https://github.com/Jellyfrog)


## 21.4.0
*(2021-04-17)*

A big thank you to the following 34 contributors this last month:

  - [Jellyfrog](https://github.com/Jellyfrog) (35)
  - [murrant](https://github.com/murrant) (26)
  - [PipoCanaja](https://github.com/PipoCanaja) (8)
  - [Torch09](https://github.com/Torch09) (2)
  - [si458](https://github.com/si458) (2)
  - [TheGracens](https://github.com/TheGracens) (2)
  - [Cupidazul](https://github.com/Cupidazul) (2)
  - [DaveB91](https://github.com/DaveB91) (1)
  - [martinberg](https://github.com/martinberg) (1)
  - [craig-nokia](https://github.com/craig-nokia) (1)
  - [codejake](https://github.com/codejake) (1)
  - [tamikkelsen](https://github.com/tamikkelsen) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [Schultz](https://github.com/Schultz) (1)
  - [opalivan](https://github.com/opalivan) (1)
  - [hrtrd](https://github.com/hrtrd) (1)
  - [zombah](https://github.com/zombah) (1)
  - [casdr](https://github.com/casdr) (1)
  - [Wooboy](https://github.com/Wooboy) (1)
  - [djamp42](https://github.com/djamp42) (1)
  - [dlangille](https://github.com/dlangille) (1)
  - [Erik-Lamers1](https://github.com/Erik-Lamers1) (1)
  - [WillIrvine](https://github.com/WillIrvine) (1)
  - [mpikzink](https://github.com/mpikzink) (1)
  - [simmonmt](https://github.com/simmonmt) (1)
  - [yswery-reconz](https://github.com/yswery-reconz) (1)
  - [0x4c6565](https://github.com/0x4c6565) (1)
  - [antonio-jose-almeida](https://github.com/antonio-jose-almeida) (1)
  - [cjsoftuk](https://github.com/cjsoftuk) (1)
  - [shepherdjay](https://github.com/shepherdjay) (1)
  - [imwuwei](https://github.com/imwuwei) (1)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)
  - [filippog](https://github.com/filippog) (1)
  - [bofh80](https://github.com/bofh80) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (50)
  - [Jellyfrog](https://github.com/Jellyfrog) (39)
  - [PipoCanaja](https://github.com/PipoCanaja) (11)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)
  - [frank42hh](https://github.com/frank42hh) (1)
  - [haydenseitz](https://github.com/haydenseitz) (1)

#### Feature
* Cisco AES256 support ([#12717](https://github.com/librenms/librenms/pull/12717)) - [Schultz](https://github.com/Schultz)
* Define Port Groups ([#12402](https://github.com/librenms/librenms/pull/12402)) - [SourceDoctor](https://github.com/SourceDoctor)
* Service watchdog - add systemd watchdog for resiliency ([#12188](https://github.com/librenms/librenms/pull/12188)) - [bofh80](https://github.com/bofh80)

#### Security
* Escape user editable field ([#12739](https://github.com/librenms/librenms/pull/12739)) - [murrant](https://github.com/murrant)
* Fix SQL injection in rediscover-device ([#12716](https://github.com/librenms/librenms/pull/12716)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Device
* Added basic support for BKtel Optical Amplifier ([#12754](https://github.com/librenms/librenms/pull/12754)) - [Torch09](https://github.com/Torch09)
* Added Liebert HPM support ([#12747](https://github.com/librenms/librenms/pull/12747)) - [martinberg](https://github.com/martinberg)
* Added basic Delta Orion Controller support ([#12741](https://github.com/librenms/librenms/pull/12741)) - [craig-nokia](https://github.com/craig-nokia)
* Basic support for zyxelac_xmg3927 ([#12740](https://github.com/librenms/librenms/pull/12740)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support Cisco CBS350 ([#12737](https://github.com/librenms/librenms/pull/12737)) - [PipoCanaja](https://github.com/PipoCanaja)
* Correct OID for c3GsmSimStatus ([#12724](https://github.com/librenms/librenms/pull/12724)) - [tamikkelsen](https://github.com/tamikkelsen)
* Added support for Infinera XTM ([#12710](https://github.com/librenms/librenms/pull/12710)) - [Torch09](https://github.com/Torch09)
* TAIT - Add entity physical support ([#12703](https://github.com/librenms/librenms/pull/12703)) - [opalivan](https://github.com/opalivan)
* BDCOM update support ([#12696](https://github.com/librenms/librenms/pull/12696)) - [hrtrd](https://github.com/hrtrd)
* Update IOS with 4948 variant hardware detection ([#12685](https://github.com/librenms/librenms/pull/12685)) - [zombah](https://github.com/zombah)
* VRP - fix global VRF being NULL and not '' for cbgp, support for NetEngine devices ([#12676](https://github.com/librenms/librenms/pull/12676)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for Canon printer model TM TX series ([#12667](https://github.com/librenms/librenms/pull/12667)) - [Wooboy](https://github.com/Wooboy)
* Fortinet per-core cpu ([#12660](https://github.com/librenms/librenms/pull/12660)) - [murrant](https://github.com/murrant)
* Fixed polling and health issues for 9001 and NCS devices, added suppo… ([#12640](https://github.com/librenms/librenms/pull/12640)) - [WillIrvine](https://github.com/WillIrvine)
* VRP - Fix SSID Client count ([#12629](https://github.com/librenms/librenms/pull/12629)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add CPU and Mem for Teldat Devices ([#12619](https://github.com/librenms/librenms/pull/12619)) - [Cupidazul](https://github.com/Cupidazul)
* Better firebrick support ([#12600](https://github.com/librenms/librenms/pull/12600)) - [cjsoftuk](https://github.com/cjsoftuk)
* Huawei VRF BGP_Peers update ([#12585](https://github.com/librenms/librenms/pull/12585)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add Packet Buffers as memory for PanOS ([#12582](https://github.com/librenms/librenms/pull/12582)) - [shepherdjay](https://github.com/shepherdjay)
* Parse info from H3C branded comware devices ([#12551](https://github.com/librenms/librenms/pull/12551)) - [imwuwei](https://github.com/imwuwei)

#### Webui
* Fix inventory sensor links when empty ([#12745](https://github.com/librenms/librenms/pull/12745)) - [murrant](https://github.com/murrant)
* Fix mini graphs ([#12738](https://github.com/librenms/librenms/pull/12738)) - [murrant](https://github.com/murrant)
* Fix alert rules display when creating new alert template ([#12731](https://github.com/librenms/librenms/pull/12731)) - [murrant](https://github.com/murrant)
* Fix bug in component table ([#12730](https://github.com/librenms/librenms/pull/12730)) - [murrant](https://github.com/murrant)
* Use native browser lazy load ([#12720](https://github.com/librenms/librenms/pull/12720)) - [murrant](https://github.com/murrant)
* Fix devices latency tab calendar position ([#12684](https://github.com/librenms/librenms/pull/12684)) - [TheGracens](https://github.com/TheGracens)
* Fix links to non-existent devices ([#12680](https://github.com/librenms/librenms/pull/12680)) - [murrant](https://github.com/murrant)
* Scrollable Dashboard selection menu ([#12656](https://github.com/librenms/librenms/pull/12656)) - [TheGracens](https://github.com/TheGracens)
* Fix double escaping sysContact on device overview ([#12653](https://github.com/librenms/librenms/pull/12653)) - [murrant](https://github.com/murrant)
* Hide disabled components from overview page CIMC ([#12650](https://github.com/librenms/librenms/pull/12650)) - [djamp42](https://github.com/djamp42)
* Fix progress-bar 0% ([#12648](https://github.com/librenms/librenms/pull/12648)) - [si458](https://github.com/si458)
* Add ability to set a custom port on IPMI agents ([#12634](https://github.com/librenms/librenms/pull/12634)) - [yswery-reconz](https://github.com/yswery-reconz)
* WebUI - Display interface errors per second instead of accumulated ([#12613](https://github.com/librenms/librenms/pull/12613)) - [antonio-jose-almeida](https://github.com/antonio-jose-almeida)
* Add ID to Device Table List and to Device Dependencies Table List + Shorten ifname in Device: Recent Events. ([#12397](https://github.com/librenms/librenms/pull/12397)) - [Cupidazul](https://github.com/Cupidazul)

#### Graphs
* Fix mempools divide by 0 ([#12734](https://github.com/librenms/librenms/pull/12734)) - [murrant](https://github.com/murrant)

#### Alerting
* Add UKFast PSS transport ([#12624](https://github.com/librenms/librenms/pull/12624)) - [0x4c6565](https://github.com/0x4c6565)
* Support multiple Alertmanager URLs ([#12346](https://github.com/librenms/librenms/pull/12346)) - [filippog](https://github.com/filippog)

#### Discovery
* Fix service template discovery ([#12662](https://github.com/librenms/librenms/pull/12662)) - [murrant](https://github.com/murrant)

#### Polling
* Allow getting VDSL stats on "down" VDSL ports ([#12753](https://github.com/librenms/librenms/pull/12753)) - [DaveB91](https://github.com/DaveB91)
* Fix printer state error ([#12681](https://github.com/librenms/librenms/pull/12681)) - [murrant](https://github.com/murrant)
* Translate hex sensor values returned from ipmitool ([#12638](https://github.com/librenms/librenms/pull/12638)) - [simmonmt](https://github.com/simmonmt)

#### Bug
* Fix globe controller up/down partition bug ([#12757](https://github.com/librenms/librenms/pull/12757)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix scopeIsArchived query ([#12756](https://github.com/librenms/librenms/pull/12756)) - [Jellyfrog](https://github.com/Jellyfrog)
* Split port_groups migration to prevent issues ([#12732](https://github.com/librenms/librenms/pull/12732)) - [murrant](https://github.com/murrant)
* Fix ports table when unpolled ports exist ([#12722](https://github.com/librenms/librenms/pull/12722)) - [murrant](https://github.com/murrant)
* JS fixes for IE ([#12721](https://github.com/librenms/librenms/pull/12721)) - [murrant](https://github.com/murrant)
* Correct snmp function usage ([#12714](https://github.com/librenms/librenms/pull/12714)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix adding discrete ipmi sensors by mistake ([#12709](https://github.com/librenms/librenms/pull/12709)) - [si458](https://github.com/si458)
* Fix mempool tags ([#12705](https://github.com/librenms/librenms/pull/12705)) - [murrant](https://github.com/murrant)
* Issue with snmpwalk_group string splitting ([#12701](https://github.com/librenms/librenms/pull/12701)) - [PipoCanaja](https://github.com/PipoCanaja)
* Use Device object instead of array ([#12699](https://github.com/librenms/librenms/pull/12699)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix alert template variable ping_timestamp ([#12690](https://github.com/librenms/librenms/pull/12690)) - [Jellyfrog](https://github.com/Jellyfrog)
* Remove snmp2ipv6 ([#12683](https://github.com/librenms/librenms/pull/12683)) - [murrant](https://github.com/murrant)
* Fix deviceUrl check ([#12682](https://github.com/librenms/librenms/pull/12682)) - [Jellyfrog](https://github.com/Jellyfrog)
* Correct globecontroller typo ([#12671](https://github.com/librenms/librenms/pull/12671)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix undefined function in vrp peers ([#12669](https://github.com/librenms/librenms/pull/12669)) - [murrant](https://github.com/murrant)
* Fix Config reference in System validations ([#12668](https://github.com/librenms/librenms/pull/12668)) - [casdr](https://github.com/casdr)
* Fix regression from #12642 ([#12661](https://github.com/librenms/librenms/pull/12661)) - [Jellyfrog](https://github.com/Jellyfrog)
* Don't fail on rrd close ([#12659](https://github.com/librenms/librenms/pull/12659)) - [murrant](https://github.com/murrant)
* Change cache table to mediumtext ([#12649](https://github.com/librenms/librenms/pull/12649)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fixed VRF name change not updated in DB ([#12644](https://github.com/librenms/librenms/pull/12644)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix a bunch of bugs ([#12643](https://github.com/librenms/librenms/pull/12643)) - [Jellyfrog](https://github.com/Jellyfrog)
* Misc cleanup ([#12641](https://github.com/librenms/librenms/pull/12641)) - [Jellyfrog](https://github.com/Jellyfrog)
* PHP8 Bug in printChangedStats ([#12639](https://github.com/librenms/librenms/pull/12639)) - [mpikzink](https://github.com/mpikzink)
* Correct sensor_id variable ([#12633](https://github.com/librenms/librenms/pull/12633)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Refactor
* Remove legacy function calls ([#12651](https://github.com/librenms/librenms/pull/12651)) - [murrant](https://github.com/murrant)
* Misc cleanups ([#12642](https://github.com/librenms/librenms/pull/12642)) - [Jellyfrog](https://github.com/Jellyfrog)
* Re-implement Printer as a class based module ([#12605](https://github.com/librenms/librenms/pull/12605)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Cleanup
* More cleanups ([#12715](https://github.com/librenms/librenms/pull/12715)) - [Jellyfrog](https://github.com/Jellyfrog)
* Cleanup ([#12695](https://github.com/librenms/librenms/pull/12695)) - [Jellyfrog](https://github.com/Jellyfrog)
* PHPDoc fixes ([#12693](https://github.com/librenms/librenms/pull/12693)) - [Jellyfrog](https://github.com/Jellyfrog)
* PHPDoc fixes ([#12687](https://github.com/librenms/librenms/pull/12687)) - [Jellyfrog](https://github.com/Jellyfrog)
* Type hint all device model relations ([#12686](https://github.com/librenms/librenms/pull/12686)) - [Jellyfrog](https://github.com/Jellyfrog)
* Linting ([#12677](https://github.com/librenms/librenms/pull/12677)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix misc problems ([#12675](https://github.com/librenms/librenms/pull/12675)) - [Jellyfrog](https://github.com/Jellyfrog)
* More PHPDoc changes ([#12674](https://github.com/librenms/librenms/pull/12674)) - [Jellyfrog](https://github.com/Jellyfrog)
* Type hint model relations ([#12673](https://github.com/librenms/librenms/pull/12673)) - [Jellyfrog](https://github.com/Jellyfrog)
* Make moduleobserver type hinting overridable ([#12670](https://github.com/librenms/librenms/pull/12670)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix more PHPDoc ([#12665](https://github.com/librenms/librenms/pull/12665)) - [Jellyfrog](https://github.com/Jellyfrog)
* Replace Auth \> Illuminate\Support\Facades\Auth ([#12664](https://github.com/librenms/librenms/pull/12664)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Documentation
* Fix typos in Dashboards.md ([#12733](https://github.com/librenms/librenms/pull/12733)) - [codejake](https://github.com/codejake)
* Fix doc building ([#12711](https://github.com/librenms/librenms/pull/12711)) - [Jellyfrog](https://github.com/Jellyfrog)
* Filter some validation when installed from a package ([#12647](https://github.com/librenms/librenms/pull/12647)) - [dlangille](https://github.com/dlangille)
* Add poller_group docs on auto-discovered devices ([#12646](https://github.com/librenms/librenms/pull/12646)) - [Erik-Lamers1](https://github.com/Erik-Lamers1)

#### Tests
* Add feature to capture a full snmprec ([#12706](https://github.com/librenms/librenms/pull/12706)) - [Jellyfrog](https://github.com/Jellyfrog)
* Lint with shellcheck ([#12666](https://github.com/librenms/librenms/pull/12666)) - [Jellyfrog](https://github.com/Jellyfrog)
* Enable Black for linter ([#12663](https://github.com/librenms/librenms/pull/12663)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add lint GitHub Action ([#12655](https://github.com/librenms/librenms/pull/12655)) - [murrant](https://github.com/murrant)

#### Misc
* Load device relationship from device cache ([#12712](https://github.com/librenms/librenms/pull/12712)) - [murrant](https://github.com/murrant)
* Switch to utf8mb4 ([#12580](https://github.com/librenms/librenms/pull/12580)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Dependencies
* Bump phpseclib/phpseclib from 2.0.30 to 3.0.7 ([#12723](https://github.com/librenms/librenms/pull/12723)) - [dependabot](https://github.com/apps/dependabot)
* Bump php-amqplib to support PHP8 ([#12698](https://github.com/librenms/librenms/pull/12698)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update php dependencies ([#12692](https://github.com/librenms/librenms/pull/12692)) - [Jellyfrog](https://github.com/Jellyfrog)


## 21.3.0
*(2021-03-20)*

A big thank you to the following 27 contributors this last month:

  - [murrant](https://github.com/murrant) (14)
  - [Jellyfrog](https://github.com/Jellyfrog) (12)
  - [PipoCanaja](https://github.com/PipoCanaja) (6)
  - [SourceDoctor](https://github.com/SourceDoctor) (4)
  - [si458](https://github.com/si458) (2)
  - [Cormoran96](https://github.com/Cormoran96) (2)
  - [miff2000](https://github.com/miff2000) (2)
  - [mpikzink](https://github.com/mpikzink) (1)
  - [rasssta](https://github.com/rasssta) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [Chewie9999](https://github.com/Chewie9999) (1)
  - [bennet-esyoil](https://github.com/bennet-esyoil) (1)
  - [rkojedzinszky](https://github.com/rkojedzinszky) (1)
  - [bofh80](https://github.com/bofh80) (1)
  - [WillIrvine](https://github.com/WillIrvine) (1)
  - [pbaldovi](https://github.com/pbaldovi) (1)
  - [h-barnhart](https://github.com/h-barnhart) (1)
  - [waddles](https://github.com/waddles) (1)
  - [scamp](https://github.com/scamp) (1)
  - [aarchijs](https://github.com/aarchijs) (1)
  - [yrebrac](https://github.com/yrebrac) (1)
  - [Serphentas](https://github.com/Serphentas) (1)
  - [theochita](https://github.com/theochita) (1)
  - [Schouwenburg](https://github.com/Schouwenburg) (1)
  - [neg2led](https://github.com/neg2led) (1)
  - [bakerds](https://github.com/bakerds) (1)
  - [CirnoT](https://github.com/CirnoT) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (28)
  - [murrant](https://github.com/murrant) (17)
  - [PipoCanaja](https://github.com/PipoCanaja) (8)
  - [SourceDoctor](https://github.com/SourceDoctor) (8)
  - [f0o](https://github.com/f0o) (1)
  - [crazy-max](https://github.com/crazy-max) (1)
  - [yrebrac](https://github.com/yrebrac) (1)

#### Feature
* Developer device simulation ([#12577](https://github.com/librenms/librenms/pull/12577)) - [murrant](https://github.com/murrant)

#### Device
* Add identification for Edgeswitch 8XP ([#12622](https://github.com/librenms/librenms/pull/12622)) - [si458](https://github.com/si458)
* Add more printer data ([#12617](https://github.com/librenms/librenms/pull/12617)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update hpe-ilo.yaml ([#12612](https://github.com/librenms/librenms/pull/12612)) - [rasssta](https://github.com/rasssta)
* Fix AOS 7 sensors ([#12599](https://github.com/librenms/librenms/pull/12599)) - [murrant](https://github.com/murrant)
* Collect BIOS version for IBMC ([#12586](https://github.com/librenms/librenms/pull/12586)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for siteboss571 + new tests ([#12568](https://github.com/librenms/librenms/pull/12568)) - [WillIrvine](https://github.com/WillIrvine)
* VRP - Huawei Wifi Controllers and routers 3G/4G update ([#12565](https://github.com/librenms/librenms/pull/12565)) - [PipoCanaja](https://github.com/PipoCanaja)
* F5 realservers (node_name) display ([#12553](https://github.com/librenms/librenms/pull/12553)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add BGP discovery and polling for Dell OS10 devices ([#12549](https://github.com/librenms/librenms/pull/12549)) - [waddles](https://github.com/waddles)
* Update support for ServersCheck ([#12546](https://github.com/librenms/librenms/pull/12546)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add support for Edge-Core ECS4100 series devices ([#12530](https://github.com/librenms/librenms/pull/12530)) - [scamp](https://github.com/scamp)
* Adding Cisco ME1200 support ([#12527](https://github.com/librenms/librenms/pull/12527)) - [aarchijs](https://github.com/aarchijs)
* Cisco enhanced cellular ([#12463](https://github.com/librenms/librenms/pull/12463)) - [Schouwenburg](https://github.com/Schouwenburg)
* Support newer SyncServer ([#12423](https://github.com/librenms/librenms/pull/12423)) - [neg2led](https://github.com/neg2led)
* Added alarm detection and optical PMs for Waveserver Ai ([#12380](https://github.com/librenms/librenms/pull/12380)) - [bakerds](https://github.com/bakerds)

#### Webui
* Fix services availability-map link ([#12632](https://github.com/librenms/librenms/pull/12632)) - [si458](https://github.com/si458)
* Add css text center ([#12608](https://github.com/librenms/librenms/pull/12608)) - [Cormoran96](https://github.com/Cormoran96)
* Sort Type List in Eventlog Pages ([#12572](https://github.com/librenms/librenms/pull/12572)) - [SourceDoctor](https://github.com/SourceDoctor)
* Spanning Tree Link in Eventlog ([#12571](https://github.com/librenms/librenms/pull/12571)) - [SourceDoctor](https://github.com/SourceDoctor)
* Notifications : display sensor state textual value ([#12554](https://github.com/librenms/librenms/pull/12554)) - [PipoCanaja](https://github.com/PipoCanaja)
* GUI - Fix the detailed access point view ([#12543](https://github.com/librenms/librenms/pull/12543)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add button to show verbose alert details in the alert, alert-log webui ([#12484](https://github.com/librenms/librenms/pull/12484)) - [theochita](https://github.com/theochita)

#### Snmp Traps
* Fixed typo jnxPowerSupplyOK ([#12556](https://github.com/librenms/librenms/pull/12556)) - [h-barnhart](https://github.com/h-barnhart)

#### Applications
* Add application powermon ([#12500](https://github.com/librenms/librenms/pull/12500)) - [yrebrac](https://github.com/yrebrac)
* Chrony support ([#12488](https://github.com/librenms/librenms/pull/12488)) - [Serphentas](https://github.com/Serphentas)
* Docker stats app ([#12358](https://github.com/librenms/librenms/pull/12358)) - [Cormoran96](https://github.com/Cormoran96)

#### Alerting
* Add Google Chat Transport ([#12558](https://github.com/librenms/librenms/pull/12558)) - [pbaldovi](https://github.com/pbaldovi)

#### Discovery
* DynamicDiscovery - Guess num_oid if not provided in YAML file ([#12570](https://github.com/librenms/librenms/pull/12570)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix location for devices with broken snmp ([#12544](https://github.com/librenms/librenms/pull/12544)) - [murrant](https://github.com/murrant)

#### Oxidized
* Oxidized support airfiber ([#12597](https://github.com/librenms/librenms/pull/12597)) - [murrant](https://github.com/murrant)

#### Bug
* Bugfix for no sockets on Unix Agents ([#12637](https://github.com/librenms/librenms/pull/12637)) - [mpikzink](https://github.com/mpikzink)
* Fix Service Templates Dynamic ([#12626](https://github.com/librenms/librenms/pull/12626)) - [murrant](https://github.com/murrant)
* Service templates -  fix rules ([#12587](https://github.com/librenms/librenms/pull/12587)) - [bofh80](https://github.com/bofh80)
* Fix vminfo invalid power state in migration ([#12567](https://github.com/librenms/librenms/pull/12567)) - [murrant](https://github.com/murrant)
* Add missing Power states ([#12559](https://github.com/librenms/librenms/pull/12559)) - [Jellyfrog](https://github.com/Jellyfrog)
* PHP 8 fixes ([#12528](https://github.com/librenms/librenms/pull/12528)) - [murrant](https://github.com/murrant)

#### Refactor
* Remove legacy json format function ([#12583](https://github.com/librenms/librenms/pull/12583)) - [murrant](https://github.com/murrant)

#### Documentation
* Update Agent-Setup.md with systemd instructions on how to restrict on which NIC the agent listens. ([#12601](https://github.com/librenms/librenms/pull/12601)) - [Chewie9999](https://github.com/Chewie9999)
* Broken link on fast-polling page ([#12595](https://github.com/librenms/librenms/pull/12595)) - [bennet-esyoil](https://github.com/bennet-esyoil)
* Fix bullet points ([#12574](https://github.com/librenms/librenms/pull/12574)) - [miff2000](https://github.com/miff2000)
* Fix the bullet point rendering ([#12560](https://github.com/librenms/librenms/pull/12560)) - [miff2000](https://github.com/miff2000)

#### Tests
* Location tests more reliable ([#12584](https://github.com/librenms/librenms/pull/12584)) - [murrant](https://github.com/murrant)
* Test both MariaDB and MySQL ([#12547](https://github.com/librenms/librenms/pull/12547)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Misc
* Set correct min database version ([#12606](https://github.com/librenms/librenms/pull/12606)) - [Jellyfrog](https://github.com/Jellyfrog)
* Simplify process reaping ([#12593](https://github.com/librenms/librenms/pull/12593)) - [rkojedzinszky](https://github.com/rkojedzinszky)
* Fix broken tests ([#12588](https://github.com/librenms/librenms/pull/12588)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add minimum database version check ([#12581](https://github.com/librenms/librenms/pull/12581)) - [Jellyfrog](https://github.com/Jellyfrog)
* Global Settings - SNMP Timeout ([#12579](https://github.com/librenms/librenms/pull/12579)) - [SourceDoctor](https://github.com/SourceDoctor)
* Improved rrdtool version validation ([#12539](https://github.com/librenms/librenms/pull/12539)) - [murrant](https://github.com/murrant)
* Use DNS Location Record for Location ([#12409](https://github.com/librenms/librenms/pull/12409)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Dependencies
* Bump elliptic from 6.5.3 to 6.5.4 ([#12602](https://github.com/librenms/librenms/pull/12602)) - [dependabot](https://github.com/apps/dependabot)


## 21.2.0
*(2021-02-16)*

A big thank you to the following 18 contributors this last month:

  - [murrant](https://github.com/murrant) (9)
  - [Jellyfrog](https://github.com/Jellyfrog) (8)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [hanserasmus](https://github.com/hanserasmus) (1)
  - [nightcore500](https://github.com/nightcore500) (1)
  - [simmonmt](https://github.com/simmonmt) (1)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)
  - [dejantep](https://github.com/dejantep) (1)
  - [TridTech](https://github.com/TridTech) (1)
  - [Showfom](https://github.com/Showfom) (1)
  - [jasoncheng7115](https://github.com/jasoncheng7115) (1)
  - [nkringle](https://github.com/nkringle) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [Negatifff](https://github.com/Negatifff) (1)
  - [Cupidazul](https://github.com/Cupidazul) (1)
  - [paddy01](https://github.com/paddy01) (1)
  - [Torch09](https://github.com/Torch09) (1)
  - [bofh80](https://github.com/bofh80) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (13)
  - [murrant](https://github.com/murrant) (12)
  - [SourceDoctor](https://github.com/SourceDoctor) (5)

#### Feature
* GPS coordinates from device ([#12521](https://github.com/librenms/librenms/pull/12521)) - [murrant](https://github.com/murrant)
* Show Alert Count on Widget ([#12503](https://github.com/librenms/librenms/pull/12503)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add Service Templates ([#12107](https://github.com/librenms/librenms/pull/12107)) - [bofh80](https://github.com/bofh80)

#### Security
* Fix url generator XSS ([#12507](https://github.com/librenms/librenms/pull/12507)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix XSS in notifications ([#12504](https://github.com/librenms/librenms/pull/12504)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Device
* VRP - Filter invalid temperature data 0x7fffffff ([#12537](https://github.com/librenms/librenms/pull/12537)) - [PipoCanaja](https://github.com/PipoCanaja)
* Rittal CMC III low warn limit and CAN bus current ([#12513](https://github.com/librenms/librenms/pull/12513)) - [nightcore500](https://github.com/nightcore500)
* Add TrueNAS temperature ([#12506](https://github.com/librenms/librenms/pull/12506)) - [simmonmt](https://github.com/simmonmt)
* Update deltaups.yaml definition ([#12497](https://github.com/librenms/librenms/pull/12497)) - [TridTech](https://github.com/TridTech)
* Fixes incorrect device overlay graph type for poweralert 12 devices ([#12491](https://github.com/librenms/librenms/pull/12491)) - [nkringle](https://github.com/nkringle)
* Socomecpdu support ([#12481](https://github.com/librenms/librenms/pull/12481)) - [Negatifff](https://github.com/Negatifff)
* Add support for SCS KS air-conditioning Devices ([#12360](https://github.com/librenms/librenms/pull/12360)) - [Torch09](https://github.com/Torch09)

#### Webui
* Speedup device list ([#12514](https://github.com/librenms/librenms/pull/12514)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Authentication
* Fixes issues with binding and authenticating users in nested groups ([#12398](https://github.com/librenms/librenms/pull/12398)) - [paddy01](https://github.com/paddy01)

#### Applications
* Add poller feature for RRDCached SNMP to query remote agent. ([#12430](https://github.com/librenms/librenms/pull/12430)) - [Cupidazul](https://github.com/Cupidazul)

#### Discovery
* Move sysContact polling to discovery ([#12524](https://github.com/librenms/librenms/pull/12524)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Bug
* Better handling of invalid notification dates ([#12523](https://github.com/librenms/librenms/pull/12523)) - [murrant](https://github.com/murrant)
* Fix invalid dates in the database ([#12512](https://github.com/librenms/librenms/pull/12512)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix Fast Ping alerts always running ([#12510](https://github.com/librenms/librenms/pull/12510)) - [murrant](https://github.com/murrant)
* Fix Fast Ping ([#12509](https://github.com/librenms/librenms/pull/12509)) - [murrant](https://github.com/murrant)
* Network map fix Css/img ([#12498](https://github.com/librenms/librenms/pull/12498)) - [dejantep](https://github.com/dejantep)
* Correct check for SNMPv3 SHA-192/256 compability ([#12494](https://github.com/librenms/librenms/pull/12494)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Cleanup
* Remove perf_times table ([#12517](https://github.com/librenms/librenms/pull/12517)) - [murrant](https://github.com/murrant)

#### Documentation
* Update transports docs ([#12518](https://github.com/librenms/librenms/pull/12518)) - [hanserasmus](https://github.com/hanserasmus)
* Correct rrdcached.sock location on doc ([#12496](https://github.com/librenms/librenms/pull/12496)) - [Showfom](https://github.com/Showfom)

#### Translation
* Updated Traditional Chinese Translation ([#12493](https://github.com/librenms/librenms/pull/12493)) - [jasoncheng7115](https://github.com/jasoncheng7115)

#### Tests
* "variant" is now required for test data ([#12531](https://github.com/librenms/librenms/pull/12531)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Dependencies
* Update php packages and fix composer warnings ([#12526](https://github.com/librenms/librenms/pull/12526)) - [murrant](https://github.com/murrant)
* Remove larapoke until they support PHP 8 ([#12522](https://github.com/librenms/librenms/pull/12522)) - [murrant](https://github.com/murrant)
* Bump laravel/framework from 8.22.1 to 8.24.0 ([#12490](https://github.com/librenms/librenms/pull/12490)) - [dependabot](https://github.com/apps/dependabot)


## 21.1.0
*(2021-02-02)*

A big thank you to the following 37 contributors this last month:

  - [murrant](https://github.com/murrant) (14)
  - [Jellyfrog](https://github.com/Jellyfrog) (8)
  - [efelon](https://github.com/efelon) (4)
  - [SourceDoctor](https://github.com/SourceDoctor) (4)
  - [paulierco](https://github.com/paulierco) (4)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (3)
  - [dependabot](https://github.com/apps/dependabot) (3)
  - [crazy-max](https://github.com/crazy-max) (2)
  - [djamp42](https://github.com/djamp42) (2)
  - [jezekus](https://github.com/jezekus) (2)
  - [martijn-schmidt](https://github.com/martijn-schmidt) (2)
  - [vitalisator](https://github.com/vitalisator) (2)
  - [hanserasmus](https://github.com/hanserasmus) (1)
  - [lukoramu](https://github.com/lukoramu) (1)
  - [deveth0](https://github.com/deveth0) (1)
  - [lazyb0nes](https://github.com/lazyb0nes) (1)
  - [mpikzink](https://github.com/mpikzink) (1)
  - [richardlawley](https://github.com/richardlawley) (1)
  - [Torch09](https://github.com/Torch09) (1)
  - [rk4an](https://github.com/rk4an) (1)
  - [FingerlessGlov3s](https://github.com/FingerlessGlov3s) (1)
  - [dlehman83](https://github.com/dlehman83) (1)
  - [fablabo](https://github.com/fablabo) (1)
  - [zerrac](https://github.com/zerrac) (1)
  - [loopodoopo](https://github.com/loopodoopo) (1)
  - [alakiza](https://github.com/alakiza) (1)
  - [yrebrac](https://github.com/yrebrac) (1)
  - [nkringle](https://github.com/nkringle) (1)
  - [ottorei](https://github.com/ottorei) (1)
  - [Senetus](https://github.com/Senetus) (1)
  - [WhippingBoy01](https://github.com/WhippingBoy01) (1)
  - [haydenseitz](https://github.com/haydenseitz) (1)
  - [admish](https://github.com/admish) (1)
  - [kedare](https://github.com/kedare) (1)
  - [ah9828](https://github.com/ah9828) (1)
  - [OahzEgroeg](https://github.com/OahzEgroeg) (1)
  - [Dmkaz](https://github.com/Dmkaz) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (36)
  - [murrant](https://github.com/murrant) (26)
  - [SourceDoctor](https://github.com/SourceDoctor) (4)
  - [f0o](https://github.com/f0o) (3)
  - [ottorei](https://github.com/ottorei) (1)
  - [laf](https://github.com/laf) (1)
  - [calinrigo](https://github.com/calinrigo) (1)

#### Feature
* Show Device Group on Map ([#12379](https://github.com/librenms/librenms/pull/12379)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Security
* Fix js injection issues in device overview ([#12475](https://github.com/librenms/librenms/pull/12475)) - [murrant](https://github.com/murrant)
* CVE-2020-35700 ([#12422](https://github.com/librenms/librenms/pull/12422)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Device
* Update enexus for "SmartPack S" ([#12465](https://github.com/librenms/librenms/pull/12465)) - [haydenseitz](https://github.com/haydenseitz)
* Added Firepower 4125 ([#12462](https://github.com/librenms/librenms/pull/12462)) - [WhippingBoy01](https://github.com/WhippingBoy01)
* Add Alcatel AOS7 bgpdescr & bgpprefix ([#12450](https://github.com/librenms/librenms/pull/12450)) - [paulierco](https://github.com/paulierco)
* Panduit PDU ([#12449](https://github.com/librenms/librenms/pull/12449)) - [Senetus](https://github.com/Senetus)
* Add sensors for Meinberg Lantime NTP-devices ([#12447](https://github.com/librenms/librenms/pull/12447)) - [ottorei](https://github.com/ottorei)
* Adds tripplight snmpwebcard support ([#12445](https://github.com/librenms/librenms/pull/12445)) - [nkringle](https://github.com/nkringle)
* Add mempools, cpu and storage to some arbor devices ([#12444](https://github.com/librenms/librenms/pull/12444)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Filter Juniper mempools ([#12443](https://github.com/librenms/librenms/pull/12443)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Add planet-pdu os device ([#12441](https://github.com/librenms/librenms/pull/12441)) - [paulierco](https://github.com/paulierco)
* Add  WTI POWER os device ([#12440](https://github.com/librenms/librenms/pull/12440)) - [jezekus](https://github.com/jezekus)
* Enumerate sensors under the Outlet for the entity-physical inventory ([#12439](https://github.com/librenms/librenms/pull/12439)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* Nokia sap graph ([#12432](https://github.com/librenms/librenms/pull/12432)) - [loopodoopo](https://github.com/loopodoopo)
* Apc epdu ([#12428](https://github.com/librenms/librenms/pull/12428)) - [zerrac](https://github.com/zerrac)
* Fix for Mikrotik SwOS discovery ([#12426](https://github.com/librenms/librenms/pull/12426)) - [jezekus](https://github.com/jezekus)
* New device OS: McafeeWebGateway ([#12418](https://github.com/librenms/librenms/pull/12418)) - [paulierco](https://github.com/paulierco)
* Make LibreNMS recognize Schleifenbauer model DPM27/E with existing OS definition ([#12414](https://github.com/librenms/librenms/pull/12414)) - [martijn-schmidt](https://github.com/martijn-schmidt)
* Update OPNsense version Regex, for _ releases ([#12407](https://github.com/librenms/librenms/pull/12407)) - [FingerlessGlov3s](https://github.com/FingerlessGlov3s)
* Change raspberry_pi_sensors state ([#12390](https://github.com/librenms/librenms/pull/12390)) - [mpikzink](https://github.com/mpikzink)
* Added support for Motorola and Thomson DOCSIS Cable Modems. ([#12386](https://github.com/librenms/librenms/pull/12386)) - [lukoramu](https://github.com/lukoramu)
* Check Point: Added HA state support ([#12382](https://github.com/librenms/librenms/pull/12382)) - [lazyb0nes](https://github.com/lazyb0nes)
* Add Zyxel IES 5206 and 5212 to supported Devices ([#12373](https://github.com/librenms/librenms/pull/12373)) - [Torch09](https://github.com/Torch09)
* Alcatel-Lucent support part2 ([#12369](https://github.com/librenms/librenms/pull/12369)) - [paulierco](https://github.com/paulierco)
* Eltek Enexus. Disable some battery sensors if no battery bank is installed at all. ([#12367](https://github.com/librenms/librenms/pull/12367)) - [vitalisator](https://github.com/vitalisator)
* New device os Raisecom Router OS (ROAP) ([#12361](https://github.com/librenms/librenms/pull/12361)) - [vitalisator](https://github.com/vitalisator)
* Freenas storage polling fix ([#12275](https://github.com/librenms/librenms/pull/12275)) - [Dmkaz](https://github.com/Dmkaz)

#### Webui
* Fix exception in device overview puppet widget ([#12474](https://github.com/librenms/librenms/pull/12474)) - [murrant](https://github.com/murrant)
* Support new lines in login_message again ([#12469](https://github.com/librenms/librenms/pull/12469)) - [efelon](https://github.com/efelon)
* Fix poller frequency display bug and warn ([#12466](https://github.com/librenms/librenms/pull/12466)) - [murrant](https://github.com/murrant)
* Remove unnecessary horizontal scroll bars in allert widgets ([#12464](https://github.com/librenms/librenms/pull/12464)) - [efelon](https://github.com/efelon)
* Reintroduce word wrapping to the custom login message ([#12460](https://github.com/librenms/librenms/pull/12460)) - [efelon](https://github.com/efelon)
* Add a button to reset port state history ([#12457](https://github.com/librenms/librenms/pull/12457)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Improving readability of tables on dark theme dashboards ([#12455](https://github.com/librenms/librenms/pull/12455)) - [efelon](https://github.com/efelon)
* Changed default param max_rows to increase widget count on dashboard ([#12438](https://github.com/librenms/librenms/pull/12438)) - [alakiza](https://github.com/alakiza)
* Fix percent bar text location ([#12406](https://github.com/librenms/librenms/pull/12406)) - [rk4an](https://github.com/rk4an)
* Don't show gelocation on snmp location string ([#12384](https://github.com/librenms/librenms/pull/12384)) - [SourceDoctor](https://github.com/SourceDoctor)
* Copy Dashboard to other User ([#11989](https://github.com/librenms/librenms/pull/11989)) - [SourceDoctor](https://github.com/SourceDoctor)
* Output image for graphs with no data ([#11865](https://github.com/librenms/librenms/pull/11865)) - [murrant](https://github.com/murrant)

#### Authentication
* Ldap auth handle no search more gracefully ([#12424](https://github.com/librenms/librenms/pull/12424)) - [murrant](https://github.com/murrant)

#### Api
* Fix oxidized API call when config is missing ([#12476](https://github.com/librenms/librenms/pull/12476)) - [murrant](https://github.com/murrant)
* Allow logs to be filtered by min/max id ([#12471](https://github.com/librenms/librenms/pull/12471)) - [kedare](https://github.com/kedare)

#### Bug
* Fix broken statement on auto discovery ([#12408](https://github.com/librenms/librenms/pull/12408)) - [djamp42](https://github.com/djamp42)
* Remove unused openssl_ver ([#12378](https://github.com/librenms/librenms/pull/12378)) - [murrant](https://github.com/murrant)
* Fix version compare ([#12376](https://github.com/librenms/librenms/pull/12376)) - [murrant](https://github.com/murrant)

#### Documentation
* Update Rancid.md ([#12487](https://github.com/librenms/librenms/pull/12487)) - [fablabo](https://github.com/fablabo)
* Creating Documentation page ([#12486](https://github.com/librenms/librenms/pull/12486)) - [yrebrac](https://github.com/yrebrac)
* Added missing / on internal link ([#12467](https://github.com/librenms/librenms/pull/12467)) - [admish](https://github.com/admish)
* Lnms link in /usr/bin ([#12446](https://github.com/librenms/librenms/pull/12446)) - [murrant](https://github.com/murrant)
* Update Documentation ([#12411](https://github.com/librenms/librenms/pull/12411)) - [dlehman83](https://github.com/dlehman83)
* Document flattened Inventory API function ([#12404](https://github.com/librenms/librenms/pull/12404)) - [richardlawley](https://github.com/richardlawley)
* Update docs for raspberry.sh ([#12389](https://github.com/librenms/librenms/pull/12389)) - [deveth0](https://github.com/deveth0)
* Update to incorporate new locking mechanisms ([#12388](https://github.com/librenms/librenms/pull/12388)) - [hanserasmus](https://github.com/hanserasmus)
* Update Distributed-Poller.md Discovery using dispatcher service ([#12387](https://github.com/librenms/librenms/pull/12387)) - [djamp42](https://github.com/djamp42)
* Doc - Increase Nginx Timeout ([#12368](https://github.com/librenms/librenms/pull/12368)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Tests
* Add test to check if os parameter matches filename ([#12442](https://github.com/librenms/librenms/pull/12442)) - [Jellyfrog](https://github.com/Jellyfrog)
* Remove Travis support ([#12416](https://github.com/librenms/librenms/pull/12416)) - [crazy-max](https://github.com/crazy-max)
* GitHub Actions dev:check ci ([#12392](https://github.com/librenms/librenms/pull/12392)) - [crazy-max](https://github.com/crazy-max)

#### Misc
* Cast REDIS_TIMEOUT to integer ([#12482](https://github.com/librenms/librenms/pull/12482)) - [OahzEgroeg](https://github.com/OahzEgroeg)
* Redis - Add scheme to allow TLS ([#12477](https://github.com/librenms/librenms/pull/12477)) - [ah9828](https://github.com/ah9828)

#### Dependencies
* Remove PHP8 blockers in LibreNMS ([#12451](https://github.com/librenms/librenms/pull/12451)) - [murrant](https://github.com/murrant)
* Bump laravel/framework from 8.21.0 to 8.22.1 ([#12448](https://github.com/librenms/librenms/pull/12448)) - [dependabot](https://github.com/apps/dependabot)
* Update php dependencies ([#12425](https://github.com/librenms/librenms/pull/12425)) - [murrant](https://github.com/murrant)
* Upgrade to Laravel Mix 6 ([#12421](https://github.com/librenms/librenms/pull/12421)) - [Jellyfrog](https://github.com/Jellyfrog)
* Bump axios from 0.19.2 to 0.21.1 ([#12420](https://github.com/librenms/librenms/pull/12420)) - [dependabot](https://github.com/apps/dependabot)
* Bump ini from 1.3.5 to 1.3.8 ([#12395](https://github.com/librenms/librenms/pull/12395)) - [dependabot](https://github.com/apps/dependabot)


## 1.70.0
*(2020-12-02)*

A big thank you to the following 27 contributors this last month:

  - [murrant](https://github.com/murrant) (17)
  - [SourceDoctor](https://github.com/SourceDoctor) (12)
  - [Jellyfrog](https://github.com/Jellyfrog) (11)
  - [ottorei](https://github.com/ottorei) (3)
  - [haydenseitz](https://github.com/haydenseitz) (2)
  - [hanserasmus](https://github.com/hanserasmus) (2)
  - [crazy-max](https://github.com/crazy-max) (2)
  - [hrtrd](https://github.com/hrtrd) (2)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [Olen](https://github.com/Olen) (1)
  - [jepke](https://github.com/jepke) (1)
  - [robje](https://github.com/robje) (1)
  - [keryazmi](https://github.com/keryazmi) (1)
  - [nightcore500](https://github.com/nightcore500) (1)
  - [ospfbgp](https://github.com/ospfbgp) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [joseUPV](https://github.com/joseUPV) (1)
  - [abrezinsky](https://github.com/abrezinsky) (1)
  - [epacke](https://github.com/epacke) (1)
  - [averzicco](https://github.com/averzicco) (1)
  - [walterav1984](https://github.com/walterav1984) (1)
  - [HalianElf](https://github.com/HalianElf) (1)
  - [MarlinMr](https://github.com/MarlinMr) (1)
  - [Senetus](https://github.com/Senetus) (1)
  - [Torch09](https://github.com/Torch09) (1)
  - [Cormoran96](https://github.com/Cormoran96) (1)
  - [deajan](https://github.com/deajan) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (33)
  - [Jellyfrog](https://github.com/Jellyfrog) (24)
  - [SourceDoctor](https://github.com/SourceDoctor) (3)
  - [PipoCanaja](https://github.com/PipoCanaja) (2)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (1)
  - [ibigbug](https://github.com/ibigbug) (1)

#### Feature
* Custom_descr Ports - configurable Icons ([#12331](https://github.com/librenms/librenms/pull/12331)) - [SourceDoctor](https://github.com/SourceDoctor)
* Faster initial database creation ([#12297](https://github.com/librenms/librenms/pull/12297)) - [murrant](https://github.com/murrant)
* Modernize mempools ([#12282](https://github.com/librenms/librenms/pull/12282)) - [murrant](https://github.com/murrant)
* Laravel 8.x Shift ([#12235](https://github.com/librenms/librenms/pull/12235)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Security
* Auth add ip to auth failure log entry for fail2ban ([#12374](https://github.com/librenms/librenms/pull/12374)) - [murrant](https://github.com/murrant)

#### Device
* New Logo Opnsense ([#12359](https://github.com/librenms/librenms/pull/12359)) - [Cormoran96](https://github.com/Cormoran96)
* Add Device "Eltek SmartPack2" to enexus definitions ([#12352](https://github.com/librenms/librenms/pull/12352)) - [Torch09](https://github.com/Torch09)
* SNR-ERD add support RSensor-H/T/P ([#12328](https://github.com/librenms/librenms/pull/12328)) - [hrtrd](https://github.com/hrtrd)
* Update regex for vCenter 7 ([#12316](https://github.com/librenms/librenms/pull/12316)) - [HalianElf](https://github.com/HalianElf)
* Add support new os SNR-ERD ([#12315](https://github.com/librenms/librenms/pull/12315)) - [hrtrd](https://github.com/hrtrd)
* Update Juniper's junos MIB-files ([#12313](https://github.com/librenms/librenms/pull/12313)) - [ottorei](https://github.com/ottorei)
* Ignore aos6 phantom fan ([#12303](https://github.com/librenms/librenms/pull/12303)) - [joseUPV](https://github.com/joseUPV)
* Fix bgp polling for BGP4-MIB devices ([#12301](https://github.com/librenms/librenms/pull/12301)) - [averzicco](https://github.com/averzicco)
* Adding Fortigate HA checks ([#12300](https://github.com/librenms/librenms/pull/12300)) - [epacke](https://github.com/epacke)
* Update F5 MIB-files ([#12296](https://github.com/librenms/librenms/pull/12296)) - [ottorei](https://github.com/ottorei)
* Add support for new Extreme VSP/VOSS models ([#12273](https://github.com/librenms/librenms/pull/12273)) - [ospfbgp](https://github.com/ospfbgp)
* Rittal CMC III: Add device support for PU and PU Compact ([#12268](https://github.com/librenms/librenms/pull/12268)) - [nightcore500](https://github.com/nightcore500)
* New device support named BTI SA-800 Series ([#12264](https://github.com/librenms/librenms/pull/12264)) - [keryazmi](https://github.com/keryazmi)
* Ubiquiti Edgepower battery values ([#12247](https://github.com/librenms/librenms/pull/12247)) - [jepke](https://github.com/jepke)
* Split VMware into seperate OS ([#12243](https://github.com/librenms/librenms/pull/12243)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Webui
* Fix nets setting can't add new ([#12341](https://github.com/librenms/librenms/pull/12341)) - [murrant](https://github.com/murrant)
* Global Settings - Discovery Network ([#12334](https://github.com/librenms/librenms/pull/12334)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix Device Dependency Count Limitation ([#12332](https://github.com/librenms/librenms/pull/12332)) - [SourceDoctor](https://github.com/SourceDoctor)
* SQL error with alerts search bar ([#12329](https://github.com/librenms/librenms/pull/12329)) - [ottorei](https://github.com/ottorei)
* Global setting ad domain ([#12326](https://github.com/librenms/librenms/pull/12326)) - [SourceDoctor](https://github.com/SourceDoctor)
* Mark acknowledged Alerts in Alert Rule List ([#12320](https://github.com/librenms/librenms/pull/12320)) - [SourceDoctor](https://github.com/SourceDoctor)
* Sort Alert Transport by Name ([#12318](https://github.com/librenms/librenms/pull/12318)) - [SourceDoctor](https://github.com/SourceDoctor)
* OSPF View Open/Close Button ([#12311](https://github.com/librenms/librenms/pull/12311)) - [SourceDoctor](https://github.com/SourceDoctor)
* Interface description types to Global Settings ([#12291](https://github.com/librenms/librenms/pull/12291)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix Network Map Device Group Highlighting ([#12290](https://github.com/librenms/librenms/pull/12290)) - [SourceDoctor](https://github.com/SourceDoctor)
* Show associated Alert Rules on Alert Templates ([#12259](https://github.com/librenms/librenms/pull/12259)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Api
* Always return json for api requests ([#12335](https://github.com/librenms/librenms/pull/12335)) - [murrant](https://github.com/murrant)
* API - Allow Hostname on add/remove Device Dependencies ([#12319](https://github.com/librenms/librenms/pull/12319)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Alerting
* Added expiration for alerts cache::lock() ([#12375](https://github.com/librenms/librenms/pull/12375)) - [haydenseitz](https://github.com/haydenseitz)
* Added devices unpolled alert rule template ([#12321](https://github.com/librenms/librenms/pull/12321)) - [Senetus](https://github.com/Senetus)
* Use a proxy server for the PagerDuty transport. ([#12294](https://github.com/librenms/librenms/pull/12294)) - [abrezinsky](https://github.com/abrezinsky)

#### Polling
* Add OSPF cost (TOS) ([#11929](https://github.com/librenms/librenms/pull/11929)) - [haydenseitz](https://github.com/haydenseitz)

#### Bug
* Vminfo bugs ([#12344](https://github.com/librenms/librenms/pull/12344)) - [Jellyfrog](https://github.com/Jellyfrog)
* Do not run validate.php as root ([#12327](https://github.com/librenms/librenms/pull/12327)) - [murrant](https://github.com/murrant)
* Fix model observer registering multiple times ([#12323](https://github.com/librenms/librenms/pull/12323)) - [murrant](https://github.com/murrant)
* Fix customers_descr config definition ([#12310](https://github.com/librenms/librenms/pull/12310)) - [murrant](https://github.com/murrant)
* Only add new outage entry if Device status changed ([#12309](https://github.com/librenms/librenms/pull/12309)) - [SourceDoctor](https://github.com/SourceDoctor)
* Remove incorrect mib_dir ([#12306](https://github.com/librenms/librenms/pull/12306)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Refactor
* Convert Virtual Machine pages to Laravel ([#12287](https://github.com/librenms/librenms/pull/12287)) - [Jellyfrog](https://github.com/Jellyfrog)
* VRP NAC polling optimisation ([#12279](https://github.com/librenms/librenms/pull/12279)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Cleanup
* Fix disabling of built-in commands when using ./lnms ([#12308](https://github.com/librenms/librenms/pull/12308)) - [Jellyfrog](https://github.com/Jellyfrog)
* Remove some unused files ([#12307](https://github.com/librenms/librenms/pull/12307)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update to Composer 2 ([#12263](https://github.com/librenms/librenms/pull/12263)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Documentation
* Fix php min version in the Docs ([#12372](https://github.com/librenms/librenms/pull/12372)) - [hanserasmus](https://github.com/hanserasmus)
* Remove hardcoded URLs to doc ([#12364](https://github.com/librenms/librenms/pull/12364)) - [crazy-max](https://github.com/crazy-max)
* Fix bad link in docs ([#12357](https://github.com/librenms/librenms/pull/12357)) - [murrant](https://github.com/murrant)
* Update Auto-Discovery.md ([#12317](https://github.com/librenms/librenms/pull/12317)) - [MarlinMr](https://github.com/MarlinMr)
* Update distributed poller documentation ([#12312](https://github.com/librenms/librenms/pull/12312)) - [hanserasmus](https://github.com/hanserasmus)
* Extend Debian instructions with Raspberry Pi OS ([#12302](https://github.com/librenms/librenms/pull/12302)) - [walterav1984](https://github.com/walterav1984)

#### Misc
* GitHub Actions ([#12353](https://github.com/librenms/librenms/pull/12353)) - [crazy-max](https://github.com/crazy-max)
* Add system validation ([#12337](https://github.com/librenms/librenms/pull/12337)) - [murrant](https://github.com/murrant)
* Distributed Poller improved validation ([#12269](https://github.com/librenms/librenms/pull/12269)) - [murrant](https://github.com/murrant)
* IRC Adding floodcontrol. Better alerts ([#12141](https://github.com/librenms/librenms/pull/12141)) - [Olen](https://github.com/Olen)

#### Dependencies
* Bump dot-prop from 4.2.0 to 4.2.1 ([#12289](https://github.com/librenms/librenms/pull/12289)) - [dependabot](https://github.com/apps/dependabot)
* Bump to PHP 7.3 minimum ([#12288](https://github.com/librenms/librenms/pull/12288)) - [Jellyfrog](https://github.com/Jellyfrog)


## 1.69
*(2020-11-01)*

A big thank you to the following 34 contributors this last month:

  - [Jellyfrog](https://github.com/Jellyfrog) (23)
  - [murrant](https://github.com/murrant) (23)
  - [PipoCanaja](https://github.com/PipoCanaja) (7)
  - [SourceDoctor](https://github.com/SourceDoctor) (4)
  - [dagbdagb](https://github.com/dagbdagb) (3)
  - [ottorei](https://github.com/ottorei) (2)
  - [hanserasmus](https://github.com/hanserasmus) (2)
  - [bakerds](https://github.com/bakerds) (2)
  - [willhseitz](https://github.com/willhseitz) (2)
  - [robje](https://github.com/robje) (2)
  - [Olen](https://github.com/Olen) (2)
  - [gerhardqux](https://github.com/gerhardqux) (1)
  - [diegocanton](https://github.com/diegocanton) (1)
  - [Negatifff](https://github.com/Negatifff) (1)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (1)
  - [avinash403](https://github.com/avinash403) (1)
  - [rkojedzinszky](https://github.com/rkojedzinszky) (1)
  - [lowinger42](https://github.com/lowinger42) (1)
  - [FingerlessGlov3s](https://github.com/FingerlessGlov3s) (1)
  - [tim427](https://github.com/tim427) (1)
  - [mjeffin](https://github.com/mjeffin) (1)
  - [guipoletto](https://github.com/guipoletto) (1)
  - [DerTFL](https://github.com/DerTFL) (1)
  - [gil-obradors](https://github.com/gil-obradors) (1)
  - [clarkchentw](https://github.com/clarkchentw) (1)
  - [h-barnhart](https://github.com/h-barnhart) (1)
  - [CameronMunroe](https://github.com/CameronMunroe) (1)
  - [ibigbug](https://github.com/ibigbug) (1)
  - [corsoblaster](https://github.com/corsoblaster) (1)
  - [dorkmatt](https://github.com/dorkmatt) (1)
  - [MarlinMr](https://github.com/MarlinMr) (1)
  - [bofh80](https://github.com/bofh80) (1)
  - [p4k8](https://github.com/p4k8) (1)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (36)
  - [murrant](https://github.com/murrant) (34)
  - [SourceDoctor](https://github.com/SourceDoctor) (17)
  - [PipoCanaja](https://github.com/PipoCanaja) (4)

#### Feature
* Optional availability calculation mode to allow planned maintenance ([#12218](https://github.com/librenms/librenms/pull/12218)) - [ottorei](https://github.com/ottorei)
* Yaml support to translate sysObjectID to get hardware ([#12187](https://github.com/librenms/librenms/pull/12187)) - [murrant](https://github.com/murrant)
* IRC Add simple tag-parsing of colors and highlights in alerts ([#12138](https://github.com/librenms/librenms/pull/12138)) - [Olen](https://github.com/Olen)
* Add refresh in widget settings ([#12127](https://github.com/librenms/librenms/pull/12127)) - [Negatifff](https://github.com/Negatifff)
* Added new authalgo support for SNMPv3 ([#11966](https://github.com/librenms/librenms/pull/11966)) - [hanserasmus](https://github.com/hanserasmus)

#### Security
* Remove legacy password algorithms and move to Laravel standard ([#12252](https://github.com/librenms/librenms/pull/12252)) - [Jellyfrog](https://github.com/Jellyfrog)
* Validate dashboard id ([#12219](https://github.com/librenms/librenms/pull/12219)) - [murrant](https://github.com/murrant)
* Fix SQL injection vulnerability in MAC Accounting graph ([#12204](https://github.com/librenms/librenms/pull/12204)) - [murrant](https://github.com/murrant)

#### Device
* Basic support for Sophos xg v18 ([#12251](https://github.com/librenms/librenms/pull/12251)) - [corsoblaster](https://github.com/corsoblaster)
* Convert VyOS to yaml ([#12231](https://github.com/librenms/librenms/pull/12231)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix wrong Zyxel GS1900 Q-BRIDGE replies (fdb-table) ([#12230](https://github.com/librenms/librenms/pull/12230)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add supermicro SVG logo ([#12222](https://github.com/librenms/librenms/pull/12222)) - [gil-obradors](https://github.com/gil-obradors)
* Fix Oki LAN OS info ([#12213](https://github.com/librenms/librenms/pull/12213)) - [murrant](https://github.com/murrant)
* Initial support for DC-UPS-48 from algcom ([#12209](https://github.com/librenms/librenms/pull/12209)) - [guipoletto](https://github.com/guipoletto)
* Put all Eaton mibs in the same subdir, update mibs, update YAML-files ([#12197](https://github.com/librenms/librenms/pull/12197)) - [dagbdagb](https://github.com/dagbdagb)
* Add a bunch more Brocade Ironware devices ([#12191](https://github.com/librenms/librenms/pull/12191)) - [robje](https://github.com/robje)
* Add opengear hardware discovery ([#12189](https://github.com/librenms/librenms/pull/12189)) - [murrant](https://github.com/murrant)
* All os detection now uses Yaml ([#12186](https://github.com/librenms/librenms/pull/12186)) - [murrant](https://github.com/murrant)
* New device: Rohde & Schwarz Sx800 ([#12181](https://github.com/librenms/librenms/pull/12181)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Add support for Supermicro hardware/serial discovery ([#12176](https://github.com/librenms/librenms/pull/12176)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add FreshTomato OS definition which is fork of tomato ([#12173](https://github.com/librenms/librenms/pull/12173)) - [FingerlessGlov3s](https://github.com/FingerlessGlov3s)
* Ciena SAOS 8 device improvements ([#12172](https://github.com/librenms/librenms/pull/12172)) - [bakerds](https://github.com/bakerds)
* Make Eaton Gigabit Network Card (AKA M2) a separate OS ([#12156](https://github.com/librenms/librenms/pull/12156)) - [dagbdagb](https://github.com/dagbdagb)
* New sensors for siteboss OS + new sensor type: percentage (%) ([#11958](https://github.com/librenms/librenms/pull/11958)) - [willhseitz](https://github.com/willhseitz)

#### Webui
* Fixed display "Power Status" for libvirt vm's ([#12283](https://github.com/librenms/librenms/pull/12283)) - [DerTFL](https://github.com/DerTFL)
* Allow pre-formatted logon message ([#12281](https://github.com/librenms/librenms/pull/12281)) - [murrant](https://github.com/murrant)
* Fix - only get shortlabel from vlan if it has ports ([#12267](https://github.com/librenms/librenms/pull/12267)) - [SourceDoctor](https://github.com/SourceDoctor)
* Webui - Corrected swapped ul/dl Max Rate ([#12255](https://github.com/librenms/librenms/pull/12255)) - [PipoCanaja](https://github.com/PipoCanaja)
* Permissions query fixes ([#12220](https://github.com/librenms/librenms/pull/12220)) - [murrant](https://github.com/murrant)
* Health Overlib Correction ([#12203](https://github.com/librenms/librenms/pull/12203)) - [SourceDoctor](https://github.com/SourceDoctor)
* Additional fix for Cisco Crossbar overview missing ([#12185](https://github.com/librenms/librenms/pull/12185)) - [lowinger42](https://github.com/lowinger42)
* Convert Device\>vlan view to Laravel ([#12163](https://github.com/librenms/librenms/pull/12163)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add Blade Submenu component ([#12159](https://github.com/librenms/librenms/pull/12159)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Graphs
* Add two day period to graph widget ([#12210](https://github.com/librenms/librenms/pull/12210)) - [murrant](https://github.com/murrant)

#### Applications
* Bug - Typo in bind application polling ([#12276](https://github.com/librenms/librenms/pull/12276)) - [PipoCanaja](https://github.com/PipoCanaja)
* Force lower case variable $unbound[] - Unbound polling ([#12178](https://github.com/librenms/librenms/pull/12178)) - [diegocanton](https://github.com/diegocanton)

#### Api
* Api functions - device_availability device_outages quick fix ([#12270](https://github.com/librenms/librenms/pull/12270)) - [bofh80](https://github.com/bofh80)

#### Alerting
* Show response on error ([#12228](https://github.com/librenms/librenms/pull/12228)) - [ibigbug](https://github.com/ibigbug)

#### Polling
* Update DSL stats even if port is down ([#12262](https://github.com/librenms/librenms/pull/12262)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix dispatcher crash on restart ([#12257](https://github.com/librenms/librenms/pull/12257)) - [murrant](https://github.com/murrant)
* Allow nullable ospf auth ([#12249](https://github.com/librenms/librenms/pull/12249)) - [willhseitz](https://github.com/willhseitz)

#### Bug
* Fix permissions bug in IRC ([#12266](https://github.com/librenms/librenms/pull/12266)) - [murrant](https://github.com/murrant)
* Snmpwalk_cache_oid() handle multiline strings ([#12254](https://github.com/librenms/librenms/pull/12254)) - [murrant](https://github.com/murrant)
* Fix sql query syntax error ([#12248](https://github.com/librenms/librenms/pull/12248)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix device url ([#12234](https://github.com/librenms/librenms/pull/12234)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add space between manufacturer and hardware ([#12233](https://github.com/librenms/librenms/pull/12233)) - [Jellyfrog](https://github.com/Jellyfrog)
* Move device exist check to prevent error ([#12232](https://github.com/librenms/librenms/pull/12232)) - [Jellyfrog](https://github.com/Jellyfrog)
* Bug - Check group os file before inclusion ([#12227](https://github.com/librenms/librenms/pull/12227)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix return value on deleting a Device Group ([#12225](https://github.com/librenms/librenms/pull/12225)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix typo cause empty data for NFS Server ([#12223](https://github.com/librenms/librenms/pull/12223)) - [clarkchentw](https://github.com/clarkchentw)
* Update only latest Null Value Row in Outages Table ([#12206](https://github.com/librenms/librenms/pull/12206)) - [ottorei](https://github.com/ottorei)
* Ircbot fix ([#12192](https://github.com/librenms/librenms/pull/12192)) - [robje](https://github.com/robje)
* Fix Linux OS mib_dir usage ([#12190](https://github.com/librenms/librenms/pull/12190)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix lnms update command ([#12182](https://github.com/librenms/librenms/pull/12182)) - [murrant](https://github.com/murrant)
* Do not remove users with API tokens ([#12162](https://github.com/librenms/librenms/pull/12162)) - [gerhardqux](https://github.com/gerhardqux)
* IRC fix joining alert channel(s) ([#12160](https://github.com/librenms/librenms/pull/12160)) - [Olen](https://github.com/Olen)
* Fix midnight poller data loss ([#11582](https://github.com/librenms/librenms/pull/11582)) - [TheMysteriousX](https://github.com/TheMysteriousX)

#### Refactor
* Cleanup generate_sensor_link ([#12154](https://github.com/librenms/librenms/pull/12154)) - [SourceDoctor](https://github.com/SourceDoctor)
* Support multiple daily process locking backends with distributed polling ([#11896](https://github.com/librenms/librenms/pull/11896)) - [murrant](https://github.com/murrant)

#### Documentation
* Update SNMP-Configuration-Examples.md ([#12265](https://github.com/librenms/librenms/pull/12265)) - [MarlinMr](https://github.com/MarlinMr)
* Clarify non-x86 hardware info sources for snmpd ([#12253](https://github.com/librenms/librenms/pull/12253)) - [dorkmatt](https://github.com/dorkmatt)
* Improve Services documentation ([#12226](https://github.com/librenms/librenms/pull/12226)) - [CameronMunroe](https://github.com/CameronMunroe)
* Added instructions for rsyslog version 8 ([#12224](https://github.com/librenms/librenms/pull/12224)) - [h-barnhart](https://github.com/h-barnhart)
* Fixed the IRC-Bot Extensions link ([#12216](https://github.com/librenms/librenms/pull/12216)) - [tim427](https://github.com/tim427)
* Add table of content to installation guide ([#12202](https://github.com/librenms/librenms/pull/12202)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update Install-LibreNMS.md ([#12201](https://github.com/librenms/librenms/pull/12201)) - [mjeffin](https://github.com/mjeffin)
* Add docs for Supermicro Superdoctor ([#12200](https://github.com/librenms/librenms/pull/12200)) - [Jellyfrog](https://github.com/Jellyfrog)
* Updated linux snmpd.conf example ([#12195](https://github.com/librenms/librenms/pull/12195)) - [murrant](https://github.com/murrant)
* Ignore changelog in docs search ([#12194](https://github.com/librenms/librenms/pull/12194)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add missing mkdocs extension ([#12193](https://github.com/librenms/librenms/pull/12193)) - [Jellyfrog](https://github.com/Jellyfrog)
* Icecast doc correction ([#12183](https://github.com/librenms/librenms/pull/12183)) - [avinash403](https://github.com/avinash403)
* Suggest Fast Ping before 1 Minute Polling ([#12179](https://github.com/librenms/librenms/pull/12179)) - [murrant](https://github.com/murrant)
* Update Initial-Detection.md ([#12174](https://github.com/librenms/librenms/pull/12174)) - [bakerds](https://github.com/bakerds)
* Update Example-Hardware-Setup.md ([#12170](https://github.com/librenms/librenms/pull/12170)) - [dagbdagb](https://github.com/dagbdagb)
* Don't index changelogs ([#12166](https://github.com/librenms/librenms/pull/12166)) - [murrant](https://github.com/murrant)

#### Tests
* Capture OSPF test data ([#12215](https://github.com/librenms/librenms/pull/12215)) - [murrant](https://github.com/murrant)
* Scheduled maintenance test ([#12171](https://github.com/librenms/librenms/pull/12171)) - [murrant](https://github.com/murrant)

#### Misc
* Apply fixes from StyleCI ([#12285](https://github.com/librenms/librenms/pull/12285)) - [Jellyfrog](https://github.com/Jellyfrog)
* Change of default .pdf font ([#12278](https://github.com/librenms/librenms/pull/12278)) - [p4k8](https://github.com/p4k8)
* Accommodate upcoming php 7.3 change ([#12180](https://github.com/librenms/librenms/pull/12180)) - [hanserasmus](https://github.com/hanserasmus)
* Add missing primary keys ([#12106](https://github.com/librenms/librenms/pull/12106)) - [rkojedzinszky](https://github.com/rkojedzinszky)

#### Dependencies
* Update PHP dependencies ([#12169](https://github.com/librenms/librenms/pull/12169)) - [murrant](https://github.com/murrant)


## 1.68
*(2020-09-29)*

A big thank you to the following 44 contributors this last month:

  - [murrant](https://github.com/murrant) (14)
  - [Jellyfrog](https://github.com/Jellyfrog) (12)
  - [Olen](https://github.com/Olen) (6)
  - [SourceDoctor](https://github.com/SourceDoctor) (4)
  - [craig-nokia](https://github.com/craig-nokia) (4)
  - [PipoCanaja](https://github.com/PipoCanaja) (3)
  - [nathanshiaulam](https://github.com/nathanshiaulam) (2)
  - [opalivan](https://github.com/opalivan) (2)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (2)
  - [Negatifff](https://github.com/Negatifff) (2)
  - [magnuslarsen](https://github.com/magnuslarsen) (1)
  - [jozefrebjak](https://github.com/jozefrebjak) (1)
  - [dupondje](https://github.com/dupondje) (1)
  - [nightcore500](https://github.com/nightcore500) (1)
  - [cmarmonier](https://github.com/cmarmonier) (1)
  - [crcro](https://github.com/crcro) (1)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (1)
  - [sjtarik](https://github.com/sjtarik) (1)
  - [thomcatdotrocks](https://github.com/thomcatdotrocks) (1)
  - [teunvink](https://github.com/teunvink) (1)
  - [arrmo](https://github.com/arrmo) (1)
  - [jasoncheng7115](https://github.com/jasoncheng7115) (1)
  - [QuadPiece](https://github.com/QuadPiece) (1)
  - [avinash403](https://github.com/avinash403) (1)
  - [pobradovic08](https://github.com/pobradovic08) (1)
  - [q7joey](https://github.com/q7joey) (1)
  - [ospfbgp](https://github.com/ospfbgp) (1)
  - [n-lyakhovoy](https://github.com/n-lyakhovoy) (1)
  - [BirkirFreyr](https://github.com/BirkirFreyr) (1)
  - [hugalafutro](https://github.com/hugalafutro) (1)
  - [dagbdagb](https://github.com/dagbdagb) (1)
  - [cliffalbert](https://github.com/cliffalbert) (1)
  - [deajan](https://github.com/deajan) (1)
  - [hanserasmus](https://github.com/hanserasmus) (1)
  - [bestlong](https://github.com/bestlong) (1)
  - [louis-oui](https://github.com/louis-oui) (1)
  - [bekreyev](https://github.com/bekreyev) (1)
  - [kvedder-amplex](https://github.com/kvedder-amplex) (1)
  - [damonreed](https://github.com/damonreed) (1)
  - [Blorpy](https://github.com/Blorpy) (1)
  - [h-barnhart](https://github.com/h-barnhart) (1)
  - [kanokc](https://github.com/kanokc) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [CirnoT](https://github.com/CirnoT) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (52)
  - [murrant](https://github.com/murrant) (20)
  - [PipoCanaja](https://github.com/PipoCanaja) (8)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (1)
  - [Negatifff](https://github.com/Negatifff) (1)

#### Feature
* Add functionality to use snmp-scan.py to add to specific poller group ([#12029](https://github.com/librenms/librenms/pull/12029)) - [nathanshiaulam](https://github.com/nathanshiaulam)

#### Device
* Fix edgeswitch regex ([#12168](https://github.com/librenms/librenms/pull/12168)) - [Jellyfrog](https://github.com/Jellyfrog)
* UBNT switch discovery issue-12133 ([#12167](https://github.com/librenms/librenms/pull/12167)) - [kanokc](https://github.com/kanokc)
* Improve DNOS fallback os info ([#12165](https://github.com/librenms/librenms/pull/12165)) - [murrant](https://github.com/murrant)
* Windows detect newer versions ([#12164](https://github.com/librenms/librenms/pull/12164)) - [murrant](https://github.com/murrant)
* Fix older ProCurve hardware/version ([#12155](https://github.com/librenms/librenms/pull/12155)) - [murrant](https://github.com/murrant)
* Eaton mgeups fixes ([#12150](https://github.com/librenms/librenms/pull/12150)) - [dagbdagb](https://github.com/dagbdagb)
* Adding basic support for Symertricom-Microsemi SSU2000 ([#12145](https://github.com/librenms/librenms/pull/12145)) - [craig-nokia](https://github.com/craig-nokia)
* TAIT - Chassis, Software and Serial detection ([#12131](https://github.com/librenms/librenms/pull/12131)) - [opalivan](https://github.com/opalivan)
* Mistake in ddmDiagnosisRXPower OID ([#12114](https://github.com/librenms/librenms/pull/12114)) - [n-lyakhovoy](https://github.com/n-lyakhovoy)
* Add NoBulk Option for Sitemonitor ([#12100](https://github.com/librenms/librenms/pull/12100)) - [kvedder-amplex](https://github.com/kvedder-amplex)
* Convert OS discovery to new style ([#12099](https://github.com/librenms/librenms/pull/12099)) - [murrant](https://github.com/murrant)
* Device support for Tait Infra93 ([#12093](https://github.com/librenms/librenms/pull/12093)) - [opalivan](https://github.com/opalivan)
* Add detection of Cisco ftd 4115 ([#12092](https://github.com/librenms/librenms/pull/12092)) - [Blorpy](https://github.com/Blorpy)
* Add basic support for Emerson Netsure Controllers ([#12091](https://github.com/librenms/librenms/pull/12091)) - [craig-nokia](https://github.com/craig-nokia)
* New OS: Ekinops ([#12088](https://github.com/librenms/librenms/pull/12088)) - [h-barnhart](https://github.com/h-barnhart)
* Add basic support for GE Galaxy Pulsar Plus Controllers ([#12087](https://github.com/librenms/librenms/pull/12087)) - [craig-nokia](https://github.com/craig-nokia)
* Add Sensatronic E4-16 support ([#12083](https://github.com/librenms/librenms/pull/12083)) - [q7joey](https://github.com/q7joey)
* Add support for VOSS switches VSP-4900-12MXU-12XE, XA1440, and XA1480 ([#12072](https://github.com/librenms/librenms/pull/12072)) - [ospfbgp](https://github.com/ospfbgp)
* Added Migrating... state ([#12071](https://github.com/librenms/librenms/pull/12071)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Added Nokia timos SFP RX/TX dbm sensors ([#12065](https://github.com/librenms/librenms/pull/12065)) - [craig-nokia](https://github.com/craig-nokia)
* Add support for Beagleboard ([#12060](https://github.com/librenms/librenms/pull/12060)) - [arrmo](https://github.com/arrmo)
* Fix atenpdu sensors ([#12055](https://github.com/librenms/librenms/pull/12055)) - [murrant](https://github.com/murrant)
* Adding serial number discovery to aruba os poller ([#12053](https://github.com/librenms/librenms/pull/12053)) - [sjtarik](https://github.com/sjtarik)
* Osnexus quantastor initial support ([#12045](https://github.com/librenms/librenms/pull/12045)) - [crcro](https://github.com/crcro)
* Ifotec product integration ([#12038](https://github.com/librenms/librenms/pull/12038)) - [cmarmonier](https://github.com/cmarmonier)
* Fix incorrect eth0 status for Ubiquiti AirFiber 5XHD ([#12025](https://github.com/librenms/librenms/pull/12025)) - [nightcore500](https://github.com/nightcore500)
* Synology DSM: Moved to yaml discovery ([#11962](https://github.com/librenms/librenms/pull/11962)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Added support for OS detection from NXOS and UCS ([#11722](https://github.com/librenms/librenms/pull/11722)) - [magnuslarsen](https://github.com/magnuslarsen)
* Updated Support of Smartax GPON OLT ([#11719](https://github.com/librenms/librenms/pull/11719)) - [jozefrebjak](https://github.com/jozefrebjak)

#### Webui
* Fix editing schedule maintenance ([#12079](https://github.com/librenms/librenms/pull/12079)) - [louis-oui](https://github.com/louis-oui)
* Replace VyOS' .png with .svg ([#12067](https://github.com/librenms/librenms/pull/12067)) - [QuadPiece](https://github.com/QuadPiece)
* Fixed URL of detailed graphs for jitter SLA probe ([#11984](https://github.com/librenms/librenms/pull/11984)) - [pobradovic08](https://github.com/pobradovic08)
* Speedup vlans list in device VLANS tab ([#11805](https://github.com/librenms/librenms/pull/11805)) - [Negatifff](https://github.com/Negatifff)

#### Snmp Traps
* More eventlogs for snmptraps ([#12112](https://github.com/librenms/librenms/pull/12112)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Applications
* Fix queries RRD graph. ([#12152](https://github.com/librenms/librenms/pull/12152)) - [hugalafutro](https://github.com/hugalafutro)
* Icecast, Opensips and Voip monitor Application ([#12070](https://github.com/librenms/librenms/pull/12070)) - [avinash403](https://github.com/avinash403)

#### Api
* API Calls to list Device Outages, calculated Availability ([#12103](https://github.com/librenms/librenms/pull/12103)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Billing
* Fix billing showing estimated transfer based on total of 1 days if billing day is first ([#10445](https://github.com/librenms/librenms/pull/10445)) - [CirnoT](https://github.com/CirnoT)

#### Discovery
* Fix skip_value_lt plurality confusion ([#12056](https://github.com/librenms/librenms/pull/12056)) - [murrant](https://github.com/murrant)

#### Polling
* Include ifName for bad_ifXEntry OS ([#12104](https://github.com/librenms/librenms/pull/12104)) - [murrant](https://github.com/murrant)

#### Rancid
* Add paloalto panos to gen_rancid.php ([#12161](https://github.com/librenms/librenms/pull/12161)) - [BirkirFreyr](https://github.com/BirkirFreyr)
* Add support for H3C/Comware ([#12144](https://github.com/librenms/librenms/pull/12144)) - [cliffalbert](https://github.com/cliffalbert)
* Since Rancid 3, the separator is ; ([#11688](https://github.com/librenms/librenms/pull/11688)) - [dupondje](https://github.com/dupondje)

#### Bug
* Make sure 1st admin user creation does not fail with error 500 ([#12119](https://github.com/librenms/librenms/pull/12119)) - [deajan](https://github.com/deajan)
* Fix to show Routing Count in Pagemenu Selector ([#12111](https://github.com/librenms/librenms/pull/12111)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix CiHelper function checkPythonExec to use 'pip3 install --user' and improve error message ([#12097](https://github.com/librenms/librenms/pull/12097)) - [damonreed](https://github.com/damonreed)
* Fix arp-search search ([#12075](https://github.com/librenms/librenms/pull/12075)) - [Jellyfrog](https://github.com/Jellyfrog)
* Improve mib_dir setting consistency ([#12069](https://github.com/librenms/librenms/pull/12069)) - [murrant](https://github.com/murrant)
* Matrix txnid fix ([#12057](https://github.com/librenms/librenms/pull/12057)) - [thomcatdotrocks](https://github.com/thomcatdotrocks)

#### Refactor
* Allow index rename migration to recover partially completed previous run ([#12084](https://github.com/librenms/librenms/pull/12084)) - [murrant](https://github.com/murrant)
* Rewrite smokeping script to be an lnms command ([#11585](https://github.com/librenms/librenms/pull/11585)) - [TheMysteriousX](https://github.com/TheMysteriousX)

#### Documentation
* Update Application Docs for Unbound ([#12151](https://github.com/librenms/librenms/pull/12151)) - [SourceDoctor](https://github.com/SourceDoctor)
* Use lnms dev:check instead of pre-commit.php ([#12149](https://github.com/librenms/librenms/pull/12149)) - [murrant](https://github.com/murrant)
* ARP endpoint can search by MAC ([#12129](https://github.com/librenms/librenms/pull/12129)) - [murrant](https://github.com/murrant)
* Update Oxidized docs ([#12116](https://github.com/librenms/librenms/pull/12116)) - [hanserasmus](https://github.com/hanserasmus)
* Begins with / Ends with, SQL example is reverse ([#12113](https://github.com/librenms/librenms/pull/12113)) - [bestlong](https://github.com/bestlong)
* MySQL Documentation Update ([#12085](https://github.com/librenms/librenms/pull/12085)) - [SourceDoctor](https://github.com/SourceDoctor)
* Update Distributed-Poller.md ([#12074](https://github.com/librenms/librenms/pull/12074)) - [nathanshiaulam](https://github.com/nathanshiaulam)
* Remove broken link ([#12059](https://github.com/librenms/librenms/pull/12059)) - [teunvink](https://github.com/teunvink)

#### Translation
* Update ru.json ([#12109](https://github.com/librenms/librenms/pull/12109)) - [bekreyev](https://github.com/bekreyev)

#### Misc
* New index in alert_log table ([#12143](https://github.com/librenms/librenms/pull/12143)) - [Negatifff](https://github.com/Negatifff)
* IRC Add more logging and debug info ([#12140](https://github.com/librenms/librenms/pull/12140)) - [Olen](https://github.com/Olen)
* Don't set nick on each tick ([#12139](https://github.com/librenms/librenms/pull/12139)) - [Olen](https://github.com/Olen)
* List external commands in help. Allow reload of external commands ([#12137](https://github.com/librenms/librenms/pull/12137)) - [Olen](https://github.com/Olen)
* Increase the read-buffer as 64 bytes can be a little small ([#12136](https://github.com/librenms/librenms/pull/12136)) - [Olen](https://github.com/Olen)
* Remove unused "irc_chan". Renamed to "irc_alert_chan" ([#12135](https://github.com/librenms/librenms/pull/12135)) - [Olen](https://github.com/Olen)
* Add some variables to the init of the class ([#12134](https://github.com/librenms/librenms/pull/12134)) - [Olen](https://github.com/Olen)
* Cast device_id to int to prevent type error in deviceCache::get() ([#12076](https://github.com/librenms/librenms/pull/12076)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add attach sysName to Prometheus. ([#12061](https://github.com/librenms/librenms/pull/12061)) - [jasoncheng7115](https://github.com/jasoncheng7115)

#### Dependencies
* Set PHP 7.3 as minimum supported version ([#12118](https://github.com/librenms/librenms/pull/12118)) - [Jellyfrog](https://github.com/Jellyfrog)
* Bump http-proxy from 1.18.0 to 1.18.1 ([#12081](https://github.com/librenms/librenms/pull/12081)) - [dependabot](https://github.com/apps/dependabot)


## 1.67
*(2020-09-03)*

A big thank you to the following 23 contributors this last month:

  - [Jellyfrog](https://github.com/Jellyfrog) (10)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (5)
  - [hanserasmus](https://github.com/hanserasmus) (5)
  - [SourceDoctor](https://github.com/SourceDoctor) (5)
  - [murrant](https://github.com/murrant) (4)
  - [Negatifff](https://github.com/Negatifff) (2)
  - [bofh80](https://github.com/bofh80) (2)
  - [pobradovic08](https://github.com/pobradovic08) (2)
  - [arrmo](https://github.com/arrmo) (2)
  - [dependabot](https://github.com/apps/dependabot) (2)
  - [jozefrebjak](https://github.com/jozefrebjak) (1)
  - [Jarod2801](https://github.com/Jarod2801) (1)
  - [Zmegolaz](https://github.com/Zmegolaz) (1)
  - [Najihel](https://github.com/Najihel) (1)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (1)
  - [craig-nokia](https://github.com/craig-nokia) (1)
  - [sprich96](https://github.com/sprich96) (1)
  - [raphael247](https://github.com/raphael247) (1)
  - [nathanshiaulam](https://github.com/nathanshiaulam) (1)
  - [m4rcu5](https://github.com/m4rcu5) (1)
  - [nbyers](https://github.com/nbyers) (1)
  - [rjmidau](https://github.com/rjmidau) (1)
  - [nightcore500](https://github.com/nightcore500) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (27)
  - [murrant](https://github.com/murrant) (14)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (5)
  - [SourceDoctor](https://github.com/SourceDoctor) (4)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [rigocalin](https://github.com/rigocalin) (1)
  - [Cormoran96](https://github.com/Cormoran96) (1)

#### Feature
* Show Device Outages as Log List ([#12011](https://github.com/librenms/librenms/pull/12011)) - [SourceDoctor](https://github.com/SourceDoctor)
* Availability Calculation for all Devices ([#12004](https://github.com/librenms/librenms/pull/12004)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Device
* Detect additional TrueNAS hardware types ([#12052](https://github.com/librenms/librenms/pull/12052)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Fix 'unused data sent totalconns' in BigIP LTM Pool Members ([#12041](https://github.com/librenms/librenms/pull/12041)) - [rjmidau](https://github.com/rjmidau)
* Expand endrun os support ([#12008](https://github.com/librenms/librenms/pull/12008)) - [hanserasmus](https://github.com/hanserasmus)
* Nokia 7705 packet microwave ([#12007](https://github.com/librenms/librenms/pull/12007)) - [craig-nokia](https://github.com/craig-nokia)
* Add Cisco Firepower 1140 ([#12006](https://github.com/librenms/librenms/pull/12006)) - [Najihel](https://github.com/Najihel)
* Updated documentation and Discovery, to match Asuswrt-Merlin PR ([#11999](https://github.com/librenms/librenms/pull/11999)) - [arrmo](https://github.com/arrmo)
* Arista EOS: Added groups and better sensor names ([#11990](https://github.com/librenms/librenms/pull/11990)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Cisco SLA jitter tag ([#11983](https://github.com/librenms/librenms/pull/11983)) - [pobradovic08](https://github.com/pobradovic08)
* Fix qnap state graphs ([#11976](https://github.com/librenms/librenms/pull/11976)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Added support for FS NMU ([#11965](https://github.com/librenms/librenms/pull/11965)) - [jozefrebjak](https://github.com/jozefrebjak)
* Wireless support for Asuswrt-Merlin (the same as Openwrt) ([#11964](https://github.com/librenms/librenms/pull/11964)) - [arrmo](https://github.com/arrmo)
* Fixed Ubiquiti Airfiber LTU retrieval ([#11844](https://github.com/librenms/librenms/pull/11844)) - [nightcore500](https://github.com/nightcore500)

#### Webui
* Only show smokeping in menu if enabled ([#12019](https://github.com/librenms/librenms/pull/12019)) - [Negatifff](https://github.com/Negatifff)
* Small update to installation steps ([#12016](https://github.com/librenms/librenms/pull/12016)) - [hanserasmus](https://github.com/hanserasmus)
* Add crossorigin policy to link rel manifest ([#12005](https://github.com/librenms/librenms/pull/12005)) - [Zmegolaz](https://github.com/Zmegolaz)
* Only show smokeping link if the url is configured ([#11992](https://github.com/librenms/librenms/pull/11992)) - [Jellyfrog](https://github.com/Jellyfrog)
* Convert LastSync date for oxidized config info ([#11779](https://github.com/librenms/librenms/pull/11779)) - [Negatifff](https://github.com/Negatifff)

#### Graphs
* Set RRD minimum to 0 to see the real change relation in app Smart, Mdadm and Certificate ([#11986](https://github.com/librenms/librenms/pull/11986)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Alerting
* Matrix alert transport ([#12018](https://github.com/librenms/librenms/pull/12018)) - [raphael247](https://github.com/raphael247)
* Clean up default alert rules ([#12014](https://github.com/librenms/librenms/pull/12014)) - [murrant](https://github.com/murrant)

#### Polling
* Use overwrite_ip when set on devices for ping checks ([#12022](https://github.com/librenms/librenms/pull/12022)) - [nathanshiaulam](https://github.com/nathanshiaulam)
* Increase default RRD data retention of MIN, MAX, and LAST to match AVERAGE ([#11995](https://github.com/librenms/librenms/pull/11995)) - [pobradovic08](https://github.com/pobradovic08)

#### Bug
* Corrected alert_rules.json file to container proper JSON. ([#12033](https://github.com/librenms/librenms/pull/12033)) - [nbyers](https://github.com/nbyers)
* Fix fping6 config definition ([#12003](https://github.com/librenms/librenms/pull/12003)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Restore device inventory logic after 055abcf ([#11993](https://github.com/librenms/librenms/pull/11993)) - [Jellyfrog](https://github.com/Jellyfrog)
* Hide pip3 missing validate error ([#11987](https://github.com/librenms/librenms/pull/11987)) - [murrant](https://github.com/murrant)
* Full path to python requirements check ([#11982](https://github.com/librenms/librenms/pull/11982)) - [murrant](https://github.com/murrant)
* Python requirements check, use sys.exit ([#11981](https://github.com/librenms/librenms/pull/11981)) - [murrant](https://github.com/murrant)
* Add default values to cast ([#11977](https://github.com/librenms/librenms/pull/11977)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add type hinting to devicecache ([#11975](https://github.com/librenms/librenms/pull/11975)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix automatic service discovery ([#11963](https://github.com/librenms/librenms/pull/11963)) - [bofh80](https://github.com/bofh80)

#### Documentation
* Update Smokeping.md ([#12048](https://github.com/librenms/librenms/pull/12048)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Rewrite the docs for OS discovery ([#12047](https://github.com/librenms/librenms/pull/12047)) - [Jellyfrog](https://github.com/Jellyfrog)
* Rewrite the test data capture section ([#12046](https://github.com/librenms/librenms/pull/12046)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update Fast Ping Check docs ([#12024](https://github.com/librenms/librenms/pull/12024)) - [hanserasmus](https://github.com/hanserasmus)
* Update PortGroups.md ([#12015](https://github.com/librenms/librenms/pull/12015)) - [sprich96](https://github.com/sprich96)
* Update Applications Docs for Apache Agent ([#12009](https://github.com/librenms/librenms/pull/12009)) - [hanserasmus](https://github.com/hanserasmus)
* Fixed header anchors in RRDCached Documentation ([#12002](https://github.com/librenms/librenms/pull/12002)) - [hanserasmus](https://github.com/hanserasmus)
* Update Install-LibreNMS.md ([#12001](https://github.com/librenms/librenms/pull/12001)) - [Jarod2801](https://github.com/Jarod2801)

#### Misc
* Update travis to Ubuntu 18.04 ([#12043](https://github.com/librenms/librenms/pull/12043)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix missing 'nets' for autodiscovery ([#12039](https://github.com/librenms/librenms/pull/12039)) - [bofh80](https://github.com/bofh80)
* Handle unknown device_type's. ([#12031](https://github.com/librenms/librenms/pull/12031)) - [m4rcu5](https://github.com/m4rcu5)
* Move Availability Setting to Poller ([#12021](https://github.com/librenms/librenms/pull/12021)) - [SourceDoctor](https://github.com/SourceDoctor)
* Cleanup Database Tables on Host Deletion ([#12012](https://github.com/librenms/librenms/pull/12012)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add missing index for device_perf ([#11974](https://github.com/librenms/librenms/pull/11974)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Dependencies
* Bump symfony/http-kernel from 5.1.2 to 5.1.5 ([#12049](https://github.com/librenms/librenms/pull/12049)) - [dependabot](https://github.com/apps/dependabot)
* Bump elliptic from 6.5.2 to 6.5.3 ([#11988](https://github.com/librenms/librenms/pull/11988)) - [dependabot](https://github.com/apps/dependabot)


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


---

##[Old Changelogs](https://github.com/librenms/librenms/tree/master/doc/General/Changelogs)
