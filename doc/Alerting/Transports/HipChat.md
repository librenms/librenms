## HipChat

See the HipChat API Documentation for [rooms/message](https://www.hipchat.com/docs/api/method/rooms/message)
for details on acceptable values.

> You may notice that the link points at the "deprecated" v1 API.  This is
> because the v2 API is still in beta.

**Example:**

| Config | Example |
| ------ | ------- |
| API URL | <https://api.hipchat.com/v1/rooms/message?auth_token=109jawregoaihj> |
| Room ID | 7654321 |
| From Name | LibreNMS |
| Options | color=red |

At present the following options are supported: `color`.

> Note: The default message format for HipChat messages is HTML.  It is
> recommended that you specify the `text` message format to prevent unexpected
> results, such as HipChat attempting to interpret angled brackets (`<` and
> `>`).