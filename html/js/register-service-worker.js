if ('serviceWorker' in navigator) {
    // Register a Service Worker.
    navigator.serviceWorker.register('http://localhost/service-worker.js');

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

            // Send the subscription details to the server
            const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
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
    });
}

// if ('serviceWorker' in navigator) {
//     window.addEventListener('load', function () {
//         initSW();
//     });
//     console.log('tried')
// } else {
//     console.log('no service worker?')
// }

function initSW() {
    if (!"serviceWorker" in navigator) {
        //service worker isn't supported
        return;
    }

    //don't use it here if you use service worker
    //for other stuff.
    if (!"PushManager" in window) {
        //push isn't supported
        return;
    }

    //register the service worker
    navigator.serviceWorker.register('../service-worker.js')
        .then(() => {
            console.log('serviceWorker installed!')
            initPush();
        })
        .catch((err) => {
            console.log(err)
        });
}

function initPush() {
    if (!navigator.serviceWorker.ready) {
        return;
    }

    new Promise(function (resolve, reject) {
        const permissionResult = Notification.requestPermission(function (result) {
            resolve(result);
        });

        if (permissionResult) {
            permissionResult.then(resolve, reject);
        }
    })
        .then((permissionResult) => {
            if (permissionResult !== 'granted') {
                throw new Error('We weren\'t granted permission.');
            }
            subscribeUser();
        });
}

function subscribeUser() {
    navigator.serviceWorker.ready
        .then((registration) => {
            const subscribeOptions = {
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(
                    'BF3eYd4ByWhjGyUoH9cXOur4dKP7iudFDhqcZddU87NSkwDKJ0QfxfMtxTFHnP9v8zEk35zQ7LYWLaXehqyqxIk'
                )
            };

            return registration.pushManager.subscribe(subscribeOptions);
        })
        .then((pushSubscription) => {
            console.log('Received PushSubscription: ', JSON.stringify(pushSubscription));
            storePushSubscription(pushSubscription);
        });
}

function storePushSubscription(pushSubscription) {
    const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');

    fetch('/push/register', {
        method: 'POST',
        body: JSON.stringify(pushSubscription),
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': token
        }
    })
        .then((res) => {
            return res.json();
        })
        .then((res) => {
            console.log(res)
        })
        .catch((err) => {
            console.log(err)
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
