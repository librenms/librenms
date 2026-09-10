# Two-Factor Authentication

The static password is the main attack vector against internet
accounts. A static password alone therefore no longer prevents
unauthorized access. Two-factor authentication adds a variable part to
the authentication. The user gives a 6-digit passcode with their
password. This passcode changes at each login.

LibreNMS implements the time based and the counter based one-time
passwords of RFC4226. The administrator can also configure a throttle
time after 3 failures. RFC4226 recommends an increase of this time with
the number of failures. LibreNMS keeps the time constant.

## Types

The algorithm of the two types is the same. Only the variable of the
passcode differs. Both types use HMAC-SHA1. The sections below give the
security advantages and disadvantages of each type.

### Timebased One-Time-Password (TOTP)

This type uses the current time, or a part of it, to generate the
passcodes. The security of these passcodes depends only on the secret
key. An attacker needs the secret key. The other variable is the time,
usually the time of the login. RFC4226 recommends a resynchronization
after a passcode mismatch. This resynchronization gives the attacker a
range of +/- 3 minutes for the passcodes.

### Counterbased One-Time-Password (HOTP)

This type uses an internal counter. This counter must match the counter
of the server for a successful authentication. The main advantage over
time based OTP is the second secret. The attacker needs the secret key
and the server counter for a valid passcode. RFC4226 recommends a
resynchronization after a passcode mismatch. This resynchronization
gives the attacker a range of +4 increments from the real counter.

## Configuration

### WebUI

Enable 'Two-Factor' in the web interface. Go to Global Settings ->
Authentication -> General Authentication Settings.

You can also enter a throttle timer in seconds. After 3 failed
attempts, the account unlocks at the end of this time. The value 0 is
the default and disables this feature. An account then stays locked
after 3 attempts, and an administrator must unlock it.

### CLI

Enable Two-Factor:

`./lnms config:set twofactor true`


Set throttle-time (in seconds):

`./lnms config:set twofactor_lock 300`

## User Administration

With Two-Factor on, the Settings -> Manage Users grid holds a '2FA'
column. A green tick marks each user with active 2FA.

LibreNMS cannot make 2FA mandatory for a user.

After 3 failed attempts of a user, edit that user in the Manage Users
table. You can then unlock the account or disable 2FA.

A throttle timer unlocks an account at the end of its time. With the
default value of 0, an administrator unlocks the account manually after
3 failed attempts.

A locked account shows a message to the user. The message gives the
throttle time. Without a timer, it asks the user to contact the
administrator.

## End-User Enrolment

These steps assume Two-Factor on the whole system, as in the
Configuration section above.

Each user enables 2FA after a normal login:

- Go to 'My Settings' (/preferences/)
- Choose TwoFactor type
- Click on 'Generate TwoFactor Secret Key'
- If your browser does not reload, reload it manually
- Scan provided QR or click on 'Manual' to see the Key

### Authenticator Apps

- [Google Authenticator](https://support.google.com/accounts/answer/1066447?hl=en).
- [LastPass Authenticator](https://support.logmeininc.com/lastpass/help/lastpass-authenticator-lp030014).
- [Enpass Authenticator](https://support.enpass.io/app/item/generating_one_time_code_in_enpass.htm)).
