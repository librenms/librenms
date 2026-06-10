

# Wireguard

The Wireguard application polls the Wireguard service and scrapes all client statistics for all interfaces configured as Wireguard interfaces.

## SNMP Extend

1. Copy the python script, wireguard.py, to the desired host

    ```bash
    wget https://github.com/librenms/librenms-agent/raw/master/snmp/wireguard.pl -O /etc/snmp/wireguard.pl
    ```

2. Install the depends.

=== "Debian/Ubuntu"

    ```bash
    apt-get install libjson-perl libmime-base64-perl libfile-slurp-perl
    ```

=== "FreeBSD"

    ```bash
    pkg install p5-JSON p5-File-Slurp p5-MIME-Base64
    ```

=== "Generic"

    ```bash
    cpanm JSON MIME::Base64 File::Slurp
    ```


3. Make the script executable

    ```bash
    chmod +x /etc/snmp/wireguard.pl
    ```

4. Edit your snmpd.conf file and add:

    ```bash
    extend wireguard /etc/snmp/wireguard.pl
    ```

5. Create the optional config file,
   `/usr/local/etc/wireguard_extend.json`.

| key                          | default     | description                                                 |
|------------------------------|-------------|-------------------------------------------------------------|
| include_pubkey               | 0           | Include the pubkey with the return.                         |
| use_short_hostname           | 1           | If the hostname should be shortened to just the first part. |
| public_key_to_arbitrary_name | {}          | A hash of pubkeys to name mappings.                         |
| pubkey_resolvers             | <see below> | Resolvers to use for the pubkeys.                           |

The default for `pubkey_resolvers` is
`config,endpoint_if_first_allowed_is_subnet_use_hosts,endpoint_if_first_allowed_is_subnet_use_ip,first_allowed_use_hosts,first_allowed_use_ip`.

| resolver                                       | description                                                                                          |
|------------------------------------------------|------------------------------------------------------------------------------------------------------|
| config                                         | Use the mappings from `.public_key_to_arbitrary_name` .                                              |
| endpoint_if_first_allowed_is_subnet_use_hosts  | If the first allowed IP is a subnet, see if a matching IP can be found in hosts for the endpoint.    |
| endpoint_if_first_allowed_is_subnet_use_getent | If the first allowed IP is a subnet, see if a hit can be found for the endpoint IP via getent hosts. |
| endpoint_if_first_allowed_is_subnet_use_ip     | If the first allowed IP is a subnet, use the endpoint IP for the name.                               |
| first_allowed_use_hosts                        | See if a match can be found in hosts for the first allowed IP.                                       |
| first_allowed_use_getent                       | Use getent hosts to see try to fetch a match for the first allowed IP.                               |
| first_allowed_use_ip                           | Use the first allowed IP as the name.                                                                |


6. Restart snmpd.

    ```bash
    sudo systemctl restart snmpd
    ```