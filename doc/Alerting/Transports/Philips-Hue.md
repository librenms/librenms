## Philips Hue

Want to spice up your noc life? LibreNMS will flash all lights
connected to your philips hue bridge whenever an alert is triggered.

To setup, go to the you <http://`your-bridge-ip`/debug/clip.html>

- Update the "URL:" field to `/api`
- Paste this in the "Message Body" {"devicetype":"librenms"}
- Press the round button on your `philips Hue Bridge`
- Click on `POST`
- In the `Command Response` You should see output with your
  username. Copy this without the quotes

More Info: [Philips Hue Documentation](https://www.developers.meethue.com/documentation/getting-started)

**Example:**

| Config | Example |
| ------ | ------- |
| Host | http://your-bridge-ip |
| Hue User | username |
| Duration | 1 Second |
