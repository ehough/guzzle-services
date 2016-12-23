<?php
namespace Hough\Guzzle\Tests\Command\Guzzle;

use Hough\Guzzle\Client as HttpClient;
use Hough\Guzzle\Command\CommandInterface;
use Hough\Guzzle\Command\Guzzle\Description;
use Hough\Guzzle\Command\Guzzle\GuzzleClient;
use Hough\Guzzle\Command\Guzzle\Operation;
use Hough\Guzzle\Command\ServiceClientInterface;
use Hough\Guzzle\Handler\MockHandler;
use Hough\Guzzle\HandlerStack;
use Hough\Psr7\Response;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\Deserializer
 */
class DeserializerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServiceClientInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $serviceClient;

    /** @var CommandInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $command;

    public function setUp()
    {
        $this->serviceClient = $this->getMockBuilder('\Hough\Guzzle\Command\Guzzle\GuzzleClient')
                            ->disableOriginalConstructor()
                            ->getMock();
        $this->command = $this->getMockBuilder('\Hough\Guzzle\Command\CommandInterface')->getMock();
    }

    protected function prepareErrorResponses($commandName, array $errors = array())
    {
        $this->command->expects($this->once())->method('getName')->will($this->returnValue($commandName));

        $description = $this->getMockBuilder('\Hough\Guzzle\Command\Guzzle\DescriptionInterface')->getMock();
        $operation = new Operation(array('errorResponses' => $errors), $description);

        $description->expects($this->once())
            ->method('getOperation')
            ->with($commandName)
            ->will($this->returnValue($operation));

        $this->serviceClient->expects($this->once())
            ->method('getDescription')
            ->will($this->returnValue($description));
    }

    public function testDoNothingIfNoException()
    {
        $mock = new MockHandler(array(new Response(200)));
        $description = new Description(array(
            'operations' => array(
                'foo' => array(
                    'uri' => 'http://httpbin.org/{foo}',
                    'httpMethod' => 'GET',
                    'responseModel' => 'j',
                    'parameters' => array(
                        'bar' => array(
                            'type'     => 'string',
                            'required' => true,
                            'location' => 'uri'
                        )
                    )
                )
            ),
            'models' => array(
                'j' => array(
                    'type' => 'object'
                )
            )
        ));
        $httpClient = new HttpClient(array('handler' => $mock));
        $client = new GuzzleClient($httpClient, $description);
        $client->foo(array('bar' => 'baz'));
    }

    /**
     * @expectedException \Hough\Guzzle\Tests\Command\Guzzle\Asset\Exception\CustomCommandException
     */
    public function testCreateExceptionWithCode()
    {
        $response = new Response(404);
        $mock = new MockHandler(array($response));

        $description = new Description(array(
            'name' => 'Test API',
            'baseUri' => 'http://httpbin.org',
            'operations' => array(
                'foo' => array(
                    'uri' => '/{foo}',
                    'httpMethod' => 'GET',
                    'responseClass' => 'Foo',
                    'parameters' => array(
                        'bar' => array(
                            'type'     => 'string',
                            'required' => true,
                            'description' => 'Unique user name (alphanumeric)',
                            'location' => 'json'
                        ),
                    ),
                    'errorResponses' => array(
                        array('code' => 404, 'class' => '\Hough\Guzzle\Tests\Command\Guzzle\Asset\Exception\CustomCommandException')
                    )
                )
            ),
            'models' => array(
                'Foo' => array(
                    'type' => 'object',
                    'additionalProperties' => array(
                        'location' => 'json'
                    )
                )
            )
        ));

        $httpClient = new HttpClient(array('handler' => $mock));
        $client = new GuzzleClient($httpClient, $description);
        $client->foo(array('bar' => 'baz'));
    }

    public function testNotCreateExceptionIfDoesNotMatchCode()
    {
        $response = new Response(401);
        $mock = new MockHandler(array($response));

        $description = new Description(array(
            'name' => 'Test API',
            'baseUri' => 'http://httpbin.org',
            'operations' => array(
                'foo' => array(
                    'uri' => '/{foo}',
                    'httpMethod' => 'GET',
                    'responseClass' => 'Foo',
                    'parameters' => array(
                        'bar' => array(
                            'type'     => 'string',
                            'required' => true,
                            'description' => 'Unique user name (alphanumeric)',
                            'location' => 'json'
                        ),
                    ),
                    'errorResponses' => array(
                        array('code' => 404, 'class' => '\Hough\Guzzle\Tests\Command\Guzzle\Asset\Exception\CustomCommandException')
                    )
                )
            ),
            'models' => array(
                'Foo' => array(
                    'type' => 'object',
                    'additionalProperties' => array(
                        'location' => 'json'
                    )
                )
            )
        ));

        $httpClient = new HttpClient(array('handler' => $mock));
        $client = new GuzzleClient($httpClient, $description);
        $client->foo(array('bar' => 'baz'));
    }

    /**
     * @expectedException \Hough\Guzzle\Tests\Command\Guzzle\Asset\Exception\CustomCommandException
     */
    public function testCreateExceptionWithExactMatchOfReasonPhrase()
    {
        $response = new Response(404, array(), null, '1.1', 'Bar');
        $mock = new MockHandler(array($response));

        $description = new Description(array(
            'name' => 'Test API',
            'baseUri' => 'http://httpbin.org',
            'operations' => array(
                'foo' => array(
                    'uri' => '/{foo}',
                    'httpMethod' => 'GET',
                    'responseClass' => 'Foo',
                    'parameters' => array(
                        'bar' => array(
                            'type'     => 'string',
                            'required' => true,
                            'description' => 'Unique user name (alphanumeric)',
                            'location' => 'json'
                        ),
                    ),
                    'errorResponses' => array(
                        array('code' => 404, 'phrase' => 'Bar', 'class' => '\Hough\Guzzle\Tests\Command\Guzzle\Asset\Exception\CustomCommandException')
                    )
                )
            ),
            'models' => array(
                'Foo' => array(
                    'type' => 'object',
                    'additionalProperties' => array(
                        'location' => 'json'
                    )
                )
            )
        ));

        $httpClient = new HttpClient(array('handler' => $mock));
        $client = new GuzzleClient($httpClient, $description);
        $client->foo(array('bar' => 'baz'));
    }

    /**
     * @expectedException \Hough\Guzzle\Tests\Command\Guzzle\Asset\Exception\OtherCustomCommandException
     */
    public function testFavourMostPreciseMatch()
    {
        $response = new Response(404, array(), null, '1.1', 'Bar');
        $mock = new MockHandler(array($response));

        $description = new Description(array(
            'name' => 'Test API',
            'baseUri' => 'http://httpbin.org',
            'operations' => array(
                'foo' => array(
                    'uri' => '/{foo}',
                    'httpMethod' => 'GET',
                    'responseClass' => 'Foo',
                    'parameters' => array(
                        'bar' => array(
                            'type'     => 'string',
                            'required' => true,
                            'description' => 'Unique user name (alphanumeric)',
                            'location' => 'json'
                        ),
                    ),
                    'errorResponses' => array(
                        array('code' => 404, 'class' => '\Hough\Guzzle\Tests\Command\Guzzle\Asset\Exception\CustomCommandException'),
                        array('code' => 404, 'phrase' => 'Bar', 'class' => '\Hough\Guzzle\Tests\Command\Guzzle\Asset\Exception\OtherCustomCommandException'),
                    )
                )
            ),
            'models' => array(
                'Foo' => array(
                    'type' => 'object',
                    'additionalProperties' => array(
                        'location' => 'json'
                    )
                )
            )
        ));

        $httpClient = new HttpClient(array('handler' => $mock));
        $client = new GuzzleClient($httpClient, $description);
        $client->foo(array('bar' => 'baz'));
    }

    /**
     * @expectedException \Hough\Guzzle\Command\Exception\CommandException
     * @expectedExceptionMessage 404
     */
    public function testDoesNotAddResultWhenExceptionIsPresent()
    {
        $description = new Description(array(
            'operations' => array(
                'foo' => array(
                    'uri' => 'http://httpbin.org/{foo}',
                    'httpMethod' => 'GET',
                    'responseModel' => 'j',
                    'parameters' => array(
                        'bar' => array(
                            'type'     => 'string',
                            'required' => true,
                            'location' => 'uri'
                        )
                    )
                )
            ),
            'models' => array(
                'j' => array(
                    'type' => 'object'
                )
            )
        ));

        $mock = new MockHandler(array(new Response(404)));
        $stack = HandlerStack::create($mock);
        $httpClient = new HttpClient(array('handler' => $stack));
        $client = new GuzzleClient($httpClient, $description);
        $client->foo(array('bar' => 'baz'));
    }

    public function testReturnsExpectedResult()
    {
        $loginResponse = new Response(
            200,
            array(),
            '{
                "LoginResponse":{
                    "result":{
                        "type":4,
                        "username":{
                            "uid":38664492,
                            "content":"skyfillers-api-test"
                        },
                        "token":"3FB1F21014D630481D35CBC30CBF4043"
                    },
                    "status":{
                        "code":200,
                        "content":"OK"
                    }
                }
            }'
        );
        $mock = new MockHandler(array($loginResponse));

        $description = new Description(array(
            'name' => 'Test API',
            'baseUri' => 'http://httpbin.org',
            'operations' => array(
                'Login' => array(
                    'uri' => '/{foo}',
                    'httpMethod' => 'POST',
                    'responseClass' => 'LoginResponse',
                    'parameters' => array(
                        'username' => array(
                            'type'     => 'string',
                            'required' => true,
                            'description' => 'Unique user name (alphanumeric)',
                            'location' => 'json'
                        ),
                        'password' => array(
                            'type'     => 'string',
                            'required' => true,
                            'description' => 'User\'s password',
                            'location' => 'json'
                        ),
                        'response' => array(
                            'type'     => 'string',
                            'required' => false,
                            'description' => 'Determines the response type: xml = result content will be xml formatted (default); plain = result content will be simple text, without structure; json  = result content will be json formatted',
                            'location' => 'json'
                        ),
                        'token' => array(
                            'type'     => 'string',
                            'required' => false,
                            'description' => 'Provides the authentication token',
                            'location' => 'json'
                        )
                    )
                )
            ),
            'models' => array(
                'LoginResponse' => array(
                    'type' => 'object',
                    'additionalProperties' => array(
                        'location' => 'json'
                    )
                )
            )
        ));

        $httpClient = new HttpClient(array('handler' => $mock));
        $client = new GuzzleClient($httpClient, $description);
        $result = $client->Login(array(
            'username' => 'test',
            'password' => 'test',
            'response' => 'json',
        ));

        $expected = array(
            'result' => array(
                'type' => 4,
                'username' => array(
                    'uid' => 38664492,
                    'content' => 'skyfillers-api-test'
                ),
                'token' => '3FB1F21014D630481D35CBC30CBF4043'
            ),
            'status' => array(
                'code' => 200,
                'content' => 'OK'
            )
        );
        $this->assertArraySubset($expected, $result['LoginResponse']);
    }
}
