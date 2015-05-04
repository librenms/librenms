#!/usr/bin/env python
import warnings
import re
warnings.filterwarnings(action="ignore", message='the sets module is deprecated')
import sets
import MySQLdb
import base64
conn = MySQLdb.connect(host='localhost',
            user='root',
            passwd='root',
            db='')

cursor = ""
cursor = conn.cursor()
cursor.execute("SHOW ENGINE INNODB STATUS")
rows = cursor.fetchall()

data = {}

currently_counting_transactions = 0
current_transactions = 0

for row in rows:
    for line in row[2].split("\n"):
        history_list = re.match(r"^History\slist\slength\s(\d+)$", line)
        trx_id_counter = re.match(r"^Trx\sid\scounter\s(\d+)$", line)
        transaction_id = re.match(r"^---TRANSACTION\s\d+.+$", line)
        section_header = re.match(r"^--------$", line)

        if (currently_counting_transactions and section_header):
            currently_counting_transactions = 0
            data['a7'] = current_transactions
        elif (currently_counting_transactions and transaction_id):
            current_transactions += 1
        elif history_list:
            data['a4'] = history_list.group(1)
        elif trx_id_counter:
            currently_counting_transactions = 1
    

for key in data:
    if data[key]:
        print key + ":", data[key]
