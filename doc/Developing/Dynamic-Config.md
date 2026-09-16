# Adding new config settings

A general configuration option is easy to add to the web interface.
This document describes how to add a new configuration option and a new
section to the web interface.

Config settings are defined in `resources/definitions/config_definitions.json`

Choose the name of your configuration setting with care. A good name
for the SNMP community is `snmp.community`. The dot notation is a path.
LibreNMS converts this path to a nested array. In `config.php`, the
user overrides the option with the format `$config['snmp']['community']`.

## Translation

The configuration definition system supports translation. Add the
English names to the `resources/lang/en/settings.php` file. Add the
other languages where you can.

To update the javascript translation files, run:

    ./lnms translation:generate

## Definition Format

For snmp.community, this is the definition:

```json
"snmp.community": {
    "group": "poller",
    "section": "snmp",
    "order": 2,
    "type": "array",
    "default": [
        "public"
    ]
}
```

## Fields

All fields are optional. The web interface needs `group` and `section`.
We also recommend `order`.

* `type`: the type of the setting. Some types are predefined. You can
  also define your own type in a Vue.js component
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
* `select`: a dropdown box with predefined options. It needs the option field.
* `email`: it validates the email format of the input
* `password`: it masks the value of the input. The value is not fully private

## Custom Types

You can set the type field to your own type. Then define a Vue.js
component for the display.

Give the Vue.js component the name "SettingType". Here, "Type" is your
own type with a capital first letter. The Vue.js components are in the
`resources/js/components` directory.

The empty component below has the name SettingType. Rename it. It uses
the BaseSetting mixin for the basic setting code. Read the BaseSetting
component.

```vue
<template>
    <div></div>
</template>

<script>
    import BaseSetting from "./BaseSetting";

    export default {
        name: "SettingType",
        mixins: [BaseSetting]
    }
</script>

<style scoped>

</style>
```

This document does not describe Vue.js. The documentation is at
[vuejs.org](https://vuejs.org/v2/guide/).
