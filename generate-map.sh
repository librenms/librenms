#!/bin/bash
./map.php > map.dot && unflatten -l4 -f map.dot | dot -Tpng -o html/network-big.png && convert -resize 400x300 html/network-big.png html/network.png

