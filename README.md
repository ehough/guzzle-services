# ehough/guzzle-services

[![Build Status](https://travis-ci.org/ehough/guzzle-services.svg?branch=develop)](https://travis-ci.org/ehough/guzzle-services)
[![Latest Stable Version](https://poser.pugx.org/ehough/guzzle-services/v/stable)](https://packagist.org/packages/ehough/guzzle-services)
[![License](https://poser.pugx.org/ehough/guzzle-services/license)](https://packagist.org/packages/ehough/guzzle-services)

A PHP 5.3-compatible fork of [Guzzle Services](https://github.com/guzzle/services).

# Why?

Sadly, [60%](https://w3techs.com/technologies/details/pl-php/5/all) of all PHP web servers still run PHP 5.4 and lower, but Guzzle Services needs PHP 5.5 or higher. This fork makes Guzzle Services compatible with PHP 5.3.29 through 7.1.

# How to Use This Fork

Usage is identical to [`guzzle/services`](https://github.com/guzzle/services), except that the code in this library is 
namespaced under `Hough\Guzzle` instead of `GuzzleHttp`.

--- 

Provides an implementation of the Guzzle Command library that uses Guzzle service descriptions to describe web services, serialize requests, and parse responses into easy to use model structures.

```php
use Hough\Guzzle\Client;
use Hough\Guzzle\Command\Guzzle\GuzzleClient;
use Hough\Guzzle\Command\Guzzle\Description;

$client = new Client();
$description = new Description(array(
	'baseUri' => 'http://httpbin.org/',
	'operations' => array(
		'testing' => array(
			'httpMethod' => 'GET',
			'uri' => '/get{?foo}',
			'responseModel' => 'getResponse',
			'parameters' => array(
				'foo' => array(
					'type' => 'string',
					'location' => 'uri'
				),
				'bar' => array(
					'type' => 'string',
					'location' => 'query'
				)
			)
		)
	),
	'models' => array(
		'getResponse' => array(
			'type' => 'object',
			'additionalProperties' => array(
				'location' => 'json'
			)
		)
	)
));

$guzzleClient = new GuzzleClient($client, $description);

$result = $guzzleClient->testing(array('foo' => 'bar'));
echo $result['args']['foo'];
// bar
```

More documentation coming soon.
