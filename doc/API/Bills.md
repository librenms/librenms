source: API/Bills.md

### `list_bills`

Retrieve the list of bills currently in the system.

Route: `/api/v0/bills`

Input:

Example:
```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/bills
```

Output:
```json
{
 "status": "ok",
 "err-msg": "",
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
       `/api/v0/bills?ref=:ref`
       `/api/v0/bills?custid=:custid`

  - id is the specific bill id
  - ref is the billing reference
  - custid is the customer reference

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
 "err-msg": "",
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
