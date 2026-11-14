## Twilio SMS

Twilio sends your alert by SMS. From your Twilio account, you need the
account SID, the account token, and the Twilio SMS phone number of the
sender. The Twilio APIs are at
[https://www.twilio.com/docs/api?filter-product=sms](https://www.twilio.com/docs/api?filter-product=sms).

**Example:**

| Config | Example |
| ------ | ------- |
| SID | ACxxxxxxxxxxxxxxxxxxxxxxxxxxxx |
| Token | 7xxxx573acxxxbc2xxx308d6xxx652d32 |
| Twilio SMS Number | 8888778660 |

## Twilio Phone

Twilio sends your alert by calls. Requirements match Twilio SMS.

At least one of Call To, Call by Roles, or Call by Users must be configured. Selected users must be enabled and have valid E.164 phone numbers, or the transport skips them and may make no calls.

Required Fields:
- Account SID: Account SID used in the API URL.
- API SID: the SID of the API key.
- Secret: the API key secret.
- Twilio Phone Number: a voice-capable Twilio number to call from, in E.164 format.
- TwiML: the call script. These placeholders are replaced with alert values (XML-escaped): `%hostname%`, `%sysName%`, `%sysDescr%`, `%sysContact%`, `%os%`, `%ip%`, `%display%`, `%title%`, `%severity%`.

See the [Voice API](https://www.twilio.com/docs/voice/api) and [TwiML](https://www.twilio.com/docs/voice/twiml) documentation.
