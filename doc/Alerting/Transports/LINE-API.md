## LINE Messaging API

[LINE Messaging API Docs](https://developers.line.biz/en/docs/messaging-api/overview/)

These are the steps to set up a LINE bot for LibreNMS.

1. Register your real LINE account in the [developer portal](https://developers.line.biz/).

1. Add a new channel and choose `Messaging API`. Then complete the forms. You cannot change `Channel name` later.

1. Open the "Messaging API" tab of your channel. This tab holds some important values.

	- `Bot basic ID` and `QR code` are the ID and the QR code of your LINE bot.
	- `Channel access token (long-lived)` goes into LibreNMS. Keep it secret.

1. Add your LINE bot as a friend from your real LINE account.

1. The recipient ID is a `groupID`, a `userID`, or a `roomID`. LibreNMS uses this ID to send a message to a group or a user. To find it, use the NodeJS program below with `ngrok` as a temporary HTTPS webhook.

	[LINE-bot-RecipientFetcher](https://github.com/j796160836/LINE-bot-RecipientFetcher)

1. Run the program. Then make the port public with `ngrok`.

	```
	$ node index.js
	$ ngrok http 3000
	```

1. Open the "Messaging API" tab of your channel. Set the Webhook URL to `https://<your ngrok domain>/webhook`.


1. To send a message to yourself, send a message to your LINE bot from your real account. The program then prints the `userID` in the console.

	sample value:  
	
	```
	{"type":"user","userId":"U527xxxxxxxxxxxxxxxxxxxxxxxxxc0ee"}
	```
	
1. To send a message to a group, do these steps.

	- Add your LINE bot to the group.
	- Send a message to the group from your real account.
	
	The program then prints the `groupID` in the console. This value is
	the recipient ID. Keep it secret.

	sample value:

	```
	{"type":"group","groupId":"Ce51xxxxxxxxxxxxxxxxxxxxxxxxxx6ef","userId":"U527xxxxxxxxxxxxxxxxxxxxxxxxxc0ee"} ```
	```

**Example:**

| Config | Example |
| ------ | ------- |
| Access token | fhJ9vH2fsxxxxxxxxxxxxxxxxxxxxlFU= |
| Recipient (groupID, userID or roomID) | Ce51xxxxxxxxxxxxxxxxxxxxxxxxxx6ef |