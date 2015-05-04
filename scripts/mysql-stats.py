#!/usr/bin/env python
import warnings
import re
warnings.filterwarnings(action="ignore", message='the sets module is deprecated')
import sets
import MySQLdb
import base64
conn = MySQLdb.connect(host='',
			user='',
			passwd='',
			db='')

cursor = conn.cursor ()


cursor.execute ("SHOW GLOBAL STATUS")
rows = cursor.fetchall()

data = {}

currently_counting_transactions = 0
current_transactions = 0
locked_transactions = 0
active_transactions = 0
not_started = 0


cursor = ""
cursor = conn.cursor()
cursor.execute("SHOW ENGINE INNODB STATUS")
rows = cursor.fetchall()

for row in rows:
    for line in row[2].split("\n"):
        history_list = re.match(r"^History\slist\slength\s(\d+)$", line)
        trx_id_counter = re.match(r"^Trx id counter \d+$", line)
        section_header = re.match(r"^--------$", line)
        not_started = re.match(r"^---TRANSACTION \d+, not started$", line)
        active = re.match(r"^---TRANSACTION \d+, ACTIVE \d+ sec inserting$", line)
        lock_wait = re.match(r"^---TRANSACTION \d+, LOCK WAIT (\d+) lock struct(s), heap size \d+, (\d+) row lock(s), undo log entries (\d+)$", line)

        if (currently_counting_transactions):
            if (section_header):
                currently_counting_transactions = 0
                data['a7'] = current_transactions
                data['a8'] = locked_transactions
                data['a9'] = active_transactions
            elif (lock_wait):
                locked_transactions += 1
            elif (not_started):
                current_transactions += 1
            elif (active):
                active_transactions += 1
    
        if history_list:
            data['a4'] = history_list.group(1)
        elif trx_id_counter:
            currently_counting_transactions = 1


for key in data:
    print key + ":", data[key]
