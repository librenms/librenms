## 25.2.0
*(2025-02-20)*

A big thank you to the following 35 contributors this last month:

  - [murrant](https://github.com/murrant) (42)
  - [laf](https://github.com/laf) (18)
  - [mpikzink](https://github.com/mpikzink) (10)
  - [rhinoau](https://github.com/rhinoau) (8)
  - [dot-mike](https://github.com/dot-mike) (3)
  - [eskyuu](https://github.com/eskyuu) (3)
  - [gcaceres123](https://github.com/gcaceres123) (2)
  - [slashdoom](https://github.com/slashdoom) (2)
  - [djamp42](https://github.com/djamp42) (2)
  - [rudybroersma](https://github.com/rudybroersma) (2)
  - [mjonkers1989](https://github.com/mjonkers1989) (2)
  - [gunkaaa](https://github.com/gunkaaa) (2)
  - [w1ll14m](https://github.com/w1ll14m) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [lanbowan-eric](https://github.com/lanbowan-eric) (1)
  - [alagoutte](https://github.com/alagoutte) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [garryshtern](https://github.com/garryshtern) (1)
  - [RayaneB75](https://github.com/RayaneB75) (1)
  - [ssvenn](https://github.com/ssvenn) (1)
  - [georgetasioulis](https://github.com/georgetasioulis) (1)
  - [Martin22](https://github.com/Martin22) (1)
  - [jasoncheng7115](https://github.com/jasoncheng7115) (1)
  - [trwhiteaker](https://github.com/trwhiteaker) (1)
  - [electrocret](https://github.com/electrocret) (1)
  - [STRUBartacus](https://github.com/STRUBartacus) (1)
  - [visbits](https://github.com/visbits) (1)
  - [Sweeny42](https://github.com/Sweeny42) (1)
  - [DBardino](https://github.com/DBardino) (1)
  - [Galileo77](https://github.com/Galileo77) (1)
  - [Cameron-84](https://github.com/Cameron-84) (1)
  - [Torstein-Eide](https://github.com/Torstein-Eide) (1)
  - [mggaskill](https://github.com/mggaskill) (1)
  - [JFisherDNS](https://github.com/JFisherDNS) (1)
  - [Calvario](https://github.com/Calvario) (1)

Thanks to maintainers and others who helped with pull requests this month:

  - [laf](https://github.com/laf) (49)
  - [murrant](https://github.com/murrant) (26)
  - [Jellyfrog](https://github.com/Jellyfrog) (16)
  - [PipoCanaja](https://github.com/PipoCanaja) (7)
  - [electrocret](https://github.com/electrocret) (2)
  - [ottorei](https://github.com/ottorei) (1)

#### Feature
* MAC OUI Vendor lookup ([#17049](https://github.com/librenms/librenms/pull/17049)) - [murrant](https://github.com/murrant)

#### Breaking Change
* Storage Module update ([#17024](https://github.com/librenms/librenms/pull/17024)) - [murrant](https://github.com/murrant)
* Moved currentInUse from custom table to sensors for Cisco ASA ([#16952](https://github.com/librenms/librenms/pull/16952)) - [laf](https://github.com/laf)
* Convert "Recommended Replace Date" from count to runtime sensor ([#16892](https://github.com/librenms/librenms/pull/16892)) - [mpikzink](https://github.com/mpikzink)

#### Security
* Web add hosts fixes ([#17162](https://github.com/librenms/librenms/pull/17162)) - [murrant](https://github.com/murrant)

#### Device
* Add support for wis bridge ([#17168](https://github.com/librenms/librenms/pull/17168)) - [lanbowan-eric](https://github.com/lanbowan-eric)
* Add ArubaCX Transceiver ([#17167](https://github.com/librenms/librenms/pull/17167)) - [alagoutte](https://github.com/alagoutte)
* Updated Moxa vendor logo ([#17146](https://github.com/librenms/librenms/pull/17146)) - [rhinoau](https://github.com/rhinoau)
* Opengear devices: Add missing sensors & support for new CM8100 model ([#17144](https://github.com/librenms/librenms/pull/17144)) - [garryshtern](https://github.com/garryshtern)
* Add power supply status to Yunshan OS (Huawei devices) ([#17143](https://github.com/librenms/librenms/pull/17143)) - [gcaceres123](https://github.com/gcaceres123)
* Add power supply status to VRP OS (Huawei devices) ([#17142](https://github.com/librenms/librenms/pull/17142)) - [gcaceres123](https://github.com/gcaceres123)
* Fix FS S3400 series polling using fs-bdcom mib ([#17141](https://github.com/librenms/librenms/pull/17141)) - [RayaneB75](https://github.com/RayaneB75)
* Added additional snr support ([#17133](https://github.com/librenms/librenms/pull/17133)) - [laf](https://github.com/laf)
* Add Palo Alto Panorama Server status ([#17119](https://github.com/librenms/librenms/pull/17119)) - [rhinoau](https://github.com/rhinoau)
* Workaround for Routeros LLDP local port index ([#17118](https://github.com/librenms/librenms/pull/17118)) - [eskyuu](https://github.com/eskyuu)
* Initial detection for Keenetic devices ([#17117](https://github.com/librenms/librenms/pull/17117)) - [rhinoau](https://github.com/rhinoau)
* Imcopower - added alarms and renamed temperature sensor ([#17113](https://github.com/librenms/librenms/pull/17113)) - [Martin22](https://github.com/Martin22)
* CIMC C220 M6 Model / Serial / Version Fix ([#17093](https://github.com/librenms/librenms/pull/17093)) - [djamp42](https://github.com/djamp42)
* Added support for WitchOS by Teltonika ([#17074](https://github.com/librenms/librenms/pull/17074)) - [laf](https://github.com/laf)
* Updated detection for Tachyon devices ([#17073](https://github.com/librenms/librenms/pull/17073)) - [laf](https://github.com/laf)
* Add Meraki MR WiFi Frequency polling ([#17069](https://github.com/librenms/librenms/pull/17069)) - [rudybroersma](https://github.com/rudybroersma)
* Repair support for Meraki MR28 accesspoints. ([#17065](https://github.com/librenms/librenms/pull/17065)) - [rudybroersma](https://github.com/rudybroersma)
* Hiveos has also added IQ Engine to the sysdescription ([#17063](https://github.com/librenms/librenms/pull/17063)) - [mjonkers1989](https://github.com/mjonkers1989)
* Additional support for SM-OS ([#17061](https://github.com/librenms/librenms/pull/17061)) - [laf](https://github.com/laf)
* Fix cumulus mellanox test data ([#17056](https://github.com/librenms/librenms/pull/17056)) - [murrant](https://github.com/murrant)
* Aruba Instant use SnmpQuery ([#17034](https://github.com/librenms/librenms/pull/17034)) - [murrant](https://github.com/murrant)
* Convert timos to SnmpQuery ([#17006](https://github.com/librenms/librenms/pull/17006)) - [murrant](https://github.com/murrant)
* Adtran ALM series ([#16984](https://github.com/librenms/librenms/pull/16984)) - [murrant](https://github.com/murrant)
* Viptela vendor - Processor and Memory fix ([#16983](https://github.com/librenms/librenms/pull/16983)) - [Cameron-84](https://github.com/Cameron-84)
* Add voltage and power sensors for Digipower PDUs ([#16803](https://github.com/librenms/librenms/pull/16803)) - [gunkaaa](https://github.com/gunkaaa)
* Add authz_status field to Arubaos-CX NAC ([#16453](https://github.com/librenms/librenms/pull/16453)) - [JFisherDNS](https://github.com/JFisherDNS)

#### Webui
* Add Device page default to preferred SNMP settings ([#17131](https://github.com/librenms/librenms/pull/17131)) - [rhinoau](https://github.com/rhinoau)
* Increased z-index for navbar css to ensure it stays on top. ([#17115](https://github.com/librenms/librenms/pull/17115)) - [ssvenn](https://github.com/ssvenn)
* Fix Proxmox module: Correct parameter usage for VM and device ID ([#17114](https://github.com/librenms/librenms/pull/17114)) - [georgetasioulis](https://github.com/georgetasioulis)
* Adding the new OPNsense Logo ([#17079](https://github.com/librenms/librenms/pull/17079)) - [STRUBartacus](https://github.com/STRUBartacus)
* Update the ordering of sensors for Overview page ([#17038](https://github.com/librenms/librenms/pull/17038)) - [laf](https://github.com/laf)
* Handle orphaned ports in port search ([#17037](https://github.com/librenms/librenms/pull/17037)) - [murrant](https://github.com/murrant)
* Dark mode for the new Sensor graphs addition ([#17015](https://github.com/librenms/librenms/pull/17015)) - [DBardino](https://github.com/DBardino)

#### Alerting
* Failed with 415 with content type not supported issue ([#17110](https://github.com/librenms/librenms/pull/17110)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Updated Grafana transport and docs to support richer information ([#16978](https://github.com/librenms/librenms/pull/16978)) - [laf](https://github.com/laf)
* Added ZenDuty Transport ([#16972](https://github.com/librenms/librenms/pull/16972)) - [laf](https://github.com/laf)
* Fix Alert diff, Add new AlertStatus changed, Fix AlertLog UI not showing the correct details ([#16313](https://github.com/librenms/librenms/pull/16313)) - [Calvario](https://github.com/Calvario)

#### Graphs
* Graph has trouble detecting the range if the sensor is constant ([#17096](https://github.com/librenms/librenms/pull/17096)) - [mpikzink](https://github.com/mpikzink)
* Fix multi port graph ([#17057](https://github.com/librenms/librenms/pull/17057)) - [murrant](https://github.com/murrant)

#### Maps
* Fullscreen map fixes ([#17136](https://github.com/librenms/librenms/pull/17136)) - [dot-mike](https://github.com/dot-mike)
* Map not working on device overview with custom engine ([#17134](https://github.com/librenms/librenms/pull/17134)) - [dot-mike](https://github.com/dot-mike)
* Add custom maps node warning state display ([#17036](https://github.com/librenms/librenms/pull/17036)) - [rhinoau](https://github.com/rhinoau)

#### Discovery
* Fix module override submodules ([#17081](https://github.com/librenms/librenms/pull/17081)) - [murrant](https://github.com/murrant)
* Work around bad bridge-mib implementations ([#17030](https://github.com/librenms/librenms/pull/17030)) - [murrant](https://github.com/murrant)

#### Polling
* Better handle issues with ipmitool/freeipmi so we try other types as … ([#17066](https://github.com/librenms/librenms/pull/17066)) - [visbits](https://github.com/visbits)

#### Bug
* Fix wlanAPChInterferenceIndex formula. Fixes Numeric value out of range: 1264 Out of range value for column 'interference' ([#17171](https://github.com/librenms/librenms/pull/17171)) - [w1ll14m](https://github.com/w1ll14m)
* Fix storage_perc_warn not being applied to new storages ([#17104](https://github.com/librenms/librenms/pull/17104)) - [murrant](https://github.com/murrant)
* Fix port churn port assoc mode if is non-existent field ([#17103](https://github.com/librenms/librenms/pull/17103)) - [murrant](https://github.com/murrant)
* Fix poller submodule support ([#17102](https://github.com/librenms/librenms/pull/17102)) - [murrant](https://github.com/murrant)
* Fix OS override in device settings ([#17095](https://github.com/librenms/librenms/pull/17095)) - [murrant](https://github.com/murrant)
* Re-add ifIndex index to ports table ([#17077](https://github.com/librenms/librenms/pull/17077)) - [murrant](https://github.com/murrant)
* Replace use of $config with Config::get() in poll-billing ([#17072](https://github.com/librenms/librenms/pull/17072)) - [laf](https://github.com/laf)
* Fix error in arp-table module ([#17031](https://github.com/librenms/librenms/pull/17031)) - [murrant](https://github.com/murrant)

#### Refactor
* Remove ObjectCache notifications page ([#17100](https://github.com/librenms/librenms/pull/17100)) - [murrant](https://github.com/murrant)
* Convert Wireless to a modern module ([#17086](https://github.com/librenms/librenms/pull/17086)) - [murrant](https://github.com/murrant)
* Remove snmp_getnext_multi() ([#17048](https://github.com/librenms/librenms/pull/17048)) - [murrant](https://github.com/murrant)
* Get_device_id_by_port_id(x) =\> PortCache::get() ([#16969](https://github.com/librenms/librenms/pull/16969)) - [mpikzink](https://github.com/mpikzink)
* Zeropad() =\> Str::padLeft() ([#16960](https://github.com/librenms/librenms/pull/16960)) - [mpikzink](https://github.com/mpikzink)
* Improve logging for use of values from SNMP;  improve logging for determining multiplier/divisor from YAML ([#16949](https://github.com/librenms/librenms/pull/16949)) - [gunkaaa](https://github.com/gunkaaa)
* Showconfig.inc.php changed svn_log() and svn_diff() to Process() ([#16483](https://github.com/librenms/librenms/pull/16483)) - [mggaskill](https://github.com/mggaskill)

#### Cleanup
* Add enable_syslog_hooks config defaults ([#17130](https://github.com/librenms/librenms/pull/17130)) - [rhinoau](https://github.com/rhinoau)
* Sizeof() =\> count() ([#17108](https://github.com/librenms/librenms/pull/17108)) - [mpikzink](https://github.com/mpikzink)
* Remove unused files ([#17101](https://github.com/librenms/librenms/pull/17101)) - [murrant](https://github.com/murrant)
* Remove Create sensor to state index ([#17097](https://github.com/librenms/librenms/pull/17097)) - [mpikzink](https://github.com/mpikzink)
* Check if json file is existing when saving test data ([#17087](https://github.com/librenms/librenms/pull/17087)) - [murrant](https://github.com/murrant)
* Remove echo calls from sensors polling ([#17076](https://github.com/librenms/librenms/pull/17076)) - [murrant](https://github.com/murrant)
* Remove echo from ports polling module ([#17075](https://github.com/librenms/librenms/pull/17075)) - [murrant](https://github.com/murrant)
* Remove some unused functions (part 2) ([#17013](https://github.com/librenms/librenms/pull/17013)) - [mpikzink](https://github.com/mpikzink)
* Remove unix agent global usage ([#17003](https://github.com/librenms/librenms/pull/17003)) - [murrant](https://github.com/murrant)

#### Documentation
* InfluxDBv2.md Fix ([#17124](https://github.com/librenms/librenms/pull/17124)) - [slashdoom](https://github.com/slashdoom)
* Update Custom-Graphs.md ([#17084](https://github.com/librenms/librenms/pull/17084)) - [slashdoom](https://github.com/slashdoom)
* Update Macros.md ([#17064](https://github.com/librenms/librenms/pull/17064)) - [dot-mike](https://github.com/dot-mike)
* Lnms dev:check modules only ([#17044](https://github.com/librenms/librenms/pull/17044)) - [murrant](https://github.com/murrant)
* Api force_add requires credentials ([#17019](https://github.com/librenms/librenms/pull/17019)) - [murrant](https://github.com/murrant)
* Doc application and RRDCached, refactoring and formatting ([#16920](https://github.com/librenms/librenms/pull/16920)) - [Torstein-Eide](https://github.com/Torstein-Eide)

#### Translation
* Settings typo ([#17083](https://github.com/librenms/librenms/pull/17083)) - [electrocret](https://github.com/electrocret)
* German typo correction for "channel" ([#17011](https://github.com/librenms/librenms/pull/17011)) - [Galileo77](https://github.com/Galileo77)

#### Tests
* Qos test fix ([#17050](https://github.com/librenms/librenms/pull/17050)) - [eskyuu](https://github.com/eskyuu)
* Wireless tests order data ([#17016](https://github.com/librenms/librenms/pull/17016)) - [murrant](https://github.com/murrant)
* Fix Tests for PHP IPv6 reserved handling changes ([#17009](https://github.com/librenms/librenms/pull/17009)) - [murrant](https://github.com/murrant)

#### Misc
* Updated Siklu vendor logo ([#17120](https://github.com/librenms/librenms/pull/17120)) - [rhinoau](https://github.com/rhinoau)
* Update detection for Planet WDAP devices ([#17116](https://github.com/librenms/librenms/pull/17116)) - [rhinoau](https://github.com/rhinoau)
* Fix comment syntax in rrdcached.service ([#17109](https://github.com/librenms/librenms/pull/17109)) - [trwhiteaker](https://github.com/trwhiteaker)
* Add support for Cisco CIMC State Sensors ([#17085](https://github.com/librenms/librenms/pull/17085)) - [djamp42](https://github.com/djamp42)
* Correct accidental addition of $agent_raw variable in unix-agent.inc.php ([#17060](https://github.com/librenms/librenms/pull/17060)) - [Sweeny42](https://github.com/Sweeny42)
* Storage deleted removal notification and alert rule ([#17055](https://github.com/librenms/librenms/pull/17055)) - [murrant](https://github.com/murrant)
* Add enable_proxmox to config defs ([#17032](https://github.com/librenms/librenms/pull/17032)) - [murrant](https://github.com/murrant)
* Remove some unused functions ([#17005](https://github.com/librenms/librenms/pull/17005)) - [mpikzink](https://github.com/mpikzink)
* StringHelper::shortenText(x) =\> Str::limit(x) ([#16973](https://github.com/librenms/librenms/pull/16973)) - [mpikzink](https://github.com/mpikzink)
* Allow ifIndex swaps during port discovery ([#16686](https://github.com/librenms/librenms/pull/16686)) - [eskyuu](https://github.com/eskyuu)

#### Internal Features
* Show unused cached snmp queries ([#17004](https://github.com/librenms/librenms/pull/17004)) - [murrant](https://github.com/murrant)
* Implement PortCache ([#17002](https://github.com/librenms/librenms/pull/17002)) - [murrant](https://github.com/murrant)

#### Mibs
* Update SCHLEIFENBAUER Mibs ([#17067](https://github.com/librenms/librenms/pull/17067)) - [mjonkers1989](https://github.com/mjonkers1989)

#### Dependencies
* Bump elliptic from 6.6.0 to 6.6.1 ([#17169](https://github.com/librenms/librenms/pull/17169)) - [dependabot](https://github.com/apps/dependabot)
* Update ENTITY and fix consequences ([#17147](https://github.com/librenms/librenms/pull/17147)) - [PipoCanaja](https://github.com/PipoCanaja)
* Update PHP dependencies ([#17059](https://github.com/librenms/librenms/pull/17059)) - [murrant](https://github.com/murrant)

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
