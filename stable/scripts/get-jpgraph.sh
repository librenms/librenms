#!/bin/bash
mkdir jpgraph
cd jpgraph
wget http://hem.bredband.net/jpgraph2/jpgraph-3.0.2.tar.bz2
tar jxf jpgraph-3.0.2.tar.bz2
rm -rf html/includes/jpgraph
rm jpgraph-3.0.2.tar.bz2
cd ..
mv jpgraph html/includes/jpgraph
