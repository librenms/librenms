## 26.3.0
*(2026-03-16)*

A big thank you to the following 37 contributors this last month:

  - [laf](https://github.com/laf) (23)
  - [murrant](https://github.com/murrant) (19)
  - [Jellyfrog](https://github.com/Jellyfrog) (18)
  - [chris9951](https://github.com/chris9951) (9)
  - [eskyuu](https://github.com/eskyuu) (7)
  - [sandap1](https://github.com/sandap1) (4)
  - [kakohegyi](https://github.com/kakohegyi) (3)
  - [dependabot](https://github.com/apps/dependabot) (3)
  - [VVelox](https://github.com/VVelox) (2)
  - [ciroiriarte](https://github.com/ciroiriarte) (2)
  - [trakennedy](https://github.com/trakennedy) (1)
  - [dgodglick](https://github.com/dgodglick) (1)
  - [adadevlabs](https://github.com/adadevlabs) (1)
  - [gmarsay](https://github.com/gmarsay) (1)
  - [dorkmatt](https://github.com/dorkmatt) (1)
  - [matemfromrussia](https://github.com/matemfromrussia) (1)
  - [xinyumoe](https://github.com/xinyumoe) (1)
  - [octalparrot](https://github.com/octalparrot) (1)
  - [Fehler12](https://github.com/Fehler12) (1)
  - [electrocret](https://github.com/electrocret) (1)
  - [peejaychilds](https://github.com/peejaychilds) (1)
  - [lrizzi](https://github.com/lrizzi) (1)
  - [scamp](https://github.com/scamp) (1)
  - [brianegge](https://github.com/brianegge) (1)
  - [erdems](https://github.com/erdems) (1)
  - [Serazio](https://github.com/Serazio) (1)
  - [evheros](https://github.com/evheros) (1)
  - [mhajder](https://github.com/mhajder) (1)
  - [shrank](https://github.com/shrank) (1)
  - [Jannos-443](https://github.com/Jannos-443) (1)
  - [glennmatth](https://github.com/glennmatth) (1)
  - [peelman](https://github.com/peelman) (1)
  - [freddy36](https://github.com/freddy36) (1)
  - [Cormoran96](https://github.com/Cormoran96) (1)
  - [Yoyasp](https://github.com/Yoyasp) (1)
  - [bennetgallein](https://github.com/bennetgallein) (1)
  - [slashdoom](https://github.com/slashdoom) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [laf](https://github.com/laf) (58)
  - [murrant](https://github.com/murrant) (42)
  - [Jellyfrog](https://github.com/Jellyfrog) (13)
  - [copilot-pull-request-reviewer](https://github.com/apps/copilot-pull-request-reviewer) (4)
  - [VVelox](https://github.com/VVelox) (1)
  - [freddy36](https://github.com/freddy36) (1)

#### Breaking Change
* [Procurve] New sensor "PoE Power Total" ([#19188](https://github.com/librenms/librenms/pull/19188)) - [gmarsay](https://github.com/gmarsay)
* Remove netcmd ajax ([#19131](https://github.com/librenms/librenms/pull/19131)) - [murrant](https://github.com/murrant)
* Cyberpower sensor alert updates ([#19122](https://github.com/librenms/librenms/pull/19122)) - [Fehler12](https://github.com/Fehler12)

#### Device
* Fixed sensor detection and divisor ([#19203](https://github.com/librenms/librenms/pull/19203)) - [laf](https://github.com/laf)
* Tuning Cisco discovery ([#19196](https://github.com/librenms/librenms/pull/19196)) - [kakohegyi](https://github.com/kakohegyi)
* Correct fsSystemElectricalSourceIsNormal state mappings ([#19191](https://github.com/librenms/librenms/pull/19191)) - [dgodglick](https://github.com/dgodglick)
* Add additional power sensors for ArubaOS-CX - PoE ([#19189](https://github.com/librenms/librenms/pull/19189)) - [adadevlabs](https://github.com/adadevlabs)
* Microsens-g6 Fix issue #19146 ([#19165](https://github.com/librenms/librenms/pull/19165)) - [sandap1](https://github.com/sandap1)
* Add additional support for FS.com Industrial switches ([#19160](https://github.com/librenms/librenms/pull/19160)) - [dorkmatt](https://github.com/dorkmatt)
* Added support for XCP-NG Virtual Machines ([#19158](https://github.com/librenms/librenms/pull/19158)) - [laf](https://github.com/laf)
* Updated Yunshan device os discovery yaml ([#19155](https://github.com/librenms/librenms/pull/19155)) - [laf](https://github.com/laf)
* Added Eltex MES5324A rev. C1 support ([#19151](https://github.com/librenms/librenms/pull/19151)) - [matemfromrussia](https://github.com/matemfromrussia)
* Fix JetStream FDB port mapping using ifIndex lookup ([#19127](https://github.com/librenms/librenms/pull/19127)) - [octalparrot](https://github.com/octalparrot)
* Enhance Netagent2 - add UPS estimated runtime ([#19112](https://github.com/librenms/librenms/pull/19112)) - [sandap1](https://github.com/sandap1)
* Enhancing support for MOXA EDS-528E / EDS-510E ([#19109](https://github.com/librenms/librenms/pull/19109)) - [sandap1](https://github.com/sandap1)
* Add Opengear Lighthouse support ([#19107](https://github.com/librenms/librenms/pull/19107)) - [peejaychilds](https://github.com/peejaychilds)
* Added support for PacketPower Enterprise Gateways ([#19106](https://github.com/librenms/librenms/pull/19106)) - [laf](https://github.com/laf)
* Remove Unifi connection quality graph ([#19104](https://github.com/librenms/librenms/pull/19104)) - [chris9951](https://github.com/chris9951)
* Filter Cisco reserved VLANs at discovery time ([#19097](https://github.com/librenms/librenms/pull/19097)) - [kakohegyi](https://github.com/kakohegyi)
* Updated mbgLtNgRefclockType mbg-ltos6 sensor state definitions ([#19074](https://github.com/librenms/librenms/pull/19074)) - [laf](https://github.com/laf)
* Updated Cisco transceiver container list ([#19065](https://github.com/librenms/librenms/pull/19065)) - [laf](https://github.com/laf)
* VOSS switch discovery of fan and powersupplies giving errors in eventlog ([#19063](https://github.com/librenms/librenms/pull/19063)) - [evheros](https://github.com/evheros)
* Fix GAIA Checkpoint CPU ([#19060](https://github.com/librenms/librenms/pull/19060)) - [murrant](https://github.com/murrant)
* Enhancing support for MOXA PT-7528 / IKS-6728 /  EDS-G512 ([#19017](https://github.com/librenms/librenms/pull/19017)) - [sandap1](https://github.com/sandap1)
* Add FS Switch Campus S3410 Series ([#18955](https://github.com/librenms/librenms/pull/18955)) - [glennmatth](https://github.com/glennmatth)
* Fix TiMOS BGP established time ([#18826](https://github.com/librenms/librenms/pull/18826)) - [peelman](https://github.com/peelman)
* Improve edgecos support ([#18813](https://github.com/librenms/librenms/pull/18813)) - [freddy36](https://github.com/freddy36)
* Improve BGP peer handling for incomplete data ([#18812](https://github.com/librenms/librenms/pull/18812)) - [Cormoran96](https://github.com/Cormoran96)
* Add sensor support for GNSS on IOS-XR devices ([#17772](https://github.com/librenms/librenms/pull/17772)) - [slashdoom](https://github.com/slashdoom)

#### Webui
* Stop use of xss in device show config ([#19218](https://github.com/librenms/librenms/pull/19218)) - [laf](https://github.com/laf)
* Updated graph background when dark mode used ([#19201](https://github.com/librenms/librenms/pull/19201)) - [laf](https://github.com/laf)
* Modernise device edit health page ([#19162](https://github.com/librenms/librenms/pull/19162)) - [laf](https://github.com/laf)
* Fix 2FA QR code scanning in dark mode ([#19141](https://github.com/librenms/librenms/pull/19141)) - [murrant](https://github.com/murrant)
* Fix SQL query when looking at all outages ([#19116](https://github.com/librenms/librenms/pull/19116)) - [eskyuu](https://github.com/eskyuu)
* Updated links to ports from Device overview page ([#19067](https://github.com/librenms/librenms/pull/19067)) - [laf](https://github.com/laf)
* Fix alert log widget rule name ([#19062](https://github.com/librenms/librenms/pull/19062)) - [murrant](https://github.com/murrant)
* Improve the usability of the dashboard drag/drop widgets ([#19055](https://github.com/librenms/librenms/pull/19055)) - [laf](https://github.com/laf)
* Add search to device ports tab ([#18997](https://github.com/librenms/librenms/pull/18997)) - [Jannos-443](https://github.com/Jannos-443)
* Set timezone display based on user preference ([#18977](https://github.com/librenms/librenms/pull/18977)) - [murrant](https://github.com/murrant)
* Device-overview add clickable email & underline coordinates ([#18605](https://github.com/librenms/librenms/pull/18605)) - [bennetgallein](https://github.com/bennetgallein)

#### Alerting
* Use FQDN for EHLO/HELO greeting per RFC2821 ([#19084](https://github.com/librenms/librenms/pull/19084)) - [ciroiriarte](https://github.com/ciroiriarte)

#### Snmp Traps
* SNMP Trap: Add SNMP trap handler for PowerNet-MIB communicationEstablished and ApcLowBattery ([#19225](https://github.com/librenms/librenms/pull/19225)) - [chris9951](https://github.com/chris9951)
* Use 'trap' log type in Zebra printer handlers ([#19194](https://github.com/librenms/librenms/pull/19194)) - [chris9951](https://github.com/chris9951)
* SNMP Trap Handler: Extend German SNMP trap translations for Zebra printers ([#19185](https://github.com/librenms/librenms/pull/19185)) - [chris9951](https://github.com/chris9951)
* SNMP Trap Handler: Apc ups test and discharge traps ([#19183](https://github.com/librenms/librenms/pull/19183)) - [chris9951](https://github.com/chris9951)
* Add additional German translations for Zebra SNMP trap handler ([#19171](https://github.com/librenms/librenms/pull/19171)) - [chris9951](https://github.com/chris9951)
* Adds SNMP trap handlers for Zebra Link-OS label printers ([#19061](https://github.com/librenms/librenms/pull/19061)) - [chris9951](https://github.com/chris9951)

#### Applications
* Add GPS location info to gpsd ([#19153](https://github.com/librenms/librenms/pull/19153)) - [VVelox](https://github.com/VVelox)

#### Api
* Add alert template API ([#18667](https://github.com/librenms/librenms/pull/18667)) - [Yoyasp](https://github.com/Yoyasp)

#### Discovery
* Fix OSPFv3 FRR ([#19173](https://github.com/librenms/librenms/pull/19173)) - [murrant](https://github.com/murrant)

#### Bug
* Modernise service discovery ([#19212](https://github.com/librenms/librenms/pull/19212)) - [laf](https://github.com/laf)
* Fix service check_mssql_health using incorrect variable ([#19205](https://github.com/librenms/librenms/pull/19205)) - [trakennedy](https://github.com/trakennedy)
* Check variable is set for application metrics ([#19192](https://github.com/librenms/librenms/pull/19192)) - [laf](https://github.com/laf)
* Fix DivisionByZeroError in Billing::getPredictedUsage when $since is 0 ([#19142](https://github.com/librenms/librenms/pull/19142)) - [xinyumoe](https://github.com/xinyumoe)
* Fix phpstan issues in Http Controllers ([#19139](https://github.com/librenms/librenms/pull/19139)) - [Jellyfrog](https://github.com/Jellyfrog)
* Make sure timezone is set before trying to use it ([#19129](https://github.com/librenms/librenms/pull/19129)) - [murrant](https://github.com/murrant)
* Fix splunk invalid function reference ([#19100](https://github.com/librenms/librenms/pull/19100)) - [murrant](https://github.com/murrant)
* Cisco FDB discovery BUG ([#19095](https://github.com/librenms/librenms/pull/19095)) - [kakohegyi](https://github.com/kakohegyi)
* Remove stray "; causing JS syntax error in device syslog filters ([#19090](https://github.com/librenms/librenms/pull/19090)) - [brianegge](https://github.com/brianegge)
* Add missing xmlns declaration to utax.svg ([#19083](https://github.com/librenms/librenms/pull/19083)) - [ciroiriarte](https://github.com/ciroiriarte)
* Fix LLDP-MIB discovery ([#19078](https://github.com/librenms/librenms/pull/19078)) - [murrant](https://github.com/murrant)
* Fix entries with no port ([#19004](https://github.com/librenms/librenms/pull/19004)) - [shrank](https://github.com/shrank)

#### Refactor
* Modernize ip-system-stats module ([#19200](https://github.com/librenms/librenms/pull/19200)) - [murrant](https://github.com/murrant)
* Convert some sensor snmp_get() calls to SnmpQuery ([#19186](https://github.com/librenms/librenms/pull/19186)) - [Jellyfrog](https://github.com/Jellyfrog)
* More granular permissions (RBAC pre-requisite) ([#19135](https://github.com/librenms/librenms/pull/19135)) - [murrant](https://github.com/murrant)
* Convert Cisco BGP peer poll to use snmpwalk and SnmpQuery ([#19027](https://github.com/librenms/librenms/pull/19027)) - [eskyuu](https://github.com/eskyuu)
* Implement WirelessSensorType enum ([#19000](https://github.com/librenms/librenms/pull/19000)) - [Jellyfrog](https://github.com/Jellyfrog)
* Mac accounting module ([#18950](https://github.com/librenms/librenms/pull/18950)) - [murrant](https://github.com/murrant)
* Timezone handling fixes and documentation ([#18802](https://github.com/librenms/librenms/pull/18802)) - [eskyuu](https://github.com/eskyuu)

#### Cleanup
* Replace dbQuery and dbFetchColumn with Eloquent equivalents ([#19210](https://github.com/librenms/librenms/pull/19210)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix vrf-lite warning ([#19181](https://github.com/librenms/librenms/pull/19181)) - [murrant](https://github.com/murrant)
* Fix phpstan baseline issues in LibreNMS/Data/Store ([#19180](https://github.com/librenms/librenms/pull/19180)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix phpstan baseline issues in ComposerHelper and InternalHttpRequest ([#19175](https://github.com/librenms/librenms/pull/19175)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix phpstan baseline issues in LibreNMS/Device ([#19172](https://github.com/librenms/librenms/pull/19172)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix phpstan baseline issues in LibreNMS/Modules ([#19170](https://github.com/librenms/librenms/pull/19170)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix phpstan baseline issues in LibreNMS/OS ([#19168](https://github.com/librenms/librenms/pull/19168)) - [Jellyfrog](https://github.com/Jellyfrog)
* Removed mib: from discovery ([#19152](https://github.com/librenms/librenms/pull/19152)) - [laf](https://github.com/laf)
* Use Sensor enum in device graph pages ([#19149](https://github.com/librenms/librenms/pull/19149)) - [Jellyfrog](https://github.com/Jellyfrog)
* Use Sensor enum in device health pages ([#19147](https://github.com/librenms/librenms/pull/19147)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix phpstan baseline entries for LibreNMS/Util ([#19140](https://github.com/librenms/librenms/pull/19140)) - [Jellyfrog](https://github.com/Jellyfrog)
* Replace Model::make() with new Model() in controllers ([#19138](https://github.com/librenms/librenms/pull/19138)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix phpstan baseline issues in DeviceRelatedModel and UserPref ([#19137](https://github.com/librenms/librenms/pull/19137)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix type hints for authSSOGetAttr() and set_null() to remove phpstan … ([#19136](https://github.com/librenms/librenms/pull/19136)) - [Jellyfrog](https://github.com/Jellyfrog)
* Use Sensor enum in device overview pages ([#19130](https://github.com/librenms/librenms/pull/19130)) - [Jellyfrog](https://github.com/Jellyfrog)
* Cleanup null as array key ([#19128](https://github.com/librenms/librenms/pull/19128)) - [murrant](https://github.com/murrant)
* Narrow ip_or_hostname validation ([#19118](https://github.com/librenms/librenms/pull/19118)) - [murrant](https://github.com/murrant)
* Clean up some unused time and timezone relates variables in http controllers ([#19114](https://github.com/librenms/librenms/pull/19114)) - [eskyuu](https://github.com/eskyuu)
* Cleanup permissions when user is deleted ([#19088](https://github.com/librenms/librenms/pull/19088)) - [erdems](https://github.com/erdems)
* F5-ltm fix undefined variables ([#19071](https://github.com/librenms/librenms/pull/19071)) - [murrant](https://github.com/murrant)
* Remove explicit default null from config definition ([#19066](https://github.com/librenms/librenms/pull/19066)) - [eskyuu](https://github.com/eskyuu)
* Remove unused dispatcher legacy settings ([#19058](https://github.com/librenms/librenms/pull/19058)) - [eskyuu](https://github.com/eskyuu)
* Updated supercap to avoid being used to detect other devices ([#19056](https://github.com/librenms/librenms/pull/19056)) - [laf](https://github.com/laf)
* Code cleanup ([#19052](https://github.com/librenms/librenms/pull/19052)) - [murrant](https://github.com/murrant)
* Normalize enum case names to PascalCase convention ([#19036](https://github.com/librenms/librenms/pull/19036)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Documentation
* Add note/work around to GPSD extend being slow thanks to gpspipe and explain mode int ([#19134](https://github.com/librenms/librenms/pull/19134)) - [VVelox](https://github.com/VVelox)
* Fixed mariadb configuration doc for Debian 13 ([#19105](https://github.com/librenms/librenms/pull/19105)) - [lrizzi](https://github.com/lrizzi)
* Add example for extending alert templates ([#19064](https://github.com/librenms/librenms/pull/19064)) - [Serazio](https://github.com/Serazio)
* Updated docs to reflect use of MIB::OID usage in yaml and code ([#19051](https://github.com/librenms/librenms/pull/19051)) - [laf](https://github.com/laf)
* Clarifies API routes and updates documentation ([#19044](https://github.com/librenms/librenms/pull/19044)) - [mhajder](https://github.com/mhajder)

#### Misc
* Fix OS level SNMP repeaters ([#19117](https://github.com/librenms/librenms/pull/19117)) - [electrocret](https://github.com/electrocret)
* Device edit legacy urls ([#19103](https://github.com/librenms/librenms/pull/19103)) - [chris9951](https://github.com/chris9951)
* Fix iftype graph background ([#19099](https://github.com/librenms/librenms/pull/19099)) - [scamp](https://github.com/scamp)
* Update to allow maintenance:fetch-rss to use proxy servers ([#19093](https://github.com/librenms/librenms/pull/19093)) - [eskyuu](https://github.com/eskyuu)

#### Internal Features
* Third javascript time handling attempt ([#19154](https://github.com/librenms/librenms/pull/19154)) - [murrant](https://github.com/murrant)
* Javascript time functions complete ([#19148](https://github.com/librenms/librenms/pull/19148)) - [murrant](https://github.com/murrant)

#### Dependencies
* Bump league/commonmark from 2.8.0 to 2.8.1 ([#19177](https://github.com/librenms/librenms/pull/19177)) - [dependabot](https://github.com/apps/dependabot)
* Bump immutable from 5.1.4 to 5.1.5 ([#19167](https://github.com/librenms/librenms/pull/19167)) - [dependabot](https://github.com/apps/dependabot)
* Bump rollup from 4.57.1 to 4.59.0 ([#19133](https://github.com/librenms/librenms/pull/19133)) - [dependabot](https://github.com/apps/dependabot)


## 26.2.0
*(2026-02-16)*

A big thank you to the following 30 contributors this last month:

  - [Jellyfrog](https://github.com/Jellyfrog) (69)
  - [murrant](https://github.com/murrant) (54)
  - [laf](https://github.com/laf) (32)
  - [dependabot](https://github.com/apps/dependabot) (8)
  - [sandap1](https://github.com/sandap1) (6)
  - [peelman](https://github.com/peelman) (6)
  - [kakohegyi](https://github.com/kakohegyi) (5)
  - [eskyuu](https://github.com/eskyuu) (2)
  - [electrocret](https://github.com/electrocret) (2)
  - [freddy36](https://github.com/freddy36) (2)
  - [andr3jk](https://github.com/andr3jk) (1)
  - [Salahzaar](https://github.com/Salahzaar) (1)
  - [cbuechler](https://github.com/cbuechler) (1)
  - [DidierFlas](https://github.com/DidierFlas) (1)
  - [SoulKyu](https://github.com/SoulKyu) (1)
  - [bonzo81](https://github.com/bonzo81) (1)
  - [makriska](https://github.com/makriska) (1)
  - [EinGlasVollKakao](https://github.com/EinGlasVollKakao) (1)
  - [nhnetsolutions](https://github.com/nhnetsolutions) (1)
  - [Jannos-443](https://github.com/Jannos-443) (1)
  - [mrwold](https://github.com/mrwold) (1)
  - [shrank](https://github.com/shrank) (1)
  - [ethanvos](https://github.com/ethanvos) (1)
  - [goersr](https://github.com/goersr) (1)
  - [heapdavid](https://github.com/heapdavid) (1)
  - [adam-bishop](https://github.com/adam-bishop) (1)
  - [xorrkaz](https://github.com/xorrkaz) (1)
  - [sshockley](https://github.com/sshockley) (1)
  - [peejaychilds](https://github.com/peejaychilds) (1)
  - [loopodoopo](https://github.com/loopodoopo) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [laf](https://github.com/laf) (81)
  - [murrant](https://github.com/murrant) (38)
  - [Jellyfrog](https://github.com/Jellyfrog) (28)
  - [copilot-pull-request-reviewer](https://github.com/apps/copilot-pull-request-reviewer) (7)
  - [peelman](https://github.com/peelman) (2)
  - [peejaychilds](https://github.com/peejaychilds) (1)
  - [Bongs81](https://github.com/Bongs81) (1)

#### Feature
* Dispatcher log stdout ([#18920](https://github.com/librenms/librenms/pull/18920)) - [murrant](https://github.com/murrant)
* Table export add button to export all records ([#18800](https://github.com/librenms/librenms/pull/18800)) - [murrant](https://github.com/murrant)

#### Breaking Change
* Alertmanager transport now strips stc_ label prefix ([#18986](https://github.com/librenms/librenms/pull/18986)) - [SoulKyu](https://github.com/SoulKyu)
* Update Arris MIBs (removes legacy temperature sensor support) ([#18868](https://github.com/librenms/librenms/pull/18868)) - [Jellyfrog](https://github.com/Jellyfrog)
* Remove logfile function ([#18852](https://github.com/librenms/librenms/pull/18852)) - [murrant](https://github.com/murrant)
* Remove tcpdf and pest ([#18850](https://github.com/librenms/librenms/pull/18850)) - [murrant](https://github.com/murrant)
* Updated Moxa EDS-4000 (MX-NOS) for sensors and port data ([#18702](https://github.com/librenms/librenms/pull/18702)) - [sandap1](https://github.com/sandap1)

#### Security
* Fix port group delete xss ([#19042](https://github.com/librenms/librenms/pull/19042)) - [murrant](https://github.com/murrant)
* Fix device group delete xss ([#19041](https://github.com/librenms/librenms/pull/19041)) - [murrant](https://github.com/murrant)
* Fix xss ([#19040](https://github.com/librenms/librenms/pull/19040)) - [murrant](https://github.com/murrant)
* Fix alert rule xss ([#19039](https://github.com/librenms/librenms/pull/19039)) - [murrant](https://github.com/murrant)
* Fix reflected XSS ([#19038](https://github.com/librenms/librenms/pull/19038)) - [murrant](https://github.com/murrant)
* Rewrite address search backend ([#18777](https://github.com/librenms/librenms/pull/18777)) - [murrant](https://github.com/murrant)

#### Device
* Updated transceiver interface discovery for Ocnos ([#19028](https://github.com/librenms/librenms/pull/19028)) - [laf](https://github.com/laf)
* Add support for Spectracool Air Conditioning units ([#18992](https://github.com/librenms/librenms/pull/18992)) - [laf](https://github.com/laf)
* Add PDU Active Power graphs for current power usage in watts for cyberpower ([#18991](https://github.com/librenms/librenms/pull/18991)) - [cbuechler](https://github.com/cbuechler)
* Handle Other entSensorThresholdSeverity for Cisco devices ([#18974](https://github.com/librenms/librenms/pull/18974)) - [makriska](https://github.com/makriska)
* Fix port polling for NOKIA-ISAM: Adding ifMtu fallback for PON Interfaces ([#18954](https://github.com/librenms/librenms/pull/18954)) - [nhnetsolutions](https://github.com/nhnetsolutions)
* Advantech - Add port descriptions ([#18941](https://github.com/librenms/librenms/pull/18941)) - [sandap1](https://github.com/sandap1)
* Add OID for Cisco Secure Firewall 4215 ([#18925](https://github.com/librenms/librenms/pull/18925)) - [mrwold](https://github.com/mrwold)
* Add additional sensors for Supercap devices ([#18912](https://github.com/librenms/librenms/pull/18912)) - [laf](https://github.com/laf)
* Fix opticalVoltage ([#18856](https://github.com/librenms/librenms/pull/18856)) - [freddy36](https://github.com/freddy36)
* Add support for Microsens G6 devices ([#18846](https://github.com/librenms/librenms/pull/18846)) - [sandap1](https://github.com/sandap1)
* Add support for PacketFlux SiteMonitor Base 3 ([#18836](https://github.com/librenms/librenms/pull/18836)) - [ethanvos](https://github.com/ethanvos)
* Additional sensors & build Version for Advantech ([#18834](https://github.com/librenms/librenms/pull/18834)) - [sandap1](https://github.com/sandap1)
* Add Support for ZTE 5950 ([#18832](https://github.com/librenms/librenms/pull/18832)) - [sandap1](https://github.com/sandap1)
* Updated Ocnos hardware list to fix transceiver discovery ([#18823](https://github.com/librenms/librenms/pull/18823)) - [laf](https://github.com/laf)
* Improve transceiver support ([#18815](https://github.com/librenms/librenms/pull/18815)) - [freddy36](https://github.com/freddy36)
* Add support for Conteg Databus Devices ([#18811](https://github.com/librenms/librenms/pull/18811)) - [sandap1](https://github.com/sandap1)
* Add PHP-based sensor discovery for Nokia TiMOS NAT statistics: ([#18807](https://github.com/librenms/librenms/pull/18807)) - [peelman](https://github.com/peelman)
* Add support for Supercap supercapacitors ([#18793](https://github.com/librenms/librenms/pull/18793)) - [laf](https://github.com/laf)
* Fix flip-flopped State values for tmnxNatIsaMemberSessionUsageHi ([#18781](https://github.com/librenms/librenms/pull/18781)) - [peelman](https://github.com/peelman)
* Add main/div/combined power on graphs ([#18705](https://github.com/librenms/librenms/pull/18705)) - [loopodoopo](https://github.com/loopodoopo)
* Remove unneeded noindex from routeros ([#18696](https://github.com/librenms/librenms/pull/18696)) - [murrant](https://github.com/murrant)

#### Webui
* Fix device settings misc html ([#19035](https://github.com/librenms/librenms/pull/19035)) - [murrant](https://github.com/murrant)
* Add Alert Map widget ([#19026](https://github.com/librenms/librenms/pull/19026)) - [laf](https://github.com/laf)
* Update leaflet css to stop Device group menu being hidden ([#19014](https://github.com/librenms/librenms/pull/19014)) - [andr3jk](https://github.com/andr3jk)
* Add Duplex status to ports lists ([#18989](https://github.com/librenms/librenms/pull/18989)) - [DidierFlas](https://github.com/DidierFlas)
* Update alertlog widget to new backend ([#18968](https://github.com/librenms/librenms/pull/18968)) - [murrant](https://github.com/murrant)
* Add filters to device vlan tab ([#18948](https://github.com/librenms/librenms/pull/18948)) - [Jannos-443](https://github.com/Jannos-443)
* Add long titles to service graphs ([#18921](https://github.com/librenms/librenms/pull/18921)) - [shrank](https://github.com/shrank)
* Set home link explicitly ([#18862](https://github.com/librenms/librenms/pull/18862)) - [murrant](https://github.com/murrant)
* Expose Applications settings in Global Settings UI ([#18833](https://github.com/librenms/librenms/pull/18833)) - [peelman](https://github.com/peelman)
* VLAN ports search include device fields ([#18830](https://github.com/librenms/librenms/pull/18830)) - [kakohegyi](https://github.com/kakohegyi)
* Inventory use modern device link ([#18797](https://github.com/librenms/librenms/pull/18797)) - [murrant](https://github.com/murrant)
* Sort spanning tree instances by vlan (PVST) ([#18791](https://github.com/librenms/librenms/pull/18791)) - [kakohegyi](https://github.com/kakohegyi)

#### Alerting
* Fix alert check null ([#18919](https://github.com/librenms/librenms/pull/18919)) - [murrant](https://github.com/murrant)
* Update mail transports to generate a single event log if no e-mail addresses are found ([#18722](https://github.com/librenms/librenms/pull/18722)) - [eskyuu](https://github.com/eskyuu)
* Alertmanager Transport - Try all nodes ([#18655](https://github.com/librenms/librenms/pull/18655)) - [electrocret](https://github.com/electrocret)

#### Snmp Traps
* Add additional Cisco snmptrap handlers ([#19032](https://github.com/librenms/librenms/pull/19032)) - [laf](https://github.com/laf)

#### Applications
* NFS application requires perl-IO-Compress, also fix a broken import ([#18804](https://github.com/librenms/librenms/pull/18804)) - [adam-bishop](https://github.com/adam-bishop)

#### Billing
* Fix 95th billing calculation under certain conditions ([#18481](https://github.com/librenms/librenms/pull/18481)) - [laf](https://github.com/laf)

#### Api
* Add 'with=vlans' support to get_device_ports API endpoint ([#18975](https://github.com/librenms/librenms/pull/18975)) - [bonzo81](https://github.com/bonzo81)

#### Discovery
* Fix discovery-arp module query ([#19030](https://github.com/librenms/librenms/pull/19030)) - [murrant](https://github.com/murrant)
* Fix error in discovery ([#18820](https://github.com/librenms/librenms/pull/18820)) - [murrant](https://github.com/murrant)
* Fix relative includes in discovery code ([#18799](https://github.com/librenms/librenms/pull/18799)) - [murrant](https://github.com/murrant)
* Implement php state sensor translations ([#18784](https://github.com/librenms/librenms/pull/18784)) - [murrant](https://github.com/murrant)
* Improved device type handling ([#18758](https://github.com/librenms/librenms/pull/18758)) - [murrant](https://github.com/murrant)

#### Authentication
* AD Auth handle search failure ([#18045](https://github.com/librenms/librenms/pull/18045)) - [murrant](https://github.com/murrant)

#### Bug
* ARP auto-discovery only device ARP ([#19047](https://github.com/librenms/librenms/pull/19047)) - [murrant](https://github.com/murrant)
* Update topChanges in stp table to unsigned int ([#19034](https://github.com/librenms/librenms/pull/19034)) - [laf](https://github.com/laf)
* Fix legacy services discovery helper inclusion and PHP 8 warnings ([#19001](https://github.com/librenms/librenms/pull/19001)) - [Salahzaar](https://github.com/Salahzaar)
* Fixed search on Device routing edit page ([#18982](https://github.com/librenms/librenms/pull/18982)) - [laf](https://github.com/laf)
* Remove duplicate ssCpuRawWait from ucd-mib poller ([#18976](https://github.com/librenms/librenms/pull/18976)) - [eskyuu](https://github.com/eskyuu)
* Fix non-utf prefix for device fields ([#18956](https://github.com/librenms/librenms/pull/18956)) - [murrant](https://github.com/murrant)
* Fix relative include in unix-agent ([#18858](https://github.com/librenms/librenms/pull/18858)) - [murrant](https://github.com/murrant)
* Add idField to RoleController for role selector filtering ([#18855](https://github.com/librenms/librenms/pull/18855)) - [peelman](https://github.com/peelman)
* Updated cef punt2host column to use bigint ([#18831](https://github.com/librenms/librenms/pull/18831)) - [laf](https://github.com/laf)
* Fix cisco-cef undefined variable ([#18821](https://github.com/librenms/librenms/pull/18821)) - [murrant](https://github.com/murrant)
* Handle malformed SNMP responses in discovery-protocols module ([#18818](https://github.com/librenms/librenms/pull/18818)) - [peelman](https://github.com/peelman)
* Handle malformed SNMP responses in fdb-table module ([#18817](https://github.com/librenms/librenms/pull/18817)) - [peelman](https://github.com/peelman)
* Updated cef columns to use bigint ([#18816](https://github.com/librenms/librenms/pull/18816)) - [laf](https://github.com/laf)
* Fix save test data dependencies ([#18810](https://github.com/librenms/librenms/pull/18810)) - [murrant](https://github.com/murrant)
* Fix errors when device is missing for port ([#18809](https://github.com/librenms/librenms/pull/18809)) - [murrant](https://github.com/murrant)
* Fix device neighbour maps ([#18805](https://github.com/librenms/librenms/pull/18805)) - [heapdavid](https://github.com/heapdavid)
* Ports table fixes ([#18801](https://github.com/librenms/librenms/pull/18801)) - [murrant](https://github.com/murrant)
* Fix unix agent dmi parsing ([#18798](https://github.com/librenms/librenms/pull/18798)) - [murrant](https://github.com/murrant)
* MplsVpnVrfDescription is not allowed to be NULL. ([#18795](https://github.com/librenms/librenms/pull/18795)) - [xorrkaz](https://github.com/xorrkaz)
* Fix maintenance:rrd-step with rrdcached ([#18785](https://github.com/librenms/librenms/pull/18785)) - [murrant](https://github.com/murrant)
* Handle rrd step validation timeout more gracefully. ([#18783](https://github.com/librenms/librenms/pull/18783)) - [murrant](https://github.com/murrant)
* Fix parse ipv6 prefix len ([#18780](https://github.com/librenms/librenms/pull/18780)) - [murrant](https://github.com/murrant)
* Fix incorrect config usage ([#18776](https://github.com/librenms/librenms/pull/18776)) - [murrant](https://github.com/murrant)
* Fix scheduler maintenance.log path ([#18775](https://github.com/librenms/librenms/pull/18775)) - [murrant](https://github.com/murrant)

#### Refactor
* Move realtime graph to Laravel ([#18857](https://github.com/librenms/librenms/pull/18857)) - [murrant](https://github.com/murrant)
* Alert log modern backend ([#18844](https://github.com/librenms/librenms/pull/18844)) - [murrant](https://github.com/murrant)

#### Cleanup
* Update os discovery yaml to use MIB::OID Misc ([#19049](https://github.com/librenms/librenms/pull/19049)) - [laf](https://github.com/laf)
* Update os discovery yaml to use MIB::OID I ([#19048](https://github.com/librenms/librenms/pull/19048)) - [laf](https://github.com/laf)
* Update os discovery yaml to use MIB::OID H ([#19046](https://github.com/librenms/librenms/pull/19046)) - [laf](https://github.com/laf)
* Update os discovery yaml to use MIB::OID g ([#19045](https://github.com/librenms/librenms/pull/19045)) - [laf](https://github.com/laf)
* Update os discovery yaml to use MIB::OID F ([#19033](https://github.com/librenms/librenms/pull/19033)) - [laf](https://github.com/laf)
* Fix bad type in NetSnmpQuery ([#19031](https://github.com/librenms/librenms/pull/19031)) - [murrant](https://github.com/murrant)
* Update os discovery yaml to use MIB::OID for S ([#19025](https://github.com/librenms/librenms/pull/19025)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID e ([#19024](https://github.com/librenms/librenms/pull/19024)) - [laf](https://github.com/laf)
* Update os discovery yaml to use MIB::OID for J files ([#19023](https://github.com/librenms/librenms/pull/19023)) - [Jellyfrog](https://github.com/Jellyfrog)
* Updated POSEIDON-MIB ([#19022](https://github.com/librenms/librenms/pull/19022)) - [laf](https://github.com/laf)
* Update os discovery yaml to use MIB::OID for L files ([#19021](https://github.com/librenms/librenms/pull/19021)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID for M files ([#19020](https://github.com/librenms/librenms/pull/19020)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID for N files ([#19019](https://github.com/librenms/librenms/pull/19019)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID for O files ([#19018](https://github.com/librenms/librenms/pull/19018)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID for P files ([#19016](https://github.com/librenms/librenms/pull/19016)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID for Q files ([#19013](https://github.com/librenms/librenms/pull/19013)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID for R ([#19012](https://github.com/librenms/librenms/pull/19012)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID for T files ([#19011](https://github.com/librenms/librenms/pull/19011)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID for U files ([#19010](https://github.com/librenms/librenms/pull/19010)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID for V files ([#19009](https://github.com/librenms/librenms/pull/19009)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID D ([#19006](https://github.com/librenms/librenms/pull/19006)) - [laf](https://github.com/laf)
* Update os discovery yaml to use MIB::OID W-Z ([#19003](https://github.com/librenms/librenms/pull/19003)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update os discovery yaml to use MIB::OID C ([#19002](https://github.com/librenms/librenms/pull/19002)) - [laf](https://github.com/laf)
* Update os discovery yaml to use MIB::OID B OSes ([#18983](https://github.com/librenms/librenms/pull/18983)) - [laf](https://github.com/laf)
* Update os discovery yaml to use MIB::OID 5 ([#18964](https://github.com/librenms/librenms/pull/18964)) - [laf](https://github.com/laf)
* Update os discovery yaml to use MIB::OID 4 ([#18963](https://github.com/librenms/librenms/pull/18963)) - [laf](https://github.com/laf)
* Update os discovery yaml to use MIB::OID 3 ([#18962](https://github.com/librenms/librenms/pull/18962)) - [laf](https://github.com/laf)
* Fix some array key null errors ([#18939](https://github.com/librenms/librenms/pull/18939)) - [murrant](https://github.com/murrant)
* Remove log driver output overrides ([#18936](https://github.com/librenms/librenms/pull/18936)) - [murrant](https://github.com/murrant)
* Update os discovery yaml to use MIB::OID 2 ([#18935](https://github.com/librenms/librenms/pull/18935)) - [laf](https://github.com/laf)
* Fix app imports and backticks ([#18851](https://github.com/librenms/librenms/pull/18851)) - [murrant](https://github.com/murrant)
* Fix some deprecation warnings ([#18849](https://github.com/librenms/librenms/pull/18849)) - [murrant](https://github.com/murrant)
* Small cleanup for phpstan ([#18848](https://github.com/librenms/librenms/pull/18848)) - [murrant](https://github.com/murrant)
* Rector 2.3 fixes ([#18845](https://github.com/librenms/librenms/pull/18845)) - [murrant](https://github.com/murrant)
* Fix PHP 8.5 pdo deprecation message ([#18840](https://github.com/librenms/librenms/pull/18840)) - [murrant](https://github.com/murrant)
* Remove old discovery code ([#18782](https://github.com/librenms/librenms/pull/18782)) - [murrant](https://github.com/murrant)

#### Documentation
* Remove 'graphviz' references, its no longer used ([#18859](https://github.com/librenms/librenms/pull/18859)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update Two-Factor-Auth.md ([#18835](https://github.com/librenms/librenms/pull/18835)) - [goersr](https://github.com/goersr)
* Correct SELinux policy installation instructions ([#18792](https://github.com/librenms/librenms/pull/18792)) - [sshockley](https://github.com/sshockley)

#### Tests
* Add --testdox option to phpunit ([#18996](https://github.com/librenms/librenms/pull/18996)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix dev:check --os-modules-only without os ([#18934](https://github.com/librenms/librenms/pull/18934)) - [murrant](https://github.com/murrant)
* Fix DiscoverDevice destructor crash during test teardown ([#18930](https://github.com/librenms/librenms/pull/18930)) - [Jellyfrog](https://github.com/Jellyfrog)
* Custom validation tests ([#18918](https://github.com/librenms/librenms/pull/18918)) - [murrant](https://github.com/murrant)

#### Misc
* Prometheus escape sysName with slashes ([#18958](https://github.com/librenms/librenms/pull/18958)) - [EinGlasVollKakao](https://github.com/EinGlasVollKakao)
* Add health check verbose output ([#18940](https://github.com/librenms/librenms/pull/18940)) - [murrant](https://github.com/murrant)
* Add safety checks for adding/deleting devices with no hostname ([#18926](https://github.com/librenms/librenms/pull/18926)) - [laf](https://github.com/laf)
* Fix Alertmanager Auth ([#18924](https://github.com/librenms/librenms/pull/18924)) - [electrocret](https://github.com/electrocret)
* Overview graph should be Power (which exists) not Voltage (which does not) ([#18787](https://github.com/librenms/librenms/pull/18787)) - [peejaychilds](https://github.com/peejaychilds)
* STP Discovery fix for Cisco devices ([#18767](https://github.com/librenms/librenms/pull/18767)) - [kakohegyi](https://github.com/kakohegyi)

#### Internal Features
* Delete all test devices ([#18932](https://github.com/librenms/librenms/pull/18932)) - [murrant](https://github.com/murrant)

#### Mibs
* Clean up dupes, move standard ([#18995](https://github.com/librenms/librenms/pull/18995)) - [Jellyfrog](https://github.com/Jellyfrog)
* Move Juniper OS mibs into shared folder ([#18994](https://github.com/librenms/librenms/pull/18994)) - [Jellyfrog](https://github.com/Jellyfrog)
* Rename to correct names ([#18933](https://github.com/librenms/librenms/pull/18933)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync from Netdisco ([#18931](https://github.com/librenms/librenms/pull/18931)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update and reorganize Nokia mibs ([#18928](https://github.com/librenms/librenms/pull/18928)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync zyxel MIBs ([#18910](https://github.com/librenms/librenms/pull/18910)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync waystream MIBs ([#18908](https://github.com/librenms/librenms/pull/18908)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync vmware MIBs ([#18907](https://github.com/librenms/librenms/pull/18907)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync sonicwall MIBs ([#18905](https://github.com/librenms/librenms/pull/18905)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync smartoptics MIBs ([#18904](https://github.com/librenms/librenms/pull/18904)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync sentry MIBs ([#18903](https://github.com/librenms/librenms/pull/18903)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync sensatronics MIBs ([#18902](https://github.com/librenms/librenms/pull/18902)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync saf MIBs ([#18901](https://github.com/librenms/librenms/pull/18901)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync ruckus MIBs ([#18900](https://github.com/librenms/librenms/pull/18900)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync riverbed MIBs ([#18899](https://github.com/librenms/librenms/pull/18899)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync rittal MIBs ([#18898](https://github.com/librenms/librenms/pull/18898)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync raritan MIBs ([#18897](https://github.com/librenms/librenms/pull/18897)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync radlan MIBs ([#18896](https://github.com/librenms/librenms/pull/18896)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync nti MIBs ([#18895](https://github.com/librenms/librenms/pull/18895)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync nortel MIBs ([#18894](https://github.com/librenms/librenms/pull/18894)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync nokia MIBs ([#18893](https://github.com/librenms/librenms/pull/18893)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync nexans MIBs ([#18892](https://github.com/librenms/librenms/pull/18892)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync netgear MIBs ([#18891](https://github.com/librenms/librenms/pull/18891)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync netapp MIBs ([#18890](https://github.com/librenms/librenms/pull/18890)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync meinberg MIBs ([#18889](https://github.com/librenms/librenms/pull/18889)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync kemp MIBs ([#18888](https://github.com/librenms/librenms/pull/18888)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync junose MIBs ([#18887](https://github.com/librenms/librenms/pull/18887)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync juniper MIBs ([#18886](https://github.com/librenms/librenms/pull/18886)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync ixsystems MIBs ([#18885](https://github.com/librenms/librenms/pull/18885)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync ibm MIBs ([#18884](https://github.com/librenms/librenms/pull/18884)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync huawei MIBs ([#18883](https://github.com/librenms/librenms/pull/18883)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync hp MIBs ([#18882](https://github.com/librenms/librenms/pull/18882)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync hirschmann MIBs ([#18881](https://github.com/librenms/librenms/pull/18881)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync fortinet MIBs ([#18880](https://github.com/librenms/librenms/pull/18880)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync firebrick MIBs ([#18879](https://github.com/librenms/librenms/pull/18879)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync fibrolan MIBs ([#18878](https://github.com/librenms/librenms/pull/18878)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync f5 MIBs ([#18877](https://github.com/librenms/librenms/pull/18877)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync extreme MIBs ([#18876](https://github.com/librenms/librenms/pull/18876)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync delta MIBs ([#18875](https://github.com/librenms/librenms/pull/18875)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync dell MIBs ([#18874](https://github.com/librenms/librenms/pull/18874)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync cisco MIBs ([#18873](https://github.com/librenms/librenms/pull/18873)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync ciena MIBs ([#18872](https://github.com/librenms/librenms/pull/18872)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync checkpoint MIBs ([#18871](https://github.com/librenms/librenms/pull/18871)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync brocade MIBs ([#18870](https://github.com/librenms/librenms/pull/18870)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync avaya MIBs ([#18869](https://github.com/librenms/librenms/pull/18869)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync arista MIBs ([#18867](https://github.com/librenms/librenms/pull/18867)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync alpha MIBs ([#18866](https://github.com/librenms/librenms/pull/18866)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync adva MIBs ([#18865](https://github.com/librenms/librenms/pull/18865)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync adtran MIBs ([#18864](https://github.com/librenms/librenms/pull/18864)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sync accedian MIBs ([#18863](https://github.com/librenms/librenms/pull/18863)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update some MIBs ([#18847](https://github.com/librenms/librenms/pull/18847)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Dependencies
* Bump webpack from 5.101.0 to 5.105.0 ([#18990](https://github.com/librenms/librenms/pull/18990)) - [dependabot](https://github.com/apps/dependabot)
* Bump psy/psysh from 0.12.18 to 0.12.19 ([#18961](https://github.com/librenms/librenms/pull/18961)) - [dependabot](https://github.com/apps/dependabot)
* Bump symfony/process from 7.4.3 to 7.4.5 ([#18949](https://github.com/librenms/librenms/pull/18949)) - [dependabot](https://github.com/apps/dependabot)
* Bump tar from 7.5.6 to 7.5.7 ([#18945](https://github.com/librenms/librenms/pull/18945)) - [dependabot](https://github.com/apps/dependabot)
* Bump phpunit/phpunit from 11.5.33 to 11.5.50 ([#18937](https://github.com/librenms/librenms/pull/18937)) - [dependabot](https://github.com/apps/dependabot)
* Bump lodash from 4.17.21 to 4.17.23 ([#18843](https://github.com/librenms/librenms/pull/18843)) - [dependabot](https://github.com/apps/dependabot)
* Bump tar from 7.5.3 to 7.5.6 ([#18842](https://github.com/librenms/librenms/pull/18842)) - [dependabot](https://github.com/apps/dependabot)
* PHP Dependency updates ([#18841](https://github.com/librenms/librenms/pull/18841)) - [murrant](https://github.com/murrant)
* Bump tar from 7.4.3 to 7.5.3 ([#18824](https://github.com/librenms/librenms/pull/18824)) - [dependabot](https://github.com/apps/dependabot)

## 26.1.0

*(2026-01-12)*

A big thank you to the following 28 contributors this last month:

* [murrant](https://github.com/murrant) (22)
* [peelman](https://github.com/peelman) (10)
* [laf](https://github.com/laf) (9)
* [sandap1](https://github.com/sandap1) (9)
* [eskyuu](https://github.com/eskyuu) (6)
* [peejaychilds](https://github.com/peejaychilds) (4)
* [alagoutte](https://github.com/alagoutte) (2)
* [jezekus](https://github.com/jezekus) (2)
* [dependabot](https://github.com/apps/dependabot) (2)
* [freddy36](https://github.com/freddy36) (2)
* [jediblair](https://github.com/jediblair) (2)
* [SourceDoctor](https://github.com/SourceDoctor) (1)
* [Jellyfrog](https://github.com/Jellyfrog) (1)
* [kakohegyi](https://github.com/kakohegyi) (1)
* [erdems](https://github.com/erdems) (1)
* [westerterp](https://github.com/westerterp) (1)
* [shrank](https://github.com/shrank) (1)
* [lennarttd](https://github.com/lennarttd) (1)
* [andrewimeson](https://github.com/andrewimeson) (1)
* [garlic17](https://github.com/garlic17) (1)
* [jakejakejakejakejakejake](https://github.com/jakejakejakejakejakejake) (1)
* [knpo](https://github.com/knpo) (1)
* [VVelox](https://github.com/VVelox) (1)
* [xorrkaz](https://github.com/xorrkaz) (1)
* [Serazio](https://github.com/Serazio) (1)
* [martinberg](https://github.com/martinberg) (1)
* [Fehler12](https://github.com/Fehler12) (1)
* [Npeca75](https://github.com/Npeca75) (1)

Thanks to maintainers and others that helped with pull requests this month:

* [laf](https://github.com/laf) (48)
* [murrant](https://github.com/murrant) (13)
* [copilot-pull-request-reviewer](https://github.com/apps/copilot-pull-request-reviewer) (10)
* [Jellyfrog](https://github.com/Jellyfrog) (9)
* [PipoCanaja](https://github.com/PipoCanaja) (9)
* [peelman](https://github.com/peelman) (1)

#### Breaking Change

* Change Algcom SM type to environment ([#18730](https://github.com/librenms/librenms/pull/18730)) - [murrant](https://github.com/murrant)
* Allow alerts to match users through device groups ([#18720](https://github.com/librenms/librenms/pull/18720)) - [eskyuu](https://github.com/eskyuu)
* Cisco IE3200 and IE3300 devices are actually IOS-XE ([#18654](https://github.com/librenms/librenms/pull/18654)) - [knpo](https://github.com/knpo)

#### Device

* Adding sensors for Nokia ESA, ISA, and NAT resources ([#18770](https://github.com/librenms/librenms/pull/18770)) - [peelman](https://github.com/peelman)
* Add grid power sensor, overload protection ([#18763](https://github.com/librenms/librenms/pull/18763)) - [peelman](https://github.com/peelman)
* Update ns-bsd MIBs and detection ([#18762](https://github.com/librenms/librenms/pull/18762)) - [alagoutte](https://github.com/alagoutte)
* Update geist-watchdog detection ([#18757](https://github.com/librenms/librenms/pull/18757)) - [sandap1](https://github.com/sandap1)
* Corrected sensor name in Ironware OS ([#18754](https://github.com/librenms/librenms/pull/18754)) - [laf](https://github.com/laf)
* Initial support for Alta Labs ([#18753](https://github.com/librenms/librenms/pull/18753)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix OID path for connection tracking RouterOS ([#18752](https://github.com/librenms/librenms/pull/18752)) - [jezekus](https://github.com/jezekus)
* Fix RFC1628 sensors not skipping non-numeric values ([#18746](https://github.com/librenms/librenms/pull/18746)) - [murrant](https://github.com/murrant)
* Add Support for Vertiv DCS devices. ([#18744](https://github.com/librenms/librenms/pull/18744)) - [sandap1](https://github.com/sandap1)
* Add additional sensors for Ironware devices ([#18743](https://github.com/librenms/librenms/pull/18743)) - [laf](https://github.com/laf)
* Add Nokia 1830 PSS device discovery and inventory support ([#18739](https://github.com/librenms/librenms/pull/18739)) - [peelman](https://github.com/peelman)
* Add System Resource Usage counts for TiMOS ([#18736](https://github.com/librenms/librenms/pull/18736)) - [peelman](https://github.com/peelman)
* Fix fortigate cellular sensors ([#18733](https://github.com/librenms/librenms/pull/18733)) - [murrant](https://github.com/murrant)
* Fix Alpha CXC UPS input voltage divisor ([#18729](https://github.com/librenms/librenms/pull/18729)) - [murrant](https://github.com/murrant)
* New sensors for FTD ([#18721](https://github.com/librenms/librenms/pull/18721)) - [kakohegyi](https://github.com/kakohegyi)
* Update SwOS and add support for SwOSLite ([#18718](https://github.com/librenms/librenms/pull/18718)) - [jezekus](https://github.com/jezekus)
* Add support for HW-Group Perseus ([#18712](https://github.com/librenms/librenms/pull/18712)) - [sandap1](https://github.com/sandap1)
* Add support for Zenitel Devices ([#18711](https://github.com/librenms/librenms/pull/18711)) - [sandap1](https://github.com/sandap1)
* Update Nokia TIMETRA MIBs to latest versions ([#18701](https://github.com/librenms/librenms/pull/18701)) - [peelman](https://github.com/peelman)
* Add TransceiverDiscovery interface implementation for Timos OS ([#18700](https://github.com/librenms/librenms/pull/18700)) - [peelman](https://github.com/peelman)
* Add Transceiver Support to AXOS ([#18699](https://github.com/librenms/librenms/pull/18699)) - [peelman](https://github.com/peelman)
* Add support for Vertiv ITA2 UPS ([#18698](https://github.com/librenms/librenms/pull/18698)) - [sandap1](https://github.com/sandap1)
* Add apdu10150sm support ([#18690](https://github.com/librenms/librenms/pull/18690)) - [peejaychilds](https://github.com/peejaychilds)
* Improve transceiver support for BDCom ([#18684](https://github.com/librenms/librenms/pull/18684)) - [freddy36](https://github.com/freddy36)
* Add support for SmartByte OS ([#18681](https://github.com/librenms/librenms/pull/18681)) - [freddy36](https://github.com/freddy36)
* Add support for insyde - Supervyse (OpenBMC) ([#18679](https://github.com/librenms/librenms/pull/18679)) - [sandap1](https://github.com/sandap1)
* Add SmokeSensors to APC NetBotz 750 ([#18675](https://github.com/librenms/librenms/pull/18675)) - [peejaychilds](https://github.com/peejaychilds)
* Add support of IPBS3 for Ascom devices ([#18668](https://github.com/librenms/librenms/pull/18668)) - [alagoutte](https://github.com/alagoutte)
* Add APC Schneider Electric NetBotz 750 ([#18665](https://github.com/librenms/librenms/pull/18665)) - [peejaychilds](https://github.com/peejaychilds)
* Fix MTS-COM rectopenstate inverted ([#18660](https://github.com/librenms/librenms/pull/18660)) - [jakejakejakejakejakejake](https://github.com/jakejakejakejakejakejake)
* Add support for Cisco FTD 4245. ([#18645](https://github.com/librenms/librenms/pull/18645)) - [xorrkaz](https://github.com/xorrkaz)
* Updated Alpha CXC support (sensors and hardware version) ([#18643](https://github.com/librenms/librenms/pull/18643)) - [laf](https://github.com/laf)
* Add support for Pandacom Equipment ([#18636](https://github.com/librenms/librenms/pull/18636)) - [Serazio](https://github.com/Serazio)
* Updated VyOS detection ([#18624](https://github.com/librenms/librenms/pull/18624)) - [laf](https://github.com/laf)
* Add support for Tailyn Equipment ([#18612](https://github.com/librenms/librenms/pull/18612)) - [sandap1](https://github.com/sandap1)
* Added POE data for Advantech Switches ([#18601](https://github.com/librenms/librenms/pull/18601)) - [sandap1](https://github.com/sandap1)
* Add iDRAC RAID rebuild progress and SSD write endurance ([#18599](https://github.com/librenms/librenms/pull/18599)) - [jediblair](https://github.com/jediblair)
* Incuded 7.4 Audiocodes MIBs and fixed call counter ([#18555](https://github.com/librenms/librenms/pull/18555)) - [Fehler12](https://github.com/Fehler12)
* MIKROTIK-MIB update ([#18479](https://github.com/librenms/librenms/pull/18479)) - [Npeca75](https://github.com/Npeca75)
* Update Tailyn OS logo & os icon from PNG to SVG ([#18695](https://github.com/librenms/librenms/pull/18695)) - [sandap1](https://github.com/sandap1)
* Add CloudLinux OS logos ([#18686](https://github.com/librenms/librenms/pull/18686)) - [lennarttd](https://github.com/lennarttd)

#### Webui

* Devices page fix selected os text ([#18759](https://github.com/librenms/librenms/pull/18759)) - [murrant](https://github.com/murrant)
* Round Celsius temperature values to 2 decimal places ([#18747](https://github.com/librenms/librenms/pull/18747)) - [peelman](https://github.com/peelman)
* Fix ghost X bug in multiport selector widget ([#18707](https://github.com/librenms/librenms/pull/18707)) - [peelman](https://github.com/peelman)
* Add multi-sensor graph aggregation for dashboard widgets ([#18706](https://github.com/librenms/librenms/pull/18706)) - [peelman](https://github.com/peelman)
* Added age and message filter to eventlog widget ([#18687](https://github.com/librenms/librenms/pull/18687)) - [shrank](https://github.com/shrank)
* Allow to hide totals in availability map ([#18677](https://github.com/librenms/librenms/pull/18677)) - [garlic17](https://github.com/garlic17)
* Service last_changed time display bug fix ([#18673](https://github.com/librenms/librenms/pull/18673)) - [jediblair](https://github.com/jediblair)
* Update poller blade to be timezone aware ([#18656](https://github.com/librenms/librenms/pull/18656)) - [eskyuu](https://github.com/eskyuu)
* Panel component body class fix ([#18647](https://github.com/librenms/librenms/pull/18647)) - [murrant](https://github.com/murrant)

#### Alerting

* Fix MTU alert rule ([#18658](https://github.com/librenms/librenms/pull/18658)) - [eskyuu](https://github.com/eskyuu)

#### Graphs

* Fixed service graphs ([#18769](https://github.com/librenms/librenms/pull/18769)) - [laf](https://github.com/laf)

#### Billing

* Fix manage_bills.php CLI bill creation (issue  #18708) ([#18709](https://github.com/librenms/librenms/pull/18709)) - [erdems](https://github.com/erdems)

#### Api

* Added API endpoints for poll information and api response ([#18742](https://github.com/librenms/librenms/pull/18742)) - [laf](https://github.com/laf)

#### Discovery

* Switch scheduled jobs to lnms device:discover ([#18738](https://github.com/librenms/librenms/pull/18738)) - [murrant](https://github.com/murrant)

#### Authentication

* Add missing SSO config definitions and update docs ([#18652](https://github.com/librenms/librenms/pull/18652)) - [VVelox](https://github.com/VVelox)

#### Bug

* Fix scheduler maintenance.log path ([#18775](https://github.com/librenms/librenms/pull/18775)) - [murrant](https://github.com/murrant)
* Fix maintenance:fetch-rss relative path ([#18766](https://github.com/librenms/librenms/pull/18766)) - [murrant](https://github.com/murrant)
* Fix for generic_multi.inc.php ([#18740](https://github.com/librenms/librenms/pull/18740)) - [eskyuu](https://github.com/eskyuu)
* Fix rector mistake ([#18697](https://github.com/librenms/librenms/pull/18697)) - [murrant](https://github.com/murrant)
* Fix mtu status changed event log 1 -> 1 ([#18683](https://github.com/librenms/librenms/pull/18683)) - [murrant](https://github.com/murrant)
* Configure output for all commands ([#18669](https://github.com/librenms/librenms/pull/18669)) - [murrant](https://github.com/murrant)
* Fix alerts in app/Jobs/PingCheck.php ([#18666](https://github.com/librenms/librenms/pull/18666)) - [eskyuu](https://github.com/eskyuu)
* Fix packet size calculation for MTU test ([#18663](https://github.com/librenms/librenms/pull/18663)) - [eskyuu](https://github.com/eskyuu)

#### Cleanup

* Change useless log from warning to debug ([#18765](https://github.com/librenms/librenms/pull/18765)) - [murrant](https://github.com/murrant)
* Drop non-functioning (and redundant) parameter $sloped_mode ([#18694](https://github.com/librenms/librenms/pull/18694)) - [westerterp](https://github.com/westerterp)
* Parse email cleanup ([#18619](https://github.com/librenms/librenms/pull/18619)) - [murrant](https://github.com/murrant)

#### Documentation

* Clarify in the docs to use MIB-NAME::OID ([#18756](https://github.com/librenms/librenms/pull/18756)) - [laf](https://github.com/laf)
* Update Docs for NTP-Server Application rewrite ([#18755](https://github.com/librenms/librenms/pull/18755)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix documentation typos, fix Procurve/Aruba command quotes ([#18682](https://github.com/librenms/librenms/pull/18682)) - [andrewimeson](https://github.com/andrewimeson)
* Add docs about additional_oids ([#18625](https://github.com/librenms/librenms/pull/18625)) - [martinberg](https://github.com/martinberg)

#### Misc

* Fix discover log target ([#18774](https://github.com/librenms/librenms/pull/18774)) - [murrant](https://github.com/murrant)
* Maintenance:cleanup-syslog run hourly ([#18737](https://github.com/librenms/librenms/pull/18737)) - [murrant](https://github.com/murrant)
* Update maintenance:rrd-step to work with rrdcached ([#18623](https://github.com/librenms/librenms/pull/18623)) - [murrant](https://github.com/murrant)

#### Dependencies

* Bump paragonie/sodium_compat from 1.23.0 to 1.24.0 ([#18750](https://github.com/librenms/librenms/pull/18750)) - [dependabot](https://github.com/apps/dependabot)
* Bump composer/composer from 2.9.1 to 2.9.3 ([#18726](https://github.com/librenms/librenms/pull/18726)) - [dependabot](https://github.com/apps/dependabot)
