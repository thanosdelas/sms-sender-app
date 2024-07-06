### SMS Sender App

DISCLAIMER:
I apologize for the two (2) spaces indentation, but this is how I am used to in many years now.
I understand that the default is four (4) tabs. I will be able to adapt to new codebase, shall the need arises.

### Composer
```
composer dump-autoload
```

### Setup PostgreSQL
CREATE DATABASE sms_sender_app;
CREATE USER sms_sender_app WITH password 'sms_sender_app';
ALTER DATABASE sms_sender_app OWNER TO sms_sender_app;

php artisan migrate:fresh
php artisan db:seed

php artisan make:model Badword

php artisan make:test Models/MessageTest --unit
php artisan make:test Repositories/BadwordRepository --unit

php artisan make:factory MessageStatusFactory --model=MessageStatus
php artisan make:factory MessageFactory --model=Message

### For testing
CREATE DATABASE sms_sender_app_testing;
CREATE USER sms_sender_app_testing WITH password 'sms_sender_app_testing';
ALTER DATABASE sms_sender_app_testing OWNER TO sms_sender_app_testing;

php artisan make:seeder TestDatabaseSeeder

php artisan migrate --env=testing
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing

php artisan make:migration add_default_sms_provider_to_users --table=users

php artisan make:controller Api/SendSMS --api
php artisan make:test Api/SendSMSTest --feature

php artisan make:exception UserSmsProviderException
php artisan make:exception SmsMessageCreateException

php artisan test --filter does_not_create_a_message_when_the_user_is_not_attached_to_sms_providers

### Docker Redis
docker pull redis
docker rm redis-laravel
docker run -d --name redis-laravel -p 6379:6379 redis:latest

### Redis PHP Install
composer require predis/predis
php artisan cache:clear
php artisan config:cache

### redis-cli
INFO
SELECT 1
KEYS *
FLUSHALL

GET laravel_database_badwords
SMEMBERS laravel_database_badwords
SCARRD count members


