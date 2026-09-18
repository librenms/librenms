# Vis JS Configuration

The [Network Maps](Network-Map.md) and [Dependency Maps](Dependency-Map.md) have configuration options for
the vis.js library, which affects the way the maps are rendered as well
as the way that users can interact with the maps. This configuration can
be adjusted by following the instructions below.

[This link](https://visjs.github.io/vis-network/docs/network/)
show you all the options and explain what they do.

!!! warning

    Setting a raw JSON value with `lnms config:set` requires escaping twice:
    once for LibreNMS' own CLI argument parsing, and once for your shell's
    quoting rules. The two layers interact, so nested quotes are easy to
    mangle. This is currently the only config path that requires raw JSON
    input, and it is unlikely to be fixed at the CLI level.

example config with working encoding

```bash
lnms config:set network_map_vis_options '"{\"nodes\":{\"shape\":\"box\",\"margin\":8,\"font\":{\"size\":20,\"face\":\"Segoe UI\",\"color\":\"#000000\",\"strokeWidth\":1,\"strokeColor\":\"#ffffff\"},\"shadow\":true},\"edges\":{\"width\":2,\"font\":{\"size\":14,\"align\":\"bottom\",\"strokeWidth\":4,\"strokeColor\":\"#1f2937\",\"color\":\"#ffffff\"},\"smooth\":{\"enabled\":true,\"type\":\"dynamic\",\"roundness\":0.3}},\"physics\":{\"enabled\":true,\"solver\":\"forceAtlas2Based\",\"forceAtlas2Based\":{\"gravitationalConstant\":-200,\"centralGravity\":0.01,\"springLength\":250,\"springConstant\":0.04,\"damping\":0.90,\"avoidOverlap\":1},\"stabilization\":{\"enabled\":true,\"iterations\":1000}},\"layout\":{\"improvedLayout\":true}}"'
```

The commands to run to use the defaults is as follows:

```bash
lnms config:set network_map_vis_options '{
  layout:{
      randomSeed:2
  },
  "edges": {
    arrows: {
          to: {enabled: true, scaleFactor:0.5},
    },
    "smooth": {
        enabled: false
    },
    font: {
        size: 14,
        color: "red",
        face: "sans",
        background: "white",
        strokeWidth:3,
        align: "middle",
        strokeWidth: 2
    }
  },
  "physics": {
     "barnesHut": {
      "gravitationalConstant": -2000,
      "centralGravity": 0.3,
      "springLength": 200,
      "springConstant": 0.04,
      "damping": 0.09,
      "avoidOverlap": 1
    },
     "forceAtlas2Based": {
      "gravitationalConstant": -50,
      "centralGravity": 0.01,
      "springLength": 200,
      "springConstant": 0.08,
      "damping": 0.4,
      "avoidOverlap": 1
    },
     "repulsion": {
      "centralGravity": 0.2,
      "springLength": 250,
      "springConstant": 0.2,
      "nodeDistance": 200,
      "damping": 0.07
    },
     "hierarchicalRepulsion": {
      "nodeDistance": 300,
      "centralGravity": 0.2,
      "springLength": 300,
      "springConstant": 0.2,
      "damping": 0.07
    },
  "maxVelocity": 50,
  "minVelocity": 0.4,
  "solver": "hierarchicalRepulsion",
  "stabilization": {
    "enabled": true,
    "iterations": 1000,
    "updateInterval": 100,
    "onlyDynamicEdges": false,
    "fit": true
  },
  "timestep": 0.4,
 }
}'
```

An example to override the device dependency map to use a hierarchical layout is below.
Note that you can choose to enter the JSON config on one line if you want.

```bash
lnms config:set network_map_dependencymap_vis_options '"{\"layout\":{\"hierarchical\":{\"enabled\":true,\"direction\":\"UD\",\"sortMethod\":\"directed\",\"nodeSpacing\":50,\"treeSpacing\":50,\"levelSeparation\":300}},\"edges\":{\"arrows\":{\"to\":{\"enabled\":true,\"scaleFactor\":0.5}},\"smooth\":{\"enabled\":false},\"font\":{\"size\":14,\"color\":\"red\",\"face\":\"sans\",\"background\":\"white\",\"strokeWidth\":2,\"align\":\"middle\"}},\"physics\":{\"enabled\":false}}"'
```

### Configurator Output

You can also open the dynamic configuration interface. [Example
here](https://visjs.github.io/vis-network/examples/network/other/configuration.html)
from within LibreNMS by adding the following to config.php

After you set your map appearance, click the generate
options button at the bottom to be given the necessary parameters to
set in the `lnms` command. Note: the configurator gives the
configuration with `const options`. Remove this text.

```bash
lnms config:set network_map_vis_options '{
  "nodes": {
    "color": {
      "background": "rgba(20,252,18,1)"
    },
    "font": {
      "face": "tahoma"
    },
    "physics": false
  },
  "edges": {
    "smooth": {
      "forceDirection": "none"
    }
  },
  "interaction": {
    "hover": true,
    "multiselect": true,
    "navigationButtons": true
  },
  "manipulation": {
    "enabled": true
  },
  "physics": {
    "barnesHut": {
      "avoidOverlap": 0.11
    },
    "minVelocity": 0.75
  }
}'
```

![Example Network Map](../img/networkmap.png)
