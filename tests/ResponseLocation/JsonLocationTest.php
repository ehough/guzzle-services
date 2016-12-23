<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\ResponseLocation;

use Hough\Guzzle\Client;
use Hough\Guzzle\Command\Guzzle\Description;
use Hough\Guzzle\Command\Guzzle\GuzzleClient;
use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\ResponseLocation\JsonLocation;
use Hough\Guzzle\Command\Result;
use Hough\Guzzle\Command\ResultInterface;
use Hough\Guzzle\Handler\MockHandler;
use Hough\Psr7\Response;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\ResponseLocation\JsonLocation
 * @covers \Hough\Guzzle\Command\Guzzle\Deserializer
 */
class JsonLocationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @group ResponseLocation
     */
    public function testVisitsLocation()
    {
        $location = new JsonLocation();
        $parameter = new Parameter(array(
            'name'    => 'val',
            'sentAs'  => 'vim',
            'filters' => array('strtoupper')
        ));
        $response = new Response(200, array(), '{"vim":"bar"}');
        $result = new Result();
        $result = $location->before($result, $response, $parameter);
        $result = $location->visit($result, $response, $parameter);
        $this->assertEquals('BAR', $result['val']);
    }

    /**
     * @group ResponseLocation
     */
    public function testVisitsAdditionalProperties()
    {
        $location = new JsonLocation();
        $parameter = new Parameter();
        $model = new Parameter(array('additionalProperties' => array('location' => 'json')));
        $response = new Response(200, array(), '{"vim":"bar","qux":[1,2]}');
        $result = new Result();
        $result = $location->before($result, $response, $parameter);
        $result = $location->visit($result, $response, $parameter);
        $result = $location->after($result, $response, $model);
        $this->assertEquals('bar', $result['vim']);
        $this->assertEquals(array(1, 2), $result['qux']);
    }

    /**
     * @group ResponseLocation
     */
    public function testVisitsAdditionalPropertiesWithEmptyResponse()
    {
        $location = new JsonLocation();
        $parameter = new Parameter();
        $model = new Parameter(array('additionalProperties' => array('location' => 'json')));
        $response = new Response(204);
        $result = new Result();
        $result = $location->before($result, $response, $parameter);
        $result = $location->visit($result, $response, $parameter);
        $result = $location->after($result, $response, $model);
        $this->assertEquals(array(), $result->toArray());
    }

    public function jsonProvider()
    {
        return array(
            array(null, array(array('foo' => 'BAR'), array('baz' => 'BAM'))),
            array('under_me', array('under_me' => array(array('foo' => 'BAR'), array('baz' => 'BAM')))),
        );
    }

    /**
     * @dataProvider jsonProvider
     * @group ResponseLocation
     * @param $name
     * @param $expected
     */
    public function testVisitsTopLevelArrays($name, $expected)
    {
        $json = array(
            array('foo' => 'bar'),
            array('baz' => 'bam'),
        );
        $body = \Hough\Guzzle\json_encode($json);
        $response = new Response(200, array('Content-Type' => 'application/json'), $body);
        $mock = new MockHandler(array($response));

        $guzzle = new Client(array('handler' => $mock));

        $description = new Description(array(
            'operations' => array(
                'foo' => array(
                    'uri' => 'http://httpbin.org',
                    'httpMethod' => 'GET',
                    'responseModel' => 'j'
                )
            ),
            'models' => array(
                'j' => array(
                    'type' => 'array',
                    'location' => 'json',
                    'name' => $name,
                    'items' => array(
                        'type' => 'object',
                        'additionalProperties' => array(
                            'type' => 'string',
                            'filters' => array('strtoupper')
                        )
                    )
                )
            )
        ));
        $guzzle = new GuzzleClient($guzzle, $description);
        /** @var ResultInterface $result */
        $result = $guzzle->foo();
        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * @group ResponseLocation
     */
    public function testVisitsNestedArrays()
    {
        $json = array(
            'scalar' => 'foo',
            'nested' => array(
                'bar',
                'baz'
            )
        );
        $body = \Hough\Guzzle\json_encode($json);
        $response = new Response(200, array('Content-Type' => 'application/json'), $body);
        $mock = new MockHandler(array($response));

        $httpClient = new Client(array('handler' => $mock));

        $description = new Description(array(
            'operations' => array(
                'foo' => array(
                    'uri' => 'http://httpbin.org',
                    'httpMethod' => 'GET',
                    'responseModel' => 'j'
                )
            ),
            'models' => array(
                'j' => array(
                    'type' => 'object',
                    'location' => 'json',
                    'properties' => array(
                        'scalar' => array('type' => 'string'),
                        'nested' => array(
                            'type' => 'array',
                            'items' => array('type' => 'string')
                        )
                    )
                )
            )
        ));
        $guzzle = new GuzzleClient($httpClient, $description);
        /** @var ResultInterface $result */
        $result = $guzzle->foo();
        $expected = array(
            'scalar' => 'foo',
            'nested' => array(
                'bar',
                'baz'
            )
        );
        $this->assertEquals($expected, $result->toArray());
    }

    public function nestedProvider()
    {
        return array(
            array(
                array(
                    'operations' => array(
                        'foo' => array(
                            'uri' => 'http://httpbin.org',
                            'httpMethod' => 'GET',
                            'responseModel' => 'j'
                        )
                    ),
                    'models' => array(
                        'j' => array(
                            'type' => 'object',
                            'properties' => array(
                                'nested' => array(
                                    'location' => 'json',
                                    'type' => 'object',
                                    'properties' => array(
                                        'foo' => array('type' => 'string'),
                                        'bar' => array('type' => 'number'),
                                        'bam' => array(
                                            'type' => 'object',
                                            'properties' => array(
                                                'abc' => array(
                                                    'type' => 'number'
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            'additionalProperties' => array(
                                'location' => 'json',
                                'type' => 'string',
                                'filters' => array('strtoupper')
                            )
                        )
                    )
                )
            ),
            array(
                array(
                    'operations' => array(
                        'foo' => array(
                            'uri' => 'http://httpbin.org',
                            'httpMethod' => 'GET',
                            'responseModel' => 'j'
                        )
                    ),
                    'models' => array(
                        'j' => array(
                            'type' => 'object',
                            'location' => 'json',
                            'properties' => array(
                                'nested' => array(
                                    'type' => 'object',
                                    'properties' => array(
                                        'foo' => array('type' => 'string'),
                                        'bar' => array('type' => 'number'),
                                        'bam' => array(
                                            'type' => 'object',
                                            'properties' => array(
                                                'abc' => array(
                                                    'type' => 'number'
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            'additionalProperties' => array(
                                'type' => 'string',
                                'filters' => array('strtoupper')
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * @dataProvider nestedProvider
     * @group ResponseLocation
     */
    public function testVisitsNestedProperties($desc)
    {
        $json = array(
            'nested' => array(
                'foo' => 'abc',
                'bar' => 123,
                'bam' => array(
                    'abc' => 456
                )
            ),
            'baz' => 'boo'
        );
        $body = \Hough\Guzzle\json_encode($json);
        $response = new Response(200, array('Content-Type' => 'application/json'), $body);
        $mock = new MockHandler(array($response));

        $httpClient = new Client(array('handler' => $mock));

        $description = new Description($desc);
        $guzzle = new GuzzleClient($httpClient, $description);
        /** @var ResultInterface $result */
        $result = $guzzle->foo();
        $expected = array(
            'nested' => array(
                'foo' => 'abc',
                'bar' => 123,
                'bam' => array(
                    'abc' => 456
                )
            ),
            'baz' => 'BOO'
        );

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * @group ResponseLocation
     */
    public function testVisitsNullResponseProperties()
    {

        $json = array(
            'data' => array(
                'link' => null
            )
        );

        $body = \Hough\Guzzle\json_encode($json);
        $response = new Response(200, array('Content-Type' => 'application/json'), $body);
        $mock = new MockHandler(array($response));

        $httpClient = new Client(array('handler' => $mock));

        $description = new Description(
            array(
                'operations' => array(
                    'foo' => array(
                        'uri' => 'http://httpbin.org',
                        'httpMethod' => 'GET',
                        'responseModel' => 'j'
                    )
                ),
                'models' => array(
                    'j' => array(
                        'type' => 'object',
                        'location' => 'json',
                        'properties' => array(
                            'scalar' => array('type' => 'string'),
                            'data' => array(
                                'type'          => 'object',
                                'location'      => 'json',
                                'properties'    => array(
                                    'link' => array(
                                        'name'    => 'val',
                                        'type' => 'string',
                                        'location' => 'json'
                                    ),
                                ),
                                'additionalProperties' => false
                            )
                        )
                    )
                )
            )
        );
        $guzzle = new GuzzleClient($httpClient, $description);
        /** @var ResultInterface $result */
        $result = $guzzle->foo();

        $expected = array(
            'data' => array(
                'link' => null
            )
        );

        $this->assertEquals($expected, $result->toArray());
    }
}
