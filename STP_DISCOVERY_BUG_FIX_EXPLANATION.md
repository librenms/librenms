# LibreNMS STP Discovery Bug Fix - Technical Explanation

## Executive Summary

This document explains the root causes, investigation process, and implementation details of fixes for STP (Spanning Tree Protocol) discovery issues in LibreNMS when monitoring Cisco IOS devices.

## Problem Statement

### What Happened

STP discovery was completely failing on Cisco IOS devices (specifically Catalyst 1000 series), while working correctly on Cisco NX-OS devices. The symptoms were:

1. **No STP instances discovered** for VLANs with active STP (VLANs 100 and 102)
2. **No STP port information** collected, even when instances were manually added
3. **Web UI defaulting to VLAN 1** which had no STP instance, showing empty data

### Why It Happened

The root cause was **architectural differences between Cisco IOS and NX-OS in SNMP BRIDGE-MIB implementation**:

**Cisco IOS Behavior:**
- Each VLAN requires a separate SNMP community string context (`public@100` for VLAN 100)
- BRIDGE-MIB OIDs without VLAN context return "No Such Instance" errors
- Each VLAN is treated as a completely separate bridge instance
- `dot1dBasePortIfIndex` mapping is VLAN-specific and different for each VLAN

**Cisco NX-OS Behavior:**
- BRIDGE-MIB queries work without VLAN context
- Single community string works for all VLANs
- Shared port mapping across VLANs

**LibreNMS Original Implementation:**
- The generic `BridgeMib` trait assumed VLAN context was optional
- Port-to-ifIndex mapping was queried once without context and shared across all VLANs
- Protocol specification check was performed without VLAN context
- This worked for NX-OS but completely failed for IOS

## Why It Needed Fixing

### Impact

1. **Monitoring Gap**: Critical network topology information was missing for IOS-based switches
2. **STP Loop Detection**: Unable to detect potential network loops on IOS devices
3. **Troubleshooting**: Network engineers couldn't diagnose STP issues through LibreNMS
4. **User Experience**: Web UI showed confusing empty data for VLAN 1 instead of actual STP instances

### Affected Devices

- **Cisco IOS**: All versions requiring VLAN context for BRIDGE-MIB (most IOS 15.x+)
- **Catalyst Series**: 1000, 2960, 3560, 3650, 3850 and similar access switches
- **Estimated Impact**: Thousands of LibreNMS installations monitoring IOS switches

## Why This Solution

### Design Decisions

#### 1. Cisco-Specific Override (Not Generic Fix)

**Decision:** Implement fixes in `LibreNMS/OS/Shared/Cisco.php` instead of modifying `LibreNMS/OS/Traits/BridgeMib.php`

**Reasoning:**
- **Compatibility**: NX-OS and other vendors work fine with the existing generic implementation
- **Separation of Concerns**: VLAN context is a Cisco-specific requirement, not a universal BRIDGE-MIB behavior
- **Maintainability**: Cisco-specific code belongs in the Cisco OS class
- **Risk Mitigation**: Changes don't affect other operating systems or vendors

#### 2. Complete Instance Discovery Rewrite

**Decision:** Override `discoverStpInstances()` entirely rather than calling parent method

**Original Approach (Failed):**
```php
$instance = parent::discoverStpInstances($vlan);
```

**Problem:** The parent method's protocol check was context-less and would fail before fetching any data.

**Solution:** Implement complete discovery logic with VLAN context:
```php
// Check protocol WITH VLAN context
$protocol = SnmpQuery::context("$vlan", 'vlan-')
    ->get('BRIDGE-MIB::dot1dStpProtocolSpecification.0')->value();

// Fetch all STP data WITH VLAN context
$stp = SnmpQuery::context("$vlan", 'vlan-')->enumStrings()->get([...]);
```

**Why:** 
- Every SNMP query must use VLAN context for IOS
- Cannot mix context and non-context queries
- Parent method cannot be modified without breaking other OS implementations

#### 3. Per-VLAN Port Mapping

**Decision:** Query `dot1dBasePortIfIndex` inside the VLAN loop with context

**Original Approach (Failed):**
```php
// Query once, use for all VLANs
$baseIfIndex = $this->getCacheByIndex('BRIDGE-MIB::dot1dBasePortIfIndex');
```

**Problem:** 
- IOS returns "No Such Instance" without VLAN context
- Even if it worked, bridge port numbers differ per VLAN on IOS

**Solution:**
```php
foreach ($stpInstances as $instance) {
    $basePortIdMap = SnmpQuery::context("$instance->vlan", 'vlan-')
        ->walk('BRIDGE-MIB::dot1dBasePortIfIndex')
        ->mapTable(function ($data, $bridgePort) { ... });
}
```

**Why:**
- Each VLAN has different bridge port numbering
- Bridge port 1 on VLAN 100 maps to different ifIndex than bridge port 1 on VLAN 102
- Context is mandatory for the query to succeed

#### 4. mapTable() for Index Preservation

**Decision:** Use `SnmpQuery::walk()->mapTable()` instead of `values()` or `table()[0]`

**Why:**
- **`values()`** returns: `['BRIDGE-MIB::dot1dBasePortIfIndex[1]' => 10101]` - OID as string key
- **`table()[0]`** returns: `['BRIDGE-MIB::dot1dBasePortIfIndex' => [1 => 10101]]` - complex nested structure
- **`mapTable()`** returns: `[1 => 998]` (bridge port → port_id) - clean numeric index

The `mapTable()` callback receives:
- `$data`: Array of OID values for this index
- `$bridgePort`: The numeric SNMP index (bridge port number)

This allows direct mapping from bridge port number to LibreNMS port_id.

#### 5. Web UI Default VLAN Fix

**Decision:** Select first available STP instance VLAN as default

**Original Code:**
```php
$active_vlan = Url::parseOptions('vlan', 1); // Always VLAN 1
```

**Problem:** 
- VLAN 1 often has no STP instance (disabled)
- Web UI showed empty page on first load

**Solution:**
```php
$firstVlan = $stpInstances->first()?->vlan ?? 1;
$active_vlan = Url::parseOptions('vlan', $firstVlan);
```

**Why:**
- Better UX: Shows actual data immediately
- Logical default: First discovered instance is most relevant
- Fallback preserved: Still defaults to 1 if no instances exist

#### 6. Protected Method Visibility

**Decision:** Change `BridgeMib::designatedPort()` from `private` to `protected`

**Why:**
- Cisco override needs to call this utility method
- Method contains reusable logic for parsing designated port format
- No security or encapsulation concerns (internal parsing logic)
- Alternative would be code duplication

## Implementation Details

### Code Changes Summary

#### File 1: `LibreNMS/OS/Traits/BridgeMib.php`
**Change:** Method visibility
```php
- private function designatedPort(string $dp): int
+ protected function designatedPort(string $dp): int
```
**Impact:** Minimal - only visibility change, no logic modified

#### File 2: `LibreNMS/OS/Shared/Cisco.php`
**Changes:** 
1. Complete `discoverStpInstances()` override (+80 lines)
2. New `discoverStpPorts()` method (+40 lines)

**Key Features:**
- VLAN context on every SNMP query
- Per-VLAN port-to-ifIndex mapping
- Integration with Cisco stpxSpanningTreeType
- Proper error handling (continue on failed VLANs)

#### File 3: `app/Http/Controllers/Device/Tabs/StpController.php`
**Change:** Smart default VLAN selection (+10 lines)

**Logic:**
1. Get first STP instance VLAN
2. Use as default if no user selection
3. Fallback to VLAN 1 if no instances

### Testing Validation

**Test Device:** Cisco Catalyst 1000 (10.0.0.201) running IOS 15.2(7)E4

**Before Fix:**
- Instances discovered: 0
- Ports discovered: 0
- Web UI: Empty VLAN 1 page

**After Fix:**
- Instances discovered: 2 (VLANs 100, 102)
- Ports discovered: 11 across both VLANs
- Web UI: VLAN 100 shown by default with complete data

**SNMP Query Validation:**
```bash
# Context-less (fails on IOS)
snmpget -v2c -c public 10.0.0.201 BRIDGE-MIB::dot1dStpProtocolSpecification.0
# Result: No Such Instance

# With context (works on IOS)
snmpget -v2c -c public@100 10.0.0.201 BRIDGE-MIB::dot1dStpProtocolSpecification.0
# Result: ieee8021d (3)
```

## Alternative Approaches Considered

### Option 1: Modify BridgeMib Trait to Always Use Context
**Rejected Because:**
- Would break NX-OS and other vendors
- VLAN context is not universally required
- Goes against trait's purpose as generic implementation

### Option 2: Auto-detect Context Requirement
**Rejected Because:**
- Adds complexity and extra SNMP queries
- Unreliable (how to detect?)
- Performance impact on every discovery
- Current OS-based approach is cleaner

### Option 3: Separate IOS and NX-OS Classes
**Rejected Because:**
- Code duplication (most logic is shared)
- Both are Cisco and share many other methods
- Current override approach is sufficient

## Backward Compatibility

### Impact on Existing Installations

**Cisco IOS Devices:**
- ✅ **Positive Impact**: STP discovery now works
- ✅ **No Breaking Changes**: Previously broken, now fixed
- ✅ **Automatic**: Next discovery run will populate data

**Cisco NX-OS Devices:**
- ✅ **No Impact**: Uses existing BridgeMib trait
- ✅ **Verified**: Discovery continues to work correctly

**Other Vendors:**
- ✅ **No Impact**: Use BridgeMib trait unchanged
- ✅ **No Regression**: Changes isolated to Cisco class

### Migration Path

**For Existing IOS Devices:**
1. Update LibreNMS code
2. Run discovery: `lnms device:discover <device> -m stp`
3. STP instances and ports will be automatically discovered
4. Web UI will show data immediately

**No manual intervention required.**

## Future Improvements

### Potential Enhancements

1. **Cache Optimization**: VLAN list could be cached to reduce queries
2. **Parallel Queries**: Multiple VLANs could be queried concurrently
3. **Error Reporting**: More detailed logging for troubleshooting
4. **MSTP Support**: Multi-instance STP handling improvements
5. **Performance Metrics**: Track discovery time per VLAN

### Known Limitations

1. **VLAN Discovery Dependency**: Requires VLAN module to run first
2. **SNMP v2c Required**: VLAN context syntax specific to v2c
3. **Community String Format**: Must support `community@vlan` syntax

## Conclusion

This fix addresses a fundamental architectural mismatch between LibreNMS's generic BRIDGE-MIB implementation and Cisco IOS's VLAN-context requirements. By implementing Cisco-specific overrides while preserving the generic trait for other vendors, we achieve:

1. ✅ **Functionality**: STP discovery works on IOS devices
2. ✅ **Compatibility**: No impact on other OS implementations  
3. ✅ **Maintainability**: Clear separation of vendor-specific logic
4. ✅ **User Experience**: Web UI shows relevant data by default

The solution follows LibreNMS architecture best practices and provides a template for handling similar vendor-specific SNMP requirements in the future.

## Checkpoint Information

**Git Branch:** `checkpoint_1` created before Web UI fix
- Contains: Working STP instance and port discovery
- Allows: Easy rollback if needed
- Tested: Verified on IOS device 10.0.0.201

---

**Document Version:** 1.0  
**Date:** January 2, 2026  
**Author:** LibreNMS Development Team  
**Related Issue:** Cisco IOS STP Discovery Failure
