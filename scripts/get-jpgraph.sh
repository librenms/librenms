#!/bin/bash
wget http://hem.bredband.net/jpgraph2/jpgraph-2.3.4.tar.gz
tar zxf jpgraph-2.3.4.tar.gz
rm -rf html/includes/jpgraph
mv jpgraph-2.3.4 html/includes/jpgraph
rm jpgraph-2.3.4.tar.gz
