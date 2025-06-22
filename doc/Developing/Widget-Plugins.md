# Widget Plugin Development

Create custom dashboard widgets using LibreNMS's V2 plugin system.

## Quick Start

Create a widget plugin in 4 steps:

1. Create widget hook class implementing `DashboardWidgetHook`
2. Create widget controller extending `WidgetController`
3. Register widget in service provider with container tagging
4. Create Blade templates for display and settings

## Plugin Structure

```
your-plugin/
├── composer.json
├── src/
│   ├── Hooks/
│   │   └── YourWidgetHook.php
│   ├── Http/Controllers/Widgets/
│   │   └── YourWidgetController.php
│   └── Providers/
│       └── YourServiceProvider.php
├── resources/
│   ├── views/widgets/
│   │   ├── your-widget.blade.php
│   │   └── settings.blade.php
│   └── lang/en/
│       └── widgets.php
└── routes/
    └── web.php
```

## Widget Hook Implementation

Create hook class implementing the required interface:

```php
<?php

namespace YourPlugin\Hooks;

use LibreNMS\Interfaces\Plugins\Hooks\DashboardWidgetHook;

class YourWidgetHook implements DashboardWidgetHook
{
    public function getWidgetName(): string
    {
        return 'your-widget-name';
    }

    public function getWidgetController(): string
    {
        return 'YourPlugin\Http\Controllers\Widgets\YourWidgetController';
    }

    public function getWidgetTitle(): string
    {
        return __('your-plugin::widgets.your_widget.title');
    }
}
```

## Widget Controller

Extend [`WidgetController`](../../app/Http/Controllers/Widgets/WidgetController.php:37) base class:

```php
<?php

namespace YourPlugin\Http\Controllers\Widgets;

use App\Http\Controllers\Widgets\WidgetController;
use Illuminate\Http\Request;

class YourWidgetController extends WidgetController
{
    protected $title;
    
    protected $defaults = [
        'title' => null,
        'refresh' => 60,
        'your_setting' => 'default_value',
    ];

    public function __construct()
    {
        $this->title = __('your-plugin::widgets.your_widget.title');
    }

    public function getView(Request $request)
    {
        $settings = $this->getSettings();
        $data = $this->getWidgetData($settings);
        
        return view('your-plugin::widgets.your-widget', [
            'data' => $data,
            'settings' => $settings,
        ]);
    }

    public function getSettingsView(Request $request)
    {
        return view('your-plugin::widgets.settings', $this->getSettings(true));
    }

    private function getWidgetData(array $settings): array
    {
        // Implement data retrieval logic
        return ['items' => [], 'count' => 0];
    }
}
```

## Service Provider Registration

Register widget using Laravel container tagging:

```php
<?php

namespace YourPlugin\Providers;

use Illuminate\Support\ServiceProvider;
use LibreNMS\Interfaces\Plugins\PluginManagerInterface;
use YourPlugin\Hooks\YourWidgetHook;

class YourServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerWidgets();
    }

    public function boot(PluginManagerInterface $pluginManager): void
    {
        if (!$pluginManager->pluginEnabled('your-plugin-name')) {
            return;
        }

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'your-plugin');
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'your-plugin');
    }

    protected function registerWidgets(): void
    {
        $this->app->bind(YourWidgetHook::class, function () {
            return new YourWidgetHook();
        });

        $this->app->tag([YourWidgetHook::class], 'librenms.widget');
    }
}
```

## Widget Templates

### Display Template

Create `resources/views/widgets/your-widget.blade.php`:

```blade
<div class="widget-content">
    @if(count($data['items']) > 0)
        @foreach($data['items'] as $item)
            <div class="widget-item">
                <span>{{ $item['name'] }}</span>
                <span>{{ $item['value'] }}</span>
            </div>
        @endforeach
    @else
        <div class="text-center text-muted p-3">
            {{ __('your-plugin::widgets.your_widget.no_data') }}
        </div>
    @endif
</div>
```

### Settings Template

Create `resources/views/widgets/settings.blade.php`:

```blade
@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}">{{ __('Widget Title') }}</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" 
               value="{{ $title }}" placeholder="{{ __('Custom title') }}">
    </div>

    <div class="form-group">
        <label for="your-setting-{{ $id }}">{{ __('Custom Setting') }}</label>
        <input type="text" class="form-control" name="your_setting" id="your-setting-{{ $id }}" 
               value="{{ $your_setting }}">
    </div>
@endsection
```

## Advanced Features

### Data Caching

Cache expensive operations:

```php
private function getWidgetData(array $settings): array
{
    $cacheKey = "widget.your-widget.{$settings['id']}";
    
    return Cache::remember($cacheKey, 300, function () use ($settings) {
        return $this->fetchExpensiveData($settings);
    });
}
```

### Settings Validation

Add validation in controller:

```php
public function getSettingsView(Request $request)
{
    $request->validate([
        'your_setting' => 'required|string|max:255'
    ]);
    
    return parent::getSettingsView($request);
}
```
