@props(['state'])

@php($icon = \LibreNMS\Util\Html::appStateIcon($state))
@if($icon['icon'])
    <i class="fa fa-fw {{ $icon['icon'] }}" title="{{ __($icon['hover_text']) }}" aria-label="{{ __($icon['hover_text']) }}"></i>
@endif
