# HoltBosse DB library

simple wrapper over php pdo

to use it:
```php
use HoltBosse\DB\DB;

DB::createInstance($dsn, $username, $password);

//utility methods
$result = DB::fetch(...);
$result = DB::fetchAll(...);
$result = DB::exec(...);
$result = DB::getLastInsertedId();

//get the raw pdo object
$pdo = DB::getPdo();
```