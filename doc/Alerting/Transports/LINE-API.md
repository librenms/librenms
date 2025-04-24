## LINE Messaging API

[LINE Messaging API Docs](https://developers.line.biz/en/docs/messaging-api/overview/)

Here is the step for setup a LINE bot and using it in LibreNMS.

1. Use your real LINE account register in [developer protal](https://developers.line.biz/).

1. Add a new channel, choose `Messaging API` and continue fill up the forms, note that `Channel name` cannot edit later.

1. Go to "Messaging API" tab of your channel, here listing some important value.

	- `Bot basic ID` and `QR code` is your LINE bot's ID and QR code.
	- `Channel access token (long-lived)`, will use it in LibreNMS, keep it safe.

1. Use your real Line account add your LINE bot as a friend.

1. Recipient ID can be `groupID`, `userID` or `roomID`, it will be used in LibreNMS to send message to a group or a user. Use the following NodeJS program and `ngrok` for temporally https webhook to listen it.

	[LINE-bot-RecipientFetcher](https://github.com/j796160836/LINE-bot-RecipientFetcher)

1. Run the program and using `ngrok` expose port to public

	```
	$ node index.js
	$ ngrok http 3000
	```

1. Go to "Messaging API" tab of your channel, fill up Webhook URL to `https://<your ngrok domain>/webhook`


1. If you want to let LINE bot send message to a yourself, use your real account to send a message to your LINE bot. Program will print out the `userID` in console.

	sample value:  
	
	```
	{"type":"user","userId":"U527xxxxxxxxxxxxxxxxxxxxxxxxxc0ee"}
	```
	
1. If you want to let LINE bot send message to a group, do the following steps.

	- Add your LINE bot into group
	- Use your real account to send a message to group
	
	Program will print out the `groupID` in console, it will be Recipient ID, keep it safe.

	sample value:

	```
	{"type":"group","groupId":"Ce51xxxxxxxxxxxxxxxxxxxxxxxxxx6ef","userId":"U527xxxxxxxxxxxxxxxxxxxxxxxxxc0ee"} ```
	```

**Example:**

| Config | Example |
| ------ | ------- |
| Access token | fhJ9vH2fsxxxxxxxxxxxxxxxxxxxxlFU= |
| Recipient (groupID, userID or roomID) | Ce51xxxxxxxxxxxxxxxxxxxxxxxxxx6ef |