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

innodb_transactions_counter = 0

for row in rows:
    for line in row[2].split("\n"):
        history_list = re.match(r"^History\slist\slength\s(\d+)$", line)
        transaction_id = re.match(r"^---TRANSACTION\s\d+.+$", line)


        if history_list:
            data['a4'] = history_list.group(1)
        elif transaction_id:
            innodb_transactions_counter += 1
    

for key in data:
    if data[key]:
        print key + ":" + data[key]
