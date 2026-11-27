{{-- device › port --}}
<span class="tw:wrap-break-word tw:min-w-0">
    {!! \LibreNMS\Util\Url::modernDeviceLink($port?->device) !!}
    @if($port)
        <span class="tw:text-gray-400">›</span>
        {!! \LibreNMS\Util\Url::modernPortLink($port) !!}
    @endif
</span>
