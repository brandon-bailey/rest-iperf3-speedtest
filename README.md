# Rest Speedtest Server built around iperf3


## Installation

Drag and drop the **application/libraries/Format.php** and **application/libraries/REST_Controller.php** files into your application's directories. To use `require_once` it at the top of your controllers to load it into the scope. Additionally, copy the **rest.php** file from **application/config** in your application's configuration directory.

## Handling Requests

When your controller extends from `REST_Controller`, the method names will be appended with the HTTP method used to access the request. If you're  making an HTTP `GET` call to `/books`, for instance, it would call a `Books#index_get()` method.

This allows you to implement a RESTful interface easily:

```php
// application/config/config.php
// Set the website URL
$config['base_url'] = 'https://test.com/';


// application/config/speedtest.php
// Ensure your compiled iperf3 instance is located in this folder and is executable
$config['speedtest_server'] = FCPATH .'/assets/pocketspeed/centos/iperf3';

// application/config/speedtest.php
// Ensure these settings are properly configured for the database.
// This stores the  processing of the speedtest results
//  and the information needed to purge any loose running instances
$db['default'] = array(
    'dsn'   => '',
    'hostname' => 'localhost',
    'username' => 'rest_speedtest',
    'password' => '<password>',
    'database' => 'test_speedtest',
    'dbdriver' => 'mysqli',
);
```


If query parameters are passed via the URL, regardless of whether it's a GET request, can be obtained by the query method:

```php
<?php
$this->query('blah'); // Query param
```

## Content Types

`REST_Controller` supports a bunch of different request/response formats, including XML, JSON and serialised PHP. By default, the class will check the URL and look for a format either as an extension or as a separate segment.

This means your URLs can look like this:
```
http://example.com/books.json
http://example.com/books?format=json
```

This can be flaky with URI segments, so the recommend approach is using the HTTP `Accept` header:

```bash
$ curl -H "Accept: application/json" http://example.com
```

Any responses you make from the class (see [responses](#responses) for more on this) will be serialised in the designated format.

## Responses

The class provides a `response()` method that allows you to return data in the user's requested response format.

Returning any object / array / string / whatever is easy:

```php
<?php
public function index_get()
{
  $this->response($this->db->get('books')->result());
}
```

This will automatically return an `HTTP 200 OK` response. You can specify the status code in the second parameter:

```php
<?php
public function index_post()
  {
    // ...create new book
    $this->response($book, 201); // Send an HTTP 201 Created
  }
```

If you don't specify a response code, and the data you respond with `== FALSE` (an empty array or string, for instance), the response code will automatically be set to `404 Not Found`:

```php
<?php
$this->response([]); // HTTP 404 Not Found
```

## Authentication

This class also provides rudimentary support for HTTP basic authentication and/or the securer HTTP digest access authentication.

You can enable basic authentication by setting the `$config['rest_auth']` to `'basic'`. The `$config['rest_valid_logins']` directive can then be used to set the usernames and passwords able to log in to your system. The class will automatically send all the correct headers to trigger the authentication dialogue:

```php
<?php
$config['rest_valid_logins'] = ['username' => 'password', 'other_person' => 'secure123'];
```

Enabling digest auth is similarly easy. Configure your desired logins in the config file like above, and set `$config['rest_auth']` to `'digest'`. The class will automatically send out the headers to enable digest auth.

If you're tying this library into an AJAX endpoint where clients authenticate using PHP sessions then you may not like either of the digest nor basic authentication methods. In that case, you can tell the REST Library what PHP session variable to check for. If the variable exists, then the user is authorized. It will be up to your application to set that variable. You can define the variable in ``$config['auth_source']``.  Then tell the library to use a php session variable by setting ``$config['rest_auth']`` to ``session``.

All three methods of authentication can be secured further by using an IP white-list. If you enable `$config['rest_ip_whitelist_enabled']` in your config file, you can then set a list of allowed IPs.

Any client connecting to your API will be checked against the white-listed IP array. If they're on the list, they'll be allowed access. If not, sorry, no can do hombre. The whitelist is a comma-separated string:

```php
<?php
$config['rest_ip_whitelist'] = '123.456.789.0, 987.654.32.1';
```

Your localhost IPs (`127.0.0.1` and `0.0.0.0`) are allowed by default.

## API Keys

In addition to the authentication methods above, the `REST_Controller` class also supports the use of API keys. Enabling API keys is easy. Turn it on in your **config/rest.php** file:

```php
<?php
$config['rest_enable_keys'] = TRUE;
```

You'll need to create a new database table to store and access the keys. `REST_Controller` will automatically assume you have a table that looks like this:

```sql
CREATE TABLE `keys` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`key` VARCHAR(40) NOT NULL,
	`level` INT(2) NOT NULL,
	`ignore_limits` TINYINT(1) NOT NULL DEFAULT '0',
	`date_created` INT(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

The class will look for an HTTP header with the API key on each request. An invalid or missing API key will result in an `HTTP 403 Forbidden`.

By default, the HTTP will be `X-API-KEY`. This can be configured in **config/rest.php**.

```bash
$ curl -X POST -H "X-API-KEY: some_key_here" http://example.com/books
```
