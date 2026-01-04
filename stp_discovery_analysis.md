# STP Discovery Analysis - 10.0.0.201 vs 10.0.0.4

## Problem Summary

**10.0.0.201 (IOS Switch)**: Doesn't discover STP instances, although VLAN 100 and 102 should have them  
**10.0.0.4 (NX-OS Switch)**: Successfully discovers STP instances for all VLANs

---

## 1. Device Information

### 10.0.0.201 (Cisco IOS)
```
OS: ios
Model: C1000
Software: Version 15.2(7)E4
STP Type: pvstPlus
VLANs: 1, 100, 102, 1002-1005
```

### 10.0.0.4 (Cisco NX-OS)
```
OS: nxos
Software: Version 7.0(3)I3(1)
STP Type: rapidPvstPlus  
VLANs: 1, 100, 101, 102, 109, 111, ... (many VLANs)
```

---

## 2. Discovery Process Comparison

### 10.0.0.201 - FAILED
```
1. SNMP GET: CISCO-STP-EXTENSIONS-MIB::stpxSpanningTreeType.0
   ✓ Result: pvstPlus

2. Foreach VLAN iteration:
   
   [VLAN 1] → context = NULL
   SNMP GET: BRIDGE-MIB::dot1dStpProtocolSpecification.0
   ✗ Result: No Such Instance currently exists at this OID
   → Returns empty collection
   
   [VLAN 100] → context = NULL  ⚠️ PROBLEM!
   SNMP GET: BRIDGE-MIB::dot1dStpProtocolSpecification.0
   ✗ Result: No Such Instance currently exists at this OID
   → Returns empty collection
   
   [VLAN 102] → context = NULL  ⚠️ PROBLEM!
   SNMP GET: BRIDGE-MIB::dot1dStpProtocolSpecification.0
   ✗ Result: No Such Instance currently exists at this OID
   → Returns empty collection
   
   [VLAN 1002-1005] → context = NULL
   SNMP GET: BRIDGE-MIB::dot1dStpProtocolSpecification.0
   ✗ Result: No Such Instance currently exists at this OID
   → Returns empty collection

Result: 0 STP instances
```

### 10.0.0.4 - SUCCESS
```
1. SNMP GET: CISCO-STP-EXTENSIONS-MIB::stpxSpanningTreeType.0
   ✓ Result: rapidPvstPlus

2. Foreach VLAN iteration:
   
   [VLAN 1] → context = NULL
   SNMP GET: BRIDGE-MIB::dot1dStpProtocolSpecification.0
   ✓ Result: 1 (unknown)
   → STP instance created
   
   [VLAN 100] → context = '100'  ✓ CORRECT!
   SNMP GET@100: BRIDGE-MIB::dot1dStpProtocolSpecification.0
   ✓ Result: 1 (unknown)
   → STP instance created
   
   [VLAN 101] → context = '101'  ✓ CORRECT!
   SNMP GET@101: BRIDGE-MIB::dot1dStpProtocolSpecification.0
   ✓ Result: 1 (unknown)
   → STP instance created
   
   ... (same for all VLANs)

Result: 69 STP instances
```

---

## 3. Code Analysis - BUG FOUND!

### LibreNMS/OS/Shared/Cisco.php line 676

```php
foreach ($vlans->isEmpty() ? [null] : $vlans as $vlan) {
    $vlan = (empty($vlan->vlan_vlan) || $vlan->vlan_vlan == '1') ? null : (string) $vlan->vlan_vlan;
    $instance = parent::discoverStpInstances($vlan);
    //...
}
```

**PROBLEM**: Variable `$vlan` is being OVERWRITTEN!

1. The foreach uses `$vlan` variable to store the VLAN object
2. Then the SAME `$vlan` variable is OVERWRITTEN with a string or null
3. In the next iteration, foreach tries to use `$vlan` as an object, but it's already a string!

### Why does it work for 10.0.0.4?

For 10.0.0.4, it **works by accident** because:
- After the first iteration, `$vlan` is indeed overwritten
- BUT PHP's foreach continues to use the original collection iterator
- In the next iteration, foreach OVERWRITES `$vlan` again with the object
- So in each iteration, the correct value is restored

### Why does it NOT work for 10.0.0.201?

**For 10.0.0.201, something works DIFFERENTLY:**

Looking at the debug output more carefully:

```
SNMP['/usr/bin/snmpget' ... 'BRIDGE-MIB::dot1dStpProtocolSpecification.0']
No Such Instance currently exists at this OID

SNMP['/usr/bin/snmpget' ... 'BRIDGE-MIB::dot1dStpProtocolSpecification.0']
No Such Instance currently exists at this OID

SNMP['/usr/bin/snmpget' ... 'BRIDGE-MIB::dot1dStpProtocolSpecification.0']  
No Such Instance currently exists at this OID
```

**Every query happens WITHOUT CONTEXT!**

---

## 4. Manual SNMP Test Results

### 10.0.0.201

```bash
# Without context (VLAN 1)
snmpget -v2c -c public 10.0.0.201 BRIDGE-MIB::dot1dStpProtocolSpecification.0
→ No Such Instance currently exists at this OID
✗ NO STP on VLAN 1

# With context (VLAN 100)
snmpget -v2c -c public@100 10.0.0.201 BRIDGE-MIB::dot1dStpProtocolSpecification.0
→ INTEGER: ieee8021d(3)
✓ STP EXISTS on VLAN 100!

# With context (VLAN 102)
snmpget -v2c -c public@102 10.0.0.201 BRIDGE-MIB::dot1dStpProtocolSpecification.0
→ INTEGER: ieee8021d(3)
✓ STP EXISTS on VLAN 102!
```

**Conclusion**: VLAN context MUST be used! STP works on VLAN 100 and 102!

---

## 5. ROOT CAUSE IN CODE

### BridgeMib.php line 42-43

```php
public function discoverStpInstances(?string $vlan = null): Collection
{
    $protocol = SnmpQuery::get('BRIDGE-MIB::dot1dStpProtocolSpecification.0')->value();
```

**It does NOT use the `$vlan` context here!**

Context is only used later, at line 53:
```php
$stp = SnmpQuery::context("$vlan", 'vlan-')->enumStrings()->get([...
```

**BUT**: If the `$protocol` check (line 45) returns an empty collection, it never reaches line 53!

```php
if ($protocol != 1 && $protocol != 3) {
    return new Collection;  // ← EARLY EXIT!
}
```

---

## 6. Why Does It Work for 10.0.0.4?

**10.0.0.4 (NX-OS) response WITHOUT context:**
```
SNMP GET: BRIDGE-MIB::dot1dStpProtocolSpecification.0
→ Result: 1 (unknown)
```

NX-OS device **returns** a value (1 = unknown) even without context!
→ `$protocol = 1`
→ `if ($protocol != 1 && $protocol != 3)` → FALSE
→ Continues execution
→ Later queries use the context

---

## 7. Why Does It NOT Work for 10.0.0.201?

**10.0.0.201 (IOS) response WITHOUT context:**
```
SNMP GET: BRIDGE-MIB::dot1dStpProtocolSpecification.0
→ Result: No Such Instance currently exists at this OID
```

IOS device does NOT return anything without context!
→ `$protocol = null` (or false)
→ `if ($protocol != 1 && $protocol != 3)` → TRUE
→ `return new Collection;` ← EARLY EXIT!
→ **Never tries with context!**

---

## 8. THE BUG FIX

### BridgeMib.php - Current (BUGGY) code:

```php
public function discoverStpInstances(?string $vlan = null): Collection
{
    $protocol = SnmpQuery::get('BRIDGE-MIB::dot1dStpProtocolSpecification.0')->value();
    // 1 = unknown (mstp?), 3 = ieee8021d

    if ($protocol != 1 && $protocol != 3) {
        return new Collection;
    }
    
    // ... rest of code
}
```

### FIX #1: Use context for protocol check as well

```php
public function discoverStpInstances(?string $vlan = null): Collection
{
    $protocol = SnmpQuery::context("$vlan", 'vlan-')->get('BRIDGE-MIB::dot1dStpProtocolSpecification.0')->value();
    // 1 = unknown (mstp?), 3 = ieee8021d

    if ($protocol != 1 && $protocol != 3) {
        return new Collection;
    }
    
    // ... rest of code
}
```

### FIX #2: Skip protocol check for context-based queries

```php
public function discoverStpInstances(?string $vlan = null): Collection
{
    // Only check protocol without context
    if ($vlan === null) {
        $protocol = SnmpQuery::get('BRIDGE-MIB::dot1dStpProtocolSpecification.0')->value();
        if ($protocol != 1 && $protocol != 3) {
            return new Collection;
        }
    }
    
    // ... rest of code
}
```

---

## 9. Summary

| Factor | 10.0.0.201 (IOS) | 10.0.0.4 (NX-OS) |
|--------|------------------|------------------|
| **STP Running?** | Yes (VLAN 100, 102) | Yes (all VLANs) |
| **BRIDGE-MIB without context** | No Such Instance | unknown (1) |
| **Protocol check result** | null → return | 1 → continue |
| **Context used?** | NO (early exit) | YES |
| **Discovery result** | 0 instances | 69 instances |

**Conclusion**:
- BridgeMib trait checks protocol **without context**
- IOS devices **don't respond** without context
- Code **exits early** before trying with context
- NX-OS devices **respond** even without context, so it works
- **The bug**: Protocol check must **either use context** or **be skipped when context is present**

---

## 10. Recommended Fix

```php
public function discoverStpInstances(?string $vlan = null): Collection
{
    // Protocol check with context support
    $protocol = SnmpQuery::context("$vlan", 'vlan-')->get('BRIDGE-MIB::dot1dStpProtocolSpecification.0')->value();
    
    // 1 = unknown (mstp?), 3 = ieee8021d
    if ($protocol != 1 && $protocol != 3) {
        return new Collection;
    }

    $timeFactor = $this->stpTimeFactor ?? 0.01;

    // fetch STP config and store it
    $stp = SnmpQuery::context("$vlan", 'vlan-')->enumStrings()->get([
        'BRIDGE-MIB::dot1dBaseBridgeAddress.0',
        // ... rest of OIDs
    ])->values();
    
    // ... rest of code
}
```

This uses context consistently for all queries.
