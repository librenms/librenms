## 23.2.0
*(2023-02-23)*

A big thank you to the following 20 contributors this last month:

  - [electrocret](https://github.com/electrocret) (5)
  - [fbouynot](https://github.com/fbouynot) (5)
  - [hanserasmus](https://github.com/hanserasmus) (3)
  - [VVelox](https://github.com/VVelox) (3)
  - [guipoletto](https://github.com/guipoletto) (1)
  - [westerterp](https://github.com/westerterp) (1)
  - [goebelmeier](https://github.com/goebelmeier) (1)
  - [MarlinMr](https://github.com/MarlinMr) (1)
  - [florisvdk](https://github.com/florisvdk) (1)
  - [kylegordon](https://github.com/kylegordon) (1)
  - [zenbeam](https://github.com/zenbeam) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [gilrim](https://github.com/gilrim) (1)
  - [murrant](https://github.com/murrant) (1)
  - [noaheroufus](https://github.com/noaheroufus) (1)
  - [AleksNovak](https://github.com/AleksNovak) (1)
  - [tristanbob](https://github.com/tristanbob) (1)
  - [tim427](https://github.com/tim427) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [bonzo81](https://github.com/bonzo81) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (22)
  - [murrant](https://github.com/murrant) (9)
  - [ottorei](https://github.com/ottorei) (4)
  - [crazy-max](https://github.com/crazy-max) (1)
  - [VVelox](https://github.com/VVelox) (1)
  - [westerterp](https://github.com/westerterp) (1)

#### Feature
* Add display query to list_devices function ([#14747](https://github.com/librenms/librenms/pull/14747)) - [bonzo81](https://github.com/bonzo81)

#### Device
* Fortigate IPS Intrusions detected stats ([#14857](https://github.com/librenms/librenms/pull/14857)) - [electrocret](https://github.com/electrocret)
* Add support for Vsol v1600d EPON OLT ([#14853](https://github.com/librenms/librenms/pull/14853)) - [guipoletto](https://github.com/guipoletto)
* Fix discovery for apc ats ([#14837](https://github.com/librenms/librenms/pull/14837)) - [florisvdk](https://github.com/florisvdk)
* Include Samsung X Series printers ([#14831](https://github.com/librenms/librenms/pull/14831)) - [kylegordon](https://github.com/kylegordon)
* EdgeOS OLT new sensors and changes ([#14807](https://github.com/librenms/librenms/pull/14807)) - [noaheroufus](https://github.com/noaheroufus)
* Update XDP string to exclude modern Cisco lightweight APs from discovery ([#14803](https://github.com/librenms/librenms/pull/14803)) - [tristanbob](https://github.com/tristanbob)
* Extend filtering of graphs device_bits on cisco ASA ([#14796](https://github.com/librenms/librenms/pull/14796)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Alerting
* Fix MS Teams alert transport, correct HTTP header ([#14843](https://github.com/librenms/librenms/pull/14843)) - [goebelmeier](https://github.com/goebelmeier)
* Add support for topics ([#14804](https://github.com/librenms/librenms/pull/14804)) - [AleksNovak](https://github.com/AleksNovak)

#### Graphs
* Update HV::Monitor support to use generic_stats.inc.php ([#14814](https://github.com/librenms/librenms/pull/14814)) - [VVelox](https://github.com/VVelox)
* Only show Eth errors when Etherlike is enabled ([#14784](https://github.com/librenms/librenms/pull/14784)) - [electrocret](https://github.com/electrocret)

#### Applications
* Add a few more graphs for CAPEv2 ([#14813](https://github.com/librenms/librenms/pull/14813)) - [VVelox](https://github.com/VVelox)
* CAPEv2 support ([#14801](https://github.com/librenms/librenms/pull/14801)) - [VVelox](https://github.com/VVelox)

#### Api
* Use Oxidized API client and add support for groups on config get ([#14750](https://github.com/librenms/librenms/pull/14750)) - [electrocret](https://github.com/electrocret)

#### Oxidized
* Show display name in Oxidized config search ([#14800](https://github.com/librenms/librenms/pull/14800)) - [electrocret](https://github.com/electrocret)

#### Bug
* Revert "Fix entity-state polling/discovery" ([#14811](https://github.com/librenms/librenms/pull/14811)) - [murrant](https://github.com/murrant)

#### Documentation
* Fix Okta image in docs ([#14848](https://github.com/librenms/librenms/pull/14848)) - [westerterp](https://github.com/westerterp)
* Change socialite settings URI ([#14845](https://github.com/librenms/librenms/pull/14845)) - [fbouynot](https://github.com/fbouynot)
* Fix possible confusion around php-fpm settings ([#14844](https://github.com/librenms/librenms/pull/14844)) - [hanserasmus](https://github.com/hanserasmus)
* Use docker compose v2 ([#14841](https://github.com/librenms/librenms/pull/14841)) - [MarlinMr](https://github.com/MarlinMr)
* Fix typo in Oauth doc ([#14840](https://github.com/librenms/librenms/pull/14840)) - [fbouynot](https://github.com/fbouynot)
* Add Zenduty Integration info to Transports.md docs ([#14826](https://github.com/librenms/librenms/pull/14826)) - [zenbeam](https://github.com/zenbeam)
* Update config docs - dump current config ([#14820](https://github.com/librenms/librenms/pull/14820)) - [hanserasmus](https://github.com/hanserasmus)
* Add SELinux instructions for nginx monitoring ([#14812](https://github.com/librenms/librenms/pull/14812)) - [fbouynot](https://github.com/fbouynot)
* Add SELinux instructions for systemd monitoring ([#14809](https://github.com/librenms/librenms/pull/14809)) - [fbouynot](https://github.com/fbouynot)
* Add SELinux instructions for systemd monitoring ([#14806](https://github.com/librenms/librenms/pull/14806)) - [fbouynot](https://github.com/fbouynot)
* Debian 11 Sury DPA provides PHP 8.2 ([#14798](https://github.com/librenms/librenms/pull/14798)) - [tim427](https://github.com/tim427)

#### Misc
* Use Device displayname on VRF page ([#14851](https://github.com/librenms/librenms/pull/14851)) - [electrocret](https://github.com/electrocret)
* Fix empty output on validation test ([#14822](https://github.com/librenms/librenms/pull/14822)) - [hanserasmus](https://github.com/hanserasmus)

#### Dependencies
* Bump symfony/http-kernel from 5.4.16 to 5.4.20 ([#14824](https://github.com/librenms/librenms/pull/14824)) - [dependabot](https://github.com/apps/dependabot)


## 23.1.0
*(2023-01-24)*

A big thank you to the following 19 contributors this last month:

  - [electrocret](https://github.com/electrocret) (3)
  - [peelman](https://github.com/peelman) (3)
  - [jasoncheng7115](https://github.com/jasoncheng7115) (2)
  - [not-known](https://github.com/not-known) (2)
  - [da-me](https://github.com/da-me) (1)
  - [systeembeheerder](https://github.com/systeembeheerder) (1)
  - [fdomain](https://github.com/fdomain) (1)
  - [jaannnis](https://github.com/jaannnis) (1)
  - [knpo](https://github.com/knpo) (1)
  - [trs80](https://github.com/trs80) (1)
  - [MANT5149](https://github.com/MANT5149) (1)
  - [LoveSkylark](https://github.com/LoveSkylark) (1)
  - [support-capensis](https://github.com/support-capensis) (1)
  - [zeroservices](https://github.com/zeroservices) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [dennypage](https://github.com/dennypage) (1)
  - [carbinefreak](https://github.com/carbinefreak) (1)
  - [bnerickson](https://github.com/bnerickson) (1)
  - [Blinq-SanderBlom](https://github.com/Blinq-SanderBlom) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (23)
  - [murrant](https://github.com/murrant) (2)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)

#### Device
* Changed discovery OID in ets.yaml ([#14795](https://github.com/librenms/librenms/pull/14795)) - [da-me](https://github.com/da-me)
* Added Support for SNS-3615-K9 ([#14792](https://github.com/librenms/librenms/pull/14792)) - [jaannnis](https://github.com/jaannnis)
* Routeros fix lldp discovery on 7.7+ ([#14791](https://github.com/librenms/librenms/pull/14791)) - [knpo](https://github.com/knpo)
* Fix cisco-flash ([#14772](https://github.com/librenms/librenms/pull/14772)) - [electrocret](https://github.com/electrocret)
* Eaton matrix wattage fix ([#14770](https://github.com/librenms/librenms/pull/14770)) - [peelman](https://github.com/peelman)
* Add Universal Input Output support for APC ([#14766](https://github.com/librenms/librenms/pull/14766)) - [dennypage](https://github.com/dennypage)
* Eltek Enexus; Fix SmartpackS divisor and hardware discovery ([#14762](https://github.com/librenms/librenms/pull/14762)) - [peelman](https://github.com/peelman)
* Nokia PMC Microwave Improvements ([#14761](https://github.com/librenms/librenms/pull/14761)) - [carbinefreak](https://github.com/carbinefreak)
* Add AXOS sensor data for PON Transceivers (the only transceivers Cali… ([#14741](https://github.com/librenms/librenms/pull/14741)) - [peelman](https://github.com/peelman)
* Adding discovery of LSI MegaRAID Device Media Errors, Other Errors, a… ([#14729](https://github.com/librenms/librenms/pull/14729)) - [bnerickson](https://github.com/bnerickson)
* Stop net-snmp from interpreting the octet-string from rttMonEchoAdmin… ([#14676](https://github.com/librenms/librenms/pull/14676)) - [not-known](https://github.com/not-known)
* Add state of the Fortigate link monitor health checks ([#14675](https://github.com/librenms/librenms/pull/14675)) - [not-known](https://github.com/not-known)
* Adding support for loop-telecom devices ([#14674](https://github.com/librenms/librenms/pull/14674)) - [Blinq-SanderBlom](https://github.com/Blinq-SanderBlom)

#### Oxidized
* Adding Fortigate switches to Oxidized model mapping config ([#14782](https://github.com/librenms/librenms/pull/14782)) - [LoveSkylark](https://github.com/LoveSkylark)

#### Bug
* Fix entity-state polling/discovery ([#14793](https://github.com/librenms/librenms/pull/14793)) - [fdomain](https://github.com/fdomain)
* Update PingCheck.php to remove duplicate "Device status changed to ... from icmp check." event ([#14785](https://github.com/librenms/librenms/pull/14785)) - [MANT5149](https://github.com/MANT5149)
* Fix calculated dbm ([#14771](https://github.com/librenms/librenms/pull/14771)) - [electrocret](https://github.com/electrocret)

#### Documentation
* Update Syslog.md ([#14794](https://github.com/librenms/librenms/pull/14794)) - [systeembeheerder](https://github.com/systeembeheerder)
* Update Authentication.md ([#14788](https://github.com/librenms/librenms/pull/14788)) - [trs80](https://github.com/trs80)
* Fix Doc Link for Canopsis transport ([#14778](https://github.com/librenms/librenms/pull/14778)) - [support-capensis](https://github.com/support-capensis)
* Update ElasticSearch Index pattern for php 8.1 changes ([#14775](https://github.com/librenms/librenms/pull/14775)) - [zeroservices](https://github.com/zeroservices)

#### Translation
* Update zh-TW.json ([#14765](https://github.com/librenms/librenms/pull/14765)) - [jasoncheng7115](https://github.com/jasoncheng7115)
* Create files with Traditional Chinese translation ([#14764](https://github.com/librenms/librenms/pull/14764)) - [jasoncheng7115](https://github.com/jasoncheng7115)

#### Misc
* Use displayname instead of hostname for Device Maintenance Modal Title ([#14681](https://github.com/librenms/librenms/pull/14681)) - [electrocret](https://github.com/electrocret)

#### Dependencies
* Bump json5 from 1.0.1 to 1.0.2 ([#14774](https://github.com/librenms/librenms/pull/14774)) - [dependabot](https://github.com/apps/dependabot)


## 22.12.0
*(2022-12-28)*

A big thank you to the following 21 contributors this last month:

  - [murrant](https://github.com/murrant) (10)
  - [electrocret](https://github.com/electrocret) (4)
  - [bnerickson](https://github.com/bnerickson) (4)
  - [peelman](https://github.com/peelman) (2)
  - [bonzo81](https://github.com/bonzo81) (2)
  - [gdepeyrot](https://github.com/gdepeyrot) (2)
  - [bogdanrotariu](https://github.com/bogdanrotariu) (2)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (2)
  - [LoveSkylark](https://github.com/LoveSkylark) (1)
  - [fcqpl](https://github.com/fcqpl) (1)
  - [angeletdemon](https://github.com/angeletdemon) (1)
  - [alchemyx](https://github.com/alchemyx) (1)
  - [rinsekloek](https://github.com/rinsekloek) (1)
  - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [gewuerfelt](https://github.com/gewuerfelt) (1)
  - [tuxgasy](https://github.com/tuxgasy) (1)
  - [hugalafutro](https://github.com/hugalafutro) (1)
  - [nightcore500](https://github.com/nightcore500) (1)
  - [mrwold](https://github.com/mrwold) (1)
  - [rhinoau](https://github.com/rhinoau) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (15)
  - [murrant](https://github.com/murrant) (14)
  - [PipoCanaja](https://github.com/PipoCanaja) (3)
  - [ottorei](https://github.com/ottorei) (3)
  - [SeeMyPing](https://github.com/SeeMyPing) (1)

#### Feature
* Pre-Install settings ([#13906](https://github.com/librenms/librenms/pull/13906)) - [murrant](https://github.com/murrant)

#### Device
* Additional sensors ([#14756](https://github.com/librenms/librenms/pull/14756)) - [peelman](https://github.com/peelman)
* Update fs-nmu.inc.php ([#14702](https://github.com/librenms/librenms/pull/14702)) - [gewuerfelt](https://github.com/gewuerfelt)
* Support Huawei SMU02B ([#14673](https://github.com/librenms/librenms/pull/14673)) - [nightcore500](https://github.com/nightcore500)
* Use 'counter' RRD type for some TrueNAS sensors ([#14670](https://github.com/librenms/librenms/pull/14670)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Device - Add support for "Smartpack R" ([#14665](https://github.com/librenms/librenms/pull/14665)) - [mrwold](https://github.com/mrwold)
* Added Moxa switching ifAlias/portName mapping ([#14633](https://github.com/librenms/librenms/pull/14633)) - [rhinoau](https://github.com/rhinoau)

#### Webui
* Fix port speed setting feedback ([#14743](https://github.com/librenms/librenms/pull/14743)) - [murrant](https://github.com/murrant)
* Fix Cimc on Overview ([#14727](https://github.com/librenms/librenms/pull/14727)) - [electrocret](https://github.com/electrocret)
* Search devices also in display ([#14714](https://github.com/librenms/librenms/pull/14714)) - [gdepeyrot](https://github.com/gdepeyrot)
* Fix check rrd file exists with remote rrdcached ([#14690](https://github.com/librenms/librenms/pull/14690)) - [tuxgasy](https://github.com/tuxgasy)

#### Alerting
* Adding sample alert for systemd application ([#14711](https://github.com/librenms/librenms/pull/14711)) - [bnerickson](https://github.com/bnerickson)
* Polling poller debug ([#14691](https://github.com/librenms/librenms/pull/14691)) - [electrocret](https://github.com/electrocret)
* Add -I flag to Traceroute cmd ([#14667](https://github.com/librenms/librenms/pull/14667)) - [electrocret](https://github.com/electrocret)

#### Graphs
* Fix graph output base64 ([#14701](https://github.com/librenms/librenms/pull/14701)) - [murrant](https://github.com/murrant)
* Fix some graphs not respecting device display name ([#14684](https://github.com/librenms/librenms/pull/14684)) - [murrant](https://github.com/murrant)
* Adding Average into graphs ([#14679](https://github.com/librenms/librenms/pull/14679)) - [bogdanrotariu](https://github.com/bogdanrotariu)

#### Applications
* Update Nvidia application - values in wrong charts ([#14736](https://github.com/librenms/librenms/pull/14736)) - [fcqpl](https://github.com/fcqpl)
* Removing max polling restriction for wireguard traffic RRD data ([#14710](https://github.com/librenms/librenms/pull/14710)) - [bnerickson](https://github.com/bnerickson)

#### Api
* Fix for RIPE NCC API Tools ([#14757](https://github.com/librenms/librenms/pull/14757)) - [LoveSkylark](https://github.com/LoveSkylark)
* API list_devices update with sysName, location_id and type ([#14731](https://github.com/librenms/librenms/pull/14731)) - [bonzo81](https://github.com/bonzo81)

#### Discovery
* Prevent spurious MAC changes on multi-interface devices ([#14671](https://github.com/librenms/librenms/pull/14671)) - [TheMysteriousX](https://github.com/TheMysteriousX)

#### Polling
* Fix ArubaInstance channel decode issue ([#14732](https://github.com/librenms/librenms/pull/14732)) - [murrant](https://github.com/murrant)

#### Refactor
* Unify time interval formatting ([#14733](https://github.com/librenms/librenms/pull/14733)) - [murrant](https://github.com/murrant)

#### Documentation
* Install updates for Rocky8 ([#14722](https://github.com/librenms/librenms/pull/14722)) - [angeletdemon](https://github.com/angeletdemon)
* Updating certificate script documentation ([#14721](https://github.com/librenms/librenms/pull/14721)) - [bnerickson](https://github.com/bnerickson)
* Update Docker.md with proper path to unzipped files ([#14717](https://github.com/librenms/librenms/pull/14717)) - [alchemyx](https://github.com/alchemyx)
* Fix typo: purge-port.php file name ([#14712](https://github.com/librenms/librenms/pull/14712)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* Alert template example uses incorrect variable ([#14683](https://github.com/librenms/librenms/pull/14683)) - [electrocret](https://github.com/electrocret)
* Correct squid snmpd.conf proxy host syntax ([#14678](https://github.com/librenms/librenms/pull/14678)) - [hugalafutro](https://github.com/hugalafutro)

#### Tests
* Adding certificate application tests ([#14708](https://github.com/librenms/librenms/pull/14708)) - [bnerickson](https://github.com/bnerickson)

#### Misc
* Add MAC OUI caching when code updates are disabled ([#14713](https://github.com/librenms/librenms/pull/14713)) - [gdepeyrot](https://github.com/gdepeyrot)

#### Internal Features
* Number Casting allow preceding space ([#14699](https://github.com/librenms/librenms/pull/14699)) - [murrant](https://github.com/murrant)
* Improve SnmpResponse value() ([#14605](https://github.com/librenms/librenms/pull/14605)) - [murrant](https://github.com/murrant)

#### Mibs
* Add CISCO-ENTITY-ALARM-MIB file ([#14754](https://github.com/librenms/librenms/pull/14754)) - [bonzo81](https://github.com/bonzo81)
* Update Nokia MIBs to release R22.2 ([#14734](https://github.com/librenms/librenms/pull/14734)) - [peelman](https://github.com/peelman)

#### Dependencies
* Update PHP dependencies (fix dependencies on PHP 8.2) ([#14759](https://github.com/librenms/librenms/pull/14759)) - [murrant](https://github.com/murrant)
* Bump qs and express ([#14705](https://github.com/librenms/librenms/pull/14705)) - [dependabot](https://github.com/apps/dependabot)


## 22.11.0
*(2022-11-24)*

A big thank you to the following 40 contributors this last month:

  - [murrant](https://github.com/murrant) (52)
  - [Jellyfrog](https://github.com/Jellyfrog) (23)
  - [PipoCanaja](https://github.com/PipoCanaja) (9)
  - [bnerickson](https://github.com/bnerickson) (5)
  - [electrocret](https://github.com/electrocret) (4)
  - [SourceDoctor](https://github.com/SourceDoctor) (4)
  - [noaheroufus](https://github.com/noaheroufus) (3)
  - [rhinoau](https://github.com/rhinoau) (3)
  - [mabra94](https://github.com/mabra94) (2)
  - [fcqpl](https://github.com/fcqpl) (2)
  - [kimhaak](https://github.com/kimhaak) (2)
  - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ) (2)
  - [fbouynot](https://github.com/fbouynot) (2)
  - [opalivan](https://github.com/opalivan) (2)
  - [mprins-RAM](https://github.com/mprins-RAM) (2)
  - [VVelox](https://github.com/VVelox) (2)
  - [koocotte](https://github.com/koocotte) (1)
  - [VoipTelCH](https://github.com/VoipTelCH) (1)
  - [cfitzw](https://github.com/cfitzw) (1)
  - [systeembeheerder](https://github.com/systeembeheerder) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [jaaruizgu](https://github.com/jaaruizgu) (1)
  - [pertruccio](https://github.com/pertruccio) (1)
  - [josh-silvas](https://github.com/josh-silvas) (1)
  - [ianhodgson](https://github.com/ianhodgson) (1)
  - [LoveSkylark](https://github.com/LoveSkylark) (1)
  - [talkstraightuk](https://github.com/talkstraightuk) (1)
  - [fufroma](https://github.com/fufroma) (1)
  - [fuzzbawl](https://github.com/fuzzbawl) (1)
  - [otkd](https://github.com/otkd) (1)
  - [kiwibrew](https://github.com/kiwibrew) (1)
  - [luc-ass](https://github.com/luc-ass) (1)
  - [andrekeller](https://github.com/andrekeller) (1)
  - [geg347](https://github.com/geg347) (1)
  - [Olen](https://github.com/Olen) (1)
  - [Frazew](https://github.com/Frazew) (1)
  - [SirMaple](https://github.com/SirMaple) (1)
  - [westerterp](https://github.com/westerterp) (1)
  - [squidly](https://github.com/squidly) (1)
  - [Bierchermuesli](https://github.com/Bierchermuesli) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (53)
  - [murrant](https://github.com/murrant) (44)
  - [PipoCanaja](https://github.com/PipoCanaja) (15)
  - [ottorei](https://github.com/ottorei) (1)
  - [VVelox](https://github.com/VVelox) (1)
  - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ) (1)
  - [fbouynot](https://github.com/fbouynot) (1)

#### Feature
* Split out lnms snmp convenience commands ([#14603](https://github.com/librenms/librenms/pull/14603)) - [murrant](https://github.com/murrant)
* Add --forgot-key to key:rotate command ([#14495](https://github.com/librenms/librenms/pull/14495)) - [murrant](https://github.com/murrant)

#### Security
* Fix Service Template XSS ([#14659](https://github.com/librenms/librenms/pull/14659)) - [murrant](https://github.com/murrant)
* Fix neighbours XSS ([#14658](https://github.com/librenms/librenms/pull/14658)) - [murrant](https://github.com/murrant)
* Fix SNMP trap -\> Eventlog XSS ([#14657](https://github.com/librenms/librenms/pull/14657)) - [murrant](https://github.com/murrant)
* Fix XSS in api access ([#14551](https://github.com/librenms/librenms/pull/14551)) - [murrant](https://github.com/murrant)
* XSS sanitize pwrstatd application script inputs ([#14545](https://github.com/librenms/librenms/pull/14545)) - [bnerickson](https://github.com/bnerickson)

#### Device
* Added divisor to SAF Integra-X temp sensors ([#14655](https://github.com/librenms/librenms/pull/14655)) - [noaheroufus](https://github.com/noaheroufus)
* Corrected ICT MPS hardware detection ([#14654](https://github.com/librenms/librenms/pull/14654)) - [noaheroufus](https://github.com/noaheroufus)
* Adjusted PMP to accomodate various other 450 models. ([#14652](https://github.com/librenms/librenms/pull/14652)) - [noaheroufus](https://github.com/noaheroufus)
* Added support for Eltek Micropack 1U ([#14645](https://github.com/librenms/librenms/pull/14645)) - [fcqpl](https://github.com/fcqpl)
* Added support for Eltek Flatpack S ([#14643](https://github.com/librenms/librenms/pull/14643)) - [fcqpl](https://github.com/fcqpl)
* Improve VRP power display ([#14624](https://github.com/librenms/librenms/pull/14624)) - [PipoCanaja](https://github.com/PipoCanaja)
* Edgeos picked up as generic device ([#14612](https://github.com/librenms/librenms/pull/14612)) - [ianhodgson](https://github.com/ianhodgson)
* Added Moxa EDS-G516E optical sensors ([#14610](https://github.com/librenms/librenms/pull/14610)) - [rhinoau](https://github.com/rhinoau)
* Fix eNexus total current for Smartpack S ([#14606](https://github.com/librenms/librenms/pull/14606)) - [murrant](https://github.com/murrant)
* Disable SNMP Bulk for Delta UPS devices ([#14599](https://github.com/librenms/librenms/pull/14599)) - [fufroma](https://github.com/fufroma)
* Improved AIX os information collection ([#14595](https://github.com/librenms/librenms/pull/14595)) - [murrant](https://github.com/murrant)
* More inclusive Cisco SB os detection ([#14594](https://github.com/librenms/librenms/pull/14594)) - [murrant](https://github.com/murrant)
* IP Infusion OcNOS basic detection ([#14588](https://github.com/librenms/librenms/pull/14588)) - [murrant](https://github.com/murrant)
* Fix APC current divide by zero ([#14578](https://github.com/librenms/librenms/pull/14578)) - [murrant](https://github.com/murrant)
* Extend EfficientIP SolidServer support ([#14549](https://github.com/librenms/librenms/pull/14549)) - [PipoCanaja](https://github.com/PipoCanaja)
* TAIT-Infra93 - Fix state + skip_values for Battery ([#14541](https://github.com/librenms/librenms/pull/14541)) - [opalivan](https://github.com/opalivan)
* Timos - Improve SAP stats graphing ([#14534](https://github.com/librenms/librenms/pull/14534)) - [mabra94](https://github.com/mabra94)
* McAfee Web Proxy with COUNTER rrd_type on sensors ([#14529](https://github.com/librenms/librenms/pull/14529)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix 'bke.yaml' discovery file ([#14524](https://github.com/librenms/librenms/pull/14524)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added support for additional Moxa EDS-P model ([#14523](https://github.com/librenms/librenms/pull/14523)) - [rhinoau](https://github.com/rhinoau)
* Fix awplus ntp polling bug ([#14521](https://github.com/librenms/librenms/pull/14521)) - [murrant](https://github.com/murrant)
* Add discovery support for Riedo Networks UPDU ([#14496](https://github.com/librenms/librenms/pull/14496)) - [andrekeller](https://github.com/andrekeller)
* Use correct description oid ([#14489](https://github.com/librenms/librenms/pull/14489)) - [Jellyfrog](https://github.com/Jellyfrog)
* Correct upsBypassVoltage oid ([#14488](https://github.com/librenms/librenms/pull/14488)) - [Jellyfrog](https://github.com/Jellyfrog)
* Correct variable typo ([#14486](https://github.com/librenms/librenms/pull/14486)) - [Jellyfrog](https://github.com/Jellyfrog)
* Hpe ilo: fix filesystem type regex ([#14485](https://github.com/librenms/librenms/pull/14485)) - [Jellyfrog](https://github.com/Jellyfrog)
* Added Fortigate SD-WAN Health checks ([#14456](https://github.com/librenms/librenms/pull/14456)) - [mprins-RAM](https://github.com/mprins-RAM)
* Dont include empty storage sensor ([#14453](https://github.com/librenms/librenms/pull/14453)) - [Jellyfrog](https://github.com/Jellyfrog)
* Remove undefined sensors ([#14449](https://github.com/librenms/librenms/pull/14449)) - [Jellyfrog](https://github.com/Jellyfrog)
* Use correct low warn limit variable ([#14447](https://github.com/librenms/librenms/pull/14447)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fortinet FortiADC detection and basic statistics ([#14434](https://github.com/librenms/librenms/pull/14434)) - [westerterp](https://github.com/westerterp)
* Device - Add support for Vultan Environmental Monitoring units ([#14401](https://github.com/librenms/librenms/pull/14401)) - [squidly](https://github.com/squidly)
* Device - Basic hatteras dslam integration ([#14115](https://github.com/librenms/librenms/pull/14115)) - [Bierchermuesli](https://github.com/Bierchermuesli)

#### Webui
* Remove ungrouped devices panel ([#14664](https://github.com/librenms/librenms/pull/14664)) - [murrant](https://github.com/murrant)
* Custom port view fix ([#14637](https://github.com/librenms/librenms/pull/14637)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix billing graph divide by zero when the period is zero or doesn't exist ([#14623](https://github.com/librenms/librenms/pull/14623)) - [jaaruizgu](https://github.com/jaaruizgu)
* Fix port neighbors missing ([#14586](https://github.com/librenms/librenms/pull/14586)) - [murrant](https://github.com/murrant)
* Fix some icons ([#14584](https://github.com/librenms/librenms/pull/14584)) - [murrant](https://github.com/murrant)
* [About] Open Laravel and RRDtool link in new tab ([#14568](https://github.com/librenms/librenms/pull/14568)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* [About] Open the contributor list link in a new tab ([#14553](https://github.com/librenms/librenms/pull/14553)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* Fix v2 plugins ([#14506](https://github.com/librenms/librenms/pull/14506)) - [murrant](https://github.com/murrant)
* Check if service_name is empty or equal to service_type ([#14499](https://github.com/librenms/librenms/pull/14499)) - [luc-ass](https://github.com/luc-ass)
* Show id on device dependencies ([#14497](https://github.com/librenms/librenms/pull/14497)) - [SourceDoctor](https://github.com/SourceDoctor)
* Userlist description fix ([#14482](https://github.com/librenms/librenms/pull/14482)) - [mprins-RAM](https://github.com/mprins-RAM)
* Sort alert transport by name ([#14464](https://github.com/librenms/librenms/pull/14464)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Graphs
* Fix device poller modules graph ([#14640](https://github.com/librenms/librenms/pull/14640)) - [murrant](https://github.com/murrant)
* Bug - XDSL module - rrd_def and rrd file definition issue for Actual rate ([#14597](https://github.com/librenms/librenms/pull/14597)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix graph errors wrong content type ([#14574](https://github.com/librenms/librenms/pull/14574)) - [murrant](https://github.com/murrant)
* Improve graph embedded title handling ([#14544](https://github.com/librenms/librenms/pull/14544)) - [murrant](https://github.com/murrant)
* Graphing cleanup ([#14492](https://github.com/librenms/librenms/pull/14492)) - [murrant](https://github.com/murrant)

#### Snmp Traps
* Adds Cisco DHCP Server SNMP MIB and Trap Handlers. ([#14618](https://github.com/librenms/librenms/pull/14618)) - [josh-silvas](https://github.com/josh-silvas)

#### Applications
* Alarm Flag on UPS-Nut Application ([#14635](https://github.com/librenms/librenms/pull/14635)) - [SourceDoctor](https://github.com/SourceDoctor)
* Adding wireguard app to the apps overview page ([#14629](https://github.com/librenms/librenms/pull/14629)) - [bnerickson](https://github.com/bnerickson)
* Adding wireguard application support ([#14625](https://github.com/librenms/librenms/pull/14625)) - [bnerickson](https://github.com/bnerickson)
* Adding systemd service status application ([#14540](https://github.com/librenms/librenms/pull/14540)) - [bnerickson](https://github.com/bnerickson)
* Memcached application: improve error visibility ([#14536](https://github.com/librenms/librenms/pull/14536)) - [murrant](https://github.com/murrant)
* Fix memcached polling bug ([#14501](https://github.com/librenms/librenms/pull/14501)) - [murrant](https://github.com/murrant)
* Add HV Monitor, a generic means for monitoring hypvervisors ([#14218](https://github.com/librenms/librenms/pull/14218)) - [VVelox](https://github.com/VVelox)
* Base64 gzip compression support for json_app_get ([#14169](https://github.com/librenms/librenms/pull/14169)) - [VVelox](https://github.com/VVelox)

#### Api
* Port search API search more than one fields ([#14646](https://github.com/librenms/librenms/pull/14646)) - [murrant](https://github.com/murrant)
* Added disable_notify and location_id ([#14619](https://github.com/librenms/librenms/pull/14619)) - [pertruccio](https://github.com/pertruccio)
* Add sortorder parameter to list_logs ([#14600](https://github.com/librenms/librenms/pull/14600)) - [talkstraightuk](https://github.com/talkstraightuk)
* API restore ability to update purpose and override_sysLocation ([#14596](https://github.com/librenms/librenms/pull/14596)) - [murrant](https://github.com/murrant)
* API graphs, variable whitelist ([#14552](https://github.com/librenms/librenms/pull/14552)) - [murrant](https://github.com/murrant)
* Graph API use new code path ([#14493](https://github.com/librenms/librenms/pull/14493)) - [murrant](https://github.com/murrant)
* Adding device's field in get_alert_rule and list-alert-rules API function (new) ([#14481](https://github.com/librenms/librenms/pull/14481)) - [geg347](https://github.com/geg347)
* Fix update_device hostname handling #14435 ([#14448](https://github.com/librenms/librenms/pull/14448)) - [rhinoau](https://github.com/rhinoau)
* Add Columns to search_ports API function ([#14348](https://github.com/librenms/librenms/pull/14348)) - [electrocret](https://github.com/electrocret)

#### Discovery
* Fix discovery ignores custom ipmi port ([#14660](https://github.com/librenms/librenms/pull/14660)) - [VoipTelCH](https://github.com/VoipTelCH)
* Sensors - Allow changing RRD type in YAML and PHP sensor discovery ([#14208](https://github.com/librenms/librenms/pull/14208)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Polling
* Don't poll sysDescr, sysObjectID, and sysName so frequently ([#14562](https://github.com/librenms/librenms/pull/14562)) - [murrant](https://github.com/murrant)
* Add connection exception handling to all redis calls ([#14467](https://github.com/librenms/librenms/pull/14467)) - [Frazew](https://github.com/Frazew)

#### Authentication
* Update Radius auth to accept permissions attributes ([#14531](https://github.com/librenms/librenms/pull/14531)) - [kimhaak](https://github.com/kimhaak)

#### Bug
* Handle Division by 0 error for SvcFdbTableSize ([#14672](https://github.com/librenms/librenms/pull/14672)) - [mabra94](https://github.com/mabra94)
* Fix sqlite test ([#14642](https://github.com/librenms/librenms/pull/14642)) - [murrant](https://github.com/murrant)
* Remove extends and hardware detection support ([#14626](https://github.com/librenms/librenms/pull/14626)) - [murrant](https://github.com/murrant)
* Set 0 as integer in loading of graylog. ([#14621](https://github.com/librenms/librenms/pull/14621)) - [kimhaak](https://github.com/kimhaak)
* Fix GeocodingHelper type-hinting ([#14604](https://github.com/librenms/librenms/pull/14604)) - [Jellyfrog](https://github.com/Jellyfrog)
* Bug in Ciscowlc AP graphs definition ([#14585](https://github.com/librenms/librenms/pull/14585)) - [PipoCanaja](https://github.com/PipoCanaja)
* SnmpQuery fix mib directory order ([#14580](https://github.com/librenms/librenms/pull/14580)) - [murrant](https://github.com/murrant)
* SnmpQuery Handle empty oids in get more gracefully ([#14577](https://github.com/librenms/librenms/pull/14577)) - [murrant](https://github.com/murrant)
* Fix PTOPO mib in module discovery-protocols ([#14564](https://github.com/librenms/librenms/pull/14564)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix Type error ([#14555](https://github.com/librenms/librenms/pull/14555)) - [fbouynot](https://github.com/fbouynot)
* Correct variable use in billing ([#14533](https://github.com/librenms/librenms/pull/14533)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix sensor limit linked port rules in collection ([#14520](https://github.com/librenms/librenms/pull/14520)) - [electrocret](https://github.com/electrocret)
* EES Rectifier various fixes from MIB + added tests ([#14519](https://github.com/librenms/librenms/pull/14519)) - [opalivan](https://github.com/opalivan)
* Fix fetch ifEntry logic ([#14483](https://github.com/librenms/librenms/pull/14483)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add a few breaks to avoid connection flooding for the irc-bot ([#14479](https://github.com/librenms/librenms/pull/14479)) - [Olen](https://github.com/Olen)

#### Refactor
* Remove Log::event ([#14526](https://github.com/librenms/librenms/pull/14526)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Cleanup
* Fix snmp_disable type ([#14650](https://github.com/librenms/librenms/pull/14650)) - [murrant](https://github.com/murrant)
* Consolidate and improve snmptranslate usage ([#14567](https://github.com/librenms/librenms/pull/14567)) - [murrant](https://github.com/murrant)
* Remove graph_min ([#14561](https://github.com/librenms/librenms/pull/14561)) - [murrant](https://github.com/murrant)
* Remove $_GET from graphs ([#14554](https://github.com/librenms/librenms/pull/14554)) - [murrant](https://github.com/murrant)
* Undefined array key fixes ([#14532](https://github.com/librenms/librenms/pull/14532)) - [Jellyfrog](https://github.com/Jellyfrog)
* Use Collection instead of collect() ([#14527](https://github.com/librenms/librenms/pull/14527)) - [Jellyfrog](https://github.com/Jellyfrog)
* Convert string references to `::class` ([#14508](https://github.com/librenms/librenms/pull/14508)) - [Jellyfrog](https://github.com/Jellyfrog)
* Miscellaneous cleanup - part 5 ([#14502](https://github.com/librenms/librenms/pull/14502)) - [Jellyfrog](https://github.com/Jellyfrog)
* Type API methods and properties ([#14476](https://github.com/librenms/librenms/pull/14476)) - [fbouynot](https://github.com/fbouynot)
* Miscellaneous cleanup - part 4 ([#14452](https://github.com/librenms/librenms/pull/14452)) - [Jellyfrog](https://github.com/Jellyfrog)
* Miscellaneous cleanup - part 3 ([#14450](https://github.com/librenms/librenms/pull/14450)) - [Jellyfrog](https://github.com/Jellyfrog)
* Miscellaneous cleanup, mostly undefined variables - part2 ([#14445](https://github.com/librenms/librenms/pull/14445)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Documentation
* Fix Traceroute debug alert template snippet ([#14668](https://github.com/librenms/librenms/pull/14668)) - [electrocret](https://github.com/electrocret)
* Update Dispatcher docs for Debian and for non /opt/librenms installation ([#14663](https://github.com/librenms/librenms/pull/14663)) - [koocotte](https://github.com/koocotte)
* Fix Location mapping docs ([#14644](https://github.com/librenms/librenms/pull/14644)) - [electrocret](https://github.com/electrocret)
* Update config name for the default device display ([#14641](https://github.com/librenms/librenms/pull/14641)) - [cfitzw](https://github.com/cfitzw)
* Update Dispatcher-Service.md ([#14632](https://github.com/librenms/librenms/pull/14632)) - [systeembeheerder](https://github.com/systeembeheerder)
* Updating postgres application documentation ([#14627](https://github.com/librenms/librenms/pull/14627)) - [bnerickson](https://github.com/bnerickson)
* Documentation for Observium Migration ([#14601](https://github.com/librenms/librenms/pull/14601)) - [LoveSkylark](https://github.com/LoveSkylark)
* Add snmp_flags and tips on string oids to the docs ([#14579](https://github.com/librenms/librenms/pull/14579)) - [murrant](https://github.com/murrant)
* Clarify Dispatcher-Service.md for systemd ([#14575](https://github.com/librenms/librenms/pull/14575)) - [fuzzbawl](https://github.com/fuzzbawl)
* Update license section of readme ([#14571](https://github.com/librenms/librenms/pull/14571)) - [otkd](https://github.com/otkd)
* Remove outdated rules videos ([#14505](https://github.com/librenms/librenms/pull/14505)) - [kiwibrew](https://github.com/kiwibrew)
* Docker quick install ([#14475](https://github.com/librenms/librenms/pull/14475)) - [murrant](https://github.com/murrant)

#### Tests
* Mock astext ([#14581](https://github.com/librenms/librenms/pull/14581)) - [murrant](https://github.com/murrant)
* Github tests log improvement ([#14559](https://github.com/librenms/librenms/pull/14559)) - [murrant](https://github.com/murrant)
* Improve trap testing ([#14546](https://github.com/librenms/librenms/pull/14546)) - [murrant](https://github.com/murrant)
* Remove unused phpstan tests ([#14503](https://github.com/librenms/librenms/pull/14503)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Misc
* New schema dump ([#14630](https://github.com/librenms/librenms/pull/14630)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add check_hetzner_storagebox to includes/services ([#14463](https://github.com/librenms/librenms/pull/14463)) - [SirMaple](https://github.com/SirMaple)
* Add instance id to error reports ([#14444](https://github.com/librenms/librenms/pull/14444)) - [murrant](https://github.com/murrant)

#### Internal Features
* DeviceCache::get() allow hostname ([#14649](https://github.com/librenms/librenms/pull/14649)) - [murrant](https://github.com/murrant)
* SnmpQuery numeric accept a boolean ([#14565](https://github.com/librenms/librenms/pull/14565)) - [murrant](https://github.com/murrant)
* Support regex in os field replace ([#14563](https://github.com/librenms/librenms/pull/14563)) - [murrant](https://github.com/murrant)
* More Replacement Capabilities for sensor index computation ([#14522](https://github.com/librenms/librenms/pull/14522)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Dependencies
* Bump loader-utils from 1.4.0 to 1.4.2 ([#14628](https://github.com/librenms/librenms/pull/14628)) - [dependabot](https://github.com/apps/dependabot)


## 22.10.0
*(2022-10-17)*

A big thank you to the following 22 contributors this last month:

  - [murrant](https://github.com/murrant) (29)
  - [Jellyfrog](https://github.com/Jellyfrog) (8)
  - [KayckMatias](https://github.com/KayckMatias) (4)
  - [Martin22](https://github.com/Martin22) (3)
  - [PipoCanaja](https://github.com/PipoCanaja) (3)
  - [fbouynot](https://github.com/fbouynot) (3)
  - [electrocret](https://github.com/electrocret) (2)
  - [ottorei](https://github.com/ottorei) (1)
  - [SirMaple](https://github.com/SirMaple) (1)
  - [carbinefreak](https://github.com/carbinefreak) (1)
  - [opalivan](https://github.com/opalivan) (1)
  - [luc-ass](https://github.com/luc-ass) (1)
  - [jgelinas](https://github.com/jgelinas) (1)
  - [mpikzink](https://github.com/mpikzink) (1)
  - [rhinoau](https://github.com/rhinoau) (1)
  - [tim427](https://github.com/tim427) (1)
  - [daniel-franca](https://github.com/daniel-franca) (1)
  - [sashashura](https://github.com/sashashura) (1)
  - [tuomari](https://github.com/tuomari) (1)
  - [kimhaak](https://github.com/kimhaak) (1)
  - [bnerickson](https://github.com/bnerickson) (1)
  - [loopodoopo](https://github.com/loopodoopo) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (20)
  - [murrant](https://github.com/murrant) (16)
  - [PipoCanaja](https://github.com/PipoCanaja) (11)
  - [ottorei](https://github.com/ottorei) (3)
  - [VVelox](https://github.com/VVelox) (2)
  - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ) (1)

#### Security
* Block disabled user session auth ([#14473](https://github.com/librenms/librenms/pull/14473)) - [murrant](https://github.com/murrant)
* Fix group delete xss ([#14472](https://github.com/librenms/librenms/pull/14472)) - [murrant](https://github.com/murrant)
* Fix alert rules XSS ([#14471](https://github.com/librenms/librenms/pull/14471)) - [murrant](https://github.com/murrant)
* Fix xss in browser push transport ([#14470](https://github.com/librenms/librenms/pull/14470)) - [murrant](https://github.com/murrant)
* Fix users xss ([#14469](https://github.com/librenms/librenms/pull/14469)) - [murrant](https://github.com/murrant)
* Fix user mass assignment vulnerability ([#14468](https://github.com/librenms/librenms/pull/14468)) - [murrant](https://github.com/murrant)
* Fix billing xss ([#14465](https://github.com/librenms/librenms/pull/14465)) - [murrant](https://github.com/murrant)
* Fix memcached unserialize vulnerability ([#14459](https://github.com/librenms/librenms/pull/14459)) - [murrant](https://github.com/murrant)
* Fix possible RSS XSS ([#14457](https://github.com/librenms/librenms/pull/14457)) - [murrant](https://github.com/murrant)
* GitHub Workflows security hardening ([#14388](https://github.com/librenms/librenms/pull/14388)) - [sashashura](https://github.com/sashashura)

#### Device
* Correct aviatModemCurModulationRx oid ([#14446](https://github.com/librenms/librenms/pull/14446)) - [Jellyfrog](https://github.com/Jellyfrog)
* MNI Microwave OS Add ([#14427](https://github.com/librenms/librenms/pull/14427)) - [carbinefreak](https://github.com/carbinefreak)
* Add OS tait-tnadmin for TN9300 ([#14413](https://github.com/librenms/librenms/pull/14413)) - [opalivan](https://github.com/opalivan)
* Skip empty drac state sensors ([#14409](https://github.com/librenms/librenms/pull/14409)) - [jgelinas](https://github.com/jgelinas)
* Added support for additional Moxa EDS-G models ([#14405](https://github.com/librenms/librenms/pull/14405)) - [rhinoau](https://github.com/rhinoau)
* Issue samsung printer m4080 fx ([#14391](https://github.com/librenms/librenms/pull/14391)) - [daniel-franca](https://github.com/daniel-franca)
* Enexus system output current fix ([#14324](https://github.com/librenms/librenms/pull/14324)) - [loopodoopo](https://github.com/loopodoopo)
* Routeros - Fix displaying distance in charts ([#14300](https://github.com/librenms/librenms/pull/14300)) - [Martin22](https://github.com/Martin22)
* Added support for Ubiquiti UFiber OLT ([#14256](https://github.com/librenms/librenms/pull/14256)) - [Martin22](https://github.com/Martin22)

#### Webui
* Fix missing device_id from device alert logs ([#14460](https://github.com/librenms/librenms/pull/14460)) - [ottorei](https://github.com/ottorei)
* Fix about date display ([#14442](https://github.com/librenms/librenms/pull/14442)) - [murrant](https://github.com/murrant)
* Fix pseudowires pages ([#14441](https://github.com/librenms/librenms/pull/14441)) - [murrant](https://github.com/murrant)
* Enhanced Service Overview on Device Overview Page ([#14410](https://github.com/librenms/librenms/pull/14410)) - [luc-ass](https://github.com/luc-ass)
* Disable plugins that have errors ([#14383](https://github.com/librenms/librenms/pull/14383)) - [murrant](https://github.com/murrant)
* Fix Inventory table for Dark theme ([#14377](https://github.com/librenms/librenms/pull/14377)) - [electrocret](https://github.com/electrocret)
* Add initselect2 to locations ports ([#14375](https://github.com/librenms/librenms/pull/14375)) - [KayckMatias](https://github.com/KayckMatias)
* Update devices filter in alert-logs to init_select2 ([#14361](https://github.com/librenms/librenms/pull/14361)) - [KayckMatias](https://github.com/KayckMatias)

#### Alerting
* Alert map location fix ([#14380](https://github.com/librenms/librenms/pull/14380)) - [KayckMatias](https://github.com/KayckMatias)
* Populate the alert rule field with the builder json ([#14374](https://github.com/librenms/librenms/pull/14374)) - [murrant](https://github.com/murrant)

#### Snmp Traps
* Improve LinkUp and LinkDown trap handling incomplete traps ([#14385](https://github.com/librenms/librenms/pull/14385)) - [tuomari](https://github.com/tuomari)

#### Applications
* Add pwrstatd application ([#14365](https://github.com/librenms/librenms/pull/14365)) - [bnerickson](https://github.com/bnerickson)

#### Api
* Fix port search with slashes ([#14403](https://github.com/librenms/librenms/pull/14403)) - [murrant](https://github.com/murrant)

#### Discovery
* Cisco NAC fix ([#14440](https://github.com/librenms/librenms/pull/14440)) - [murrant](https://github.com/murrant)

#### Polling
* Fix bgp-peers bgpPeerIface bug and update test data ([#14420](https://github.com/librenms/librenms/pull/14420)) - [murrant](https://github.com/murrant)

#### Oxidized
* Fix oxidized web requests unclosed connections or responding fast enough ([#14370](https://github.com/librenms/librenms/pull/14370)) - [fbouynot](https://github.com/fbouynot)

#### Authentication
* Rework socialite integration ([#14367](https://github.com/librenms/librenms/pull/14367)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Bug
* Snmpwalk functions: dont include invalid data ([#14438](https://github.com/librenms/librenms/pull/14438)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix VRP duplicate BGP Peers ([#14431](https://github.com/librenms/librenms/pull/14431)) - [PipoCanaja](https://github.com/PipoCanaja)
* Version and git helper improvements ([#14412](https://github.com/librenms/librenms/pull/14412)) - [murrant](https://github.com/murrant)
* Cache version data ([#14404](https://github.com/librenms/librenms/pull/14404)) - [murrant](https://github.com/murrant)
* Fix rpi codec state sensor ([#14400](https://github.com/librenms/librenms/pull/14400)) - [murrant](https://github.com/murrant)
* Fix libvirt count() uncountable error ([#14398](https://github.com/librenms/librenms/pull/14398)) - [murrant](https://github.com/murrant)
* Fix Firebrick local ASN ([#14397](https://github.com/librenms/librenms/pull/14397)) - [murrant](https://github.com/murrant)
* Fix ups nut PHP 8 issue ([#14392](https://github.com/librenms/librenms/pull/14392)) - [murrant](https://github.com/murrant)

#### Refactor
* Fix a few Db* to Eloquent requests ([#14278](https://github.com/librenms/librenms/pull/14278)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Cleanup
* Remove tabs from yaml ([#14437](https://github.com/librenms/librenms/pull/14437)) - [Jellyfrog](https://github.com/Jellyfrog)
* Miscellaneous fixes, mostly undefined variables ([#14432](https://github.com/librenms/librenms/pull/14432)) - [Jellyfrog](https://github.com/Jellyfrog)
* Trim whitespaces from device data ([#14429](https://github.com/librenms/librenms/pull/14429)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix bgp_peer array_merge error ([#14416](https://github.com/librenms/librenms/pull/14416)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Documentation
* Updating details of my LibreNMS install ([#14458](https://github.com/librenms/librenms/pull/14458)) - [SirMaple](https://github.com/SirMaple)
* Fix php7.2 occurence in Performance.md documentation ([#14394](https://github.com/librenms/librenms/pull/14394)) - [fbouynot](https://github.com/fbouynot)
* Fix documentation for php8.1 and bad package name ([#14393](https://github.com/librenms/librenms/pull/14393)) - [fbouynot](https://github.com/fbouynot)

#### Translation
* Fix Typo in Settings ([#14443](https://github.com/librenms/librenms/pull/14443)) - [electrocret](https://github.com/electrocret)

#### Tests
* Speed up tests ([#14421](https://github.com/librenms/librenms/pull/14421)) - [murrant](https://github.com/murrant)
* Fix cisco-pw test capture ([#14415](https://github.com/librenms/librenms/pull/14415)) - [murrant](https://github.com/murrant)
* Test supported PHP versions only ([#14389](https://github.com/librenms/librenms/pull/14389)) - [murrant](https://github.com/murrant)
* Run CI on ubuntu 22.04 ([#14379](https://github.com/librenms/librenms/pull/14379)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Misc
* Update ports_purge docs and definitions ([#14402](https://github.com/librenms/librenms/pull/14402)) - [KayckMatias](https://github.com/KayckMatias)
* Added Ubuntu and Raspbian to ifAlias script ([#14399](https://github.com/librenms/librenms/pull/14399)) - [tim427](https://github.com/tim427)

#### Dependencies
* Increase minimum version to PHP 8.1 ([#14378](https://github.com/librenms/librenms/pull/14378)) - [murrant](https://github.com/murrant)


## 22.9.0
*(2022-09-21)*

A big thank you to the following 19 contributors this last month:

  - [murrant](https://github.com/murrant) (53)
  - [Jellyfrog](https://github.com/Jellyfrog) (17)
  - [PipoCanaja](https://github.com/PipoCanaja) (7)
  - [fbouynot](https://github.com/fbouynot) (6)
  - [bp0](https://github.com/bp0) (3)
  - [Npeca75](https://github.com/Npeca75) (2)
  - [sembeek](https://github.com/sembeek) (1)
  - [huntr-helper](https://github.com/huntr-helper) (1)
  - [KayckMatias](https://github.com/KayckMatias) (1)
  - [LoveSkylark](https://github.com/LoveSkylark) (1)
  - [quentinsch](https://github.com/quentinsch) (1)
  - [loopodoopo](https://github.com/loopodoopo) (1)
  - [electrocret](https://github.com/electrocret) (1)
  - [Martin22](https://github.com/Martin22) (1)
  - [luc-ass](https://github.com/luc-ass) (1)
  - [ktims](https://github.com/ktims) (1)
  - [VirTechSystems](https://github.com/VirTechSystems) (1)
  - [tim427](https://github.com/tim427) (1)
  - [mwobst](https://github.com/mwobst) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (43)
  - [murrant](https://github.com/murrant) (39)
  - [PipoCanaja](https://github.com/PipoCanaja) (9)
  - [ottorei](https://github.com/ottorei) (4)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)

#### Feature
* Improved Modern Modules ([#14315](https://github.com/librenms/librenms/pull/14315)) - [murrant](https://github.com/murrant)
* Add @signedGraphTag() and @signedGraphUrl() blade directives ([#14269](https://github.com/librenms/librenms/pull/14269)) - [murrant](https://github.com/murrant)
* Device settings: attempt to open related tab ([#14250](https://github.com/librenms/librenms/pull/14250)) - [murrant](https://github.com/murrant)
* Error reporting ([#14190](https://github.com/librenms/librenms/pull/14190)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Security
* Fix scheduled maintenance xss ([#14360](https://github.com/librenms/librenms/pull/14360)) - [murrant](https://github.com/murrant)
* Add huntr.dev to SECURITY.md ([#14359](https://github.com/librenms/librenms/pull/14359)) - [huntr-helper](https://github.com/huntr-helper)

#### Device
* Added status checks for (BWCC) battery status and condition for onboa… ([#14349](https://github.com/librenms/librenms/pull/14349)) - [quentinsch](https://github.com/quentinsch)
* UHP VSAT modems ([#14317](https://github.com/librenms/librenms/pull/14317)) - [loopodoopo](https://github.com/loopodoopo)
* Ray2 - Oid repair at ber ([#14302](https://github.com/librenms/librenms/pull/14302)) - [Martin22](https://github.com/Martin22)
* Fix ciena-sds inventory bugs ([#14252](https://github.com/librenms/librenms/pull/14252)) - [murrant](https://github.com/murrant)
* F5 partition ram ([#14226](https://github.com/librenms/librenms/pull/14226)) - [fbouynot](https://github.com/fbouynot)
* Add Cisco Flash storage support ([#14219](https://github.com/librenms/librenms/pull/14219)) - [fbouynot](https://github.com/fbouynot)

#### Webui
* Load device selection dynamically on Ports page ([#14353](https://github.com/librenms/librenms/pull/14353)) - [KayckMatias](https://github.com/KayckMatias)
* Port pages fixes and cleanups ([#14310](https://github.com/librenms/librenms/pull/14310)) - [murrant](https://github.com/murrant)
* Remove Caffeine ([#14277](https://github.com/librenms/librenms/pull/14277)) - [murrant](https://github.com/murrant)
* Updated brother.svg to make it render in Safari ([#14271](https://github.com/librenms/librenms/pull/14271)) - [luc-ass](https://github.com/luc-ass)
* Fix for Cisco group device health tab ([#14265](https://github.com/librenms/librenms/pull/14265)) - [fbouynot](https://github.com/fbouynot)
* Fix dashboard widgets becoming unlocked (especially when using a touchscreen) ([#14222](https://github.com/librenms/librenms/pull/14222)) - [tim427](https://github.com/tim427)
* Migrate xDSL code to module, and add support for VDSL2 MIB ([#14207](https://github.com/librenms/librenms/pull/14207)) - [PipoCanaja](https://github.com/PipoCanaja)
* Improved sorting options for Availability Map ([#14073](https://github.com/librenms/librenms/pull/14073)) - [mwobst](https://github.com/mwobst)

#### Alerting
* Discord ability to attach graph images ([#14276](https://github.com/librenms/librenms/pull/14276)) - [murrant](https://github.com/murrant)
* Email Transport: embed graphs by default ([#14270](https://github.com/librenms/librenms/pull/14270)) - [murrant](https://github.com/murrant)

#### Graphs
* Return GraphImage to include more metadata ([#14307](https://github.com/librenms/librenms/pull/14307)) - [murrant](https://github.com/murrant)

#### Api
* Allow delete location by id ([#14334](https://github.com/librenms/librenms/pull/14334)) - [bp0](https://github.com/bp0)
* API update_device, make location field work (as does location_id) ([#14325](https://github.com/librenms/librenms/pull/14325)) - [bp0](https://github.com/bp0)

#### Polling
* Fix ping.php skipped results ([#14368](https://github.com/librenms/librenms/pull/14368)) - [sembeek](https://github.com/sembeek)

#### Oxidized
* Oxidized "allow purpose and notes" ([#14352](https://github.com/librenms/librenms/pull/14352)) - [LoveSkylark](https://github.com/LoveSkylark)

#### Bug
* Fix cipsec-tunnels ftd bad data causes error ([#14366](https://github.com/librenms/librenms/pull/14366)) - [murrant](https://github.com/murrant)
* Handle null in unix-agent ([#14358](https://github.com/librenms/librenms/pull/14358)) - [murrant](https://github.com/murrant)
* Fix the case of 'Ungrouped' in smokeping integration ([#14351](https://github.com/librenms/librenms/pull/14351)) - [fbouynot](https://github.com/fbouynot)
* Fix for smokeping integration with ping-only devices ([#14341](https://github.com/librenms/librenms/pull/14341)) - [fbouynot](https://github.com/fbouynot)
* Match displayname source for graylog ([#14339](https://github.com/librenms/librenms/pull/14339)) - [fbouynot](https://github.com/fbouynot)
* Check dot1dBasePortIfIndex exists before using it ([#14337](https://github.com/librenms/librenms/pull/14337)) - [Jellyfrog](https://github.com/Jellyfrog)
* Prevent errors when hrStorageTable doesn't exist ([#14327](https://github.com/librenms/librenms/pull/14327)) - [Jellyfrog](https://github.com/Jellyfrog)
* Revert "ZTE ZXA10 Update (Added dBm graphs)" ([#14320](https://github.com/librenms/librenms/pull/14320)) - [murrant](https://github.com/murrant)
* Billing module fix/cleanup ([#14309](https://github.com/librenms/librenms/pull/14309)) - [electrocret](https://github.com/electrocret)
* SnmpResponse filterBadLines fix ([#14306](https://github.com/librenms/librenms/pull/14306)) - [murrant](https://github.com/murrant)
* Fix ARP Table on Device overview ([#14304](https://github.com/librenms/librenms/pull/14304)) - [Npeca75](https://github.com/Npeca75)
* Disable error reporting when dependencies are outdated ([#14291](https://github.com/librenms/librenms/pull/14291)) - [murrant](https://github.com/murrant)
* Don't send zeros to Graphite for missing metrics ([#14262](https://github.com/librenms/librenms/pull/14262)) - [ktims](https://github.com/ktims)
* Fix removing all port groups ([#14253](https://github.com/librenms/librenms/pull/14253)) - [murrant](https://github.com/murrant)
* Move cronjob time for daily.sh ([#14245](https://github.com/librenms/librenms/pull/14245)) - [Jellyfrog](https://github.com/Jellyfrog)
* Firebrick bgp polling was broken ([#14237](https://github.com/librenms/librenms/pull/14237)) - [murrant](https://github.com/murrant)
* Quick fix for cipsec-tunnels Cisco implementation ([#14232](https://github.com/librenms/librenms/pull/14232)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix redeclare of function breaking tests in OS eltex-mes23xx ([#14227](https://github.com/librenms/librenms/pull/14227)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Refactor
* Cleanup and optimize the availability widget ([#14329](https://github.com/librenms/librenms/pull/14329)) - [murrant](https://github.com/murrant)

#### Cleanup
* Defer loading cli option defaults ([#14363](https://github.com/librenms/librenms/pull/14363)) - [Jellyfrog](https://github.com/Jellyfrog)
* Remove call to Config from artisan ([#14362](https://github.com/librenms/librenms/pull/14362)) - [Jellyfrog](https://github.com/Jellyfrog)
* Mibs - Cleanup names ([#14323](https://github.com/librenms/librenms/pull/14323)) - [PipoCanaja](https://github.com/PipoCanaja)
* Enable more checks ([#14318](https://github.com/librenms/librenms/pull/14318)) - [Jellyfrog](https://github.com/Jellyfrog)
* Do not include _token in legacy vars ([#14313](https://github.com/librenms/librenms/pull/14313)) - [murrant](https://github.com/murrant)
* Don't include null os when loading defs ([#14312](https://github.com/librenms/librenms/pull/14312)) - [murrant](https://github.com/murrant)
* 2fa not all routes have names ([#14311](https://github.com/librenms/librenms/pull/14311)) - [murrant](https://github.com/murrant)
* Cache astext for 1 day ([#14303](https://github.com/librenms/librenms/pull/14303)) - [murrant](https://github.com/murrant)
* Linux distro images require feature field ([#14301](https://github.com/librenms/librenms/pull/14301)) - [murrant](https://github.com/murrant)
* Silence return type mismatch ([#14298](https://github.com/librenms/librenms/pull/14298)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix more percent calculations ([#14294](https://github.com/librenms/librenms/pull/14294)) - [murrant](https://github.com/murrant)
* Fix rounding null in mempools module ([#14290](https://github.com/librenms/librenms/pull/14290)) - [murrant](https://github.com/murrant)
* Check if polling module exists ([#14289](https://github.com/librenms/librenms/pull/14289)) - [murrant](https://github.com/murrant)
* Fix undefined variable ([#14287](https://github.com/librenms/librenms/pull/14287)) - [murrant](https://github.com/murrant)
* Handle connection timed out errors in unix agent ([#14286](https://github.com/librenms/librenms/pull/14286)) - [murrant](https://github.com/murrant)
* Remove unused function ([#14283](https://github.com/librenms/librenms/pull/14283)) - [murrant](https://github.com/murrant)
* Remove reference to unused variable ([#14280](https://github.com/librenms/librenms/pull/14280)) - [murrant](https://github.com/murrant)
* Cleanup some RunAlerts issues ([#14274](https://github.com/librenms/librenms/pull/14274)) - [murrant](https://github.com/murrant)
* Fix a bunch of "Since fakerphp/faker 1.14: Accessing property.." ([#14267](https://github.com/librenms/librenms/pull/14267)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix more error exceptions ([#14266](https://github.com/librenms/librenms/pull/14266)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix hrStorageType being undefined ([#14260](https://github.com/librenms/librenms/pull/14260)) - [Jellyfrog](https://github.com/Jellyfrog)
* Misc errors cleanup ([#14257](https://github.com/librenms/librenms/pull/14257)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix ipv6 hexdec error ([#14254](https://github.com/librenms/librenms/pull/14254)) - [murrant](https://github.com/murrant)
* Use empty string instead of null for routes ([#14247](https://github.com/librenms/librenms/pull/14247)) - [Jellyfrog](https://github.com/Jellyfrog)
* Sort device types alphabetically ([#14244](https://github.com/librenms/librenms/pull/14244)) - [VirTechSystems](https://github.com/VirTechSystems)
* Misc webui code cleanups ([#14242](https://github.com/librenms/librenms/pull/14242)) - [murrant](https://github.com/murrant)
* Fix some ErrorExceptions ([#14241](https://github.com/librenms/librenms/pull/14241)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix os_group unset errors ([#14238](https://github.com/librenms/librenms/pull/14238)) - [murrant](https://github.com/murrant)
* Juniper bgp-peers cleanup ([#14236](https://github.com/librenms/librenms/pull/14236)) - [murrant](https://github.com/murrant)
* Percentage calculation helper ([#14235](https://github.com/librenms/librenms/pull/14235)) - [murrant](https://github.com/murrant)
* Memcached app undefined vars ([#14225](https://github.com/librenms/librenms/pull/14225)) - [Npeca75](https://github.com/Npeca75)
* Fix Undefined variable/key warnings ([#14134](https://github.com/librenms/librenms/pull/14134)) - [murrant](https://github.com/murrant)

#### Documentation
* Move list_locations from devices to locations ([#14328](https://github.com/librenms/librenms/pull/14328)) - [bp0](https://github.com/bp0)
* Cisco-sla module was renamed to slas ([#14288](https://github.com/librenms/librenms/pull/14288)) - [murrant](https://github.com/murrant)
* Update docs around APP_URL ([#14282](https://github.com/librenms/librenms/pull/14282)) - [murrant](https://github.com/murrant)

#### Tests
* Do not allow sysDescr to be fetched in os module yaml ([#14331](https://github.com/librenms/librenms/pull/14331)) - [murrant](https://github.com/murrant)
* Remove PHP version constraint ([#14314](https://github.com/librenms/librenms/pull/14314)) - [Jellyfrog](https://github.com/Jellyfrog)
* Lnms dev:simulate check simulated device exists ([#14243](https://github.com/librenms/librenms/pull/14243)) - [murrant](https://github.com/murrant)
* Save-test-data to refresh all variants of an OS ([#14231](https://github.com/librenms/librenms/pull/14231)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Misc
* Defer loading cli option values ([#14354](https://github.com/librenms/librenms/pull/14354)) - [murrant](https://github.com/murrant)
* Ignore CommandNotFoundException from reporting ([#14342](https://github.com/librenms/librenms/pull/14342)) - [Jellyfrog](https://github.com/Jellyfrog)
* Allow dumping of errors and warnings ([#14275](https://github.com/librenms/librenms/pull/14275)) - [murrant](https://github.com/murrant)
* Allow SnmpQuery to optionally abort walks if one fails ([#14255](https://github.com/librenms/librenms/pull/14255)) - [murrant](https://github.com/murrant)

#### Dependencies
* Update dependencies ([#14319](https://github.com/librenms/librenms/pull/14319)) - [murrant](https://github.com/murrant)
* Bump php-cs-fixer to 3.4.0 ([#14224](https://github.com/librenms/librenms/pull/14224)) - [murrant](https://github.com/murrant)


## 22.8.0
*(2022-08-19)*

A big thank you to the following 26 contributors this last month:

  - [murrant](https://github.com/murrant) (18)
  - [Npeca75](https://github.com/Npeca75) (12)
  - [Jellyfrog](https://github.com/Jellyfrog) (9)
  - [gs-kamnas](https://github.com/gs-kamnas) (5)
  - [earendilfr](https://github.com/earendilfr) (3)
  - [fbouynot](https://github.com/fbouynot) (3)
  - [VVelox](https://github.com/VVelox) (3)
  - [rhinoau](https://github.com/rhinoau) (2)
  - [Schouwenburg](https://github.com/Schouwenburg) (2)
  - [electrocret](https://github.com/electrocret) (2)
  - [aztec102](https://github.com/aztec102) (2)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [loopodoopo](https://github.com/loopodoopo) (1)
  - [washcroft](https://github.com/washcroft) (1)
  - [Laplacence](https://github.com/Laplacence) (1)
  - [opalivan](https://github.com/opalivan) (1)
  - [Jarod2801](https://github.com/Jarod2801) (1)
  - [Martin22](https://github.com/Martin22) (1)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (1)
  - [ciscoqid](https://github.com/ciscoqid) (1)
  - [Fehler12](https://github.com/Fehler12) (1)
  - [PedroChaps](https://github.com/PedroChaps) (1)
  - [ajsiersema](https://github.com/ajsiersema) (1)
  - [quentinsch](https://github.com/quentinsch) (1)
  - [Mar974](https://github.com/Mar974) (1)
  - [mwobst](https://github.com/mwobst) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (46)
  - [Jellyfrog](https://github.com/Jellyfrog) (26)
  - [PipoCanaja](https://github.com/PipoCanaja) (2)
  - [VVelox](https://github.com/VVelox) (1)
  - [Cormoran96](https://github.com/Cormoran96) (1)
  - [bennet-esyoil](https://github.com/bennet-esyoil) (1)
  - [ottorei](https://github.com/ottorei) (1)

#### Feature
* Prepare for PHP 8.1 ([#14156](https://github.com/librenms/librenms/pull/14156)) - [Jellyfrog](https://github.com/Jellyfrog)
* New Poller validations ([#14148](https://github.com/librenms/librenms/pull/14148)) - [murrant](https://github.com/murrant)
* New lnms command to enable and disable plugins ([#14147](https://github.com/librenms/librenms/pull/14147)) - [murrant](https://github.com/murrant)
* Support for SSL/TLS protected connections to MySQL databases ([#14142](https://github.com/librenms/librenms/pull/14142)) - [gs-kamnas](https://github.com/gs-kamnas)
* Collect OS distro and LibreNMS version ([#14138](https://github.com/librenms/librenms/pull/14138)) - [murrant](https://github.com/murrant)
* Lnms device:poll better feedback ([#14130](https://github.com/librenms/librenms/pull/14130)) - [murrant](https://github.com/murrant)
* Per-App data storage ([#14087](https://github.com/librenms/librenms/pull/14087)) - [VVelox](https://github.com/VVelox)

#### Device
* Fix luminato port poll ([#14217](https://github.com/librenms/librenms/pull/14217)) - [murrant](https://github.com/murrant)
* Teltonika RUT fix ([#14202](https://github.com/librenms/librenms/pull/14202)) - [loopodoopo](https://github.com/loopodoopo)
* Initial support for Moxa AWK Industrial APs ([#14197](https://github.com/librenms/librenms/pull/14197)) - [rhinoau](https://github.com/rhinoau)
* Mikrotik wifi station mode sensors ([#14193](https://github.com/librenms/librenms/pull/14193)) - [Npeca75](https://github.com/Npeca75)
* Update mellanox os image ([#14184](https://github.com/librenms/librenms/pull/14184)) - [Laplacence](https://github.com/Laplacence)
* Change state_name for Racoms modulation ([#14174](https://github.com/librenms/librenms/pull/14174)) - [Martin22](https://github.com/Martin22)
* Cisco SB/CBS environment sensors ([#14154](https://github.com/librenms/librenms/pull/14154)) - [Fehler12](https://github.com/Fehler12)
* Fix Rocky Linux and AlmaLinux icons and logos ([#14150](https://github.com/librenms/librenms/pull/14150)) - [murrant](https://github.com/murrant)
* Add additional sysObjectIDs for variants of the Arista 7130 ([#14144](https://github.com/librenms/librenms/pull/14144)) - [gs-kamnas](https://github.com/gs-kamnas)
* Eltex MES 2324p Add ([#14135](https://github.com/librenms/librenms/pull/14135)) - [aztec102](https://github.com/aztec102)
* Solid Optics EDFAMUX support ([#14129](https://github.com/librenms/librenms/pull/14129)) - [murrant](https://github.com/murrant)
* Merge netmanplus and riello and improve device support ([#14125](https://github.com/librenms/librenms/pull/14125)) - [murrant](https://github.com/murrant)
* Bats support ([#14108](https://github.com/librenms/librenms/pull/14108)) - [Schouwenburg](https://github.com/Schouwenburg)
* Solved aos6 problem where Librenms wasn't identifying all vlans ([#14107](https://github.com/librenms/librenms/pull/14107)) - [PedroChaps](https://github.com/PedroChaps)
* Fix Cisco polling BGP peers in non-default VRF ([#14105](https://github.com/librenms/librenms/pull/14105)) - [ajsiersema](https://github.com/ajsiersema)
* Added support for CheckPoint 1100, 1450 & 1490 models. ([#14074](https://github.com/librenms/librenms/pull/14074)) - [quentinsch](https://github.com/quentinsch)
* Ns-bsd updated for SNS LTSB 3.7.19 ([#14060](https://github.com/librenms/librenms/pull/14060)) - [Mar974](https://github.com/Mar974)
* ZTE ZXA10 Update (Added dBm graphs) ([#14049](https://github.com/librenms/librenms/pull/14049)) - [aztec102](https://github.com/aztec102)

#### Webui
* Fix ports display ([#14183](https://github.com/librenms/librenms/pull/14183)) - [murrant](https://github.com/murrant)
* Ports by device group ([#14175](https://github.com/librenms/librenms/pull/14175)) - [electrocret](https://github.com/electrocret)
* Empty Outages table ([#14167](https://github.com/librenms/librenms/pull/14167)) - [Npeca75](https://github.com/Npeca75)
* View Ports in Portgroups ([#14141](https://github.com/librenms/librenms/pull/14141)) - [electrocret](https://github.com/electrocret)
* Fix eventlog filtering ([#14136](https://github.com/librenms/librenms/pull/14136)) - [murrant](https://github.com/murrant)
* [gui] enable permanent vertical scroll ([#14102](https://github.com/librenms/librenms/pull/14102)) - [Npeca75](https://github.com/Npeca75)

#### Alerting
* Correct logic for recurring alert rules that span UTC days ([#14145](https://github.com/librenms/librenms/pull/14145)) - [gs-kamnas](https://github.com/gs-kamnas)
* Improvements to PagerDuty alert formatting ([#14143](https://github.com/librenms/librenms/pull/14143)) - [gs-kamnas](https://github.com/gs-kamnas)
* Add rich (=html) support for messages via Matrix ([#14054](https://github.com/librenms/librenms/pull/14054)) - [mwobst](https://github.com/mwobst)

#### Graphs
* Fix unauth application graphs ([#14216](https://github.com/librenms/librenms/pull/14216)) - [murrant](https://github.com/murrant)
* Allow specifying the background colour in graph images ([#14192](https://github.com/librenms/librenms/pull/14192)) - [washcroft](https://github.com/washcroft)

#### Applications
* Add possibility to monitor redis application through the unix-agent ([#14182](https://github.com/librenms/librenms/pull/14182)) - [earendilfr](https://github.com/earendilfr)
* Fix error between application module and unix-agent ([#14177](https://github.com/librenms/librenms/pull/14177)) - [earendilfr](https://github.com/earendilfr)
* Add possibility to monitor the php-fpm service with the unix agent ([#14173](https://github.com/librenms/librenms/pull/14173)) - [earendilfr](https://github.com/earendilfr)
* [apps] Docker only show current containers ([#14152](https://github.com/librenms/librenms/pull/14152)) - [Npeca75](https://github.com/Npeca75)
* Add support for Sagan ([#14070](https://github.com/librenms/librenms/pull/14070)) - [VVelox](https://github.com/VVelox)
* Add Opensearch\Elasticsearch monitoring ([#14053](https://github.com/librenms/librenms/pull/14053)) - [VVelox](https://github.com/VVelox)

#### Api
* Standardize device and device group maintenance API ([#14153](https://github.com/librenms/librenms/pull/14153)) - [rhinoau](https://github.com/rhinoau)
* Fix maintenance APIs not associating device or group ([#14127](https://github.com/librenms/librenms/pull/14127)) - [murrant](https://github.com/murrant)

#### Discovery
* Fix legacy os extends ([#14220](https://github.com/librenms/librenms/pull/14220)) - [murrant](https://github.com/murrant)
* Sensors, convert hex to strings ([#14121](https://github.com/librenms/librenms/pull/14121)) - [murrant](https://github.com/murrant)

#### Polling
* Fix for number in sensor string ([#14185](https://github.com/librenms/librenms/pull/14185)) - [Schouwenburg](https://github.com/Schouwenburg)

#### Authentication
* AD Auth PHP 8.1 fixes ([#14215](https://github.com/librenms/librenms/pull/14215)) - [murrant](https://github.com/murrant)

#### Bug
* Autodiscovery fix ([#14213](https://github.com/librenms/librenms/pull/14213)) - [Npeca75](https://github.com/Npeca75)
* Fix alert log clearing SQL query ([#14200](https://github.com/librenms/librenms/pull/14200)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix error when ldap_search returns false ([#14199](https://github.com/librenms/librenms/pull/14199)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix unnecessary Updates of DB when values are equal ([#14179](https://github.com/librenms/librenms/pull/14179)) - [opalivan](https://github.com/opalivan)
* Fix APP_KEY generation when using fpm ([#14168](https://github.com/librenms/librenms/pull/14168)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix validation error ([#14163](https://github.com/librenms/librenms/pull/14163)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* IPv6 Debug typo ([#14162](https://github.com/librenms/librenms/pull/14162)) - [Npeca75](https://github.com/Npeca75)
* Replace git show --no-patch option with --quiet ([#14160](https://github.com/librenms/librenms/pull/14160)) - [ciscoqid](https://github.com/ciscoqid)
* Fix 500 error in validation when UI containers and poller containers have differing node_ids ([#14146](https://github.com/librenms/librenms/pull/14146)) - [gs-kamnas](https://github.com/gs-kamnas)
* Use --no-patch to support old git clients ([#14137](https://github.com/librenms/librenms/pull/14137)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Refactor
* Use "database version" instead of "mysql version" ([#14158](https://github.com/librenms/librenms/pull/14158)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Documentation
* Update to mkdocs-material 8.3.9 ([#14189](https://github.com/librenms/librenms/pull/14189)) - [Jellyfrog](https://github.com/Jellyfrog)
* SNMP extend / fix mdadm documentation ([#14186](https://github.com/librenms/librenms/pull/14186)) - [Npeca75](https://github.com/Npeca75)
* Update index.md ([#14178](https://github.com/librenms/librenms/pull/14178)) - [Jarod2801](https://github.com/Jarod2801)
* Add instructions for PHP 8.1 and Ubuntu 22.04 ([#14166](https://github.com/librenms/librenms/pull/14166)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update php installation instruction for CentOS 8 to reflect the minim… ([#14159](https://github.com/librenms/librenms/pull/14159)) - [fbouynot](https://github.com/fbouynot)
* Fix typo in Smokeping SELinux documentation ([#14155](https://github.com/librenms/librenms/pull/14155)) - [fbouynot](https://github.com/fbouynot)
* Add device_default_display info ([#14151](https://github.com/librenms/librenms/pull/14151)) - [murrant](https://github.com/murrant)
* Change SELinux context for logs ([#14128](https://github.com/librenms/librenms/pull/14128)) - [fbouynot](https://github.com/fbouynot)

#### Translation
* Serbian translation, part3 ([#14205](https://github.com/librenms/librenms/pull/14205)) - [Npeca75](https://github.com/Npeca75)
* More Serbian translation ([#14181](https://github.com/librenms/librenms/pull/14181)) - [Npeca75](https://github.com/Npeca75)
* Enable translation on Eventlog & Component-status widgets ([#14180](https://github.com/librenms/librenms/pull/14180)) - [Npeca75](https://github.com/Npeca75)
* Initial support for Serbian language ([#14165](https://github.com/librenms/librenms/pull/14165)) - [Npeca75](https://github.com/Npeca75)
* Enable translation in 3 Alert widget ([#14164](https://github.com/librenms/librenms/pull/14164)) - [Npeca75](https://github.com/Npeca75)

#### Tests
* Allow save-test-data.php to run all modules explicitely ([#14212](https://github.com/librenms/librenms/pull/14212)) - [PipoCanaja](https://github.com/PipoCanaja)
* Increase OS detection time, sometimes it is not ready by 5s ([#14133](https://github.com/librenms/librenms/pull/14133)) - [murrant](https://github.com/murrant)


## 22.7.0
*(2022-07-20)*

A big thank you to the following 21 contributors this last month:

  - [murrant](https://github.com/murrant) (12)
  - [fbouynot](https://github.com/fbouynot) (4)
  - [mwobst](https://github.com/mwobst) (3)
  - [dependabot](https://github.com/apps/dependabot) (2)
  - [wrongecho](https://github.com/wrongecho) (2)
  - [ppasserini](https://github.com/ppasserini) (2)
  - [QuadPiece](https://github.com/QuadPiece) (2)
  - [Jellyfrog](https://github.com/Jellyfrog) (2)
  - [enferas](https://github.com/enferas) (1)
  - [00gh](https://github.com/00gh) (1)
  - [bennet-esyoil](https://github.com/bennet-esyoil) (1)
  - [VVelox](https://github.com/VVelox) (1)
  - [rhinoau](https://github.com/rhinoau) (1)
  - [prahal](https://github.com/prahal) (1)
  - [paulgear](https://github.com/paulgear) (1)
  - [duhow](https://github.com/duhow) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [Mar974](https://github.com/Mar974) (1)
  - [ottorei](https://github.com/ottorei) (1)
  - [mostdaysarebaddays](https://github.com/mostdaysarebaddays) (1)
  - [dagbdagb](https://github.com/dagbdagb) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (19)
  - [murrant](https://github.com/murrant) (14)
  - [ottorei](https://github.com/ottorei) (2)
  - [PipoCanaja](https://github.com/PipoCanaja) (2)
  - [mpikzink](https://github.com/mpikzink) (1)
  - [VVelox](https://github.com/VVelox) (1)

#### Security
* Security fixes XSS in oxidized-cfg-check.inc.php and print-customoid.php ([#14126](https://github.com/librenms/librenms/pull/14126)) - [enferas](https://github.com/enferas)

#### Device
* Missing "s" in NsBsd.php ([#14067](https://github.com/librenms/librenms/pull/14067)) - [Mar974](https://github.com/Mar974)
* Add full version of Mikrotik logo ([#14063](https://github.com/librenms/librenms/pull/14063)) - [QuadPiece](https://github.com/QuadPiece)
* Add apc battery operation time, including display in WebUI ([#14058](https://github.com/librenms/librenms/pull/14058)) - [mwobst](https://github.com/mwobst)
* Fix the parsing of the temperature unit value (CMCIII devices) ([#14056](https://github.com/librenms/librenms/pull/14056)) - [mwobst](https://github.com/mwobst)
* Update Mikrotik logo ([#14045](https://github.com/librenms/librenms/pull/14045)) - [QuadPiece](https://github.com/QuadPiece)
* Aviat WTM reduce snmp load ([#13918](https://github.com/librenms/librenms/pull/13918)) - [murrant](https://github.com/murrant)

#### Webui
* Fix device filtering false values ([#14103](https://github.com/librenms/librenms/pull/14103)) - [murrant](https://github.com/murrant)
* Minor visual changes to the apps-overview page ([#14090](https://github.com/librenms/librenms/pull/14090)) - [bennet-esyoil](https://github.com/bennet-esyoil)
* Search device by MAC via URL ([#14072](https://github.com/librenms/librenms/pull/14072)) - [duhow](https://github.com/duhow)
* Add BGP description to eventlog BGP Peers messages ([#14061](https://github.com/librenms/librenms/pull/14061)) - [mostdaysarebaddays](https://github.com/mostdaysarebaddays)

#### Alerting
* Example rules for diskspace on / ([#14082](https://github.com/librenms/librenms/pull/14082)) - [VVelox](https://github.com/VVelox)

#### Graphs
* Ping perf ([#14117](https://github.com/librenms/librenms/pull/14117)) - [00gh](https://github.com/00gh)

#### Applications
* Fix app docker ([#14080](https://github.com/librenms/librenms/pull/14080)) - [prahal](https://github.com/prahal)

#### Polling
* Increase traceroute timeout ([#14084](https://github.com/librenms/librenms/pull/14084)) - [murrant](https://github.com/murrant)

#### Authentication
* Add option STARTTLS for authentication via AD ([#14051](https://github.com/librenms/librenms/pull/14051)) - [dagbdagb](https://github.com/dagbdagb)

#### Bug
* Prevent duplicate plugin table entries ([#14120](https://github.com/librenms/librenms/pull/14120)) - [murrant](https://github.com/murrant)
* Fix auth_test.php debug ([#14118](https://github.com/librenms/librenms/pull/14118)) - [murrant](https://github.com/murrant)
* Fix config seeder will never run ([#14113](https://github.com/librenms/librenms/pull/14113)) - [murrant](https://github.com/murrant)
* Reset the opcache after install ([#14098](https://github.com/librenms/librenms/pull/14098)) - [murrant](https://github.com/murrant)
* Update config cache on install finalize step ([#14097](https://github.com/librenms/librenms/pull/14097)) - [murrant](https://github.com/murrant)
* Fix more webserver validation issues ([#14096](https://github.com/librenms/librenms/pull/14096)) - [murrant](https://github.com/murrant)
* Revert "Add apc battery operation time, including display in WebUI" ([#14068](https://github.com/librenms/librenms/pull/14068)) - [PipoCanaja](https://github.com/PipoCanaja)
* Use --no-patch instead ([#14047](https://github.com/librenms/librenms/pull/14047)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Cleanup
* Remove mib poller module remnants ([#14077](https://github.com/librenms/librenms/pull/14077)) - [murrant](https://github.com/murrant)

#### Documentation
* Small documentation typos ([#14101](https://github.com/librenms/librenms/pull/14101)) - [wrongecho](https://github.com/wrongecho)
* Documentation typos ([#14099](https://github.com/librenms/librenms/pull/14099)) - [wrongecho](https://github.com/wrongecho)
* Add SELinux configuration for SNMPd on Centos8 in the documentation ([#14095](https://github.com/librenms/librenms/pull/14095)) - [fbouynot](https://github.com/fbouynot)
* Add SELinux configuration for LDAP/AD authentication on Centos8 in the documentation ([#14094](https://github.com/librenms/librenms/pull/14094)) - [fbouynot](https://github.com/fbouynot)
* Add SELinux configuration for Smokeping on Centos8 in the documentation ([#14093](https://github.com/librenms/librenms/pull/14093)) - [fbouynot](https://github.com/fbouynot)
* Add SELinux configuration for RRDCached on Centos8 in the documentation ([#14092](https://github.com/librenms/librenms/pull/14092)) - [fbouynot](https://github.com/fbouynot)
* Corrected API devicegroup curl creation examples ([#14081](https://github.com/librenms/librenms/pull/14081)) - [rhinoau](https://github.com/rhinoau)
* Minor grammar fixes in doc ([#14078](https://github.com/librenms/librenms/pull/14078)) - [paulgear](https://github.com/paulgear)
* Add documentation for Chrony application monitoring ([#14066](https://github.com/librenms/librenms/pull/14066)) - [ottorei](https://github.com/ottorei)

#### Translation
* Few more updates to Ita lang ([#14091](https://github.com/librenms/librenms/pull/14091)) - [ppasserini](https://github.com/ppasserini)
* Italian language update ([#14085](https://github.com/librenms/librenms/pull/14085)) - [ppasserini](https://github.com/ppasserini)
* Adjustments to german translation ([#14083](https://github.com/librenms/librenms/pull/14083)) - [mwobst](https://github.com/mwobst)

#### Tests
* Test PHP 8.1 ([#14109](https://github.com/librenms/librenms/pull/14109)) - [murrant](https://github.com/murrant)

#### Misc
* Allow reapply yaml config via env ([#14100](https://github.com/librenms/librenms/pull/14100)) - [murrant](https://github.com/murrant)

#### Mibs
* More MIB fixing ([#14018](https://github.com/librenms/librenms/pull/14018)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Dependencies
* Bump terser from 4.8.0 to 4.8.1 ([#14123](https://github.com/librenms/librenms/pull/14123)) - [dependabot](https://github.com/apps/dependabot)
* Bump guzzlehttp/guzzle from 7.4.4 to 7.4.5 ([#14059](https://github.com/librenms/librenms/pull/14059)) - [dependabot](https://github.com/apps/dependabot)


## 22.6.0
*(2022-06-14)*

A big thank you to the following 22 contributors this last month:

  - [murrant](https://github.com/murrant) (22)
  - [gs-kamnas](https://github.com/gs-kamnas) (5)
  - [Jellyfrog](https://github.com/Jellyfrog) (4)
  - [dependabot](https://github.com/apps/dependabot) (2)
  - [kruczek8989](https://github.com/kruczek8989) (2)
  - [bile0026](https://github.com/bile0026) (1)
  - [dennypage](https://github.com/dennypage) (1)
  - [hjcday](https://github.com/hjcday) (1)
  - [Martin22](https://github.com/Martin22) (1)
  - [spyfly](https://github.com/spyfly) (1)
  - [Bobdave](https://github.com/Bobdave) (1)
  - [bennet-esyoil](https://github.com/bennet-esyoil) (1)
  - [DaftBrit](https://github.com/DaftBrit) (1)
  - [SanderBlom](https://github.com/SanderBlom) (1)
  - [thecityofguanyu](https://github.com/thecityofguanyu) (1)
  - [glance-](https://github.com/glance-) (1)
  - [duhow](https://github.com/duhow) (1)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (1)
  - [kevinwallace](https://github.com/kevinwallace) (1)
  - [HolgerHees](https://github.com/HolgerHees) (1)
  - [charlyforot](https://github.com/charlyforot) (1)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (21)
  - [Jellyfrog](https://github.com/Jellyfrog) (14)
  - [ottorei](https://github.com/ottorei) (5)
  - [PipoCanaja](https://github.com/PipoCanaja) (2)
  - [mpikzink](https://github.com/mpikzink) (1)
  - [f0o](https://github.com/f0o) (1)

#### Feature
* SnmpQuery walk multiple oids ([#14015](https://github.com/librenms/librenms/pull/14015)) - [murrant](https://github.com/murrant)
* Add support to REST API for creating Maintenance Schedules for Device Groups ([#13985](https://github.com/librenms/librenms/pull/13985)) - [thecityofguanyu](https://github.com/thecityofguanyu)
* Automatic fixes for validation failures ([#13930](https://github.com/librenms/librenms/pull/13930)) - [murrant](https://github.com/murrant)

#### Security
* Bumpver handlebars.js to 4.7.7 to resolve CVE-2021-23369 ([#13990](https://github.com/librenms/librenms/pull/13990)) - [gs-kamnas](https://github.com/gs-kamnas)

#### Device
* Fix use of incorrect variable to retrieve current sensor value ([#14037](https://github.com/librenms/librenms/pull/14037)) - [dennypage](https://github.com/dennypage)
* Support Huawei SMU11B ([#14029](https://github.com/librenms/librenms/pull/14029)) - [murrant](https://github.com/murrant)
* Update Teltonika Sensors for FW R_00.07.02 ([#14012](https://github.com/librenms/librenms/pull/14012)) - [hjcday](https://github.com/hjcday)
* Procurve hardware description cleanup ([#14007](https://github.com/librenms/librenms/pull/14007)) - [murrant](https://github.com/murrant)
* Racom Ray2 and Ray3 - Modulation states added ([#14001](https://github.com/librenms/librenms/pull/14001)) - [Martin22](https://github.com/Martin22)
* Fix OS Detection for USW-Flex-XG ([#13999](https://github.com/librenms/librenms/pull/13999)) - [spyfly](https://github.com/spyfly)
* Add support for Cisco Nexus 3550 series (formerly Exalink Fusion) devices ([#13992](https://github.com/librenms/librenms/pull/13992)) - [gs-kamnas](https://github.com/gs-kamnas)
* Improve support for Arista/Metamako MOS devices ([#13988](https://github.com/librenms/librenms/pull/13988)) - [gs-kamnas](https://github.com/gs-kamnas)
* Workaround issues with lldp information from GS108Tv1 ([#13971](https://github.com/librenms/librenms/pull/13971)) - [glance-](https://github.com/glance-)
* Add additional OpenBSD PF graphs ([#13963](https://github.com/librenms/librenms/pull/13963)) - [kevinwallace](https://github.com/kevinwallace)
* BGP unnumbered support for Cumulus ([#13785](https://github.com/librenms/librenms/pull/13785)) - [charlyforot](https://github.com/charlyforot)

#### Webui
* Dashboard code cleanup ([#13996](https://github.com/librenms/librenms/pull/13996)) - [murrant](https://github.com/murrant)
* Device Types Widget ([#13670](https://github.com/librenms/librenms/pull/13670)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Alerting
* Add macro.past_20m macro ([#14023](https://github.com/librenms/librenms/pull/14023)) - [kruczek8989](https://github.com/kruczek8989)
* Allow the use of a custom URL for accessing the PagerDuty API and correct API schema violation ([#14010](https://github.com/librenms/librenms/pull/14010)) - [gs-kamnas](https://github.com/gs-kamnas)
* Setting MSteams card summary to alert title ([#13989](https://github.com/librenms/librenms/pull/13989)) - [DaftBrit](https://github.com/DaftBrit)
* Use display name when sending alerts to Sensu ([#13967](https://github.com/librenms/librenms/pull/13967)) - [TheMysteriousX](https://github.com/TheMysteriousX)

#### Applications
* Add error-state to non-responsive mysql-servers ([#13993](https://github.com/librenms/librenms/pull/13993)) - [bennet-esyoil](https://github.com/bennet-esyoil)

#### Discovery
* Fix printer count sensors when extra garbage is returned ([#14014](https://github.com/librenms/librenms/pull/14014)) - [murrant](https://github.com/murrant)

#### Oxidized
* Syslog-notify-oxidized.php now always notifies Oxidized ([#14011](https://github.com/librenms/librenms/pull/14011)) - [murrant](https://github.com/murrant)
* Fix Oxidized syslog change notifier when the change was made via snmp. ([#14005](https://github.com/librenms/librenms/pull/14005)) - [kruczek8989](https://github.com/kruczek8989)

#### Authentication
* Implement support for usernames coming from reverse proxies ([#13894](https://github.com/librenms/librenms/pull/13894)) - [HolgerHees](https://github.com/HolgerHees)

#### Bug
* Git version validation improvement ([#14042](https://github.com/librenms/librenms/pull/14042)) - [murrant](https://github.com/murrant)
* Server name validation, handle ports better ([#14041](https://github.com/librenms/librenms/pull/14041)) - [murrant](https://github.com/murrant)
* Fix some broken migrations ([#14040](https://github.com/librenms/librenms/pull/14040)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix rrd version validation check ([#14036](https://github.com/librenms/librenms/pull/14036)) - [murrant](https://github.com/murrant)
* Fix DB timezone validation ([#14035](https://github.com/librenms/librenms/pull/14035)) - [murrant](https://github.com/murrant)
* Regression fix from #13596 ([#14034](https://github.com/librenms/librenms/pull/14034)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix an issue when getting the local version from git ([#14020](https://github.com/librenms/librenms/pull/14020)) - [murrant](https://github.com/murrant)
* Fix migrations failing one time after dashboard cleanup ([#14002](https://github.com/librenms/librenms/pull/14002)) - [murrant](https://github.com/murrant)
* Fix Netscaler VServer database updates during polling ([#13995](https://github.com/librenms/librenms/pull/13995)) - [Bobdave](https://github.com/Bobdave)
* Fix 500 error in validations when capabilities are not supported or set ([#13991](https://github.com/librenms/librenms/pull/13991)) - [gs-kamnas](https://github.com/gs-kamnas)

#### Cleanup
* Remove traceroute6, it is unused ([#14019](https://github.com/librenms/librenms/pull/14019)) - [murrant](https://github.com/murrant)
* Remove DefaultWidgetSeeder ([#14006](https://github.com/librenms/librenms/pull/14006)) - [murrant](https://github.com/murrant)

#### Documentation
* Okta saml ([#14038](https://github.com/librenms/librenms/pull/14038)) - [bile0026](https://github.com/bile0026)
* HPE Comware snmp config example ([#13997](https://github.com/librenms/librenms/pull/13997)) - [murrant](https://github.com/murrant)

#### Tests
* Fix tests failing when device with IP 127.1.6.1 exists ([#14016](https://github.com/librenms/librenms/pull/14016)) - [murrant](https://github.com/murrant)

#### Misc
* Slightly easier validation page error access ([#14044](https://github.com/librenms/librenms/pull/14044)) - [murrant](https://github.com/murrant)
* Lnms scan respect -q parameter ([#14027](https://github.com/librenms/librenms/pull/14027)) - [murrant](https://github.com/murrant)
* Custom OID processing of numeric strings with filters ([#13968](https://github.com/librenms/librenms/pull/13968)) - [duhow](https://github.com/duhow)
* Validate base_url and server_name ([#13941](https://github.com/librenms/librenms/pull/13941)) - [murrant](https://github.com/murrant)

#### Mibs
* Update MIBs ([#14017](https://github.com/librenms/librenms/pull/14017)) - [Jellyfrog](https://github.com/Jellyfrog)
* Updated existing Siemens MIBs and added trap MIB ([#13986](https://github.com/librenms/librenms/pull/13986)) - [SanderBlom](https://github.com/SanderBlom)

#### Dependencies
* Update Larastan ([#14031](https://github.com/librenms/librenms/pull/14031)) - [murrant](https://github.com/murrant)
* Update PHP dependencies ([#14028](https://github.com/librenms/librenms/pull/14028)) - [murrant](https://github.com/murrant)
* Bump guzzlehttp/guzzle from 7.4.3 to 7.4.4 ([#14025](https://github.com/librenms/librenms/pull/14025)) - [dependabot](https://github.com/apps/dependabot)
* Bump guzzlehttp/guzzle from 7.4.1 to 7.4.3 ([#13994](https://github.com/librenms/librenms/pull/13994)) - [dependabot](https://github.com/apps/dependabot)


## 22.5.0
*(2022-05-21)*

A big thank you to the following 23 contributors this last month:

  - [murrant](https://github.com/murrant) (7)
  - [VVelox](https://github.com/VVelox) (6)
  - [slimey99uk](https://github.com/slimey99uk) (2)
  - [dependabot](https://github.com/apps/dependabot) (2)
  - [Npeca75](https://github.com/Npeca75) (2)
  - [nox-x](https://github.com/nox-x) (1)
  - [nsn-amagruder](https://github.com/nsn-amagruder) (1)
  - [mkuurstra](https://github.com/mkuurstra) (1)
  - [booth-f](https://github.com/booth-f) (1)
  - [sajiby3k](https://github.com/sajiby3k) (1)
  - [skandragon](https://github.com/skandragon) (1)
  - [Martin22](https://github.com/Martin22) (1)
  - [twelch24](https://github.com/twelch24) (1)
  - [ppasserini](https://github.com/ppasserini) (1)
  - [bl3nd3r](https://github.com/bl3nd3r) (1)
  - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ) (1)
  - [Jimmy-Cl](https://github.com/Jimmy-Cl) (1)
  - [lfkeitel](https://github.com/lfkeitel) (1)
  - [steffann](https://github.com/steffann) (1)
  - [micko](https://github.com/micko) (1)
  - [IVI053](https://github.com/IVI053) (1)
  - [pfromme25](https://github.com/pfromme25) (1)
  - [mzacchi](https://github.com/mzacchi) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (19)
  - [murrant](https://github.com/murrant) (16)
  - [ottorei](https://github.com/ottorei) (3)
  - [mpikzink](https://github.com/mpikzink) (1)

#### Feature
* Allow unordered OIDs (global and per-os) ([#13923](https://github.com/librenms/librenms/pull/13923)) - [murrant](https://github.com/murrant)
* Added --ping-only to snmp-scan.py ([#13810](https://github.com/librenms/librenms/pull/13810)) - [IVI053](https://github.com/IVI053)

#### Device
* Add Cisco Business Wirless to ciscowlc.yaml ([#13984](https://github.com/librenms/librenms/pull/13984)) - [nsn-amagruder](https://github.com/nsn-amagruder)
* Add Eaton SC200 OS model ([#13978](https://github.com/librenms/librenms/pull/13978)) - [slimey99uk](https://github.com/slimey99uk)
* Fortigate LTE sensor addition ([#13977](https://github.com/librenms/librenms/pull/13977)) - [slimey99uk](https://github.com/slimey99uk)
* Added BKE power supply support. ([#13972](https://github.com/librenms/librenms/pull/13972)) - [Martin22](https://github.com/Martin22)
* Don't use bulk-walk for PrimeKey Appliances ([#13958](https://github.com/librenms/librenms/pull/13958)) - [bl3nd3r](https://github.com/bl3nd3r)
* Fix avtech12e sensors ([#13943](https://github.com/librenms/librenms/pull/13943)) - [lfkeitel](https://github.com/lfkeitel)
* [mikrotik] fixed RouterOS ipv4/ipv6 routes ([#13902](https://github.com/librenms/librenms/pull/13902)) - [Npeca75](https://github.com/Npeca75)
* Netscaler new counter metrics ([#13323](https://github.com/librenms/librenms/pull/13323)) - [mzacchi](https://github.com/mzacchi)

#### Webui
* Update dark mode to fix BGP and Peering page ([#13951](https://github.com/librenms/librenms/pull/13951)) - [Jimmy-Cl](https://github.com/Jimmy-Cl)
* Fix snmp.timeout setting via Web UI ([#13937](https://github.com/librenms/librenms/pull/13937)) - [murrant](https://github.com/murrant)

#### Alerting
* More realistic alert test data ([#13969](https://github.com/librenms/librenms/pull/13969)) - [murrant](https://github.com/murrant)

#### Graphs
* [RRD] fix IPv6 folder name ([#13945](https://github.com/librenms/librenms/pull/13945)) - [Npeca75](https://github.com/Npeca75)

#### Applications
* Remove alert keys from component for Suricata ([#13959](https://github.com/librenms/librenms/pull/13959)) - [VVelox](https://github.com/VVelox)
* Fix a few issues with variable names for Suricata ([#13956](https://github.com/librenms/librenms/pull/13956)) - [VVelox](https://github.com/VVelox)
* Scripts/json-app-tool.php JSON generation fix and add -S for SNMP extend name ([#13948](https://github.com/librenms/librenms/pull/13948)) - [VVelox](https://github.com/VVelox)
* Add Suricata monitoring ([#13942](https://github.com/librenms/librenms/pull/13942)) - [VVelox](https://github.com/VVelox)

#### Discovery
* Fix polling and discovery of FortiGate cluster sensors ([#13980](https://github.com/librenms/librenms/pull/13980)) - [mkuurstra](https://github.com/mkuurstra)

#### Oxidized
* Oxidized API to return groups based on device purpose or notes ([#13976](https://github.com/librenms/librenms/pull/13976)) - [sajiby3k](https://github.com/sajiby3k)

#### Authentication
* Auth_ldap_skip_group_check when ldap_compare is not supported ([#13926](https://github.com/librenms/librenms/pull/13926)) - [micko](https://github.com/micko)
* Add LDAP bind and userlist filter support to ldap-authorization ([#13788](https://github.com/librenms/librenms/pull/13788)) - [pfromme25](https://github.com/pfromme25)

#### Bug
* Inconsistency in ldap starttls config parameter ([#13987](https://github.com/librenms/librenms/pull/13987)) - [nox-x](https://github.com/nox-x)
* Fix version check error ([#13981](https://github.com/librenms/librenms/pull/13981)) - [murrant](https://github.com/murrant)
* Use full sudo path ([#13975](https://github.com/librenms/librenms/pull/13975)) - [skandragon](https://github.com/skandragon)
* Fix IPv6 in service check host ([#13939](https://github.com/librenms/librenms/pull/13939)) - [steffann](https://github.com/steffann)

#### Refactor
* Improve the efficiency of some queries ([#13974](https://github.com/librenms/librenms/pull/13974)) - [murrant](https://github.com/murrant)
* Move Config loading to a service provider ([#13927](https://github.com/librenms/librenms/pull/13927)) - [murrant](https://github.com/murrant)

#### Documentation
* Fix typo in dispatcher service doc ([#13979](https://github.com/librenms/librenms/pull/13979)) - [booth-f](https://github.com/booth-f)
* Fix  a typo in alerting doc ([#13970](https://github.com/librenms/librenms/pull/13970)) - [twelch24](https://github.com/twelch24)
* DOCS, switch to dark or light mode ([#13953](https://github.com/librenms/librenms/pull/13953)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* Assorted cleanups to alerting docs, primarily to make mdl happier ([#13950](https://github.com/librenms/librenms/pull/13950)) - [VVelox](https://github.com/VVelox)
* Update test doc making it more mdl happy and add a section on writing JSON app tests ([#13949](https://github.com/librenms/librenms/pull/13949)) - [VVelox](https://github.com/VVelox)

#### Translation
* Small Italian lang update ([#13960](https://github.com/librenms/librenms/pull/13960)) - [ppasserini](https://github.com/ppasserini)

#### Misc
* Improve lnms shortcut validation ([#13982](https://github.com/librenms/librenms/pull/13982)) - [murrant](https://github.com/murrant)

#### Dependencies
* Bump async from 2.6.3 to 2.6.4 ([#13947](https://github.com/librenms/librenms/pull/13947)) - [dependabot](https://github.com/apps/dependabot)
* Bump composer/composer from 2.2.4 to 2.3.5 ([#13944](https://github.com/librenms/librenms/pull/13944)) - [dependabot](https://github.com/apps/dependabot)


## 22.4.0
*(2022-04-21)*

A big thank you to the following 27 contributors this last month:

  - [murrant](https://github.com/murrant) (18)
  - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ) (3)
  - [laf](https://github.com/laf) (3)
  - [dependabot](https://github.com/apps/dependabot) (3)
  - [Fehler12](https://github.com/Fehler12) (3)
  - [ottorei](https://github.com/ottorei) (2)
  - [bl3nd3r](https://github.com/bl3nd3r) (2)
  - [p4k8](https://github.com/p4k8) (1)
  - [si458](https://github.com/si458) (1)
  - [TheMysteriousX](https://github.com/TheMysteriousX) (1)
  - [cliffalbert](https://github.com/cliffalbert) (1)
  - [Jimmy-Cl](https://github.com/Jimmy-Cl) (1)
  - [frenchie](https://github.com/frenchie) (1)
  - [ppasserini](https://github.com/ppasserini) (1)
  - [claude191](https://github.com/claude191) (1)
  - [westerterp](https://github.com/westerterp) (1)
  - [Cormoran96](https://github.com/Cormoran96) (1)
  - [WillIrvine](https://github.com/WillIrvine) (1)
  - [lucalo72](https://github.com/lucalo72) (1)
  - [ssasso](https://github.com/ssasso) (1)
  - [Jellyfrog](https://github.com/Jellyfrog) (1)
  - [geg347](https://github.com/geg347) (1)
  - [dandare100](https://github.com/dandare100) (1)
  - [bonzo81](https://github.com/bonzo81) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [jonathanjdavis](https://github.com/jonathanjdavis) (1)
  - [manonfgoo](https://github.com/manonfgoo) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (25)
  - [murrant](https://github.com/murrant) (16)
  - [laf](https://github.com/laf) (10)
  - [ottorei](https://github.com/ottorei) (3)
  - [PipoCanaja](https://github.com/PipoCanaja) (3)
  - [SourceDoctor](https://github.com/SourceDoctor) (2)
  - [haxmeadroom](https://github.com/haxmeadroom) (1)

#### Feature
* Add ISIS discovery and polling for iosxe devices ([#13880](https://github.com/librenms/librenms/pull/13880)) - [WillIrvine](https://github.com/WillIrvine)

#### Security
* Fix services command injection ([#13932](https://github.com/librenms/librenms/pull/13932)) - [murrant](https://github.com/murrant)
* Fix Graylog XSS ([#13931](https://github.com/librenms/librenms/pull/13931)) - [murrant](https://github.com/murrant)
* Bump minimist from 1.2.5 to 1.2.6 ([#13872](https://github.com/librenms/librenms/pull/13872)) - [dependabot](https://github.com/apps/dependabot)
* Fix SQL injection in get-host-dependencies ([#13868](https://github.com/librenms/librenms/pull/13868)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Device
* Support tplink routers ([#13922](https://github.com/librenms/librenms/pull/13922)) - [si458](https://github.com/si458)
* Fix Hytera error ([#13909](https://github.com/librenms/librenms/pull/13909)) - [murrant](https://github.com/murrant)
* PrimeKey Improvements ([#13901](https://github.com/librenms/librenms/pull/13901)) - [bl3nd3r](https://github.com/bl3nd3r)
* Enhancements for Zyxel OS ([#13897](https://github.com/librenms/librenms/pull/13897)) - [Jimmy-Cl](https://github.com/Jimmy-Cl)
* Added support for Dell PowerVault ME4024 ([#13883](https://github.com/librenms/librenms/pull/13883)) - [laf](https://github.com/laf)
* Add support for Riello NetMan 204 ([#13878](https://github.com/librenms/librenms/pull/13878)) - [lucalo72](https://github.com/lucalo72)
* Fix usw flex switch hardware detection ([#13877](https://github.com/librenms/librenms/pull/13877)) - [Fehler12](https://github.com/Fehler12)
* Added Polycom Lens SNMP support. ([#13876](https://github.com/librenms/librenms/pull/13876)) - [Fehler12](https://github.com/Fehler12)
* Fix for PFSense state table removals field ([#13863](https://github.com/librenms/librenms/pull/13863)) - [dandare100](https://github.com/dandare100)
* McAfee Proxy Sensor name update ([#13853](https://github.com/librenms/librenms/pull/13853)) - [PipoCanaja](https://github.com/PipoCanaja)
* Add support for PrimeKey Hardware Appliance ([#13806](https://github.com/librenms/librenms/pull/13806)) - [bl3nd3r](https://github.com/bl3nd3r)

#### Webui
* Add ISIS-details to alert details ([#13920](https://github.com/librenms/librenms/pull/13920)) - [ottorei](https://github.com/ottorei)
* Fix port mini graphs ([#13911](https://github.com/librenms/librenms/pull/13911)) - [murrant](https://github.com/murrant)
* Replaced OpenWRT icon with new logo ([#13891](https://github.com/librenms/librenms/pull/13891)) - [frenchie](https://github.com/frenchie)
* Add "Ping Response" graph to "Ping Only" Device Overview page ([#13886](https://github.com/librenms/librenms/pull/13886)) - [westerterp](https://github.com/westerterp)
* Remove fix size for column mac adresse ([#13881](https://github.com/librenms/librenms/pull/13881)) - [Cormoran96](https://github.com/Cormoran96)
* Fixed the top port errors widget returning bits graphs instead ([#13860](https://github.com/librenms/librenms/pull/13860)) - [laf](https://github.com/laf)

#### Alerting
* Correct type hint ([#13915](https://github.com/librenms/librenms/pull/13915)) - [TheMysteriousX](https://github.com/TheMysteriousX)
* Improve alert template saving ([#13910](https://github.com/librenms/librenms/pull/13910)) - [murrant](https://github.com/murrant)
* Added Basic authentication capability to Alertmanager Transport ([#13867](https://github.com/librenms/librenms/pull/13867)) - [geg347](https://github.com/geg347)

#### Snmp Traps
* Add Cisco Err-Disable interface event trap handler ([#13855](https://github.com/librenms/librenms/pull/13855)) - [bonzo81](https://github.com/bonzo81)

#### Api
* Ensure 'add_device' API returns indexed-array (as per doco) ([#13887](https://github.com/librenms/librenms/pull/13887)) - [claude191](https://github.com/claude191)

#### Polling
* Fix STP polling bug ([#13924](https://github.com/librenms/librenms/pull/13924)) - [murrant](https://github.com/murrant)
* Print full error message in poller/discovery output ([#13903](https://github.com/librenms/librenms/pull/13903)) - [murrant](https://github.com/murrant)

#### Rancid
* Add MRV OptiDriver support in gen_rancid ([#13900](https://github.com/librenms/librenms/pull/13900)) - [cliffalbert](https://github.com/cliffalbert)
* Add support for VyOS on Rancid conf ([#13874](https://github.com/librenms/librenms/pull/13874)) - [ssasso](https://github.com/ssasso)

#### Bug
* Validate fixes ([#13935](https://github.com/librenms/librenms/pull/13935)) - [murrant](https://github.com/murrant)
* Lnms device:add handle snmp.community bad format ([#13914](https://github.com/librenms/librenms/pull/13914)) - [murrant](https://github.com/murrant)
* Fix install icons ([#13904](https://github.com/librenms/librenms/pull/13904)) - [murrant](https://github.com/murrant)
* Handle bad uptime input ([#13899](https://github.com/librenms/librenms/pull/13899)) - [murrant](https://github.com/murrant)
* Fix custom error messages ([#13898](https://github.com/librenms/librenms/pull/13898)) - [murrant](https://github.com/murrant)
* Migrate addhost.php to lnms device:add ([#13870](https://github.com/librenms/librenms/pull/13870)) - [murrant](https://github.com/murrant)

#### Refactor
* Port Validation Page to Laravel ([#13921](https://github.com/librenms/librenms/pull/13921)) - [murrant](https://github.com/murrant)

#### Cleanup
* Better device:add error output ([#13913](https://github.com/librenms/librenms/pull/13913)) - [murrant](https://github.com/murrant)
* Remove non-working Dell specific alert rules from the collection ([#13706](https://github.com/librenms/librenms/pull/13706)) - [Fehler12](https://github.com/Fehler12)

#### Documentation
* Docs, fix code block in oxidized group ([#13908](https://github.com/librenms/librenms/pull/13908)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* [DOC] Fix Code blocks Step 2 Installing Network-WeatherMap ([#13905](https://github.com/librenms/librenms/pull/13905)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* [DOC] Styling the .env word quote ([#13889](https://github.com/librenms/librenms/pull/13889)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* Docs, fix Linux SNMP example only listening on ::1 ([#13882](https://github.com/librenms/librenms/pull/13882)) - [murrant](https://github.com/murrant)
* Script that describes how to migrate traffic bills from observium. ([#13757](https://github.com/librenms/librenms/pull/13757)) - [manonfgoo](https://github.com/manonfgoo)

#### Translation
* Ukrainian translation update ([#13933](https://github.com/librenms/librenms/pull/13933)) - [p4k8](https://github.com/p4k8)
* Few more language (ITA) updates ([#13890](https://github.com/librenms/librenms/pull/13890)) - [ppasserini](https://github.com/ppasserini)

#### Dependencies
* Bump guzzlehttp/psr7 from 2.1.0 to 2.2.1 ([#13879](https://github.com/librenms/librenms/pull/13879)) - [dependabot](https://github.com/apps/dependabot)
* Bump jpgraph version to 4 ([#13875](https://github.com/librenms/librenms/pull/13875)) - [ottorei](https://github.com/ottorei)
* Bump node-forge from 1.2.1 to 1.3.0 ([#13869](https://github.com/librenms/librenms/pull/13869)) - [dependabot](https://github.com/apps/dependabot)


## 22.3.0
*(2022-03-17)*

A big thank you to the following 22 contributors this last month:

  - [murrant](https://github.com/murrant) (7)
  - [Jellyfrog](https://github.com/Jellyfrog) (5)
  - [bonzo81](https://github.com/bonzo81) (4)
  - [laf](https://github.com/laf) (3)
  - [PipoCanaja](https://github.com/PipoCanaja) (2)
  - [charlyforot](https://github.com/charlyforot) (2)
  - [geg347](https://github.com/geg347) (2)
  - [westerterp](https://github.com/westerterp) (2)
  - [ospfbgp](https://github.com/ospfbgp) (1)
  - [MrXermon](https://github.com/MrXermon) (1)
  - [pandalion98](https://github.com/pandalion98) (1)
  - [eskyuu](https://github.com/eskyuu) (1)
  - [josh-silvas](https://github.com/josh-silvas) (1)
  - [martinberg](https://github.com/martinberg) (1)
  - [lpailhas](https://github.com/lpailhas) (1)
  - [hanserasmus](https://github.com/hanserasmus) (1)
  - [si458](https://github.com/si458) (1)
  - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ) (1)
  - [LTangaF](https://github.com/LTangaF) (1)
  - [dlangille](https://github.com/dlangille) (1)
  - [Npeca75](https://github.com/Npeca75) (1)
  - [woidi](https://github.com/woidi) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (19)
  - [murrant](https://github.com/murrant) (10)
  - [laf](https://github.com/laf) (8)
  - [SourceDoctor](https://github.com/SourceDoctor) (3)
  - [ottorei](https://github.com/ottorei) (1)
  - [bboy8012](https://github.com/bboy8012) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)

#### Feature
* Device_add support display field ([#13846](https://github.com/librenms/librenms/pull/13846)) - [murrant](https://github.com/murrant)
* Display Name in availability map ([#13841](https://github.com/librenms/librenms/pull/13841)) - [murrant](https://github.com/murrant)
* Implement OAuth and SAML2 support ([#13764](https://github.com/librenms/librenms/pull/13764)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Security
* Resolved XSS issue from alert rule list modal ([#13805](https://github.com/librenms/librenms/pull/13805)) - [laf](https://github.com/laf)

#### Device
* Add support for VOSS 8.6 ([#13857](https://github.com/librenms/librenms/pull/13857)) - [ospfbgp](https://github.com/ospfbgp)
* Device - HWG-WLD version 2 support ([#13849](https://github.com/librenms/librenms/pull/13849)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix an issue with the APC current discovery ([#13837](https://github.com/librenms/librenms/pull/13837)) - [eskyuu](https://github.com/eskyuu)
* Add VS state for Gaia ([#13831](https://github.com/librenms/librenms/pull/13831)) - [martinberg](https://github.com/martinberg)
* [new OS] Alpine OptoElectronics TDCM-EDFA support ([#13825](https://github.com/librenms/librenms/pull/13825)) - [charlyforot](https://github.com/charlyforot)
* Fix db delete on cisco-vrf-lite discovery ([#13823](https://github.com/librenms/librenms/pull/13823)) - [lpailhas](https://github.com/lpailhas)
* Add rocky linux OS identification ([#13815](https://github.com/librenms/librenms/pull/13815)) - [hanserasmus](https://github.com/hanserasmus)
* Detect truenas scale ([#13812](https://github.com/librenms/librenms/pull/13812)) - [si458](https://github.com/si458)
* [comware] dropped dbfetch from discovery/sensors ([#13796](https://github.com/librenms/librenms/pull/13796)) - [Npeca75](https://github.com/Npeca75)
* Correct divisor in discovery definition vertiv-pdu.yaml ([#13768](https://github.com/librenms/librenms/pull/13768)) - [woidi](https://github.com/woidi)

#### Webui
* Fixed displaying hostname in create bill when port is passed ([#13830](https://github.com/librenms/librenms/pull/13830)) - [laf](https://github.com/laf)
* Fix Oxidized Config tab showing when Device OS or Device Type is disabled ([#13809](https://github.com/librenms/librenms/pull/13809)) - [westerterp](https://github.com/westerterp)
* Position the buttons in the center on user preferences page ([#13802](https://github.com/librenms/librenms/pull/13802)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* Add usage hints for Display Name placeholder usage ([#13801](https://github.com/librenms/librenms/pull/13801)) - [LTangaF](https://github.com/LTangaF)
* Fix second menu bar in Apps screens ([#13800](https://github.com/librenms/librenms/pull/13800)) - [westerterp](https://github.com/westerterp)
* WebUI - Search results for ports ([#13787](https://github.com/librenms/librenms/pull/13787)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Alerting
* Newer versions of Jira use HTTP code 201 fore created issues ([#13852](https://github.com/librenms/librenms/pull/13852)) - [MrXermon](https://github.com/MrXermon)
* Add extra dynamic values alerttransport alertmanager ([#13828](https://github.com/librenms/librenms/pull/13828)) - [geg347](https://github.com/geg347)

#### Snmp Traps
* Add APC SmartAvrReducing trap handlers ([#13839](https://github.com/librenms/librenms/pull/13839)) - [bonzo81](https://github.com/bonzo81)
* Add APC upsOnBattery & powerRestored trap handler ([#13836](https://github.com/librenms/librenms/pull/13836)) - [bonzo81](https://github.com/bonzo81)
* Add OspfTxRetransmit Trap Handler ([#13824](https://github.com/librenms/librenms/pull/13824)) - [bonzo81](https://github.com/bonzo81)
* Cisco Mac address violation trap handler ([#13811](https://github.com/librenms/librenms/pull/13811)) - [bonzo81](https://github.com/bonzo81)

#### Api
* Adds API call to update port notes on devices. ([#13834](https://github.com/librenms/librenms/pull/13834)) - [josh-silvas](https://github.com/josh-silvas)

#### Bug
* Rewrite agent packages parsing code ([#13840](https://github.com/librenms/librenms/pull/13840)) - [murrant](https://github.com/murrant)
* Fix snmpv3 context when empty SnmpQuery ([#13832](https://github.com/librenms/librenms/pull/13832)) - [murrant](https://github.com/murrant)
* Regression fix from b6a8b602b891d9eb8633f62632c17bdc559cd620 ([#13819](https://github.com/librenms/librenms/pull/13819)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix PHPStan Deprecated test ([#13794](https://github.com/librenms/librenms/pull/13794)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Refactor
* Remove addHost from ModuleTestHelper ([#13847](https://github.com/librenms/librenms/pull/13847)) - [murrant](https://github.com/murrant)

#### Tests
* PHP8 phpstan fix ([#13843](https://github.com/librenms/librenms/pull/13843)) - [murrant](https://github.com/murrant)
* Fix tests for MariaDB \> 10.5.15 ([#13829](https://github.com/librenms/librenms/pull/13829)) - [Jellyfrog](https://github.com/Jellyfrog)
* Add MIB to OS helper to tests ([#13795](https://github.com/librenms/librenms/pull/13795)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Misc
* Add configuration support for IPMIv2 Kg key ([#13845](https://github.com/librenms/librenms/pull/13845)) - [pandalion98](https://github.com/pandalion98)
* New device:add code ([#13842](https://github.com/librenms/librenms/pull/13842)) - [murrant](https://github.com/murrant)
* Fix class error name prevents to show BER graph ([#13833](https://github.com/librenms/librenms/pull/13833)) - [charlyforot](https://github.com/charlyforot)
* Add generic PSU status failed alert rule template ([#13821](https://github.com/librenms/librenms/pull/13821)) - [geg347](https://github.com/geg347)
* Bump version to 22.2.1 ([#13798](https://github.com/librenms/librenms/pull/13798)) - [dlangille](https://github.com/dlangille)


## 22.2.0
*(2022-02-16)*

A big thank you to the following 22 contributors this last month:

  - [Jellyfrog](https://github.com/Jellyfrog) (22)
  - [murrant](https://github.com/murrant) (11)
  - [PipoCanaja](https://github.com/PipoCanaja) (7)
  - [Npeca75](https://github.com/Npeca75) (7)
  - [ilGino](https://github.com/ilGino) (6)
  - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ) (4)
  - [laf](https://github.com/laf) (3)
  - [h-barnhart](https://github.com/h-barnhart) (2)
  - [aztec102](https://github.com/aztec102) (2)
  - [Fehler12](https://github.com/Fehler12) (1)
  - [JKJameson](https://github.com/JKJameson) (1)
  - [jepke](https://github.com/jepke) (1)
  - [sGoico](https://github.com/sGoico) (1)
  - [dorkmatt](https://github.com/dorkmatt) (1)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)
  - [si458](https://github.com/si458) (1)
  - [dfitton](https://github.com/dfitton) (1)
  - [Martin22](https://github.com/Martin22) (1)
  - [fsmeets](https://github.com/fsmeets) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [knpo](https://github.com/knpo) (1)
  - [guipoletto](https://github.com/guipoletto) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (35)
  - [murrant](https://github.com/murrant) (16)
  - [laf](https://github.com/laf) (14)
  - [PipoCanaja](https://github.com/PipoCanaja) (10)
  - [ottorei](https://github.com/ottorei) (6)
  - [SourceDoctor](https://github.com/SourceDoctor) (5)
  - [kkrumm1](https://github.com/kkrumm1) (1)

#### Feature
* Implement system for user packages in composer ([#13718](https://github.com/librenms/librenms/pull/13718)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Security
* Hide community and make it XSS safer ([#13783](https://github.com/librenms/librenms/pull/13783)) - [PipoCanaja](https://github.com/PipoCanaja)
* Moved some pages to be within admin route ([#13782](https://github.com/librenms/librenms/pull/13782)) - [laf](https://github.com/laf)
* XSS fix ([#13780](https://github.com/librenms/librenms/pull/13780)),([#13778](https://github.com/librenms/librenms/pull/13778)),([#13776](https://github.com/librenms/librenms/pull/13776)),([#13775](https://github.com/librenms/librenms/pull/13775)) - [PipoCanaja](https://github.com/PipoCanaja)
* Bump follow-redirects package from 1.14.7 to 1.14.8 ([#13774](https://github.com/librenms/librenms/pull/13774)) - [dependabot](https://github.com/apps/dependabot)

#### Device
* Support for Terra sdi410c / sdi480 devices ([#13759](https://github.com/librenms/librenms/pull/13759)) - [Npeca75](https://github.com/Npeca75)
* Added BER to Racom Ray and Ray3 ([#13758](https://github.com/librenms/librenms/pull/13758)) - [Martin22](https://github.com/Martin22)
* Added system temperature support including state for Extreme VSP 4900 devices ([#13743](https://github.com/librenms/librenms/pull/13743)) - [laf](https://github.com/laf)
* Added ipv6 route discovery for Mikrotik devices ([#13737](https://github.com/librenms/librenms/pull/13737)) - [Npeca75](https://github.com/Npeca75)
* Fixed airos temp in 8.7.7 for ubnt devices ([#13731](https://github.com/librenms/librenms/pull/13731)) - [murrant](https://github.com/murrant)
* Added sensors to ZXA OS ([#13724](https://github.com/librenms/librenms/pull/13724)) - [aztec102](https://github.com/aztec102)
* Added support for Volius OS ([#13723](https://github.com/librenms/librenms/pull/13723)) - [aztec102](https://github.com/aztec102)
* Updated DDM MIBs for Jetstream OS ([#13715](https://github.com/librenms/librenms/pull/13715)) - [Npeca75](https://github.com/Npeca75)
* Added support for Ubiquiti Airfiber60 devices ([#13680](https://github.com/librenms/librenms/pull/13680)) - [jepke](https://github.com/jepke)
* Fixed Temperature Sensor for AirOS 8.7.4+ ([#13655](https://github.com/librenms/librenms/pull/13655)) - [JKJameson](https://github.com/JKJameson)
* Added support for Lenovo think station devices ([#13617](https://github.com/librenms/librenms/pull/13617)) - [Fehler12](https://github.com/Fehler12)
* Added sensors for huawei vrp devices ([#13352](https://github.com/librenms/librenms/pull/13352)) - [guipoletto](https://github.com/guipoletto)

#### Webui
* Rewrite includes/html/pages/device/health.inc.php ([#13777](https://github.com/librenms/librenms/pull/13777)) - [Npeca75](https://github.com/Npeca75)
* Various fixes for STP ([#13773](https://github.com/librenms/librenms/pull/13773)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added missing icons for progressive web app ([#13771](https://github.com/librenms/librenms/pull/13771)) - [murrant](https://github.com/murrant)
* Show selected selection option on graphs page in Mono theme ([#13765](https://github.com/librenms/librenms/pull/13765)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* Show selected option of devices-graphs-select in Mono theme ([#13752](https://github.com/librenms/librenms/pull/13752)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* Upgrade to font awesome 6 ([#13760](https://github.com/librenms/librenms/pull/13760)) - [Jellyfrog](https://github.com/Jellyfrog)
* Upgrade to font awesome 5 ([#13754](https://github.com/librenms/librenms/pull/13754)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update dark.css to improve visibility ([#13749](https://github.com/librenms/librenms/pull/13749)) - [dfitton](https://github.com/dfitton)
* Removing colon symbol from multiple dialogs ([#13742](https://github.com/librenms/librenms/pull/13742)) - [ilGino](https://github.com/ilGino)
* Added the word Actions in the header of the oxidized-nodes table ([#13741](https://github.com/librenms/librenms/pull/13741)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* Updated icons of links pointing to Health pages ([#13729](https://github.com/librenms/librenms/pull/13729)) - [ilGino](https://github.com/ilGino)
* Updated to a more consistent Fontawesome icon for the Export to PDF ([#13713](https://github.com/librenms/librenms/pull/13713)) - [ilGino](https://github.com/ilGino)
* Removing the colon symbol to the right of a control labels ([#13704](https://github.com/librenms/librenms/pull/13704)),([#13705](https://github.com/librenms/librenms/pull/13705)) - [ilGino](https://github.com/ilGino)
* Update to tailwind 3 ([#13695](https://github.com/librenms/librenms/pull/13695)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Alerting
* Added better default sensor alert template ([#13703](https://github.com/librenms/librenms/pull/13703)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Snmp Traps
* Added SNMP Traps for Brocade login traps ([#13770](https://github.com/librenms/librenms/pull/13770)) - [h-barnhart](https://github.com/h-barnhart)
* Added SNMP Traps for APC Overload Traps ([#13726](https://github.com/librenms/librenms/pull/13726)) - [h-barnhart](https://github.com/h-barnhart)

#### Discovery
* Check for empty SLA tags so we skip bad entries ([#13679](https://github.com/librenms/librenms/pull/13679)) - [laf](https://github.com/laf)

#### Bug
* Only try contexts in STP for Cisco devices ([#13767](https://github.com/librenms/librenms/pull/13767)) - [murrant](https://github.com/murrant)
* Fix settings array initial value ([#13755](https://github.com/librenms/librenms/pull/13755)) - [Jellyfrog](https://github.com/Jellyfrog)
* Use better filesystem functions to delete host rrd dir ([#13735](https://github.com/librenms/librenms/pull/13735)) - [Jellyfrog](https://github.com/Jellyfrog)
* Device:poll log poll complete ([#13733](https://github.com/librenms/librenms/pull/13733)) - [murrant](https://github.com/murrant)
* Fixed \< 0 exception in ports poller ([#13732](https://github.com/librenms/librenms/pull/13732)) - [murrant](https://github.com/murrant)
* Fixed ups-nut check ([#13722](https://github.com/librenms/librenms/pull/13722)) - [Jellyfrog](https://github.com/Jellyfrog)
* Changed fping hardcoded binary to user defined fping ([#13720](https://github.com/librenms/librenms/pull/13720)) - [sGoico](https://github.com/sGoico)

#### Refactor
* Oxidized reload called on Device update and remove ([#13730](https://github.com/librenms/librenms/pull/13730)) - [murrant](https://github.com/murrant)
* Stp module rewrite ([#13570](https://github.com/librenms/librenms/pull/13570)) - [murrant](https://github.com/murrant)

#### Cleanup
* Dropped dbfetch from discovery/sensors for pbn devices ([#13789](https://github.com/librenms/librenms/pull/13789)) - [Npeca75](https://github.com/Npeca75)
* Dropped dbfetch from discovery/sensors for junos devices ([#13784](https://github.com/librenms/librenms/pull/13784)) - [Npeca75](https://github.com/Npeca75)
* Removed contrib directory ([#13727](https://github.com/librenms/librenms/pull/13727)) - [murrant](https://github.com/murrant)
* Removed old vendor fix ([#13717](https://github.com/librenms/librenms/pull/13717)) - [Jellyfrog](https://github.com/Jellyfrog)
* Replace Requests library with HTTP Client ([#13689](https://github.com/librenms/librenms/pull/13689)) - [Jellyfrog](https://github.com/Jellyfrog)
* Use built in method to render a string with Blade ([#13688](https://github.com/librenms/librenms/pull/13688)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Documentation
* Simple docs tweaking ([#13792](https://github.com/librenms/librenms/pull/13792)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update Switching API doc to fix link ([#13786](https://github.com/librenms/librenms/pull/13786)) - [knpo](https://github.com/knpo)
* Rename DHCP Stats to ISC DHCP Stats ([#13756](https://github.com/librenms/librenms/pull/13756)) - [SourceDoctor](https://github.com/SourceDoctor)
* Update Install-LibreNMS.md to include pip3 install ([#13746](https://github.com/librenms/librenms/pull/13746)) - [si458](https://github.com/si458)
* validate-config-icon better positioning in the text ([#13744](https://github.com/librenms/librenms/pull/13744)) - [SantiagoSilvaZ](https://github.com/SantiagoSilvaZ)
* Updated Debian install docs to include pip3 install ([#13721](https://github.com/librenms/librenms/pull/13721)) - [dorkmatt](https://github.com/dorkmatt)
* Full rework of documentation for better nav, cleanup and fixes ([#13709](https://github.com/librenms/librenms/pull/13709)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Tests
* Bump test timeout to 2 hours ([#13769](https://github.com/librenms/librenms/pull/13769)) - [Jellyfrog](https://github.com/Jellyfrog)
* Speed up tests by reducing snmp timeout ([#13725](https://github.com/librenms/librenms/pull/13725)) - [murrant](https://github.com/murrant)
* Lnms dev:check add --os-modules-only option ([#13700](https://github.com/librenms/librenms/pull/13700)) - [murrant](https://github.com/murrant)

#### Misc
* Set default DB_TEST_PORT in database config ([#13793](https://github.com/librenms/librenms/pull/13793)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fixed links to connected devices in old style maps ([#13762](https://github.com/librenms/librenms/pull/13762)) - [fsmeets](https://github.com/fsmeets)
* Updated Top Devices widget to include filtering on ifOperStatus ([#13748](https://github.com/librenms/librenms/pull/13748)) - [Npeca75](https://github.com/Npeca75)
* Correct prefix for "kilo" should be small k, not capital K ([#13714](https://github.com/librenms/librenms/pull/13714)) - [ilGino](https://github.com/ilGino)
* Remove old composer preinstall script ([#13712](https://github.com/librenms/librenms/pull/13712)) - [Jellyfrog](https://github.com/Jellyfrog)
* Improve Proxy::shouldBeUsed ([#13702](https://github.com/librenms/librenms/pull/13702)) - [Jellyfrog](https://github.com/Jellyfrog)
* Use commit date to compare pull requestes ([#13701](https://github.com/librenms/librenms/pull/13701)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Dependencies
* Update JS deps ([#13694](https://github.com/librenms/librenms/pull/13694)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update dependencies ([#13684](https://github.com/librenms/librenms/pull/13684)) - [Jellyfrog](https://github.com/Jellyfrog)


## 22.1.0
*(2022-01-23)*

A big thank you to the following 26 contributors this last month:

  - [murrant](https://github.com/murrant) (9)
  - [Npeca75](https://github.com/Npeca75) (4)
  - [loopodoopo](https://github.com/loopodoopo) (3)
  - [ilGino](https://github.com/ilGino) (2)
  - [Jellyfrog](https://github.com/Jellyfrog) (2)
  - [SourceDoctor](https://github.com/SourceDoctor) (2)
  - [laf](https://github.com/laf) (2)
  - [aztec102](https://github.com/aztec102) (2)
  - [twelch24](https://github.com/twelch24) (1)
  - [tkjaer](https://github.com/tkjaer) (1)
  - [ssasso](https://github.com/ssasso) (1)
  - [TechieDylan](https://github.com/TechieDylan) (1)
  - [hvanoch](https://github.com/hvanoch) (1)
  - [h-barnhart](https://github.com/h-barnhart) (1)
  - [gdepeyrot](https://github.com/gdepeyrot) (1)
  - [k0079898](https://github.com/k0079898) (1)
  - [efelon](https://github.com/efelon) (1)
  - [westerterp](https://github.com/westerterp) (1)
  - [avermeer-tc](https://github.com/avermeer-tc) (1)
  - [Fehler12](https://github.com/Fehler12) (1)
  - [bonzo81](https://github.com/bonzo81) (1)
  - [glance-](https://github.com/glance-) (1)
  - [VirTechSystems](https://github.com/VirTechSystems) (1)
  - [iopsthecloud](https://github.com/iopsthecloud) (1)
  - [blubecks](https://github.com/blubecks) (1)
  - [thford89](https://github.com/thford89) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (22)
  - [Jellyfrog](https://github.com/Jellyfrog) (13)
  - [laf](https://github.com/laf) (10)
  - [PipoCanaja](https://github.com/PipoCanaja) (6)
  - [ottorei](https://github.com/ottorei) (2)
  - [SourceDoctor](https://github.com/SourceDoctor) (2)

#### Feature
* Add Sla jitter packet loss percent graph ([#13600](https://github.com/librenms/librenms/pull/13600)) - [bonzo81](https://github.com/bonzo81)
* Add API-routes for listing MPLS SAPs and services ([#13561](https://github.com/librenms/librenms/pull/13561)) - [blubecks](https://github.com/blubecks)

#### Device
* Merge enviromux-micro in existing NTI ([#13696](https://github.com/librenms/librenms/pull/13696)) - [Npeca75](https://github.com/Npeca75)
* Change the variable to detect version for mikrotik ups runtime device divisor ([#13678](https://github.com/librenms/librenms/pull/13678)) - [TechieDylan](https://github.com/TechieDylan)
* VRP BGP fixes ([#13675](https://github.com/librenms/librenms/pull/13675)) - [murrant](https://github.com/murrant)
* Added new sensors to mes23xx ([#13671](https://github.com/librenms/librenms/pull/13671)) - [Npeca75](https://github.com/Npeca75)
* Added initial detection MES2324F or MES2324FB ([#13669](https://github.com/librenms/librenms/pull/13669)) - [aztec102](https://github.com/aztec102)
* New OS: Adva XG 304 ([#13668](https://github.com/librenms/librenms/pull/13668)) - [h-barnhart](https://github.com/h-barnhart)
* Basic ZTE ZXA10 detection ([#13658](https://github.com/librenms/librenms/pull/13658)) - [murrant](https://github.com/murrant)
* Added CET Power T2S TSI ([#13645](https://github.com/librenms/librenms/pull/13645)) - [aztec102](https://github.com/aztec102)
* Eltek ospf poller disable ([#13635](https://github.com/librenms/librenms/pull/13635)) - [loopodoopo](https://github.com/loopodoopo)
* Smartoptics dcp m 40 zr ([#13634](https://github.com/librenms/librenms/pull/13634)) - [avermeer-tc](https://github.com/avermeer-tc)
* Updated XOS (Extreme) processor data to use correct OID ([#13633](https://github.com/librenms/librenms/pull/13633)) - [laf](https://github.com/laf)
* Initial Support for Fujitsu RX300 with iRMC4 ([#13631](https://github.com/librenms/librenms/pull/13631)) - [Fehler12](https://github.com/Fehler12)
* Teleste Luminato ports & qam/asi output sensors & yaml ([#13616](https://github.com/librenms/librenms/pull/13616)) - [Npeca75](https://github.com/Npeca75)
* Workaround linksys always mapping to g1 ([#13595](https://github.com/librenms/librenms/pull/13595)) - [glance-](https://github.com/glance-)
* Equallogic fix typo on os name in the storage module ([#13580](https://github.com/librenms/librenms/pull/13580)) - [iopsthecloud](https://github.com/iopsthecloud)

#### Webui
* [WebUI] Removing the colon symbol to the right of a control label ([#13698](https://github.com/librenms/librenms/pull/13698)) - [ilGino](https://github.com/ilGino)
* Remove unneccessary Section Description ([#13677](https://github.com/librenms/librenms/pull/13677)) - [SourceDoctor](https://github.com/SourceDoctor)
* Device tracepath using wrong variables to check for traceroute output ([#13674](https://github.com/librenms/librenms/pull/13674)) - [laf](https://github.com/laf)
* Allow filter by display name in device list ([#13665](https://github.com/librenms/librenms/pull/13665)) - [gdepeyrot](https://github.com/gdepeyrot)
* Sort Vlan Ports by ifName, ifDescr ([#13657](https://github.com/librenms/librenms/pull/13657)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix horizontal scrollbar in device list always visible ([#13653](https://github.com/librenms/librenms/pull/13653)) - [efelon](https://github.com/efelon)

#### Alerting
* Use display name in alerts ([#13650](https://github.com/librenms/librenms/pull/13650)) - [murrant](https://github.com/murrant)

#### Graphs
* Timos sap dot1q graphing fix ([#13654](https://github.com/librenms/librenms/pull/13654)) - [loopodoopo](https://github.com/loopodoopo)

#### Applications
* Add supervisord application ([#13673](https://github.com/librenms/librenms/pull/13673)) - [hvanoch](https://github.com/hvanoch)

#### Api
* API detect if new location should be fixed by default ([#13637](https://github.com/librenms/librenms/pull/13637)) - [murrant](https://github.com/murrant)
* Set fixed_coordinates via api ([#13593](https://github.com/librenms/librenms/pull/13593)) - [VirTechSystems](https://github.com/VirTechSystems)

#### Polling
* Added oids.no_bulk os setting ([#13666](https://github.com/librenms/librenms/pull/13666)) - [Npeca75](https://github.com/Npeca75)
* Add consistent output of name and app_id to Poller for all Applications (fixes #13641) ([#13648](https://github.com/librenms/librenms/pull/13648)) - [westerterp](https://github.com/westerterp)
* Fix SnmpQuery bulk boolean backwards ([#13636](https://github.com/librenms/librenms/pull/13636)) - [murrant](https://github.com/murrant)

#### Rancid
* Gen RANCID host also for FS.com devices ([#13682](https://github.com/librenms/librenms/pull/13682)) - [ssasso](https://github.com/ssasso)

#### Bug
* Use PHP_BINARY directly instead of trying to build path to PHP binary ([#13690](https://github.com/librenms/librenms/pull/13690)) - [Jellyfrog](https://github.com/Jellyfrog)
* Small text change for misspelled Virtual ([#13686](https://github.com/librenms/librenms/pull/13686)) - [ilGino](https://github.com/ilGino)
* Snmp timeout is a float, allow in config ([#13676](https://github.com/librenms/librenms/pull/13676)) - [murrant](https://github.com/murrant)
* Fix NULL device alert caused by services ([#13663](https://github.com/librenms/librenms/pull/13663)) - [k0079898](https://github.com/k0079898)
* Disable VRP VLANs test, seems to be a bug in snmpsim snmpbulkwalk ([#13649](https://github.com/librenms/librenms/pull/13649)) - [murrant](https://github.com/murrant)
* Remove extra rows with duplicate keys in SyncsModels trait ([#13632](https://github.com/librenms/librenms/pull/13632)) - [murrant](https://github.com/murrant)

#### Documentation
* Update rrdcached link ([#13692](https://github.com/librenms/librenms/pull/13692)) - [twelch24](https://github.com/twelch24)
* Add missing python3-pip installation dependency for debian11 ([#13691](https://github.com/librenms/librenms/pull/13691)) - [tkjaer](https://github.com/tkjaer)

#### Translation
* Fix settings language file to include Distributed Poller labels. ([#13511](https://github.com/librenms/librenms/pull/13511)) - [thford89](https://github.com/thford89)

#### Dependencies
* Update recommended PHP version to 8.0 ([#13687](https://github.com/librenms/librenms/pull/13687)) - [Jellyfrog](https://github.com/Jellyfrog)


## 21.12.0
*(2021-12-21)*

A big thank you to the following 30 contributors this last month:

  - [murrant](https://github.com/murrant) (38)
  - [Npeca75](https://github.com/Npeca75) (6)
  - [Jellyfrog](https://github.com/Jellyfrog) (3)
  - [twelch24](https://github.com/twelch24) (3)
  - [johnstruse](https://github.com/johnstruse) (2)
  - [nightcore500](https://github.com/nightcore500) (2)
  - [Martin22](https://github.com/Martin22) (2)
  - [wkamlun](https://github.com/wkamlun) (2)
  - [martinberg](https://github.com/martinberg) (1)
  - [mathieu-artic](https://github.com/mathieu-artic) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [carranzamarioagustin](https://github.com/carranzamarioagustin) (1)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)
  - [si458](https://github.com/si458) (1)
  - [Cormoran96](https://github.com/Cormoran96) (1)
  - [claude191](https://github.com/claude191) (1)
  - [ottorei](https://github.com/ottorei) (1)
  - [banachtarski-91](https://github.com/banachtarski-91) (1)
  - [RockyVod](https://github.com/RockyVod) (1)
  - [enferas](https://github.com/enferas) (1)
  - [jepke](https://github.com/jepke) (1)
  - [duhow](https://github.com/duhow) (1)
  - [Nocturr](https://github.com/Nocturr) (1)
  - [hjcday](https://github.com/hjcday) (1)
  - [djamp42](https://github.com/djamp42) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)
  - [amanualgoldstein](https://github.com/amanualgoldstein) (1)
  - [pepperoni-pi](https://github.com/pepperoni-pi) (1)
  - [paulierco](https://github.com/paulierco) (1)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (42)
  - [murrant](https://github.com/murrant) (20)
  - [SourceDoctor](https://github.com/SourceDoctor) (3)
  - [ottorei](https://github.com/ottorei) (3)
  - [bakerds](https://github.com/bakerds) (2)
  - [jaypo82](https://github.com/jaypo82) (1)
  - [mpikzink](https://github.com/mpikzink) (1)
  - [troublestarter](https://github.com/troublestarter) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)

#### Feature
* Catch all module errors ([#13542](https://github.com/librenms/librenms/pull/13542)) - [murrant](https://github.com/murrant)
* Lnms snmp:fetch query multiple devices ([#13538](https://github.com/librenms/librenms/pull/13538)) - [murrant](https://github.com/murrant)
* Configurable device display name ([#13528](https://github.com/librenms/librenms/pull/13528)) - [murrant](https://github.com/murrant)
* RRD Allow specifying a source file and ds to fill data ([#13480](https://github.com/librenms/librenms/pull/13480)) - [murrant](https://github.com/murrant)

#### Security
* Fix unescaped strings XSS issues ([#13554](https://github.com/librenms/librenms/pull/13554)) - [enferas](https://github.com/enferas)

#### Device
* Fix EdgeOS hardware detection ([#13629](https://github.com/librenms/librenms/pull/13629)) - [johnstruse](https://github.com/johnstruse)
* Add additional ICX 7150 devices ([#13627](https://github.com/librenms/librenms/pull/13627)) - [johnstruse](https://github.com/johnstruse)
* Fix axiscam serial format ([#13620](https://github.com/librenms/librenms/pull/13620)) - [murrant](https://github.com/murrant)
* Fix Cisco WLC AP cleanup ([#13615](https://github.com/librenms/librenms/pull/13615)) - [murrant](https://github.com/murrant)
* CPU, HW type, HW ver, Fan state discovery ([#13608](https://github.com/librenms/librenms/pull/13608)) - [Npeca75](https://github.com/Npeca75)
* Initial Eltex-mes IPv6 address discovery ([#13594](https://github.com/librenms/librenms/pull/13594)) - [Npeca75](https://github.com/Npeca75)
* Add better support for Eaton UPS ([#13588](https://github.com/librenms/librenms/pull/13588)) - [mathieu-artic](https://github.com/mathieu-artic)
* SmartAX supports IF-MIB, skip custom polling and fix polling GPON ports ([#13579](https://github.com/librenms/librenms/pull/13579)) - [carranzamarioagustin](https://github.com/carranzamarioagustin)
* Add back Areca secondary hardware OID ([#13562](https://github.com/librenms/librenms/pull/13562)) - [murrant](https://github.com/murrant)
* Ray3 - Added memory chart ([#13557](https://github.com/librenms/librenms/pull/13557)) - [Martin22](https://github.com/Martin22)
* Add all Aviat WTM4k family devices ([#13556](https://github.com/librenms/librenms/pull/13556)) - [RockyVod](https://github.com/RockyVod)
* Fix discovery and pooling Racom Ray2 ([#13553](https://github.com/librenms/librenms/pull/13553)) - [Martin22](https://github.com/Martin22)
* Add version for ironware ([#13551](https://github.com/librenms/librenms/pull/13551)) - [wkamlun](https://github.com/wkamlun)
* Add ICX 7150 description ([#13550](https://github.com/librenms/librenms/pull/13550)) - [wkamlun](https://github.com/wkamlun)
* Add discovery for APC EPDU1132M ([#13545](https://github.com/librenms/librenms/pull/13545)) - [duhow](https://github.com/duhow)
* Split Eltex-mes OS to mes21xx / mes23xx. + few improvements ([#13544](https://github.com/librenms/librenms/pull/13544)) - [Npeca75](https://github.com/Npeca75)
* Add Initial Support for VMware SD-WAN / Velocloud ([#13536](https://github.com/librenms/librenms/pull/13536)) - [Nocturr](https://github.com/Nocturr)
* Arista EOS, use sysObjectID for detection (to include vEOS) ([#13534](https://github.com/librenms/librenms/pull/13534)) - [murrant](https://github.com/murrant)
* RUTX fix sensor limits ([#13526](https://github.com/librenms/librenms/pull/13526)) - [hjcday](https://github.com/hjcday)
* Added support for Himoinsa gensets status state sensors ([#13456](https://github.com/librenms/librenms/pull/13456)) - [TheGreatDoc](https://github.com/TheGreatDoc)

#### Webui
* VLANs sort in GUI ([#13628](https://github.com/librenms/librenms/pull/13628)) - [Npeca75](https://github.com/Npeca75)
* Fix broken links ([#13625](https://github.com/librenms/librenms/pull/13625)) - [murrant](https://github.com/murrant)
* Fix the displayed unit for frequency and distance in the wireless section ([#13614](https://github.com/librenms/librenms/pull/13614)) - [nightcore500](https://github.com/nightcore500)
* Add more device fields to oxidized map configuration ([#13604](https://github.com/librenms/librenms/pull/13604)) - [martinberg](https://github.com/martinberg)
* Correct graph row component responsive layout for linked graphs ([#13587](https://github.com/librenms/librenms/pull/13587)) - [murrant](https://github.com/murrant)
* Global search: search device display ([#13583](https://github.com/librenms/librenms/pull/13583)) - [murrant](https://github.com/murrant)
* Availibility Map - show Display Name if set ([#13574](https://github.com/librenms/librenms/pull/13574)) - [SourceDoctor](https://github.com/SourceDoctor)
* Do not show location in device overview if location is not found ([#13572](https://github.com/librenms/librenms/pull/13572)) - [murrant](https://github.com/murrant)
* Don't use @lang() it doesn't escape the string ([#13566](https://github.com/librenms/librenms/pull/13566)) - [murrant](https://github.com/murrant)
* Allow LegacyPlugin Pages to receive all parameters ([#13519](https://github.com/librenms/librenms/pull/13519)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Alerting
* Use http for proxy urls via guzzle ([#13601](https://github.com/librenms/librenms/pull/13601)) - [Jellyfrog](https://github.com/Jellyfrog)
* Twilio change text to show alert termplate msg ([#13521](https://github.com/librenms/librenms/pull/13521)) - [djamp42](https://github.com/djamp42)

#### Graphs
* Fix nototal graph option ([#13589](https://github.com/librenms/librenms/pull/13589)) - [nightcore500](https://github.com/nightcore500)
* Add additional type tag for Bind metrics ([#13581](https://github.com/librenms/librenms/pull/13581)) - [murrant](https://github.com/murrant)

#### Snmp Traps
* Veeam SNMP traps fix and extend ([#13549](https://github.com/librenms/librenms/pull/13549)) - [jepke](https://github.com/jepke)
* Add Alcatel Omniswitch Traps Handlers ([#13492](https://github.com/librenms/librenms/pull/13492)) - [paulierco](https://github.com/paulierco)

#### Applications
* PHP8 compatibility for ntp-server polling app ([#13513](https://github.com/librenms/librenms/pull/13513)) - [amanualgoldstein](https://github.com/amanualgoldstein)

#### Billing
* Billing fix a couple divide by zero errors ([#13517](https://github.com/librenms/librenms/pull/13517)) - [murrant](https://github.com/murrant)

#### Api
* API 'list_parents_of_host' - mostly doco, but one small code enhancement ([#13567](https://github.com/librenms/librenms/pull/13567)) - [claude191](https://github.com/claude191)

#### Discovery
* Q-bridge-mib, discover missing VLANs, v2 ([#13569](https://github.com/librenms/librenms/pull/13569)) - [Npeca75](https://github.com/Npeca75)

#### Polling
* Ios-fdb-table-fix -- Fixed misnamed dictionary keys for dot1dTpFdbPor… ([#13559](https://github.com/librenms/librenms/pull/13559)) - [banachtarski-91](https://github.com/banachtarski-91)
* OSPF issue when devices don't support OSPF-MIB::ospfIfTable ([#13530](https://github.com/librenms/librenms/pull/13530)) - [murrant](https://github.com/murrant)
* Poller command rewrite ([#13414](https://github.com/librenms/librenms/pull/13414)) - [murrant](https://github.com/murrant)

#### Bug
* Escape net-snmp unformatted strings, try 2 ([#13584](https://github.com/librenms/librenms/pull/13584)) - [murrant](https://github.com/murrant)
* Workaround don't poll WLC on IOSXE ([#13563](https://github.com/librenms/librenms/pull/13563)) - [murrant](https://github.com/murrant)
* Fix Ciscowlc AP-polling ([#13560](https://github.com/librenms/librenms/pull/13560)) - [ottorei](https://github.com/ottorei)
* Mark OID not increasing as invalid ([#13548](https://github.com/librenms/librenms/pull/13548)) - [murrant](https://github.com/murrant)
* Skip invalid OSPF data ([#13547](https://github.com/librenms/librenms/pull/13547)) - [murrant](https://github.com/murrant)
* Remove color markers when logging to files ([#13541](https://github.com/librenms/librenms/pull/13541)) - [murrant](https://github.com/murrant)
* Fix plugin_active check when plugin is not found ([#13531](https://github.com/librenms/librenms/pull/13531)) - [murrant](https://github.com/murrant)
* Remove unused buggy arp_discovery code ([#13529](https://github.com/librenms/librenms/pull/13529)) - [murrant](https://github.com/murrant)
* Pseudowire cpwVcID can exceed database max value ([#13510](https://github.com/librenms/librenms/pull/13510)) - [pepperoni-pi](https://github.com/pepperoni-pi)

#### Refactor
* Ipv6 discovery switch to new DB syntax ([#13591](https://github.com/librenms/librenms/pull/13591)) - [Npeca75](https://github.com/Npeca75)
* OSPF port module ([#13498](https://github.com/librenms/librenms/pull/13498)) - [murrant](https://github.com/murrant)

#### Cleanup
* Fix bad snmp context option ([#13497](https://github.com/librenms/librenms/pull/13497)) - [murrant](https://github.com/murrant)
* Polling cleanup, fix PHP warnings ([#13460](https://github.com/librenms/librenms/pull/13460)) - [murrant](https://github.com/murrant)

#### Documentation
* Clarify docker app setup on debian/ubuntu ([#13573](https://github.com/librenms/librenms/pull/13573)) - [si458](https://github.com/si458)
* Update winbox launcher doc (again) ([#13558](https://github.com/librenms/librenms/pull/13558)) - [twelch24](https://github.com/twelch24)
* Add more detailed instructions to winbox launcher ([#13552](https://github.com/librenms/librenms/pull/13552)) - [twelch24](https://github.com/twelch24)
* Link for can't check Python dependencies validation ([#13520](https://github.com/librenms/librenms/pull/13520)) - [murrant](https://github.com/murrant)
* Elaborate on feeding Oxidized ([#13514](https://github.com/librenms/librenms/pull/13514)) - [murrant](https://github.com/murrant)

#### Tests
* Tests dont include empty tables ([#13619](https://github.com/librenms/librenms/pull/13619)) - [murrant](https://github.com/murrant)
* Collect-snmp-data.php can now capture snmp context test data ([#13596](https://github.com/librenms/librenms/pull/13596)) - [murrant](https://github.com/murrant)
* Snmp.unescape setting ([#13590](https://github.com/librenms/librenms/pull/13590)) - [murrant](https://github.com/murrant)
* Use phpstan-deprecation-rules instead ([#13582](https://github.com/librenms/librenms/pull/13582)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Mibs
* Update netapp mib ([#13571](https://github.com/librenms/librenms/pull/13571)) - [Cormoran96](https://github.com/Cormoran96)

#### Dependencies
* Bump symfony/http-kernel from 5.3.9 to 5.4.0 ([#13585](https://github.com/librenms/librenms/pull/13585)) - [dependabot](https://github.com/apps/dependabot)


## 21.11.0
*(2021-11-12)*

A big thank you to the following 49 contributors this last month:

  - [murrant](https://github.com/murrant) (39)
  - [Jellyfrog](https://github.com/Jellyfrog) (6)
  - [arrmo](https://github.com/arrmo) (4)
  - [Nocturr](https://github.com/Nocturr) (4)
  - [PipoCanaja](https://github.com/PipoCanaja) (3)
  - [TheGreatDoc](https://github.com/TheGreatDoc) (3)
  - [martinberg](https://github.com/martinberg) (3)
  - [robje](https://github.com/robje) (2)
  - [loopodoopo](https://github.com/loopodoopo) (2)
  - [Npeca75](https://github.com/Npeca75) (2)
  - [drshawnkwang](https://github.com/drshawnkwang) (2)
  - [jul13579](https://github.com/jul13579) (2)
  - [bakerds](https://github.com/bakerds) (2)
  - [SourceDoctor](https://github.com/SourceDoctor) (2)
  - [deajan](https://github.com/deajan) (2)
  - [jonathansm](https://github.com/jonathansm) (1)
  - [lfkeitel](https://github.com/lfkeitel) (1)
  - [Deltawings](https://github.com/Deltawings) (1)
  - [fcuello-gc](https://github.com/fcuello-gc) (1)
  - [drommc](https://github.com/drommc) (1)
  - [techladsjamie](https://github.com/techladsjamie) (1)
  - [duhow](https://github.com/duhow) (1)
  - [hjcday](https://github.com/hjcday) (1)
  - [DanielMuller-TN](https://github.com/DanielMuller-TN) (1)
  - [blagh](https://github.com/blagh) (1)
  - [cenjui](https://github.com/cenjui) (1)
  - [TheGracens](https://github.com/TheGracens) (1)
  - [eskyuu](https://github.com/eskyuu) (1)
  - [nq5](https://github.com/nq5) (1)
  - [mjbnz](https://github.com/mjbnz) (1)
  - [roycruse](https://github.com/roycruse) (1)
  - [ottorei](https://github.com/ottorei) (1)
  - [si458](https://github.com/si458) (1)
  - [nmanzi](https://github.com/nmanzi) (1)
  - [apokryphal](https://github.com/apokryphal) (1)
  - [thford89](https://github.com/thford89) (1)
  - [arjitc](https://github.com/arjitc) (1)
  - [tuxgasy](https://github.com/tuxgasy) (1)
  - [kterobinson](https://github.com/kterobinson) (1)
  - [bl3nd3r](https://github.com/bl3nd3r) (1)
  - [hanserasmus](https://github.com/hanserasmus) (1)
  - [mpikzink](https://github.com/mpikzink) (1)
  - [dagbdagb](https://github.com/dagbdagb) (1)
  - [sthen](https://github.com/sthen) (1)
  - [ahmedsajid](https://github.com/ahmedsajid) (1)
  - [dorkmatt](https://github.com/dorkmatt) (1)
  - [evheros](https://github.com/evheros) (1)
  - [nightcore500](https://github.com/nightcore500) (1)
  - [CirnoT](https://github.com/CirnoT) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (53)
  - [Jellyfrog](https://github.com/Jellyfrog) (47)
  - [PipoCanaja](https://github.com/PipoCanaja) (7)
  - [SourceDoctor](https://github.com/SourceDoctor) (5)
  - [ottorei](https://github.com/ottorei) (5)
  - [mpikzink](https://github.com/mpikzink) (2)
  - [arjitc](https://github.com/arjitc) (1)
  - [salmayno](https://github.com/salmayno) (1)
  - [yoeunes](https://github.com/yoeunes) (1)
  - [Aeet](https://github.com/Aeet) (1)
  - [oussama-aitmi](https://github.com/oussama-aitmi) (1)
  - [nightcore500](https://github.com/nightcore500) (1)
  - [drshawnkwang](https://github.com/drshawnkwang) (1)

#### Feature
* Discovery on Reboot ([#13422](https://github.com/librenms/librenms/pull/13422)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Security
* Fix widget title injection vulnerability ([#13452](https://github.com/librenms/librenms/pull/13452)) - [murrant](https://github.com/murrant)
* Kick other sessions when changing password ([#13194](https://github.com/librenms/librenms/pull/13194)) - [murrant](https://github.com/murrant)

#### Device
* APC Load, wrong low precision oid ([#13506](https://github.com/librenms/librenms/pull/13506)) - [jonathansm](https://github.com/jonathansm)
* Nokia SAR HMC ([#13503](https://github.com/librenms/librenms/pull/13503)) - [loopodoopo](https://github.com/loopodoopo)
* Ciena 2 ([#13491](https://github.com/librenms/librenms/pull/13491)) - [loopodoopo](https://github.com/loopodoopo)
* Added initial IPv6 Address discovery for TP-LINK Jetstream ([#13484](https://github.com/librenms/librenms/pull/13484)) - [Npeca75](https://github.com/Npeca75)
* Update Windows Versions ([#13474](https://github.com/librenms/librenms/pull/13474)) - [arrmo](https://github.com/arrmo)
* Support for PDUMNV30HVLX with PADM 20 ([#13473](https://github.com/librenms/librenms/pull/13473)) - [drommc](https://github.com/drommc)
* Opengear improvement, don't needlessly fetch ogEmdTemperatureTable ([#13471](https://github.com/librenms/librenms/pull/13471)) - [drshawnkwang](https://github.com/drshawnkwang)
* RouterOS now returns the correct runtime ([#13461](https://github.com/librenms/librenms/pull/13461)) - [murrant](https://github.com/murrant)
* Fix latitude having an extra - in the middle after the decimal point ([#13454](https://github.com/librenms/librenms/pull/13454)) - [techladsjamie](https://github.com/techladsjamie)
* Fix Sophos-XG OID for number of active tunnels ([#13444](https://github.com/librenms/librenms/pull/13444)) - [Nocturr](https://github.com/Nocturr)
* Fix hwg poseidon state sensors ([#13438](https://github.com/librenms/librenms/pull/13438)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* UniFi/EdgeSwitch OS support for fw 5.x ([#13434](https://github.com/librenms/librenms/pull/13434)) - [Nocturr](https://github.com/Nocturr)
* Added support for Extreme SLX-OS switches ([#13431](https://github.com/librenms/librenms/pull/13431)) - [bakerds](https://github.com/bakerds)
* Mikrotik vlans discovery, v2 ([#13427](https://github.com/librenms/librenms/pull/13427)) - [Npeca75](https://github.com/Npeca75)
* Added sensor discovery for APC NetworkAIR FM and InfraStruXure ATS ([#13426](https://github.com/librenms/librenms/pull/13426)) - [bakerds](https://github.com/bakerds)
* Add Sophos-XG OID for monitoring HA, IPSec and license state ([#13423](https://github.com/librenms/librenms/pull/13423)) - [Nocturr](https://github.com/Nocturr)
* Add more discovery components for Dell PowerConnect 28xx ([#13420](https://github.com/librenms/librenms/pull/13420)) - [duhow](https://github.com/duhow)
* Added Wireless Sensors for Teltonika RUTX Routers ([#13419](https://github.com/librenms/librenms/pull/13419)) - [hjcday](https://github.com/hjcday)
* Add APC PowerChute sysObjectID ([#13406](https://github.com/librenms/librenms/pull/13406)) - [cenjui](https://github.com/cenjui)
* Fix APC high precision divisor ([#13405](https://github.com/librenms/librenms/pull/13405)) - [TheGracens](https://github.com/TheGracens)
* Luminato device doesn't support alternative uptimes ([#13399](https://github.com/librenms/librenms/pull/13399)) - [eskyuu](https://github.com/eskyuu)
* Update ports module to accept VDSL2 ifType in xDSL polling ([#13393](https://github.com/librenms/librenms/pull/13393)) - [roycruse](https://github.com/roycruse)
* Fix windows fanspeed ([#13376](https://github.com/librenms/librenms/pull/13376)) - [si458](https://github.com/si458)
* Add support for Teltonika RUT360 ([#13375](https://github.com/librenms/librenms/pull/13375)) - [martinberg](https://github.com/martinberg)
* Add support for NTI Enviromux ([#13373](https://github.com/librenms/librenms/pull/13373)) - [martinberg](https://github.com/martinberg)
* Update Himoinsa Genset discovery ([#13356](https://github.com/librenms/librenms/pull/13356)) - [TheGreatDoc](https://github.com/TheGreatDoc)
* Improve Poweralert based devices support ([#13340](https://github.com/librenms/librenms/pull/13340)) - [arjitc](https://github.com/arjitc)
* Add iDrac physical disk state ([#13264](https://github.com/librenms/librenms/pull/13264)) - [tuxgasy](https://github.com/tuxgasy)
* Add opengear humidity to sensors ([#13226](https://github.com/librenms/librenms/pull/13226)) - [drshawnkwang](https://github.com/drshawnkwang)
* IOS/IOSXE PoE stats ([#13213](https://github.com/librenms/librenms/pull/13213)) - [martinberg](https://github.com/martinberg)
* Poll SPU memory from Juniper SRX devices ([#13191](https://github.com/librenms/librenms/pull/13191)) - [bl3nd3r](https://github.com/bl3nd3r)
* Extend support for Endrun Sonoma Meridian II devices. ([#13069](https://github.com/librenms/librenms/pull/13069)) - [hanserasmus](https://github.com/hanserasmus)
* Bintec be.IP plus support ([#12993](https://github.com/librenms/librenms/pull/12993)) - [jul13579](https://github.com/jul13579)
* Eaton Network MS xups sensors ([#12992](https://github.com/librenms/librenms/pull/12992)) - [dagbdagb](https://github.com/dagbdagb)
* Don't hardcode index for Mikrotik LTE wireless statistics ([#12976](https://github.com/librenms/librenms/pull/12976)) - [sthen](https://github.com/sthen)
* OS support for West Mountain 4005i DC PDU ([#12885](https://github.com/librenms/librenms/pull/12885)) - [dorkmatt](https://github.com/dorkmatt)
* FabOS remove disabled dbm sensors ([#12877](https://github.com/librenms/librenms/pull/12877)) - [evheros](https://github.com/evheros)
* Raspberry Pi: Add SNMP extend to monitor IO pins or sensor modules connected to the GPIO header ([#12749](https://github.com/librenms/librenms/pull/12749)) - [nightcore500](https://github.com/nightcore500)
* Use high precision OIDs for APC UPS sensors ([#12594](https://github.com/librenms/librenms/pull/12594)) - [CirnoT](https://github.com/CirnoT)

#### Webui
* Increase default session lifetime to one month ([#13505](https://github.com/librenms/librenms/pull/13505)) - [murrant](https://github.com/murrant)
* Fix maintenance mode button in Firefox ([#13500](https://github.com/librenms/librenms/pull/13500)) - [lfkeitel](https://github.com/lfkeitel)
* Only call htmlentities on port ifAlias,ifName, and ifDescr ([#13489](https://github.com/librenms/librenms/pull/13489)) - [murrant](https://github.com/murrant)
* Merchandise shop link in about ([#13485](https://github.com/librenms/librenms/pull/13485)) - [murrant](https://github.com/murrant)
* Tweak new notification appearance ([#13477](https://github.com/librenms/librenms/pull/13477)) - [murrant](https://github.com/murrant)
* Add missing \</div\> ([#13459](https://github.com/librenms/librenms/pull/13459)) - [robje](https://github.com/robje)
* Fix syslog widget priority filtering ([#13411](https://github.com/librenms/librenms/pull/13411)) - [murrant](https://github.com/murrant)
* PHP-Flasher for toast messages ([#13401](https://github.com/librenms/librenms/pull/13401)) - [murrant](https://github.com/murrant)
* Update alertlog-widget to allow filtering by device group ([#13380](https://github.com/librenms/librenms/pull/13380)) - [ottorei](https://github.com/ottorei)

#### Alerting
* Fix slack errors when variables are not set ([#13476](https://github.com/librenms/librenms/pull/13476)) - [murrant](https://github.com/murrant)
* Fix api transport mult-line parsing ([#13469](https://github.com/librenms/librenms/pull/13469)) - [murrant](https://github.com/murrant)
* Drop PDConnect Install links ([#13407](https://github.com/librenms/librenms/pull/13407)) - [blagh](https://github.com/blagh)
* Port Speed degraded alert rule ([#13371](https://github.com/librenms/librenms/pull/13371)) - [murrant](https://github.com/murrant)
* Added Unpolled Devices rule to collection ([#12896](https://github.com/librenms/librenms/pull/12896)) - [ahmedsajid](https://github.com/ahmedsajid)

#### Graphs
* Add missing graph definition for pf_matches (pfSense firewall) ([#13507](https://github.com/librenms/librenms/pull/13507)) - [robje](https://github.com/robje)

#### Snmp Traps
* SNMP Trap handler: UpsTrapOnBattery ([#13482](https://github.com/librenms/librenms/pull/13482)) - [TheGreatDoc](https://github.com/TheGreatDoc)

#### Applications
* Doc - MySQL Application ([#13495](https://github.com/librenms/librenms/pull/13495)) - [Deltawings](https://github.com/Deltawings)

#### Api
* Add get_ports_by_group API function ([#13361](https://github.com/librenms/librenms/pull/13361)) - [nmanzi](https://github.com/nmanzi)

#### Discovery
* Fix Dispatcher service not discovering poller groups ([#13377](https://github.com/librenms/librenms/pull/13377)) - [murrant](https://github.com/murrant)
* Modified SQL delete statement for vrf is null ([#13199](https://github.com/librenms/librenms/pull/13199)) - [kterobinson](https://github.com/kterobinson)

#### Polling
* Added TOS support for fping ([#13496](https://github.com/librenms/librenms/pull/13496)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix SnmpQuery mibdir from os group ([#13475](https://github.com/librenms/librenms/pull/13475)) - [murrant](https://github.com/murrant)
* Fix application and storage query errors ([#13417](https://github.com/librenms/librenms/pull/13417)) - [murrant](https://github.com/murrant)
* Fix uptime polling event ([#13388](https://github.com/librenms/librenms/pull/13388)) - [murrant](https://github.com/murrant)
* Keep stats for snmptranslate ([#13379](https://github.com/librenms/librenms/pull/13379)) - [murrant](https://github.com/murrant)
* Rewrite netstats polling ([#13368](https://github.com/librenms/librenms/pull/13368)) - [murrant](https://github.com/murrant)
* Run Alert Rules on Service status change. ([#13348](https://github.com/librenms/librenms/pull/13348)) - [thford89](https://github.com/thford89)

#### Rancid
* Show single quotes correcting for device configs ([#13360](https://github.com/librenms/librenms/pull/13360)) - [apokryphal](https://github.com/apokryphal)

#### Oxidized
* Syslog hook examples and documentation for Procurve devices ([#13397](https://github.com/librenms/librenms/pull/13397)) - [nq5](https://github.com/nq5)

#### Bug
* Fix PyMySQL upstream dependency bug ([#13508](https://github.com/librenms/librenms/pull/13508)) - [murrant](https://github.com/murrant)
* Fix net-snmp unformatted strings ([#13486](https://github.com/librenms/librenms/pull/13486)) - [murrant](https://github.com/murrant)
* [bug] Fix & extend MAC OUI table updates ([#13479](https://github.com/librenms/librenms/pull/13479)) - [PipoCanaja](https://github.com/PipoCanaja)
* Attempt to fix dispatcher stats thread exception ([#13478](https://github.com/librenms/librenms/pull/13478)) - [murrant](https://github.com/murrant)
* PHP8, correct multiplication in packages application ([#13462](https://github.com/librenms/librenms/pull/13462)) - [arrmo](https://github.com/arrmo)
* Don't use proxy for localhost (Oxidized and Prometheus) ([#13450](https://github.com/librenms/librenms/pull/13450)) - [murrant](https://github.com/murrant)
* Better handling of some alerting errors ([#13446](https://github.com/librenms/librenms/pull/13446)) - [murrant](https://github.com/murrant)
* Fix PHP8 error in sensor unit conversion ([#13433](https://github.com/librenms/librenms/pull/13433)) - [arrmo](https://github.com/arrmo)
* PHP8 Unit Conversion, ups-nut ([#13432](https://github.com/librenms/librenms/pull/13432)) - [arrmo](https://github.com/arrmo)
* Services and ping not polling default groups ([#13403](https://github.com/librenms/librenms/pull/13403)) - [murrant](https://github.com/murrant)
* Increase length of devices_attribs.attrib_type column ([#13395](https://github.com/librenms/librenms/pull/13395)) - [mjbnz](https://github.com/mjbnz)
* Bug - Fix missing uptime in fillable (Device Model) ([#13387](https://github.com/librenms/librenms/pull/13387)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fix regression from #12998 ([#13385](https://github.com/librenms/librenms/pull/13385)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Refactor
* Change how options are handled for SnmpQuery ([#13488](https://github.com/librenms/librenms/pull/13488)) - [murrant](https://github.com/murrant)
* Rename concrete SnmpQuery class to avoid confusion ([#13412](https://github.com/librenms/librenms/pull/13412)) - [murrant](https://github.com/murrant)
* Fully convert core to a modern module ([#13347](https://github.com/librenms/librenms/pull/13347)) - [murrant](https://github.com/murrant)
* New plugin system based on Laravel Package Development ([#12998](https://github.com/librenms/librenms/pull/12998)) - [mpikzink](https://github.com/mpikzink)

#### Cleanup
* Fixes for misc unset variables ([#13421](https://github.com/librenms/librenms/pull/13421)) - [murrant](https://github.com/murrant)
* Remove unused snom files ([#13369](https://github.com/librenms/librenms/pull/13369)) - [murrant](https://github.com/murrant)
* Fix config fetch disrupted by stderr ([#13362](https://github.com/librenms/librenms/pull/13362)) - [deajan](https://github.com/deajan)
* Use PHPStan level 6 ([#13308](https://github.com/librenms/librenms/pull/13308)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Documentation
* Fix typo in filepath for unit-tests to work ([#13440](https://github.com/librenms/librenms/pull/13440)) - [Nocturr](https://github.com/Nocturr)
* Debian 11 Install Docs ([#13430](https://github.com/librenms/librenms/pull/13430)) - [SourceDoctor](https://github.com/SourceDoctor)
* Don't suggest running validate.php as root ([#13378](https://github.com/librenms/librenms/pull/13378)) - [murrant](https://github.com/murrant)

#### Tests
* Fix IPV6 test ([#13468](https://github.com/librenms/librenms/pull/13468)) - [Jellyfrog](https://github.com/Jellyfrog)
* Ability to save cipsec-tunnels test data ([#13463](https://github.com/librenms/librenms/pull/13463)) - [murrant](https://github.com/murrant)
* Run phpstan locally with `lnms dev:check lint` ([#13458](https://github.com/librenms/librenms/pull/13458)) - [murrant](https://github.com/murrant)
* Dusk: improve speed and safety ([#13370](https://github.com/librenms/librenms/pull/13370)) - [murrant](https://github.com/murrant)

#### Dependencies
* Bump psutil=\>5.6.0 to satifsy command_runner ([#13501](https://github.com/librenms/librenms/pull/13501)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update to Larastan 1.0 ([#13466](https://github.com/librenms/librenms/pull/13466)) - [Jellyfrog](https://github.com/Jellyfrog)
* Update PHP dependencies ([#13396](https://github.com/librenms/librenms/pull/13396)) - [murrant](https://github.com/murrant)
* Install new python dependencies during daily maintenance ([#13186](https://github.com/librenms/librenms/pull/13186)) - [deajan](https://github.com/deajan)


## 21.10.0
*(2021-10-16)*

A big thank you to the following 21 contributors this last month:

  - [murrant](https://github.com/murrant) (40)
  - [SourceDoctor](https://github.com/SourceDoctor) (9)
  - [Jellyfrog](https://github.com/Jellyfrog) (5)
  - [loopodoopo](https://github.com/loopodoopo) (3)
  - [Cupidazul](https://github.com/Cupidazul) (3)
  - [maxnz](https://github.com/maxnz) (3)
  - [mpikzink](https://github.com/mpikzink) (3)
  - [ottorei](https://github.com/ottorei) (2)
  - [gs-kamnas](https://github.com/gs-kamnas) (2)
  - [topranks](https://github.com/topranks) (2)
  - [mctaguma](https://github.com/mctaguma) (1)
  - [DanielMuller-TN](https://github.com/DanielMuller-TN) (1)
  - [hjcday](https://github.com/hjcday) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [calvinthefreak](https://github.com/calvinthefreak) (1)
  - [si458](https://github.com/si458) (1)
  - [Laplacence](https://github.com/Laplacence) (1)
  - [peelman](https://github.com/peelman) (1)
  - [noaheroufus](https://github.com/noaheroufus) (1)
  - [deajan](https://github.com/deajan) (1)
  - [lutfisan](https://github.com/lutfisan) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [murrant](https://github.com/murrant) (36)
  - [Jellyfrog](https://github.com/Jellyfrog) (29)
  - [SourceDoctor](https://github.com/SourceDoctor) (7)
  - [ottorei](https://github.com/ottorei) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)

#### Feature
* New SNMP code and lnms snmp:fetch command ([#13303](https://github.com/librenms/librenms/pull/13303)) - [murrant](https://github.com/murrant)
* Add eventlog on_create device version 2 w/deps ([#13302](https://github.com/librenms/librenms/pull/13302)) - [Cupidazul](https://github.com/Cupidazul)
* Push Notifications (Mobile and PC) ([#13277](https://github.com/librenms/librenms/pull/13277)) - [murrant](https://github.com/murrant)
* Modified Prometheus extension to support adding a prefix to metric names ([#13272](https://github.com/librenms/librenms/pull/13272)) - [topranks](https://github.com/topranks)
* Config seeder ([#13259](https://github.com/librenms/librenms/pull/13259)) - [murrant](https://github.com/murrant)
* Infer character encoding for ifAlias and sysLocation ([#13248](https://github.com/librenms/librenms/pull/13248)) - [murrant](https://github.com/murrant)
* Log count of logged in users in database from HOST-RESOURCES-MIB ([#13137](https://github.com/librenms/librenms/pull/13137)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Security
* Use the same error message for different kind of authentiction errors ([#13306](https://github.com/librenms/librenms/pull/13306)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Device
* Added support for Teltonika RUTX series routers ([#13350](https://github.com/librenms/librenms/pull/13350)) - [hjcday](https://github.com/hjcday)
* Ericsson TN - Wireless MSE ([#13328](https://github.com/librenms/librenms/pull/13328)) - [loopodoopo](https://github.com/loopodoopo)
* Ciena 6500 Optics sensors ([#13324](https://github.com/librenms/librenms/pull/13324)) - [loopodoopo](https://github.com/loopodoopo)
* Ericsson Traffic Node support ([#13299](https://github.com/librenms/librenms/pull/13299)) - [loopodoopo](https://github.com/loopodoopo)
* Procurve E model prefix fix ([#13261](https://github.com/librenms/librenms/pull/13261)) - [maxnz](https://github.com/maxnz)
* Fix axis cam discovery ([#13258](https://github.com/librenms/librenms/pull/13258)) - [maxnz](https://github.com/maxnz)
* Wireless and GSM Basic Monitoring ([#13255](https://github.com/librenms/librenms/pull/13255)) - [Cupidazul](https://github.com/Cupidazul)
* Added SAF Integra-X OS Support ([#13236](https://github.com/librenms/librenms/pull/13236)) - [noaheroufus](https://github.com/noaheroufus)
* Arubaos cx hardware ([#13045](https://github.com/librenms/librenms/pull/13045)) - [maxnz](https://github.com/maxnz)
* MegaRaid controller in Linux (Broadcom/LSI) ([#12999](https://github.com/librenms/librenms/pull/12999)) - [mpikzink](https://github.com/mpikzink)
* Add Ericsson IPOS router support ([#12625](https://github.com/librenms/librenms/pull/12625)) - [lutfisan](https://github.com/lutfisan)

#### Webui
* Alert detail display fix ([#13335](https://github.com/librenms/librenms/pull/13335)) - [SourceDoctor](https://github.com/SourceDoctor)
* Show Detail by default Option on Alert Widget ([#13309](https://github.com/librenms/librenms/pull/13309)) - [SourceDoctor](https://github.com/SourceDoctor)
* Fix various port links ([#13296](https://github.com/librenms/librenms/pull/13296)) - [murrant](https://github.com/murrant)
* Add ORDER BY to the ports query when showing the ports list on device… ([#13276](https://github.com/librenms/librenms/pull/13276)) - [peelman](https://github.com/peelman)
* Use local timezone for outages pages ([#13274](https://github.com/librenms/librenms/pull/13274)) - [ottorei](https://github.com/ottorei)
* Remove device dark mode detection ([#13273](https://github.com/librenms/librenms/pull/13273)) - [murrant](https://github.com/murrant)
* Linkable graph component ([#13263](https://github.com/librenms/librenms/pull/13263)) - [murrant](https://github.com/murrant)
* Improve tailwind dark theme colors ([#13262](https://github.com/librenms/librenms/pull/13262)) - [murrant](https://github.com/murrant)
* Workaround for dashboard widgets showing over popups. ([#13257](https://github.com/librenms/librenms/pull/13257)) - [murrant](https://github.com/murrant)

#### Alerting
* Fix alert transport api with POST method ([#13288](https://github.com/librenms/librenms/pull/13288)) - [Laplacence](https://github.com/Laplacence)

#### Graphs
* Fix graphs showing bps instead of pps ([#13266](https://github.com/librenms/librenms/pull/13266)) - [Cupidazul](https://github.com/Cupidazul)

#### Snmp Traps
* HP Fault Traps ([#13254](https://github.com/librenms/librenms/pull/13254)) - [mpikzink](https://github.com/mpikzink)
* Veeam backup SNMP Traps ([#13170](https://github.com/librenms/librenms/pull/13170)) - [mpikzink](https://github.com/mpikzink)

#### Api
* Added conditional check for rules parameter on add_device_group ([#13353](https://github.com/librenms/librenms/pull/13353)) - [DanielMuller-TN](https://github.com/DanielMuller-TN)
* API add_device: respond with more device array ([#13251](https://github.com/librenms/librenms/pull/13251)) - [SourceDoctor](https://github.com/SourceDoctor)
* API Call to assign/remove a Portgroup to Ports ([#13245](https://github.com/librenms/librenms/pull/13245)) - [SourceDoctor](https://github.com/SourceDoctor)
* API Call to set instant Maintenance mode ([#13237](https://github.com/librenms/librenms/pull/13237)) - [SourceDoctor](https://github.com/SourceDoctor)
* API port search by arbitrary field ([#13231](https://github.com/librenms/librenms/pull/13231)) - [SourceDoctor](https://github.com/SourceDoctor)

#### Settings
* Automatically set rrdtool_version once ([#13327](https://github.com/librenms/librenms/pull/13327)) - [murrant](https://github.com/murrant)

#### Discovery
* Quick fix for route discovery on PHP8 ([#13284](https://github.com/librenms/librenms/pull/13284)) - [murrant](https://github.com/murrant)

#### Polling
* Dispatcher bugfix queues not being disabled properly ([#13364](https://github.com/librenms/librenms/pull/13364)) - [murrant](https://github.com/murrant)
* Fix for cimc polling on PHP8 ([#13357](https://github.com/librenms/librenms/pull/13357)) - [murrant](https://github.com/murrant)
* Dispatch Service: Don't stop dispatching if master moves to a node with a queue disabled ([#13355](https://github.com/librenms/librenms/pull/13355)) - [murrant](https://github.com/murrant)
* Fix device query when last_polled_timetaken is null ([#13331](https://github.com/librenms/librenms/pull/13331)) - [murrant](https://github.com/murrant)
* Restore accidentally removed code ([#13330](https://github.com/librenms/librenms/pull/13330)) - [murrant](https://github.com/murrant)
* Allow non-snmp modules to run when snmp disabled ([#13321](https://github.com/librenms/librenms/pull/13321)) - [murrant](https://github.com/murrant)
* Fix python config fetch disrupted by stderr output ([#13295](https://github.com/librenms/librenms/pull/13295)) - [murrant](https://github.com/murrant)
* Fix poller wrapper error ([#13290](https://github.com/librenms/librenms/pull/13290)) - [murrant](https://github.com/murrant)

#### Oxidized
* Added OneOS map for Oxidized ([#13313](https://github.com/librenms/librenms/pull/13313)) - [calvinthefreak](https://github.com/calvinthefreak)

#### Authentication
* Improvements to SSO Authorization and logout handling ([#13311](https://github.com/librenms/librenms/pull/13311)) - [gs-kamnas](https://github.com/gs-kamnas)

#### Bug
* Fix poller groups reverting when setting via the Web UI. ([#13363](https://github.com/librenms/librenms/pull/13363)) - [murrant](https://github.com/murrant)
* Rename config var auth_redirect_handler -\> auth_logout_handler ([#13329](https://github.com/librenms/librenms/pull/13329)) - [gs-kamnas](https://github.com/gs-kamnas)
* HrSystem Columns have to be optional ([#13316](https://github.com/librenms/librenms/pull/13316)) - [SourceDoctor](https://github.com/SourceDoctor)
* Hrsystem write fix ([#13314](https://github.com/librenms/librenms/pull/13314)) - [SourceDoctor](https://github.com/SourceDoctor)
* Check if vlan-\>port exists ([#13305](https://github.com/librenms/librenms/pull/13305)) - [Jellyfrog](https://github.com/Jellyfrog)
* Fix html.device.links validation ([#13269](https://github.com/librenms/librenms/pull/13269)) - [murrant](https://github.com/murrant)
* Fix lnms some commands throwing errors ([#13265](https://github.com/librenms/librenms/pull/13265)) - [murrant](https://github.com/murrant)

#### Refactor
* SnmpQuery updates and more tests ([#13359](https://github.com/librenms/librenms/pull/13359)) - [murrant](https://github.com/murrant)
* Remove load_os and load_discovery functions ([#13345](https://github.com/librenms/librenms/pull/13345)) - [murrant](https://github.com/murrant)
* Rename NetSnmp to SnmpQuery ([#13344](https://github.com/librenms/librenms/pull/13344)) - [murrant](https://github.com/murrant)
* Use Measurements for all statistic collection ([#13333](https://github.com/librenms/librenms/pull/13333)) - [murrant](https://github.com/murrant)
* Use built in trusted proxy functionality ([#13318](https://github.com/librenms/librenms/pull/13318)) - [murrant](https://github.com/murrant)
* Connectivity Helper to check and record device reachability ([#13315](https://github.com/librenms/librenms/pull/13315)) - [murrant](https://github.com/murrant)
* Cleanup config.php.default ([#13297](https://github.com/librenms/librenms/pull/13297)) - [murrant](https://github.com/murrant)
* SNMP Capabilities ([#13289](https://github.com/librenms/librenms/pull/13289)) - [murrant](https://github.com/murrant)
* Cleanup device type override code ([#13256](https://github.com/librenms/librenms/pull/13256)) - [murrant](https://github.com/murrant)
* Full Python code fusion / refactor and hardening 2nd edition ([#13188](https://github.com/librenms/librenms/pull/13188)) - [deajan](https://github.com/deajan)
* Convert all ports backend to Laravel style ajax table ([#13184](https://github.com/librenms/librenms/pull/13184)) - [murrant](https://github.com/murrant)

#### Documentation
* Change "move" to "migrate" to make it easier to find ([#13365](https://github.com/librenms/librenms/pull/13365)) - [murrant](https://github.com/murrant)
* Updated link to Dan Brown's migration scripts ([#13354](https://github.com/librenms/librenms/pull/13354)) - [mctaguma](https://github.com/mctaguma)
* Edit existing install url ([#13342](https://github.com/librenms/librenms/pull/13342)) - [murrant](https://github.com/murrant)
* Oxidized doc update, links and ignore groups ([#13341](https://github.com/librenms/librenms/pull/13341)) - [murrant](https://github.com/murrant)
* Update docs: Update Dispatcher service documentation ([#13339](https://github.com/librenms/librenms/pull/13339)) - [ottorei](https://github.com/ottorei)
* Send security researchers to Discord ([#13319](https://github.com/librenms/librenms/pull/13319)) - [murrant](https://github.com/murrant)
* Fix to puppet snmp extend formatting ([#13312](https://github.com/librenms/librenms/pull/13312)) - [si458](https://github.com/si458)
* Update formatting of Prometheus extension doc ([#13291](https://github.com/librenms/librenms/pull/13291)) - [topranks](https://github.com/topranks)

#### Tests
* Disallow usage of deprecated functions ([#13267](https://github.com/librenms/librenms/pull/13267)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Dependencies
* Bump composer/composer from 2.1.8 to 2.1.9 ([#13336](https://github.com/librenms/librenms/pull/13336)) - [dependabot](https://github.com/apps/dependabot)
* Update dependencies ([#13310](https://github.com/librenms/librenms/pull/13310)) - [Jellyfrog](https://github.com/Jellyfrog)


## 21.9.0
*(2021-09-16)*

A big thank you to the following 20 contributors this last month:

  - [murrant](https://github.com/murrant) (29)
  - [Jellyfrog](https://github.com/Jellyfrog) (5)
  - [ottorei](https://github.com/ottorei) (3)
  - [SourceDoctor](https://github.com/SourceDoctor) (3)
  - [Galileo77](https://github.com/Galileo77) (2)
  - [paulierco](https://github.com/paulierco) (2)
  - [Fehler12](https://github.com/Fehler12) (2)
  - [Negatifff](https://github.com/Negatifff) (2)
  - [deajan](https://github.com/deajan) (2)
  - [mpikzink](https://github.com/mpikzink) (2)
  - [vakartel](https://github.com/vakartel) (2)
  - [efelon](https://github.com/efelon) (1)
  - [pimvanpelt](https://github.com/pimvanpelt) (1)
  - [kimhaak](https://github.com/kimhaak) (1)
  - [kevinwallace](https://github.com/kevinwallace) (1)
  - [noaheroufus](https://github.com/noaheroufus) (1)
  - [si458](https://github.com/si458) (1)
  - [mzacchi](https://github.com/mzacchi) (1)
  - [niddey](https://github.com/niddey) (1)
  - [PipoCanaja](https://github.com/PipoCanaja) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (21)
  - [murrant](https://github.com/murrant) (19)
  - [ottorei](https://github.com/ottorei) (3)
  - [SourceDoctor](https://github.com/SourceDoctor) (2)
  - [PipoCanaja](https://github.com/PipoCanaja) (2)
  - [mpikzink](https://github.com/mpikzink) (1)

#### Feature
* API alert transport can include Options variables in the Body for POST requests ([#13167](https://github.com/librenms/librenms/pull/13167)) - [mzacchi](https://github.com/mzacchi)
* Show port speed on port graphs, optionally set scale ([#11858](https://github.com/librenms/librenms/pull/11858)) - [murrant](https://github.com/murrant)

#### Device
* Fix Dell server sensors ([#13247](https://github.com/librenms/librenms/pull/13247)) - [murrant](https://github.com/murrant)
* Add mouseover mini graphs that makes sense for Liebert PDU ([#13246](https://github.com/librenms/librenms/pull/13246)) - [Galileo77](https://github.com/Galileo77)
* Add ifName to osag os ([#13243](https://github.com/librenms/librenms/pull/13243)) - [paulierco](https://github.com/paulierco)
* IfName  to mcafeewebgateway ([#13242](https://github.com/librenms/librenms/pull/13242)) - [paulierco](https://github.com/paulierco)
* Add VPP logo ([#13230](https://github.com/librenms/librenms/pull/13230)) - [pimvanpelt](https://github.com/pimvanpelt)
* Add Support for USW-Flex ([#13229](https://github.com/librenms/librenms/pull/13229)) - [Fehler12](https://github.com/Fehler12)
* Added Cisco CBS 250 Support ([#13228](https://github.com/librenms/librenms/pull/13228)) - [Fehler12](https://github.com/Fehler12)
* Update geist-watchdog.yaml ([#13223](https://github.com/librenms/librenms/pull/13223)) - [Galileo77](https://github.com/Galileo77)
* Add skip_values to Liebert capacity sensor definitions ([#13200](https://github.com/librenms/librenms/pull/13200)) - [kevinwallace](https://github.com/kevinwallace)
* Procurve add SysDescr Regex ([#13196](https://github.com/librenms/librenms/pull/13196)) - [mpikzink](https://github.com/mpikzink)
* Updated OS Support: Dragonwave Horizon ([#13193](https://github.com/librenms/librenms/pull/13193)) - [noaheroufus](https://github.com/noaheroufus)
* ZTE ZXR10 define discovery for mempool and processor ([#13192](https://github.com/librenms/librenms/pull/13192)) - [vakartel](https://github.com/vakartel)
* Dell Network Virtual Link Trunk Status ([#13162](https://github.com/librenms/librenms/pull/13162)) - [mpikzink](https://github.com/mpikzink)

#### Webui
* Update to fix table row color and hover color for dark.css ([#13244](https://github.com/librenms/librenms/pull/13244)) - [efelon](https://github.com/efelon)
* Fix port minigraph layout ([#13240](https://github.com/librenms/librenms/pull/13240)) - [murrant](https://github.com/murrant)
* Sort dashboard entries alphabetically ([#13238](https://github.com/librenms/librenms/pull/13238)) - [ottorei](https://github.com/ottorei)
* Fix graph row columns ([#13232](https://github.com/librenms/librenms/pull/13232)) - [murrant](https://github.com/murrant)
* Changed map functionality in device overview ([#13225](https://github.com/librenms/librenms/pull/13225)) - [kimhaak](https://github.com/kimhaak)
* New Blade Components: x-device-link, x-port-link, x-graph-row, x-popup ([#13197](https://github.com/librenms/librenms/pull/13197)) - [murrant](https://github.com/murrant)
* Add serial search in ajax search process ([#13185](https://github.com/librenms/librenms/pull/13185)) - [Negatifff](https://github.com/Negatifff)
* Allow device actions to appear in device list and improve docs ([#13177](https://github.com/librenms/librenms/pull/13177)) - [murrant](https://github.com/murrant)
* Show count of Ports in PortGroup display ([#13164](https://github.com/librenms/librenms/pull/13164)) - [SourceDoctor](https://github.com/SourceDoctor)
* Change automatic widget updates to use bootgrid when possible ([#13159](https://github.com/librenms/librenms/pull/13159)) - [niddey](https://github.com/niddey)

#### Alerting
* Fix PagerDuty transport's group field ([#13235](https://github.com/librenms/librenms/pull/13235)) - [ottorei](https://github.com/ottorei)
* Format port speed changes in the event log ([#13174](https://github.com/librenms/librenms/pull/13174)) - [murrant](https://github.com/murrant)

#### Graphs
* Change default graph view to zoom in on traffic. ([#13173](https://github.com/librenms/librenms/pull/13173)) - [murrant](https://github.com/murrant)

#### Settings
* Dynamic Select setting ([#13179](https://github.com/librenms/librenms/pull/13179)) - [murrant](https://github.com/murrant)
* Increase config value length limit ([#13178](https://github.com/librenms/librenms/pull/13178)) - [murrant](https://github.com/murrant)
* Default port group in Settings ([#13175](https://github.com/librenms/librenms/pull/13175)) - [SourceDoctor](https://github.com/SourceDoctor)
* Default port_group for new ports ([#13166](https://github.com/librenms/librenms/pull/13166)) - [SourceDoctor](https://github.com/SourceDoctor)
* Add support for description texts in Settings page ([#13104](https://github.com/librenms/librenms/pull/13104)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Discovery
* Mark snmp disabled devices as skipped ([#13202](https://github.com/librenms/librenms/pull/13202)) - [murrant](https://github.com/murrant)
* Allow more compatibility in STP port discovery/polling ([#13109](https://github.com/librenms/librenms/pull/13109)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Polling
* Fix snmp_bulk setting not being applied ([#13209](https://github.com/librenms/librenms/pull/13209)) - [murrant](https://github.com/murrant)
* Do not poll passive ISIS-circuits ([#13168](https://github.com/librenms/librenms/pull/13168)) - [ottorei](https://github.com/ottorei)
* Common contexts for polling ([#13158](https://github.com/librenms/librenms/pull/13158)) - [murrant](https://github.com/murrant)
* Rewrite ISIS Adjacency discovery/polling ([#13155](https://github.com/librenms/librenms/pull/13155)) - [murrant](https://github.com/murrant)

#### Oxidized
* Add sysobjectid and hardware fields into oxidized maps ([#13221](https://github.com/librenms/librenms/pull/13221)) - [Negatifff](https://github.com/Negatifff)

#### Bug
* Fix mempool unit display ([#13241](https://github.com/librenms/librenms/pull/13241)) - [murrant](https://github.com/murrant)
* Select dynamic fixes ([#13187](https://github.com/librenms/librenms/pull/13187)) - [murrant](https://github.com/murrant)
* Validate schema in utc ([#13182](https://github.com/librenms/librenms/pull/13182)) - [murrant](https://github.com/murrant)
* Fix DB Inconsistent friendly error message ([#13163](https://github.com/librenms/librenms/pull/13163)) - [murrant](https://github.com/murrant)

#### Refactor
* Update configuration docs to use lnms config:set ([#13157](https://github.com/librenms/librenms/pull/13157)) - [murrant](https://github.com/murrant)
* Rename nobulk -\> snmp_bulk ([#13098](https://github.com/librenms/librenms/pull/13098)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Documentation
* Add security context service ([#13218](https://github.com/librenms/librenms/pull/13218)) - [deajan](https://github.com/deajan)
* Fix numbering in application docs ([#13183](https://github.com/librenms/librenms/pull/13183)) - [si458](https://github.com/si458)
* Docs link to webui settings in user's install ([#13176](https://github.com/librenms/librenms/pull/13176)) - [murrant](https://github.com/murrant)

#### Misc
* Increase RestartSec time to a reasonable value ([#13217](https://github.com/librenms/librenms/pull/13217)) - [deajan](https://github.com/deajan)
* Validate APP_KEY ([#13171](https://github.com/librenms/librenms/pull/13171)) - [murrant](https://github.com/murrant)


## 21.8.0
*(2021-08-22)*

A big thank you to the following 40 contributors this last month:

  - [PipoCanaja](https://github.com/PipoCanaja) (12)
  - [murrant](https://github.com/murrant) (10)
  - [Jellyfrog](https://github.com/Jellyfrog) (8)
  - [paulierco](https://github.com/paulierco) (5)
  - [mpikzink](https://github.com/mpikzink) (5)
  - [ottorei](https://github.com/ottorei) (3)
  - [fbourqui](https://github.com/fbourqui) (2)
  - [facuxt](https://github.com/facuxt) (2)
  - [geg347](https://github.com/geg347) (2)
  - [dennypage](https://github.com/dennypage) (2)
  - [opalivan](https://github.com/opalivan) (2)
  - [keryazmi](https://github.com/keryazmi) (2)
  - [wkamlun](https://github.com/wkamlun) (2)
  - [si458](https://github.com/si458) (2)
  - [martinberg](https://github.com/martinberg) (2)
  - [vakartel](https://github.com/vakartel) (1)
  - [SanderBlom](https://github.com/SanderBlom) (1)
  - [SourceDoctor](https://github.com/SourceDoctor) (1)
  - [VirTechSystems](https://github.com/VirTechSystems) (1)
  - [Talkabout](https://github.com/Talkabout) (1)
  - [hannut](https://github.com/hannut) (1)
  - [kevinwallace](https://github.com/kevinwallace) (1)
  - [jasoncheng7115](https://github.com/jasoncheng7115) (1)
  - [arjitc](https://github.com/arjitc) (1)
  - [igorek24](https://github.com/igorek24) (1)
  - [mtoupsUNO](https://github.com/mtoupsUNO) (1)
  - [Laplacence](https://github.com/Laplacence) (1)
  - [tcwarn](https://github.com/tcwarn) (1)
  - [deajan](https://github.com/deajan) (1)
  - [Npeca75](https://github.com/Npeca75) (1)
  - [Negatifff](https://github.com/Negatifff) (1)
  - [adamus1red](https://github.com/adamus1red) (1)
  - [rhinoau](https://github.com/rhinoau) (1)
  - [arrmo](https://github.com/arrmo) (1)
  - [e-caille](https://github.com/e-caille) (1)
  - [Sea-n](https://github.com/Sea-n) (1)
  - [saschareichert](https://github.com/saschareichert) (1)
  - [bennetgallein](https://github.com/bennetgallein) (1)
  - [loopodoopo](https://github.com/loopodoopo) (1)
  - [tikitaru](https://github.com/tikitaru) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (37)
  - [murrant](https://github.com/murrant) (28)
  - [PipoCanaja](https://github.com/PipoCanaja) (20)
  - [SourceDoctor](https://github.com/SourceDoctor) (2)

#### Feature
* Lnms config:set ability to set os settings ([#13151](https://github.com/librenms/librenms/pull/13151)) - [murrant](https://github.com/murrant)
* Detect sending non-html emails as html ([#13114](https://github.com/librenms/librenms/pull/13114)) - [murrant](https://github.com/murrant)
* Add peak in and out ([#13006](https://github.com/librenms/librenms/pull/13006)) - [bennetgallein](https://github.com/bennetgallein)

#### Device
* Add OSAG new OS ([#13156](https://github.com/librenms/librenms/pull/13156)) - [paulierco](https://github.com/paulierco)
* More specific grandstream-ht detection ([#13152](https://github.com/librenms/librenms/pull/13152)) - [murrant](https://github.com/murrant)
* Added voltage, cell states and wireless data for cell interface (GEMDS OS). ([#13142](https://github.com/librenms/librenms/pull/13142)) - [SanderBlom](https://github.com/SanderBlom)
* Opnsense detection ([#13097](https://github.com/librenms/librenms/pull/13097)) - [mpikzink](https://github.com/mpikzink)
* Added OID for Extreme switch X350-48t ([#13096](https://github.com/librenms/librenms/pull/13096)) - [tcwarn](https://github.com/tcwarn)
* Add Janitza power consumed ([#13095](https://github.com/librenms/librenms/pull/13095)) - [mpikzink](https://github.com/mpikzink)
* Added/fixed LLDP discovery ([#13082](https://github.com/librenms/librenms/pull/13082)) - [Npeca75](https://github.com/Npeca75)
* Allow stack index other than '1.' for CiscoSB ([#13078](https://github.com/librenms/librenms/pull/13078)) - [dennypage](https://github.com/dennypage)
* Add logo for Scientific Linux ([#13075](https://github.com/librenms/librenms/pull/13075)) - [mpikzink](https://github.com/mpikzink)
* Fix Cisco SLAs garbage entries ([#13068](https://github.com/librenms/librenms/pull/13068)) - [murrant](https://github.com/murrant)
* Cisco ISE version, HW, SW and test-data ([#13062](https://github.com/librenms/librenms/pull/13062)) - [PipoCanaja](https://github.com/PipoCanaja)
* Fixed Packetlight EDFA sensors and add test-data ([#13060](https://github.com/librenms/librenms/pull/13060)) - [opalivan](https://github.com/opalivan)
* Alcatel-Lucent AOS7 bgp fix2 ([#13059](https://github.com/librenms/librenms/pull/13059)) - [paulierco](https://github.com/paulierco)
* Add logo for Linux Mint ([#13055](https://github.com/librenms/librenms/pull/13055)) - [arrmo](https://github.com/arrmo)
* Alcatel-Lucent Aos6 ignore second power supply ([#13054](https://github.com/librenms/librenms/pull/13054)) - [paulierco](https://github.com/paulierco)
* Alcatel-Lucent Aos7 increase fan threshold ([#13053](https://github.com/librenms/librenms/pull/13053)) - [paulierco](https://github.com/paulierco)
* Use non numeric snmpwalk for nxos fan trays ([#13048](https://github.com/librenms/librenms/pull/13048)) - [e-caille](https://github.com/e-caille)
* Alcatel-Lucent Aos7 bgp fix ([#13047](https://github.com/librenms/librenms/pull/13047)) - [paulierco](https://github.com/paulierco)
* Add number of connected wireless client and number of connected FortiAP ([#13037](https://github.com/librenms/librenms/pull/13037)) - [wkamlun](https://github.com/wkamlun)
* Update Sensors for ADVA FSP150CC + discovery fix ([#13020](https://github.com/librenms/librenms/pull/13020)) - [keryazmi](https://github.com/keryazmi)
* Improve dell-compellent detection for newer firmwares ([#13019](https://github.com/librenms/librenms/pull/13019)) - [saschareichert](https://github.com/saschareichert)
* Poll current connections for F5 ltm ([#12968](https://github.com/librenms/librenms/pull/12968)) - [martinberg](https://github.com/martinberg)
* Ericsson 6600 series ([#12931](https://github.com/librenms/librenms/pull/12931)) - [loopodoopo](https://github.com/loopodoopo)
* Fix WUT ThermoHygro with new hardware revisions ([#12913](https://github.com/librenms/librenms/pull/12913)) - [mpikzink](https://github.com/mpikzink)
* Fix polling current on ICT2000DB-12IRC ([#12529](https://github.com/librenms/librenms/pull/12529)) - [tikitaru](https://github.com/tikitaru)

#### Webui
* Cleanup Port hover minigraph description when using "Interface Description Parsing" ([#13143](https://github.com/librenms/librenms/pull/13143)) - [fbourqui](https://github.com/fbourqui)
* Dark mode improvements ([#13141](https://github.com/librenms/librenms/pull/13141)) - [facuxt](https://github.com/facuxt)
* Improvements to dark theme. ([#13139](https://github.com/librenms/librenms/pull/13139)) - [facuxt](https://github.com/facuxt)
* Adjust App String Pi-hole to project name ([#13136](https://github.com/librenms/librenms/pull/13136)) - [SourceDoctor](https://github.com/SourceDoctor)
* Sort port selector dropdown ([#13135](https://github.com/librenms/librenms/pull/13135)) - [VirTechSystems](https://github.com/VirTechSystems)
* Priority filtering for syslog widget ([#13134](https://github.com/librenms/librenms/pull/13134)) - [Talkabout](https://github.com/Talkabout)
* Center new service window ([#13115](https://github.com/librenms/librenms/pull/13115)) - [arjitc](https://github.com/arjitc)
* Add urlencode for location link in device view ([#13076](https://github.com/librenms/librenms/pull/13076)) - [Negatifff](https://github.com/Negatifff)
* Add device_group to availability widget hyperlink ([#13061](https://github.com/librenms/librenms/pull/13061)) - [rhinoau](https://github.com/rhinoau)
* More sensor data on inventory page ([#13057](https://github.com/librenms/librenms/pull/13057)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Api
* Adding API route to set bgpPeerDescr field ([#13056](https://github.com/librenms/librenms/pull/13056)) - [geg347](https://github.com/geg347)
* API mac search ([#12964](https://github.com/librenms/librenms/pull/12964)) - [mpikzink](https://github.com/mpikzink)

#### Alerting
* Add EU Service Region to PagerDuty transport ([#13154](https://github.com/librenms/librenms/pull/13154)) - [ottorei](https://github.com/ottorei)
* Fix HTML encoded characters in Slack ([#13120](https://github.com/librenms/librenms/pull/13120)) - [geg347](https://github.com/geg347)
* Add SignalWire (Twilio alternative) alert transport support ([#13107](https://github.com/librenms/librenms/pull/13107)) - [igorek24](https://github.com/igorek24)

#### Discovery
* Full Python code fusion / refactor and hardening ([#13094](https://github.com/librenms/librenms/pull/13094)) - [deajan](https://github.com/deajan)
* Extend REGEX filtering option by sensor_class ([#13066](https://github.com/librenms/librenms/pull/13066)) - [opalivan](https://github.com/opalivan)

#### Oxidized
* Follow redirects when reloading Oxidized nodes list ([#13051](https://github.com/librenms/librenms/pull/13051)) - [martinberg](https://github.com/martinberg)

#### Bug
* Fix issue syslog_xlate containing dots ([#13148](https://github.com/librenms/librenms/pull/13148)) - [vakartel](https://github.com/vakartel)
* Enclose IPv6 literal in [brackets] for snmpget and unix-agent ([#13130](https://github.com/librenms/librenms/pull/13130)) - [kevinwallace](https://github.com/kevinwallace)
* Fix lnms scan, nets not detected ([#13129](https://github.com/librenms/librenms/pull/13129)) - [murrant](https://github.com/murrant)

#### Documentation
* Update docs for controlling modules ([#13147](https://github.com/librenms/librenms/pull/13147)) - [murrant](https://github.com/murrant)
* Update docs: Example for optional data on templates ([#13128](https://github.com/librenms/librenms/pull/13128)) - [ottorei](https://github.com/ottorei)
* Update FAQs for large groups ([#13110](https://github.com/librenms/librenms/pull/13110)) - [ottorei](https://github.com/ottorei)
* Document new optional ups-nut arg1 ([#13072](https://github.com/librenms/librenms/pull/13072)) - [adamus1red](https://github.com/adamus1red)
* Formatted applications docs for copy/paste ([#13049](https://github.com/librenms/librenms/pull/13049)) - [si458](https://github.com/si458)
* Update cleanup config document ([#13026](https://github.com/librenms/librenms/pull/13026)) - [Sea-n](https://github.com/Sea-n)

#### Translation
* Updated Traditional Chinese Translation ([#13116](https://github.com/librenms/librenms/pull/13116)) - [jasoncheng7115](https://github.com/jasoncheng7115)

#### Tests
* Run PHPStan with higher level for new files ([#13108](https://github.com/librenms/librenms/pull/13108)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Misc
* Added two indexes to 'syslog' table for performance ([#13105](https://github.com/librenms/librenms/pull/13105)) - [mtoupsUNO](https://github.com/mtoupsUNO)
* Remove timeouts for passthrough lnms commands ([#13080](https://github.com/librenms/librenms/pull/13080)) - [murrant](https://github.com/murrant)
* Add Laravel task scheduling ([#13074](https://github.com/librenms/librenms/pull/13074)) - [Jellyfrog](https://github.com/Jellyfrog)

#### Mibs
* MIB cleaning (misc again) ([#13103](https://github.com/librenms/librenms/pull/13103)) - [PipoCanaja](https://github.com/PipoCanaja)
* Added H3C / Comware MIB files ([#13102](https://github.com/librenms/librenms/pull/13102)) - [Laplacence](https://github.com/Laplacence)
* Update Cisco Small Business (mostly) MIBs ([#13099](https://github.com/librenms/librenms/pull/13099)) - [dennypage](https://github.com/dennypage)
* Update NET-SNMP mibs ([#13093](https://github.com/librenms/librenms/pull/13093)) - [Jellyfrog](https://github.com/Jellyfrog)
* MIB cleaning for Dell + Gandi ([#13089](https://github.com/librenms/librenms/pull/13089)) - [PipoCanaja](https://github.com/PipoCanaja)
* MIB cleaning for Panasonic (1 file) and Avtech (11 files) ([#13088](https://github.com/librenms/librenms/pull/13088)) - [PipoCanaja](https://github.com/PipoCanaja)
* MIB cleaning for OS "Junose" ([#13087](https://github.com/librenms/librenms/pull/13087)) - [PipoCanaja](https://github.com/PipoCanaja)
* MIB cleaning (Misc) ([#13086](https://github.com/librenms/librenms/pull/13086)) - [PipoCanaja](https://github.com/PipoCanaja)
* MIB cleaning for Junos ([#13085](https://github.com/librenms/librenms/pull/13085)) - [PipoCanaja](https://github.com/PipoCanaja)
* MIB cleaning for HP ([#13084](https://github.com/librenms/librenms/pull/13084)) - [PipoCanaja](https://github.com/PipoCanaja)
* MIB cleaning for equallogic ([#13083](https://github.com/librenms/librenms/pull/13083)) - [PipoCanaja](https://github.com/PipoCanaja)

#### Dependencies
* Bump super-linter ([#13073](https://github.com/librenms/librenms/pull/13073)) - [Jellyfrog](https://github.com/Jellyfrog)
* Bump larastan ([#13071](https://github.com/librenms/librenms/pull/13071)) - [Jellyfrog](https://github.com/Jellyfrog)


## 21.7.0
*(2021-07-16)*

A big thank you to the following 27 contributors this last month:

  - [murrant](https://github.com/murrant) (9)
  - [rhinoau](https://github.com/rhinoau) (8)
  - [PipoCanaja](https://github.com/PipoCanaja) (5)
  - [mpikzink](https://github.com/mpikzink) (4)
  - [mathieu-artic](https://github.com/mathieu-artic) (4)
  - [martinberg](https://github.com/martinberg) (3)
  - [Npeca75](https://github.com/Npeca75) (2)
  - [epiecs](https://github.com/epiecs) (2)
  - [Sea-n](https://github.com/Sea-n) (2)
  - [paulierco](https://github.com/paulierco) (2)
  - [djamp42](https://github.com/djamp42) (2)
  - [Jellyfrog](https://github.com/Jellyfrog) (1)
  - [geg347](https://github.com/geg347) (1)
  - [ziodona85](https://github.com/ziodona85) (1)
  - [keryazmi](https://github.com/keryazmi) (1)
  - [hanserasmus](https://github.com/hanserasmus) (1)
  - [edgetho007](https://github.com/edgetho007) (1)
  - [dagbdagb](https://github.com/dagbdagb) (1)
  - [jbronn](https://github.com/jbronn) (1)
  - [adamboutcher](https://github.com/adamboutcher) (1)
  - [VirTechSystems](https://github.com/VirTechSystems) (1)
  - [skoobasteeve](https://github.com/skoobasteeve) (1)
  - [dependabot](https://github.com/apps/dependabot) (1)
  - [si458](https://github.com/si458) (1)
  - [kdanev14](https://github.com/kdanev14) (1)
  - [cjsoftuk](https://github.com/cjsoftuk) (1)
  - [jezekus](https://github.com/jezekus) (1)

Thanks to maintainers and others that helped with pull requests this month:

  - [Jellyfrog](https://github.com/Jellyfrog) (32)
  - [murrant](https://github.com/murrant) (14)
  - [PipoCanaja](https://github.com/PipoCanaja) (13)

#### Feature
* Support multiple db servers ([#12963](https://github.com/librenms/librenms/pull/12963)) - [djamp42](https://github.com/djamp42)

#### Device
* Inital support for Eltex-MES switches ([#13036](https://github.com/librenms/librenms/pull/13036)) - [Npeca75](https://github.com/Npeca75)
* Add support for Ucopia Appliances ([#13031](https://github.com/librenms/librenms/pull/13031)) - [mathieu-artic](https://github.com/mathieu-artic)
* Fixing irrelevant values rpm packetloss ([#13010](https://github.com/librenms/librenms/pull/13010)) - [geg347](https://github.com/geg347)
* Added Vlan discovery on LAG ports, Jetstream OS ([#13007](https://github.com/librenms/librenms/pull/13007)) - [Npeca75](https://github.com/Npeca75)
* Add showtime start for OneAccess SDSL routers ([#13005](https://github.com/librenms/librenms/pull/13005)) - [mathieu-artic](https://github.com/mathieu-artic)
* Arista_mos-support ([#13003](https://github.com/librenms/librenms/pull/13003)) - [hanserasmus](https://github.com/hanserasmus)
* Add LTE/UMTS support for Oneaccess routers ([#13002](https://github.com/librenms/librenms/pull/13002)) - [mathieu-artic](https://github.com/mathieu-artic)
* Add Sonicwall OS 7 ([#12997](https://github.com/librenms/librenms/pull/12997)) - [edgetho007](https://github.com/edgetho007)
* Added support for the Rittal LCP DX Chiller ([#12995](https://github.com/librenms/librenms/pull/12995)) - [epiecs](https://github.com/epiecs)
* Eaton M2 EMP g2 ([#12994](https://github.com/librenms/librenms/pull/12994)) - [dagbdagb](https://github.com/dagbdagb)
* TP-Link Jetstream DDM and PoE Support ([#12990](https://github.com/librenms/librenms/pull/12990)) - [jbronn](https://github.com/jbronn)
* Alcatel-Lucent AOS6 to yaml ([#12982](https://github.com/librenms/librenms/pull/12982)) - [paulierco](https://github.com/paulierco)
* Added logo svg for Rocky Linux ([#12977](https://github.com/librenms/librenms/pull/12977)) - [skoobasteeve](https://github.com/skoobasteeve)
* Added VRP ICMP SLA (NQA in huawei naming) support ([#12973](https://github.com/librenms/librenms/pull/12973)) - [PipoCanaja](https://github.com/PipoCanaja)
* EUROstor RAID ([#12969](https://github.com/librenms/librenms/pull/12969)) - [mpikzink](https://github.com/mpikzink)
* Add support for Liebert humidity setpoint and UPS powerfactor ([#12965](https://github.com/librenms/librenms/pull/12965)) - [martinberg](https://github.com/martinberg)
* Added support to new device Controlbox TH-332B ([#12940](https://github.com/librenms/librenms/pull/12940)) - [kdanev14](https://github.com/kdanev14)
* Add OS Support for Siemens Scalance X Switching ([#12938](https://github.com/librenms/librenms/pull/12938)) - [rhinoau](https://github.com/rhinoau)
* Alcatel-Lucent aos7 LLDP Neighbors ([#12886](https://github.com/librenms/librenms/pull/12886)) - [paulierco](https://github.com/paulierco)
* Add HPE-maPDU support ([#12550](https://github.com/librenms/librenms/pull/12550)) - [jezekus](https://github.com/jezekus)

#### Webui
* Fix availability widget service backend error ([#13044](https://github.com/librenms/librenms/pull/13044)) - [rhinoau](https://github.com/rhinoau)
* Fix availability widget device totals ([#13043](https://github.com/librenms/librenms/pull/13043)) - [rhinoau](https://github.com/rhinoau)
* Don't add %3F=yes to the url ([#13041](https://github.com/librenms/librenms/pull/13041)) - [murrant](https://github.com/murrant)
* Webui - Display app metric if available ([#13023](https://github.com/librenms/librenms/pull/13023)) - [PipoCanaja](https://github.com/PipoCanaja)
* Webui - Fix application fault detail display ([#13016](https://github.com/librenms/librenms/pull/13016)) - [PipoCanaja](https://github.com/PipoCanaja)
* Two-factor UI config and status display ([#13012](https://github.com/librenms/librenms/pull/13012)) - [rhinoau](https://github.com/rhinoau)
* Leave it to generate_device_link for sysName/hostName/IP ([#13000](https://github.com/librenms/librenms/pull/13000)) - [PipoCanaja](https://github.com/PipoCanaja)
* Filter out NULL lat/lng values from Geographical Map display queries ([#12985](https://github.com/librenms/librenms/pull/12985)) - [rhinoau](https://github.com/rhinoau)
* Fix ldap/ad group webui settings ([#12967](https://github.com/librenms/librenms/pull/12967)) - [murrant](https://github.com/murrant)
* Create Laravel Sessions Table ([#12962](https://github.com/librenms/librenms/pull/12962)) - [djamp42](https://github.com/djamp42)
* Fix "Sub-directory Support" in small steps ([#12951](https://github.com/librenms/librenms/pull/12951)) - [mpikzink](https://github.com/mpikzink)

#### Api
* Return api error when device doesn't exist ([#12978](https://github.com/librenms/librenms/pull/12978)) - [VirTechSystems](https://github.com/VirTechSystems)

#### Discovery
* Fix an issue which led to duplication of BGP peers. ([#12932](https://github.com/librenms/librenms/pull/12932)) - [cjsoftuk](https://github.com/cjsoftuk)

#### Oxidized
* Option to filter Oxidized groups ([#12966](https://github.com/librenms/librenms/pull/12966)) - [martinberg](https://github.com/martinberg)

#### Bug
* Fix scripts to allow pathname with space ([#13027](https://github.com/librenms/librenms/pull/13027)) - [Sea-n](https://github.com/Sea-n)
* Wrong statement used for Oxidized ignore_groups ([#13001](https://github.com/librenms/librenms/pull/13001)) - [martinberg](https://github.com/martinberg)
* Fix typo in filename ([#12996](https://github.com/librenms/librenms/pull/12996)) - [Sea-n](https://github.com/Sea-n)
* Make migrating after upgrading MySQL easier. ([#12971](https://github.com/librenms/librenms/pull/12971)) - [murrant](https://github.com/murrant)
* Fix proxmox menu url ([#12970](https://github.com/librenms/librenms/pull/12970)) - [si458](https://github.com/si458)

#### Refactor
* Refractor health ([#13022](https://github.com/librenms/librenms/pull/13022)) - [mpikzink](https://github.com/mpikzink)

#### Cleanup
* PHPStan fixes ([#13038](https://github.com/librenms/librenms/pull/13038)) - [murrant](https://github.com/murrant)

#### Documentation
* Documentation cleanup of sections 4-6 ([#13018](https://github.com/librenms/librenms/pull/13018)) - [rhinoau](https://github.com/rhinoau)
* Documentation cleanup of General, Install, Getting Started sections ([#13013](https://github.com/librenms/librenms/pull/13013)) - [rhinoau](https://github.com/rhinoau)
* Update Applications.md for SQUID ([#12987](https://github.com/librenms/librenms/pull/12987)) - [adamboutcher](https://github.com/adamboutcher)

#### Misc
* Aruba 8.8.0 MIBS ([#13042](https://github.com/librenms/librenms/pull/13042)) - [mpikzink](https://github.com/mpikzink)
* Create HP-SN-AGENT-MIB ([#13009](https://github.com/librenms/librenms/pull/13009)) - [ziodona85](https://github.com/ziodona85)
* Update ADVA's MIB file ([#13004](https://github.com/librenms/librenms/pull/13004)) - [keryazmi](https://github.com/keryazmi)
* Help users that did not upgrade MySQL try two ([#12989](https://github.com/librenms/librenms/pull/12989)) - [murrant](https://github.com/murrant)
* Add renamehost function result handling and exit codes to renamehost.php ([#12980](https://github.com/librenms/librenms/pull/12980)) - [rhinoau](https://github.com/rhinoau)

#### Dependencies
* Php-cs-fixer 3 prep ([#13039](https://github.com/librenms/librenms/pull/13039)) - [murrant](https://github.com/murrant)
* PHP dependencies update ([#13034](https://github.com/librenms/librenms/pull/13034)) - [murrant](https://github.com/murrant)
* Bump phpmailer/phpmailer from 6.4.1 to 6.5.0 ([#12975](https://github.com/librenms/librenms/pull/12975)) - [dependabot](https://github.com/apps/dependabot)


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

---

##[Old Changelogs](https://github.com/librenms/librenms/tree/master/doc/General/Changelogs)
