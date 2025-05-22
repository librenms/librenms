# Tags

LibreNMS supports tagging devices via the API and CLI. These are displayed in the GUI on the device overview, but there is currently no support for creating via the GUI.

## Why

Tags are intended for administrators to be able to syncronise LibreNMS with external data sources, such as a CMDB.

On their own, tags are meaningless - they are just strings that are attached to the device in the database. To be truly useful, the need to be applied consistently and ideally reference something outside of LibreNMS.

Some ideas for useful tags that you might want to set:
* system_owner
* customer_id
* cost_centre
* performance_tier
* documentation_url
* jira_component
* resource_pool
* crowdstrike_policy
* business_hours
* uptime_sla
* azure_region
* workload
* environment
* redundancy

When displayed in the GUI, underscores are converted to spaces and words are capitalised. Before storing in the database they are converted to lowercase.

# Cautions

## Do not use tags to store sensitive information

While tags can be hidden this is a cosmetic setting and does not make them inaccessible. Tag values can be found elsewhere in the UI, API and CLI regardless of visiblity.

## Tags must come from trusted sources only 

If used for forming groups, a change in a tag can and will make a device accessible to different users.

If a user is able to change a tag, then they can change the devices they have access to in LibreNMS

# How

## API

The following API routes are used for working with tags:

### GET tags/{key}

Gets all devices with a tag named $key defined.

### GET tags/{key}/{value}

Gets all devices with a tag named $key defined and set to $value.

### GET {hostname}/tags

Gets all tags assigned to a device.

### GET {hostname}/tags/{key}

Gets the value of a tag named $key assigned to a device.

### POST {hostname}/tags

Sets one or more tags on a device, with the following scheme:

```
[
    {"key": "value"},
    {"key2": "value2"}
]
```

### POST tags/define

Creates a new tag definition (see advanced usage below)

```
[
    {
        "key": "key",
        "type": "email",
        "visible": false
    }
]
```

### DELETE {hostname}/tags/{key}

Deletes a tag named $key assigned to a device

## CLI

There are two CLI functions for working with tags.

`$ lnms device:tags [get/set/delete] [tag[=value]] [tag[=value]]`

`$ lnms device:define-tag [tag]`

Both CLI functions support `--help` for more detailed usage information.

As far as possible, the CLI commands have been written to be automation friendly - they can output JSON, and will try and execute whatever is asked of them without avoidable errors. Invalid input will be ignored, and only successes will be reported on.

Note that tags do not have to be defined before you create them as undefined tags are defined automatically - but you may want to. See the 'Advanced Usage' section below for reasons why you might want to explicitly define a tag key before adding it to a device.

# Where can I use them
Aside from showing up on the device overview, they can be used in a couple of other places.

With both of the below examples, these would be most useful when you have a tool such as a CRM where the customer data could be pulled from automatically, and syncronised to LibreNMS' tags.

## Alerting

Tags can be used as part of a rule - if you were to set a tag called `customer_id`, you could create an alert rule that only fires when a specified `customer_id` tag is set. You could use this to create bespoke alerting thresholds to monitor SLA's.

## Groups

Tags can be used to create dynamic groups - if you were to set a tag called "customer_id", you could create a dynamic group with `devicetagkey.key equals customer` and `devicetag.value equals 28` to create a dynamic group of that customer's systems.

## Informational

Tags can be useful on their own just for information. Perhaps your backup system could write the date and time of the last backup - or your team could record when the device last had a backup verified.

# Advanced Usage

## Visibility

The visibility option hides tags from the UI. This is useful when you have a tag that has a value that is not useful to a human, such as a GUID used in another system.

As an example, imagine you have an DCIM that uses GUID's for its referring to objects in its own database. You could store the identifer in a hidden tag, `dcim_guid`, allowing you to simplify any API integrations between the two systems.

## Type

The type field tries to enforce a specific format for tags. As tags are created via the API and CLI.

This allows formatting to be applied to visible links when displayed on the overview. Currently, `email` and `url` will be turned into links.

# Future enhancement

## GUI
Currently there is no GUI - patches are welcome.

In addition to a GUI, support for searching/filtering in the device list would be useful.

## Interfaces
It's likely there are use cases for ports to be support tagging for integration with tools such as Netbox - patches are welcome. 