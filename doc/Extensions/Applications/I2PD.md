# I2PD

This is simple Python3-based SNMP extend script for monitoring I2PD routing daemon using LibreNMS. Script provides very basic metrics for example tunnel success rate, network status and throughput monitoring.

Script communicates with I2PD via I2PControl protocol. Enable and configure that first.

## Enabling I2PControl

1. Edit i2pd.conf (usually at `/etc/i2pd/i2pd.conf`) and find `[i2pcontrol]` section

2. Set `enabled = true`

3. Make sure that I2PC listens only on localhost! Config should look like this:

   ```ini
   [i2pcontrol]
   enabled = true
   address = 127.0.0.1
   port = 7650
   password = itoopie
   ```

   Its recommended to change I2PC password! If you change it, remember to change it in extend script too.

4. Reload i2pd with `systemctl restart i2pd.service`

## Enabling SNMP extend

1. Download script onto host running i2pd and snmpd

   ```
   wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/i2pd-stats.py -O /etc/snmp/i2pd-stats.py 
   ```

2. Make script executable

   ```
   chmod +x /etc/snmp/i2pd-stats.py
   ```

3. Edit snmpd.conf (usually at `/etc/snmp/snmpd.conf`) and add:

   ```
   extend i2pd /etc/snmp/i2pd-stats.py
   ```

4. Edit `/etc/snmp/i2pd-stats.py` and make sure that config values **I2PC_URL** and **I2PC_PASS** matches your values set in i2pd.conf

5. Try to execute script and see if it works

6. At last remember to restart snmpd

   ```
   systemctl restart snmpd.service
   ```

### Script requirements

You should already have everything needed, but:

- python3
- python3-urllib3

Install with `apt install` or your distros equivalent

## Error messages

- `ERROR(1): Invalid I2PControl password or token!`

Your i2pd daemon is configured with different I2PControl password than script. Check your I2PControl password from i2pd.conf and update value **I2PC_PASS** in `i2pd-stats.py`.

- `ERROR(2): Unable to connect I2PControl socket!`

I2PControl protocol is not enabled in i2pd daemon, or **I2PC_URL** is incorrect in `i2pd-stats.py`. 

- `ERROR(3): Connection timed out to I2PControl socket!`

I2PControl protocol is not enabled in i2pd daemon, or **I2PC_URL** is incorrect in `i2pd-stats.py`.  Your i2pd daemon may be stuck, try to restart `i2pd.service`.

## Alerting

There is few basic but extremely useful alert rules included in rule collection.

Go to *LibreNMS -> Alerts -> Alert rules* and click **Create rule from collection**

Write `I2PD` into search box to find and apply those rules. Rules must be manually applied!
