source: Developing/Dynamic-Config.md
path: blob/master/doc/

# Adding new config settings

Adding support for users to update a new config option via the WebUI
is now a lot easier for general options. This document shows you how
to add a new config option and even section to the WebUI.

Config settings are defined in `misc/config_definitions.json`

You should give a little thought to the name of your config setting.
For example: a good setting for snmp community, would be `snmp.community`.
The dot notation is path and when the config is hydrated, it is converted to a nested array.
If the user is overriding the option in config.php it would use the format `$config['snmp']['community']`

## Translation

The config definition system inherently supports translation. You must add the English names in the
`resoures/lang/en/settings.php` file (and other languages if you can).

To update the javascript translation files, run:

    ./lnms translation:generate

# Definition Format

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

All fields are optional. To show in the web ui, group and section are required, order is recommended.

* `type`: Defines the type, there are a few predefined types and custom
types can be defined and implemented in a vue.js component
* `default`: the default value for this setting
* `options`: the options for the select type. An object with {"value1": "display string", "value2": "display string"}
* `validate`: Defines more complex validation than the default simple type check.  Uses Laravel validation syntax.
* `group`: The web ui tab this is under
* `section`: A panel grouping settings in the web ui
* `order`: The order to display this setting within the section

## Predefined Types

* `string`: A string
* `integer`: A number
* `boolean`: A simple toggle switch
* `array`: A list of values that can be added, removed, and re-ordered.
* `select`: A dropdown box with predefined options. Requires the option field.
* `email`: Will validate the input is the correct format for an email
* `password`: Will mask the value of the input (but does not keep it fully private)

# Custom Types

You may set the type field to a custom type and define a Vue.js component to display it to the user.

The Vue.js component should be named as "SettingType" where type is the custom type entered with the first
letter capitalized. Vue.js components exist in the `resources/js/components` directory.

Here is an empty component named SettingType (make sure to rename it).  It pulls in BaseSetting mixin for
basic setting code to reuse.  You should review the BaseSetting component.

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

Using Vue.js is beyond the scope of this document. Documentation can be found at [vuejs.org](https://vuejs.org/v2/guide/).
