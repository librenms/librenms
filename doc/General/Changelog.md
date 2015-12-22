### December 2015

#### Bug fixes
  - WebUI:
    - Fixed regex for negative lat/lng coords (PR2524)
    - Fixed map page looping due to device connected to itself (PR2545)
    - Fixed PATH_INFO for nginx (PR2551)
    - urlencode the custom port types (PR2597)
    - Stop non-admin users from being able to get to settings pages (PR2627)
    - Fix JpGraph php version compare (PR2631)
  - Discovery / Polling:
    - Pointed snmp calls for Huawei to correct MIB folder (PR2541)
    - Fixed Ceph unix-agent support. (PR2588)
    - Moved memory graphs from storage to memory polling (PR2616)
    - Mask alert_log mysql output when debug is enabled to stop console crashes (PR2618)
    - Stop Quanta devices being detected as Ubiquiti (PR2632)
    - Fix MySQL unix-agent graphs (PR2645)
    - Added MTA-MIB and NETWORK-SERVICES-MIB to stop warnings printed in poller debug (PR2653)
  - Services:
    - Fix SSL check for PHP 7 (PR2647)
  - Alerting:
    - Fix glue-expansion for alerts (PR2522)
    - Fix HipChat transport (PR2586)
  - Documentation:
    - Removed duplicate mysql-client install from Debian/Ubuntu install docs (PR2543)
  - Misc:
    - Update daily.sh to ignore issues writing to log file (PR2595)

#### Improvements
  - WebUI:
    - Converted sensors page to use bootgrid (PR2531)
    - Added new widgets for dashboard. Notes (PR2582), Generic image (PR2617)
    - Added config option to disable lazy loading of images (PR2589)
    - Visual update to Navbar. (PR2593)
    - Update alert rules to show actual alert rule ID (PR2603)
    - Initial support added for per user default dashboard (PR2620)
    - Updated Worldmap to show clusters in red if one device is down (PR2621)
  - Discovery / Polling
    - Added traffic bits as default for Cambium devices (PR2525)
    - Overwrite eth0 port data from UniFi MIBs for AirFibre devices (PR2544)
    - Added lastupdate column to sensors table for use with alerts (PR2590,PR2592)
    - Updated auto discovery via lldp to check for devices that use mac address in lldpRemPortId (PR2591)
    - Updated auto discovery via lldp with absent lldpRemSysName (PR2619)
  - API:
    - Added ability to filter devices by type and os for Oxidized API call (PR2539)
    - Added ability to update device information (PR2585)
    - Added support for returning device groups (PR2611)
    - Added ability to select port graphs based on ifDescr (PR2648)
  - Documentation:
    - Improved alerting docs explaining more options (PR2560)
    - Added Docs for Ubuntu/Debian Smokeping integration (PR2610)
  - Added detection for:
    - Updated Netonix switch MIBs (PR2523)
    - Updated Fotinet MIBs (PR2529, PR2534)
    - Cisco SG500 (PR2609)
    - Updated processor support for Fortigate (PR2613)
  - Misc:
    - Updated validation to check for php extension and classes required (PR2602)
    - Added Radius Authentication support (PR2615)
    - Removed distinct() from alerts query to use indexes (PR2649)

### November 2015

#### Bug fixes
  - WebUI:
    - getRates should return in and out average rates (PR2375)
    - Fix 95th percent lines in negative range (PR2405)
    - Fix percentage bar for billing pages (PR2419)
    - Use HC counters first in realtime graphs (PR2420)
    - Fix netcmd.php URI for sub dir installations (PR2428)
    - Fixed Oxidized fetch config with groups (PR2501)
    - Fixed background colour to white for some graphs (PR2516)
  - API:
    - Added missing quotes for MySQL queries (PR2382)
  - Discovery / Polling:
    - Specified MIB used when polling ntpd-server (PR2418)
    - Added missing fields when inserting data into applications table (PR2445)
    - Fix auto-discovery failing (PR2457)
    - Juniper hardware inventory fix (PR2466)
    - Fix discovery of Cisco PIX running PixOS 8.0 (PR2480)
    - Fix bug in Proxmox support if only one VM was detected (PR2490, PR2547)
  - Alerting:
    - Strip && and || from query for device-groups (PR2476)
    - Fix transports being triggered when empty keys set (PR2491)
  Misc:
    - Updated device_traffic_descr config to stop graphs failing (PR2386)

#### Improvements
  - WebUI:
    - Status column now sortable for /devices/ (PR2397)
    - Update Gridster library to be responsive (PR2414)
    - Improved rrdtool 1.4/1.5 compatibility (PR2430)
    - Use event_id in query for Eventlog (PR2437)
    - Add graph selector to devices overview (PR2438)
    - Improved Navbar for varying screen sizes (PR2450)
    - Added RIPE NCC API support for lookups (PR2455, PR2474)
    - Improved ports page for device with large number of neighbours (PR2460)
    - Merged all CPU graphs into one on overview page (PR2470)
    - Added support for sortting by traffic on device port page (PR2508)
    - Added support for dynamic graph sizes based on browser size (PR2510)
    - Made device location clickable in device header (PR2515)
    - Visual improvements to bills page (PR2519)
  - Discovery / Polling:
    - Updated Cisco SB discovery (PR2396)
    - Added Ceph support via Applications (PR2412)
    - Added support for per device unix-agent port (PR2439)
    - Added ability to select up/down devices on worldmap (PR2441)
    - Allow powerdns app to be set for Unix Agent (PR2489)
    - Added SLES detection to distro script (PR2502)
  - Added detection for:
    - Added CPU + Memory usage for Ubiquiti UniFi (PR2421)
    - Added support for LigoWave Infinity AP's (PR2456)
  - Alerting:
    - Added ability to globally disable sending alerts (PR2385)
    - Added support for Clickatell, PlaySMS and VictorOps (PR24104, PR2443)
  - Documnetation:
    - Improved CentOS install docs (PR2462)
    - Improved Proxmox setup docs (PR2483)
  - Misc:
    - Provide InnoDB config for buffer size issues (PR2401)
    - Added AD Authentication support (PR2411, PR2425, PR2432, PR2434)
    - Added Features document (PR2436, PR2511, PR2513)
    - Centralised innodb buffer check and added to validate (PR2482)
    - Updated and improved daily.sh (PR2487)


### October 2015

#### Bug fixes
  - Discovery / Polling:
    - Check file exists via rrdcached before creating new files on 1.5 (PR2041)
    - Fix Riverbed discovery (PR2133)
    - Fixes issue where snmp_get would not return the value 0 (PR2134)
    - Fixed powerdns snmp checks (PR2176)
    - De-dupe checks for hostname when adding hosts (PR2189)
  - WebUI:
    - Soft fail if PHP Pear not installed (PR2036)
    - Escape quotes for ifAlias in overlib calls (PR2072)
    - Fix table name for access points (PR2075)
    - Removed STACK text in graphs (PR2097)
    - Enable multiple ifDescr overrides to be done per device (PR2099)
    - Removed ping + performance graphs and tab if skip ping check (PR2175)
    - Fixed services -> Alerts menu link + page (PR2173)
    - Fix percent bar also for quota bills (PR2198)
    - Fix new Bill (PR2199)
    - Change default solver to hierarchicalRepulsion in vis.js (PR2202)
    - Fix: setting user port permissions fails (PR2203)
    - Updated devices Graphs links to use non-static time references (PR2211)
    - Removed ignored,deleted and disabled ports from query (PR2213)
  - API:
    - Fixed API call for alert states (PR2076)
    - Fixed nginx rewrite for api (PR2112)
    - Change on the add_edit_rule to modify a rule without modify the name (PR2159)
    - Fixed list_bills function when using :bill_id (PR2212)

#### Improvements
  - WebUI:
    - Updated Bootstrap to 3.3.5 (PR2015)
    - Added billing graphs to graphs widget (PR2027)
    - Lock widgets by default so they can't be moved (PR2042)
    - Moved Device Groups menu (PR2049)
    - Show Config tab only if device isn't excluded from oxidized (PR2118)
    - Simplify adding config options to WebUI (PR2120)
    - Move red map markers to foreground (PR2127)
    - Styled the two factor auth token prompt (PR2151)
    - Update Font Awesome (PR2167)
    - Allow user to influence when devices are grouped on world map (PR2170)
    - Centralised the date selector for graphs for re-use (PR2183)
    - Dont show dashboard settings if `/bare=yes/` (PR2364)
  - API:
    - Added unmute alert function to API (PR2082)
  - Discovery / Polling:
    - Added additional support for some UPS' based on Multimatic cards (PR2046)
    - Improved WatchGuard OS detection (PR2048)
    - Treat Dell branded Wifi controllers as ArubaOS (PR2065)
    - Added discovery option for OS or Device type (PR2088)
    - Updated pfSense to firewall type (PR2096)
    - Added ability to turn off icmp checks globally or per device (PR2131)
    - Reformat check a bit to make it easier for adding additional oids in (PR2135)
    - Updated to disable auto-discovery by ip (PR2182)
    - Updated to use env in distro script (PR2204)
  - Added detection for:
    - Pulse Secure OS (PR2053)
    - Riverbed Steelhead support (PR2107)
    - OpenBSD sensors (PR2113)
    - Additional comware detection (PR2162)
    - Version from Synology MIB (PR2163)
    - VCSA as VMWare (PR2185)
    - SAF Lumina radios (PR2361)
    - TP-Link detection (PR2362)
  - Documentation:
    - Improved RHEL/CentOS install docs (PR2043)
    - Update Varnish Docs (PR2116, PR2126)
    - Added passworded channels for the IRC-Bot (PR2122)
    - Updated Two-Factor-Auth.md RE: Google Authenticator (PR2146)
  - General:
    - Added colour support to IRC bot (PR2059)
    - Fixed IRC bot reconnect if socket dies (PR2061)
    - Updated default crons (PR2177)
  - Reverts:
    - "Removed what appears to be unecessary STACK text" (PR2128)

### September 2015

#### Bug fixes
  - Alerting:
    - Process followups if there are changes (PR1817)
    - Typo in alert_window setting (PR1841)
    - Issue alert-trigger as test object (PR1850)
  - WebUI:
    - Fix permissions for World-map widget (PR1866)
    - Clean up Gloabl / World Map name mixup (PR1874)
    - Removed required flag for community when adding new hosts (PR1961)
    - Stop duplicate devices showing in map (PR1963)
    - Fix adduser bug storing users real name (PR1990)
    - Stop alerts top-menu being clickable (PR1995)
  - Services:
    - Honour IP field for DNS checks (PR1933)
  - Discovery / Poller:
    - Fix Huawei VRP os detection (PR1931)
    - Set empty processor descr for *nix processors (PR1951)
    - Ensure udp6/tcp6 snmp devices use fping6 (PR1959)
    - Fix RRD creation parameters (PR2010)
  - General:
    - Remove 'sh' from cronjob (PR1818)
    - Remove MySQL Locks (PR1822,PR1826,PR1829,PR1836)
    - Change DB config options to use single quotes to allow $ (PR1948)

#### Improvements
  - WebUI:
    - Ability to edit ifAlias (PR1811)
    - Honour Mouseout/Mouseleave on map widget (PR1814)
    - Make syslog/eventlog responsive (PR1816)
    - Reformat Proxmox UI (PR1825,PR1827)
    - Misc Changes (PR1828,PR1830,PR1875,PR1885,PR1886,PR1887,PR1891,PR1896,PR1901,PR1913,PR1944)
    - Added support for Oxidized versioning (PR1842)
    - Added graph widget + settings for widgets (PR1835,PR1861,PR1968)
    - Added Support for multiple dashboards (PR1869)
    - Added settings page for Worldmap widget (PR1872)
    - Added uptime to availability widget (PR1881)
    - Added top devices and ports widgets (PR1903)
    - Added support for saving notes for devices (PR1927)
    - Added fullscreen mobile support (PR2022)
  - Added detection for:
    - FortiOS (PR1815)
    - HP MSM (PR1953)
  - Discovery / Poller:
    - Added Proxmox support (PR1789)
    - Added CPU/Mem support for SonicWALL (PR1957)
    - Updated distro script to support Arch Linux + fall back to lsb-release (PR1978)
  - Documentation:
    - Add varnish docs (PR1809)
    - Added CentOS 7 RRCached docs (PR1893)
    - Improved description of fping options (PR1952)
  - Alerting:
    - Added RegEx support for alert rules and device groups (PR1998)
  - General:
    - Make installer more responsive (PR1832)
    - Update fping millisec option to 200 default (PR1833)
    - Reduced cleanup of device_perf (PR1837)
    - Added support for negative values in munin-plugins (PR1907)
    - Added default librenms user to config for use in validate.php (PR1956)
    - Added working memcache support (PR2007)

### August 2015

#### Bug fixes
  - WebUI:
    - Fix web_mouseover not honoured on All Devices page (PR1592)
    - Fixed bug with edit/create alert template to clear out previous values (PR1636)
    - Initialise $port_count in devices list (PR1640)
    - Fixed Web installer due to code tidying update (PR1644)
    - Updated gridster variable names to make unique (PR1646)
    - Fixed issues with displaying devices with ' in location (PR1655)
    - Fixes updating snmpv3 details in webui (PR1727)
    - Check for user perms before listing neighbour ports (PR1749)
    - Fixed Test-Transport button (PR1772)
  - DB:
    - Added proper indexes on device_perf table (PR1621)
    - Fixed multiple mysql strict issues (PR1638, PR1659)
    - Convert bgpPeerRemoteAs to bigint (PR1691)
  - Discovery / Poller:
    - Fixed Synology system temps (PR1649)
    - Fixed discovery-arp not running since code formatting update (PR1671)
    - Correct the DSM upgrade OID (PR1696)
    - Fix MySQL agent host variable usage (PR1710)
    - Pass snmp-auth parameters enclosed by single-quotes (PR1730)
    - Revert change which skips over down ports (PR1742)
    - Stop PoE polling for each port (PR1747)
    - Use ifHighSpeed if ifSpeed equals 0 (PR1750)
    - Keep PHP Backwards compatibility (PR1766)
    - False identification of Zyxel as Cisco (PR1776)
    - Fix MySQL statement in poller-service.py (PR1794)
    - Fix upstart script for poller-service.py (PR1812)
  - General:
    - Fixed path to defaults.inc.php in config.php.default (PR1673)
    - Strip '::ffff:' from syslog input (PR1734)
    - Fix RRA (PR1791)

#### Improvements
  - WebUI Updates:
    - Added support for Google API key in Geo coding (PR1594)
    - Added ability to updated storage % warning (PR1613)
    - Updated eventlog page to allow filtering by type (PR1623)
    - Hide logo and plugins text on smaller windows (PR1624)
    - Added poller group name to poller groups table (PR1634)
    - Updated Customers page to use Bootgrid (PR1658)
    - Added basic Graylog integration support (PR1665)
    - Added support for running under sub-directory (PR1667)
    - Updated vis.js to latest version (PR1708)
    - Added border on availability map (PR1713)
    - Make new dashboard the default (PR1719)
    - Rearrange about page (PR1735,PR1743)
    - Center/Cleanup graphs (PR1736)
    - Added Hover-Effect on devices table (PR1738)
    - Show Test-Transport result (PR1777)
    - Add arrows to the network map (PR1787)
    - Add errored ports to summary widget (PR1788)
    - Show message if no Device-Groups exist (PR1796)
    - Misc UI fixes (Titles, Headers, ...) (PR1797,PR1798,PR1800,PR1801,PR1802,PR1803,PR1804,PR1805)
    - Move packages to overview dropdown (PR1810)
  - API Updates:
    - Improvided billing support in API (PR1599)
    - Extended support for list devices to support mac/ipv4 and ipv6 filtering (PR1744)
  - Added detection for:
    - Perle Media convertors (PR1607)
    - Mac OSX 10 (PR1774)
  - Improved detection for:
    - Windows devices (PR1639)
    - Zywall CPU, Version and Memory (PR1660,PR1784)
    - Added LLDP support for PBN devices (PR1705)
    - Netgear GS110TP (PR1751)
  - Additional Sensors:
    - Added Compressor state for PCOWEB (PR1600)
    - Added dbm support for IOS-XR (PR1661)
    - Added temperature support for DNOS (PR1782)
  - Discovery / Poller:
    - Updated autodiscovery function to log new type (PR1623)
    - Improve application polling (PR1724)
    - Improve debug output (PR1756)
  - DB:
    - Added MySQLi support (PR1647)
  - Documentation:
    - Added docs on MySQL strict mode (PR1635)
    - Updated billing docs to use librenms user in cron (PR1676)
    - Updated LDAP docs to indicate php-ldap module needs installing (PR1716)
    - Typo/Spellchecks (PR1731,PR1806)
    - Improved Alerting and Device-Groups (PR1781)
  - Alerting:
    - Reformatted eventlog message to show state for alerts (PR1685)
    - Add basic Pushbullet transport (PR1721)
    - Allow custom titles (PR1807)
  - General:
    - Added more debugging and checks to discovery-protocols (PR1590)
    - Cleanup debug statements (PR1725,PR1737)

### July 2015

#### Bug fixes
  - WebUI:
    - Fixed API not functioning. (PR1367)
    - Fixed API not storing alert rule names (PR1372)
    - Fixed datetimepicker use (PR1376)
    - Added 'running' status for BGP peers as up (PR1412)
    - Fixed the remove search link in devices (PR1413)
    - Fixed clicking anywhere in a search result will now take you to where you want (PR1472)
    - Fixed inventory page not displaying results (PR1488)
    - Fixed buggy alert templating in WebUI (PR1527)
    - Fixed bug in creating api tokens in Firefox (PR1530)
  - Discovery / Poller:
    - Do not allow master to rejoin itself. (PR1377)
    - Fixed poller group query in discovery (PR1433)
    - Fixed ARMv5 detection (PR1522)
    - Fixed pfSense detection (PR1567)
  - Sensors:
    - Fixed bug in EqualLogic sensors (PR1513)
    - Fixed bug in DRAC voltage sensor (PR1521)
    - Fixed bug in APC bank detection (PR1560)
  - Documentation:
    - Fixed Nginx config file (PR1389)
  - General:
    - Fixed a number of permission issues (PR1411)

#### Improvements
  - Added detection for:
    - Meraki (PR1402)
    - Brocade (PR1404)
    - Dell iDrac (PR1419,PR1420,PR1423,PR1427)
    - Dell Networking OS (PR1474)
    - Netonix (PR1476)
    - IBM Tape Library (PR1519,PR1550)
    - Aerohive (PR1546)
    - Cisco Voice Gateways (PR1565)
  - Improved detection for:
    - RouterOS RB260GS (PR1545)
    - Dell PowerConnect (PR1452,PR1517)
    - Brocade (PR1548)
    - Rielo UPS (PR1381)
    - Cisco IPSLAs (PR1586)
  - Additional Sensors:
    - Added power, temperature and fan speed support for XOS (PR1493,PR1494,PR1496)
  - WebUI Updates:
    - Added missing load and state icons (PR1392)
    - Added ability to update users passwords in WebUI (PR1440)
    - Default to two days performance data being shown (PR1442)
    - Improved sensors page for mobile view (PR1454)
    - Improvements to network map (PR1455,PR1470,PR1486,PR1528,PR1557)
    - Added availability map (PR1464)
    - Updated edit ports page to use Bootstrap (PR1498)
    - Added new World Map and support for lat/lng lookup (PR1501,PR1552)
    - Added sysName to overview page for device (PR1520)
    - Added New Overview dashboard uilising Widgets (PR1523,PR1580)
    - Added new config option to disable Device groups (PR1569)
  - Discovery / Poller Updates:
    - Updated discovery of IP based devices (PR1406)
    - Added using cronic for poller-wrapper.py to allow cron to send emails (PR1408,PR1531)
    - Updated Cisco MIBs to latest versions (PR1436)
    - Improve performance of unix-agent processes DB code (PR1447,PR1460)
    - Added BGP discovery code (PR1414)
    - Use snmpEngineTime as a fallback to uptime (PR1477)
    - Added fallback support for devices not reporting ifAlias (PR1479)
    - Git pull and schema updates will now pause if InnoDB buffers overused (PR1563)
  - Documentation:
    - Updated Unix-Agent docs to use LibreNMS repo for scripts (PR1568,PR1570,PR1573)
    - Added info on using MariaDB (PR1585)
  - Alerting:
    - Added Boxcar (www.boxcar.io) transport for alerting (PR1481)
    - Removed old alerting code (PR1581)
  - General:
    - Code cleanup and formatting (PR1415,PR1416,PR1431,PR1434,PR1439,PR1444,PR1450)
    - Added support for CollectD flush (PR1463)
    - Added support for LDAP pure DN member groups (PR1516)
    - Updated validate.php to check for distributed poller setup issues (PR1526)
    - Improved service check support (PR1385,PR1386,PR1387,PR1388)
    - Added SNMP Scanner to discover devices within subnets and docs (PR1577)

### June 2015

#### Bug fixes
  - Fixed services list SQL issue (PR1181)
  - Fixed negative values for storage when volume is > 2TB (PR1185)
  - Fixed visual display for input fields on /syslog/ (PR1193)
  - Fixed fatal php issue in shoutcast.php (PR1203)
  - Fixed percent bars in /bills/ (PR1208)
  - Fixed item count in memory and storage pages (PR1210)
  - Fixed syslog not loading (PR1219)
  - Fixed fatal on reload in IRC bot (PR1218)
  - Alter Windows CPU description when unknown (PR1226)
  - Fixed rfc1628 current calculation (PR1256)
  - Fixed alert mapping not working (PR1280)
  - Fixed legend ifLabels (PR1296)
  - Fixed bug causing map to not load when stale link data was present (PR1297)
  - Fixed javascript issue preventing removal of alert rules (PR1312)
  - Fixed removal of IPs before ports are deleted (PR1329)
  - Fixed JS issue when removing ports from bills (PR1330)
  - Fixed adding --daemon a second time to collectd Graphs (PR1342)
  - Fixed CollectD DS names (PR1347,PR1349,PR1368)
  - Fixed graphing issues when rrd contains special chars (PR1350)
  - Fixed regex for device groups (PR1359)
  - Added HOST-RESOURCES-MIB into Synology detection (RP1360)
  - Fix health page graphs showing the first graph for all (PR1363)

#### Improvements
  - Updated Syslog docs to include syslog-ng 3.5.1 updates (PR1171)
  - Added Pushover Transport (PR1180, PR1191)
  - Converted processors and memory table to bootgrid (PR1188, PR1192)
  - Issued alerts and transport now logged to eventlog (PR1194)
  - Added basic support for Enterasys devices (PR1211)
  - Added dynamic config to configure alerting (PR1153)
  - Added basic support for Multimatic USV (PR1215)
  - Disabled and ignored ports no longer show by default on /ports/ (PR1228,PR1301)
  - Added additional graphs to menu on devices page (PR1229)
  - Added Docs on configuring Globe front page (PR1231)
  - Added robots.txt to html folder to disallow indexing (PR1234)
  - Added additional support for Synology units (PR1235,PR1244,PR1269)
  - Added IP check to autodiscovery code (PR1248)
  - Updated HP ProCurve detection (PR1249)
  - Added basic detection for Alcatel-Lucent OmniSwitch (PR1253, PR1282)
  - Added additional metrics for rfc1628 UPS (PR1258, PR1268)
  - Allow multiple discovery modules to be specified on command line (PR1263)
  - Updated docs on using libvirt (PR1264)
  - Updated Ruckus detection (PR1267)
  - Initial release of MIB based polling (PR1273)
  - Added support for CISCO-BGP4-MIB (PR1184)
  - Added support for Dell EqualLogic units (PR1283,PR1309)
  - Added logging of success/ failure for alert transports (PR1286)
  - Updated VyOS detection (PR1299)
  - Added primary serial number detection for Cisco units (PR1300)
  - Added support for specifying MySQL port number in config.php (PR1302)
  - Updated alert subject to use rule name not ID (PR1310)
  - Added macro %macros.sensor (PR1311)
  - Added WebUI support for Pushover (PR1313)
  - Updated path check for Oxidized config (PR1316)
  - Added Multimatic UPS to rfc1628 detection (PR1317)
  - Added timeout for Unix agent (PR1319)
  - Added support for a poller to use more than one poller group (PR1323)
  - Added ability to use Plugins on device overview page (PR1325)
  - Added latency loss/avg/max/min results to DB and Graph (PR1326)
  - Added recording of device down (snmp/icmp) (PR1326)
  - Added debugging output for when invalid SNMPv3 options used (PR1331)
  - Added load and state output to device overview page (PR1333)
  - Added load sensors to RFC1628 Devices (PR1336)
  - Added support for WebPower Pro II UPS Cards (PR1338)
  - No longer rewrite server-status in .htaccess (PR1339)
  - Added docs for setting up Service extensions (PR1354)
  - Added additional info from pfsense devices (PR1356)

### May 2015

#### Bug fixes
  - Updated nested addHosts to use variables passed (PR889)
  - Fixed map drawing issue (PR907)
  - Fixed sensors issue where APC load sensors overwrote current (PR912)
  - Fixed devices location filtering (PR917, PR921)
  - Minor fix to rrdcached_dir handling (PR940)
  - Now set defaults for AddHost on XDP discovery (PR941)
  - Fix web installer to generate config correctly if possible (PR954)
  - Fix inverse option for graphs (PR955)
  - Fix ifAlias parsing (PR960)
  - Rewrote rrdtool_escape to fix graph formatting issues (PR961, PR965)
  - Updated ports check to include ifAdminStatus (PR962)
  - Fixed custom sensors high / low being overwritten on discovery (PR977)
  - Fixed APC powerbar phase limit discovery (PR981)
  - Fix for 4 digit cpu% for Datacom (PR984)
  - Fix SQL query for restricted users in /devices/ (PR990)
  - Fix for post-formatting time-macros (PR1006)
  - Honour disabling alerts for hosts (PR1051)
  - Make OSPF and ARP discovery independant xDP (PR1053)
  - Fixed ospf_nbrs lookup to use device_id (PR1088)
  - Removed trailing / from some urls (PR1089 / PR1100)
  - Fix to device search for Device type and location (PR1101)
  - Stop non-device boxes on overview appearing when device is down (PR1106)
  - Fixed nfsen directory checks (PR1123)
  - Removed lower limit for sensor graphs so negative values show (PR1124)
  - Added fallback for poller_group if empty when adding devices (PR1126)
  - Fixed processor graphs tooltips (PR1127)
  - Fixed /poll-log/ count (PR1130)
  - Fixed ARP search graph type reference (PR1131)
  - Fixed showing state=X in device list (PR1144)
  - Removed ability for demo user to delete users (PR1151)
  - Fixed user / port perms for top X front page boxes (PR1156)
  - Fixed truncating UTF-8 strings (PR1166)
  - Fixed attaching templates due to JS issue (PR1167)

#### Improvements
  - Added loading bar to top nav (PR893)
  - Added load and current for APC units (PR888)
  - Improved web installer (PR887)
  - Updated alerts status box (PR875)
  - Updated syslog page (PR862)
  - Added temperature polling for IBM Flexsystem (PR894)
  - Updated typeahead libraries and relevant forms (PR882)
  - Added docs showing configuration options and how to use them (PR910)
  - Added docs on discovery / poller and how to debug (PR911)
  - Updated docs for MySQL / Nginx / Bind use in Unix agent (PR916)
  - Update development docs (PR919)
  - Updated install docs to advise about whitespace in config.php (PR920)
  - Added docs on authentication modules (PR922)
  - Added support for Oxidized config archival (PR927)
  - Added API to feed devices to Oxidized (PR928)
  - Added support for per OS bad_iftype, bad_if and bad_if_regexp (PR930)
  - Enable alerting on tables with relative / indirect glues (PR932)
  - Added bills support in rulesuggest and alert system (PR934)
  - Added detection for Sentry Smart CDU (PR938)
  - Added basic detection for Netgear devices (PR942)  
  - addhost.php now uses distributed_poller_group config if set (PR944)
  - Added port rewrite function (PR946)
  - Added basic detection for Ubiquiti Edgeswitch (PR947)
  - Added support for retrieving email address from LDAP (PR949)
  - Updated JunOS logo (PR952)
  - Add aggregates on multi_bits_separate graphs (PR956)
  - Fix port name issue for recent snmp versions on Linux (PR957)
  - Added support for quick access to devices via url (PR958)
  - Added work around for PHP creating zombie processes on certain distros (PR959)
  - Added detection support for NetApp + disks + temperature (PR967, PR971)
  - Define defaults for graphs (PR968)
  - Added docs for migrating from Observium (PR974)
  - Added iLo temperature support (PR982)
  - Added disk temperature for Synology DSM (PR986)
  - Added ICMP, TLS/SSL and Domain expiry service checks (PR987, PR1040, PR1041)
  - Added IPMI detection (PR988)
  - Mikrotik MIB update (PR991)
  - Set better timeperiod for caching graphs (PR992)
  - Added config option to disable port relationship in ports list (PR996)
  - Added support for custom customer description parse (PR998)
  - Added hardware and MySQL version stats to callback (PR999)
  - Added support for alerting to PagerDuty (PR1004)
  - Now send ack notifications for alerts that are acked (PR1008)
  - Updated contributing docs and added placeholder (PR1024, PR1025)
  - Updated globe.php overview page with updated map support (PR1029)
  - Converted storage page to use Bootgrid (PR1030)
  - Added basic FibreHome detection (PR1031)
  - Show details of alerts in alert log (PR1043)
  - Allow a user-defined windows to add tolerance for alerting (PR1044)
  - Added inlet support for Raritan PX iPDU (PR1045)
  - Updated MIBS for Cisco SB (PR1058)
  - Added error checking for build-base on install (PR1059)
  - Added fan and raid state for Dell OpenManage (PR1062)
  - Updated MIBS for Ruckus ZoneDirectors (PR1067)
  - Added check for ./rename.php (PR1069)
  - Added install instructions to use librenms user (PR1071)
  - Honour sysContact over riding for alerts (PR1073)
  - Added services page for adding/deleting and editing services (PR1076)
  - Added more support for Mikrotik devices (PR1080)
  - Added better detection for Cisco ASA 5585-SSP40 (PR1082)
  - Added CPU dataplane support for JunOS (PR1086)
  - Removed requirement for hostnames on add device (PR1087)
  - Added config option to exclude sysContact from alerts (PR1093)
  - Added config option to regenerate contacts on alerts (PR1109)
  - Added validation tool to help fault find issues with installs (PR1112)
  - Added CPU support for EdgeOS (PR1114)
  - Added ability to customise transit/peering/core descriptions (PR1125)
  - Show ifName in ARP search if devices are set to use this (PR1133)
  - Added FibreHome CPU and Mempool support (PR1134)
  - Added config options for region and resolution on globe map (PR1137)
  - Addded RRDCached example docs (PR1148)
  - Updated support for additional NetBotz models (PR1152)
  - Updated /iftype/ page to include speed/circuit/notes (PR1155)
  - Added detection for PowerConnect 55XX devices (PR1165)

### Apr 2015

####Bug fixes
  - Fixed ack of worse/better alerts (PR720)
  - Fixed ORIG_PATH_INFO warnings (PR727)
  - Added missing CPU id for Cisco SB (PR744)
  - Changed Processors table name to lower case in processors discovery (PR751)
  - Fixed alerts path issue (PR756, PR760)
  - Supress further port alerts when interface goes down (PR745)
  - Fixed login so redirects via 303 when POST data sent (PR775)
  - Fixed missing link to errored or ignored ports (PR787)
  - Updated alert log query for performance improvements (PR783)
  - Honour alert_rules.disabled field (PR784)
  - Stop page debug if user not logged in (PR785)
  - Added text filtering for new tables (PR797)
  - Fixed VMWare VM detection + hardware / serial support (PR799)
  - Fix links from /health/processor (PR810)
  - Hide divider if no plugins installed (PR811)
  - Added Nginx fix for using debug option (PR823)
  - Bug fixes for device groups SQL (PR840)
  - Fixed path issue when using rrdcached (PR839)
  - Fixed JS issues when deleting alert maps / poller groups / device groups (PR846,PR848,PR877)
  - Fixed links and popover for /health/metric=storage/ (PR847)
  - Fixed lots of user permission issues (PR855)
  - Fixed search ip / arp / mac pages (PR845)
  - Added missing charge icon (PR878)

####Improvements
  - New theme support added (light,dark and mono) (PR682,PR683,PR701)
  - Tables being converted to Jquery Bootgrid (PR693,PR706,PR716)
  - Detect Cisco ASA Hardware and OS Version (PR708)
  - Update LDAP support (PR707)
  - Updated APC powernet MIB (PR713)
  - Update to Foritgate support (PR709)
  - Added support for UBNT AirOS and AirFibre (PR721,PR730,PR731)
  - Added support device groups + alerts to be mapped to devices or groups (PR722)
  - Added basic Cambium support (PR738)
  - Added basic F5 support (PR670)
  - Shorten interface names on map (PR752)
  - Added PowerCode support (PR762)
  - Added Autodiscovery via OSPF (PR772)
  - Added visual graph of alert log (PR777, PR809)
  - Added Callback system to send anonymous stats (PR768)
  - More tables converted to use bootgrid (PR729, PR761)
  - New Global Cache to store common queries added (PR780)
  - Added proxy support for submitting stats (PR791)
  - Minor APC Polling change (PR800)
  - Updated to HP switch detection (PR802)
  - Added Datacom basic detection (PR816)
  - Updated Cisco detection (PR815)
  - Added CSV export system + ability to export ports (PR818)
  - Added basic detection for PacketLogic devices (PR773)
  - Added fallback support for IBM switches for Serial / Version (PR822)
  - Added Juniper Inventory support (PR825)
  - Sharpen graphs produced (PR826)
  - Updated map to show device overview graphs and port graphs (PR826)
  - Added hostname to API call for list_alerts (PR834)
  - Added ability to schedule maintenance (PR835,PR841)
  - Added ability to expand alert triggers for more details (PR857)
  - Added support for XTM/FBX Watchguard devices (PR849)
  - Updated Juniper MIBS and hardware rewrite (PR838)
  - Updated OpenBSD detection (PR860)
  - Added Macro support for alerting system (PR863)
  - Added support for tcp connections on rrdcached (PR866)
  - Added config option to enable / disable mouseover graphs (PR873)
  - General cleanup of files / folders permissions (PR874)
  - Added window size detection for map (PR884)
  - Added text to let users know refresh is disabled (PR883)

### Mar 2015

####Bug fixes
  - Updates to alert rules split (PR550)
  - Updated get_graphs() for API to resolve graph names (PR613)
  - Fixed use of REMOTE_ADDR to use X_FORWARDED_FOR if available (PR620)
  - Added yocto support from entPhySensorScale (PR632)
  - Eventlog search fixed (PR644)
  - Added missing OS discovery to default list (PR660)
  - Fixed logging issue when description of a port was removed (PR673)
  - Fixed logging issue when ports changed status (PR675)
  - Shortened interface names for graph display (PR676)

####Improvements
  - Visual updates to alert logs (PR541)
  - Added temperature support for APC AC units (PR545)
  - Added ability to pause and resume page refresh (PR557)
  - Added polling support for NXOS (PR562)
  - Added discovery support for 3Com switches (PR568)
  - Updated Comware support (PR583)
  - Added new logo (PR584)
  - Added dynamic removal of device data when removing device (PR592)
  - Updated alerting to use fifo (PR607)
  - Added distributed poller support (PR609 and PR610)
  - Added PowerConnect 55xx (PR635)
  - Added inventory API endpoint (PR640)
  - Added serial number detection for ASA firewalls (PR642)
  - Added missing MKTree library for inventory support (PR646)
  - Added support for exporting Alert logs to PDF (PR653)
  - Added basic Ubiquiti support (PR659)
  - Numerous docs update (PR662, PR663, PR677, PR694)
  - Added Polling information page (PR664)
  - Added HipChat notification support (PR669)
  - Implemented Jquery Bootgrid support (PR671)
  - Added new map to show xDP discovered links and devices (PR679 + PR680)

###Feb 2015

####Bug fixes
 - Removed header redirect causing page load delays (PR436)
 - Fixed stale alerting data (PR475)
 - Fixed api call for port stats to use device_id / hostname (PR478)
 - Work started on ensuring MySQL strict mode is supported (PR521)

####Improvements
 - Added support for Cisco Wireless Controllers (PR422)
 - Updated IRC Bot to support alerting system (PR434)
 - Added new message box to alert when a device hasn't polled for 15 minutes or more (PR435)
 - Added quick links on device list page to quickly access common pages (PR440)
 - Alerting docs updated to cover new features (PR446)
 - IBM NOS Support added (PR454)
 - Added basic Barracuda Loadbalancer support (PR456)
 - Small change to the search results to add port desc / alias (PR457)
 - Added Device sub menu to access devices category directly (PR465)
 - Added basic Ruckus Wireless support (PR466)
 - Added support for a demo user (PR471)
 - Many small visual updates
 - Added additional support for Cisco SB devices (PR487)
 - Added support to default home page for printing alerts (PR488)
 - Tidied up Alert menubar into sub menu (PR489)
 - Added historical alerts page (PR495)
 - Added battery charge monitoring for (PR519)
 - Added Slack support for alert system (PR525)
 - Added new debug for php / sql option to page footer (PR484)

###Jan 2015

####Bug fixes
 - Reverted chmod to make poller.php executable again (PR394)
 - Fixed duplicate port listing (PR396)
 - Fixed create bill from port page (PR404)
 - Fixed autodiscovery to use $config['mydomain'] correctly (PR423)
 - Fixed mute bug for alerts (PR428)

####Improvements
 - Updated login page visually (PR391)
 - Added Hikvision support (PR393)
 - Added ability to search for packages using unix agent (PR395)
 - Updated ifAlias support for varying distributions (PR398)
 - Updated visually Global Settings page (PR401)
 - Added missing default nginx graphs (PR403)
 - Updated check_mk_agent to latest git version (PR409)
 - Added support for recording process list with unix agent (PR410)
 - Added support for named/bind9/TinyDNS application using unix agent (PR413, PR416)
 - About page tidied up (PR414, PR425)
 - Updated progress bars to use bootstrap (PR42)
 - Updated install docs to cover CentOS7 (PR424)
 - Alerting system updated with more features (PR429, PR430)

###Dec 2014

####Bug fixes
 - Fixed Global Search box bootstrap (PR357)
 - Fixed display issues when calculating CDR in billing system (PR359)
 - Fixed API route order to resolve get_port_graphs working (PR364)

####Improvements
 - Added new API route to retrieve list of graphs for a device (PR355)
 - Added new API route to retrieve list of port for a device (PR356)
 - Added new API route to retrieve billing info (PR360)
 - Added alerting system (PR370, PR369, PR367)
 - Added dbSchema version to about page (PR377)
 - Added git log link to about page (PR378)
 - Added Two factor authentication (PR383)

###Nov 2014

####Bug fixes
 - Updated Alcatel-Lucent OmniSwitch detection (PR340)
 - Added fix for DLink port detection (PR347)
 - Fixed BGP session count (PR334)
 - Fixed errors with BGP polling and storing data in RRD (PR346)

####Improvements
 - Added option to clean old perf_times table entries (PR343)
 - Added nginx+php-fpm instructions (PR345)
 - Added BGP route to API (PR335)
 - Updated check_mk to new version + removed Observium branding (PR311)
 - Updated Edit SNMP settings page for device to only show relevant SNMP options (PR317)
 - Eventlog page now uses paged results (PR336)
 - Added new API route to show peering, transit and core graphs (PR349)
 - Added VyOS and EdgeOS detection (PR351 / PR352)
 - Documentation style and markdown updates (PR353)

###Oct 2014

####Bug fixes
 - Fixed displaying device image in device list (PR296)
 - Fixed placement of popups (PR297)
 - Updated authToken response code in API to 401 (PR310)
 - Removed trailing / from v0 part of API url (PR312)
 - Added correct response code for API call get_vlans (PR313)
 - Updated yearly graphs to fix year variable being passed (PR316)
 - Updated transport list to be generated from $config (PR318)
 - Moved addhost button on add host page as it was hidden (PR319)
 - Added stripslashes to hrdevice page (PR321)
 - Fixed web installer issue due to variable name change (PR325)
 - Updated disabled field in api tokens (PR327)
 - Fixed daily.sh not running from outside install directory (cron) (PR328)
 - Removed --no-edit from daily.php git pull (PR309)

####Improvements
 - Added ability to create api tokens (PR294)
 - Added icmp and poller graphs for devices (PR295)
 - Added urldecode/urlencode support for interface names in API (PR298)
 - Added new library to support on screen notifications (PR300)
 - Added authlog purge function and improved efficiency in clearing syslog table (PR301)
 - Updated addhost page to show relevant snmp options (PR303)
 - Added limit $config for front page boxes (PR305)
 - Updated http-auth adding user to check if user already exists (PR307)
 - Added names to all API routes (PR314)
 - Added route to call list of API endpoints (PR315)
 - Added options to $config to specify fping retry and timeout (PR323)
 - Added icmp / snnmp to device down alerts for debugging (PR324)
 - Added function to page results for large result pages (PR333)

###Sep 2014

####Bug fixes
 - Updated vtpversion check to fix vlan discovery issues (PR289)
 - Fixed mac address change false positives (PR292)

####Improvements
 - Hide snmp passwords on edit snmp form (PR290)
 - Updates to API (PR291)

###Aug 2014

####Bug fixes
 - Disk % not showing in health view (PR284)
 - Fixed layout issue for ports list (PR286)
 - Removed session regeneration (PR287)
 - Updated edit button on edit user screen (PR288)
  
####Improvements
 - Added email field for add user form (PR278)
 - V0 of API release (PR282)

###Jul 2014

####Bug fixes
 - Fixed RRD creation using MAX twice (PR266)
 - Fixed variables leaking in poller run (PR267)
 - Fixed links to health graphs (PR271)
 - Fixed install docs to remove duplicate snmpd on install (PR276)

####Improvements
 - Added support for Cisco ASA connection graphs (PR268)
 - Updated delete device page (PR270)

###Jun 2014

####Bug fixes
 - Fixed a couple of DB queries (PR222)
 - Fixes to make interface more mobile friendly (PR227)
 - Fixed link to device on overview apps page (PR228)
 - Fixed missing backticks on SQL queries (PR253 / PR254)
 - Fixed user permissions page (PR265)

####Improvements
 - Updated index page (PR224)
 - Updated global search visually (PR223)
 - Added contributors aggrement (PR225)
 - Added ability to update health values (PR226)
 - Tidied up search box on devices list page (PR229)
 - Updated port search box and port table list (PR230)
 - Removed some unused javascript libraries (PR231)
 - Updated year and column for vertical status summary (PR232)
 - Tidied up the delete user page (PR235)
 - Added snmp port to $config (PR237)
 - Added documentation for lighttpd (PR238)
 - Updated all device edit pages (PR239)
 - Added IPv6 only host support (PR241)
 - Added public status page (PR246)
 - Added validate_device_id function (PR257)
 - Added auto detect of install location (PR259)

###Mar 2014

####Bug fixes
 - Removed link to pdf in billing history (PR146)
 - librenms logs now saved in correct location (PR163)
 - Updated pfsense detection (PR182)
 - Fixed health page mini cpu (PR195)
 - Updated install docs to include php5-json (PR196)
 - Fixed Dlink interface names (PR200 / PR203)
 - Stop shortening IP in shorthost function (PR210)
 - Fixed status box overlapping (PR211)
 - Fixed top port overlay issue (PR212)
 - Updated docs and daily.sh to update DB schemas (PR215)
 - Updated hardware detection for RouterOS (PR217)
 - Restore _GET variables for logging in (PR218)

####Improvements
 - Updated inventory page to use bootstrap (PR141)
 - Updated mac / arp pages to use bootstrap (PR147)
 - Updated devices page to use bootstrap (PR149)
 - Updated delete host page to use bootstrap (PR151)
 - Updated print_error function to use bootstrap (PR153)
 - Updated install docs for Apache 2.3 > (PR161)
 - Upgraded PHPMailer (PR169)
 - Added send_mail function using PHPMailer (PR170)
 - Added new and awesome IRC Bot (PR171)
 - Added Gentoo detection and logo (PR174 / PR179)
 - Added Engenius detection (PR186)
 - Updated edit user to enable editing (PR187)
 - Added EAP600 engenius support (PR188)
 - Added Plugin system (PR189)
 - MySQL calls updated to use dbFacile (PR190)
 - Added support for Dlink devices (PR193)
 - Added Windows 2012 polling support (PR201)
 - Added purge options for syslog / eventlog (PR204)
 - Added BGP to global search box (PR205)

###Feb 2014

####Bug fixes
 - Set poller-wrapper.py to be executable (PR89)
 - Fix device/port down boxes (PR99)
 - Ports set to be ignored honoured for threshold alerts (PR104)
 - Added PasswordHash.php to adduser.php (PR119)
 - build-base.php update to run DB updates (PR128)

####Improvements
 - Added web based installer (PR75)
 - Updated login page design (PR78)
 - Ability to enable / disable topX boxes (PR100)
 - Added PHPPass support for MySQL auth logins (PR101)
 - Updated to Bootstrap 3.1 (PR106)
 - index.php tidied up (PR107)
 - Updated device overview page design (PR113)
 - Updated print_optionbar* to use bootstrap (PR115)
 - Updated device/port/services box to use bootstrap (PR117)
 - Updated eventlog / syslog to use bootstrap (PR132 / PR134)

###Jan 2014

####Bug fixes
 - Moved location redirect for logout (PR55)
 - Remove debug statements from process_syslog (PR57)
 - Stop print-syslog.inc.php from shortening hostnames (PR62)
 - Moved some variables from defaults.inc.php to definitions.inc.php (PR66)
 - Fixed title being set correctly (PR73)
 - Added documentation to enable billing module (PR74)

####Improvements
 - Deleting devices now asks for confirmation (PR53)
 - Added ARP discovered device name and IP to eventlog (PR54)
 - Initial updated design release (PR59)
 - Added ifAlias script (PR70)
 - Added console ui (PR72)

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
