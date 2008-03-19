-----------------------------------------------------------------------------------------------------------------

                                                README.TXT

                                                -----------

snmpv1      –  6.1 enterprise mibs in SMI v1 format
snmpv2      –  6.1 enterprise mibs in SMI v2 format
ipv6        –  6.1 enterprise ipv6 mibs in SMI v2 format



In addition to the above the following rfc's are supported:

RFC1213 - MIBII
RFC1657 - BGP4 MIB
RFC1724 – RIPV2
RFC2465 - IPV6 MIB
RFC2466 - IPV6 ICMP MIB
RFC2863 - INTERFACES MIB
RFC2495 - DS1 MIB
RFC3896 - DS3 MIB
RFC1850 - OSPF MIB
RFC3020 - MFR MIB
RFC2115 - Frame Relay DTE MIB
 

Notes: RFC2115, RFC3020, RFC2495 and RFC3896 are not fully supported due to an implementation limitation.

-----------------------------------------------------------------------------------------------------------------

ScreenOS Standard Traps
=======================

RFC	MIB 	          TrapName	             Supported y/n
------------------------------------------------------------------
1213	MIB II	          authenticationFailure        y
1213	MIB II	          coldStart	               y
1213	MIB II	          warmStart	               n
1659	BGP4 	          bgpEstablished	       y
1659	BGP4 	          bgpBackwardTransition        y
1724	RIPv2                                          
2465	IPv6	          ipv6IfStateChange	       n
2466	IPv6 ICMP                                      
2863	Interface         linkUp	               y
2863	Interface         linkDown	               y
2495	DS1	          dsx1LineStatusChange	       y
3896	DS3	          dsx3LineStatusChange	       y
1850	OSPF	          ospfIfStateChange	       y
1850	OSPF	          ospfVirtIfStateChange        y
1850	OSPF	          ospfNbrStateChange	       y
1850	OSPF	          ospfVirtNbrStateChange       y
1850	OSPF	          ospfVirtIfConfigError        y
1850	OSPF	          ospfVirtIfAuthFailure        y
1850	OSPF	          ospfIfRxBadPacket	       y
1850	OSPF	          ospfVirtIfRxBadPacke	       y
1850	OSPF	          ospfTxRetransmit	       y
1850	OSPF	          ospfVirtIfTxRetransmit       y
1850	OSPF	          ospfOriginateLsa	       y
1850	OSPF	          ospfMaxAgeLsa	               y
1850	OSPF	          ospfLsdbOverflow	       y
1850	OSPF	          ospfLsdbApproachingOverflow  y
3020	MFR	          mfrBundleLinkMismatch	       y
2115	Frame Relay DTE	  frDLCIStatusChange           y


------------------------------------------< end >----------------------------------------------------------------
