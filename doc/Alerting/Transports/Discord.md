## Discord

The Discord transport sends the alert message to your Discord incoming
webhook with a POST request. Only the Discord URL is necessary. Without
this URL, LibreNMS makes no call to Discord. 

To add a graph to the template, use ```<img class="librenms-graph" src=""/>```.
LibreNMS removes all other HTML tags from the message.


 The Options field accepts the JSON and form parameters of the [Discord
Docs](https://discordapp.com/developers/docs/resources/webhook#execute-webhook).
"Fields to embed" is a comma separated list from the [Alert
Data](https://github.com/librenms/librenms/blob/master/LibreNMS/Alert/AlertData.php).


**Example:**

| Config | Example |
| ------ | ------- |
| Discord URL | <https://discordapp.com/api/webhooks/4515489001665127664/82-sf4385ysuhfn34u2fhfsdePGLrg8K7cP9wl553Fg6OlZuuxJGaa1d54fe> |
| Options | username=myname</br>content=Some content</br>tts=false |
| Fields to embed | hostname,name,timestamp,severity |