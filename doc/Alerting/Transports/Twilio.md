## Twilio SMS

Twilio sends your alert by SMS. The Twilio APIs are at
[https://www.twilio.com/docs/api?filter-product=sms](https://www.twilio.com/docs/api?filter-product=sms).

You can authenticate in one of two ways:

- **Account SID and Auth Token:** Leave API SID blank and enter your
  account's Auth Token in Token. The Auth Token has full access to your
  Twilio account.
- **API Key (recommended):** Create an API key in the Twilio Console under
  Account > API keys & tokens. Enter its SID (starts with `SK`) in API SID,
  and its secret in Token. API keys can be revoked without changing your
  Auth Token.

In both cases the Account SID is required. Enter phone numbers in E.164
format, including the country code, e.g. `+15055551234`.

**Example:**

| Config | Example |
| ------ | ------- |
| Account SID | ACxxxxxxxxxxxxxxxxxxxxxxxxxxxx |
| Token | 7xxxx573acxxxbc2xxx308d6xxx652d32 |
| API SID | SKxxxxxxxxxxxxxxxxxxxxxxxxxxxx (optional) |
| Mobile Number | +15055551234 |
| Twilio SMS Number | +18888778660 |
