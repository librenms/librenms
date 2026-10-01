## aspSMS

aspSMS is an SMS provider. It uses the generic API transport. You need
a token from your personal space.

[aspSMS docs](https://www.aspsms.com/en/documentation/)

**Example:**

| Config | Example |
| ------ | ------- |
| Transport type | Api |
| API Method | POST |
| API URL | https://soap.aspsms.com/aspsmsx.asmx/SimpleTextSMS |
| Options | UserKey=USERKEY<br />Password=APIPASSWORD<br />Recipient=RECIPIENT<br/> Originator=ORIGINATOR<br />MessageText={{ $msg }} |