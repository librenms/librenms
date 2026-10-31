{{-- device › port --}}
<span class="tw:whitespace-nowrap">
    {!! \LibreNMS\Util\Url::modernDeviceLink($port?->device) !!}
    @if($port)
        <span class="tw:text-gray-400">›</span>
        {!! \LibreNMS\Util\Url::modernPortLink($port) !!}
    @endif
</span>
