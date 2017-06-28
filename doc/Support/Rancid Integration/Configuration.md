source: Support/Rancid Integration/Rancid Integration.md
The options shown below also contains the default values.

NOTE - This is Only for Ubuntu 16.04 at this time, and may not work on other distros!

Install Rancid/Subversion:
sudo apt install rancid subversion


Edit Rancid config file to use subversion instead of default cvs, and adds a group:
sudo nano /etc/rancid/rancid.conf
add :
LIST_OF_GROUPS="librenms"

and change these two lines:
CVSROOT=$BASEDIR/CVS; export CVSROOT
RCSSYS=cvs; export RCSSYS
to:
CVSROOT=$BASEDIR/SVN; export CVSROOT
RCSSYS=svn; export RCSSYS

save and exit
****NOTE - This only creates 1 group! You can of course make more when you get the hang of it, this is just a basic 'Need it to work" deal.


sudo su -c /var/lib/rancid/bin/rancid-cvs -s /bin/bash -l rancid
NOTE - do NOT change cvs to svn here! Leave command as is!

Get a list of devices from Librenms you can pull configs from:
cd /opt/librenms/scripts
sudo ./gen_rancid.php

copy the output. replace all ":" with ";" example:
alphcr1:cisco:up will change to:
alphcr1;cisco;up
copy and past results into the below file: 
sudo nano /var/lib/rancid/librenms/router.db

save and exit
NOTE - This ONLY applies to newer RANCID versions and LInux distros. Older versions will need to retain the : and not the ;

Create/edit rancids login file:
sudo nano /var/lib/rancid/.cloginrc
### add following at minimum:
add user * <your username here>
add password * <your password here>
add method * ssh
add noenable * {1}                         ******This disables the enable when using radius etc *******

save and exit

Grand permissions for rancid:
sudo chown rancid /var/lib/rancid/.cloginrc
sudo chmod 600 /var/lib/rancid/.cloginrc
^^^^^
NOTE - I am still somewhat of a 'noob' here. If this in anyway makes something unsecure, please comment and let me know!

Test config:
sudo /usr/lib/rancid/bin/clogin -f /var/lib/rancid/.cloginrc <device hostname>

exit the device you logged into.

NOTE: IF you run into a 'diffie-hellmen' kind of error, then it is because your Linux distro is using newer encyryprtion methods etc. This is basically just letting you know that the device you tested on is running an outdated encryption type. I recommend updating downstream device if able. If not, the following should fix:
sudo nano /etc/ssh/ssh_config
add
KexAlgorithms diffie-hellman-group1-sha1
save and exit

try logging into your device again

Upon success, run rancid:
sudo su -c /var/lib/rancid/bin/rancid-run -s /bin/bash -l rancid

Ensure your configs pulled:
sudo su - rancid
cd librenms/configs/

ls

Make sure your config files are there :-)

exit rancid

sudo usermod -a -G rancid www-data

cd /opt/librenms/

Add Rancid into Librenms config:
sudo nano config.php
add:
### Rancid
$config['rancid_configs'][]             = '/var/lib/rancid/librenms/configs/';
$config['rancid_ignorecomments']        = 0;

save and exit

restart apache
sudo /etc/init.d/apache2 restart

done!!!!!!!!!
