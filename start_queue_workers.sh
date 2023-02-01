# Stop running worker
php artisan queue:restart

# Start new worker
screen -dm -S emailwish_staging_queue_worker php artisan queue:work --tries=3
