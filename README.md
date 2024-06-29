### SMS Sender App

DISCLAIMER:
I apologize for the two (2) spaces indentation, but this is how I am used to in many years now.
I understand that the default is four (4) tabs. I will be able to adapt to new codebase, shall the need arises.

### Setup PostgreSQL
CREATE DATABASE sms_sender_app;
CREATE USER sms_sender_app WITH password 'sms_sender_app';
ALTER DATABASE sms_sender_app OWNER TO sms_sender_app;


php artisan migrate:fresh
php artisan db:seed