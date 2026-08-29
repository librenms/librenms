# Developing for the Plugin System

With plugins you can extend LibreNMS with special functions that are
specific to your setup or are not relevant or interesting for all community members.

You can intervene at defined places in the behaviour of
the website, without it coming to problems with future updates.

This documentation gives the basis of a plugin for
LibreNMS.

## Distribution

There are two ways to create a plugin.

 1. Local plugin: Within LibreNMS under the app/Plugins directory. This is appropriate for plugins that are
    for your own instance only. A local plugin uses ONLY the plugin
    hooks to extend LibreNMS.
 3. Plugin package: A php package that can be distributed via composer/packagist.org.  This is appropriate
    for plugins that are intended to be installed by many people. A plugin package can publish multiple
    routes, views, database migrations and more in addition to using hooks to augment specific parts of LibreNMS.

### Plugin package

Create a package according to the Laravel documentation [https://laravel.com/docs/packages](https://laravel.com/docs/packages)
To tie in to specific parts of LibreNMS such as the Menu, Device Overview or a Port Tab, use Plugin Hooks.

You can see an example plugin here: [example plugin repository](https://github.com/murrant/librenms-example-plugin).

> Please come to discord and share any experiences and update this documentation!

## Local plugin

Local plugins need to be placed in app/Plugins

> Note: Plugins are disabled when the have an error, to show errors instead set plugins.show_errors

The structure of a plugin is follows:

```
app/Plugins
            /PluginName
                       /DeviceOverview.php
                       /Menu.php
                       /Page.php
                       /PortTab.php
                       /Settings.php
                       /resources/views
                                       /device-overview.blade.php
                                       /menu.blade.php
                                       /page.blade.php
                                       /port-tab.blade.php
                                       /settings.blade.php
```

The above structure is checked before a plugin can be installed.

All file/folder names are case sensitive and must match the structure.

Only the blade files that are really needed need to be created. A plugin manager
then loads a hook with a basic function.

If you want to customize the basic behavior of the hooks, you can create a
class in 'app/Plugins/PluginName' and overload the hook methods.

- device-overview.blade.php :: This is called in the Device
  Overview page. You receive the $device as a object per default, you can do your
  work here and display your results in a frame.

```
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
            <div class="panel-heading">
                <strong>{{ $title }}</strong>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                         {{ $device->hostname }}
                 <!-- Do you stuff here -->
                    </div>
        </div>
        </div>
    </div>
    </div>
</div>
```

- port-tab.blade.php :: This is called in the Port page,
  in the "Plugins" menu option. This option appears when LibreNMS
  enabled. In this blade, you can do your work and display your
  results in a frame.

- menu.blade.php :: For a menu entry

- page.blade.pho :: Here is a good place to add a own LibreNMS page without dependence with a device. A good place to create your own lists with special requirements and behavior.

- settings.blade.php :: If you need your own settings and variables, you can have a look in the ExamplePlugin.


### PHP Hooks customization

Put your PHP code in the hook method, not in the blade view.
The built in hooks support authorize and data methods.

These methods are called with [Dependency Injection](https://laravel.com/docs/container#method-invocation-and-injection)
A hook with database models passes them in these calls.
The settings argument passes the plugin settings to the method.

#### Data

Override the data method to give data to your view. Do the processing
in this method.
You can do things like access the database or configuration settings and more.

In the data method we are injecting settings here to count how many we have for display in the menu entry blade view.
Note: give a default value, `= []` here, for each argument outside the
parent method.

```php
class Menu extends MenuEntryHook
{
    public function data(array $settings = []): array
    {
        return [
            'count' => count($settings),
        ];
    }
}
```

#### Authorize 

By default, LibreNMS always shows the hooks. You can control the access
of the user to the hook content.

For example, `device-overview.blade.php` appears only when the
device is in a maintenance mode and the current user has the admin role.

```php
class DeviceOverview extends DeviceOverviewHook
{
    public function authorize(User $user, Device $device): bool
    {
        return $user->can('admin') && $device->isUnderMaintenance();
    }
}
```
