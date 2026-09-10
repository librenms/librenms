# About

The Component extension gives generic database storage to the discovery
modules and the poller modules. It brings the features of ports to
these modules in a generic form.

It gives a status in the Nagios convention. It also gives a Disable
option, which stops the poll, and an Ignore option, which stops the
alert.

## Database Structure

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

- `id` - ID for each component, unique index
- `device_id` - device_id from the devices table
- `type` - name from the component_type table
- `label` - Display label for the component
- `status` - The status of the component, retrieved from the device
- `disabled` - it stops the poll of this component
- `ignore` - it stops the alerts of this component
- `error` - Error message if in Alert state

The component_prefs table holds custom data in an Attribute/Value format:

```sql
mysql> select * from component_prefs limit 1;
+----+-----------+-----------+-----------+
| id | component | attribute | value     |
+----+-----------+-----------+-----------+
|  4 |         9 | TEST_ATTR | TEST_ATTR |
+----+-----------+-----------+-----------+
2 rows in set (0.00 sec)
```

### <a name="reserved">Reserved Fields</a>

The data of the `component` table and the `component_prefs` table comes
back in one array. A user can therefore try to set an attribute in
`component_prefs` with the name of a `component` field. All fields of
the `component` table are therefore reserved. You cannot use them as
custom attributes. An update of such a field goes to the `component`
table, not to the `component_prefs` table.

## Using Components

Create an instance of the component class:

```php
$COMPONENT = new LibreNMS\Component();
```

### <a name="get">Retrieving Components</a>

Now you can retrieve an array of the available components:

```php
$ARRAY = $COMPONENT->getComponents($DEVICE_ID, $OPTIONS);
```

`getComponents` takes 2 arguments:

- `DEVICE_ID` or null for all devices.
- `OPTIONS` - an array of various options.

`getComponents` returns an array of the components in this format:

```php
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
```

Here, X is the device ID. Y1 and Y2 are the component IDs. In the
example above, `TEST_ATTR` is a custom field. The other fields are
reserved fields.

### Options

The options of `getComponents` control the selection and the format of
the components.

#### Filtering

You can filter on each [reserved](#reserved) field. A filter has this
format:

```php
$options['filter']['FIELD'] = array ('OPERATOR', 'CRITERIA');
```

Where:

- `FIELD` - The [reserved](#reserved) field to filter on
- `OPERATOR` - 'LIKE' or '='. '=' tests for an equal FIELD. 'LIKE'
  tests for a FIELD that holds the CRITERIA.
- `CRITERIA` - The criteria to search on

There are 2 filtering shortcuts:

`$DEVICE_ID` is a synonym for:

```php
$OPTIONS['filter']['device_id'] = array ('=', $DEVICE_ID);
```

`$OPTIONS['type'] = $TYPE` is a synonym for:

```php
$OPTIONS['filter']['type'] = array ('=', $TYPE);
```

#### Sorting

This option sorts the returned records:

```php
$OPTIONS['sort'][FIELD] = 'DIRECTION';
```

Where Direction is one of:

- `ASC` - Ascending, from Low to High
- `DESC` - Descending, from High to Low

## Creating Components

To create a new component, run the `createComponent` function.

```php
$ARRAY = $COMPONENT->createComponent($DEVICE_ID, $TYPE);
```

`createComponent` takes 2 arguments:

- `DEVICE_ID` - The ID of the device to attach the component to.
- `TYPE` - The unique type for your module.

It returns a new empty array with a component ID and a type. All other
fields have their default values.

```php
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
```

## Deleting Components

When a component is no longer needed, it can be deleted.

```php
$COMPONENT->deleteComponent($COMPONENT_ID)
```

It returns `True` at a success and `False` at a failure.

## Editing Components

To edit a component, the procedure is:

1. [Get the Current Components](#get)
1. [Edit the array](#edit-the-array)
1. [Write the components](#update-write)

### Edit the Array

First get a component array from `getComponents`. Then extract the
components of the device of your edit. This step is necessary, because
the `setComponentPrefs` function saves only one device at a time.

```php
$ARRAY = $COMPONENT->getComponents($DEVICE_ID, $OPTIONS);
$ARRAY = $ARRAY[$DEVICE_ID];
```

Then edit this array for your own needs.
To add a new attribute and value pair:

```php
$ARRAY[COMPONENT_ID]['New Attribute'] = "Value";
```

To remove an attribute and value pair:

```php
unset($ARRAY[COMPONENT_ID]['New Attribute']);
```

To edit an attribute and value pair:

```php
$ARRAY[COMPONENT_ID]['Existing Attribute'] = "New Value";
```

### <a name="update-write">Write the components </a> 

To write the component changes back to the database:

```php
$COMPONENT->setComponentPrefs($DEVICE_ID, $ARRAY)
```

The write of a component array has these limits:

- `$ARRAY` must be in the format of a single device ID -
  `$ARRAY[$COMPONENT_ID][Attribute] = 'Value';` NOT in the multi
  device format returned by `getComponents` -
  `$ARRAY[$DEVICE_ID][$COMPONENT_ID][Attribute] = 'Value';`
- You cannot edit the component ID or the device ID
- You cannot remove a [reserved](#reserved) field
- A change writes an entry to the eventlog

## API

Component details are available via the API.
For the details, read the [API docs](../API/Devices.md#get_components).

## Alerting

A discovery module or a poller module detects the status of a component
in the polling cycle. LibreNMS logs the status with the Nagios status
codes:

```
0 = Ok,
1 = Warning,
2 = Critical
```

In a poller module that detects a fault, set STATUS to a value other
than 0. Set ERROR to a message with the description of the problem.

The user creates an alert rule for the alert. These alerting macros
help:

- `%macro.component_normal` - A component that is not disabled or
  ignored and in a Normal state.
- `%macro.component_warning` - a component that is not disabled and not
  ignored, and is in a warning state.
- `%macro.component_critical` - a component that is not disabled and
  not ignored, and is in a critical state.

These rules raise alerts for components:

- `%macros.component_critical = "1"` - To alert on all Critical
  components
- `%macros.component_critical = "1" && %component.type = "<Type of
  Component>"` - To alert on all Critical components of a particular
  type.

To exclude a component from the alerting, set its ignore field to 1.

Each raised alert holds the data in this format:

`COMPONENT_TYPE - LABEL - ERROR`

# Example Code

These modules give an example of the component module:

- Cisco OTV
  - `includes/discovery/cisco-otv.inc.php`
  - `includes/polling/cisco-otv.inc.php`
  - `html/includes/graphs/device/cisco-otv-mac.inc.php`
  - `html/pages/routing/cisco-otv.inc.php`
