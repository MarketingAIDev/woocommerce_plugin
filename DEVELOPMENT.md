# Development:
- Operating System: Ubuntu 18.04 LTS
- PHP Version: 7.2 or above
- Database: MySQL or MariaDB

## Installation

#### Install PHP Packages
```
sudo apt install php7.2-fpm php7.2-gmp php7.2-json php7.2-zip php7.2-mysql php7.2-gd php7.2-dom php7.2-curl php7.2-imap php7.2-sqlite3
```

#### Install Other Packages
```
sudo apt install composer mysql-server
```

#### Clone the project and install dependencies
```
cd ~
mkdir Projects
cd Projects
git clone git@gitlab.com:volobot/ankit/emailwish/emailwish-v4.git emailwish-v4
cd emailwish-v4
git checkout staging
composer install
```

#### Create a database and user for the project
```
sudo mysql
create database emailwish_dev;
create user 'emailwish_dev'@'localhost' identified by '23rT@@hyUT45533';
grant all privileges on `emailwish_dev`.* to 'emailwish_dev'@'localhost';
# Press Ctrl+D to exit
```

#### Configure the project
```
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

## Running the application
### Start the server with the following command:
```
php artisan optimize:clear && php artisan serve
```
See the application in http://localhost:8000

Login with the following credentials
```
Email: admin@emailwish.com
Password: admin@123
```

### Run queue processing
Run the following command once until exited
```
php artisan optimize:clear && php artisan queue:work --retries=3
```

### Run cron jobs
Run the following command once every time to trigger cron job
```
php artisan optimize:clear && php artisan schedule:run
```

## Contributing
All contributions should be made on your own branch and merge requests should be raised against `staging` branch.
```
git checkout staging
git checkout -b yourname # Create a new branch on your name
# Make changes
git add .
git commit -m "Commit message" # Use your own commit messages
git push

# Login to gitlab web and raise a merge request
```