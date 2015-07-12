#! /usr/bin/env python
"""
 ping-wrapper   A small tool which pings hosts and reassings them to poller groups.

 Usage:         This program accepts one command line argument: the number of threads
                that should run simultaneously. If no argument is given it will assume
                a default of 16 threads.
		
		Reassigns device to the device to the poller with best ping.
		Ohterwise leaves group unchanged.
		Assumes poller group names correspond to poller names.
"""
try:

    import json
    import os
    import subprocess
    import sys
    import time

except:
    print "ERROR: missing one or more of the following python modules:"
    print "sys, subprocess, time, os, json"
    sys.exit(2)

try:
    import MySQLdb
except:
    print "ERROR: missing the mysql python module:"
    print "On ubuntu: apt-get install python-mysqldb"
    print "On FreeBSD: cd /usr/ports/*/py-MySQLdb && make install clean"
    sys.exit(2)

"""
    Fetch configuration details from the config_to_json.php script
"""

ob_install_dir = os.path.dirname(os.path.realpath(__file__))
config_file = ob_install_dir + '/config.php'


def get_config_data():
    config_cmd = ['/usr/bin/env', 'php', '%s/config_to_json.php' % ob_install_dir]
    try:
        proc = subprocess.Popen(config_cmd, stdout=subprocess.PIPE, stdin=subprocess.PIPE)
    except:
        print "ERROR: Could not execute: %s" % config_cmd
        sys.exit(2)
    return proc.communicate()[0]

try:
    with open(config_file) as f:
        pass
except IOError as e:
    print "ERROR: Oh dear... %s does not seem readable" % config_file
    sys.exit(2)

try:
    config = json.loads(get_config_data())
except:
    print "ERROR: Could not load or parse configuration, are PATHs correct?"
    sys.exit(2)

fping_path = config['fping']
db_username = config['db_user']
db_password = config['db_pass']
poller_name = config['distributed_poller_name']

if config['db_host'][:5].lower() == 'unix:':
	db_server = config['db_host']
	db_port = 0
elif ':' in config['db_host']:
	db_server = config['db_host'].rsplit(':')[0]
	db_port = int(config['db_host'].rsplit(':')[1])
else:
	db_server = config['db_host']
	db_port =0

db_dbname = config['db_name']


if 'distributed_poller_group' in config:
    poller_group = str(config['distributed_poller_group'])
else:
    print "ERROR: This is only useful when using distributed polling" 
    sys.exit(2)

s_time = time.time()
real_duration = 0
per_device_duration = {}
pinged_devices = 0

devices_list = []

try:
    if db_port == 0:
        db = MySQLdb.connect(host=db_server, user=db_username, passwd=db_password, db=db_dbname)
    else:
        db = MySQLdb.connect(host=db_server, port=db_port, user=db_username, passwd=db_password, db=db_dbname)
    cursor = db.cursor()
except:
    print "ERROR: Could not connect to MySQL database!"
    sys.exit(2)

"""
    This query specificly orders the results depending on the last_polled_timetaken variable
    Because this way, we put the devices likely to be slow, in the top of the queue
    thus greatening our chances of completing _all_ the work in exactly the time it takes to
    poll the slowest device! cool stuff he
"""
# (c) 2015, GPLv3, Daniel Preussker <f0o@devilcode.org> <<<EOC2
query = "select device_id, hostname from devices where disabled = 0 order by last_polled_timetaken"
# EOC2

cursor.execute(query)
devices = cursor.fetchall()
for row in devices:
    devices_list.append((int(row[0]),row[1])) #device_id, hostname

#Fetch poller_id for current poller
query ="select id from librenms.pollers where poller_name='%s'" % (poller_name)
cursor.execute(query)
row = cursor.fetchone()
if row is None:
    print "ERROR: Poller not listed in database (yet).  Rerun once poller is listed."
    sys.exit(2)
poller_id=int(row[0])

#Fetch master_poller_id
query ="select min(id) from librenms.pollers" 
cursor.execute(query)
row = cursor.fetchone()
if row is None:
    print "ERROR: No pollers listed in database (yet).  Rerun once pollers are listed."
    sys.exit(2)
master_poller_id=int(row[0])

def runcommand(command):
    try:
        proc = subprocess.Popen(command, stdout=subprocess.PIPE, stdin=subprocess.PIPE,stderr=subprocess.PIPE)
    except:
        print "ERROR: Could not execute: %s" % command[0]
        sys.exit(2)
    return proc.communicate()[1]



print "INFO: starting the ping at %s with fping." % (time.strftime("%Y-%m-%d %H:%M:%S"))

#Build command
command=[fping_path,'-C','5','-q']
for device in devices_list:
    device_id, hostname = device
    command.append(hostname)

#Run command
output=runcommand(command).split('\n')

#Analyse output and build update query.
values=""
for device in devices_list:
    device_id, hostname = device
    for line in output:
        phostname = line.split(":")[0].strip()
        if hostname == phostname:
            pings = line.split(":")[1].split()
            total_pings = float(0.0)
            n_pings = int(0)
            for p in pings:
                n_pings += 1
                if p == '-':
                    total_pings += float(1000)
                else:
                    total_pings += float(p)
            av_ping = float(total_pings / n_pings)
            if av_ping<float(1000):
                pinged_devices += 1
                print "INFO: Av. ping for %s : %s" % (hostname,str(av_ping))
                if values != "":
                    values += ","
                values += "(%s, %s, %s)" % (poller_id, device_id, av_ping) 
            

query = "delete from poller_pings where poller_id=%s" % (poller_id)
cursor.execute(query)
db.commit()

query = "insert into poller_pings (poller_id, device_id, ping) VALUES " + values
cursor.execute(query)
db.commit()

if poller_id == master_poller_id:
    print "INFO: I am master, I neeed to reassign devices but only after waiting 60 seconds"
    time.sleep(60) #wait for other pings to finish
    query =  """
    update devices
    inner join 
        (select 
            poller_pings.device_id, poller_groups.id as group_id
        from 
            poller_pings 
            
            inner join 
            (select 
                device_id, min(ping) as ping
            from 
                poller_pings 
            group by
                device_id) as best 
                on poller_pings.device_id=best.device_id 
                    and poller_pings.ping=best.ping
            inner join pollers on poller_pings.poller_id=pollers.id
            inner join poller_groups on pollers.poller_name=poller_groups.group_name
        ) as newgroup on devices.device_id=newgroup.device_id
    set poller_group=newgroup.group_id;"""
    cursor.execute(query)
    db.commit()
total_time = int(time.time() - s_time)

print "INFO: ping-wrapper pinged %s devices in %s seconds" % (pinged_devices, total_time)


show_stopper = False

if total_time > 300:
    print "WARNING: the process took more than 5 minutes to finish, you need faster hardware or more threads"
    sys.exit(2)
