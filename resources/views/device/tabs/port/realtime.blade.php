<x-option-bar :name="__('Polling Interval')"
              :options="collect($intervals)->mapWithKeys(fn ($i) => [$i => ['text' => $i . 's', 'link' => route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => 'realtime', 'interval' => $i])]])->all()"
              :selected="$interval" />
<div class="tw:text-center tw:m-8">
    <object data="{{ route('realtime.graph', ['port' => $port->port_id, 'interval' => $interval]) }}" type="image/svg+xml" width="1000" height="400" class="tw:max-w-full">
        <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options">{{ __('Your webserver has header X-Frame-Options set to DENY. Please change to SAMEORIGIN for realtime graphs.') }}</a>
    </object>
</div>
