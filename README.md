В composer.json добавляем в блок require
```json
 "s-dev/builder_lara_5" : "1.*"
```
Выполняем
```json
composer update
```
Добавляем в файле app.php в блок providers
```php
  Vis\Builder\BuilderServiceProvider::class,
```
Добавляем в файле app.php в блок alias
```php
  'Jarboe' => Vis\Builder\Facades\Jarboe::class,
```
В корне проекта в файле .env заменяем подключение к БД на свои

Инсталим админку
```json
   php artisan admin:install
```
Генерируем пароль для админа
```json
   php artisan admin:generatePassword
```
