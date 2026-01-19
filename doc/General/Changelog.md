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
