A LibreNMS user, [Dan](https://twitter.com/thedanbrown), supplied full
details and scripts for a migration from Observium to LibreNMS.

We mirror his scripts with his consent. The scripts are in the
`scripts\Migration` folder of your installation.

# Setup:

The scripts must do these steps:

-   Build the RRD directories on LibreNMS
-   Convert the RRD files on Observium to XML (a move from x86 to x64)
-   Copy the RRD files and the XML files to LibreNMS
-   Convert the XML files back to RRD files
-   Add the device to LibreNMS

# Script:

Two versions of the scripts are available:

- The first version converts the RRD files to XML. It then converts them back to RRD files on the destination server. A move from x86 to x64 needs this version.
- The second version copies the original RRD files with SCP. Use this version when both servers have the same architecture.

You need four files. **Put all four files on both servers. The scripts use `/tmp/` by default**:

-   `nodelist.txt` – the list of the hosts to move. Each name must match the hostname in Observium exactly.
-   `mkdir.sh` – this script creates the necessary directories on your LibreNMS server.
-   `destwork.sh` – this script adds the device to LibreNMS. In one version, it also converts XML back to RRD.
-   `convert.sh` – the main script. It controls the migration.

You can read and modify the scripts. Each file has some variables that you must set for your conversion. If you have a problem, leave a comment.

# Conversion:

This section assumes these conditions:

-   Root access is available on both servers
-   You have SSH access to both servers
-   All four files are in the `/tmp/` directory of both servers

Start with one or two hosts. Then examine the result. In one test, 10 standard devices took about 20 minutes with the RRD to XML conversion. Each environment is different. Start slowly, then increase the level of automation.

### SSH Keys

Exchange SSH keys first. The scripts can then log in without a password. Do these steps on your Observium server:

`ssh-keygen -t rsa`

Accept the defaults. You can enter a passphrase. Then run:

`ssh-copy-id librenms`

Here, `librenms` is the hostname or the IP address of your destination server.

## Nodelist.txt

The `nodelist.txt` file holds the list of the hosts to migrate from Observium. Each name must match the name of the RRD folder on Observium. To get these names, run:

`ls /opt/observium/rrd/`

The `nodelist.txt` file must be on **both the Observium server and the LibreNMS server**. To edit the file, use nano:

`nano /tmp/nodelist.txt`

Replace the dummy data with your hosts. Press CTRL+X, then Y, to save the file. Make the same changes on the LibreNMS server.

## Script Variables

After you configure `nodelist.txt`, set the variables in all three shell scripts. Start with `convert.sh`. Edit the file with nano:

`nano /tmp/convert.sh`

Change the variables for your environment. The list below gives each variable:

-   `DEST` – the IP address or the hostname of your LibreNMS server
-   `L_RRDPATH` – the location of the LibreNMS RRD directory. The default value is the default install location
-   `O_RRDPATH` – the location of the Observium RRD directory. The default value is the default install location
-   `MKDIR` – the location of the `mkdir.sh` script
-   `DESTSCRIPT` – the location of the `destwork.sh` script
-   `NODELIST` – the location of the `nodelist.txt` file

Then edit the `destwork.sh` script:

`nano /tmp/destwork.sh`
