Table of Content:

- [About](#about)
- [Database Structure](#database)
    - [Reserved Fields](#reserved)
- [Using Components](#using)
    - [Getting Current Components](#get)
        - [Options](#options)
            - [Filtering Results](#filter)
            - [Sorting Results](#sort)
    - [Creating Components](#create)
    - [Deleting Components](#delete)
    - [Editing Components](#update)
        - [Edit the Array](#update-edit)
        - [Writing the Components](#update-write)
    - [API](#api)
    - [Alerting](#alert)
- [Example Code](#example)


# <a name="about">About</a>

The Component extension provides a generic database storage mechanism for discovery and poller modules.
The Driver behind this extension was to provide the features of ports, in a generic manner to discovery/poller modules.

It provides a status (Normal or Alert), the ability to Disable (do not poll), or Ignore (do not Alert).

# <a name="database">Database Structure</a>

The database structure contains the component table:

```SQL
mysql> select * from component limit 1;
+----+-----------+------+------------+--------+----------+--------+-------+
| id | device_id | type | label      | status | disabled | ignore | error |
+----+-----------+------+------------+--------+----------+--------+-------+
|  9 |         1 | TEST | TEST LABEL |      0 |        1 |      1 |       |
+----+-----------+------+------------+--------+----------+--------+-------+
1 row in set (0.00 sec)
```

These fields are described below:

- id - ID for each component, unique index
- device_id - device_id from the devices table
- type - name from the component_type table
- label - Display label for the component
- status - The status of the component, retrieved from the device
- disabled - Should this component be polled?
- ignore - Should this component be alerted on
- error - Error message if in Alert state

The component_prefs table holds custom data in an Attribute/Value format:

```SQL
mysql> select * from component_prefs limit 1;
+----+-----------+-----------+-----------+
| id | component | attribute | value     |
+----+-----------+-----------+-----------+
|  4 |         9 | TEST_ATTR | TEST_ATTR |
+----+-----------+-----------+-----------+
2 rows in set (0.00 sec)
```

## <a name="reserved">Reserved Fields</a>

When this data from both the component and component_prefs tables is returned in one single consolidated array, there is the potential for someone to attempt to set an attribute (in the component_prefs) table that is used in the components table.
Because of this all fields of the component table are reserved, they cannot be used as custom attributes, if you update these the module will attempt to write them to the component table, not the component_prefs table.


# <a name="using">Using Components</a>

To use components in you application, first you need to include the code.

From a Discovery/Poller module:

```php
require_once 'includes/component.php';
```

From the html tree:

```php
require_once "../includes/component.php";
```

Once the code is loaded, create an instance of the component class:

```php
$COMPONENT = new component();
```

## <a name="get">Retrieving Components</a>

Now you can retrieve an array of the available components:

```php
$ARRAY = $COMPONENT->getComponents($DEVICE_ID,$OPTIONS);
```

`getComponents` takes 2 arguments:

- DEVICE_ID or null for all devices.
- OPTIONS - an array of various options.

`getComponents` will return an array containing components in the following format:

    Array
    (
        [X] => Array
        (
            [Y1] => Array
            (
                [device_id] => 1
                [TEST_ATTR] => TEST_ATTR
                [type] => TEST
                [label] => TEST LABEL
                [status] => 0
                [ignore] => 1
                [disabled] => 1
                [error] => 
            ),
            [Y2] => Array
            (
                [device_id] => 1
                [TEST_ATTR] => TEST_ATTR
                [type] => TESTING
                [label] => TEST LABEL
                [status] => 0
                [ignore] => 1
                [disabled] => 0
                [error] => 
            ),
        )
    )
Where X is the Device ID and Y1/Y2 is the Component ID. In the example above, 'TEST_ATTR' is a custom field, the rest are reserved fields.

### <a name="options">Options</a>

Options can be supplied to `getComponents` to influence which and how components are returned.

#### <a name="filter">Filtering</a>

You can filter on any of the [reserved](#reserved) fields. Filters are created in the following format:

```php
$OPTIONS['filter'][FIELD] = array ('OPERATOR', 'CRITERIA');
```

Where:

- FIELD - The [reserved](#reserved) field to filter on
- OPERATOR - 'LIKE' or '=', are we checking if the FIELD equals or contains the CRITERIA.
- CRITERIA - The criteria to search on


There are 2 filtering shortcuts:
 
$DEVICE_ID is a synonym for:

```php
$OPTIONS['filter']['device_id'] = array ('=', $DEVICE_ID);
```

`$OPTIONS['type'] = $TYPE` is a synonym for:

```php
$OPTIONS['filter']['type'] = array ('=', $TYPE);
```

#### <a name="sort">Sorting</a>

You can sort the records that are returned by specifying the following option:

```php
$OPTIONS['sort'][FIELD] = 'DIRECTION';
```

Where Direction is one of:

- ASC - Ascending, from Low to High
- DESC - Descending, from High to Low


## <a name="creating">Creating Components</a>

To create a new component, run the createComponent function.

```php
$ARRAY = $COMPONENT->createComponent($DEVICE_ID,$TYPE);
```
`createComponent` takes 2 arguments:

- DEVICE_ID - The ID of the device to attach the component to.
- TYPE - The unique type for your module.

This will return a new, empty array with a component ID and Type set, all other fields will be set to defaults.

    Array
    (
        [1] => Array
        (
            [type] => TESTING
            [label] => 
            [status] => 1
            [ignore] => 0
            [disabled] => 0
            [error] => 
        )
    )


## <a name="delete">Deleting Components</a>

When a component is no longer needed, it can be deleted.

```php
$COMPONENT->deleteComponent($COMPONENT_ID)
```

This will return True on success or False on failure.


## <a name="update">Editing Components</a>

To edit a component, the procedure is:

1. [Get the Current Components](#get)
2. [Edit the array](#update-edit)
3. [Write the components](#update-write)

### <a name="update-edit">Edit the Array</a>

Once you have a component array from getComponents the first thing to do is extract the components for only the single device you are editing. This is required because the setComponentPrefs function only saves a single device at a time.

```php
$ARRAY = $COMPONENT->getComponents($DEVICE_ID,$OPTIONS);
$ARRAY = $ARRAY[$DEVICE_ID];
```

Then simply edit this array to suit your needs.
If you need to add a new Attribute/Value pair you can:

```php
$ARRAY[COMPONENT_ID]['New Attribute'] = 'Value';
```

If you need to delete a previously set Attribute/Value pair you can:

```php
unset($ARRAY[COMPONENT_ID]['New Attribute']);
```

If you need to edit a previously set Attribute/Value pair you can:

```php
$ARRAY[COMPONENT_ID]['Existing Attribute'] = "New Value";
```


### <a name="update-write">Write the components</a>

To write component changes back to the database simply:

```php
$COMPONENT->setComponentPrefs($DEVICE_ID,$ARRAY)
```

When writing the component array there are several caveats to be aware of, these are:

- $ARRAY must be in the format of a single device ID - `$ARRAY[$COMPONENT_ID][Attribute] = 'Value';` NOT in the multi device format returned by getComponents - $ARRAY[$DEVICE_ID][$COMPONENT_ID][Attribute] = 'Value';`
- You cannot edit the Component ID or the Device ID
- [reserved](#reserved) fields can not be removed
- if a change is found an entry will be written to the eventlog.


## <a name="api">API</a>

Component details are available via the API.
Please see the [API-Docs](http://docs.librenms.org/API/API-Docs/#api-route-25) for details.

## <a name="alert">Alerting</a>

It is intended that discovery/poller modules will detect the status of a component during the polling cycle.
If you are creating a poller module which can detect a fault condition simply set STATUS to 0 and ERROR to a message that indicates the problem.


To actually raise an alert, the user will need to create an alert rule. To assist with this several Alerting Macro's have been created:

- ```%macro.component_alert``` - A component that is not disabled or ignored and in alert state.
- ```%macro.component_normal``` - A component that is not disabled or ignored and NOT in alert state.

To raise alerts for components, the following rules could be created:

- ```%macros.component_alert = "1"``` - To alert on all components
- ```%macros.component_alert = "1" && %component.type = "<Type of Component>"``` - To alert on all components of a particular type.

If there is a particular component you would like excluded from alerting, simply set the ignore field to 1.

The data that is written to each alert when it is raised is in the following format:

`COMPONENT_TYPE - LABEL - ERROR`

# <a name="examples">Example Code</a>

To see an example of how the component module can used, please see the following modules:

- Cisco CBQoS
    - includes/discovery/cisco-cbqos.inc.php
    - includes/polling/cisco-cbqos.inc.php
    - html/includes/graphs/device/cbqos_traffic.inc.php
- Cisco OTV
    - includes/discovery/cisco-otv.inc.php
    - includes/polling/cisco-otv.inc.php
    - html/includes/graphs/device/cisco-otv-mac.inc.php
    - html/pages/routing/cisco-otv.inc.php

