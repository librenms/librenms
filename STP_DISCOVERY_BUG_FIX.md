# LibreNMS STP Discovery Bug Fix

## Probléma

A LibreNMS STP discovery nem működött Cisco IOS eszközökön (pl. 10.0.0.201), miközben NX-OS eszközökön (pl. 10.0.0.4) helyesen működött.

### Tünetek
- IOS eszközön: 0 STP instance felfedezve, pedig VLAN 100 és 102-n aktív STP
- NX-OS eszközön: Minden VLAN-ra helyesen felfedezte az STP instance-okat

## Gyökérok

A `LibreNMS/OS/Traits/BridgeMib.php` trait `discoverStpInstances()` metódusában a protokoll ellenőrzés **VLAN context nélkül** történt:

```php
// HIBÁS KÓD (line 42)
public function discoverStpInstances(?string $vlan = null): Collection
{
    $protocol = SnmpQuery::get('BRIDGE-MIB::dot1dStpProtocolSpecification.0')->value();
    // ↑ Nincs context használva!
    
    if ($protocol != 1 && $protocol != 3) {
        return new Collection; // Korai kilépés
    }
    
    // A későbbi lekérdezések már context-et használnak
    $stp = SnmpQuery::context("$vlan", 'vlan-')->enumStrings()->get([...
}
```

### Miért működött NX-OS-en, de nem IOS-en?

**NX-OS eszközök** context nélkül is visszaadnak egy értéket:
```
SNMP GET: BRIDGE-MIB::dot1dStpProtocolSpecification.0
→ Result: 1 (unknown)
→ Folytatja a kódot, később használja a context-et
```

**IOS eszközök** context nélkül NEM válaszolnak:
```
SNMP GET: BRIDGE-MIB::dot1dStpProtocolSpecification.0
→ Result: No Such Instance currently exists at this OID
→ $protocol = null
→ Korai kilépés, soha nem használja a context-et!
```

## Megoldás

A protokoll ellenőrzés **context-tel** történik:

```php
// JAVÍTOTT KÓD
public function discoverStpInstances(?string $vlan = null): Collection
{
    $protocol = SnmpQuery::context("$vlan", 'vlan-')->get('BRIDGE-MIB::dot1dStpProtocolSpecification.0')->value();
    // ↑ Most már context-tel történik!
    
    if ($protocol != 1 && $protocol != 3) {
        return new Collection;
    }
    
    // ... rest of the code
}
```

## Tesztelés

### Előtte (IOS eszköz 10.0.0.201)
```sql
SELECT * FROM stp WHERE device_id = 33;
-- 0 sor
```

### Utána (IOS eszköz 10.0.0.201)
```sql
SELECT vlan, rootBridge, bridgeAddress, protocolSpecification FROM stp WHERE device_id = 33;
-- VLAN 100: Non-root bridge, ieee8021d
-- VLAN 102: Non-root bridge, ieee8021d
```

### SNMP teszt eredmények

```bash
# Context nélkül (nem működik)
$ snmpget -v2c -c public 10.0.0.201 BRIDGE-MIB::dot1dStpProtocolSpecification.0
→ No Such Instance currently exists at this OID

# Context-tel (működik!)
$ snmpget -v2c -c public@100 10.0.0.201 BRIDGE-MIB::dot1dStpProtocolSpecification.0
→ INTEGER: ieee8021d(3)

$ snmpget -v2c -c public@102 10.0.0.201 BRIDGE-MIB::dot1dStpProtocolSpecification.0
→ INTEGER: ieee8021d(3)
```

## Változtatott fájl

- **Fájl**: `/opt/librenms/LibreNMS/OS/Traits/BridgeMib.php`
- **Sor**: 42
- **Változtatás**: `SnmpQuery::get(...)` → `SnmpQuery::context("$vlan", 'vlan-')->get(...)`

## Érintett eszközök

A fix javítja a discovery-t minden olyan Cisco IOS eszközön, amely:
- VLAN-onként futtat STP-t (PVST+, Rapid-PVST+)
- Context nélkül nem válaszol BRIDGE-MIB lekérdezésekre
- VLAN context-tel (@vlan-ID) helyesen válaszol

Tipikusan érintett modellek:
- Catalyst 1000 sorozat
- Catalyst 2960 sorozat
- Catalyst 3560/3750 sorozat
- IOS 12.x, 15.x verziókat futtató switche-ek

## Impact

- **Pozitív**: IOS eszközökön megjelenik az STP információ a WebUI-ban
- **Negatív**: Nincs (a NX-OS és egyéb eszközök továbbra is működnek)
- **Teljesítmény**: Minimális változás (1 extra SNMP GET hívás per VLAN)

## Dátum

2026-01-02

## Tesztelők

- IOS eszköz: 10.0.0.201 (Cisco C1000, IOS 15.2(7)E4)
- NX-OS eszköz: 10.0.0.4 (Nexus, NX-OS 7.0(3)I3(1))
