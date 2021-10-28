source: API/Bills.md
path: blob/master/doc/

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
- period=previous indicates you would like the data for the last
  complete period rather than the current period

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

### `get_bill_graph`

Retrieve a graph image associated with a bill.

NB: The graphs returned from this will always be png as they do not
come from rrdtool, even if you have SVG set.

Route: `/api/v0/bills/:id/graphs/:graph_type

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/graphs/bits
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/graphs/bits?from=1517443200
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/graphs/bits?from=1517443200&to=1517788800
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/graphs/monthly
```

Output:

Graph Image

### `get_bill_graphdata`

Retrieve the data used to draw a graph so it can be rendered in an external system

Route: `/api/v0/bills/:id/graphdata/:graph_type`

Input:

The `reducefactor` parameter is used to reduce the number of data
points. Billing data has 5 minute granularity, so requesting a graph
for a long time period will result in many data points.  If not
supplied, it will be automatically calculated.  A reducefactor of 1
means return all items, 2 means half of the items etc.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/graphdata/bits
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/graphdata/bits?from=1517443200
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/graphdata/bits?from=1517443200&to=1517788800
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/graphdata/bits?from=1517443200&to=1517788800&reducefactor=5
```

Output:

{
    "status": "ok",
    "graph_data": {
        "from": "1517443200",
        "to": 1518196161,
        "last": "1518195901",
        "in_data": [
            103190525.20999999,
            104949255.81
        ],
        "out_data": [
            1102059.1299999999,
            1079216.46
        ],
        "tot_data": [
            104292584.33999999,
            106028472.27
        ],
        "ticks": [
            "1517750401",
            "1517756101"
        ],
        "rate_95th": "251880417",
        "rate_average": "146575554",
        "bill_type": "cdr",
        "max_in": 9888289942,
        "max_out": 75848756,
        "ave_in": 18029660.242105871,
        "ave_out": 196447.38060137472,
        "last_in": 3790227.9500000002,
        "last_out": 122731.63333333333
    }
}

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
        "bill_peak_out": "2782349290",
        "bill_peak_in": "10161119",
        "pdf": null
    }
 ],
 "count": 1,
}
```

### `get_bill_history_graph`

Retrieve a graph of a previous period of a bill

NB: The graphs returned from this will always be png as they do not
come from rrdtool, even if you have SVG set.

Route: `/api/v0/bills/:id/history/:bill_hist_id/graphs/:graph_type`

Input:

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/history/1/graphs/bits
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/history/1/graphs/hour
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/history/1/graphs/day
```

Output:

(image)

### `get_bill_history_graphdata`

Retrieve the data for a graph of a previous period of a bill, to be
rendered in an external system

Route: `/api/v0/bills/:id/history/:bill_hist_id/graphdata/:graph_type`

Input:

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/history/1/graphdata/bits
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/history/1/graphdata/hour
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills/1/history/1/graphdata/day
```

Output:

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

- If you send an existing bill_id the call replaces all values it
  receives. For example if you send 2 ports it will delete the
  existing ports and add the the 2 new ports. So to add ports you have
  to get the current ports first and add them to your update call.

Input:

Example (create):

```curl
curl -X POST -d '{"ports":[ 1021 ],"bill_name":"NEWBILL","bill_day":"1","bill_type":"quota","bill_quota":"2000000000000","bill_custid":"1337","bill_ref":"reference1","bill_notes":"mynote"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills
```

Example (set):

```curl
curl -X POST -d '{"bill_id":"32","ports":[ 1021 ],"bill_name":"NEWNAME","bill_quota":"1000000000000"}' -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills
```

Output:

```json
{
    "status": "ok",
    "bill_id": 32
}
```

