source: API/Bills.md

### `list_bills`

Retrieve the list of bills currently in the system.

Route: `/api/v0/bills`
       `/api/v0/bills?period=previous`

Input:

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills?period=previous
```

Output:
```json
{
 "status": "ok",
 "message": "",
 "count": 1,
 "bills": [
  {
   "bill_id": "1",
   "bill_name": "Router bills",
   "bill_type": "cdr",
   "bill_cdr": "10000000",
   "bill_day": "1",
   "bill_quota": "0",
   "rate_95th_in": "0",
   "rate_95th_out": "0",
   "rate_95th": "0",
   "dir_95th": "in",
   "total_data": "0",
   "total_data_in": "0",
   "total_data_out": "0",
   "rate_average_in": "0",
   "rate_average_out": "0",
   "rate_average": "0",
   "bill_last_calc": "2015-07-02 17:01:26",
   "bill_custid": "Router",
   "bill_ref": "Router",
   "bill_notes": "Bill me",
   "bill_autoadded": "0",
   "ports_total": "0",
   "allowed": "10Mbps",
   "used": "0bps",
   "percent": 0,
   "overuse": "-",
   "ports": [
       {
           "device_id": "168",
           "port_id": "35146",
           "ifName": "eth0"
       }
   ]
  }
 ]
}
```

### `get_bill`

Retrieve a specific bill

Route: `/api/v0/bills/:id`
       `/api/v0/bills/:id?period=previous`
       `/api/v0/bills?ref=:ref`
       `/api/v0/bills?ref=:ref&period=previous`
       `/api/v0/bills?custid=:custid`
       `/api/v0/bills?custid=:custid&period=previous`

  - id is the specific bill id
  - ref is the billing reference
  - custid is the customer reference
  - period=previous indicates you would like the data for the last complete period rather than the current period

Input:

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills?ref=:customerref
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills?custid=:custid
```

Output:
```json
{
 "status": "ok",
 "message": "",
 "count": 1,
 "bills": [
  {
   "bill_id": "1",
   "bill_name": "Router bills",
   "bill_type": "cdr",
   "bill_cdr": "10000000",
   "bill_day": "1",
   "bill_quota": "0",
   "rate_95th_in": "0",
   "rate_95th_out": "0",
   "rate_95th": "0",
   "dir_95th": "in",
   "total_data": "0",
   "total_data_in": "0",
   "total_data_out": "0",
   "rate_average_in": "0",
   "rate_average_out": "0",
   "rate_average": "0",
   "bill_last_calc": "2015-07-02 17:01:26",
   "bill_custid": "Router",
   "bill_ref": "Router",
   "bill_notes": "Bill me",
   "bill_autoadded": "0",
   "ports_total": "0",
   "allowed": "10Mbps",
   "used": "0bps",
   "percent": 0,
   "overuse": "-",
   "ports": [
       {
           "device_id": "168",
           "port_id": "35146",
           "ifName": "eth0"
       }
   ]
  }
 ]
}
```

### `get_bill_history`

Retrieve the history of specific bill

Route: `/api/v0/bills/:id/history`

Input:

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/history
```

Output:
```json
{
 "status": "ok",
 "bill_history": [
    {
        "bill_hist_id": "1",
        "bill_id": "1",
        "updated": "2018-02-06 17:01:01",
        "bill_datefrom": "2018-02-01 00:00:00",
        "bill_dateto": "2018-02-28 23:59:59",
        "bill_type": "CDR",
        "bill_allowed": "100000000",
        "bill_used": "229963765",
        "bill_overuse": "129963765",
        "bill_percent": "229.96",
        "rate_95th_in": "229963765",
        "rate_95th_out": "1891344",
        "rate_95th": "229963765",
        "dir_95th": "in",
        "rate_average": "136527101",
        "rate_average_in": "135123359",
        "rate_average_out": "1403743",
        "traf_in": "3235123452544",
        "traf_out": "33608406566",
        "traf_total": "3268731859110",
        "pdf": null
    }
 ],
 "count": 1,
}
```

### `delete_bill`

Delete a specific bill and all dependent data

Route: `/api/v0/bills/:id`

- id is the specific bill id

Input:

Example:
```curl
curl -X DELETE -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1
```

Output:
```json
{
    "status": "ok",
    "message": "Bill has been removed"
}
```

### `create_edit_bill`

Creates a new bill or updates an existing one

Route: `/api/v0/bills`

Method: `POST`

- If you send an existing bill_id the call replaces all values it receives.  
  For example if you send 2 ports it will delete the existing ports and add the the 2 new ports.  
  So to add ports you have to get the current ports first and add them to your update call.

Input:

Example (create):
```curl
curl -X POST -d '{"ports":["1021"],"bill_name":"NEWBILL","bill_day":"1","bill_type":"quota","bill_quota":"2000000000000","bill_custid":"1337","bill_ref":"reference1","bill_notes":"mynote"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills
```

Example (set):
```curl
curl -X POST -d '{"bill_id":"32","ports":["1021"],"bill_name":"NEWNAME","bill_quota":"1000000000000"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills
```

Output:
```json
{
    "status": "ok",
    "bill_id": 32
}
```