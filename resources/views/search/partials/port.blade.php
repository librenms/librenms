{{-- device › port, $dimDevice fades a device repeated from the row above so rows read as groups --}}
<span class="tw:whitespace-nowrap">
    <span @class(['tw:opacity-50' => $dimDevice ?? false])>{!! \LibreNMS\Util\Url::modernDeviceLink($port?->device) !!}</span>
    @if($port)
        <span class="tw:text-gray-400">›</span>
        {!! \LibreNMS\Util\Url::modernPortLink($port) !!}
    @endif
</span>
