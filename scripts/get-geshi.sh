#!/bin/bash
wget http://surfnet.dl.sourceforge.net/sourceforge/geshi/GeSHi-1.0.8.3.tar.gz
tar zxf GeSHi-1.0.8.3.tar.gz
mv geshi html/includes
ln scripts/geshi-ios.php html/includes/geshi/geshi/ios.php
rm GeSHi-1.0.8.3.tar.gz
