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

## Custom device menu entry

You can add custom external links in the menu on the device page.

This feature allows you to easily link applications to related
systems, as shown in the example of Open-audIT.

The Links value is parsed by Laravel Blade's templating engine so you
can use Device variables such as `hostname`, `sysName` and more.

```bash
lnms config:set html.device.links.+ '{"url": "http://atssrv/open-audit/index/devices/{{ $device->sysName }}", "title": "Open-AudIT"}'
```

### Launching Windows programs from the LibreNMS device menu

You can launch windows programs from links in LibrNMS, but it does take
some registry entries on the client device

```
[HKEY_CLASSES_ROOT\winbox]
@="URL:winbox Protocol"
"URL Protocol"=""
[HKEY_CLASSES_ROOT\winbox\shell]
[HKEY_CLASSES_ROOT\winbox\shell\open]
[HKEY_CLASSES_ROOT\winbox\shell\open\command]
@="c:\winbox.exe" "%1"
```

Now we can use that in the device menu entry to open winbox

```bash
lnms config:set html.device.links.+ '{"url": "winbox://{{ $device->ip }}", "title": "Winbox"}'
```
