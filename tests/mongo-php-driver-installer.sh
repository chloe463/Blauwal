#!/bin/bash

wget https://github.com/mongodb/mongo-php-driver/releases/download/1.2.2/mongodb-1.2.2.tgz
tar zxf mongodb-1.2.2.tgz
sh -c "cd mongodb-1.2.2 && phpize && ./configure && make && make install"

echo "extension=mongodb.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
