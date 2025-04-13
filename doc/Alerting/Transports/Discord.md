## Discord

The Discord transport will POST the alert message to your Discord
Incoming WebHook. The only required value is Discord URL, without this no call to Discord will be made. 

Graphs can be included in the template using: ```<img class="librenms-graph" src=""/>```. The rest of the html tags are stripped from the message.


 The Options field supports JSON/Form Params listed
in the 
[Discord Docs](https://discordapp.com/developers/docs/resources/webhook#execute-webhook). Fields to embed is a comma separated list from the [Alert Data](https://github.com/librenms/librenms/blob/master/LibreNMS/Alert/AlertData.php)).


**Example:**

| Config | Example |
| ------ | ------- |
| Discord URL | <https://discordapp.com/api/webhooks/4515489001665127664/82-sf4385ysuhfn34u2fhfsdePGLrg8K7cP9wl553Fg6OlZuuxJGaa1d54fe> |
| Options | username=myname</br>content=Some content</br>tts=false |
| Fields to embed | hostname,name,timestamp,severity |