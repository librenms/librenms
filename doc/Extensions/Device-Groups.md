# Grouping Devices

LibreNMS supports grouping your devices together in much the same way
as you can configure alerts. This document will hopefully help you get
started.

## Dynamic Groups

### Rule Editor

Just like our alerting system, dynamic groups are based on the MySQL
structure your data is in and uses QueryBuilder to generate SQL
queries to build your groups.

You can browse around inside MySQL using `show tables` to view all
of the tables within LibreNMS and then run `desc <tablename>` to
be able to see the table structure. Both of these then form the
basic format for the QueryBuilder interface such as __tablename.columnname__.

To see the data within the table, you can then run
`select * from <tablename> limit 5;`. This way, you can get an idea
of what data will be returned for your dynamic group.

As a working example and a common question, let's assume you want to
group devices by hostname. If your hostname format is
dcX.[devicetype].example.com.

If you want to group them by the device type of `rtr`, you would add
a rule for routers of `devices.hostname` `endswith` `rtr.example.com`.
This would match dcX.`rtr.example.com`

If you want to group them by DC, you could use the rule
`devices.hostname` regex `dc1\..*\.example\.com` (Don't forget to
escape periods in the regex). This would match `dc1.rtr.example.com`.

## Static Groups

You can create static groups (and convert dynamic groups to static) to
put specific devices in a group. Just select static as the type and
select the devices you want in the group.

![Device Groups](../img/device_groups.png)

You can now select this group from the Devices -> All Devices link in
the navigation at the top. You can also use map your device groups to
an alert rule in the section `Match devices, groups and locations list`
against any alert rule.

## Device Group Permissions (Beta)

Device groups can be used to grant users access to specific devices based on group membership. This feature is marked as beta and requires specific configuration to function properly.

### Configuration

To enable device group permissions, you must set the following configuration option:

!!! setting "authorization"
    ```bash
    lnms config:set permission.device_group.allow_dynamic true
    ```

By default, this setting is `false`, which means:
- Device group permissions are **disabled** and in **read-only mode**
- Users with assigned device group permissions will see those assignments in the UI, but they will **not grant any actual access**
- Administrators cannot add or remove device group permission assignments
- The permissions query will **exclude all dynamic device groups** from access calculations

### Important Notes

⚠️ **Warning**: If `permission.device_group.allow_dynamic` is `false` (the default), device group permissions appear to be "invisible" or "disappear" from the user's effective permissions, even though the database records still exist. This is a common source of confusion.

### How It Works

When enabled (`permission.device_group.allow_dynamic = true`):
- Users can be granted access to devices via device group assignments
- Both **static** and **dynamic** device groups can be used for permissions
- Admins can add/remove device group assignments on the user edit page
- Permissions are cached for 24 hours for performance

When disabled (default: `permission.device_group.allow_dynamic = false`):
- The device group permissions section on the user edit page becomes **read-only**
- A warning banner indicates the feature is disabled
- Delete buttons are replaced with lock icons
- The "Add" form is hidden
- All modification attempts (add/delete) are blocked at the backend

### Assigning Device Group Permissions

1. Navigate to Settings → Manage Users → Edit User
2. Scroll to the "Device access via Device Group (beta)" section
3. Select a device group from the dropdown
4. Click "Add" to grant access
5. To revoke access, click the trash icon next to the group name

### Static vs Dynamic Groups

- **Static Groups**: Manually maintained list of devices
- **Dynamic Groups**: Automatically updated based on rules (e.g., hostname patterns, location, etc.)

When `permission.device_group.allow_dynamic = false`:
- Only **static** device groups are considered for permissions
- Dynamic group assignments exist in the database but are excluded from permission checks

### Troubleshooting

**Problem**: Device group permissions "disappear" or users lose access intermittently

**Cause**: The `permission.device_group.allow_dynamic` setting is `false` (or was changed from `true` to `false`)

**Solution**: Enable dynamic groups in your configuration:

```bash
lnms config:set permission.device_group.allow_dynamic true
```

**Alternative**: If you don't want to use dynamic groups, ensure all device groups assigned to users are **static** type groups.

### Database Tables

Device group permissions are stored in the following tables:
- `devices_group_perms`: Links users to device groups
- `device_group_device`: Links devices to device groups
- `device_groups`: Stores device group definitions (including `type`: static or dynamic)

### See Also

- [Authentication Options](Authentication.md) - For user role management
- [API: Device Groups](../API/DeviceGroups.md) - For programmatic access to device groups