source: Support/Slave-Database.md
path: blob/master/doc/

# Read-only Database configuration

The [Laravel database framework](https://laravel.com/docs/master/database#read-and-write-connections) supports separate
read/write connections.  By default, LibreNMS will utilize the values defined for the master database in the
[Environment Variables](http://docs.librenms.org/Support/Enviroment-Variables.md) for both read and write connections.
If you have a Master/Slave setup for your MySQL database, you can configure the following settings in your .env file to
override the defaults.

```dotenv
DB_READ=$DB_HOST
DB_READ_PORT=$DB_PORT
DB_READ_DATABASE=$DB_DATABASE
DB_READ_USERNAME=$DB_USERNAME
DB_READ_PASSWORD=$DB_PASSWORD
DB_READ_SOCKET=$DB_SOCKET
DB_READ_STICKY=True
```
