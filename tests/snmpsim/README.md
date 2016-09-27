## Instructions
Copy the skel.snmprec file to the desired filename this must start with the os name. This skel.snmprec file contains empty sysDescr and sysObjectID entries.

## File format
Data file format is optimized to be compact, human-readable and inexpensive to parse. It's also important to store full and exact response information in a most intact 
form. Here's an example data file content:

1.3.6.1.2.1.1.1.0|4|Linux 2.6.25.5-smp SMP Tue Jun 19 14:58:11 CDT 2007 i686
1.3.6.1.2.1.1.2.0|6|1.3.6.1.4.1.8072.3.2.10
1.3.6.1.2.1.1.3.0|67|233425120
1.3.6.1.2.1.2.2.1.6.2|4x|00127962f940
1.3.6.1.2.1.4.22.1.3.2.192.21.54.7|64x|c3dafe61
There is a pipe-separated triplet of OID-tag-value items where:

OID is a dot-separated set of numbers.
Tag is a BER-encoded ASN.1 tag. When value is hexified, an 'x' literal is appended. Reference to a variation module can also be embedded into tag.
Value is either a printable string, a number or a hexifed value.
Valid tag values and their corresponding ASN.1/SNMP types are:

| type | value |
| --- | --- |
| Integer32 | 2 |
| OCTET STRING | 4 |
| NULL | 5 |
| OBJECT IDENTIFIER | 6 |
| IpAddress | 64 |
| Counter32 | 65 |
| Gauge32 | 66 |
| TimeTicks | 67 |
| Opaque | 68 |
| Counter64 | 70 |
