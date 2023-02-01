#!/bin/bash
DIR=$(dirname "$0")
found=$(screen -ls | grep -c emailwish_staging_queue_worker)

if ((found < 1)); then
    cd "${DIR}" || exit
    ./start_queue_workers.sh
fi
