# Features

Here's a brief list of supported features, some might be missing. If
you think something is missing, feel free to ask us.

* Auto discovery
* Alerting
* Multiple environment sensors support
* Multiple protocols data collection (STP, OSPF, BGP etc)
* VLAN, ARP and FDB table collection
* Customizable Dashboards
* Device Backup integration (Oxidized, RANCID)
* Distributed Polling
* Multiple Authentication Methods (MySQL, LDAP, Active Directory, HTTP)
* NetFlow, sFlow, IPFIX (NfSen)
* Service monitoring (Nagios Plugins)
* Syslog (Integrated, Graylog)
* Traffic Billing (Quota, 95th Percentile)
* Two Factor Authentication
* API
* Auto Updating

## Supported Vendors

Here's a list of supported vendors, some might be missing.
If you are unsure of whether your device is supported or not, feel free to ask us.

<!---

Generate this list with:

rg --pcre2 "^text: *([\"']?)(.+)\1" --replace '$2' --no-filename includes/definitions/*yaml| sort --ignore-case --unique | awk '{\
  if (last != tolower(substr($0, 0, 1))) {\
    print "\n### "toupper(substr($0,0,1))"\n* "$0; last = tolower(substr($1, 0, 1))\
  } else {\
    print "* "$0\
  }\
}'

-->

### 3
* 3Com

### A
* A10 Networks
* Acano OS
* Accedian AEN
* Adtran AOS
* ADVA FSP150CC
* ADVA FSP3000 R7
* ADVA OptiSwitch
* Advantech
* Aerohive HiveOS
* Airconsole Server
* AIX
* AKCP SensorProbe
* Alcatel OmniPCX
* Alcatel-Lucent Enterprise Stellar Wireless OS (AWOS)
* Alcatel-Lucent OS
* Alcoma
* ALGCOM DC UPS
* Allied Telesis Wireless (TQ)
* Alliedware
* Alliedware Plus
* Allworx VoIP
* Alpha Comp@s
* Alpha CXC HP Controller
* Alpha FXM
* Alpine OptoElectronics TDCM-EDFA platform
* AlteonOS
* Alvarion Breeze
* Anue
* AnyOS
* APC Environmental Monitoring Unit
* APC ePDU
* APC Management Module
* APC MGE UPS
* Apex Lynx
* Apex Plus
* Apple AirPort
* Apple OS X
* Aprisa
* ApsoluteOS
* ArbOS
* Areca RAID Subsystem
* Arista EOS
* Arista MOS
* Array Networks
* Arris Apex
* Arris CMTS
* Arris D5 Universal EdgeQAM
* ARRIS DOCSIS
* Arris Satellite Receiver
* Aruba Clearpass
* Aruba Instant
* ArubaOS
* ArubaOS-CX
* Ascom
* Asentria SiteBoss
* AsusWRT Merlin
* Aten PDU
* Audiocodes
* Automatic Transfer Switch
* Avaya Scopia
* AvediaPlayer Receivers
* AvediaStream Encoder
* Aviat WTM
* Avocent
* Avtech Environment Sensor
* AXIS Audio Appliances
* AXIS Network Camera
* AXIS Network Document Server

### B
* Barco Clickshare
* Barracuda Email Security Gateway
* Barracuda Load Balancer
* Barracuda NG Firewall
* Barracuda Web Application Firewall
* BATS AATS
* BDCOM(tm) Software
* BeagleBoard
* Benu
* Bintec Be.IP Plus
* Bintec Smart Router
* BKE
* BKtel
* Blade Network Technologies
* BladeShelter PDU by PowerTek
* Blue Coat PacketShaper
* Blue Coat SSL Visibility
* Bluecat Networks
* BlueCoat ProxySG
* Broadcom BCM963xx
* Brocade FabricOS
* Brocade IronWare
* Brocade NOS
* Brocade ServerIron
* Brother Printer
* BTI SA-800
* Buffalo

### C
* C&C Power Commander plus
* Calix AXOS
* Calix B6 System
* Calix EXA
* Calix Legacy
* Cambium
* Cambium CMM
* Cambium CMM4
* Cambium cnMatrix
* Cambium cnPilot
* Cambium cnPilot Router
* Cambium cnWave60
* Cambium epmp
* Cambium PTP 250
* Cambium PTP 300/500
* Cambium PTP 600
* Cambium PTP 650
* Cambium PTP 670
* Cambium PTP 800
* Canon Printer
* Carel pCOWeb
* cdata
* Ceragon CeraOS
* CET TSI Controller
* Chatsworth PDU
* Check Point GAiA
* CheckPoint SecurePlatform
* Christie Projector
* Ciena SAOS
* Ciena Service Delivery Switch
* Ciena Waveserver
* Ciena Z-Series
* cirpack
* Cisco ACE
* Cisco ACS
* Cisco APIC
* Cisco ASA
* Cisco AsyncOS
* Cisco Catalyst 1900
* Cisco CatOS
* Cisco EPC
* Cisco FTD
* Cisco FX-OS
* Cisco Identity Services Engine
* Cisco Integrated Management Controller
* Cisco Intrusion Prevention System
* Cisco IOS
* Cisco IOS-XE
* Cisco IOS-XR
* Cisco ME1200
* Cisco Nexus 3550 Series
* Cisco NX-OS
* Cisco ONS
* Cisco PIX-OS
* Cisco SAN-OS
* Cisco Satellite Receiver
* Cisco SCE
* Cisco Services Ready Platform
* Cisco Small Business
* Cisco Unified Communications Manager
* Cisco Voice Gateway
* Cisco WAAS
* Cisco Wireless Access Point
* Cisco WLC
* Citrix Netscaler
* Comet System Web Sensor
* Comtrol Industrial Switch
* Controlbox TH-332B
* CoreOS
* Corero CMS
* Coriant TNMS
* CradlePoint WiPipe
* CTC Union
* Cumulus Linux
* CXR Networks TS
* Cyberoam UTM
* Cyberpower

### D
* D-Link Access Point
* D-Link Switch
* Dahua NVR
* Dantel Webmon
* Dantherm
* Dasan NOS
* Datacom
* dd-wrt
* DDN Storage
* Deliberant OS
* Dell DRAC
* Dell EMC Networking OS10 Enterprise
* Dell EqualLogic
* Dell Laser
* Dell Networking OS
* Dell OpenManage Enterprise Modular
* Dell PowerConnect
* Dell PowerVault
* Dell PowerVault MD
* Dell Rack PDU
* Dell Remote Console
* Dell Storage
* Dell UPS
* Delta Orion Controller
* Delta UPS
* Develop Printer
* DHCPatriot
* Digipower
* Digital China Networks
* DKT Comega
* DPS Telecom NetGuardian
* DragonflyBSD
* Dragonwave Harmony Enhanced
* Dragonwave Horizon Compact
* Dragonwave Horizon Compact Plus
* Dragonwave Horizon Duo
* DrayTek
* DVB Modulator & Ampiflier
* DVB-T Transmitter

### E
* E3 Meter
* E3 Meter DataConcentrator
* Eagle-I
* East
* Eaton ATS
* Eaton Matrix
* Eaton MGE PDU
* Eaton PDU
* Eaton SC200 Controller
* Eaton UPS
* EDFA
* Edgecore
* EdgeOS
* EdgeSwitch
* EDS
* EfficientIP SOLIDserver
* Ekinops Optical
* Eltek Valere
* Eltek Valere eNexus
* Eltek WebPower
* Eltex OLT
* Eltex-MES21xx
* Eltex-MES23xx
* EMC Data Domain
* EMC Flare OS
* EMC Isilon OneFS
* Emerson Energy System
* Emerson Netsure
* Endian
* EndRun
* EnGenius Access Point
* enLogic PDU
* Enterasys
* Epson Printer
* Epson Projector
* Ericsson 6600 series
* Ericsson IPOS
* Ericsson LG iPECS UCP
* Ericsson MINI-LINK
* Ericsson Traffic Node
* EricssonLG IPECS ES
* Etherwan Managed Switch
* EUROstor RAID Subsystem
* Exagrid
* Exalt ExtendAir
* Exinda
* Extrahop Appliance
* Extreme BOSS
* Extreme SLX-OS
* Extreme VOSS
* Extreme Wireless Convergence
* Extreme XOS
* Extremeware

### F
* F5 Big IP
* F5OS
* Fiberhome
* FiberHome Switch
* Fibernet XMUX 4+
* Fiberstore GBN
* Fiberstore Switch
* Firebrick
* FireEye OS
* Force10 FTOS
* Fortinet Application Deliver Controller
* Fortinet FortiAuthenticator
* Fortinet Fortigate
* Fortinet FortiMail
* Fortinet FortiSandbox
* Fortinet FortiSwitch
* Fortinet FortiVoice
* Fortinet FortiWeb
* Fortinet FortiWLC
* FortiOS
* Foundry Networking
* FreeBSD
* FreshTomato
* FS.COM monitored pdu
* Fujitsu
* Fujitsu ETERNUS
* FUJITSU iRMC
* Fujitsu NAS
* FusionHub

### G
* Gamatronic UPS Stack
* Gandi Packet Journey
* GE Digital Energy UPS
* GE MDS Orbit network Operating System
* GE Pulsar
* Geist PDU
* Geist Watchdog
* Generex UPS SNMP adapter
* Generic
* Generic Device
* Gestetner Printer
* GigaVUE
* Glass Way WDM EYDFA
* Grandstream HT
* Greenbone OS
* Gude Expert Transfer Switch
* gwd

### H
* Halon
* Hanwha Techwin
* HAProxy ALOHA
* Hardware Appliance
* Hatteras Overdue DSLAM
* Helios IP
* Hikvision Camera
* Hikvision NVR
* Hillstone StoneOs
* Himoinsa Generator Sets
* Hirschmann Railswitch
* Hitachi Storage Virtualization Operating System (SVOS)
* HP Blade Management
* HP MSM
* HP PDU Management Module
* HP Print server
* HP ProCurve
* HP UPS
* HP Virtual Connect
* HPE 3PAR
* HPE Comware
* HPE Integrated Lights Out
* HPE iPDU
* HPE Managed Power Distribution Unit
* HPE MSA
* HPE OpenVMS
* HPE StoreEver MSL
* Huawei iBMC Management Console
* Huawei OceanStor
* Huawei SmartAX
* Huawei SmartAX MDU
* Huawei SMU
* Huawei UPS
* Huawei VRP
* HWg Poseidon
* HWg STE
* HWg STE2
* HWg WLD
* Hytera Repeater

### I
* IBM AMM
* IBM DPI
* IBM i
* IBM IMM
* IBM Networking Operating System
* IBM Tape Library
* iBoot PDU
* Icotera OS
* ICR-OS
* ICT Digital Series Power Supply
* ICT Distribution Series
* ICT Modular Power System
* ICT Sine Wave Inverter
* Ifotec
* IgniteNet FusionSwitch
* IgniteNet HeliOS
* Illustra Network Camera
* Imco Power
* Imco Power LS110
* Infinera Groove
* Infinera PON
* Infinera XTM
* Infoblox
* Ingrasys iPoMan
* Innovaphone ISDN
* Inteno GW
* IONODES
* IP Infusion OcNOS
* IP Office Firmware
* ITWatchDogs Goose

### J
* Jacarta InterSeptor
* Janitza
* Janitza UMG96
* Juniper EX2500
* Juniper JunOS
* Juniper JunOSe
* Juniper JWOS
* Juniper MSS
* Juniper ScreenOS

### K
* Kemp Loadbalancer
* Konica-Minolta Printer
* KTI
* Kyocera Mita Printer

### L
* Lambdatrail
* Lancom OS
* Lanier Printer
* LANTIME v6
* Lantronix SLC
* Lantronix UDS
* Last Mile Gear CTM
* Lenovo Cloud Network Operating System
* Lenovo XCC IMPI
* LenovoEMC
* Lexmark Printer
* Liebert
* LigoWave Infinity
* LigoWave LigoOS
* Linksys Smart Switch
* Linux
* Loadbalancer.org
* LogMaster
* Loop Telecom Operating System

### M
* m0n0wall
* Maipu MyPower
* Marathon UPS
* McAfee Linux OS - ATD
* McAfee Linux OS - NSP
* McAfee SIEM Nitro
* Mcafee Web Gateway
* MegaTec NetAgent II
* Mellanox
* Meraki AP
* Meraki MX Appliance
* Meraki Switch
* Microsemi PowerDsine Midspan PoE
* Microsemi Synchronization Supply Unit
* Microsoft Windows
* Mikrotik RouterOS
* Mikrotik SwOS
* Mimosa
* Minkels RMS
* Mirth Connect
* Mitel Standard Linux
* MNI Microwave Radio
* MobileIron
* Montclair EDFA
* Motorola DOCSIS Cable Modem
* Motorola Netopia
* Moxa
* Moxa AWK
* MRV LambdaDriver
* MRV OptiDriver

### N
* NEC Univerge
* NetApp
* NetBotz Environment Sensor
* NetBSD
* Netgear ProSafe
* NetMan Plus
* NetModule
* Netonix
* NetScaler SD-WAN
* Network Management Unit
* Nexans
* Nimble OS
* NOKIA ISAM
* Nokia SR OS (TiMOS)
* Novell Netware
* NRG Printer
* NTI
* Nutanix AOS
* NVT Phybridge

### O
* OKI Printer
* Omnitron iConverter
* OneAccess
* Open Access Netspire
* Open-E
* OpenBSD
* Opengear
* OpenIndiana
* OpenSystems
* OpenWrt
* OPNsense
* Oracle ILOM
* Orvaldi UPS

### P
* Packetflux SiteMonitor
* Packetlight
* Panasonic KX-NS Series
* Panduit PDU
* PanOS
* Papouch QUIDO
* Papouch TME
* Paradyne (by Zhone)
* Patton SmartNode
* PBI Digital Decoder
* PBN
* PBN P2P CP100 Series Platform
* Pegasus
* Pepwave
* Perle
* pfSense
* Pica8 OS
* Ping only
* PLANET
* Polycom Videoconferencing System
* Powercode BMU
* PowerWalker UPS
* PowerWalker VFI
* Prime Infrastructure
* Procera Networks
* Proxim
* proxmox pve
* Pulse Secure

### Q
* QNAP TurboNAS
* QTECH
* Quanta
* QuantaStor

### R
* Radlan
* RADWIN
* Raisecom ROAP
* Raisecom ROS
* Raritan EMX
* Raritan KVM
* Raritan PDU
* RAy
* RAy3
* RecoveryOS
* Red Lion Sixnet
* Redback Networks SmartEdge
* Redlion N-Tron
* Ribbon GSX
* Ribbon SBC
* Ricoh Printer
* Rittal CMC
* Rittal CMC III PU
* Rittal IT Chiller
* Rittal LCP
* Rittal LCP DX Chiller
* Riverbed
* RNX UPDU
* Rohde & Schwarz
* Rubrik
* Ruckus Wireless HotZone
* Ruckus Wireless SmartZone
* Ruckus Wireless Unleashed
* Ruckus Wireless ZoneDirector
* Ruijie Networks

### S
* SAF CFM
* SAF Integra B
* SAF Integra E
* SAF Integra W
* SAF Integra X
* SAF Tehnika
* Sagem ADR IONOS
* Samsung Printer
* Savin Printer
* Schleifenbauer SPDM
* Schneider PowerLogic
* SCS KS
* Sensatronics EM1
* Sensatronics ITTM
* ServersCheck
* ServerTech Sentry3
* ServerTech Sentry4
* Sharp Printer
* SIAE Alfoplus 80HD
* Siemens Ruggedcom Switches (ROS)
* Siemens SCALANCE
* Siklu Wireless
* Silver Peak VXOA
* Sinetica UPS
* SM-OS
* SmartOptics DCP-M Series
* SmartOptics M-Series
* SmartOptics T-Series
* snr
* snr-erd
* Socomec Net Vision
* Socomec PDU
* Software Appliance
* Solid Optics EDFAMUX
* SonicWALL
* Sophos UTM Firewall
* Sophos XG
* Stormshield NS-BSD
* Stulz GMBH Klimatechnik
* Sub10 Systems
* Sun OpenSolaris
* Sun Solaris
* Supermicro Switch
* Symbol AP
* SyncServer
* Synology DSM

### T
* Tait Infra93 Series
* Tait TN Admin OS
* Tandberg Magnum
* technicolor TG MediaAcces
* Tegile IntelliFlash
* Telco Systems BiNOS
* Telco Systems BiNOX
* Teldat
* TelePresence Codec
* TelePresence Conductor
* Teleste Luminato
* teltonika rutos
* Teltonika RutOS RUTX Series
* Teradici PCoIP
* Terra
* Thomson DOCSIS Cable Modem
* Thomson Speedtouch
* Tomato
* TopVision
* Toshiba Printer
* Toshiba RemotEye4
* TP-Link JetStream
* TP-Link Switch
* Transition
* Tranzeo
* TRENDnet Switch
* Tripp Lite PowerAlert
* TrueNAS
* TSC Printer
* Tycon Systems TPDIN

### U
* Ubiquiti AirFiber
* Ubiquiti AirFiber 60
* Ubiquiti AirFiber LTU
* Ubiquiti AirOS
* Ubiquiti Edgepower
* Ubiquiti UniFi
* Ubiquoss PON
* Ucopia
* UFiber
* UHP Networks VSAT Terminal
* UniPing

### V
* V-Solution
* Vanguard ApplicationsWare
* Vertiv Avocent MergePoint Unity
* Vertiv PDU
* Video Communication Server
* Viprinux
* Viptela
* Vivotek Camera
* VMware ESXi
* VMware SD-WAN
* VMware vCenter
* Volius
* Voswall
* Vubiq Networks
* Vutlan
* Vyatta
* VyOS

### W
* Watchguard Fireware
* Waystream iBOS
* Web-Thermo-Hygrometer
* WebPower
* West Mountain RMCU
* WISI Tangram
* WTI CONSOLE
* WTI MPC
* WTI POWER

### X
* Xerox Printer
* Xirrus ArrayOS

### Z
* ZebraNet
* Zhone MXK
* Zmtel Greenpacket
* ZTE ZXA10
* ZTE ZXR10
* ZXDSL
* ZyXEL AC
* ZyXEL DSLAM
* ZyXEL Ethernet Switch
* ZyXEL IES MSAN
* ZyXEL IES-5000 DSLAM
* ZyXEL NWA
* ZyXEL Prestige
* ZyXEL ZyWALL
