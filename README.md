## SMS Sender App

DISCLAIMER:
I apologize for the two (2) spaces indentation. This is how I am used to programming, for many years now.
I understand that the default is four (4) tabs. I will be able to adapt to the new codebase, should the need arise.

#

### Env configuration files

#### Docker
If you are going to use docker to setup the application, rename:\
`.env.docker.example` to `.env`\
`.env.docker.testing.example` to `.env.testing`

and replace `APP_SMS_TO_PROVIDER_ACCESS_TOKEN` in both files with the access token from your SMS provider.

#### Localhost
If you are going to manually setup everything on your localhost, rename:\
`.env.localhost.example` to `.env`\
`.env.localhost.testing.example` to `.env.testing`

and replace `APP_SMS_TO_PROVIDER_ACCESS_TOKEN` in both files with the access token from your SMS provider.

NOTE:

In both cases there should always be two files in the root folder: `.env` and `.env.testing`.
The most important difference is the `DB_HOST` and `REDIS_HOST`.

For the docker env, the same database is
used for both `dev` and `testing`. To be resolved soon, to use different containers for each environment.

#

### Docker installation
```
docker-compose down
docker-compose up --build --force-recreate
```

Ensure everything is up:
```
docker ps
```
You should get something like the following (~):
```
CONTAINER ID   IMAGE                COMMAND                  CREATED              STATUS              PORTS                                                 NAMES
f053b56a7135   sms-sender-app_app   "docker-php-entrypoi…"   About a minute ago   Up About a minute   0.0.0.0:8000->8000/tcp, :::8000->8000/tcp, 9000/tcp   sms-sender-app_app_1
970f3111c528   postgres:14.12       "docker-entrypoint.s…"   About a minute ago   Up About a minute   0.0.0.0:5432->5432/tcp, :::5432->5432/tcp             sms-sender-app_postgres_1
e832b97730a1   redis:7.2.5          "docker-entrypoint.s…"   About a minute ago   Up About a minute   0.0.0.0:6379->6379/tcp, :::6379->6379/tcp             sms-sender-app_redis_1

```

Once the containers are up and running, issue the following your host machine:
```
docker-compose exec app composer install
docker-compose exec app php artisan migrate:fresh
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan migrate:fresh --env="testing"
docker-compose exec app php artisan db:seed --env="testing"
```

You can visit `http://localhost:8000/` to see the app running.

If you have successfully configured the `APP_SMS_TO_PROVIDER_ACCESS_TOKEN`,
you can replace your phone number bellow, and visit the following URL, to actually receive an SMS message on your phone:
```
http://localhost:8000/test-sms?phone_number=<Your Phone Number Here in the form of (+30...)>
```

Then run the queue work to process and dispatch the above request:
```
docker-compose exec app php artisan queue:work --queue=sms_messages
```

To run all the tests, issue the following command.

For docker, it's going to use the same database as dev, so if you run in conflicts,
run `migrate:fresh` and `db:seed` again.

```
docker-compose exec app php artisan test
```

To get an interactive bash use:

```
docker-compose exec app bash
```

If you wish to install a text editor to modify any files, or replace your phone inside the specs, use:
```
apt install nano
```
or
```
apt install vim
```

To login into the PostgreSQL shell:

```
docker-compose exec postgres psql -U sms_sender_app
```
#

### Manual installation on Ubuntu 22.04 / Localhost Setup

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

#

### Laravel

#### Composer
```
composer install
composer dump-autoload
```

#### Setup the database
Setup the development database
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

#### Clear cache and config
```
php artisan cache:clear
php artisan config:clear
php artisan config:cache
```
