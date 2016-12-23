<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\Handler;

use Hough\Guzzle\Client as HttpClient;
use Hough\Guzzle\Command\Guzzle\Description;
use Hough\Guzzle\Command\Guzzle\GuzzleClient;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\Handler\ValidatedDescriptionHandler
 */
class ValidatedDescriptionHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Hough\Guzzle\Command\Exception\CommandException
     * @expectedExceptionMessage Validation errors: [bar] is a required string
     */
    public function testValidates()
    {
        $description = new Description(array(
            'operations' => array(
                'foo' => array(
                    'uri' => 'http://httpbin.org',
                    'httpMethod' => 'GET',
                    'responseModel' => 'j',
                    'parameters' => array(
                        'bar' => array(
                            'type'     => 'string',
                            'required' => true
                        )
                    )
                )
            )
        ));

        $client = new GuzzleClient(new HttpClient(), $description);
        $client->foo(array());
    }

    public function testSuccessfulValidationDoesNotThrow()
    {
        $description = new Description(array(
            'operations' => array(
                'foo' => array(
                    'uri' => 'http://httpbin.org',
                    'httpMethod' => 'GET',
                    'responseModel' => 'j',
                    'parameters' => array()
                )
            ),
            'models' => array(
                'j' => array(
                    'type' => 'object'
                )
            )
        ));

        $client = new GuzzleClient(new HttpClient(), $description);
        $client->foo(array());
    }

    /**
     * @expectedException \Hough\Guzzle\Command\Exception\CommandException
     * @expectedExceptionMessage Validation errors: [bar] must be of type string
     */
    public function testValidatesAdditionalParameters()
    {
        $description = new Description(array(
            'operations' => array(
                'foo' => array(
                    'uri' => 'http://httpbin.org',
                    'httpMethod' => 'GET',
                    'responseModel' => 'j',
                    'additionalParameters' => array(
                        'type'     => 'string'
                    )
                )
            ),
            'models' => array(
                'j' => array(
                    'type' => 'object'
                )
            )
        ));

        $client = new GuzzleClient(new HttpClient(), $description);
        $client->foo(array('bar' => new \stdClass()));
    }
}
