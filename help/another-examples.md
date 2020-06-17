
## Other Examples for Creating Document


#### Create a document by setting the ID

```php
$user = new Document();
$user->setID('FIXED ID');
$user->name = "Fulano da Silva";
$user->username = "fulano";
$user->password = md5("fulano");

$deskdb->post($user);
```

#### Create multiple documents
```php
use deskdb\drivers\JSON as JSON;
use deskdb\Collection as Collection;
use deskdb\Document as Document;

$collection = new Collection('users',new JSON(__DIR__.'/mybase/'));

$users = [];
for ($i=0; $i < 10; $i++) { 
	$user = new Document();
	$user->name = "Test ".$i;
	$user->username = "username ".$i;
	array_unshift($users, $user);
}

$collection->post($users);
```
