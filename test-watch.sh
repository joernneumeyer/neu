#!/bin/sh
composer test
while inotifywait -e modify,create tests/ -r
do
  composer test
done
