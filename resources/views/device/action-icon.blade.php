<td style="padding: 2px;">
    <a href="{{ $href }}" @if($external ?? true)target="_blank" rel="noopener" @endif title="{{ $title }}">
        &nbsp;<i class="fa fa-lg icon-theme {{ $icon ?? 'fa-external-link' }}"></i>
    </a>
</td>
