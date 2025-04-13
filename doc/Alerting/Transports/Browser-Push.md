## Browser Push

Browser push notifications can send a notification to the user's
device even when the browser is not open. This requires HTTPS, the PHP
GMP extension, [Push
API](https://developer.mozilla.org/en-US/docs/Web/API/Push_API)
support, and permissions on each device to send alerts.

Simply configure an alert transport and allow notification permission
on the device(s) you wish to receive alerts on.  You may disable
alerts on a browser on the user preferences page.