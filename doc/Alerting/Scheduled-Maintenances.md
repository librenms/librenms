# Scheduled Maintenances

Scheduled maintenance puts a device, a location, or a whole device group
into maintenance mode. A screwdriver symbol at the left of the device
name shows the maintenance state. This symbol appears on the detail
page of the device and on the device group pages. A maintenance changes
the behaviour of the alerting and of the alert transports, that is the
user notifications.

## Maintenance Behaviors

A maintenance has one of three behaviours:

- Skip alerts: the existing alerts do not change, and LibreNMS skips
all alert rule checks. LibreNMS creates no new alert, and an existing
alert does not recover. This behaviour is the default.
- Mute alert: LibreNMS handles the alerts in the normal way. It raises
new alerts, and existing alerts can recover. It sends no alert through
a transport such as mail. This behaviour gives silence for a period of
time, and you still see the state of your devices.
- Run alerts: this behaviour is only cosmetic. You see the maintenance
state of the device, but LibreNMS still sends the alerts.

## Managing Maintenances

The Scheduled Maintenance page is in the main menu, at
Alert → Scheduled Maintenance. The table shows all maintenances: the
future ones, the active ones, and the expired ones. On this page you
can add a new maintenance. You can also edit and remove an existing
maintenance in the "Actions" column.

The form for a maintenance always has the fields Title, Notes,
Behavior, and "Map To". The "Map To" field selects the devices, the
device groups, and the locations of the maintenance. A location is an
entity in a separate table, and a device points to it. You can select
these locations here.

The form also has a slider with the label "Recurring". This slider
selects one of two maintenance types:

- A non-recurring maintenance starts at one time and ends at a later
time. It then expires and has no more effect. You can change the date
values again.
- A recurring maintenance has a start date and an end date. The
maintenance periods occur between these two dates. You also define the
days, the start hour, and the end hour. The hour range applies to each
selected day.

For example, a group of devices can be in maintenance from Monday to
Friday, from 10 pm to 11 pm, from 01.01.20xx to 31.01.20xx. A date must
not be in the past. The end hour and the end date must be later than
the start hour and the start date, or the same.

To end a maintenance early, remove it.

## Add Single Device Maintenance

To put one device into maintenance, open its edit section and then
"Device Settings". A green button with the label "Maintenance Mode" is
there, unless the device is already in maintenance. This button opens a
dialogue with the notes, the duration, and the behaviour. The default
behaviour is "Skip alerts".

At the start, the maximum duration is 23:30 hours. You can change the
duration later in the maintenance object. The title of the maintenance
is the display name of the device. Without a display name, the title is
the hostname or the IP address. You can also change the title later.

If at least one maintenance already applies to a device, the button is
orange with the label "Device already in maintenance". You cannot
manage or remove a device maintenance here.

## Setting a Default Behavior for scheduled maintenance

!!! setting "alerting/scheduled-maintenance"
    ```bash
    lnms config:set alert.scheduled_maintenance_default_behavior 1
    ```

These values are valid:

- 1 = Skip alerts
- 2 = Mute alerts
- 3 = Run alerts
