## Philips Hue

LibreNMS flashes all lights on your Philips Hue bridge at each
triggered alert.

To set this up, open <http://`your-bridge-ip`/debug/clip.html>.

- Set the "URL:" field to `/api`.
- Paste `{"devicetype":"librenms"}` into the "Message Body" field.
- Press the round button on your Philips Hue bridge.
- Click `POST`.
- The `Command Response` field then shows your username. Copy this value without the quotation marks.

For more information, read the [Philips Hue documentation](https://www.developers.meethue.com/documentation/getting-started).

**Example:**

| Config | Example |
| ------ | ------- |
| Host | http://your-bridge-ip |
| Hue User | username |
| Duration | 1 Second |
