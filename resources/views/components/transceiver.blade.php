@props(['transceiver', 'portlink' => true])

<div class="tw:flex tw:sm:flex-row tw:flex-col" {{ $attributes }}>
    <div class="tw:pr-8">
        @if($portlink && $transceiver->port)<x-port-link :port="$transceiver->port"></x-port-link>@endif
        @if($transceiver->vendor || $transceiver->type)<p class="tw:text-2xl tw:font-bold">{{ $transceiver->vendor }} {{ $transceiver->type }}</p>@endif
        @if($transceiver->model)<p>{{ __('port.transceivers.fields.model', $transceiver->only('model')) }}</p>@endif
        @if($transceiver->serial)<p>{{ __('port.transceivers.fields.serial', $transceiver->only('serial')) }}</p>@endif
        <p>@if($transceiver->revision){{ __('port.transceivers.fields.revision', $transceiver->only('revision')) }}@endif @if($transceiver->date){{ __('port.transceivers.fields.date', $transceiver->only('date')) }}@endif</p>
        @if($transceiver->ddm !== null){{ __('port.transceivers.fields.ddm', ['ddm' => $transceiver->ddm ? __('Yes') : __('No')]) }}@endif
    </div>
    <div>
        @if($transceiver->cable)<p>{{ __('port.transceivers.fields.cable', $transceiver->only('cable')) }}</p>@endif
        @if($transceiver->distance)<p>{{ __('port.transceivers.fields.distance', ['distance' => \LibreNMS\Util\Number::formatSi($transceiver->distance, suffix: 'm')]) }}</p>@endif
        @if($transceiver->wavelength)<p>{{ __('port.transceivers.fields.wavelength', ['wavelength' => $transceiver->wavelength . 'nm']) }}</p>@endif
        @if($transceiver->encoding)<p>{{ __('port.transceivers.fields.encoding', $transceiver->only('encoding')) }}</p>@endif
        @if($transceiver->connector)<p>{{ __('port.transceivers.fields.connector', $transceiver->only('connector')) }}</p>@endif
        @if($transceiver->channels > 1)<p>{{ __('port.transceivers.fields.channels', $transceiver->only('channels')) }}</p>@endif
    </div>
</div>
