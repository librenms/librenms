### `get_inventory`

Retrieve the inventory of a device. A call without parameters returns
only a part of the inventory, because many devices nest their
components. For example, the chassis holds the ports, one port is an
SFP cage, and the cage holds the SFP itself. This API call therefore
does a recursive lookup. The first call returns the root entry. The
response holds entPhysicalIndex. You then call for
entPhysicalContainedIn, and this call returns the next layer of
results. To get all items at one time, read
[get_inventory_for_device](#get_inventory_for_device).

Route: `/api/v0/inventory/:hostname`

- hostname can be either the device hostname or the device id

Input:

- entPhysicalClass: it limits the class of the inventory. For example,
  the value `chassis` returns only the items with the chassis label.
- entPhysicalContainedIn: it returns the items inside a previous
  component. For example, the entPhysicalIndex of the chassis returns
  all items with the chassis as their parent.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/inventory/localhost?entPhysicalContainedIn=65536
```

Output:

```json
{
    "status": "ok",
    "message": "",
    "count": 1,
    "inventory": [
        {
            "entPhysical_id": "2",
            "device_id": "32",
            "entPhysicalIndex": "262145",
            "entPhysicalDescr": "Linux 3.3.5 ehci_hcd RB400 EHCI",
            "entPhysicalClass": "unknown",
            "entPhysicalName": "1:1",
            "entPhysicalHardwareRev": "",
            "entPhysicalFirmwareRev": "",
            "entPhysicalSoftwareRev": "",
            "entPhysicalAlias": "",
            "entPhysicalAssetID": "",
            "entPhysicalIsFRU": "false",
            "entPhysicalModelName": "0x0002",
            "entPhysicalVendorType": "zeroDotZero",
            "entPhysicalSerialNum": "rb400_usb",
            "entPhysicalContainedIn": "65536",
            "entPhysicalParentRelPos": "-1",
            "entPhysicalMfgName": "0x1d6b",
            "ifIndex": "0"
        }
    ]
}
```

### `get_inventory_for_device`

Retrieve the flat inventory of a device. This call returns all
inventory items of the device at any structure level. It is therefore
more useful for a device with nested components.

Route: `/api/v0/inventory/:hostname/all`

- hostname can be either the device hostname or the device id

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/inventory/localhost/all?entPhysicalContainedIn=65536
```

Output:

```json
{
    "status": "ok",
    "message": "",
    "count": 1,
    "inventory": [
        {
            "entPhysical_id": "2",
            "device_id": "32",
            "entPhysicalIndex": "262145",
            "entPhysicalDescr": "Linux 3.3.5 ehci_hcd RB400 EHCI",
            "entPhysicalClass": "unknown",
            "entPhysicalName": "1:1",
            "entPhysicalHardwareRev": "",
            "entPhysicalFirmwareRev": "",
            "entPhysicalSoftwareRev": "",
            "entPhysicalAlias": "",
            "entPhysicalAssetID": "",
            "entPhysicalIsFRU": "false",
            "entPhysicalModelName": "0x0002",
            "entPhysicalVendorType": "zeroDotZero",
            "entPhysicalSerialNum": "rb400_usb",
            "entPhysicalContainedIn": "65536",
            "entPhysicalParentRelPos": "-1",
            "entPhysicalMfgName": "0x1d6b",
            "ifIndex": "0"
        }
    ]
}
```
