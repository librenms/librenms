self.addEventListener('fetch', function (event) {
});

self.addEventListener('push', function (e) {
    if (!(self.Notification && self.Notification.permission === 'granted') && localStorage.getItem('notifications') !== 'disabled') {
        //notifications aren't supported or permission not granted!
        return;
    }

    if (e.data) {
        const msg = e.data.json();
        // console.log(msg)
        e.waitUntil(self.registration.showNotification(msg.title, {
            body: msg.body,
            icon: msg.icon,
            actions: msg.actions,
            data: msg.data
        }));
    }
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    if (event.action === 'alert.acknowledge') {
        post(`./alert/${event.notification.data.id}/ack`, {state: 1})
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
    }
}, false);

let csrf;

function post(url, data, retry = true) {
    if (!self.csrf) {
        return self.post(url, data, true);
    }

    return fetch(url, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': self.csrf
        },
        body: JSON.stringify(data)
    }).then((response) => {
        if (response.status === 419 && retry === true) {
            self.csrf = null; // reset csrf and try again
            return self.post(url, data, false);
        }
    }).catch(e => console.log(e));
}

const fetchCsrf = async () => {
    const response = await fetch('/push/token')
    self.csrf = await response.text();
}
