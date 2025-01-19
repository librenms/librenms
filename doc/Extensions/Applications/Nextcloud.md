# Nextcloud

### Install prereqs

=== "Debian/Ubuntu"

    ```bash
    apt-get install libjson-perl libfile-slurp-perl libmime-base64-perl cpanminus
    cpanm Time::Piece
    ```

=== "CentOS/RedHat" 

    ```bash
    yum install perl-JSON perl-File-Slurp perl-MIME-Base64 perl-String-ShellQuote perl-Time-Piece
    ```

=== "FreeBSD"   

    ```bash
    pkg install p5-JSON p5-File-Slurp p5-MIME-Base64 p5-Time-Piece   
    ```

=== "Generic"

    ```bash
    cpanm JSON File::Slurp MIME::Base64 String::ShellQuote Time::Piece
    ```

### SNMP Extend

1. Copy the shell script to the desired host.

    ```bash
    wget https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/nextcloud -O /etc/snmp/nextcloud
    ```

2. Make the script executable

    ```bash
    chmod +x /etc/snmp/nextcloud
    ```


4. Create the cache dir and chown it to the user Nextcloud is running
   as.

    ```bash
    mkdir /var/cache/nextcloud_extend
    chown -R $nextcloud_user /var/cache/nextcloud_extend
    ```

5. Set it up in the crontab for the Nextcloud user using `-i` to point
   it to the Nextcloud install dir.

    ```
    */5 * * * * /etc/snmpd/nextcloud -q -i $install_dir
    ```

6. Add it to snmpd.conf

    ```
    extend nextcloud /bin/cat /var/cache/nextcloud_extend/snmp
    ```

Then just wait for it to be rediscovered.