#!/usr/bin/env bash

if [[ $# -eq 0 ]]
    then
        php /home/develop/online-kkt/public/console.php apiv1:send-check-pack >> /home/develop/online-kkt/data/logs/umka/send-check-pack_$(date +%Y.%m.%d).log 2>&1
else
        php /home/develop/online-kkt/public/console.php apiv1:send-check-pack -s $1 >> /home/develop/online-kkt/data/logs/umka/send-check-pack-$1-$(date +%Y.%m.%d).log 2>&1
fi