## HipChat

For the valid values, read the HipChat API documentation for
[rooms/message](https://www.hipchat.com/docs/api/method/rooms/message).

> The link points to the deprecated v1 API, because the v2 API is still
> in beta.

**Example:**

| Config | Example |
| ------ | ------- |
| API URL | <https://api.hipchat.com/v1/rooms/message?auth_token=109jawregoaihj> |
| Room ID | 7654321 |
| From Name | LibreNMS |
| Options | color=red |

Only the `color` option is available.

> Note: the default message format of HipChat is HTML. Use the `text`
> message format to prevent an unexpected result. For example, HipChat
> interprets the angle brackets `<` and `>` in HTML mode.