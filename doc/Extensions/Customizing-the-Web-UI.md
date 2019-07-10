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

You can add custom external system link in the menu on the device page, and you can take the device information as a parameter to the url, such as hostname, sysName.
  
This feature allows you to easily link applications to related systems, as shown in the example of Open-audIT.
  
Open the file `config.php`:

```php
$config['html']['device']['links'] = [['url' => 'http://atssrv/open-audit/index/devices/{{ $device[\'sysName\'] }}', 'title' => 'Open-AudIT']];
```
  
If you want to add more external system links, add them to the array of values.
