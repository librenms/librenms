# Scheduled Maintenances

Scheduled Maintenances enable u user to put a device, a location or even a whole device group into maintenance mode. Maintenance is usually displayed by all screwdriver symbol left of the device name (at its detail page, at device group pages, etc.). A maintenance affects how alerting and/or alert transporting (aka user notifications) are handled.

## Maintenance Behaviors

A maintenance can have three different behaviors:

- "Skip alerts": Existing alerts stay as they are, and all alert rule checks are skipped. This means that new alerts are not created, and existing alerts will not recover. This is the default behavior.
- "Mute alert": Alerts are handled as usual (new ones will be raised, existing ones could recover etc.) but any alert transport like e-mail is suppressed. This is useful if you just want "silence" for a period of time for whatever reason but don't want to lose sight of what is happening to your devices.
- "Run alerts": This is basically just a cosmetic maintenance. You will see that a device is in maintenance, but this setting has no effect on alerts and alert transport.

## Managing Maintenances

You may access the page for Scheduled Maintenance by the main menu (Alert → Scheduled Maintenance). The table shows all maintenances: future ones, active ones, and lapsed ones. Beside adding a new maintenance, you can edit and delete existing ones here as well (column "Actions").

The form for adding and editing maintenances always has fields for Title Notes, Behavior and "Map To". With the last field you can set which devices, device groups, and locations will be affected. Locations are entities in a separate table and referenced by devices; you can choose these here.

Beside the general attributes, the form offers a slider labelled "Recurring". Use this to choose between two types of maintenances:

- Non-recurring maintenances start at a certain time and end at a later time; afterward they are lapsed and have no effect anymore unless you opt to change the date values again.
- Recurring maintenances simply have a start date and an end date between which maintenance periods may happen. You also define all weekdays and the start hour and end hour for the maintenance; this hour range will be applied to each selected day.

For example: You could put a group of devices into maintenance from Monday until Friday from 10 pm to 11pm, starting at 01.01.20xx until 31.01.20xx. Dates cannot be in the past, however,and End Hour/Date must be later or same as the Start Hour/Date.

If you want to end a certain maintenance early, simply delete it.

## Add Single Device Maintenance

To put a single device into maintenance, simply access its edit section and there the "Device Settings". Unless the device is already in maintenance, you will find there a green button labelled with "Maintenance Mode". Pressing it opens a dialogue with settings like notes, duration, and the aforementioned behavior (the "Skip alerts" option is selected by default).

Initially, you can only choose a duration of 23:30h at most, but you may change it later by editing the corresponding maintenance object. The maintenance's title will always be the device's display name (if set) or its hostname or IP address. It can be changed later as well.

If a device is already affected by at least one maintenance, the button will be orange, the label being "Device already in maintenance". You can't manage or remove a device maintenance here.

## Setting a Default Behavior for Web UI

You can either change the setting in the Web UI:

- Access "Global Settings"
- Go to the tab "Web UI"
- At section "Scheduled Maintenance", select a fitting value at the "Default Behavior" dropdown menu

Or can you add a line to your local `config.php` file, for example:

`$config['webui.scheduled_maintenance_default_behavior'] = 1;`

The following values can be used:

- 1 = Skip alerts
- 2 = Mute alerts
- 3 = Run alerts
