### SMS Sender App

DISCLAIMER:
I apologize for the two (2) spaces indentation. This is how I am used to programming, for many years now.
I understand that the default is four (4) tabs. I will be able to adapt to the new codebase, should the need arise.

### Manually install on Ubuntu 22.04

#### Install PHP 8.1
```
sudo apt-get install -y php8.1 php8.1-cli php8.1-common php8.1-xml php8.1-curl php8.1-pgsql php8.1-mbstring
```

#### Install PostgreSQL
```
sudo apt-get -y install postgresql-14
```

#### Install Redis server with Docker
```
docker pull redis
docker rm redis-laravel
docker run -d --name redis-laravel -p 6379:6379 redis:latest
```

#### Install Composer
```
cd ~
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
HASH=`curl -sS https://composer.github.io/installer.sig`
php -r "if (hash_file('SHA384', '/tmp/composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

### Setup PostgreSQL databases for development and testing environments
```
sudo -u postgres psql
```

Create a database for the development environement.
```
CREATE DATABASE sms_sender_app;
CREATE USER sms_sender_app WITH password 'sms_sender_app';
ALTER DATABASE sms_sender_app OWNER TO sms_sender_app;
```

Create a database for the testing environement (to run the tests).

```
CREATE DATABASE sms_sender_app_testing;
CREATE USER sms_sender_app_testing WITH password 'sms_sender_app_testing';
ALTER DATABASE sms_sender_app_testing OWNER TO sms_sender_app_testing;
```

### Laravel

#### Composer
```
composer install
composer dump-autoload
```

#### Setup the database
Setup the development
```
php artisan migrate:fresh
php artisan db:seed
```

Setup the test database
```
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing
```

#### Create custom exceptions
```
php artisan make:exception UserSmsProviderException
php artisan make:exception SmsMessageCreateException
```

#### Redis PHP Install
```
composer require predis/predis
php artisan cache:clear
php artisan config:cache
```

#### Queues
```
php artisan queue:failed
php artisan queue:work --queue=sms_messages
```

#### Install Horizon to monitor job queues.
```
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
composer remove laravel/horizon
```

#### Install libphonenumber for phone validation.
```
composer require giggsey/libphonenumber-for-php
```
