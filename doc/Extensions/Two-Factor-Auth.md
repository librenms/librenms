source: Extensions/Two-Factor-Auth.md
path: blob/master/doc/

# About

Over the last couple of years, the primary attack vector for internet
accounts has been static passwords. Therefore static passwords are no
longer sufficient to protect unauthorized access to accounts. Two
Factor Authentication adds a variable part in authentication
procedures. A user is now required to supply a changing 6-digit
passcode in addition to it's password to obtain access to the account.

LibreNMS has a RFC4226 conform implementation of both Time and Counter
based One-Time-Passwords. It also allows the administrator to
configure a throttle time to enforce after 3 failures exceeded. Unlike
RFC4226 suggestions, this throttle time will not stack on the amount of failures.

# Types

In general, these two types do not differ in algorithmic terms.
The types only differ in the variable being used to derive the passcodes from.
The underlying HMAC-SHA1 remains the same for both types, security
advantages or disadvantages of each are discussed further down.

## Timebased One-Time-Password (TOTP)

Like the name suggests, this type uses the current Time or a subset of
it to generate the passcodes. These passcodes solely rely on the
secrecy of their Secretkey in order to provide passcodes. An attacker
only needs to guess that Secretkey and the other variable part is any
given time, presumably the time upon login. RFC4226 suggests a
resynchronization attempt in case the passcode mismatches, providing
the attacker a range of up to +/- 3 Minutes to create passcodes.

## Counterbased One-Time-Password (TOTP)

This type uses an internal counter that needs to be in sync with the
server's counter to successfully authenticate the passcodes. The main
advantage over timebased OTP is the attacker doesn't only need to know
the Secretkey but also the server's Counter in order to create valid
passcodes. RFC4226 suggests a resynchronization attempt in case the
passcode mismatches, providing the attacker a range of up to +4
increments from the actual counter to create passcodes.

# Configuration

Enable Two-Factor:

```php
$config['twofactor'] = true;
```

Set throttle-time (in seconds):

```php
$config['twofactor_lock'] = 300;
```

# Usage

These steps imply that TwoFactor has been enabled in your `config.php`

Create a Two-Factor key:

- Go to 'My Settings' (/preferences/)
- Choose TwoFactor type
- Click on 'Generate TwoFactor Secret Key'
- If your browser didn't reload, reload manually
- Scan provided QR or click on 'Manual' to see the Key

## Google Authenticator

Installation guides for Google Authenticator can be found [here](https://support.google.com/accounts/answer/1066447?hl=en).

Usage:

- Create a key like described above
- Scan provided QR or click on 'Manual' and type down the Secret
- On next login, enter the passcode that the App provides
