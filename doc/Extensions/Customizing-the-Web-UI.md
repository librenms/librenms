source: Extensions/Customizing-the-Web-UI.md
path: blob/master/doc/

# Customizing the Web UI

## Custom menu entry

Create the file `resources/views/menu/custom.blade.php`

Example contents:

```blade
<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-star fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i>
        <span class="hidden-sm">Custom Menu</span></a>
    <ul class="dropdown-menu">
        @admin
            <li><a href="plugins/Weathermap/output/history/index.html"><i class="fa fa-film fa-fw fa-lg" aria-hidden="true"></i> Weathermap Animation</a></li>
            <li role="presentation" class="divider"></li>
            <li><a href="#"><i class="fa fa-database fa-fw fa-lg" aria-hidden="true"></i> Item 1</a></li>
            <li><a href="#"><i class="fa fa-smile-o fa-fw fa-lg" aria-hidden="true"></i> Item 2</a></li>
            <li><a href="#"><i class="fa fa-anchor fa-fw fa-lg" aria-hidden="true"></i> Item 3</a></li>
            <li><a href="#"><i class="fa fa-plug fa-fw fa-lg" aria-hidden="true"></i> Item 4</a></li>
            <li><a href="#"><i class="fa fa-code-fork fa-fw fa-lg" aria-hidden="true"></i> Item 5</a></li>
            <li><a href="#"><i class="fa fa-truck fa-fw fa-lg" aria-hidden="true"></i> Item 63</a></li>
        @else
            <li><a href="#">You need admin rights to see this</a></li>
        @endadmin
    </ul>
</li>
```

## Custom device menu action

You can add custom external links in the menu on the device page.

This feature allows you to easily link applications to related
systems, as shown in the example of Open-audIT.

The url value is parsed by the [Laravel Blade](https://laravel.com/docs/blade) templating engine. You
can access device variables such as `$device->hostname`, `$device->sysName` and use full PHP.

!!! setting "settings/webui/device"

    ```bash
    lnms config:set html.device.links.+ '{"url": "http://atssrv/open-audit/index/devices/{{ $device->sysName }}", "title": "Open-AudIT"}'
    ```

| Field | Description |
| ---- | ----------- |
| url | Url blade template resulting in valid url. Required. |
| title | Title text displayed in the menu. Required. |
| icon | [Font Awesome icon](https://fontawesome.com/v4.7/icons/) class. Default: fa-external-link |
| external | Open link in new window. Default: true |
| action | Show as action on device list. Default: false |

### Launching Windows programs from the LibreNMS device menu

You can launch windows programs from links in LibreNMS, but it does take
some registry entries on the client device. Save the following as winbox.reg, 
edit for your winbox.exe path and double click to add to your registry.

```
Windows Registry Editor Version 5.00
[HKEY_CLASSES_ROOT\winbox]
@= '@="URL:Winbox Protocol"' =@
"URL Protocol"=""
[HKEY_CLASSES_ROOT\winbox\shell]
[HKEY_CLASSES_ROOT\winbox\shell\open]
[HKEY_CLASSES_ROOT\winbox\shell\open\command]
@= '@="C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe -Command \"$val=\'%l\'; $val = $val.TrimEnd(\'/\');if ($val.StartsWith(\'winbox://\' { $val = $val.SubString(9) }; & \'C:\Program Files\winbox64.exe\' \"$val\"\""' =@
```

Now we can use that in the device menu entry to open winbox.

!!! setting "settings/webui/device"

    ```bash
    lnms config:set html.device.links.+ '{"url": "winbox://{{ $device->hostname }}", "title": "Winbox"}'
    ```

## Setting the primary device menu action

You can change the icon that is clickable in the device without having to open the dropdown menu.
The primary button is edit device by default.

!!! setting "settings/webui/device"

    ```bash
    lnms config:set html.device.primary_link web
    ```

| Value | Description |
| ----- | ----------- |
| edit | Edit device |
| web | Connect to the device via https/http |
| ssh | launch ssh:// protocol to the device, make sure you have a handler registered |
| telnet | launch telnet:// protocol to the device |
| capture | Link to the device capture page |
| custom1 | Custom Link 1 |
| custom2 | Custom Link 2 |
| custom3 | Custom Link 3 |
| custom4 | Custom Link 4 |
| custom5 | Custom Link 5 |
| custom6 | Custom Link 6 |
| custom7 | Custom Link 7 |
| custom8 | Custom Link 8 |

