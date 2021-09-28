if ('serviceWorker' in navigator) {
    // Register a Service Worker.
    navigator.serviceWorker.register('/service-worker.js');

    navigator.serviceWorker.ready
        .then(function (registration) {
            return registration.pushManager.getSubscription()
                .then(async function (subscription) {
                    if (subscription) {
                        return subscription;
                    }

                    if (Notification.permission === "granted") {
                        // Get the server's public key
                        const response = await fetch('./push/key');
                        const vapidPublicKey = await response.text();
                        const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

                        return registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: convertedVapidKey
                        });
                    }
                });
        }).then(function (subscription) {
            if (! subscription) {
                console.log('no subscription');
                return;
            }

            const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');

            if (localStorage.getItem('notifications') === 'disabled' || Notification.permission === "denied") {
                // disabled by user remove the subscription
                fetch('./push/unregister', {
                    method: 'post',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': token
                    },
                    body: JSON.stringify({
                        endpoint: subscription.endpoint
                    }),
                });

            } else {
                // Send the subscription details to the server
                fetch('./push/register', {
                    method: 'post',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': token
                    },
                    body: JSON.stringify({
                        description: navigator.userAgent,
                        subscription: subscription
                    }),
                });

            }
    });
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}
