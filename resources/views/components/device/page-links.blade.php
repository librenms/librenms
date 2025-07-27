<div class="btn-group pull-right" role="group">
    <a href="{{ $primaryDeviceLink['url'] }}"
       class="btn btn-default"
       type="button"
       @if(isset($primaryDeviceLink['onclick']))onclick="{{ $primaryDeviceLink['onclick'] }}" @endif
       @if($primaryDeviceLink['external'])target="_blank" rel="noopener" @endif
       title="{{ $primaryDeviceLink['title'] }}"
    >&nbsp;<i class="fa {{ $primaryDeviceLink['icon'] }} fa-lg icon-theme"></i>
    </a>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            &nbsp;<i class="fa fa-ellipsis-v fa-lg icon-theme"></i>&nbsp;
        </button>
        <ul class="dropdown-menu dropdown-menu-right">
            @foreach($deviceLinks as $link)
                <li><a href="{{ $link['url'] }}"
                       @if(isset($link['onclick']))onclick="{{ $link['onclick'] }}" @endif
                       @if($link['external'])target="_blank" rel="noopener" @endif
                    ><i class="fa {{ $link['icon'] }} fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ $link['title'] }}</a></li>
            @endforeach
            @if($pageLinks)
                <li role="presentation" class="divider"></li>
                @foreach($pageLinks as $link)
                    <li><a href="{{ $link['url'] }}"
                           @if(isset($link['onclick']))onclick="{{ $link['onclick'] }}" @endif
                           @if($link['external'])target="_blank" rel="noopener" @endif
                        ><i class="fa {{ $link['icon'] }} fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ $link['title'] }}</a></li>
                @endforeach
            @endif
        </ul>
    </div>
</div>
