## aspSMS

aspSMS is a SMS provider that can be configured by using the generic API Transport.
You need a token you can find on your personnal space.

[aspSMS docs](https://www.aspsms.com/en/documentation/)

**Example:**

| Config | Example |
| ------ | ------- |
| Transport type | Api |
| API Method | POST |
| API URL | https://soap.aspsms.com/aspsmsx.asmx/SimpleTextSMS |
| Options | UserKey=USERKEY<br />Password=APIPASSWORD<br />Recipient=RECIPIENT<br/> Originator=ORIGINATOR<br />MessageText={{ $msg }} |