#!/bin/sh
./map.php > map.dot && unflatten -l5 -f map.dot | fdp -Tpng -o html/network-big.png && convert -resize 400x500 html/network-big.png html/network.png
./map.php > map.dot && unflatten -l5 -f map.dot | dot -Tpng -o html/network-screen-big.png && convert -resize 900x2000 html/network-screen-big.png html/network-screen.png

