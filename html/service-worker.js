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
        url = '/alerts?alert_id=' + event.notification.data.id;
    } else {
        // Main body of notification was clicked
    }

    // Try to open in existing window WIP not working
    if (url) {
        event.waitUntil(clients.matchAll({ type: 'window' }).then(clientsArr => {
            console.log(clientsArr);
            if (clientsArr.length === 0) {
                clients.openWindow(url).then(windowClient => windowClient ? windowClient.focus() : null);
                return;
            }

            let first = clientsArr[0];
            first.navigate(url).then(windowClient => windowClient ? windowClient.focus() : null);

        }));
    }
}, false);

var csrf;
const fetchCsrf = async () => {
    const response = await fetch('/push/token')
    csrf = await response.text();
}
