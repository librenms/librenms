source: Carel-pCOweb-Devices.md
path: blob/master/doc/

# Carel pCOweb Devices

The pCOWeb card is used to interface the pCO system to networks that
use the HVAC protocols based on the Ethernet physical standard such
the SNMP. The problem with this card is that the implementation is
based on the final manufacturer of the HVAC (Heating, Ventilation and
Air Conditioning) and not based on a standard given by Carel. So each
pCOweb card has a different configuration that needs a different MIB
depending on the manufacturer implementation.

The main problem is that LibreNMS will discover this card as pCOweb
and not as your real manufacturer like it should. A solution was found
to bypass this issue, but it's LibreNMS independent and you need to
first configure your pCOWeb through the admin interface.

## Configuring the pCOweb card SNMP for LibreNMS

First you need to configure your SNMP card using the admin
interface. An SNMP tab in the configuration menu leaves you the choice
to choose a System OID. This is a little tricky but based on this
information we defined a "standard" for all implementation of Carel
products with LibreNMS.

![pCOweb](/img/carelpcowebsystemoid.png)

The base Carel OID is 1.3.6.1.4.1.9839. To this OID we will add the
final manufacturer Enterprise OID. You can find all enterprise OID
[following this
link](https://www.iana.org/assignments/enterprise-numbers/enterprise-numbers). This
will allow us to create a specific support for this device.

Example for the Rittal IT Chiller that uses a pCOweb card:

1. Base Carel OID : **1.3.6.1.4.1.9839**
1. Rittal (the manufacturer) base enterprise OID : **2606**
1. Adding value to identify this device in LibreNMS : **1**
1. Complete System OID for a Rittal Chiller using a Carel pCOweb card: **1.3.6.1.4.1.9839.2606.1**

After constructing the correct System OID for your SNMP card, you can
start the LibreNMS [new OS implementation](Developing/Support-New-OS/)
and use this new OID as sysObjectID for the YAML definition file.

## pCOweb card already supported

LibreNMS is ready for the devices listed in this table. You only need
to configure your pCOweb card with the accorded System OID:

| Manufacturer | Description | System OID |
| ------------ | ------------- | ------------ |
| Rittal | IT Chiller | 1.3.6.1.4.1.9839.2606.1 |
