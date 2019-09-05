source: Support/Slave-Database.md
path: blob/master/doc/

# Slave Database configuration

The Laravel database framework supports separate read/write connections.  By default, LibreNMS will utilize the values
defined for the master database in the [Environment Variables](http://docs.librenms.org/Support/Slave-Database.md) for
both read and write connections.  If you have a Master/Slave setup for your MySQL database, you can configure the
following settings in your .env file to override the defaults.  The `DB_SLAVE_STICKY`

```dotenv
DB_SLAVE=$DB_HOST
DB_SLAVE_PORT=$DB_PORT
DB_SLAVE_DATABASE=$DB_DATABASE
DB_SLAVE_USERNAME=$DB_USERNAME
DB_SLAVE_PASSWORD=$DB_PASSWORD
DB_SLAVE_SOCKET=$DB_SOCKET
DB_SLAVE_STICKY=False
```
