
## Deploying the project


```$xslt
composer install
```
```$xslt
cp .env.example .env
```
```$xslt
php artisan key:generate
```
```$xslt
php artisan migrate
```
```angular2html
php artisan db:seed
```
```angular2html
php artisan storage:link
```
```angular2html
composer require guzzlehttp/guzzle
```

Add to CRON:
```$xslt
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```


**For testing after new pul:
```angular2html
php artisan migrate:reset
```
```angular2html
php artisan migrate
```
```angular2html
php artisan db:seed
```