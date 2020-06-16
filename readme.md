
# DeskDB
A small embedded database, based on JSON format.

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install Directly.

```bash
$ composer require wallrio/deskdb "*"
```

#### Instantiate DeskDB to use a collection

```php
use deskdb\DeskDB as DeskDB;

$deskdb = new DeskDB([
	'base' 			=>	DIRECTORY_OF_DATABASE,
	'collection'	=> 	COLLECTION_NAME
]);
```
> base is optional, if omitted, the system's temporary directory will be used.



#### Create a document

```php

use deskdb\Document as Document;

$user = new Document();
$user->name = "Fulano da Silva";
$user->username = "fulano";
$user->password = md5("fulano");

$deskdb->post($user);
```
> [Another examples](help/another-examples.md)


#### Search all documents in the collection

```php
$result = $deskdb->get();
```


#### Search for a document

```php
$result = $deskdb->get(KEY,VALUE,OPERATOR);
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
$result = $deskdb->getFirst(KEY,VALUE,OPERATOR);
```


#### Delete document

```php
$result = $deskdb->get();

$deskdb->delete($result);

```
> the value ** delete ** accepts an array of results



#### Update document

```php
$result = $deskdb->getFirst();

$result->name="ANOTHER NAME";
$result->age="34";

$deskdb->put($result);

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