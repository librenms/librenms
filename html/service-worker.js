self.addEventListener('fetch', function(event) {});

self.addEventListener('push', function (e) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        //notifications aren't supported or permission not granted!
        return;
    }

    if (e.data) {
        var msg = e.data.json();
        console.log(msg)
        e.waitUntil(self.registration.showNotification(msg.title, {
            body: msg.body,
            icon: msg.icon,
            actions: msg.actions,
            data: msg.data
        }));
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    console.log(event);
    let url;
    if (event.action === 'alert.acknowledge') {

    } else if (event.action === 'alert.view') {
        // navigate to alert
        event.waitUntil(self.clients.claim().then(() => self.clients.matchAll({type: 'window'}))
            .then(clients => {
            return clients.map(client => {
                let alert_url = '/alerts?alert_id=' + event.notification.data.id;
                // Check to make sure WindowClient.navigate() is supported.
                if ('navigate' in client) {
                    return client.navigate(alert_url);
                }

                return self.clients.openWindow(alert_url);
            });
        }));
    } else {
        // Main body of notification was clicked
    }
}, false);

var csrf;
const fetchCsrf = async () => {
    const response = await fetch('/push/token')
    csrf = await response.text();
}
