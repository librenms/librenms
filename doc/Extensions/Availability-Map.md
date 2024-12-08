# Availability Map

LibreNMS has the following page to show an availability map:

 - Overview -> Maps -> Availability

This map will show all devices on a single page, with each device
having either a box or a coloured square representing its status.

## Widget
There is an availability map widget that can be added to a dashboard
to give a quick overview of the status of all devices on the network.

## Settings
```bash
# Set the compact view mode for the availability map
lnms config:set webui.availability_map_compact false

# Size of the box for each device in the availability map (not compact)
lnms config:set webui.availability_map_box_size 165

# Sort by status instead of hostname
lnms config:set webui.availability_map_sort_status false

# Show the device group drop-down on the availabiltiy map page
lnms config:set webui.availability_map_use_device_groups true
```
