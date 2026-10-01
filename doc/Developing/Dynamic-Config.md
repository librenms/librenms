# Adding new config settings

A general configuration option is easy to add to the web interface.
This document describes how to add a new configuration option and a new
section to the web interface.

Config settings are defined in `resources/definitions/config_definitions.json`

Choose the name of your configuration setting with care. A good name
for the SNMP transports is `snmp.transports`. The dot notation is a path.
LibreNMS converts this path to a nested array. In `config.php`, the
user overrides the option with the format `$config['snmp']['transports']`.

## Translation

The configuration definition system supports translation. Add the
English names to the `resources/lang/en/settings.php` file. Add the
other languages where you can.

## Definition Format

For snmp.transports, this is the definition:

```json
"snmp.transports": {
    "group": "poller",
    "section": "snmp",
    "order": 0,
    "type": "array",
    "default": [
        "udp",
        "udp6",
        "tcp",
        "tcp6"
    ]
}
```

## Fields

All fields are optional. The web interface needs `group` and `section`.
We also recommend `order`.

* `type`: the type of the setting. Some types are predefined. You can
  also define your own type with a Blade template
* `default`: the default value for this setting
* `options`: the options for the select type. An object with {"value1": "display string", "value2": "display string"}
* `validate`: a more complex validation than the default type check. It
  uses the Laravel validation syntax.
* `group`: the tab of the web interface for this setting
* `section`: a panel of settings in the web interface
* `order`: the position of this setting in the section

## Predefined Types

* `string`: A string
* `integer`: A number
* `boolean`: A simple toggle switch
* `array`: a list of values. You can add, remove, and reorder them.
* `array-dynamic`: a list of values chosen from an ajax select. `options.target` is the select route, for example `secret`.
* `select`: a dropdown box with predefined options. It needs the option field.
* `email`: it validates the email format of the input
* `password`: it masks the value of the input. The value is not fully private

## Custom Types

You can set the type field to your own type. Then add a Blade template
for the display.

The settings page renders each setting with
`resources/views/settings/partials/setting.blade.php`. Each type has a
template in the `resources/views/settings/types` directory. Add your
template there and add it to the `$templates` list in the setting
partial, with the types it displays.

```php
'my-type' => ['my-type'],
```

The templates use [Alpine.js](https://alpinejs.dev). These variables and
functions are available in a template:

* `setting`: the setting definition (`name`, `type`, `options`,
  `required`, `pattern`, `overridden`)
* `value`: the current value of the setting
* `changeValue(value)`: set and save a new value
* `inputId`: the id to use for the input, so the label works

The template below is a text input:

```blade
<input type="text"
       class="form-control"
       :id="inputId"
       :value="value"
       @input="changeValue($event.target.value)"
       :disabled="setting.overridden"
>
```

For complex types, add an `Alpine.data()` component to
`resources/js/components/alpine/settings.js` and register it in
`resources/js/app.js`. The existing array and snmp3auth types are good
examples.
