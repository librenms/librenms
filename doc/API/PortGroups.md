### `get_graph_by_portgroup`

Get the graph based on the group type.

Route: `/api/v0/portgroups/:group`

- group is the type of port group graph you want, I.e Transit,
  Peering, and more. You can give several types in a comma separated list.

Input:

- from: the start date of the graph. See
  <http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html> for more information.
- to: the end date of the graph. See
  <http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html> for more information.
- width: The graph width, defaults to 1075.
- height: The graph height, defaults to 300.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/portgroups/transit,peering
```

Output:

Output is an image.

### `get_graph_by_portgroup_multiport_bits`

Get the graph based on the multiple port id separated by commas `,`.

Route: `/api/v0/portgroups/multiport/bits/:id`

- id is a comma separated list of port ids you want, I.e 1,2,3,4,
  and more. You can give several IDs in a comma separated list.

Input:

- from: the start date of the graph. See
  <http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html> for more information.
- to: the end date of the graph. See
  <http://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html> for more information.
- width: The graph width, defaults to 1075.
- height: The graph height, defaults to 300.

Example:

```curl
curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://foo.example/api/v0/portgroups/multiport/bits/1,2,3
```

Output:

Output is an image.
