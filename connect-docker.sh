#!/usr/bin/env bash
work_dir=`dirname $0`

p=`pwd`
containerName=`basename $p`
containerName="${containerName//./}"

docker exec -it ${containerName}_app_1 bash