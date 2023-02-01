# Notice: Development Environment
This file has instructions on how to deploy the project to a production server.
For instructions on how to prepare a development environment, please read DEVELOPMENT.md

# Deployment to server:
- Operating System: Ubuntu 18.04 LTS
- PHP Version: 7.2 or above
- Database: MySQL or MariaDB


#### Install nginx
```
sudo apt install nginx 
```

#### Install PHP Packages
```
sudo apt install php7.2-fpm php7.2-gmp php7.2-json php7.2-zip php7.2-mysql php7.2-gd php7.2-dom php7.2-curl php7.2-imap php7.2-sqlite3
```

#### Install Other Packages
```
sudo apt install composer mysql-server
```

#### Clone the project and install dependencies
Assuming that the logged in user's name is liveuser, group is liveuser and has sudo permissions
```
sudo mkdir /var/www/emailwish
sudo chown liveuser:liveuser /var/www/emailwish 
git clone git@gitlab.com:volobot/ankit/emailwish/emailwish-v4.git /var/www/emailwish
cd /var/www/emailwish
composer install
```

#### Create a database and user for the project
```
sudo mysql
create database emailwish_live;
create user 'emailwish_live'@'localhost' identified by 'LIVE_PASSWORD';
grant all privileges on `emailwish_live`.* to 'emailwish_live'@'localhost';
# Press Ctrl+D to exit
```

#### Make PHP service for login user
```
cd /etc/php/7.4/fpm/pool.d/
sudo cp www.conf liveuser.conf
sudo nano liveuser.conf 
#change the following
    `[www]` to `[liveuser]`
    `user = www` to `user = liveuser`
    `group = www` to `group = liveuser`
    'listen = /run/php/php7.2-fpm.sock` to `/run/php/php7.2-fpm.liveuser.sock`
```

#### Verify that the configuration is correct
```
sudo php-fpm7.2 -tt
```

#### Restart the PHP service
```
sudo service php7.4-fpm stop
sudo service php7.4-fpm start
```


#### Configure the project
```
cd /var/www/emailwish
cp .env.example .env
nano .env
# Make sure that DB_HOST DB_DATABASE DB_USERNAME DB_PASSWORD are set correctly
# Press Ctrl+X and y and Enter to save the file
php artisan optimize:clear
php artisan key:generate
```

#### Create database tables and seed dummy data
```
php artisan optimize:clear
php artisan migrate:fresh --step --seed
```

# Serve using nginx