source: General/Changelog.md
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

---

## v1.22.01
*(2016-11-30)*

#### Bugfixes
* arp-table uses array_column() breaking discovery on php <=5.4 ([#5099](https://github.com/librenms/librenms/issues/5099))

---

## v1.22
*(2016-11-25)*

#### Features
* validate list devices that have not been polled in the last 5 minutes or took more than 5 minutes to poll ([#5037](https://github.com/librenms/librenms/issues/5037))
* Add Microsoft Teams Alert Transport ([#5023](https://github.com/librenms/librenms/issues/5023))
* Added formatted uptime value for alert templates [#4983](https://github.com/librenms/librenms/issues/4983)
* Adds support for enabling / disabling modules per OS ([#4963](https://github.com/librenms/librenms/issues/4963))
* Improve Dell OpenManage Discovery ([#4957](https://github.com/librenms/librenms/issues/4957))
* Added the option to select alert rules from a collection

#### Bugfixes
* use password type for SMTP Auth [#5051](https://github.com/librenms/librenms/issues/5051)
* Added alert init module to ajax_form [#5058](https://github.com/librenms/librenms/issues/5058)
* eventlog type variable collision ([#5046](https://github.com/librenms/librenms/issues/5046))
* Fixed loaded modules for ajax search ([#5043](https://github.com/librenms/librenms/issues/5043))
* timos6-7 snmprec file error ([#5035](https://github.com/librenms/librenms/issues/5035))
* Strip out " returned from Proxmox application [#4908](https://github.com/librenms/librenms/issues/4908) ([#5003](https://github.com/librenms/librenms/issues/5003))
* Used correct variable for displaying total email count in alert capture ([#5022](https://github.com/librenms/librenms/issues/5022))
* Cisco ASA Sensor Discovery, use correct variable ([#5021](https://github.com/librenms/librenms/issues/5021))
* Stop service modal form disabling services for read-only admin ([#4994](https://github.com/librenms/librenms/issues/4994))
* dbUpdate calls now check if it is 0 or above ([#4996](https://github.com/librenms/librenms/issues/4996))
* Links on devices graphs page to take users straight to specific graph page ([#5001](https://github.com/librenms/librenms/issues/5001))
* Fixed poweralert discovery, check is now case insensitive ([#5000](https://github.com/librenms/librenms/issues/5000))
* Daily.sh log_dir failed when install_dir and log_dir were not set ([#4992](https://github.com/librenms/librenms/issues/4992))
* Merge pull request [#4939](https://github.com/librenms/librenms/issues/4939) from laf/issue-4937
* Remove service type from uniform display ([#4974](https://github.com/librenms/librenms/issues/4974))
* Fixed check for VRFs, so this runs on routers without any VRFs defined ([#4972](https://github.com/librenms/librenms/issues/4972))
* Api rate percent calculation incorrect ([#4956](https://github.com/librenms/librenms/issues/4956))
* Corrects path to proxmox script in docs ([#4949](https://github.com/librenms/librenms/issues/4949))
* Update debug output in service check ([#4933](https://github.com/librenms/librenms/issues/4933))
* Fujitsu PRIMERGY 10Gbe switches are now detected correctly ([#4923](https://github.com/librenms/librenms/issues/4923))
* Toner graphs with invalid chars
* Updated syslog table to use display() for msg output ([#4859](https://github.com/librenms/librenms/issues/4859))
* Added support for https links in alerts procedure url ([#4872](https://github.com/librenms/librenms/issues/4872))
* Updated check to use != in daily.sh ([#4916](https://github.com/librenms/librenms/issues/4916))
* Remove escape characters for services form / display [#4891](https://github.com/librenms/librenms/issues/4891)
* Only update components if data exists in cimc entity-physical discovery [#4902](https://github.com/librenms/librenms/issues/4902)
* Renamed hp3par os polling file to informos ([#4861](https://github.com/librenms/librenms/issues/4861))
* Updated Cisco ASA state sensors descr to be a bit more verbose

#### Documentation
* Added FAQ on why EdgeRouters might not be detected ([#4985](https://github.com/librenms/librenms/issues/4985))
* Update freenode links ([#4935](https://github.com/librenms/librenms/issues/4935))
* Issue template to ask people to use irc / community for creating issues

#### Refactoring
* Rewrite arp-table discovery ([#5048](https://github.com/librenms/librenms/issues/5048))
* Collection and output of db and snmp stats ([#5049](https://github.com/librenms/librenms/issues/5049))
* Disable modules for pbn-cp and multimatic os
* Centralize includes and initialization ([#4991](https://github.com/librenms/librenms/issues/4991))
* Remove inappropriate usages of truncate() ([#5028](https://github.com/librenms/librenms/issues/5028))
* Watchguard Fireware cleanup ([#5015](https://github.com/librenms/librenms/issues/5015))
* Tidy up mibs V-Z ([#4979](https://github.com/librenms/librenms/issues/4979))
* Limit perf array index length to 19 characters due to limitation in ds-name rrdtool ([#4731](https://github.com/librenms/librenms/issues/4731))
* Daily.sh updated ([#4920](https://github.com/librenms/librenms/issues/4920))
* Default to only using mysqli ([#4915](https://github.com/librenms/librenms/issues/4915))
* Start of cleaning up mibs
* Update wifi clients polling to support more than 2 radios ([#4913](https://github.com/librenms/librenms/issues/4913))
* Refactored and added support for $config['log_dir'] to daily.sh
* Improve Cisco ASA Polling Performance ([#4999](https://github.com/librenms/librenms/issues/4999))

#### Devices
* Updated edge-core to edgecos and added further detection ([#5024](https://github.com/librenms/librenms/issues/5024))
* Added basic support for Ceragon devices
* Added support for Dell PowerConnect 6024
* Added PBN-CP devices.
* Added support for Edgerouter devices [#4936](https://github.com/librenms/librenms/issues/4936)
* Added support for Dell Remote consoles [#4881](https://github.com/librenms/librenms/issues/4881)
* Added support for FortiSwitch [#4852](https://github.com/librenms/librenms/issues/4852) ([#4858](https://github.com/librenms/librenms/issues/4858))

#### WebUI
* Availability map compact view, use square tiles instead of rectangles ([#5038](https://github.com/librenms/librenms/issues/5038))
* Add link to recently added device ([#5032](https://github.com/librenms/librenms/issues/5032))
* Do not show Config tab for devices set to be excluded from oxidized [#4592](https://github.com/librenms/librenms/issues/4592) ([#5029](https://github.com/librenms/librenms/issues/5029))
* Update Availability-Map Widget to use sysName when IPs used and config enabled ([#4968](https://github.com/librenms/librenms/issues/4968))
* Added support for skipping snmp check on edit snmp page for devices ([#4896](https://github.com/librenms/librenms/issues/4896))
* Update wifi_clients graph ([#4846](https://github.com/librenms/librenms/issues/4846))
* Further decouple the avail-map page from the widget ([#4887](https://github.com/librenms/librenms/issues/4887))

---

## v1.21
*(2016-10-30)*

#### Features
* Added support for global max repeaters for snmp ([#4880](https://github.com/librenms/librenms/issues/4880))
* Added custom css and include directories which are ignored by git ([#4871](https://github.com/librenms/librenms/issues/4871))
* Add an option for ad authentication to have a default level ([#4801](https://github.com/librenms/librenms/issues/4801))
* Add ping and RxLevel for SAF devices ([#4840](https://github.com/librenms/librenms/issues/4840))
* Added ability to exclude devices from xDP disco based on sysdescr, sysname or platform
* Add Extra Mimosa Discovery ([#4831](https://github.com/librenms/librenms/issues/4831))
* Add support for NX-OS fan status ([#4824](https://github.com/librenms/librenms/issues/4824))
* Add osTicket Alert Transport ([#4791](https://github.com/librenms/librenms/issues/4791))
* Add SonicWALL Sessions [#1686](https://github.com/librenms/librenms/issues/1686)
* Updated libvirt-vminfo to support oVirt
* Enhance Unifi Wireless Client count for multiple VAPs ([#4794](https://github.com/librenms/librenms/issues/4794))
* Added CEF Display page ([#3978](https://github.com/librenms/librenms/issues/3978))
* Added CPU detection for Synology DSM [#2081](https://github.com/librenms/librenms/issues/2081) ([#4756](https://github.com/librenms/librenms/issues/4756))
* Added CPU detection for Synology DSM [#2081](https://github.com/librenms/librenms/issues/2081)
* Stop displaying sensitive info in the settings page ([#4724](https://github.com/librenms/librenms/issues/4724))
* Added Cisco Integrated Management Console inventory and sensor support [#4454](https://github.com/librenms/librenms/issues/4454)
* Added support for show faults array in recovery alerts ([#4708](https://github.com/librenms/librenms/issues/4708))
* Add description and notes to be used in alerts templates ([#4706](https://github.com/librenms/librenms/issues/4706))
* validate.php: check poller and discovery status ([#4663](https://github.com/librenms/librenms/issues/4663))
* Added GlobalProtect sessions to PANOS

#### Bugfixes
* Replace \\\\l with \l on GPRINT lines ([#4882](https://github.com/librenms/librenms/issues/4882))
* fix missing config entries on global settings page [#4884](https://github.com/librenms/librenms/issues/4884)
* Fix the detection of NX-OS fan names ([#4864](https://github.com/librenms/librenms/issues/4864))
* API call to services only returned first one
* Change the wording for the create default rules button
* incomplete polling on aruba controllers
* Fixed wifi clients not reporting when value 0
* ZyWALL Fixes for OS and mem polling [#1652](https://github.com/librenms/librenms/issues/1652)
* Fix irc bot user level ([#4833](https://github.com/librenms/librenms/issues/4833))
* Updated min/max values for ubnt graphs ([#4811](https://github.com/librenms/librenms/issues/4811))
* Fix Riverbed temperature discovery ([#4832](https://github.com/librenms/librenms/issues/4832))
* only poll cipsec for cisco devices. ([#4819](https://github.com/librenms/librenms/issues/4819))
* Zywall Fixes [#1652](https://github.com/librenms/librenms/issues/1652)
* do not show fail if running as the librenms user + slightly less false positives ([#4821](https://github.com/librenms/librenms/issues/4821))
* Do not create rrd folder when -r is specified for poller ([#4812](https://github.com/librenms/librenms/issues/4812))
* Delete all port_id references [#4684](https://github.com/librenms/librenms/issues/4684)
* Used dos2unix on all mibs to prevent .index issue ([#4803](https://github.com/librenms/librenms/issues/4803))
* availability map multiple instances ([#4773](https://github.com/librenms/librenms/issues/4773))
* top widget multiple instances ([#4757](https://github.com/librenms/librenms/issues/4757))
* Updated bin/bash to use env in cronic script ([#4752](https://github.com/librenms/librenms/issues/4752))
* skip ip_exists function when we force add ([#4738](https://github.com/librenms/librenms/issues/4738))
* Stopped showing sub menus when empty [#4713](https://github.com/librenms/librenms/issues/4713)
* Samsun ML typo, remove need for hex_string translation ([#4788](https://github.com/librenms/librenms/issues/4788))
* apc load, runtime and current sensors ([#4780](https://github.com/librenms/librenms/issues/4780))
* Prevent accidental anonymous binds ([#4784](https://github.com/librenms/librenms/issues/4784))
* Update brocade fanspeed description
* qnap temperature sensors [#4586](https://github.com/librenms/librenms/issues/4586)
* Stop displaying sensitive info in the settings page ([#4724](https://github.com/librenms/librenms/issues/4724))
* Ignore meraki bad_uptime [#4691](https://github.com/librenms/librenms/issues/4691)
* Fixed trying to map devices to alert rules
* Re-enable the edit device groups button ([#4726](https://github.com/librenms/librenms/issues/4726))
* Raise version size for packages table to 255 char  ([#4656](https://github.com/librenms/librenms/issues/4656))
* Adjusted padding based on screen width to fit all icons ([#4711](https://github.com/librenms/librenms/issues/4711))
* fixed count test for cisco-otv poller module ([#4714](https://github.com/librenms/librenms/issues/4714))
* Fall back to ipNetToMediaPhysAddress when ipNetToPhysicalPhysAddress not available [#4559](https://github.com/librenms/librenms/issues/4559)
* ipmi poller, run with USER rights and surround username and password with '' [#4710](https://github.com/librenms/librenms/issues/4710)
* Wrapped ipmi user / pass in quotes [#4686](https://github.com/librenms/librenms/issues/4686) and [#4702](https://github.com/librenms/librenms/issues/4702)
* Use snmpv3 username even when NoAuthNoPriv is selected [#4677](https://github.com/librenms/librenms/issues/4677)

#### Documentation
* homepage headers: vertical align, match color, add spacing ([#4870](https://github.com/librenms/librenms/issues/4870))
* Added FAQ on moving install to another server
* Updated index page to make it look more attractive ([#4855](https://github.com/librenms/librenms/issues/4855))
* Adding setup of distro script for Linux (snmpd) configuration
* Added doc on security and vulnerabilities
* Update Graylog.md ([#4717](https://github.com/librenms/librenms/issues/4717))

#### Refactoring
* populate native vlans in the ports_vlan table for cisco devices too ([#4805](https://github.com/librenms/librenms/issues/4805))
* Small poller improvements, removes unecessary queries / execs ([#4741](https://github.com/librenms/librenms/issues/4741))
* Cleanup poller include files ([#4751](https://github.com/librenms/librenms/issues/4751))
* Update alert rules to generate sql query and store in db ([#4748](https://github.com/librenms/librenms/issues/4748))
* toner support ([#4795](https://github.com/librenms/librenms/issues/4795))
* Updated and added more options for http proxy support ([#4718](https://github.com/librenms/librenms/issues/4718))
* small fixes for cisco-voice code ([#4719](https://github.com/librenms/librenms/issues/4719))
* Improve sensors polling for performance increase ([#4725](https://github.com/librenms/librenms/issues/4725))
* Improve sensors polling for performance increase
* Rewrite for qnap fanspeeds ([#4590](https://github.com/librenms/librenms/issues/4590))
* edituser page to allow user selection of a default dashboard ([#4551](https://github.com/librenms/librenms/issues/4551))
* snmp cleanup ([#4683](https://github.com/librenms/librenms/issues/4683))

#### Devices
* Added support for Megatec NetAgent II
* Add UniFi Wireless MIB polling for Capacity [#4266](https://github.com/librenms/librenms/issues/4266)
* Added support for Sinetica UPS ¢4613
* Added additional support for Synology DSM devices [#2738](https://github.com/librenms/librenms/issues/2738)
* Add additional F5 sensor support ([#4642](https://github.com/librenms/librenms/issues/4642))
* Added Unifi Wireless Client statistics [#4772](https://github.com/librenms/librenms/issues/4772)
* Additional support for Hikvision products
* More dnos additions [#4745](https://github.com/librenms/librenms/issues/4745) ([#4749](https://github.com/librenms/librenms/issues/4749))
* Additional support for Hikvision products ([#4750](https://github.com/librenms/librenms/issues/4750))
* Add support for Moxa [#4733](https://github.com/librenms/librenms/issues/4733)
* Add additional features to SAF Tehnika ([#4666](https://github.com/librenms/librenms/issues/4666))
* Add support for more Pulse Secure devices [#4680](https://github.com/librenms/librenms/issues/4680)
* Add support for more DNOS devices [#4627](https://github.com/librenms/librenms/issues/4627)
* Added support for Sinetica UPS
* Add support for Mimosa Wireless [#4676](https://github.com/librenms/librenms/issues/4676)
* Add support for Mimosa Wireless [#4676](https://github.com/librenms/librenms/issues/4676)

#### WebUI
* Allow users to set their default dashboard from preferences page
* Updated devices view ([#4700](https://github.com/librenms/librenms/issues/4700))
* Disable page refresh on the search pages.  Users can manually hit the refresh on the grid. ([#4787](https://github.com/librenms/librenms/issues/4787))
* Display vlans for all devices. [#4349](https://github.com/librenms/librenms/issues/4349), [#3059](https://github.com/librenms/librenms/issues/3059)
* Added sorting and poller time support to top-devices widget [#4668](https://github.com/librenms/librenms/issues/4668)

---

## Release: 201609
*September 2016*

#### Features
* Added alerts output to capture system ([#4574](https://github.com/librenms/librenms/issues/4574))
* Add support for ups-apcups via snmp
* Add snmpsim to Travis automated testing. Update to check new setting for true and isset
* use snmpsim for testing fallback feature so we don't have to run snmpsim on devel computers, should be adequate for now ./scripts/pre-commit.php -u -snmpsim will start an snmpsimd.py process automatically
* Improved readability for snmp debug output
* Add last changed, connected, and mtu to all ports data
* Add temp & state sensors to Riverbed
* Added support for all OS tests
* Added Runtime support for APC ups 
* Capture device troubleshooting info (discovery, poller, snmpwalk)
* Add temp & state sensors to Riverbed
* Add more state sensors to Dell iDrac
* Allow scripts to be run from any working directory ([#4437](https://github.com/librenms/librenms/issues/4437))
* New app: ups-nut ([#4386](https://github.com/librenms/librenms/issues/4386))
* Added new discovery-wrapper.py script to replicate poller-wrapper.py ([#4351](https://github.com/librenms/librenms/issues/4351))
* Extended graphing for sla - icmp-jitter [#4341](https://github.com/librenms/librenms/issues/4341)
* Added Cisco Stackwise Support [#4301](https://github.com/librenms/librenms/issues/4301)
* Add Cisco WAAS Optimized TCP Connections Graph ([#4645](https://github.com/librenms/librenms/issues/4645))

#### Bugfixes
* Toner nrg os capacity ([#4177](https://github.com/librenms/librenms/issues/4177))
* Fixed swos detection [#4533](https://github.com/librenms/librenms/issues/4533)
* Updated edit snmp to set default poller_group ([#4694](https://github.com/librenms/librenms/issues/4694))
* Fixed SQL query for bgpPeers check to remove stale sessions ([#4697](https://github.com/librenms/librenms/issues/4697))
* Netonix version display ([#4672](https://github.com/librenms/librenms/issues/4672))
* FreeBSD variants ([#4661](https://github.com/librenms/librenms/issues/4661))
* unix-agent handling of reported time values from check_mk [#4652](https://github.com/librenms/librenms/issues/4652)
* Add checks for devices with no uptime over snmp [#4587](https://github.com/librenms/librenms/issues/4587)
* stop qnap discovery from running for every device
* Fixed the old port rrd migration code to work with new rrdtool functions ([#4616](https://github.com/librenms/librenms/issues/4616))
* Run cleanup for ipmi sensor discovery ([#4582](https://github.com/librenms/librenms/issues/4582))
* Numerous availability-map bug fixes
* AD auth stop alerts being generated
* Possible additional fix for non-terminating rrdtool processes.
* AD auth stop alerts being generated
* APC runtime graph missing in device>health>overview
* LibreNMS/Proc improvements Should fix sending rrdtool the quit command without a newline at the end. (not sure if this is an issue)
* Port ifLastChange polling now usable ([#4541](https://github.com/librenms/librenms/issues/4541))
* brother toner levels ([#4526](https://github.com/librenms/librenms/issues/4526))
* poweralert ups divisor
* Update Fortinet Logo
* Change CiscoSB devices to use ifEntry
* Disable refreshing on window resize when $no_refresh is set.
* Fix quota bills showing 0/0 for in/out ([#4462](https://github.com/librenms/librenms/issues/4462))
* This removes stale entries in the mac_ipv4 table ([#4444](https://github.com/librenms/librenms/issues/4444))
* Swos os discovery fixes [#3593](https://github.com/librenms/librenms/issues/3593)
* Vyos discovery fix [#4486](https://github.com/librenms/librenms/issues/4486)
* Toner descr that contain invalid characters [#4464](https://github.com/librenms/librenms/issues/4464)
* Alert statics not showing data
* Ubnt bad edgeswitch uptime [#4470](https://github.com/librenms/librenms/issues/4470)
* New installs would have multiple entries in dbSchema table ([#4460](https://github.com/librenms/librenms/issues/4460))
* Force add now ignores all snmp queries
* Clean up errors in the webui ([#4438](https://github.com/librenms/librenms/issues/4438))
* Reduce mib graph queries ([#4439](https://github.com/librenms/librenms/issues/4439))
* Ports page includes disabled, ignored, and deleted ports ([#4419](https://github.com/librenms/librenms/issues/4419))
* RRDTool call was always being done to check for local files ([#4427](https://github.com/librenms/librenms/issues/4427))
* MikroTik OS detection [#3593](https://github.com/librenms/librenms/issues/3593)
* Added cisco886Va to bad_ifXEntry for cisco os ([#4374](https://github.com/librenms/librenms/issues/4374))
* Stop irc bot crashing on .reload [#4353](https://github.com/librenms/librenms/issues/4353)
* Quanta blade switches are now being correctly detected as Quanta switches ([#4358](https://github.com/librenms/librenms/issues/4358))
* Added options to make temperature graphs display y-axis correctly [#4350](https://github.com/librenms/librenms/issues/4350)
* Added options to make voltage graphs display y-axis correctly [#4326](https://github.com/librenms/librenms/issues/4326)
* Calling rrdtool_pipe_open() instead of rrdtool_initialize(); ([#4343](https://github.com/librenms/librenms/issues/4343))
* Enterasys use ifname for port names [#3263](https://github.com/librenms/librenms/issues/3263)
* Ricoh/nrg toner levels [#4177](https://github.com/librenms/librenms/issues/4177)
* Availability map device box reverted to original size, fixes for device groups ([#4334](https://github.com/librenms/librenms/issues/4334))
* Remove Cisco remote access stats graph transparency ([#4331](https://github.com/librenms/librenms/issues/4331))
* Cisco remote access stats bugfix [#4293](https://github.com/librenms/librenms/issues/4293) ([#4309](https://github.com/librenms/librenms/issues/4309))
* Added ability to force devices to use ifEntry instead of ifXEntry ([#4100](https://github.com/librenms/librenms/issues/4100))
* Don’t add Cisco VSS sensors if VSS is not running [#4111](https://github.com/librenms/librenms/issues/4111)
* Always validate the default dashboard_id to make sure it still exists
* NRG Toner detection [#4250](https://github.com/librenms/librenms/issues/4250)
* Missing variable in services api call
* Added influxdb options to check-services.php

#### Documentation
* Include PHP Install instructions for MySQL app
* Added FAQ for why interfaces are missing from overall traffic graphs ([#4696](https://github.com/librenms/librenms/issues/4696))
* Updated Applications to clarify apache setup
* Update apache applications to detail testing and additional requirements.md
* Updated release doc with more information on stable / dev branches
* Corrected the rsyslog documentation to be compatible with logrotate
* Fixed centos snmp path
* Updated to include info on how to use git hook to validate code ([#4484](https://github.com/librenms/librenms/issues/4484))
* Added info on how to perform unit testing
* Added faq to explain why devices show as warning ([#4449](https://github.com/librenms/librenms/issues/4449))
* Standardize snmp extend script location to /etc/snmp/ ([#4418](https://github.com/librenms/librenms/issues/4418))
* Added NFSen docs + update general config docs ([#4412](https://github.com/librenms/librenms/issues/4412))
* Clarify install docs to run validate as root [#4286](https://github.com/librenms/librenms/issues/4286) 
* Added example to alerting doc for using variables of similar name [#4264](https://github.com/librenms/librenms/issues/4264)
* Added docs + file changes to support creating new releases/changelog
* Update snmpd setup in Installation-Ubuntu-1604 docs [#4243](https://github.com/librenms/librenms/issues/4243)

#### Refactoring
* Centralize MIB include directory specification ([#4603](https://github.com/librenms/librenms/issues/4603))
* OS discovery files (a-z)
* F5 device discovery cleanup + test unit
* Remove external uses of GenGroupSQL()
* consolidate snmpcmd generation
* consolidate snmpcmd generation I needed to generate an snmpcmd for an upcoming PR, so I figured I'd save a little code duplication.
* Refactored new helper functions for case sensitivity [#4283](https://github.com/librenms/librenms/issues/4283) 
* Final PSR2 cleanup
* Moved IRCBot class to LibreNMS namespace [#4246](https://github.com/librenms/librenms/issues/4246) 
* Update code in /includes to be psr2 compliant [#4220](https://github.com/librenms/librenms/issues/4220)

#### Devices
* Samsung Printer Discovery [#4251](https://github.com/librenms/librenms/issues/4251) ([#4258](https://github.com/librenms/librenms/issues/4258))
* HP 1820 Discovery [#3933](https://github.com/librenms/librenms/issues/3933) ([#4259](https://github.com/librenms/librenms/issues/4259))
* Added support for Cisco Callmanager
* Edge Core ES3528M - base support
* Added support for Cisco IPS ([#4561](https://github.com/librenms/librenms/issues/4561))
* Added MGE detection
* Netonix switch data collection update
* Eaton PowerXpert
* Added Datacom Dbm Support
* Updated Edgerouter lite detection
* Added support for Cisco Callmanager
* Procurve 5400R series [#4375](https://github.com/librenms/librenms/issues/4375)
* hp online admin cpu and mem [#4327](https://github.com/librenms/librenms/issues/4327)
* Added support for Foundry Networks [#4311](https://github.com/librenms/librenms/issues/4311)
* Added Cisco Stackwise Support [#4301](https://github.com/librenms/librenms/issues/4301)
* Added support for PLANET Networking & Communication switches ([#4308](https://github.com/librenms/librenms/issues/4308))
* Added support for Fujitsu Primergy switches [#4277](https://github.com/librenms/librenms/issues/4277) ([#4280](https://github.com/librenms/librenms/issues/4280))
* Added support for Lanier printers [#4267](https://github.com/librenms/librenms/issues/4267) 
* Added Temp and State support for EdgeSwitch OS [#4265](https://github.com/librenms/librenms/issues/4265) 
* Added support for DDN Storage [#2737](https://github.com/librenms/librenms/issues/2737) ([#4261](https://github.com/librenms/librenms/issues/4261))
* Improved support for UBNT EdgeSwitch OS [#4249](https://github.com/librenms/librenms/issues/4249)
* Improved support for Avaya VSP [#4237](https://github.com/librenms/librenms/issues/4237)
* Added support for macOS Sierra ([#4557](https://github.com/librenms/librenms/issues/4557))
* Improve BDCOM detection ([#4329](https://github.com/librenms/librenms/issues/4329))

#### WebUI
* top devices enhancement [#4447](https://github.com/librenms/librenms/issues/4447)
* Individual devices now use bootgrid syslog ([#4584](https://github.com/librenms/librenms/issues/4584))
* added amazon server icon
* Update all glyphicon to font awesome
* Relocate Alerts menu
* Updated force add option for addhost.php to be present in all instances ([#4428](https://github.com/librenms/librenms/issues/4428))
* Add check to display make bill on port page only if billing is enabled ([#4361](https://github.com/librenms/librenms/issues/4361))
* Added Pagination and server side search via Ajax to NTP ([#4330](https://github.com/librenms/librenms/issues/4330))

---

### August 2016

#### Bug fixes
  - WebUI
    - Fix Infoblox dhcp messages graph ([PR3898](https://github.com/librenms/librenms/pull/3898))
    - Fix version_info output in Safari ([PR3914](https://github.com/librenms/librenms/pull/3914))
    - Added missing apps to Application page ([PR3964](https://github.com/librenms/librenms/pull/3964))
  - Discovery / Polling
    - Clear our stale IPSEC sessions from the DB ([PR3904](https://github.com/librenms/librenms/pull/3904))
    - Fixed some InfluxDB bugs in check-services and ports ([PR4031](https://github.com/librenms/librenms/pull/4031))
    - Fixed Promox and Ceph rrd's ([PR4038](https://github.com/librenms/librenms/pull/4038), [PR4037](https://github.com/librenms/librenms/pull/4037), [PR4047](https://github.com/librenms/librenms/pull/4047), [PR4041](https://github.com/librenms/librenms/pull/4041))
    - Fixed LLDP Remote port in discovery-protocols module ([PR4070](https://github.com/librenms/librenms/pull/4070))
  - Billing
    - Check if ifSpeed is returned for calculating billing ([PR3921](https://github.com/librenms/librenms/pull/3921))
  - Applications
    - NFS-V3 stats fixed ([PR3963](https://github.com/librenms/librenms/pull/3963))
  - Misc
    - Dell Equallogic storage fix ([PR3956](https://github.com/librenms/librenms/pull/3956))
    - Fix syslog bug where entries would log to the wrong device ([PR3996](https://github.com/librenms/librenms/pull/3996))

#### Improvements
  - Added / improved detection for:
    - Cisco WAAS / WAVE ([PR3899](https://github.com/librenms/librenms/pull/3899))
    - Maipu MyPower ([PR3909](https://github.com/librenms/librenms/pull/3909))
    - TPLink Switches ([PR3919](https://github.com/librenms/librenms/pull/3919))
    - Dell N3024 ([PR3941](https://github.com/librenms/librenms/pull/3941))
    - Cisco FXOS ([PR3943](https://github.com/librenms/librenms/pull/3943))
    - Brocade FABOS ([PR3959](https://github.com/librenms/librenms/pull/3959), [PR3988](https://github.com/librenms/librenms/pull/3988))
    - JunOS ([PR3976](https://github.com/librenms/librenms/pull/3976))
    - Dell PowerConnect ([PR3998](https://github.com/librenms/librenms/pull/3998), [PR4007](https://github.com/librenms/librenms/pull/4007))
    - Comware ([PR3967](https://github.com/librenms/librenms/pull/3967))
    - Calix E5 ([PR3864](https://github.com/librenms/librenms/pull/3864))
    - Raisecom ([PR3992](https://github.com/librenms/librenms/pull/3864))
    - Cisco ISE ([PR4063](https://github.com/librenms/librenms/pull/4063))
    - Acano ([PR4064](https://github.com/librenms/librenms/pull/4064))
    - McAfee SIEM Nitro ([PR4066](https://github.com/librenms/librenms/pull/4064))
    - HP Bladesystem C3000/C7000 OA ([PR4035](https://github.com/librenms/librenms/pull/4035))
    - Cisco VCS (Expressway) ([PR4086](https://github.com/librenms/librenms/pull/4086))
    - Cisco Telepresence Conductor ([PR4087](https://github.com/librenms/librenms/pull/4087))
    - Avaya VSP ([PR4048](https://github.com/librenms/librenms/pull/4048))
    - Cisco/Tandberg Video Conferencing ([PR4065](https://github.com/librenms/librenms/pull/4065))
    - Cisco Prime Infrastructure ([PR4088](https://github.com/librenms/librenms/pull/4088))
    - HWGroup STE2 ([PR4116](https://github.com/librenms/librenms/pull/4116))
    - HP 2530 Procurve / Arube ([PR4119](https://github.com/librenms/librenms/pull/4119))
    - Brother Printers ([PR4141](https://github.com/librenms/librenms/pull/4141))
    - Hytera Repeater ([PR4163](https://github.com/librenms/librenms/pull/4163))
    - Sonus ([PR4176](https://github.com/librenms/librenms/pull/4176))
    - Freeswitch ([PR4203](https://github.com/librenms/librenms/pull/4203))
  - WebUI
    - Improved OSPF display ([PR3908](https://github.com/librenms/librenms/pull/3908))
    - Improved Apps overview page ([PR3954](https://github.com/librenms/librenms/pull/3954))
    - Improved Syslog page ([PR3955](https://github.com/librenms/librenms/pull/3955), [PR3971](https://github.com/librenms/librenms/pull/3971))
    - Rewrite availability map ([PR4043](https://github.com/librenms/librenms/pull/4043))
    - Add predicted usage to billing overview ([PR4049](https://github.com/librenms/librenms/pull/4049))
  - API
    - Added services calls to API ([PR4215](https://github.com/librenms/librenms/pull/4215))
  - Discovery / Polling
    - Added CPU detection for Dell PowerConnect 8024F ([PR3966](https://github.com/librenms/librenms/pull/3966))
    - Cisco VSS state discovery ([PR3977](https://github.com/librenms/librenms/pull/3977))
    - Refactor of BGP Discovery and Polling (mainly JunOS) ([PR3938](https://github.com/librenms/librenms/pull/3938))
    - Added Sensors for Brocade NOS ([PR3969](https://github.com/librenms/librenms/pull/3969))
    - Cisco ASA HA States ([PR4012](https://github.com/librenms/librenms/pull/4012))
    - Improved IPSLA Support ([PR4006](https://github.com/librenms/librenms/pull/4006))
    - Added support for CISCO-NTP-MIB ([PR4005](https://github.com/librenms/librenms/pull/4005))
    - Improved toner support for Ricoh devices ([PR4180](https://github.com/librenms/librenms/pull/4180))
  - Documentation
    - New doc site live http://docs.librenms.org/
    - Added rsyslog 5 example to syslog docs ([PR3912](https://github.com/librenms/librenms/pull/3912))
    - Application doc updates ([PR3928](https://github.com/librenms/librenms/pull/3928))
  - Applications
    - App OS Updates support ([PR3935](https://github.com/librenms/librenms/pull/3935))
    - PowerDNS Recursor improvements ([PR3932](https://github.com/librenms/librenms/pull/3932))
    - Add DHCP Stats support ([PR3970](https://github.com/librenms/librenms/pull/3970))
    - Added snmp support to Memcached ([PR3949](https://github.com/librenms/librenms/pull/3949))
    - Added Unbound support ([PR4074](https://github.com/librenms/librenms/pull/4074))
    - Added snmp support to Proxmox ([PR4052](https://github.com/librenms/librenms/pull/4052))
    - Added Raspberry Pi Sensor support ([PR4057](https://github.com/librenms/librenms/pull/4057))
    - Updated NTPD support ([PR4077](https://github.com/librenms/librenms/pull/4077))
  - Misc
    - Added cleanup of old RRD files to daily.sh ([PR3907](https://github.com/librenms/librenms/pull/3907))
    - Refactored addHost event logs ([PR3929](https://github.com/librenms/librenms/pull/3929), [PR3997](https://github.com/librenms/librenms/pull/3997))
    - Refactored RRD Functions ([PR3800](https://github.com/librenms/librenms/pull/3800), [PR4081](https://github.com/librenms/librenms/pull/4081))
    - Added support for nets-exclude in snmp-scan ([PR4000](https://github.com/librenms/librenms/pull/4045))
    - Refactored files in html (Libraries and PSR2 style ([PR4071](https://github.com/librenms/librenms/pull/4071), [PR4101](https://github.com/librenms/librenms/pull/4101), [PR4117](https://github.com/librenms/librenms/pull/4117))
    - Various IRC updates and fixes ([PR4200](https://github.com/librenms/librenms/pull/4200), [PR4204](https://github.com/librenms/librenms/pull/4204), [PR4201](https://github.com/librenms/librenms/pull/4201))

### July 2016

#### Bug fixes
  - API
    - Stop outputting vrf lite and IP info when device doesn't exist ([PR3785](https://github.com/librenms/librenms/pull/3785))
  - WebUI
    - Added force refresh for generic image widget ([PR3817](https://github.com/librenms/librenms/pull/3817))
    - Fixed NFSen tab not showing in all cases ([PR3857](https://github.com/librenms/librenms/pull/3857))
  - Discovery / Polling
    - Fixed incorrect IBM-AMM thresholds ([PR3866](https://github.com/librenms/librenms/pull/3866))
    - Fixed Pulse OS whitespace in polling ([PR3883](https://github.com/librenms/librenms/pull/3883))
  - Misc
    - Fixed device group search ([PR3788](https://github.com/librenms/librenms/pull/3788))
    - Fixed sporadic device delete ([PR3805](https://github.com/librenms/librenms/pull/3805))
    - Retry creation of two tables ([PR3848](https://github.com/librenms/librenms/pull/3848))

#### Improvements
  - Added / improved detection for:
    - Telco systems ([PR3773](https://github.com/librenms/librenms/pull/3773), [PR3804](https://github.com/librenms/librenms/pull/3804))
    - Cisco ACS ([PR3786](https://github.com/librenms/librenms/pull/3786))
    - Adtran AOS ([PR3787](https://github.com/librenms/librenms/pull/3787), [PR3799](https://github.com/librenms/librenms/pull/3799))
    - Lantronix SLC ([PR3797](https://github.com/librenms/librenms/pull/3797))
    - PBN Sensor support ([PR3820](https://github.com/librenms/librenms/pull/3820))
    - Ironware VRF discovery ([PR3827](https://github.com/librenms/librenms/pull/3827))
    - Comware sensors discovery ([PR3881](https://github.com/librenms/librenms/pull/3881), [PR3889](https://github.com/librenms/librenms/pull/3889), [PR3896](https://github.com/librenms/librenms/pull/3896))
    - Brocade VDX detection ([PR3888](https://github.com/librenms/librenms/pull/3888))
    - Checkpoint GAiA ([PR3890](https://github.com/librenms/librenms/pull/3890))
    - Cisco ASA-X Hardware detection ([PR3897](https://github.com/librenms/librenms/pull/3897))
  - WebUI
    - Added sysName to global search if != hostname ([PR3815](https://github.com/librenms/librenms/pull/3815))
    - Improved look of device SLA panel ([PR3831](https://github.com/librenms/librenms/pull/3831))
    - Added more colours to Cisco CBQOS graphs ([PR3842](https://github.com/librenms/librenms/pull/3842))
    - Improved look of Cisco IPSEC Tunnels page ([PR3874](https://github.com/librenms/librenms/pull/3874))
  - Discovery / Polling
    - Added ability to set Max repeaters per device ([PR3781](https://github.com/librenms/librenms/pull/3781))
  - Applications
    - Moved all application scripts to librenms/librenms-agent repo ([PR3865](https://github.com/librenms/librenms/pull/3865), [PR3886](https://github.com/librenms/librenms/pull/3886))
    - Added NFS stats ([PR3792](https://github.com/librenms/librenms/pull/3792), [PR3853](https://github.com/librenms/librenms/pull/3853))
    - Added PowerDNS Recursor ([PR3869](https://github.com/librenms/librenms/pull/3869))
  - Alerting
    - Updated format for Slack alerts ([PR3852](https://github.com/librenms/librenms/pull/3852))
    - Added support for multiple emails in sysContact and users table ([PR3885](https://github.com/librenms/librenms/pull/3885))
    - Added ability to use uptime in alert templates ([PR3893](https://github.com/librenms/librenms/pull/3893))
  - Misc
    - Added date to git version info ([PR3782](https://github.com/librenms/librenms/pull/3782))
    - Added logging of versions when upgrading ([PR3807](https://github.com/librenms/librenms/pull/3807))
    - Added ability to lookup device from IP for syslog ([PR3812](https://github.com/librenms/librenms/pull/3812))
    - Updated component system ([PR3821](https://github.com/librenms/librenms/pull/3821))
    - Improvements to validate script ([PR3840](https://github.com/librenms/librenms/pull/3840), [PR3868](https://github.com/librenms/librenms/pull/3868))

### June 2016

#### Bug fixes
  - WebUI:
    - Rename $ds to $ldap_connection for auth modules ([PR3596](https://github.com/librenms/librenms/pull/3596))
    - Fix the display of custom snmp ports ([PR3646](https://github.com/librenms/librenms/pull/3646))
    - Fix bugs in Create new / edit alert templates ([PR3651](https://github.com/librenms/librenms/pull/3651))
    - Fixed ajax_ calls for use with base_url ([PR3661](https://github.com/librenms/librenms/pull/3661))
    - Updated old frontpage to use new services format ([PR3691](https://github.com/librenms/librenms/pull/3691))
    - Order alerts by state to indicate which alerts are open ([PR3692](https://github.com/librenms/librenms/pull/3692))
    - Fixed maintenance windows showing as lapsed ([PR3704](https://github.com/librenms/librenms/pull/3704))
    - Removed duplicated dbInsert from dashboard creation ([PR3761](https://github.com/librenms/librenms/pull/3761))
    - Fixed 95th for graphs ([PR3762](https://github.com/librenms/librenms/pull/3762))
  - Polling / Discovery:
    - Updated Poweralert divisor to 10 for sensors ([PR3645](https://github.com/librenms/librenms/pull/3645))
    - Fixed NX-OS version polling ([PR3688](https://github.com/librenms/librenms/pull/3688))
    - Fixed STP log spam from Mikrotik device ([PR3689](https://github.com/librenms/librenms/pull/3689))
    - Removed " from ZyWall version number ([PR3693](https://github.com/librenms/librenms/pull/3693))
    - Updated register_mib to use d_echo ([PR3739](https://github.com/librenms/librenms/pull/3739))
    - Fixed invalid SQL for BGP Discovery ([PR3742](https://github.com/librenms/librenms/pull/3742))
  - Alerting:
    - Unacknowledged alerts will now continue to send alerts ([PR3667](https://github.com/librenms/librenms/pull/3667))
  - Misc:
    - Fix smokeping path in gen_smokeping ([PR3577](https://github.com/librenms/librenms/pull/3577))
    - Fix full include path in includes/polling/functions.inc.php ([PR3614](https://github.com/librenms/librenms/pull/3614))
    - Added port_id to tune_port.php query ([PR3753](https://github.com/librenms/librenms/pull/3753))
    - Updated port schema to support > 17.1 Gbs for _rate values ([PR3754](https://github.com/librenms/librenms/pull/3754))

#### Improvements
  - Added / improved detection for:
    - HPE 3Par ([PR3578](https://github.com/librenms/librenms/pull/3578))
    - Buffalo TeraStation ([PR3587](https://github.com/librenms/librenms/pull/3587))
    - Samsung C printers ([PR3598](https://github.com/librenms/librenms/pull/3598))
    - Roomalert3e ([PR3599](https://github.com/librenms/librenms/pull/3599))
    - Avtech Switches ([PR3611](https://github.com/librenms/librenms/pull/3611))
    - IBM Bladecenter switches ([PR3623](https://github.com/librenms/librenms/pull/3623))
    - HWg support ([PR3624](https://github.com/librenms/librenms/pull/3624))
    - IBM IMM ([PR3625](https://github.com/librenms/librenms/pull/3625))
    - ServerTech Sentry4 PDUs ([PR3659](https://github.com/librenms/librenms/pull/3659))
    - SwOS ([PR3662](https://github.com/librenms/librenms/pull/3662))
    - Sophos (R3678, [PR3679](https://github.com/librenms/librenms/pull/3679), [PR3736](https://github.com/librenms/librenms/pull/3736))
    - OSX El Capitan ([PR3690](https://github.com/librenms/librenms/pull/3690))
    - DNOS ([PR3703](https://github.com/librenms/librenms/pull/3703), [PR3730](https://github.com/librenms/librenms/pull/3730))
    - Cisco SB SG200 ([PR3705](https://github.com/librenms/librenms/pull/3705))
    - EMC FlareOS ([PR3712](https://github.com/librenms/librenms/pull/3712))
    - Enhance Brocade Fabric OS ([PR3712](https://github.com/librenms/librenms/pull/3712))
    - Huawei SmartAX ([PR3737](https://github.com/librenms/librenms/pull/3737))
  - Polling / Discovery:
    - Use lsb_release in distro script ([PR3580](https://github.com/librenms/librenms/pull/3580))
    - Allow lmsensors fanspeeds of 0 to be discovered ([PR3616](https://github.com/librenms/librenms/pull/3616))
    - Added support for rrdcached application monitoring ([PR3627](https://github.com/librenms/librenms/pull/3627))
    - Improve the output of polling/debug to make it easier to see modules ([PR3694](https://github.com/librenms/librenms/pull/3694))
  - WebUI:
    - Resolve some reported security issues ([PR3586](https://github.com/librenms/librenms/pull/3586)) With thanks to https://twitter.com/wireghoul
    - Order apps list alphabetically ([PR3600](https://github.com/librenms/librenms/pull/3600))
    - Network map improvements ([PR3602](https://github.com/librenms/librenms/pull/3602))
    - Added support for varying hostname formats in Oxidized integration ([PR3617](https://github.com/librenms/librenms/pull/3617))
    - Added device hw/location on hover in alerts table ([PR3621](https://github.com/librenms/librenms/pull/3621))
    - Updated unpolled notification to link directly to those devices ([PR3696](https://github.com/librenms/librenms/pull/3696))
    - Added ability to search via IP for Graylog integration ([PR3697](https://github.com/librenms/librenms/pull/3697))
    - Optimised network map SQL ([PR3715](https://github.com/librenms/librenms/pull/3715))
    - Added support for wildcards in custom graph groups ([PR3722](https://github.com/librenms/librenms/pull/3722))
    - Added ability to override ifSpeed for ports ([PR3752](https://github.com/librenms/librenms/pull/3752))
    - Added sysName to global search ([PR3757](https://github.com/librenms/librenms/pull/3757))
  - Alerting:
    - Added ability to use location in alert templates ([PR3652](https://github.com/librenms/librenms/pull/3652))
  - Documentation:
    - Added docs on Auto discovery ([PR3671](https://github.com/librenms/librenms/pull/3671))
    - Updated InfluxDB docs ([PR3673](https://github.com/librenms/librenms/pull/3673))
    - Updated distributed polling docs ([PR3675](https://github.com/librenms/librenms/pull/3675))
    - Updated FAQs ([PR3677](https://github.com/librenms/librenms/pull/3677))
  - Misc:
    - Added pivot table for device groups ready for V2 ([PR3589](https://github.com/librenms/librenms/pull/3589))
    - Added device_id column to eventlog ([PR3682](https://github.com/librenms/librenms/pull/3682))
    - Cleanup sensors and related tables + added constraints ([PR3745](https://github.com/librenms/librenms/pull/3745))

### May 2016

#### Bug fixes
  - WebUI:
    - Fixed broken performance charts using VisJS ([PR3479](https://github.com/librenms/librenms/pull/3479))
    - Fixed include path to file in create alert item ([PR3480](https://github.com/librenms/librenms/pull/3480))
    - Updated services box on front page to utilise the new services ([PR3481](https://github.com/librenms/librenms/pull/3481))
    - Potential fix for intermittent logouts ([PR3372](https://github.com/librenms/librenms/pull/3372))
    - Updated sensors hostname to use correct variable ([PR3485](https://github.com/librenms/librenms/pull/3485))
  - Polling / Discovery:
    - Only poll AirMAX if device supports the MIB ([PR3486](https://github.com/librenms/librenms/pull/3486))
  - Alerting:
    - Don't alert unless the sensor value surpasses the threshold ([PR3507](https://github.com/librenms/librenms/pull/3507))

#### Improvements
  - Added / improved detection for:
    - Microsemo timing devices ([PR3453](https://github.com/librenms/librenms/pull/3453))
    - Bintec smart routers ([PR3454](https://github.com/librenms/librenms/pull/3454))
    - PoweWalker support ([PR3456](https://github.com/librenms/librenms/pull/3456))
    - BDCom support ([PR3459](https://github.com/librenms/librenms/pull/3459))
    - Cisco WAPs ([PR3460](https://github.com/librenms/librenms/pull/3460))
    - EMC Data domain ([PR3461](https://github.com/librenms/librenms/pull/3461))
    - Xerox support ([PR3462](https://github.com/librenms/librenms/pull/3462))
    - Calix support ([PR3463](https://github.com/librenms/librenms/pull/3463))
    - Isilon OneFS ([PR3482](https://github.com/librenms/librenms/pull/3482))
    - Ricoh printers ([PR3483](https://github.com/librenms/librenms/pull/3483))
    - HP Virtual Connect ([PR3487](https://github.com/librenms/librenms/pull/3487))
    - Equallogic arrays + Dell servers ([PR3519](https://github.com/librenms/librenms/pull/3519))
    - Alcatel-Lucent SR + SAR ([PR3535](https://github.com/librenms/librenms/pull/3535), [PR3553](https://github.com/librenms/librenms/pull/3553))
    - Xirrus Wireless Access Points ([PR3543](https://github.com/librenms/librenms/pull/3543))
  - Polling / Discovery:
    - Add config option to stop devices with duplicate sysName's being added ([PR3473](https://github.com/librenms/librenms/pull/3473))
    - Enable discovery support of CDP neighbours by IP ([PR3561](https://github.com/librenms/librenms/pull/3561))
  - Alerting:
    - Added ability to use sysName in templates ([PR3470](https://github.com/librenms/librenms/pull/3470))
    - Send Slack alerts as pure JSON ([PR3522](https://github.com/librenms/librenms/pull/3522))
    - Apply colour to HipChat messages ([PR3539](https://github.com/librenms/librenms/pull/3539))
  - WebUI:
    - Added ability to filter alerts by state ([PR3471](https://github.com/librenms/librenms/pull/3471))
    - Added support for using local openstreet map tiles ([PR3472](https://github.com/librenms/librenms/pull/3472))
    - Added ability to show services on availability map ([PR3496](https://github.com/librenms/librenms/pull/3496))
    - Added combined auth module for http auth and AD auth ([PR3531](https://github.com/librenms/librenms/pull/3531))
    - List services alphabetically ([PR3538](https://github.com/librenms/librenms/pull/3538))
    - Added support for scrollable widgets ([PR3565](https://github.com/librenms/librenms/pull/3565))
  - Graphs:
    - Added Hit/Misses for memcached graphs ([PR3499](https://github.com/librenms/librenms/pull/3499))
  - API:
    - Update get_graph_generic_by_hostname to use device_id as well ([PR3494](https://github.com/librenms/librenms/pull/3494))
  - Docs:
    - Added configuration for SNMP Proxy support ([PR3528](https://github.com/librenms/librenms/pull/3528))
  - Misc:
    - Added purge for alert log ([PR3469](https://github.com/librenms/librenms/pull/3469))

### April 2016

#### Bug fixes
  - Discovery / Polling:
    - Fix poweralert OS detection ([PR3414](https://github.com/librenms/librenms/pull/3414))
  - WebUI:
    - Fixed headers for varying ajax calls ([PR3432](https://github.com/librenms/librenms/pull/3432), [PR3433](https://github.com/librenms/librenms/pull/3433), [PR3434](https://github.com/librenms/librenms/pull/3434), [PR3435](https://github.com/librenms/librenms/pull/3435))
  - Misc:
    - Update syslog to support incorrect time ([PR3348](https://github.com/librenms/librenms/pull/3348))
    - Fixed InfluxDB to send data as int/float ([PR3354](https://github.com/librenms/librenms/pull/3354))
    - Small bug fixes to the services update ([PR3366](https://github.com/librenms/librenms/pull/3366), [PR3396](https://github.com/librenms/librenms/pull/3396), [PR3425](https://github.com/librenms/librenms/pull/3425), [PR3426](https://github.com/librenms/librenms/pull/3426), [PR3427](https://github.com/librenms/librenms/pull/3427))
    - Fix bug with obtaining data for new bills in some scenarios ([PR3404](https://github.com/librenms/librenms/pull/3404))
    - Improved PHP 7 support ([PR3417](https://github.com/librenms/librenms/pull/3417))
    - Fix urls within billing section for sub dir support ([PR3442](https://github.com/librenms/librenms/pull/3442))

#### Improvements
  - WebUI:
    - Update rancid file detection ([PR3341](https://github.com/librenms/librenms/pull/3341))
    - Make graphs in widgets clickable ([PR3355](https://github.com/librenms/librenms/pull/3355))
    - Add config option to set the typeahead results ([PR3363](https://github.com/librenms/librenms/pull/3363))
    - Add config option to set min graph height ([PR3410](https://github.com/librenms/librenms/pull/3410))
  - Discovery / Polling:
    - Updated Infoblox mibs and logo ([PR3340](https://github.com/librenms/librenms/pull/3340))
    - Updated arp discovery to support vrf lite ([PR3359](https://github.com/librenms/librenms/pull/3359))
    - Added RSSI and MNC for Cisco WWAN routers ([PR3371](https://github.com/librenms/librenms/pull/3371))
    - Updated DNOS and added CPU, Memory and Temp ([PR3391](https://github.com/librenms/librenms/pull/3391), [PR3393](https://github.com/librenms/librenms/pull/3393), [PR3395](https://github.com/librenms/librenms/pull/3395))
    - Added PoE state support for Netonix devices ([PR3416](https://github.com/librenms/librenms/pull/3416))
    - Added ability to exclude ports via ifName and ifAlias regex ([PR3439](https://github.com/librenms/librenms/pull/3439))
  - Added detection for:
    - Viprenet routers ([PR3365](https://github.com/librenms/librenms/pull/3365))
    - FreeBSD via distro script ([PR3399](https://github.com/librenms/librenms/pull/3399))
  - Documentation:
    - Updated nginx install docs ([PR3397](https://github.com/librenms/librenms/pull/3397))
    - Added FAQ on renaming hosts ([PR3444](https://github.com/librenms/librenms/pull/3444))
  - API:
    - Added call for IPsec tunnels ([PR3411](https://github.com/librenms/librenms/pull/3411))
  - Misc:
    - Added check_mk FreeBSD agent support ([PR3406](https://github.com/librenms/librenms/pull/3406))
    - Added suggestion to fix files not owned by correct user to validate.php ([PR3415](https://github.com/librenms/librenms/pull/3415))
    - Added detection for missing timezone to validate.php ([PR3428](https://github.com/librenms/librenms/pull/3428))
    - Added detection for install_dir config and local git repo issues to validate.php ([PR3440](https://github.com/librenms/librenms/pull/3440))

### March 2016

#### Bug fixes
  - WebUI:
    - Skip authentication check in graph.php if unauth graphs is enabled ([PR3019](https://github.com/librenms/librenms/pull/3019))
    - Stop double escaping notes for devices ([PR3149](https://github.com/librenms/librenms/pull/3149))
    - Corrected aggregate graph on smokeping page ([PR3177](https://github.com/librenms/librenms/pull/3177))
    - Fix non-admin syslog queries ([PR3191](https://github.com/librenms/librenms/pull/3191))
    - Fix services SQL ([PR3205](https://github.com/librenms/librenms/pull/3205))
  - Discovery / Polling:
    - Revert arp discovery to pre-vrf lite support ([PR3126](https://github.com/librenms/librenms/pull/3126))
    - Fix IOS-XR DBM sensors ([PR3291](https://github.com/librenms/librenms/pull/3291))
  - Alerting:
    - Fix alert failure response from transports ([PR3283](https://github.com/librenms/librenms/pull/3283))
  - Misc:
    - Fix data in bills if counters doesn't change ([PR3132](https://github.com/librenms/librenms/pull/3132))
    - Improve performance of billing poller ([PR3129](https://github.com/librenms/librenms/pull/3129))
    - Fix API tokens when using LDAP auth ([PR3178](https://github.com/librenms/librenms/pull/3178))
    - Import notifications with original datetime ([PR3200](https://github.com/librenms/librenms/pull/3200))
    - Add sysName for top-interfaces widget ([PR3201](https://github.com/librenms/librenms/pull/3201))
    - Fix Cisco syslog parsing when logging timestamp enabled ([PR3203](https://github.com/librenms/librenms/pull/3203))

#### Improvements
  - WebUI:
    - Added ability to show device group specific maps ([PR3018](https://github.com/librenms/librenms/pull/3018))
    - Updated Billing UI ([PR3194](https://github.com/librenms/librenms/pull/3194), [PR3195](https://github.com/librenms/librenms/pull/3195), [PR3216](https://github.com/librenms/librenms/pull/3216), [PR3239](https://github.com/librenms/librenms/pull/3239), [PR3240](https://github.com/librenms/librenms/pull/3240))
    - Added Juniper
    - Added config option for HTML emails in mail transport ([PR3221](https://github.com/librenms/librenms/pull/3221))
  - Discovery / Polling:
    - Added Juniper state support ([PR3121](https://github.com/librenms/librenms/pull/3121))
    - Added Ironware state support ([PR3160](https://github.com/librenms/librenms/pull/3160))
    - Check sysObjectID before detecting ILO temp sensors ([PR3204](https://github.com/librenms/librenms/pull/3204))
    - Improved Avtech support ([PR3207](https://github.com/librenms/librenms/pull/3207))
    - Improved Dell NOS detection ([PR3213](https://github.com/librenms/librenms/pull/3213))
    - Added Juniper alarm state monitoring ([PR3226](https://github.com/librenms/librenms/pull/3226))
    - Updated Drac state support ([PR3228](https://github.com/librenms/librenms/pull/3228))
    - Improved serial # detection for Brocade Ironware devices ([PR3292](https://github.com/librenms/librenms/pull/3292))
  - Added detection for:
    - Develop Ineo printers ([PR3224](https://github.com/librenms/librenms/pull/3224))
    - Cumulus Linux ([PR3237](https://github.com/librenms/librenms/pull/3237))
    - Deliberant WiFi ([PR3246](https://github.com/librenms/librenms/pull/3246))
    - Juniper EX2500 ([PR3254](https://github.com/librenms/librenms/pull/3254))
    - Cambium devices ([PR3279](https://github.com/librenms/librenms/pull/3279))
  - Alerting:
    - Added Canopsis alerting transport ([PR3299](https://github.com/librenms/librenms/pull/3299))
  - Misc:
    - Improved syslog support ([PR3171](https://github.com/librenms/librenms/pull/3171), [PR3172](https://github.com/librenms/librenms/pull/3172), [PR3173](https://github.com/librenms/librenms/pull/3173))
    - Added Nginx install docs for Debian/Ubuntu ([PR3301](https://github.com/librenms/librenms/pull/3301))
    - Updated InfluxDB php module ([PR3302](https://github.com/librenms/librenms/pull/3302))
    - Updated Component API ([PR3304](https://github.com/librenms/librenms/pull/3304))

### February 2016

#### Bug fixes
  - Discovery / Polling:
    - Quote snmp v2c community ([PR2927](https://github.com/librenms/librenms/pull/2927))
    - For entity-sensor, changed variable name again ([PR2948](https://github.com/librenms/librenms/pull/2948))
    - Fix some issues with/introduced by port association mode configuration ([PR2923](https://github.com/librenms/librenms/pull/2923))
    - Deal with 0 value sensors better ([PR2972](https://github.com/librenms/librenms/pull/2972), [PR2973](https://github.com/librenms/librenms/pull/2973))
    - Reverted Fortigate CPU change from Dec 2015 ([PR2990](https://github.com/librenms/librenms/pull/2990))
    - Reverted bgp code from vrf lite support ([PR3010](https://github.com/librenms/librenms/pull/3010), [PR3011](https://github.com/librenms/librenms/pull/3011), [PR3028](https://github.com/librenms/librenms/pull/3028), [PR3050](https://github.com/librenms/librenms/pull/3050))
    - Add icon to database ([PR3076](https://github.com/librenms/librenms/pull/3076))
    - Discovery updated to check for distributed polling group ([PR3086](https://github.com/librenms/librenms/pull/3086))
  - WebUI:
    - Fix ceph graps ([PR2909](https://github.com/librenms/librenms/pull/2909), [PR2942](https://github.com/librenms/librenms/pull/2942))
    - BGP Overlib ([PR2915](https://github.com/librenms/librenms/pull/2915))
    - Added `application/json` headers where json is returned ([PR2936](https://github.com/librenms/librenms/pull/2936), [PR2961](https://github.com/librenms/librenms/pull/2961))
    - Stop realtime graph page from auto refreshing ([PR2939](https://github.com/librenms/librenms/pull/2939))
    - Updated parsing of alert rules to allow `|` ([PR2917](https://github.com/librenms/librenms/pull/2917))
    - Fix IP Display ([PR2951](https://github.com/librenms/librenms/pull/2951))
    - Added missing from email config option ([PR2986](https://github.com/librenms/librenms/pull/2986))
    - Ignore devices that do not provide an uptime statistic ([PR3009](https://github.com/librenms/librenms/pull/3009))
    - Added unique id for alert widget ([PR3034](https://github.com/librenms/librenms/pull/3034))
  - Misc:
    - Updated `device_by_id_cache()` to convert IP column ([PR2940](https://github.com/librenms/librenms/pull/2940))
    - Fixed auto updating if not enabled ([PR3063](https://github.com/librenms/librenms/pull/3063))
  - Documentation:
    - Removed devloping doc as none of the info is current ([PR2911](https://github.com/librenms/librenms/pull/2911))

#### Improvements
  - WebUI:
    - Merged device option links to dropdown ([PR2955](https://github.com/librenms/librenms/pull/2955))
    - Added ability to configure # results for global search ([PR2957](https://github.com/librenms/librenms/pull/2957))
    - Added ability to show / hide line numbers for config for devices ([PR2988](https://github.com/librenms/librenms/pull/2988))
    - Added support for showing diff for Oxidized configs ([PR2994](https://github.com/librenms/librenms/pull/2994))
    - Updated visjs to 4.14.0 ([PR3031](https://github.com/librenms/librenms/pull/3031))
    - Updated apps layout to use panels ([PR3117](https://github.com/librenms/librenms/pull/3117))
  - Discovery / Polling:
    - Added VRF Lite support ([PR2820](https://github.com/librenms/librenms/pull/2820))
    - Added ability to ignore device sensors from entity mib ([PR2862](https://github.com/librenms/librenms/pull/2862))
    - Added `ifOperStatus_prev` and `ifAdminStatus_prev` values to db ([PR2912](https://github.com/librenms/librenms/pull/2912))
    - Improved bgpPolling efficiency ([PR2967](https://github.com/librenms/librenms/pull/2967))
    - Use raw timeticks for uptime ([PR3021](https://github.com/librenms/librenms/pull/3021))
    - Introduced state monitoring ([PR3102](https://github.com/librenms/librenms/pull/3102))
  - Added detection for:
    - Dell Networking N2048 ([PR2949](https://github.com/librenms/librenms/pull/2949))
    - Calix E7 devices ([PR2958](https://github.com/librenms/librenms/pull/2958))
    - Improved support for Netonix ([PR2959](https://github.com/librenms/librenms/pull/2959))
    - Improved detection for Windows 10 ([PR2962](https://github.com/librenms/librenms/pull/2962))
    - Improved support for FortiOS ([PR2991](https://github.com/librenms/librenms/pull/2991))
    - Barracuda Spam firewall support ([PR2998](https://github.com/librenms/librenms/pull/2998))
    - Improved sysDescr parsing for Unifi Switches ([PR3020](https://github.com/librenms/librenms/pull/3020))
    - Canon iR ([PR3045](https://github.com/librenms/librenms/pull/3045))
    - Cisco SF500 ([PR3057](https://github.com/librenms/librenms/pull/3057))
    - Eaton UPS ([PR3066](https://github.com/librenms/librenms/pull/3066), [PR3067](https://github.com/librenms/librenms/pull/3067), [PR3070](https://github.com/librenms/librenms/pull/3070), [PR3071](https://github.com/librenms/librenms/pull/3071))
    - ServerIron / ServerIron ADX ([PR3074](https://github.com/librenms/librenms/pull/3074))
    - Additional Qnap sensors ([PR3088](https://github.com/librenms/librenms/pull/3088), [PR3089](https://github.com/librenms/librenms/pull/3089))
    - Avtech environment sensors ([PR3091](https://github.com/librenms/librenms/pull/3091))
  - Misc:
    - Added check for rrd vadility ([PR2908](https://github.com/librenms/librenms/pull/2908))
    - Add systemd unit file for the python poller service ([PR2913](https://github.com/librenms/librenms/pull/2913))
    - Added more detection to validate for bad installs ([PR2985](https://github.com/librenms/librenms/pull/2985))
    - Syslog cleanup ([PR3036](https://github.com/librenms/librenms/pull/3036), [PR3093](https://github.com/librenms/librenms/pull/3093), [PR3099](https://github.com/librenms/librenms/pull/3099))
  - Documentation:
    -  Added description of AD configuration options ([PR2910](https://github.com/librenms/librenms/pull/2910))
    -  Add description to mibbases polling ([PR2919](https://github.com/librenms/librenms/pull/2919))

### January 2016

#### Bug fixes
  - Discovery / Polling:
    - Ignore HC Broadcast and Multicast counters for Cisco SB ([PR2552](https://github.com/librenms/librenms/pull/2552))
    - Fix Cisco temperature discovery ([PR2765](https://github.com/librenms/librenms/pull/2765))
  - WebUI:
    - Fix ajax_search.php returning null instead of [] ([PR2695](https://github.com/librenms/librenms/pull/2695))
    - Fix notification links ([PR2721](https://github.com/librenms/librenms/pull/2721))
    - Fix wrong suggestion to install PEAR in Web installer ([PR2727](https://github.com/librenms/librenms/pull/2727))
    - Fixed mysqli support for Web installer ([PR2730](https://github.com/librenms/librenms/pull/2730))
  - Misc:
    - Fix deleting device_perf entries ([PR2755](https://github.com/librenms/librenms/pull/2755))
    - Fix for schema updates to device table when poller is running ([PR2825](https://github.com/librenms/librenms/pull/2825))

#### Improvements
  - WebUI:
    - Converted arp pages to use bootgrid ([PR2669](https://github.com/librenms/librenms/pull/2669))
    - Updated VMWare listing page ([PR2684](https://github.com/librenms/librenms/pull/2684))
    - Updated typeahead.js ([PR2698](https://github.com/librenms/librenms/pull/2698))
    - Added ability to set notes for ports ([PR2688](https://github.com/librenms/librenms/pull/2688))
    - Use browser width to scale CPU and Bandwidth graphs ([PR2537](https://github.com/librenms/librenms/pull/2537), [PR2633](https://github.com/librenms/librenms/pull/2633))
    - Removed onClick from ports list ([PR2744](https://github.com/librenms/librenms/pull/2744))
    - Added support for showing sysName when hostname is IP ([PR2796](https://github.com/librenms/librenms/pull/2796))
    - Updated rancid support for different hostnames ([PR2807](https://github.com/librenms/librenms/pull/2807))
    - Added combined HTTP Auth and LDAP Auth authentication module ([PR2835](https://github.com/librenms/librenms/pull/2835))
    - Added ability to filter alerts using widgets ([PR2834](https://github.com/librenms/librenms/pull/2834))
  - Discovery / Polling:
    - Print runtime info per poller/discovery modules ([PR2713](https://github.com/librenms/librenms/pull/2713))
    - Improved polling/discovery vmware module performance ([PR2696](https://github.com/librenms/librenms/pull/2696))
    - Added STP/RSTP support ([PR2690](https://github.com/librenms/librenms/pull/2690))
    - Moved system poller module to core module ([PR2637](https://github.com/librenms/librenms/pull/2637))
    - Added lookup of IP for devices with hostname ([PR2798](https://github.com/librenms/librenms/pull/2798))
    - Centralised sensors module file structure ([PR2794](https://github.com/librenms/librenms/pull/2794))
    - Graph poller module run times ([PR2849](https://github.com/librenms/librenms/pull/2849))
    - Updated vlan support using IEEE8021-Q-BRIDGE-MIB ([PR2851](https://github.com/librenms/librenms/pull/2851))
  - Added detection for:
    - Added support for Samsung printers ([PR2680](https://github.com/librenms/librenms/pull/2680))
    - Added support for Canon printers ([PR2687](https://github.com/librenms/librenms/pull/2687))
    - Added support for Sub10 support ([PR2469](https://github.com/librenms/librenms/pull/2469))
    - Added support for Zyxel GS range ([PR2729](https://github.com/librenms/librenms/pull/2729))
    - Added support for HWGroup Poseidon ([PR2742](https://github.com/librenms/librenms/pull/2742))
    - Added support for Samsung SCX printers ([PR2760](https://github.com/librenms/librenms/pull/2760))
    - Added additional support for HP MSM ([PR2766](https://github.com/librenms/librenms/pull/2766), [PR2768](https://github.com/librenms/librenms/pull/2768))
    - Added additional support for Cisco ASA and RouterOS ([PR2784](https://github.com/librenms/librenms/pull/2784))
    - Added support for Lenovo EMC NAS ([PR2795](https://github.com/librenms/librenms/pull/2795))
    - Added support for Infoblox ([PR2801](https://github.com/librenms/librenms/pull/2801))
  - API:
    - Added support for Oxidized groups ([PR2745](https://github.com/librenms/librenms/pull/2745))
  - Misc:
    - Added option to specify Smokeping ping value ([PR2676](https://github.com/librenms/librenms/pull/2676))
    - Added backend support for InfluxDB ([PR2208](https://github.com/librenms/librenms/pull/2208))
    - Alpha2 release of MIB Polling released ([PR2536](https://github.com/librenms/librenms/pull/2536), [PR2763](https://github.com/librenms/librenms/pull/2763))
    - Centralised version info ([PR2697](https://github.com/librenms/librenms/pull/2697))
    - Added username support for libvirt over SSH ([PR2728](https://github.com/librenms/librenms/pull/2728))
    - Added Oxidized reload call when adding device ([PR2792](https://github.com/librenms/librenms/pull/2792))
    - Added components system to centralize data in MySQL ([PR2623](https://github.com/librenms/librenms/pull/2623))

### December 2015

#### Bug fixes
  - WebUI:
    - Fixed regex for negative lat/lng coords ([PR2524](https://github.com/librenms/librenms/pull/2524))
    - Fixed map page looping due to device connected to itself ([PR2545](https://github.com/librenms/librenms/pull/2545))
    - Fixed PATH_INFO for nginx ([PR2551](https://github.com/librenms/librenms/pull/2551))
    - urlencode the custom port types ([PR2597](https://github.com/librenms/librenms/pull/2597))
    - Stop non-admin users from being able to get to settings pages ([PR2627](https://github.com/librenms/librenms/pull/2627))
    - Fix JpGraph php version compare ([PR2631](https://github.com/librenms/librenms/pull/2631))
  - Discovery / Polling:
    - Pointed snmp calls for Huawei to correct MIB folder ([PR2541](https://github.com/librenms/librenms/pull/2541))
    - Fixed Ceph unix-agent support. ([PR2588](https://github.com/librenms/librenms/pull/2588))
    - Moved memory graphs from storage to memory polling ([PR2616](https://github.com/librenms/librenms/pull/2616))
    - Mask alert_log mysql output when debug is enabled to stop console crashes ([PR2618](https://github.com/librenms/librenms/pull/2618))
    - Stop Quanta devices being detected as Ubiquiti ([PR2632](https://github.com/librenms/librenms/pull/2632))
    - Fix MySQL unix-agent graphs ([PR2645](https://github.com/librenms/librenms/pull/2645))
    - Added MTA-MIB and NETWORK-SERVICES-MIB to stop warnings printed in poller debug ([PR2653](https://github.com/librenms/librenms/pull/2653))
  - Services:
    - Fix SSL check for PHP 7 ([PR2647](https://github.com/librenms/librenms/pull/2647))
  - Alerting:
    - Fix glue-expansion for alerts ([PR2522](https://github.com/librenms/librenms/pull/2522))
    - Fix HipChat transport ([PR2586](https://github.com/librenms/librenms/pull/2586))
  - Documentation:
    - Removed duplicate mysql-client install from Debian/Ubuntu install docs ([PR2543](https://github.com/librenms/librenms/pull/2543))
  - Misc:
    - Update daily.sh to ignore issues writing to log file ([PR2595](https://github.com/librenms/librenms/pull/2595))

#### Improvements
  - WebUI:
    - Converted sensors page to use bootgrid ([PR2531](https://github.com/librenms/librenms/pull/2531))
    - Added new widgets for dashboard. Notes ([PR2582](https://github.com/librenms/librenms/pull/2582)), Generic image ([PR2617](https://github.com/librenms/librenms/pull/2617))
    - Added config option to disable lazy loading of images ([PR2589](https://github.com/librenms/librenms/pull/2589))
    - Visual update to Navbar. ([PR2593](https://github.com/librenms/librenms/pull/2593))
    - Update alert rules to show actual alert rule ID ([PR2603](https://github.com/librenms/librenms/pull/2603))
    - Initial support added for per user default dashboard ([PR2620](https://github.com/librenms/librenms/pull/2620))
    - Updated Worldmap to show clusters in red if one device is down ([PR2621](https://github.com/librenms/librenms/pull/2621))
    - Cleaned up Billing pages ([PR2671](https://github.com/librenms/librenms/pull/2671))
  - Discovery / Polling
    - Added traffic bits as default for Cambium devices ([PR2525](https://github.com/librenms/librenms/pull/2525))
    - Overwrite eth0 port data from UniFi MIBs for AirFibre devices ([PR2544](https://github.com/librenms/librenms/pull/2544))
    - Added lastupdate column to sensors table for use with alerts ([PR2590](https://github.com/librenms/librenms/pull/2590),[PR2592](https://github.com/librenms/librenms/pull/2592))
    - Updated auto discovery via lldp to check for devices that use mac address in lldpRemPortId ([PR2591](https://github.com/librenms/librenms/pull/2591))
    - Updated auto discovery via lldp with absent lldpRemSysName ([PR2619](https://github.com/librenms/librenms/pull/2619))
  - API:
    - Added ability to filter devices by type and os for Oxidized API call ([PR2539](https://github.com/librenms/librenms/pull/2539))
    - Added ability to update device information ([PR2585](https://github.com/librenms/librenms/pull/2585))
    - Added support for returning device groups ([PR2611](https://github.com/librenms/librenms/pull/2611))
    - Added ability to select port graphs based on ifDescr ([PR2648](https://github.com/librenms/librenms/pull/2648))
  - Documentation:
    - Improved alerting docs explaining more options ([PR2560](https://github.com/librenms/librenms/pull/2560))
    - Added Docs for Ubuntu/Debian Smokeping integration ([PR2610](https://github.com/librenms/librenms/pull/2610))
  - Added detection for:
    - Updated Netonix switch MIBs ([PR2523](https://github.com/librenms/librenms/pull/2523))
    - Updated Fotinet MIBs ([PR2529](https://github.com/librenms/librenms/pull/2529), [PR2534](https://github.com/librenms/librenms/pull/2534))
    - Cisco SG500 ([PR2609](https://github.com/librenms/librenms/pull/2609))
    - Updated processor support for Fortigate ([PR2613](https://github.com/librenms/librenms/pull/2613))
    - Added CPU / Memory support for PBN ([PR2672](https://github.com/librenms/librenms/pull/2672))
  - Misc:
    - Updated validation to check for php extension and classes required ([PR2602](https://github.com/librenms/librenms/pull/2602))
    - Added Radius Authentication support ([PR2615](https://github.com/librenms/librenms/pull/2615))
    - Removed distinct() from alerts query to use indexes ([PR2649](https://github.com/librenms/librenms/pull/2649))

### November 2015

#### Bug fixes
  - WebUI:
    - getRates should return in and out average rates ([PR2375](https://github.com/librenms/librenms/pull/2375))
    - Fix 95th percent lines in negative range ([PR2405](https://github.com/librenms/librenms/pull/2405))
    - Fix percentage bar for billing pages ([PR2419](https://github.com/librenms/librenms/pull/2419))
    - Use HC counters first in realtime graphs ([PR2420](https://github.com/librenms/librenms/pull/2420))
    - Fix netcmd.php URI for sub dir installations ([PR2428](https://github.com/librenms/librenms/pull/2428))
    - Fixed Oxidized fetch config with groups ([PR2501](https://github.com/librenms/librenms/pull/2501))
    - Fixed background colour to white for some graphs ([PR2516](https://github.com/librenms/librenms/pull/2516))
    - Added missing Service description on services page ([PR2679](https://github.com/librenms/librenms/pull/2679))
  - API:
    - Added missing quotes for MySQL queries ([PR2382](https://github.com/librenms/librenms/pull/2382))
  - Discovery / Polling:
    - Specified MIB used when polling ntpd-server ([PR2418](https://github.com/librenms/librenms/pull/2418))
    - Added missing fields when inserting data into applications table ([PR2445](https://github.com/librenms/librenms/pull/2445))
    - Fix auto-discovery failing ([PR2457](https://github.com/librenms/librenms/pull/2457))
    - Juniper hardware inventory fix ([PR2466](https://github.com/librenms/librenms/pull/2466))
    - Fix discovery of Cisco PIX running PixOS 8.0 ([PR2480](https://github.com/librenms/librenms/pull/2480))
    - Fix bug in Proxmox support if only one VM was detected ([PR2490](https://github.com/librenms/librenms/pull/2490), [PR2547](https://github.com/librenms/librenms/pull/2547))
  - Alerting:
    - Strip && and || from query for device-groups ([PR2476](https://github.com/librenms/librenms/pull/2476))
    - Fix transports being triggered when empty keys set ([PR2491](https://github.com/librenms/librenms/pull/2491))
  Misc:
    - Updated device_traffic_descr config to stop graphs failing ([PR2386](https://github.com/librenms/librenms/pull/2386))

#### Improvements
  - WebUI:
    - Status column now sortable for /devices/ ([PR2397](https://github.com/librenms/librenms/pull/2397))
    - Update Gridster library to be responsive ([PR2414](https://github.com/librenms/librenms/pull/2414))
    - Improved rrdtool 1.4/1.5 compatibility ([PR2430](https://github.com/librenms/librenms/pull/2430))
    - Use event_id in query for Eventlog ([PR2437](https://github.com/librenms/librenms/pull/2437))
    - Add graph selector to devices overview ([PR2438](https://github.com/librenms/librenms/pull/2438))
    - Improved Navbar for varying screen sizes ([PR2450](https://github.com/librenms/librenms/pull/2450))
    - Added RIPE NCC API support for lookups ([PR2455](https://github.com/librenms/librenms/pull/2455), [PR2474](https://github.com/librenms/librenms/pull/2474))
    - Improved ports page for device with large number of neighbours ([PR2460](https://github.com/librenms/librenms/pull/2460))
    - Merged all CPU graphs into one on overview page ([PR2470](https://github.com/librenms/librenms/pull/2470))
    - Added support for sorting by traffic on device port page ([PR2508](https://github.com/librenms/librenms/pull/2508))
    - Added support for dynamic graph sizes based on browser size ([PR2510](https://github.com/librenms/librenms/pull/2510))
    - Made device location clickable in device header ([PR2515](https://github.com/librenms/librenms/pull/2515))
    - Visual improvements to bills page ([PR2519](https://github.com/librenms/librenms/pull/2519))
  - Discovery / Polling:
    - Updated Cisco SB discovery ([PR2396](https://github.com/librenms/librenms/pull/2396))
    - Added Ceph support via Applications ([PR2412](https://github.com/librenms/librenms/pull/2412))
    - Added support for per device unix-agent port ([PR2439](https://github.com/librenms/librenms/pull/2439))
    - Added ability to select up/down devices on worldmap ([PR2441](https://github.com/librenms/librenms/pull/2441))
    - Allow powerdns app to be set for Unix Agent ([PR2489](https://github.com/librenms/librenms/pull/2489))
    - Added SLES detection to distro script ([PR2502](https://github.com/librenms/librenms/pull/2502))
  - Added detection for:
    - Added CPU + Memory usage for Ubiquiti UniFi ([PR2421](https://github.com/librenms/librenms/pull/2421))
    - Added support for LigoWave Infinity AP's ([PR2456](https://github.com/librenms/librenms/pull/2456))
  - Alerting:
    - Added ability to globally disable sending alerts ([PR2385](https://github.com/librenms/librenms/pull/2385))
    - Added support for Clickatell, PlaySMS and VictorOps ([PR24104](https://github.com/librenms/librenms/pull/24104), [PR2443](https://github.com/librenms/librenms/pull/2443))
  - Documentation:
    - Improved CentOS install docs ([PR2462](https://github.com/librenms/librenms/pull/2462))
    - Improved Proxmox setup docs ([PR2483](https://github.com/librenms/librenms/pull/2483))
  - Misc:
    - Provide InnoDB config for buffer size issues ([PR2401](https://github.com/librenms/librenms/pull/2401))
    - Added AD Authentication support ([PR2411](https://github.com/librenms/librenms/pull/2411), [PR2425](https://github.com/librenms/librenms/pull/2425), [PR2432](https://github.com/librenms/librenms/pull/2432), [PR2434](https://github.com/librenms/librenms/pull/2434))
    - Added Features document ([PR2436](https://github.com/librenms/librenms/pull/2436), [PR2511](https://github.com/librenms/librenms/pull/2511), [PR2513](https://github.com/librenms/librenms/pull/2513))
    - Centralised innodb buffer check and added to validate ([PR2482](https://github.com/librenms/librenms/pull/2482))
    - Updated and improved daily.sh ([PR2487](https://github.com/librenms/librenms/pull/2487))


### October 2015

#### Bug fixes
  - Discovery / Polling:
    - Check file exists via rrdcached before creating new files on 1.5 ([PR2041](https://github.com/librenms/librenms/pull/2041))
    - Fix Riverbed discovery ([PR2133](https://github.com/librenms/librenms/pull/2133))
    - Fixes issue where snmp_get would not return the value 0 ([PR2134](https://github.com/librenms/librenms/pull/2134))
    - Fixed powerdns snmp checks ([PR2176](https://github.com/librenms/librenms/pull/2176))
    - De-dupe checks for hostname when adding hosts ([PR2189](https://github.com/librenms/librenms/pull/2189))
  - WebUI:
    - Soft fail if PHP Pear not installed ([PR2036](https://github.com/librenms/librenms/pull/2036))
    - Escape quotes for ifAlias in overlib calls ([PR2072](https://github.com/librenms/librenms/pull/2072))
    - Fix table name for access points ([PR2075](https://github.com/librenms/librenms/pull/2075))
    - Removed STACK text in graphs ([PR2097](https://github.com/librenms/librenms/pull/2097))
    - Enable multiple ifDescr overrides to be done per device ([PR2099](https://github.com/librenms/librenms/pull/2099))
    - Removed ping + performance graphs and tab if skip ping check ([PR2175](https://github.com/librenms/librenms/pull/2175))
    - Fixed services -> Alerts menu link + page ([PR2173](https://github.com/librenms/librenms/pull/2173))
    - Fix percent bar also for quota bills ([PR2198](https://github.com/librenms/librenms/pull/2198))
    - Fix new Bill ([PR2199](https://github.com/librenms/librenms/pull/2199))
    - Change default solver to hierarchicalRepulsion in vis.js ([PR2202](https://github.com/librenms/librenms/pull/2202))
    - Fix: setting user port permissions fails ([PR2203](https://github.com/librenms/librenms/pull/2203))
    - Updated devices Graphs links to use non-static time references ([PR2211](https://github.com/librenms/librenms/pull/2211))
    - Removed ignored,deleted and disabled ports from query ([PR2213](https://github.com/librenms/librenms/pull/2213))
  - API:
    - Fixed API call for alert states ([PR2076](https://github.com/librenms/librenms/pull/2076))
    - Fixed nginx rewrite for api ([PR2112](https://github.com/librenms/librenms/pull/2112))
    - Change on the add_edit_rule to modify a rule without modify the name ([PR2159](https://github.com/librenms/librenms/pull/2159))
    - Fixed list_bills function when using :bill_id ([PR2212](https://github.com/librenms/librenms/pull/2212))

#### Improvements
  - WebUI:
    - Updated Bootstrap to 3.3.5 ([PR2015](https://github.com/librenms/librenms/pull/2015))
    - Added billing graphs to graphs widget ([PR2027](https://github.com/librenms/librenms/pull/2027))
    - Lock widgets by default so they can't be moved ([PR2042](https://github.com/librenms/librenms/pull/2042))
    - Moved Device Groups menu ([PR2049](https://github.com/librenms/librenms/pull/2049))
    - Show Config tab only if device isn't excluded from oxidized ([PR2118](https://github.com/librenms/librenms/pull/2118))
    - Simplify adding config options to WebUI ([PR2120](https://github.com/librenms/librenms/pull/2120))
    - Move red map markers to foreground ([PR2127](https://github.com/librenms/librenms/pull/2127))
    - Styled the two factor auth token prompt ([PR2151](https://github.com/librenms/librenms/pull/2151))
    - Update Font Awesome ([PR2167](https://github.com/librenms/librenms/pull/2167))
    - Allow user to influence when devices are grouped on world map ([PR2170](https://github.com/librenms/librenms/pull/2170))
    - Centralised the date selector for graphs for re-use ([PR2183](https://github.com/librenms/librenms/pull/2183))
    - Don't show dashboard settings if `/bare=yes/` ([PR2364](https://github.com/librenms/librenms/pull/2364))
  - API:
    - Added unmute alert function to API ([PR2082](https://github.com/librenms/librenms/pull/2082))
  - Discovery / Polling:
    - Added additional support for some UPS' based on Multimatic cards ([PR2046](https://github.com/librenms/librenms/pull/2046))
    - Improved WatchGuard OS detection ([PR2048](https://github.com/librenms/librenms/pull/2048))
    - Treat Dell branded Wifi controllers as ArubaOS ([PR2065](https://github.com/librenms/librenms/pull/2065))
    - Added discovery option for OS or Device type ([PR2088](https://github.com/librenms/librenms/pull/2088))
    - Updated pfSense to firewall type ([PR2096](https://github.com/librenms/librenms/pull/2096))
    - Added ability to turn off icmp checks globally or per device ([PR2131](https://github.com/librenms/librenms/pull/2131))
    - Reformat check a bit to make it easier for adding additional oids in ([PR2135](https://github.com/librenms/librenms/pull/2135))
    - Updated to disable auto-discovery by ip ([PR2182](https://github.com/librenms/librenms/pull/2182))
    - Updated to use env in distro script ([PR2204](https://github.com/librenms/librenms/pull/2204))
  - Added detection for:
    - Pulse Secure OS ([PR2053](https://github.com/librenms/librenms/pull/2053))
    - Riverbed Steelhead support ([PR2107](https://github.com/librenms/librenms/pull/2107))
    - OpenBSD sensors ([PR2113](https://github.com/librenms/librenms/pull/2113))
    - Additional comware detection ([PR2162](https://github.com/librenms/librenms/pull/2162))
    - Version from Synology MIB ([PR2163](https://github.com/librenms/librenms/pull/2163))
    - VCSA as VMWare ([PR2185](https://github.com/librenms/librenms/pull/2185))
    - SAF Lumina radios ([PR2361](https://github.com/librenms/librenms/pull/2361))
    - TP-Link detection ([PR2362](https://github.com/librenms/librenms/pull/2362))
  - Documentation:
    - Improved RHEL/CentOS install docs ([PR2043](https://github.com/librenms/librenms/pull/2043))
    - Update Varnish Docs ([PR2116](https://github.com/librenms/librenms/pull/2116), [PR2126](https://github.com/librenms/librenms/pull/2126))
    - Added passworded channels for the IRC-Bot ([PR2122](https://github.com/librenms/librenms/pull/2122))
    - Updated Two-Factor-Auth.md RE: Google Authenticator ([PR2146](https://github.com/librenms/librenms/pull/2146))
  - General:
    - Added colour support to IRC bot ([PR2059](https://github.com/librenms/librenms/pull/2059))
    - Fixed IRC bot reconnect if socket dies ([PR2061](https://github.com/librenms/librenms/pull/2061))
    - Updated default crons ([PR2177](https://github.com/librenms/librenms/pull/2177))
  - Reverts:
    - "Removed what appears to be unnecessary STACK text" ([PR2128](https://github.com/librenms/librenms/pull/2128))

### September 2015

#### Bug fixes
  - Alerting:
    - Process followups if there are changes ([PR1817](https://github.com/librenms/librenms/pull/1817))
    - Typo in alert_window setting ([PR1841](https://github.com/librenms/librenms/pull/1841))
    - Issue alert-trigger as test object ([PR1850](https://github.com/librenms/librenms/pull/1850))
  - WebUI:
    - Fix permissions for World-map widget ([PR1866](https://github.com/librenms/librenms/pull/1866))
    - Clean up Global / World Map name mixup ([PR1874](https://github.com/librenms/librenms/pull/1874))
    - Removed required flag for community when adding new hosts ([PR1961](https://github.com/librenms/librenms/pull/1961))
    - Stop duplicate devices showing in map ([PR1963](https://github.com/librenms/librenms/pull/1963))
    - Fix adduser bug storing users real name ([PR1990](https://github.com/librenms/librenms/pull/1990))
    - Stop alerts top-menu being clickable ([PR1995](https://github.com/librenms/librenms/pull/1995))
  - Services:
    - Honour IP field for DNS checks ([PR1933](https://github.com/librenms/librenms/pull/1933))
  - Discovery / Poller:
    - Fix Huawei VRP os detection ([PR1931](https://github.com/librenms/librenms/pull/1931))
    - Set empty processor descr for *nix processors ([PR1951](https://github.com/librenms/librenms/pull/1951))
    - Ensure udp6/tcp6 snmp devices use fping6 ([PR1959](https://github.com/librenms/librenms/pull/1959))
    - Fix RRD creation parameters ([PR2010](https://github.com/librenms/librenms/pull/2010))
  - General:
    - Remove 'sh' from cronjob ([PR1818](https://github.com/librenms/librenms/pull/1818))
    - Remove MySQL Locks ([PR1822](https://github.com/librenms/librenms/pull/1822),[PR1826](https://github.com/librenms/librenms/pull/1826),[PR1829](https://github.com/librenms/librenms/pull/1829),[PR1836](https://github.com/librenms/librenms/pull/1836))
    - Change DB config options to use single quotes to allow $ ([PR1948](https://github.com/librenms/librenms/pull/1948))

#### Improvements
  - WebUI:
    - Ability to edit ifAlias ([PR1811](https://github.com/librenms/librenms/pull/1811))
    - Honour Mouseout/Mouseleave on map widget ([PR1814](https://github.com/librenms/librenms/pull/1814))
    - Make syslog/eventlog responsive ([PR1816](https://github.com/librenms/librenms/pull/1816))
    - Reformat Proxmox UI ([PR1825](https://github.com/librenms/librenms/pull/1825),[PR1827](https://github.com/librenms/librenms/pull/1827))
    - Misc Changes ([PR1828](https://github.com/librenms/librenms/pull/1828),[PR1830](https://github.com/librenms/librenms/pull/1830),[PR1875](https://github.com/librenms/librenms/pull/1875),[PR1885](https://github.com/librenms/librenms/pull/1885),[PR1886](https://github.com/librenms/librenms/pull/1886),[PR1887](https://github.com/librenms/librenms/pull/1887),[PR1891](https://github.com/librenms/librenms/pull/1891),[PR1896](https://github.com/librenms/librenms/pull/1896),[PR1901](https://github.com/librenms/librenms/pull/1901),[PR1913](https://github.com/librenms/librenms/pull/1913),[PR1944](https://github.com/librenms/librenms/pull/1944))
    - Added support for Oxidized versioning ([PR1842](https://github.com/librenms/librenms/pull/1842))
    - Added graph widget + settings for widgets ([PR1835](https://github.com/librenms/librenms/pull/1835),[PR1861](https://github.com/librenms/librenms/pull/1861),[PR1968](https://github.com/librenms/librenms/pull/1968))
    - Added Support for multiple dashboards ([PR1869](https://github.com/librenms/librenms/pull/1869))
    - Added settings page for Worldmap widget ([PR1872](https://github.com/librenms/librenms/pull/1872))
    - Added uptime to availability widget ([PR1881](https://github.com/librenms/librenms/pull/1881))
    - Added top devices and ports widgets ([PR1903](https://github.com/librenms/librenms/pull/1903))
    - Added support for saving notes for devices ([PR1927](https://github.com/librenms/librenms/pull/1927))
    - Added fullscreen mobile support ([PR2022](https://github.com/librenms/librenms/pull/2022))
  - Added detection for:
    - FortiOS ([PR1815](https://github.com/librenms/librenms/pull/1815))
    - HP MSM ([PR1953](https://github.com/librenms/librenms/pull/1953))
  - Discovery / Poller:
    - Added Proxmox support ([PR1789](https://github.com/librenms/librenms/pull/1789))
    - Added CPU/Mem support for SonicWALL ([PR1957](https://github.com/librenms/librenms/pull/1957))
    - Updated distro script to support Arch Linux + fall back to lsb-release ([PR1978](https://github.com/librenms/librenms/pull/1978))
  - Documentation:
    - Add varnish docs ([PR1809](https://github.com/librenms/librenms/pull/1809))
    - Added CentOS 7 RRCached docs ([PR1893](https://github.com/librenms/librenms/pull/1893))
    - Improved description of fping options ([PR1952](https://github.com/librenms/librenms/pull/1952))
  - Alerting:
    - Added RegEx support for alert rules and device groups ([PR1998](https://github.com/librenms/librenms/pull/1998))
  - General:
    - Make installer more responsive ([PR1832](https://github.com/librenms/librenms/pull/1832))
    - Update fping millisec option to 200 default ([PR1833](https://github.com/librenms/librenms/pull/1833))
    - Reduced cleanup of device_perf ([PR1837](https://github.com/librenms/librenms/pull/1837))
    - Added support for negative values in munin-plugins ([PR1907](https://github.com/librenms/librenms/pull/1907))
    - Added default librenms user to config for use in validate.php ([PR1956](https://github.com/librenms/librenms/pull/1956))
    - Added working memcache support ([PR2007](https://github.com/librenms/librenms/pull/2007))

### August 2015

#### Bug fixes
  - WebUI:
    - Fix web_mouseover not honoured on All Devices page ([PR1592](https://github.com/librenms/librenms/pull/1592))
    - Fixed bug with edit/create alert template to clear out previous values ([PR1636](https://github.com/librenms/librenms/pull/1636))
    - Initialise $port_count in devices list ([PR1640](https://github.com/librenms/librenms/pull/1640))
    - Fixed Web installer due to code tidying update ([PR1644](https://github.com/librenms/librenms/pull/1644))
    - Updated gridster variable names to make unique ([PR1646](https://github.com/librenms/librenms/pull/1646))
    - Fixed issues with displaying devices with ' in location ([PR1655](https://github.com/librenms/librenms/pull/1655))
    - Fixes updating snmpv3 details in webui ([PR1727](https://github.com/librenms/librenms/pull/1727))
    - Check for user perms before listing neighbour ports ([PR1749](https://github.com/librenms/librenms/pull/1749))
    - Fixed Test-Transport button ([PR1772](https://github.com/librenms/librenms/pull/1772))
  - DB:
    - Added proper indexes on device_perf table ([PR1621](https://github.com/librenms/librenms/pull/1621))
    - Fixed multiple mysql strict issues ([PR1638](https://github.com/librenms/librenms/pull/1638), [PR1659](https://github.com/librenms/librenms/pull/1659))
    - Convert bgpPeerRemoteAs to bigint ([PR1691](https://github.com/librenms/librenms/pull/1691))
  - Discovery / Poller:
    - Fixed Synology system temps ([PR1649](https://github.com/librenms/librenms/pull/1649))
    - Fixed discovery-arp not running since code formatting update ([PR1671](https://github.com/librenms/librenms/pull/1671))
    - Correct the DSM upgrade OID ([PR1696](https://github.com/librenms/librenms/pull/1696))
    - Fix MySQL agent host variable usage ([PR1710](https://github.com/librenms/librenms/pull/1710))
    - Pass snmp-auth parameters enclosed by single-quotes ([PR1730](https://github.com/librenms/librenms/pull/1730))
    - Revert change which skips over down ports ([PR1742](https://github.com/librenms/librenms/pull/1742))
    - Stop PoE polling for each port ([PR1747](https://github.com/librenms/librenms/pull/1747))
    - Use ifHighSpeed if ifSpeed equals 0 ([PR1750](https://github.com/librenms/librenms/pull/1750))
    - Keep PHP Backwards compatibility ([PR1766](https://github.com/librenms/librenms/pull/1766))
    - False identification of Zyxel as Cisco ([PR1776](https://github.com/librenms/librenms/pull/1776))
    - Fix MySQL statement in poller-service.py ([PR1794](https://github.com/librenms/librenms/pull/1794))
    - Fix upstart script for poller-service.py ([PR1812](https://github.com/librenms/librenms/pull/1812))
  - General:
    - Fixed path to defaults.inc.php in config.php.default ([PR1673](https://github.com/librenms/librenms/pull/1673))
    - Strip '::ffff:' from syslog input ([PR1734](https://github.com/librenms/librenms/pull/1734))
    - Fix RRA ([PR1791](https://github.com/librenms/librenms/pull/1791))

#### Improvements
  - WebUI Updates:
    - Added support for Google API key in Geo coding ([PR1594](https://github.com/librenms/librenms/pull/1594))
    - Added ability to updated storage % warning ([PR1613](https://github.com/librenms/librenms/pull/1613))
    - Updated eventlog page to allow filtering by type ([PR1623](https://github.com/librenms/librenms/pull/1623))
    - Hide logo and plugins text on smaller windows ([PR1624](https://github.com/librenms/librenms/pull/1624))
    - Added poller group name to poller groups table ([PR1634](https://github.com/librenms/librenms/pull/1634))
    - Updated Customers page to use Bootgrid ([PR1658](https://github.com/librenms/librenms/pull/1658))
    - Added basic Graylog integration support ([PR1665](https://github.com/librenms/librenms/pull/1665))
    - Added support for running under sub-directory ([PR1667](https://github.com/librenms/librenms/pull/1667))
    - Updated vis.js to latest version ([PR1708](https://github.com/librenms/librenms/pull/1708))
    - Added border on availability map ([PR1713](https://github.com/librenms/librenms/pull/1713))
    - Make new dashboard the default ([PR1719](https://github.com/librenms/librenms/pull/1719))
    - Rearrange about page ([PR1735](https://github.com/librenms/librenms/pull/1735),[PR1743](https://github.com/librenms/librenms/pull/1743))
    - Center/Cleanup graphs ([PR1736](https://github.com/librenms/librenms/pull/1736))
    - Added Hover-Effect on devices table ([PR1738](https://github.com/librenms/librenms/pull/1738))
    - Show Test-Transport result ([PR1777](https://github.com/librenms/librenms/pull/1777))
    - Add arrows to the network map ([PR1787](https://github.com/librenms/librenms/pull/1787))
    - Add errored ports to summary widget ([PR1788](https://github.com/librenms/librenms/pull/1788))
    - Show message if no Device-Groups exist ([PR1796](https://github.com/librenms/librenms/pull/1796))
    - Misc UI fixes (Titles, Headers, ...) ([PR1797](https://github.com/librenms/librenms/pull/1797),[PR1798](https://github.com/librenms/librenms/pull/1798),[PR1800](https://github.com/librenms/librenms/pull/1800),[PR1801](https://github.com/librenms/librenms/pull/1801),[PR1802](https://github.com/librenms/librenms/pull/1802),[PR1803](https://github.com/librenms/librenms/pull/1803),[PR1804](https://github.com/librenms/librenms/pull/1804),[PR1805](https://github.com/librenms/librenms/pull/1805))
    - Move packages to overview dropdown ([PR1810](https://github.com/librenms/librenms/pull/1810))
  - API Updates:
    - Improved billing support in API ([PR1599](https://github.com/librenms/librenms/pull/1599))
    - Extended support for list devices to support mac/ipv4 and ipv6 filtering ([PR1744](https://github.com/librenms/librenms/pull/1744))
  - Added detection for:
    - Perle Media convertors ([PR1607](https://github.com/librenms/librenms/pull/1607))
    - Mac OSX 10 ([PR1774](https://github.com/librenms/librenms/pull/1774))
  - Improved detection for:
    - Windows devices ([PR1639](https://github.com/librenms/librenms/pull/1639))
    - Zywall CPU, Version and Memory ([PR1660](https://github.com/librenms/librenms/pull/1660),[PR1784](https://github.com/librenms/librenms/pull/1784))
    - Added LLDP support for PBN devices ([PR1705](https://github.com/librenms/librenms/pull/1705))
    - Netgear GS110TP ([PR1751](https://github.com/librenms/librenms/pull/1751))
  - Additional Sensors:
    - Added Compressor state for PCOWEB ([PR1600](https://github.com/librenms/librenms/pull/1600))
    - Added dbm support for IOS-XR ([PR1661](https://github.com/librenms/librenms/pull/1661))
    - Added temperature support for DNOS ([PR1782](https://github.com/librenms/librenms/pull/1782))
  - Discovery / Poller:
    - Updated autodiscovery function to log new type ([PR1623](https://github.com/librenms/librenms/pull/1623))
    - Improve application polling ([PR1724](https://github.com/librenms/librenms/pull/1724))
    - Improve debug output ([PR1756](https://github.com/librenms/librenms/pull/1756))
  - DB:
    - Added MySQLi support ([PR1647](https://github.com/librenms/librenms/pull/1647))
  - Documentation:
    - Added docs on MySQL strict mode ([PR1635](https://github.com/librenms/librenms/pull/1635))
    - Updated billing docs to use librenms user in cron ([PR1676](https://github.com/librenms/librenms/pull/1676))
    - Updated LDAP docs to indicate php-ldap module needs installing ([PR1716](https://github.com/librenms/librenms/pull/1716))
    - Typo/Spellchecks ([PR1731](https://github.com/librenms/librenms/pull/1731),[PR1806](https://github.com/librenms/librenms/pull/1806))
    - Improved Alerting and Device-Groups ([PR1781](https://github.com/librenms/librenms/pull/1781))
  - Alerting:
    - Reformatted eventlog message to show state for alerts ([PR1685](https://github.com/librenms/librenms/pull/1685))
    - Add basic Pushbullet transport ([PR1721](https://github.com/librenms/librenms/pull/1721))
    - Allow custom titles ([PR1807](https://github.com/librenms/librenms/pull/1807))
  - General:
    - Added more debugging and checks to discovery-protocols ([PR1590](https://github.com/librenms/librenms/pull/1590))
    - Cleanup debug statements ([PR1725](https://github.com/librenms/librenms/pull/1725),[PR1737](https://github.com/librenms/librenms/pull/1737))

### July 2015

#### Bug fixes
  - WebUI:
    - Fixed API not functioning. ([PR1367](https://github.com/librenms/librenms/pull/1367))
    - Fixed API not storing alert rule names ([PR1372](https://github.com/librenms/librenms/pull/1372))
    - Fixed datetimepicker use ([PR1376](https://github.com/librenms/librenms/pull/1376))
    - Added 'running' status for BGP peers as up ([PR1412](https://github.com/librenms/librenms/pull/1412))
    - Fixed the remove search link in devices ([PR1413](https://github.com/librenms/librenms/pull/1413))
    - Fixed clicking anywhere in a search result will now take you to where you want ([PR1472](https://github.com/librenms/librenms/pull/1472))
    - Fixed inventory page not displaying results ([PR1488](https://github.com/librenms/librenms/pull/1488))
    - Fixed buggy alert templating in WebUI ([PR1527](https://github.com/librenms/librenms/pull/1527))
    - Fixed bug in creating api tokens in Firefox ([PR1530](https://github.com/librenms/librenms/pull/1530))
  - Discovery / Poller:
    - Do not allow master to rejoin itself. ([PR1377](https://github.com/librenms/librenms/pull/1377))
    - Fixed poller group query in discovery ([PR1433](https://github.com/librenms/librenms/pull/1433))
    - Fixed ARMv5 detection ([PR1522](https://github.com/librenms/librenms/pull/1522))
    - Fixed pfSense detection ([PR1567](https://github.com/librenms/librenms/pull/1567))
  - Sensors:
    - Fixed bug in EqualLogic sensors ([PR1513](https://github.com/librenms/librenms/pull/1513))
    - Fixed bug in DRAC voltage sensor ([PR1521](https://github.com/librenms/librenms/pull/1521))
    - Fixed bug in APC bank detection ([PR1560](https://github.com/librenms/librenms/pull/1560))
  - Documentation:
    - Fixed Nginx config file ([PR1389](https://github.com/librenms/librenms/pull/1389))
  - General:
    - Fixed a number of permission issues ([PR1411](https://github.com/librenms/librenms/pull/1411))

#### Improvements
  - Added detection for:
    - Meraki ([PR1402](https://github.com/librenms/librenms/pull/1402))
    - Brocade ([PR1404](https://github.com/librenms/librenms/pull/1404))
    - Dell iDrac ([PR1419](https://github.com/librenms/librenms/pull/1419),[PR1420](https://github.com/librenms/librenms/pull/1420),[PR1423](https://github.com/librenms/librenms/pull/1423),[PR1427](https://github.com/librenms/librenms/pull/1427))
    - Dell Networking OS ([PR1474](https://github.com/librenms/librenms/pull/1474))
    - Netonix ([PR1476](https://github.com/librenms/librenms/pull/1476))
    - IBM Tape Library ([PR1519](https://github.com/librenms/librenms/pull/1519),[PR1550](https://github.com/librenms/librenms/pull/1550))
    - Aerohive ([PR1546](https://github.com/librenms/librenms/pull/1546))
    - Cisco Voice Gateways ([PR1565](https://github.com/librenms/librenms/pull/1565))
  - Improved detection for:
    - RouterOS RB260GS ([PR1545](https://github.com/librenms/librenms/pull/1545))
    - Dell PowerConnect ([PR1452](https://github.com/librenms/librenms/pull/1452),[PR1517](https://github.com/librenms/librenms/pull/1517))
    - Brocade ([PR1548](https://github.com/librenms/librenms/pull/1548))
    - Rielo UPS ([PR1381](https://github.com/librenms/librenms/pull/1381))
    - Cisco IPSLAs ([PR1586](https://github.com/librenms/librenms/pull/1586))
  - Additional Sensors:
    - Added power, temperature and fan speed support for XOS ([PR1493](https://github.com/librenms/librenms/pull/1493),[PR1494](https://github.com/librenms/librenms/pull/1494),[PR1496](https://github.com/librenms/librenms/pull/1496))
  - WebUI Updates:
    - Added missing load and state icons ([PR1392](https://github.com/librenms/librenms/pull/1392))
    - Added ability to update users passwords in WebUI ([PR1440](https://github.com/librenms/librenms/pull/1440))
    - Default to two days performance data being shown ([PR1442](https://github.com/librenms/librenms/pull/1442))
    - Improved sensors page for mobile view ([PR1454](https://github.com/librenms/librenms/pull/1454))
    - Improvements to network map ([PR1455](https://github.com/librenms/librenms/pull/1455),[PR1470](https://github.com/librenms/librenms/pull/1470),[PR1486](https://github.com/librenms/librenms/pull/1486),[PR1528](https://github.com/librenms/librenms/pull/1528),[PR1557](https://github.com/librenms/librenms/pull/1557))
    - Added availability map ([PR1464](https://github.com/librenms/librenms/pull/1464))
    - Updated edit ports page to use Bootstrap ([PR1498](https://github.com/librenms/librenms/pull/1498))
    - Added new World Map and support for lat/lng lookup ([PR1501](https://github.com/librenms/librenms/pull/1501),[PR1552](https://github.com/librenms/librenms/pull/1552))
    - Added sysName to overview page for device ([PR1520](https://github.com/librenms/librenms/pull/1520))
    - Added New Overview dashboard uilising Widgets ([PR1523](https://github.com/librenms/librenms/pull/1523),[PR1580](https://github.com/librenms/librenms/pull/1580))
    - Added new config option to disable Device groups ([PR1569](https://github.com/librenms/librenms/pull/1569))
  - Discovery / Poller Updates:
    - Updated discovery of IP based devices ([PR1406](https://github.com/librenms/librenms/pull/1406))
    - Added using cronic for poller-wrapper.py to allow cron to send emails ([PR1408](https://github.com/librenms/librenms/pull/1408),[PR1531](https://github.com/librenms/librenms/pull/1531))
    - Updated Cisco MIBs to latest versions ([PR1436](https://github.com/librenms/librenms/pull/1436))
    - Improve performance of unix-agent processes DB code ([PR1447](https://github.com/librenms/librenms/pull/1447),[PR1460](https://github.com/librenms/librenms/pull/1460))
    - Added BGP discovery code ([PR1414](https://github.com/librenms/librenms/pull/1414))
    - Use snmpEngineTime as a fallback to uptime ([PR1477](https://github.com/librenms/librenms/pull/1477))
    - Added fallback support for devices not reporting ifAlias ([PR1479](https://github.com/librenms/librenms/pull/1479))
    - Git pull and schema updates will now pause if InnoDB buffers overused ([PR1563](https://github.com/librenms/librenms/pull/1563))
  - Documentation:
    - Updated Unix-Agent docs to use LibreNMS repo for scripts ([PR1568](https://github.com/librenms/librenms/pull/1568),[PR1570](https://github.com/librenms/librenms/pull/1570),[PR1573](https://github.com/librenms/librenms/pull/1573))
    - Added info on using MariaDB ([PR1585](https://github.com/librenms/librenms/pull/1585))
  - Alerting:
    - Added Boxcar (www.boxcar.io) transport for alerting ([PR1481](https://github.com/librenms/librenms/pull/1481))
    - Removed old alerting code ([PR1581](https://github.com/librenms/librenms/pull/1581))
  - General:
    - Code cleanup and formatting ([PR1415](https://github.com/librenms/librenms/pull/1415),[PR1416](https://github.com/librenms/librenms/pull/1416),[PR1431](https://github.com/librenms/librenms/pull/1431),[PR1434](https://github.com/librenms/librenms/pull/1434),[PR1439](https://github.com/librenms/librenms/pull/1439),[PR1444](https://github.com/librenms/librenms/pull/1444),[PR1450](https://github.com/librenms/librenms/pull/1450))
    - Added support for CollectD flush ([PR1463](https://github.com/librenms/librenms/pull/1463))
    - Added support for LDAP pure DN member groups ([PR1516](https://github.com/librenms/librenms/pull/1516))
    - Updated validate.php to check for distributed poller setup issues ([PR1526](https://github.com/librenms/librenms/pull/1526))
    - Improved service check support ([PR1385](https://github.com/librenms/librenms/pull/1385),[PR1386](https://github.com/librenms/librenms/pull/1386),[PR1387](https://github.com/librenms/librenms/pull/1387),[PR1388](https://github.com/librenms/librenms/pull/1388))
    - Added SNMP Scanner to discover devices within subnets and docs ([PR1577](https://github.com/librenms/librenms/pull/1577))

### June 2015

#### Bug fixes
  - Fixed services list SQL issue ([PR1181](https://github.com/librenms/librenms/pull/1181))
  - Fixed negative values for storage when volume is > 2TB ([PR1185](https://github.com/librenms/librenms/pull/1185))
  - Fixed visual display for input fields on /syslog/ ([PR1193](https://github.com/librenms/librenms/pull/1193))
  - Fixed fatal php issue in shoutcast.php ([PR1203](https://github.com/librenms/librenms/pull/1203))
  - Fixed percent bars in /bills/ ([PR1208](https://github.com/librenms/librenms/pull/1208))
  - Fixed item count in memory and storage pages ([PR1210](https://github.com/librenms/librenms/pull/1210))
  - Fixed syslog not loading ([PR1219](https://github.com/librenms/librenms/pull/1219))
  - Fixed fatal on reload in IRC bot ([PR1218](https://github.com/librenms/librenms/pull/1218))
  - Alter Windows CPU description when unknown ([PR1226](https://github.com/librenms/librenms/pull/1226))
  - Fixed rfc1628 current calculation ([PR1256](https://github.com/librenms/librenms/pull/1256))
  - Fixed alert mapping not working ([PR1280](https://github.com/librenms/librenms/pull/1280))
  - Fixed legend ifLabels ([PR1296](https://github.com/librenms/librenms/pull/1296))
  - Fixed bug causing map to not load when stale link data was present ([PR1297](https://github.com/librenms/librenms/pull/1297))
  - Fixed javascript issue preventing removal of alert rules ([PR1312](https://github.com/librenms/librenms/pull/1312))
  - Fixed removal of IPs before ports are deleted ([PR1329](https://github.com/librenms/librenms/pull/1329))
  - Fixed JS issue when removing ports from bills ([PR1330](https://github.com/librenms/librenms/pull/1330))
  - Fixed adding --daemon a second time to collectd Graphs ([PR1342](https://github.com/librenms/librenms/pull/1342))
  - Fixed CollectD DS names ([PR1347](https://github.com/librenms/librenms/pull/1347),[PR1349](https://github.com/librenms/librenms/pull/1349),[PR1368](https://github.com/librenms/librenms/pull/1368))
  - Fixed graphing issues when rrd contains special chars ([PR1350](https://github.com/librenms/librenms/pull/1350))
  - Fixed regex for device groups ([PR1359](https://github.com/librenms/librenms/pull/1359))
  - Added HOST-RESOURCES-MIB into Synology detection (RP1360)
  - Fix health page graphs showing the first graph for all ([PR1363](https://github.com/librenms/librenms/pull/1363))

#### Improvements
  - Updated Syslog docs to include syslog-ng 3.5.1 updates ([PR1171](https://github.com/librenms/librenms/pull/1171))
  - Added Pushover Transport ([PR1180](https://github.com/librenms/librenms/pull/1180), [PR1191](https://github.com/librenms/librenms/pull/1191))
  - Converted processors and memory table to bootgrid ([PR1188](https://github.com/librenms/librenms/pull/1188), [PR1192](https://github.com/librenms/librenms/pull/1192))
  - Issued alerts and transport now logged to eventlog ([PR1194](https://github.com/librenms/librenms/pull/1194))
  - Added basic support for Enterasys devices ([PR1211](https://github.com/librenms/librenms/pull/1211))
  - Added dynamic config to configure alerting ([PR1153](https://github.com/librenms/librenms/pull/1153))
  - Added basic support for Multimatic USV ([PR1215](https://github.com/librenms/librenms/pull/1215))
  - Disabled and ignored ports no longer show by default on /ports/ ([PR1228](https://github.com/librenms/librenms/pull/1228),[PR1301](https://github.com/librenms/librenms/pull/1301))
  - Added additional graphs to menu on devices page ([PR1229](https://github.com/librenms/librenms/pull/1229))
  - Added Docs on configuring Globe front page ([PR1231](https://github.com/librenms/librenms/pull/1231))
  - Added robots.txt to html folder to disallow indexing ([PR1234](https://github.com/librenms/librenms/pull/1234))
  - Added additional support for Synology units ([PR1235](https://github.com/librenms/librenms/pull/1235),[PR1244](https://github.com/librenms/librenms/pull/1244),[PR1269](https://github.com/librenms/librenms/pull/1269))
  - Added IP check to autodiscovery code ([PR1248](https://github.com/librenms/librenms/pull/1248))
  - Updated HP ProCurve detection ([PR1249](https://github.com/librenms/librenms/pull/1249))
  - Added basic detection for Alcatel-Lucent OmniSwitch ([PR1253](https://github.com/librenms/librenms/pull/1253), [PR1282](https://github.com/librenms/librenms/pull/1282))
  - Added additional metrics for rfc1628 UPS ([PR1258](https://github.com/librenms/librenms/pull/1258), [PR1268](https://github.com/librenms/librenms/pull/1268))
  - Allow multiple discovery modules to be specified on command line ([PR1263](https://github.com/librenms/librenms/pull/1263))
  - Updated docs on using libvirt ([PR1264](https://github.com/librenms/librenms/pull/1264))
  - Updated Ruckus detection ([PR1267](https://github.com/librenms/librenms/pull/1267))
  - Initial release of MIB based polling ([PR1273](https://github.com/librenms/librenms/pull/1273))
  - Added support for CISCO-BGP4-MIB ([PR1184](https://github.com/librenms/librenms/pull/1184))
  - Added support for Dell EqualLogic units ([PR1283](https://github.com/librenms/librenms/pull/1283),[PR1309](https://github.com/librenms/librenms/pull/1309))
  - Added logging of success/ failure for alert transports ([PR1286](https://github.com/librenms/librenms/pull/1286))
  - Updated VyOS detection ([PR1299](https://github.com/librenms/librenms/pull/1299))
  - Added primary serial number detection for Cisco units ([PR1300](https://github.com/librenms/librenms/pull/1300))
  - Added support for specifying MySQL port number in config.php ([PR1302](https://github.com/librenms/librenms/pull/1302))
  - Updated alert subject to use rule name not ID ([PR1310](https://github.com/librenms/librenms/pull/1310))
  - Added macro %macros.sensor ([PR1311](https://github.com/librenms/librenms/pull/1311))
  - Added WebUI support for Pushover ([PR1313](https://github.com/librenms/librenms/pull/1313))
  - Updated path check for Oxidized config ([PR1316](https://github.com/librenms/librenms/pull/1316))
  - Added Multimatic UPS to rfc1628 detection ([PR1317](https://github.com/librenms/librenms/pull/1317))
  - Added timeout for Unix agent ([PR1319](https://github.com/librenms/librenms/pull/1319))
  - Added support for a poller to use more than one poller group ([PR1323](https://github.com/librenms/librenms/pull/1323))
  - Added ability to use Plugins on device overview page ([PR1325](https://github.com/librenms/librenms/pull/1325))
  - Added latency loss/avg/max/min results to DB and Graph ([PR1326](https://github.com/librenms/librenms/pull/1326))
  - Added recording of device down (snmp/icmp) ([PR1326](https://github.com/librenms/librenms/pull/1326))
  - Added debugging output for when invalid SNMPv3 options used ([PR1331](https://github.com/librenms/librenms/pull/1331))
  - Added load and state output to device overview page ([PR1333](https://github.com/librenms/librenms/pull/1333))
  - Added load sensors to RFC1628 Devices ([PR1336](https://github.com/librenms/librenms/pull/1336))
  - Added support for WebPower Pro II UPS Cards ([PR1338](https://github.com/librenms/librenms/pull/1338))
  - No longer rewrite server-status in .htaccess ([PR1339](https://github.com/librenms/librenms/pull/1339))
  - Added docs for setting up Service extensions ([PR1354](https://github.com/librenms/librenms/pull/1354))
  - Added additional info from pfsense devices ([PR1356](https://github.com/librenms/librenms/pull/1356))

### May 2015

#### Bug fixes
  - Updated nested addHosts to use variables passed ([PR889](https://github.com/librenms/librenms/pull/889))
  - Fixed map drawing issue ([PR907](https://github.com/librenms/librenms/pull/907))
  - Fixed sensors issue where APC load sensors overwrote current ([PR912](https://github.com/librenms/librenms/pull/912))
  - Fixed devices location filtering ([PR917](https://github.com/librenms/librenms/pull/917), [PR921](https://github.com/librenms/librenms/pull/921))
  - Minor fix to rrdcached_dir handling ([PR940](https://github.com/librenms/librenms/pull/940))
  - Now set defaults for AddHost on XDP discovery ([PR941](https://github.com/librenms/librenms/pull/941))
  - Fix web installer to generate config correctly if possible ([PR954](https://github.com/librenms/librenms/pull/954))
  - Fix inverse option for graphs ([PR955](https://github.com/librenms/librenms/pull/955))
  - Fix ifAlias parsing ([PR960](https://github.com/librenms/librenms/pull/960))
  - Rewrote rrdtool_escape to fix graph formatting issues ([PR961](https://github.com/librenms/librenms/pull/961), [PR965](https://github.com/librenms/librenms/pull/965))
  - Updated ports check to include ifAdminStatus ([PR962](https://github.com/librenms/librenms/pull/962))
  - Fixed custom sensors high / low being overwritten on discovery ([PR977](https://github.com/librenms/librenms/pull/977))
  - Fixed APC powerbar phase limit discovery ([PR981](https://github.com/librenms/librenms/pull/981))
  - Fix for 4 digit cpu% for Datacom ([PR984](https://github.com/librenms/librenms/pull/984))
  - Fix SQL query for restricted users in /devices/ ([PR990](https://github.com/librenms/librenms/pull/990))
  - Fix for post-formatting time-macros ([PR1006](https://github.com/librenms/librenms/pull/1006))
  - Honour disabling alerts for hosts ([PR1051](https://github.com/librenms/librenms/pull/1051))
  - Make OSPF and ARP discovery independent xDP ([PR1053](https://github.com/librenms/librenms/pull/1053))
  - Fixed ospf_nbrs lookup to use device_id ([PR1088](https://github.com/librenms/librenms/pull/1088))
  - Removed trailing / from some urls ([PR1089](https://github.com/librenms/librenms/pull/1089) / [PR1100](https://github.com/librenms/librenms/pull/1100))
  - Fix to device search for Device type and location ([PR1101](https://github.com/librenms/librenms/pull/1101))
  - Stop non-device boxes on overview appearing when device is down ([PR1106](https://github.com/librenms/librenms/pull/1106))
  - Fixed nfsen directory checks ([PR1123](https://github.com/librenms/librenms/pull/1123))
  - Removed lower limit for sensor graphs so negative values show ([PR1124](https://github.com/librenms/librenms/pull/1124))
  - Added fallback for poller_group if empty when adding devices ([PR1126](https://github.com/librenms/librenms/pull/1126))
  - Fixed processor graphs tooltips ([PR1127](https://github.com/librenms/librenms/pull/1127))
  - Fixed /poll-log/ count ([PR1130](https://github.com/librenms/librenms/pull/1130))
  - Fixed ARP search graph type reference ([PR1131](https://github.com/librenms/librenms/pull/1131))
  - Fixed showing state=X in device list ([PR1144](https://github.com/librenms/librenms/pull/1144))
  - Removed ability for demo user to delete users ([PR1151](https://github.com/librenms/librenms/pull/1151))
  - Fixed user / port perms for top X front page boxes ([PR1156](https://github.com/librenms/librenms/pull/1156))
  - Fixed truncating UTF-8 strings ([PR1166](https://github.com/librenms/librenms/pull/1166))
  - Fixed attaching templates due to JS issue ([PR1167](https://github.com/librenms/librenms/pull/1167))

#### Improvements
  - Added loading bar to top nav ([PR893](https://github.com/librenms/librenms/pull/893))
  - Added load and current for APC units ([PR888](https://github.com/librenms/librenms/pull/888))
  - Improved web installer ([PR887](https://github.com/librenms/librenms/pull/887))
  - Updated alerts status box ([PR875](https://github.com/librenms/librenms/pull/875))
  - Updated syslog page ([PR862](https://github.com/librenms/librenms/pull/862))
  - Added temperature polling for IBM Flexsystem ([PR894](https://github.com/librenms/librenms/pull/894))
  - Updated typeahead libraries and relevant forms ([PR882](https://github.com/librenms/librenms/pull/882))
  - Added docs showing configuration options and how to use them ([PR910](https://github.com/librenms/librenms/pull/910))
  - Added docs on discovery / poller and how to debug ([PR911](https://github.com/librenms/librenms/pull/911))
  - Updated docs for MySQL / Nginx / Bind use in Unix agent ([PR916](https://github.com/librenms/librenms/pull/916))
  - Update development docs ([PR919](https://github.com/librenms/librenms/pull/919))
  - Updated install docs to advise about whitespace in config.php ([PR920](https://github.com/librenms/librenms/pull/920))
  - Added docs on authentication modules ([PR922](https://github.com/librenms/librenms/pull/922))
  - Added support for Oxidized config archival ([PR927](https://github.com/librenms/librenms/pull/927))
  - Added API to feed devices to Oxidized ([PR928](https://github.com/librenms/librenms/pull/928))
  - Added support for per OS bad_iftype, bad_if and bad_if_regexp ([PR930](https://github.com/librenms/librenms/pull/930))
  - Enable alerting on tables with relative / indirect glues ([PR932](https://github.com/librenms/librenms/pull/932))
  - Added bills support in rulesuggest and alert system ([PR934](https://github.com/librenms/librenms/pull/934))
  - Added detection for Sentry Smart CDU ([PR938](https://github.com/librenms/librenms/pull/938))
  - Added basic detection for Netgear devices ([PR942](https://github.com/librenms/librenms/pull/942))
  - addhost.php now uses distributed_poller_group config if set ([PR944](https://github.com/librenms/librenms/pull/944))
  - Added port rewrite function ([PR946](https://github.com/librenms/librenms/pull/946))
  - Added basic detection for Ubiquiti Edgeswitch ([PR947](https://github.com/librenms/librenms/pull/947))
  - Added support for retrieving email address from LDAP ([PR949](https://github.com/librenms/librenms/pull/949))
  - Updated JunOS logo ([PR952](https://github.com/librenms/librenms/pull/952))
  - Add aggregates on multi_bits_separate graphs ([PR956](https://github.com/librenms/librenms/pull/956))
  - Fix port name issue for recent snmp versions on Linux ([PR957](https://github.com/librenms/librenms/pull/957))
  - Added support for quick access to devices via url ([PR958](https://github.com/librenms/librenms/pull/958))
  - Added work around for PHP creating zombie processes on certain distros ([PR959](https://github.com/librenms/librenms/pull/959))
  - Added detection support for NetApp + disks + temperature ([PR967](https://github.com/librenms/librenms/pull/967), [PR971](https://github.com/librenms/librenms/pull/971))
  - Define defaults for graphs ([PR968](https://github.com/librenms/librenms/pull/968))
  - Added docs for migrating from Observium ([PR974](https://github.com/librenms/librenms/pull/974))
  - Added iLo temperature support ([PR982](https://github.com/librenms/librenms/pull/982))
  - Added disk temperature for Synology DSM ([PR986](https://github.com/librenms/librenms/pull/986))
  - Added ICMP, TLS/SSL and Domain expiry service checks ([PR987](https://github.com/librenms/librenms/pull/987), [PR1040](https://github.com/librenms/librenms/pull/1040), [PR1041](https://github.com/librenms/librenms/pull/1041))
  - Added IPMI detection ([PR988](https://github.com/librenms/librenms/pull/988))
  - Mikrotik MIB update ([PR991](https://github.com/librenms/librenms/pull/991))
  - Set better timeperiod for caching graphs ([PR992](https://github.com/librenms/librenms/pull/992))
  - Added config option to disable port relationship in ports list ([PR996](https://github.com/librenms/librenms/pull/996))
  - Added support for custom customer description parse ([PR998](https://github.com/librenms/librenms/pull/998))
  - Added hardware and MySQL version stats to callback ([PR999](https://github.com/librenms/librenms/pull/999))
  - Added support for alerting to PagerDuty ([PR1004](https://github.com/librenms/librenms/pull/1004))
  - Now send ack notifications for alerts that are acked ([PR1008](https://github.com/librenms/librenms/pull/1008))
  - Updated contributing docs and added placeholder ([PR1024](https://github.com/librenms/librenms/pull/1024), [PR1025](https://github.com/librenms/librenms/pull/1025))
  - Updated globe.php overview page with updated map support ([PR1029](https://github.com/librenms/librenms/pull/1029))
  - Converted storage page to use Bootgrid ([PR1030](https://github.com/librenms/librenms/pull/1030))
  - Added basic FibreHome detection ([PR1031](https://github.com/librenms/librenms/pull/1031))
  - Show details of alerts in alert log ([PR1043](https://github.com/librenms/librenms/pull/1043))
  - Allow a user-defined windows to add tolerance for alerting ([PR1044](https://github.com/librenms/librenms/pull/1044))
  - Added inlet support for Raritan PX iPDU ([PR1045](https://github.com/librenms/librenms/pull/1045))
  - Updated MIBS for Cisco SB ([PR1058](https://github.com/librenms/librenms/pull/1058))
  - Added error checking for build-base on install ([PR1059](https://github.com/librenms/librenms/pull/1059))
  - Added fan and raid state for Dell OpenManage ([PR1062](https://github.com/librenms/librenms/pull/1062))
  - Updated MIBS for Ruckus ZoneDirectors ([PR1067](https://github.com/librenms/librenms/pull/1067))
  - Added check for ./rename.php ([PR1069](https://github.com/librenms/librenms/pull/1069))
  - Added install instructions to use librenms user ([PR1071](https://github.com/librenms/librenms/pull/1071))
  - Honour sysContact over riding for alerts ([PR1073](https://github.com/librenms/librenms/pull/1073))
  - Added services page for adding/deleting and editing services ([PR1076](https://github.com/librenms/librenms/pull/1076))
  - Added more support for Mikrotik devices ([PR1080](https://github.com/librenms/librenms/pull/1080))
  - Added better detection for Cisco ASA 5585-SSP40 ([PR1082](https://github.com/librenms/librenms/pull/1082))
  - Added CPU dataplane support for JunOS ([PR1086](https://github.com/librenms/librenms/pull/1086))
  - Removed requirement for hostnames on add device ([PR1087](https://github.com/librenms/librenms/pull/1087))
  - Added config option to exclude sysContact from alerts ([PR1093](https://github.com/librenms/librenms/pull/1093))
  - Added config option to regenerate contacts on alerts ([PR1109](https://github.com/librenms/librenms/pull/1109))
  - Added validation tool to help fault find issues with installs ([PR1112](https://github.com/librenms/librenms/pull/1112))
  - Added CPU support for EdgeOS ([PR1114](https://github.com/librenms/librenms/pull/1114))
  - Added ability to customise transit/peering/core descriptions ([PR1125](https://github.com/librenms/librenms/pull/1125))
  - Show ifName in ARP search if devices are set to use this ([PR1133](https://github.com/librenms/librenms/pull/1133))
  - Added FibreHome CPU and Mempool support ([PR1134](https://github.com/librenms/librenms/pull/1134))
  - Added config options for region and resolution on globe map ([PR1137](https://github.com/librenms/librenms/pull/1137))
  - Added RRDCached example docs ([PR1148](https://github.com/librenms/librenms/pull/1148))
  - Updated support for additional NetBotz models ([PR1152](https://github.com/librenms/librenms/pull/1152))
  - Updated /iftype/ page to include speed/circuit/notes ([PR1155](https://github.com/librenms/librenms/pull/1155))
  - Added detection for PowerConnect 55XX devices ([PR1165](https://github.com/librenms/librenms/pull/1165))

### Apr 2015

####Bug fixes
  - Fixed ack of worse/better alerts ([PR720](https://github.com/librenms/librenms/pull/720))
  - Fixed ORIG_PATH_INFO warnings ([PR727](https://github.com/librenms/librenms/pull/727))
  - Added missing CPU id for Cisco SB ([PR744](https://github.com/librenms/librenms/pull/744))
  - Changed Processors table name to lower case in processors discovery ([PR751](https://github.com/librenms/librenms/pull/751))
  - Fixed alerts path issue ([PR756](https://github.com/librenms/librenms/pull/756), [PR760](https://github.com/librenms/librenms/pull/760))
  - Suppress further port alerts when interface goes down ([PR745](https://github.com/librenms/librenms/pull/745))
  - Fixed login so redirects via 303 when POST data sent ([PR775](https://github.com/librenms/librenms/pull/775))
  - Fixed missing link to errored or ignored ports ([PR787](https://github.com/librenms/librenms/pull/787))
  - Updated alert log query for performance improvements ([PR783](https://github.com/librenms/librenms/pull/783))
  - Honour alert_rules.disabled field ([PR784](https://github.com/librenms/librenms/pull/784))
  - Stop page debug if user not logged in ([PR785](https://github.com/librenms/librenms/pull/785))
  - Added text filtering for new tables ([PR797](https://github.com/librenms/librenms/pull/797))
  - Fixed VMWare VM detection + hardware / serial support ([PR799](https://github.com/librenms/librenms/pull/799))
  - Fix links from /health/processor ([PR810](https://github.com/librenms/librenms/pull/810))
  - Hide divider if no plugins installed ([PR811](https://github.com/librenms/librenms/pull/811))
  - Added Nginx fix for using debug option ([PR823](https://github.com/librenms/librenms/pull/823))
  - Bug fixes for device groups SQL ([PR840](https://github.com/librenms/librenms/pull/840))
  - Fixed path issue when using rrdcached ([PR839](https://github.com/librenms/librenms/pull/839))
  - Fixed JS issues when deleting alert maps / poller groups / device groups ([PR846](https://github.com/librenms/librenms/pull/846),[PR848](https://github.com/librenms/librenms/pull/848),[PR877](https://github.com/librenms/librenms/pull/877))
  - Fixed links and popover for /health/metric=storage/ ([PR847](https://github.com/librenms/librenms/pull/847))
  - Fixed lots of user permission issues ([PR855](https://github.com/librenms/librenms/pull/855))
  - Fixed search ip / arp / mac pages ([PR845](https://github.com/librenms/librenms/pull/845))
  - Added missing charge icon ([PR878](https://github.com/librenms/librenms/pull/878))

####Improvements
  - New theme support added (light,dark and mono) ([PR682](https://github.com/librenms/librenms/pull/682),[PR683](https://github.com/librenms/librenms/pull/683),[PR701](https://github.com/librenms/librenms/pull/701))
  - Tables being converted to Jquery Bootgrid ([PR693](https://github.com/librenms/librenms/pull/693),[PR706](https://github.com/librenms/librenms/pull/706),[PR716](https://github.com/librenms/librenms/pull/716))
  - Detect Cisco ASA Hardware and OS Version ([PR708](https://github.com/librenms/librenms/pull/708))
  - Update LDAP support ([PR707](https://github.com/librenms/librenms/pull/707))
  - Updated APC powernet MIB ([PR713](https://github.com/librenms/librenms/pull/713))
  - Update to Foritgate support ([PR709](https://github.com/librenms/librenms/pull/709))
  - Added support for UBNT AirOS and AirFibre ([PR721](https://github.com/librenms/librenms/pull/721),[PR730](https://github.com/librenms/librenms/pull/730),[PR731](https://github.com/librenms/librenms/pull/731))
  - Added support device groups + alerts to be mapped to devices or groups ([PR722](https://github.com/librenms/librenms/pull/722))
  - Added basic Cambium support ([PR738](https://github.com/librenms/librenms/pull/738))
  - Added basic F5 support ([PR670](https://github.com/librenms/librenms/pull/670))
  - Shorten interface names on map ([PR752](https://github.com/librenms/librenms/pull/752))
  - Added PowerCode support ([PR762](https://github.com/librenms/librenms/pull/762))
  - Added Autodiscovery via OSPF ([PR772](https://github.com/librenms/librenms/pull/772))
  - Added visual graph of alert log ([PR777](https://github.com/librenms/librenms/pull/777), [PR809](https://github.com/librenms/librenms/pull/809))
  - Added Callback system to send anonymous stats ([PR768](https://github.com/librenms/librenms/pull/768))
  - More tables converted to use bootgrid ([PR729](https://github.com/librenms/librenms/pull/729), [PR761](https://github.com/librenms/librenms/pull/761))
  - New Global Cache to store common queries added ([PR780](https://github.com/librenms/librenms/pull/780))
  - Added proxy support for submitting stats ([PR791](https://github.com/librenms/librenms/pull/791))
  - Minor APC Polling change ([PR800](https://github.com/librenms/librenms/pull/800))
  - Updated to HP switch detection ([PR802](https://github.com/librenms/librenms/pull/802))
  - Added Datacom basic detection ([PR816](https://github.com/librenms/librenms/pull/816))
  - Updated Cisco detection ([PR815](https://github.com/librenms/librenms/pull/815))
  - Added CSV export system + ability to export ports ([PR818](https://github.com/librenms/librenms/pull/818))
  - Added basic detection for PacketLogic devices ([PR773](https://github.com/librenms/librenms/pull/773))
  - Added fallback support for IBM switches for Serial / Version ([PR822](https://github.com/librenms/librenms/pull/822))
  - Added Juniper Inventory support ([PR825](https://github.com/librenms/librenms/pull/825))
  - Sharpen graphs produced ([PR826](https://github.com/librenms/librenms/pull/826))
  - Updated map to show device overview graphs and port graphs ([PR826](https://github.com/librenms/librenms/pull/826))
  - Added hostname to API call for list_alerts ([PR834](https://github.com/librenms/librenms/pull/834))
  - Added ability to schedule maintenance ([PR835](https://github.com/librenms/librenms/pull/835),[PR841](https://github.com/librenms/librenms/pull/841))
  - Added ability to expand alert triggers for more details ([PR857](https://github.com/librenms/librenms/pull/857))
  - Added support for XTM/FBX Watchguard devices ([PR849](https://github.com/librenms/librenms/pull/849))
  - Updated Juniper MIBS and hardware rewrite ([PR838](https://github.com/librenms/librenms/pull/838))
  - Updated OpenBSD detection ([PR860](https://github.com/librenms/librenms/pull/860))
  - Added Macro support for alerting system ([PR863](https://github.com/librenms/librenms/pull/863))
  - Added support for tcp connections on rrdcached ([PR866](https://github.com/librenms/librenms/pull/866))
  - Added config option to enable / disable mouseover graphs ([PR873](https://github.com/librenms/librenms/pull/873))
  - General cleanup of files / folders permissions ([PR874](https://github.com/librenms/librenms/pull/874))
  - Added window size detection for map ([PR884](https://github.com/librenms/librenms/pull/884))
  - Added text to let users know refresh is disabled ([PR883](https://github.com/librenms/librenms/pull/883))

### Mar 2015

####Bug fixes
  - Updates to alert rules split ([PR550](https://github.com/librenms/librenms/pull/550))
  - Updated get_graphs() for API to resolve graph names ([PR613](https://github.com/librenms/librenms/pull/613))
  - Fixed use of REMOTE_ADDR to use X_FORWARDED_FOR if available ([PR620](https://github.com/librenms/librenms/pull/620))
  - Added yocto support from entPhySensorScale ([PR632](https://github.com/librenms/librenms/pull/632))
  - Eventlog search fixed ([PR644](https://github.com/librenms/librenms/pull/644))
  - Added missing OS discovery to default list ([PR660](https://github.com/librenms/librenms/pull/660))
  - Fixed logging issue when description of a port was removed ([PR673](https://github.com/librenms/librenms/pull/673))
  - Fixed logging issue when ports changed status ([PR675](https://github.com/librenms/librenms/pull/675))
  - Shortened interface names for graph display ([PR676](https://github.com/librenms/librenms/pull/676))

####Improvements
  - Visual updates to alert logs ([PR541](https://github.com/librenms/librenms/pull/541))
  - Added temperature support for APC AC units ([PR545](https://github.com/librenms/librenms/pull/545))
  - Added ability to pause and resume page refresh ([PR557](https://github.com/librenms/librenms/pull/557))
  - Added polling support for NXOS ([PR562](https://github.com/librenms/librenms/pull/562))
  - Added discovery support for 3Com switches ([PR568](https://github.com/librenms/librenms/pull/568))
  - Updated Comware support ([PR583](https://github.com/librenms/librenms/pull/583))
  - Added new logo ([PR584](https://github.com/librenms/librenms/pull/584))
  - Added dynamic removal of device data when removing device ([PR592](https://github.com/librenms/librenms/pull/592))
  - Updated alerting to use fifo ([PR607](https://github.com/librenms/librenms/pull/607))
  - Added distributed poller support ([PR609](https://github.com/librenms/librenms/pull/609) and [PR610](https://github.com/librenms/librenms/pull/610))
  - Added PowerConnect 55xx ([PR635](https://github.com/librenms/librenms/pull/635))
  - Added inventory API endpoint ([PR640](https://github.com/librenms/librenms/pull/640))
  - Added serial number detection for ASA firewalls ([PR642](https://github.com/librenms/librenms/pull/642))
  - Added missing MKTree library for inventory support ([PR646](https://github.com/librenms/librenms/pull/646))
  - Added support for exporting Alert logs to PDF ([PR653](https://github.com/librenms/librenms/pull/653))
  - Added basic Ubiquiti support ([PR659](https://github.com/librenms/librenms/pull/659))
  - Numerous docs update ([PR662](https://github.com/librenms/librenms/pull/662), [PR663](https://github.com/librenms/librenms/pull/663), [PR677](https://github.com/librenms/librenms/pull/677), [PR694](https://github.com/librenms/librenms/pull/694))
  - Added Polling information page ([PR664](https://github.com/librenms/librenms/pull/664))
  - Added HipChat notification support ([PR669](https://github.com/librenms/librenms/pull/669))
  - Implemented Jquery Bootgrid support ([PR671](https://github.com/librenms/librenms/pull/671))
  - Added new map to show xDP discovered links and devices ([PR679](https://github.com/librenms/librenms/pull/679) + [PR680](https://github.com/librenms/librenms/pull/680))

###Feb 2015

####Bug fixes
 - Removed header redirect causing page load delays ([PR436](https://github.com/librenms/librenms/pull/436))
 - Fixed stale alerting data ([PR475](https://github.com/librenms/librenms/pull/475))
 - Fixed api call for port stats to use device_id / hostname ([PR478](https://github.com/librenms/librenms/pull/478))
 - Work started on ensuring MySQL strict mode is supported ([PR521](https://github.com/librenms/librenms/pull/521))

####Improvements
 - Added support for Cisco Wireless Controllers ([PR422](https://github.com/librenms/librenms/pull/422))
 - Updated IRC Bot to support alerting system ([PR434](https://github.com/librenms/librenms/pull/434))
 - Added new message box to alert when a device hasn't polled for 15 minutes or more ([PR435](https://github.com/librenms/librenms/pull/435))
 - Added quick links on device list page to quickly access common pages ([PR440](https://github.com/librenms/librenms/pull/440))
 - Alerting docs updated to cover new features ([PR446](https://github.com/librenms/librenms/pull/446))
 - IBM NOS Support added ([PR454](https://github.com/librenms/librenms/pull/454))
 - Added basic Barracuda Loadbalancer support ([PR456](https://github.com/librenms/librenms/pull/456))
 - Small change to the search results to add port desc / alias ([PR457](https://github.com/librenms/librenms/pull/457))
 - Added Device sub menu to access devices category directly ([PR465](https://github.com/librenms/librenms/pull/465))
 - Added basic Ruckus Wireless support ([PR466](https://github.com/librenms/librenms/pull/466))
 - Added support for a demo user ([PR471](https://github.com/librenms/librenms/pull/471))
 - Many small visual updates
 - Added additional support for Cisco SB devices ([PR487](https://github.com/librenms/librenms/pull/487))
 - Added support to default home page for printing alerts ([PR488](https://github.com/librenms/librenms/pull/488))
 - Tidied up Alert menubar into sub menu ([PR489](https://github.com/librenms/librenms/pull/489))
 - Added historical alerts page ([PR495](https://github.com/librenms/librenms/pull/495))
 - Added battery charge monitoring for ([PR519](https://github.com/librenms/librenms/pull/519))
 - Added Slack support for alert system ([PR525](https://github.com/librenms/librenms/pull/525))
 - Added new debug for php / sql option to page footer ([PR484](https://github.com/librenms/librenms/pull/484))

###Jan 2015

####Bug fixes
 - Reverted chmod to make poller.php executable again ([PR394](https://github.com/librenms/librenms/pull/394))
 - Fixed duplicate port listing ([PR396](https://github.com/librenms/librenms/pull/396))
 - Fixed create bill from port page ([PR404](https://github.com/librenms/librenms/pull/404))
 - Fixed autodiscovery to use $config['mydomain'] correctly ([PR423](https://github.com/librenms/librenms/pull/423))
 - Fixed mute bug for alerts ([PR428](https://github.com/librenms/librenms/pull/428))

####Improvements
 - Updated login page visually ([PR391](https://github.com/librenms/librenms/pull/391))
 - Added Hikvision support ([PR393](https://github.com/librenms/librenms/pull/393))
 - Added ability to search for packages using unix agent ([PR395](https://github.com/librenms/librenms/pull/395))
 - Updated ifAlias support for varying distributions ([PR398](https://github.com/librenms/librenms/pull/398))
 - Updated visually Global Settings page ([PR401](https://github.com/librenms/librenms/pull/401))
 - Added missing default nginx graphs ([PR403](https://github.com/librenms/librenms/pull/403))
 - Updated check_mk_agent to latest git version ([PR409](https://github.com/librenms/librenms/pull/409))
 - Added support for recording process list with unix agent ([PR410](https://github.com/librenms/librenms/pull/410))
 - Added support for named/bind9/TinyDNS application using unix agent ([PR413](https://github.com/librenms/librenms/pull/413), [PR416](https://github.com/librenms/librenms/pull/416))
 - About page tidied up ([PR414](https://github.com/librenms/librenms/pull/414), [PR425](https://github.com/librenms/librenms/pull/425))
 - Updated progress bars to use bootstrap ([PR42](https://github.com/librenms/librenms/pull/42))
 - Updated install docs to cover CentOS7 ([PR424](https://github.com/librenms/librenms/pull/424))
 - Alerting system updated with more features ([PR429](https://github.com/librenms/librenms/pull/429), [PR430](https://github.com/librenms/librenms/pull/430))

###Dec 2014

####Bug fixes
 - Fixed Global Search box bootstrap ([PR357](https://github.com/librenms/librenms/pull/357))
 - Fixed display issues when calculating CDR in billing system ([PR359](https://github.com/librenms/librenms/pull/359))
 - Fixed API route order to resolve get_port_graphs working ([PR364](https://github.com/librenms/librenms/pull/364))

####Improvements
 - Added new API route to retrieve list of graphs for a device ([PR355](https://github.com/librenms/librenms/pull/355))
 - Added new API route to retrieve list of port for a device ([PR356](https://github.com/librenms/librenms/pull/356))
 - Added new API route to retrieve billing info ([PR360](https://github.com/librenms/librenms/pull/360))
 - Added alerting system ([PR370](https://github.com/librenms/librenms/pull/370), [PR369](https://github.com/librenms/librenms/pull/369), [PR367](https://github.com/librenms/librenms/pull/367))
 - Added dbSchema version to about page ([PR377](https://github.com/librenms/librenms/pull/377))
 - Added git log link to about page ([PR378](https://github.com/librenms/librenms/pull/378))
 - Added Two factor authentication ([PR383](https://github.com/librenms/librenms/pull/383))

###Nov 2014

####Bug fixes
 - Updated Alcatel-Lucent OmniSwitch detection ([PR340](https://github.com/librenms/librenms/pull/340))
 - Added fix for DLink port detection ([PR347](https://github.com/librenms/librenms/pull/347))
 - Fixed BGP session count ([PR334](https://github.com/librenms/librenms/pull/334))
 - Fixed errors with BGP polling and storing data in RRD ([PR346](https://github.com/librenms/librenms/pull/346))

####Improvements
 - Added option to clean old perf_times table entries ([PR343](https://github.com/librenms/librenms/pull/343))
 - Added nginx+php-fpm instructions ([PR345](https://github.com/librenms/librenms/pull/345))
 - Added BGP route to API ([PR335](https://github.com/librenms/librenms/pull/335))
 - Updated check_mk to new version + removed Observium branding ([PR311](https://github.com/librenms/librenms/pull/311))
 - Updated Edit SNMP settings page for device to only show relevant SNMP options ([PR317](https://github.com/librenms/librenms/pull/317))
 - Eventlog page now uses paged results ([PR336](https://github.com/librenms/librenms/pull/336))
 - Added new API route to show peering, transit and core graphs ([PR349](https://github.com/librenms/librenms/pull/349))
 - Added VyOS and EdgeOS detection ([PR351](https://github.com/librenms/librenms/pull/351) / [PR352](https://github.com/librenms/librenms/pull/352))
 - Documentation style and markdown updates ([PR353](https://github.com/librenms/librenms/pull/353))

###Oct 2014

####Bug fixes
 - Fixed displaying device image in device list ([PR296](https://github.com/librenms/librenms/pull/296))
 - Fixed placement of popups ([PR297](https://github.com/librenms/librenms/pull/297))
 - Updated authToken response code in API to 401 ([PR310](https://github.com/librenms/librenms/pull/310))
 - Removed trailing / from v0 part of API url ([PR312](https://github.com/librenms/librenms/pull/312))
 - Added correct response code for API call get_vlans ([PR313](https://github.com/librenms/librenms/pull/313))
 - Updated yearly graphs to fix year variable being passed ([PR316](https://github.com/librenms/librenms/pull/316))
 - Updated transport list to be generated from $config ([PR318](https://github.com/librenms/librenms/pull/318))
 - Moved addhost button on add host page as it was hidden ([PR319](https://github.com/librenms/librenms/pull/319))
 - Added stripslashes to hrdevice page ([PR321](https://github.com/librenms/librenms/pull/321))
 - Fixed web installer issue due to variable name change ([PR325](https://github.com/librenms/librenms/pull/325))
 - Updated disabled field in api tokens ([PR327](https://github.com/librenms/librenms/pull/327))
 - Fixed daily.sh not running from outside install directory (cron) ([PR328](https://github.com/librenms/librenms/pull/328))
 - Removed --no-edit from daily.php git pull ([PR309](https://github.com/librenms/librenms/pull/309))

####Improvements
 - Added ability to create api tokens ([PR294](https://github.com/librenms/librenms/pull/294))
 - Added icmp and poller graphs for devices ([PR295](https://github.com/librenms/librenms/pull/295))
 - Added urldecode/urlencode support for interface names in API ([PR298](https://github.com/librenms/librenms/pull/298))
 - Added new library to support on screen notifications ([PR300](https://github.com/librenms/librenms/pull/300))
 - Added authlog purge function and improved efficiency in clearing syslog table ([PR301](https://github.com/librenms/librenms/pull/301))
 - Updated addhost page to show relevant snmp options ([PR303](https://github.com/librenms/librenms/pull/303))
 - Added limit $config for front page boxes ([PR305](https://github.com/librenms/librenms/pull/305))
 - Updated http-auth adding user to check if user already exists ([PR307](https://github.com/librenms/librenms/pull/307))
 - Added names to all API routes ([PR314](https://github.com/librenms/librenms/pull/314))
 - Added route to call list of API endpoints ([PR315](https://github.com/librenms/librenms/pull/315))
 - Added options to $config to specify fping retry and timeout ([PR323](https://github.com/librenms/librenms/pull/323))
 - Added icmp / snmp to device down alerts for debugging ([PR324](https://github.com/librenms/librenms/pull/324))
 - Added function to page results for large result pages ([PR333](https://github.com/librenms/librenms/pull/333))

###Sep 2014

####Bug fixes
 - Updated vtpversion check to fix vlan discovery issues ([PR289](https://github.com/librenms/librenms/pull/289))
 - Fixed mac address change false positives ([PR292](https://github.com/librenms/librenms/pull/292))

####Improvements
 - Hide snmp passwords on edit snmp form ([PR290](https://github.com/librenms/librenms/pull/290))
 - Updates to API ([PR291](https://github.com/librenms/librenms/pull/291))

###Aug 2014

####Bug fixes
 - Disk % not showing in health view ([PR284](https://github.com/librenms/librenms/pull/284))
 - Fixed layout issue for ports list ([PR286](https://github.com/librenms/librenms/pull/286))
 - Removed session regeneration ([PR287](https://github.com/librenms/librenms/pull/287))
 - Updated edit button on edit user screen ([PR288](https://github.com/librenms/librenms/pull/288))

####Improvements
 - Added email field for add user form ([PR278](https://github.com/librenms/librenms/pull/278))
 - V0 of API release ([PR282](https://github.com/librenms/librenms/pull/282))

###Jul 2014

####Bug fixes
 - Fixed RRD creation using MAX twice ([PR266](https://github.com/librenms/librenms/pull/266))
 - Fixed variables leaking in poller run ([PR267](https://github.com/librenms/librenms/pull/267))
 - Fixed links to health graphs ([PR271](https://github.com/librenms/librenms/pull/271))
 - Fixed install docs to remove duplicate snmpd on install ([PR276](https://github.com/librenms/librenms/pull/276))

####Improvements
 - Added support for Cisco ASA connection graphs ([PR268](https://github.com/librenms/librenms/pull/268))
 - Updated delete device page ([PR270](https://github.com/librenms/librenms/pull/270))

###Jun 2014

####Bug fixes
 - Fixed a couple of DB queries ([PR222](https://github.com/librenms/librenms/pull/222))
 - Fixes to make interface more mobile friendly ([PR227](https://github.com/librenms/librenms/pull/227))
 - Fixed link to device on overview apps page ([PR228](https://github.com/librenms/librenms/pull/228))
 - Fixed missing backticks on SQL queries ([PR253](https://github.com/librenms/librenms/pull/253) / [PR254](https://github.com/librenms/librenms/pull/254))
 - Fixed user permissions page ([PR265](https://github.com/librenms/librenms/pull/265))

####Improvements
 - Updated index page ([PR224](https://github.com/librenms/librenms/pull/224))
 - Updated global search visually ([PR223](https://github.com/librenms/librenms/pull/223))
 - Added contributors agreement ([PR225](https://github.com/librenms/librenms/pull/225))
 - Added ability to update health values ([PR226](https://github.com/librenms/librenms/pull/226))
 - Tidied up search box on devices list page ([PR229](https://github.com/librenms/librenms/pull/229))
 - Updated port search box and port table list ([PR230](https://github.com/librenms/librenms/pull/230))
 - Removed some unused javascript libraries ([PR231](https://github.com/librenms/librenms/pull/231))
 - Updated year and column for vertical status summary ([PR232](https://github.com/librenms/librenms/pull/232))
 - Tidied up the delete user page ([PR235](https://github.com/librenms/librenms/pull/235))
 - Added snmp port to $config ([PR237](https://github.com/librenms/librenms/pull/237))
 - Added documentation for lighttpd ([PR238](https://github.com/librenms/librenms/pull/238))
 - Updated all device edit pages ([PR239](https://github.com/librenms/librenms/pull/239))
 - Added IPv6 only host support ([PR241](https://github.com/librenms/librenms/pull/241))
 - Added public status page ([PR246](https://github.com/librenms/librenms/pull/246))
 - Added validate_device_id function ([PR257](https://github.com/librenms/librenms/pull/257))
 - Added auto detect of install location ([PR259](https://github.com/librenms/librenms/pull/259))

###Mar 2014

####Bug fixes
 - Removed link to pdf in billing history ([PR146](https://github.com/librenms/librenms/pull/146))
 - librenms logs now saved in correct location ([PR163](https://github.com/librenms/librenms/pull/163))
 - Updated pfsense detection ([PR182](https://github.com/librenms/librenms/pull/182))
 - Fixed health page mini cpu ([PR195](https://github.com/librenms/librenms/pull/195))
 - Updated install docs to include php5-json ([PR196](https://github.com/librenms/librenms/pull/196))
 - Fixed Dlink interface names ([PR200](https://github.com/librenms/librenms/pull/200) / [PR203](https://github.com/librenms/librenms/pull/203))
 - Stop shortening IP in shorthost function ([PR210](https://github.com/librenms/librenms/pull/210))
 - Fixed status box overlapping ([PR211](https://github.com/librenms/librenms/pull/211))
 - Fixed top port overlay issue ([PR212](https://github.com/librenms/librenms/pull/212))
 - Updated docs and daily.sh to update DB schemas ([PR215](https://github.com/librenms/librenms/pull/215))
 - Updated hardware detection for RouterOS ([PR217](https://github.com/librenms/librenms/pull/217))
 - Restore _GET variables for logging in ([PR218](https://github.com/librenms/librenms/pull/218))

####Improvements
 - Updated inventory page to use bootstrap ([PR141](https://github.com/librenms/librenms/pull/141))
 - Updated mac / arp pages to use bootstrap ([PR147](https://github.com/librenms/librenms/pull/147))
 - Updated devices page to use bootstrap ([PR149](https://github.com/librenms/librenms/pull/149))
 - Updated delete host page to use bootstrap ([PR151](https://github.com/librenms/librenms/pull/151))
 - Updated print_error function to use bootstrap ([PR153](https://github.com/librenms/librenms/pull/153))
 - Updated install docs for Apache 2.3 > ([PR161](https://github.com/librenms/librenms/pull/161))
 - Upgraded PHPMailer ([PR169](https://github.com/librenms/librenms/pull/169))
 - Added send_mail function using PHPMailer ([PR170](https://github.com/librenms/librenms/pull/170))
 - Added new and awesome IRC Bot ([PR171](https://github.com/librenms/librenms/pull/171))
 - Added Gentoo detection and logo ([PR174](https://github.com/librenms/librenms/pull/174) / [PR179](https://github.com/librenms/librenms/pull/179))
 - Added Engenius detection ([PR186](https://github.com/librenms/librenms/pull/186))
 - Updated edit user to enable editing ([PR187](https://github.com/librenms/librenms/pull/187))
 - Added EAP600 engenius support ([PR188](https://github.com/librenms/librenms/pull/188))
 - Added Plugin system ([PR189](https://github.com/librenms/librenms/pull/189))
 - MySQL calls updated to use dbFacile ([PR190](https://github.com/librenms/librenms/pull/190))
 - Added support for Dlink devices ([PR193](https://github.com/librenms/librenms/pull/193))
 - Added Windows 2012 polling support ([PR201](https://github.com/librenms/librenms/pull/201))
 - Added purge options for syslog / eventlog ([PR204](https://github.com/librenms/librenms/pull/204))
 - Added BGP to global search box ([PR205](https://github.com/librenms/librenms/pull/205))

###Feb 2014

####Bug fixes
 - Set poller-wrapper.py to be executable ([PR89](https://github.com/librenms/librenms/pull/89))
 - Fix device/port down boxes ([PR99](https://github.com/librenms/librenms/pull/99))
 - Ports set to be ignored honoured for threshold alerts ([PR104](https://github.com/librenms/librenms/pull/104))
 - Added PasswordHash.php to adduser.php ([PR119](https://github.com/librenms/librenms/pull/119))
 - build-base.php update to run DB updates ([PR128](https://github.com/librenms/librenms/pull/128))

####Improvements
 - Added web based installer ([PR75](https://github.com/librenms/librenms/pull/75))
 - Updated login page design ([PR78](https://github.com/librenms/librenms/pull/78))
 - Ability to enable / disable topX boxes ([PR100](https://github.com/librenms/librenms/pull/100))
 - Added PHPPass support for MySQL auth logins ([PR101](https://github.com/librenms/librenms/pull/101))
 - Updated to Bootstrap 3.1 ([PR106](https://github.com/librenms/librenms/pull/106))
 - index.php tidied up ([PR107](https://github.com/librenms/librenms/pull/107))
 - Updated device overview page design ([PR113](https://github.com/librenms/librenms/pull/113))
 - Updated print_optionbar* to use bootstrap ([PR115](https://github.com/librenms/librenms/pull/115))
 - Updated device/port/services box to use bootstrap ([PR117](https://github.com/librenms/librenms/pull/117))
 - Updated eventlog / syslog to use bootstrap ([PR132](https://github.com/librenms/librenms/pull/132) / [PR134](https://github.com/librenms/librenms/pull/134))

###Jan 2014

####Bug fixes
 - Moved location redirect for logout ([PR55](https://github.com/librenms/librenms/pull/55))
 - Remove debug statements from process_syslog ([PR57](https://github.com/librenms/librenms/pull/57))
 - Stop print-syslog.inc.php from shortening hostnames ([PR62](https://github.com/librenms/librenms/pull/62))
 - Moved some variables from defaults.inc.php to definitions.inc.php ([PR66](https://github.com/librenms/librenms/pull/66))
 - Fixed title being set correctly ([PR73](https://github.com/librenms/librenms/pull/73))
 - Added documentation to enable billing module ([PR74](https://github.com/librenms/librenms/pull/74))

####Improvements
 - Deleting devices now asks for confirmation ([PR53](https://github.com/librenms/librenms/pull/53))
 - Added ARP discovered device name and IP to eventlog ([PR54](https://github.com/librenms/librenms/pull/54))
 - Initial updated design release ([PR59](https://github.com/librenms/librenms/pull/59))
 - Added ifAlias script ([PR70](https://github.com/librenms/librenms/pull/70))
 - Added console ui ([PR72](https://github.com/librenms/librenms/pull/72))

###Nov 2013

####Bug fixes
 - Updates to fix arp discovery

####Improvements
 - Added poller-wrapper (f8debf4)
 - Documentation####Improvements and additions
 - Added auto update feature
 - Visual updates
 - License tidy up started

###Oct 2013

 - Initial release
