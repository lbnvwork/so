#!/usr/bin/env bash
cd /data
find -type f,d -not -name 'cache' -not -name 'logs' -not -name 'docker' -not -name '.gitignore' -delete