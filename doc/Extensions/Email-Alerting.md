Currently, the email alerts needs to be set up in the config. If you want to enable it, paste this in your config and change it:

```php
// Mailer backend Settings
$config['email_backend']              = 'mail';               // Mail backend. Allowed: "mail" (PHP's built-in), "sendmail", "smtp".
$config['email_from']                 = NULL;                 // Mail from. Default: "ProjectName" <projectid@`hostname`>
$config['email_user']                 = $config['project_id'];
$config['email_sendmail_path']        = '/usr/sbin/sendmail'; // The location of the sendmail program.
$config['email_smtp_host']            = 'localhost';          // Outgoing SMTP server name.
$config['email_smtp_port']            = 25;                   // The port to connect.
$config['email_smtp_timeout']         = 10;                   // SMTP connection timeout in seconds.
$config['email_smtp_secure']          = NULL;                 // Enable encryption. Use 'tls' or 'ssl'
$config['email_smtp_auth']            = FALSE;                // Whether or not to use SMTP authentication.
$config['email_smtp_username']        = NULL;                 // SMTP username.
$config['email_smtp_password']        = NULL;                 // Password for SMTP authentication.

// Alerting Settings
$config['alerts']['email']['default']      = 'sendto@somewhere.com';    // Default alert recipient
$config['alerts']['email']['default_only'] = FALSE;   // Only use default recipient
$config['alerts']['email']['enable']       = TRUE;    // Enable email alerts
$config['alerts']['bgp']['whitelist']      = NULL;    // Populate as an array() with ASNs to alert on.
$config['alerts']['port']['ifdown']        = FALSE;   // Generate alerts for ports that go down
```