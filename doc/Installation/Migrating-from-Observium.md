A LibreNMS user, [Dan](https://twitter.com/thedanbrown), has kindly
provided full details and scripts to be able to migrate from Observium
to LibreNMS.

We have mirrored the scripts he's provided with consent, these are
available in the `scripts\Migration` folder of your installation..

# Setup:

First I had to lay out my script requirements:

-   Build the RRD directories on LibreNMS
-   Convert the RRD files on Observium to XML (x86 to x64 move)
-   Copy the RRD/XML files to LibreNMS
-   Convert the XML files back to RRD files
-   Add the device to LibreNMS

# Script:

There are two versions of the scripts available for you to download:
- One converts the RRDs to XML and then back to RRD files when they hit the destination. This is a requirement if you are moving from x86 to x64. 
- Assuming you’re moving servers that are on the same architecture, we can skip that step and just SCP the original RRD files.

For everything to work as originally intended, you’ll need four files. **Put all four files on both servers, the scripts default to /tmp/**:

-   nodelist.txt – this file contains the list of hosts you would like to move. This must match exactly to the hostname Observium uses
-   mkdir.sh – this script creates the necessary directories on your LibreNMS server
-   destwork.sh – depending on the version you choose, this script will add the device to LibreNMS and possibly convert from XML to RRD
-   convert.sh – convert is the main script we’ll be calling. All of the magic happens here.

Feel free to crack open the scripts and modify them to suit you. Each file has a handful of variables you’ll need to set for your conversion. They should be self-explanatory, but please leave a comment if you have trouble. 

# Conversion:

This section assumes the following:

-   Root access is available on both servers
-   You have SSH access to both servers
-   All four files have been placed in the tmp directory of both servers

I would strongly suggest you start with just one or two hosts and see how things work. For me, 10 standard sized devices took about 20 minutes with the RRD to XML conversion. Every environment will be different, so start slow and work your way up to full automation.

### SSH Keys

First thing we will want to do is exchange SSH keys so that we can automate the login process used by the scripts. Perform these steps on your Observium server:

`ssh-keygen -t rsa`

Accept the defaults and enter a passphrase if you wish. Then:

`ssh-copy-id librenms`

Where librenms is the hostname or IP of your destination server.

## Nodelist.txt

The nodelist.txt file contains a list of hosts we want to migrate from Observium. These names must match the name of the RRD folder on Observium. You can get those names by running the following –

`ls /opt/observium/rrd/`

Also important, the nodelist.txt file must be on **both your Observium and LibreNMS server**. Once you have your list, edit nodelist.txt with nano:

`nano /tmp/nodelist.txt`

And replace the dummy data with the hosts you are converting. CTRL+X and then Y to save your modifications. Make the same changes on the LibreNMS server.

## Script Variables

Now that we have nodelist.txt setup correctly, it is time to set the variables in all three shell scripts. We are going to start with convert.sh. Edit it with nano:

`nano /tmp/convert.sh`

and change the variables to suit your environment. Here is a quick list of them:

-   DEST – This should be the IP or hostname of your LibreNMS server
-   L_RRDPATH – This signifies the location of the LibreNMS RRD directory. The default value is the default install location
-   O_RRDPATH – Location of the Observium RRD directory. The default value is the default install location
-   MKDIR – Location of the mkdir.sh script
-   DESTSCRIPT – Location of the destwork.sh script
-   NODELIST – Location of the nodelist.txt file

Next, edit the destwork.sh script:

`nano /tmp/destwork.sh`
