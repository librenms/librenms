@extends('layouts.librenmsv1')

@section('title', __('API v1 Reference'))

@section('content')
    <div class="container-fluid">
        @include('user.api-navigation')

        {{-- Scalar's standalone bundle automatically mounts any element with id="api-reference". --}}
        <div id="librenms-api-reference" data-openapi-url="{{ route('api-docs.v1.specification') }}"
             data-api-url="{{ url('api/v1') }}"></div>
    </div>
@endsection

@push('styles')
<style>
    :root {
        --scalar-font: var(--tw-font-sans);
        --scalar-font-code: var(--tw-font-mono);
    }

    html.dark body {
        --scalar-background-1: var(--tw-color-dark-gray-500);
        --scalar-background-2: var(--tw-color-dark-gray-400);
        --scalar-background-3: var(--tw-color-dark-gray-300);
        --scalar-color-1: var(--tw-color-dark-white-100);
        --scalar-color-2: var(--tw-color-dark-white-200);
        --scalar-color-3: var(--tw-color-dark-white-300);
        --scalar-border-color: var(--tw-color-dark-gray-100);
    }

    #librenms-api-reference .section {
        padding-block: clamp(24px, 4vw, 48px);
    }

    @container references-container (width > 900px) {
        #librenms-api-reference .section-container {
            padding-inline: 32px;
        }
    }

    .dark .scalar-app .markdown :not(pre) > code {
        color: var(--scalar-color-1);
        background-color: var(--scalar-background-2);
    }

    .dark .scalar-app .scalar-code-block-pre {
        background-color: transparent;
        color: var(--scalar-color-1);
    }

    /* Bootstrap's important .hidden utility otherwise overrides Scalar's desktop sidebar. */
    @layer base {
        @media (min-width: 64rem) {
            #librenms-api-reference .t-doc__sidebar {
                display: flex !important;
            }
        }
    }
</style>
@endpush

@push('scripts')
<script defer src="{{ asset('js/scalar-api-reference-1.84.0.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const reference = document.getElementById('librenms-api-reference');
        const specUrl = new URL(reference.dataset.openapiUrl, location.href);
        const apiUrl = new URL(reference.dataset.apiUrl, location.href);
        const root = document.documentElement;
        let darkMode = root.classList.contains('dark');

        const navbar = document.querySelector('.navbar-sticky-top');
        if (navbar) {
            const updateOffset = () => reference.style.setProperty('--scalar-custom-header-height', navbar.offsetHeight + 'px');
            updateOffset();
            new ResizeObserver(updateOffset).observe(navbar);
        }

        const config = {
            url: specUrl.href,
            agent: { disabled: true },
            documentDownloadType: 'none',
            darkMode,
            forceDarkModeState: darkMode ? 'dark' : 'light',
            hideClientButton: true,
            persistAuth: false,
            showDeveloperTools: 'never',
            withDefaultFonts: false,
            customFetch(input, options) {
                const request = new Request(input, options);
                const url = new URL(request.url);

                if (url.origin !== location.origin) {
                    throw new Error('API requests must target this LibreNMS installation.');
                }

                if (url.href === specUrl.href && request.method === 'GET' && !request.headers.has('Authorization')) {
                    return fetch(request, { redirect: 'error' });
                }

                // Send the bearer token only to this installation's API. The web session is for the spec.
                if (!url.pathname.startsWith(apiUrl.pathname + '/')) {
                    throw new Error('API requests must target this LibreNMS installation.');
                }

                return fetch(request, { credentials: 'omit', redirect: 'error' });
            },
        };
        const viewer = Scalar.createApiReference(reference, config);

        // Scalar updates its request client from darkMode, while its theme classes live on the body.
        const syncTheme = () => {
            const nextDarkMode = root.classList.contains('dark');
            document.body.classList.toggle('dark-mode', nextDarkMode);
            document.body.classList.toggle('light-mode', !nextDarkMode);
            if (nextDarkMode !== darkMode) {
                darkMode = nextDarkMode;
                viewer.updateConfiguration({ ...config, darkMode, forceDarkModeState: darkMode ? 'dark' : 'light' });
            }
        };
        const observer = new MutationObserver(syncTheme);
        observer.observe(root, { attributeFilter: ['class'] });
        observer.observe(document.body, { attributeFilter: ['class'] });
        syncTheme();
    });
</script>
@endpush
