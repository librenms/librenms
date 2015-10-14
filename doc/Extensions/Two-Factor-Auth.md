Table of Content:
- [About](#about)
- [Types](#types)
 - [Timebased One-Time-Password (TOTP)](#totp)
 - [Counterbased One-Time-Password (HOTP)](#hotp)
- [Configuration](#config)
- [Usage](#usage)
 - [Google Authenticator](#usage-google)

# <a name="about">About</a>

Over the last couple of years, the primary attack vector for internet accounts has been static passwords.  
Therefore static passwords are no longer suffient to protect unauthorized access to accounts.  
Two Factor Authentication adds a variable part in authentication procedures.  
A user is now required to supply a changing 6-digit passcode in addition to it's password to obtain access to the account.

LibreNMS has a RFC4226 conform implementation of both Time and Counter based One-Time-Passwords.  
It also allows the administrator to configure a throttle time to enforce after 3 failures exceeded. Unlike RFC4226 suggestions, this throttle time will not stack on the amount of failures.

# <a name="types">Types</a>

In general, these two types do not differ in algorithmic terms.  
The types only differ in the variable being used to derive the passcodes from.  
The underlying HMAC-SHA1 remains the same for both types, security advantages or disadvantages of each are discussed further down.

## <a name="totp">Timebased One-Time-Password (TOTP)</a>

Like the name suggests, this type uses the current Time or a subset of it to generate the passcodes.  
These passcodes solely rely on the secrecy of their Secretkey in order to provide passcodes.  
An attacker only needs to guess that Secretkey and the other variable part is any given time, presumably the time upon login.  
RFC4226 suggests a resynchronization attempt in case the passcode mismatches, providing the attacker a range of upto +/- 3 Minutes to create passcodes.


## <a name="hotp">Counterbased One-Time-Password (TOTP)</a>

This type uses an internal counter that needs to be in-synch with the server's counter to successfully authenticate the passcodes.  
The main advantage over timebased OTP is the attacker doesnt only need to know the Secretkey but also the server's Counter in order to create valid passcodes.  
RFC4226 suggests a resynchronization attempt in case the passcode mismatches, providing the attacker a range of upto +4 increments from the actual counter to create passcodes.

# <a name="config">Configuration</a>

Enable Two-Factor:
```php
$config['twofactor'] = true;
```

Set throttle-time (in secconds):
```php
$config['twofactor_lock'] = 300;
```

# <a name="usage">Usage</a>

These steps imply that TwoFactor has been enabled in your `config.php`

Create a Two-Factor key:
- Go to 'My Settings' (/preferences/)
- Choose TwoFactor type
- Click on 'Generate TwoFactor Secret Key'
- If your browser didnt reload, reload manually
- Scan provided QR or click on 'Manual' to see the Key

## <a name="usage-google">Google Authenticator</a>

Installation guides for Google Authneticator can be found [here](https://support.google.com/accounts/answer/1066447?hl=en).

Usage:
- Create a key like described above
- Scan provided QR or click on 'Manual' and type down the Secret
- On next login, enter the passcode that the App provides

