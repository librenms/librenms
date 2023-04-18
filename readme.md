

Initial Librenms Definitions, Icon's, MIB's and Discovery for a few device models. These have been working well for a few months onsite now (with a few minor mods). The modem's and RFU's on the 2Wcx and 4Wcx are accessed via community.x (1-2 for 2Wcx and 1-4 for 4Wcx) for .1807.55 OID's, unable to get more than one at the minute. Each modem's configuration and ability will vary (see below in known issues). The other units here only have single modems.

Units covered here: Ultralink-GX80, Ultralink-FX80, OmniBas-2Wcx, OmniBas-4Wcx, OmniBas-BX and OmniBas-OSDR (combined)

Known Issues: (only with 2Wcx and 4Wcx)
* Only gets one Modem/RFU - In SNMPv2 as defined above (each modem/RFU, different community), in SNMPv3 defined as per context from what i can gather. Only an issue with 2Wcx and 4Wcx models that have multiple modem's and RFU's. 
* The state values for "modemStatPHYModValue" & "modemStatRxPHYModValue" should be derived from .1.3.6.1.4.1.1807.55.1.1.2.1.2. - .1 to .16 on each modem as these are the values for the current configuration. Static QAM value set in the discovery/poller are kind of suitable for most configurations to give an indication. Below is an example showing the running configurations values of two systems, these will differ with different modem configurations in the same chassis. As above based on SNMP community.x or SNMPv3 context for modems and RFU's. 
* RFU and Modem temperatures as above (basically anything in enterprise.1807.55.) realates to specific SNMP community.x or SNMPv3.context if 2Wcx or 4Wcx
* No information is displayed in the main "overall traffic graph", go to specific port for it's details.
* Some work arounds for modem and RFU values by getting 1807.71 performance history OID's.

Values based on current configuration (not sure how to get these to be the state values without using a PHP script i guess, same with each modem/rfu values as they are all related). Any suggestions welcome.
-------------------------------------------------------------------------------------
iso.3.6.1.4.1.1807.55.1.1.2.1.2.1 = STRING: "(v1.17) 4QAM   74.6 56MHz atpc-Max_Gain"
iso.3.6.1.4.1.1807.55.1.1.2.1.2.2 = STRING: "(v1.17) 16QAM   149.2 56MHz atpc-Max_Gain"
iso.3.6.1.4.1.1807.55.1.1.2.1.2.3 = STRING: "(v1.17) 32QAM   186.5 56MHz atpc-Max_Gain"
iso.3.6.1.4.1.1807.55.1.1.2.1.2.4 = STRING: "(v1.17) 64QAM   250.7 56MHz atpc-Max_Gain"
iso.3.6.1.4.1.1807.55.1.1.2.1.2.5 = STRING: "(v1.17) 128QAM   301.4 56MHz atpc-Max_Gain"
iso.3.6.1.4.1.1807.55.1.1.2.1.2.6 = STRING: "(v1.17) 256QAM   352.2 56MHz atpc-Max_Gain"
iso.3.6.1.4.1.1807.55.1.1.2.1.2.7 = STRING: "(v1.17) 512QAM   402.9 56MHz atpc-Max_Gain"
iso.3.6.1.4.1.1807.55.1.1.2.1.2.8 = STRING: "(v1.17) 1024QAM   453.6 56MHz atpc-Max_Gain"
iso.3.6.1.4.1.1807.55.1.1.2.1.2.9 = STRING: "(v1.17) 2048QAM   504.4 56MHz atpc-Max_Gain"
iso.3.6.1.4.1.1807.55.1.1.2.1.2.10 = STRING: "(v1.17) 4096QAM   555.1 56MHz atpc-Max_Gain"
iso.3.6.1.4.1.1807.55.1.1.2.1.2.11 = ""
iso.3.6.1.4.1.1807.55.1.1.2.1.2.12 = ""
iso.3.6.1.4.1.1807.55.1.1.2.1.2.13 = ""
iso.3.6.1.4.1.1807.55.1.1.2.1.2.14 = ""
iso.3.6.1.4.1.1807.55.1.1.2.1.2.15 = ""
iso.3.6.1.4.1.1807.55.1.1.2.1.2.16 = ""
----------------------------------------------------------------------------------------
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.1 = STRING: "(v3.46) 4QAM   103.2 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.2 = STRING: "(v3.46) 4QAM   103.2 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.3 = STRING: "(v3.46) 16QAM   206.3 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.4 = STRING: "(v3.46) 16QAM   206.3 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.5 = STRING: "(v3.46) 32QAM   257.8 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.6 = STRING: "(v3.46) 64QAM   346.5 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.7 = STRING: "(v3.46) 128QAM   416.6 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.8 = STRING: "(v3.46) 256QAM   486.8 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.9 = STRING: "(v3.46) 512QAM   556.9 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.10 = STRING: "(v3.46) 1024QAM   627.0 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.11 = STRING: "(v3.46) 2048QAM   697.1 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.12 = STRING: "(v3.46) 4096QAM   767.3 80MHz atpc-Enh_Acm"
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.13 = ""
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.14 = ""
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.15 = ""
SNMPv2-SMI::enterprises.1807.55.1.1.2.1.2.16 = ""
------------------------------------------------------------------------------------------------------------
