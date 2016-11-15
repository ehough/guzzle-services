===============
Guzzle Services
===============

Provides an implementation of the Guzzle Command library that uses Guzzle service descriptions to describe web services, serialize requests, and parse responses into easy to use model structures.

.. image:: https://travis-ci.org/Konafets/guzzle-services.svg?branch=guzzle6-pr1
    :target: https://travis-ci.org/Konafets/guzzle-services
.. image:: https://scrutinizer-ci.com/g/Konafets/guzzle-services/badges/quality-score.png?b=guzzle6-pr1
    :target: https://scrutinizer-ci.com/g/Konafets/guzzle-services/?branch=guzzle6-pr1
.. image:: https://scrutinizer-ci.com/g/Konafets/guzzle-services/badges/coverage.png?b=guzzle6-pr1
    :target: https://scrutinizer-ci.com/g/Konafets/guzzle-services/?branch=guzzle6-pr1

.. code-block:: php

    use GuzzleHttp\Client;
    use GuzzleHttp\Command\Guzzle\GuzzleClient;
    use GuzzleHttp\Command\Guzzle\Description;

    $client = new Client();
    $description = new Description([
        'baseUri' => 'http://httpbin.org/',
        'operations' => [
            'testing' => [
                'httpMethod' => 'GET',
                'uri' => '/get{?foo}',
                'responseModel' => 'getResponse',
                'parameters' => [
                    'foo' => [
                        'type' => 'string',
                        'location' => 'uri'
                    ],
                    'bar' => [
                        'type' => 'string',
                        'location' => 'query'
                    ]
                ]
            ]
        ],
        'models' => [
            'getResponse' => [
                'type' => 'object',
                'additionalProperties' => [
                    'location' => 'json'
                ]
            ]
        ]
    ]);

    $guzzleClient = new GuzzleClient($client, $description);

    $result = $guzzleClient->testing(['foo' => 'bar']);
    echo $result['args']['foo'];
    // bar

Installing
==========

This project can be installed using Composer. Add the following to your
composer.json:

.. code-block:: javascript

    {
        "require": {
            "guzzlehttp/guzzle-services": "0.5.*"
        }
    }

Plugins
=======

* Load Service description from file [https://github.com/gimler/guzzle-description-loader]

More documentation coming soon.
