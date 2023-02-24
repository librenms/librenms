<div<div x-data="notificationSubscriptionStatus()">
    <div x-show="! supported">{{ __('components.notification-subscription-status.no-support') }}</div>
    @if($userHasTransport)
    <div x-show="supported">
        <div>
            <span x-text="enabled ? '{{ __('components.notification-subscription-status.enabled') }}' : '{{ __('components.notification-subscription-status.disabled') }}'"></span>
            <button x-on:click="toggle()" type="button" class="tw-float-right tw-border tw-border-gray-500 tw-text-gray-500 hover:tw-bg-gray-500 hover:tw-text-gray-100 tw-rounded tw-px-4 tw-py-2" x-text="enabled ? '{{ __('components.notification-subscription-status.disable') }}' : '{{ __('components.notification-subscription-status.enable') }}'"></button>
        </div>
    </div>
    @else
    <div x-show="supported">
        @admin
            <a href="{{ url('alert-transports') }}">
                {{ __('components.notification-subscription-status.no-transport') }}
            </a>
        @else
            {{ __('components.notification-subscription-status.no-transport') }}
        @endadmin
    </div>
    @endif
    <script>
        function notificationSubscriptionStatus() {
            return {
                supported: 'Notification' in window,
                enabled: 'Notification' in window && Notification.permission === 'granted' && localStorage.getItem('notifications') !== 'disabled',
                toggle() {
                    if (this.enabled) {
                        localStorage.setItem('notifications', 'disabled');
                        this.enabled = false;
                        navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
                            serviceWorkerRegistration.pushManager.getSubscription()
                                .then(function(subscription) {
                                    if (subscription) {
                                        subscription.unsubscribe().then(function(success) {
                                            if (success) {
                                                fetch('./push/unregister', {
                                                    method: 'post',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-Token': '{{ csrf_token() }}'
                                                    },
                                                    body: JSON.stringify({
                                                        endpoint: subscription.endpoint
                                                    }),
                                                });
                                            }
                                        })
                                    }
                                })
                        })
                    } else if (Notification.permission === 'granted') {
                        localStorage.setItem('notifications', 'enabled');
                        this.enabled = true;
                    } else {
                        Notification.requestPermission().then((permission) => {
                            localStorage.setItem('notifications', 'enabled');
                            this.enabled = permission === 'granted';
                        });
                    }
                }
            }
        }
    </script>
</di</div>
