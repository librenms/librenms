### `list_locations`

Return a list of locations.

Route: `/api/v0/resources/locations`

Input:

-

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/resources/locations
```

Output:

```json
{
    "status": "ok",
    "locations": [
        {
            "id": "1",
            "location": "Example location, Example city, Example Country",
            "lat": "-18.911436",
            "lng": "47.517446",
            "timestamp": "2017-04-01 02:40:05"
        },
        ...
    ],
    "count": 100
}
```

### `add_location`

Add a new location

Route: `/api/v0/locations/`

Input:

- location: name of the new location
- lat: latitude
- lng: longitude
- fixed_coordinates: 0 if updated from the device or 1 if the coordinate is fixed (default is fixed if lat and lng are valid)

Example:

```curl
curl -X POST -d '{"location":"Google", "lat":"37.4220041","lng":"-122.0862462"}' -H 'X-Auth-Token:YOUR-API-TOKEN' https://librenms.org/api/v0/locations
```

Output:

```json
{
    "status": "ok",
    "message": "Location added with id #45"
}
```

### `delete_location`

Deletes an existing location

Route: `/api/v0/locations/:location`

- location: name or id of the location to delete

Example:

```curl
curl -X DELETE -H 'X-Auth-Token:YOUR-API-TOKEN' https://librenms.org/api/v0/locations/Google
```

Output:

```json
{
    "status": "ok",
    "message": "Location Google has been deleted successfully"

}
```

### `edit_location`

Edits a location

Route: `/api/v0/locations/:location`

- location: name or id of the location to edit

Input:

- lat: latitude
- lng: longitude

Example:

```curl
curl -X PATCH -d '{"lng":"100.0862462"}' -H 'X-Auth-Token:YOUR-API-TOKEN' https://librenms.org/api/v0/locations/Google
```

Output:

```json
{
    "status": "ok",
    "message": "Location updated successfully"
}
```

###`get_location`

Gets a specific location

Route: `/api/v0/location/:location`

- location: name or id of the location to get

Output:

```json
{
    "status": "ok",
    "get_location": [
        {
            "id": 1,
            "location": "TEST",
            "lat": 00.000000,
            "lng": 00.000000,
            "timestamp": "2023-01-01 00:00:00",
            "fixed_coordinates": 1
        }
    ],
    "count": 1
}
```
