source: Extensions/public-status-page.md
path: blob/master/doc/
# Public Status Page

> Status: Stable

LibreNMS include a public status page option that you can enable to give a looking glass on your infrastructure.

This document will show you how to enable this page and customize it


## Enabling the public status page

Configuration of the public status page activation is made directly from your config.php:

```php
$config['public_status']    = true;
```

## Basic Customization

The default status page may not feed your needs or what you want to show.
The configuration of the status page take place in this file:

`librenmsinstalldir/resources/views/auth/public-status.blade.php`

Please make a backup of it before editing.

### Title of the page

Replace only the name `@lang('Public Devices')` on line 4:

```php
@section('title')
    @lang('Public Devices')
@append
```

### Title of the menus
Lines 17 to 23.

The `@lang` vars must correspond to internal LibreNMS vars for it to adapt automatically the translation.

If you don't care about automating translation, you can put the name in hard text.

```php
                    <tr>
                        <th></th>
                        <th id="icon-header"></th>
                        <th>@lang('Device')</th>
                        <th>@lang('Platform')</th>
                        <th>@lang('Uptime')/@lang('Location')</th>
                    </tr>
```


### Informations to show
Lines 26 to 32. What we really want is at lines 29 to 31 to choose what information we show here.

The simpliest thing here to get the right information to show is to take the vars from the alerting configuration:

`$device->purpose`

```php
                    @foreach($devices as $device)
                        <tr>
                            <td><span class="alert-status {{ $device->status ? 'label-success' : 'label-danger' }}"></span></td>
                            <td><img src="{{ asset($device->icon) }}" width="32px" height="32px"></td>
                            <td class="device-name">{{ $device->displayName() }}</td>
                            <td>{{ $device->hardware }} {{ $device->features }}</td>
                            <td>{{ $device->formatUptime(true) }}<br>{{ substr($device->location, 0, 32) }}</td>
                        </tr>
```

## Sample
(Please add your own customization to show the possibility of this page if you can):

- https://github.com/stylersnico/LibreNMS-publicstatus
