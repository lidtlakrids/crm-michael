# crmv2
New crm running on Laravel 5.

Requirenments : 
-php 5.5
-composer

Installation 

1.The installation requires composer. https://getcomposer.org/

  1.1 run : git clone https://github.com/greenclickmedia/crmv2
  
2.After cloning the repository run :

    composer install
    
3.copy the env.example file and fill your database information in it.

3.1 Migrate the database tables, required for the system to run (acl and users).

4 -php artisan migrate

Documentation about the http client can be found https://github.com/php-curl-class/php-curl-class

Documentation about the javascript localization : https://github.com/rmariuzzo/laravel-js-localization

Rememebr to disable laravel debugbar from app/debugbar.conf, when deploying on live.
