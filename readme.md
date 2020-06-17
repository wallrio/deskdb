
# DeskDB
A small embedded database, based on JSON format.

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install Directly.

```bash
$ composer require wallrio/deskdb "*"
```

#### Instantiate a collection class

```php
use deskdb\Collection as Collection;

$deskdb = new Collection(COLLECTION_NAME,DRIVER);
```

- Example

```php
use deskdb\drivers\JSON as JSON;
use deskdb\Collection as Collection;

$collection = new Collection('users',new JSON(__DIR__.'/mybase/'));
```
> JSON, is the class responsible for effectively carrying out operations.

> JSON, if the contractor's value is omitted, then the system's temporary directory will be used.

#### Create a document

```php

use deskdb\Document as Document;

$user = new Document();
$user->name = "Fulano da Silva";
$user->username = "fulano";
$user->password = md5("fulano");

$collection->post($user);
```
> [Another examples](help/another-examples.md)


#### Search all documents in the collection

```php
$result = $collection->get();
```


#### Search for a document

```php
$result = $collection->get(KEY,VALUE,OPERATOR);
```

- **KEY**: can be any document key
- **VALUE**: can be any document value, partial or whole
- **OPERATOR**: reference value to perform the search
	- **==:** search for a similar value
	- **!=:** search for different value
	- **===:** search by identical value
	- **!==:** look for exactly different value
	- **like**: look for a similar value (type soundex)
	- **!like**: search for unlike value (type soundex)
	- **contain**: search by value part
	- **!contain**: search for part of non-existent value


#### Search for the first document occurrence

```php
$result = $collection->getFirst(KEY,VALUE,OPERATOR);
```


#### Delete document

```php
$result = $collection->get();

$collection->delete($result);

```
> the value ** delete ** accepts an array of results



#### Update document

```php
$result = $collection->getFirst();

$result->name="ANOTHER NAME";
$result->age="34";

$collection->put($result);

```
> the ** put ** value accepts an array of results





## Recomendations


It is explicitly recommended to use the code below to block the viewing of your JSON directories and documents.

Below example of script for blocking

##### APACHE


Create an .htaccess file in your application's main directory

```bash
# Block directory list view
Options -Indexes

# Block the visualization of document JSON
<Files "*_deskdb.json">
Order Allow,Deny
Deny from all
</Files>
```

#####  NGINX

Insert the excerpt below into your server's nginx.conf file.

```bash
location ~ \.*_deskdb.json {
        deny all;
    }
```


## License

The DeskDB is licensed under the MIT license. See [License File](LICENSE) for more information.