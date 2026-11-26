@config('api.v1.enabled')
    <nav aria-label="{{ __('API Settings') }}" class="tw:mb-4">
        <ul class="nav nav-tabs">
            <li @class(['active' => request()->routeIs('api-access.index')])>
                <a href="{{ route('api-access.index') }}" @if(request()->routeIs('api-access.index')) aria-current="page" @endif>
                    {{ __('API Tokens') }}
                </a>
            </li>
            <li @class(['active' => request()->routeIs('api-docs.v1.index')])>
                <a href="{{ route('api-docs.v1.index') }}" @if(request()->routeIs('api-docs.v1.index')) aria-current="page" @endif>
                    {{ __('API v1 Reference') }}
                </a>
            </li>
            @if(request()->routeIs('api-docs.v1.index'))
                <li class="pull-right">
                    <a href="{{ route('api-docs.v1.specification') }}" download="librenms-api-v1.json">
                        <i class="fa fa-download" aria-hidden="true"></i>
                        <span class="hidden-xs">{{ __('Download OpenAPI JSON') }}</span>
                        <span class="visible-xs-inline">{{ __('OpenAPI JSON') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>
@endconfig
