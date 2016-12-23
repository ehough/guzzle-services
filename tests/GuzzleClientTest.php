<?php
namespace Hough\Guzzle\Tests\Command\Guzzle;

use Hough\Guzzle\Client as HttpClient;
use Hough\Guzzle\Command\CommandInterface;
use Hough\Guzzle\Command\Guzzle\Description;
use Hough\Guzzle\Command\Guzzle\GuzzleClient;
use Hough\Guzzle\Command\Result;
use Hough\Guzzle\Command\ResultInterface;
use Hough\Guzzle\Handler\MockHandler;
use Hough\Psr7\Request;
use Hough\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\GuzzleClient
 */
class GuzzleClientTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteCommandViaMagicMethod()
    {
        $client = $this->getServiceClient(
            array(
                new Response(200, array(), '{"foo":"bar"}'),
                new Response(200, array(), '{"foofoo":"barbar"}'),
            ),
            null,
            $this->commandToRequestTransformer()
        );

        // Synchronous
        $result1 = $client->doThatThingYouDo(array('fizz' => 'buzz'));
        $this->assertEquals('bar', $result1['foo']);
        $this->assertEquals('buzz', $result1['_request']['fizz']);
        $this->assertEquals('doThatThingYouDo', $result1['_request']['action']);

        // Asynchronous
        $result2 = $client->doThatThingOtherYouDoAsync(array('fizz' => 'buzz'))->wait();
        $this->assertEquals('barbar', $result2['foofoo']);
        $this->assertEquals('doThatThingOtherYouDo', $result2['_request']['action']);
    }

    public function testExecuteWithQueryLocation()
    {
        $mock = new MockHandler();
        $client = $this->getServiceClient(
            array(
                new Response(200, array(), '{"foo":"bar"}'),
                new Response(200, array(), '{"foo":"bar"}')
            ),
            $mock
        );

        $client->doQueryLocation(array('foo' => 'Foo'));
        $this->assertEquals('foo=Foo', $mock->getLastRequest()->getUri()->getQuery());

        $client->doQueryLocation(array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        ));
        $last = $mock->getLastRequest();
        $this->assertEquals('foo=Foo&bar=Bar&baz=Baz', $last->getUri()->getQuery());
    }

    public function testExecuteWithBodyLocation()
    {
        $mock = new MockHandler();

        $client = $this->getServiceClient(
            array(
                new Response(200, array(), '{"foo":"bar"}'),
                new Response(200, array(), '{"foo":"bar"}')
            ),
            $mock
        );

        $client->doBodyLocation(array('foo' => 'Foo'));
        $this->assertEquals('foo=Foo', (string) $mock->getLastRequest()->getBody());

        $client->doBodyLocation(array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        ));
        $this->assertEquals('foo=Foo&bar=Bar&baz=Baz', (string) $mock->getLastRequest()->getBody());
    }

    public function testExecuteWithJsonLocation()
    {
        $mock = new MockHandler();

        $client = $this->getServiceClient(
            array(
                new Response(200, array(), '{"foo":"bar"}'),
                new Response(200, array(), '{"foo":"bar"}')
            ),
            $mock
        );

        $client->doJsonLocation(array('foo' => 'Foo'));
        $this->assertEquals('{"foo":"Foo"}', (string) $mock->getLastRequest()->getBody());

        $client->doJsonLocation(array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        ));
        $this->assertEquals('{"foo":"Foo","bar":"Bar","baz":"Baz"}', (string) $mock->getLastRequest()->getBody());
    }

    public function testExecuteWithHeaderLocation()
    {
        $mock = new MockHandler();

        $client = $this->getServiceClient(
            array(
                new Response(200, array(), '{"foo":"bar"}'),
                new Response(200, array(), '{"foo":"bar"}')
            ),
            $mock
        );

        $client->doHeaderLocation(array('foo' => 'Foo'));
        $this->assertEquals(array('Foo'), $mock->getLastRequest()->getHeader('foo'));

        $client->doHeaderLocation(array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        ));
        $this->assertEquals(array('Foo'), $mock->getLastRequest()->getHeader('foo'));
        $this->assertEquals(array('Bar'), $mock->getLastRequest()->getHeader('bar'));
        $this->assertEquals(array('Baz'), $mock->getLastRequest()->getHeader('baz'));
    }

    public function testExecuteWithXmlLocation()
    {
        $mock = new MockHandler();

        $client = $this->getServiceClient(
            array(
                new Response(200, array(), '{"foo":"bar"}'),
                new Response(200, array(), '{"foo":"bar"}')
            ),
            $mock
        );

        $client->doXmlLocation(array('foo' => 'Foo'));
        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<Request><foo>Foo</foo></Request>\n",
            (string) $mock->getLastRequest()->getBody()
        );

        $client->doXmlLocation(array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        ));
        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<Request><foo>Foo</foo><bar>Bar</bar><baz>Baz</baz></Request>\n",
            $mock->getLastRequest()->getBody()
        );
    }
    
    public function testExecuteWithMultiPartLocation()
    {
        $mock = new MockHandler();

        $client = $this->getServiceClient(
            array(
                new Response(200, array(), '{"foo":"bar"}'),
                new Response(200, array(), '{"foo":"bar"}'),
                new Response(200, array(), '{"foo":"bar"}')
            ),
            $mock
        );

        $client->doMultiPartLocation(array('foo' => 'Foo'));
        $multiPartRequestBody = (string) $mock->getLastRequest()->getBody();
        $this->assertContains('name="foo"', $multiPartRequestBody);
        $this->assertContains('Foo', $multiPartRequestBody);

        $client->doMultiPartLocation(array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz'
        ));

        $multiPartRequestBody = (string) $mock->getLastRequest()->getBody();
        $this->assertContains('name="foo"', $multiPartRequestBody);
        $this->assertContains('Foo', $multiPartRequestBody);
        $this->assertContains('name="bar"', $multiPartRequestBody);
        $this->assertContains('Bar', $multiPartRequestBody);
        $this->assertContains('name="baz"', $multiPartRequestBody);
        $this->assertContains('Baz', $multiPartRequestBody);

        $client->doMultiPartLocation(array(
            'file' => fopen(dirname(__FILE__) . '/Asset/test.html', 'r'),
        ));
        $multiPartRequestBody = (string) $mock->getLastRequest()->getBody();
        $this->assertContains('name="file"', $multiPartRequestBody);
        $this->assertContains('filename="test.html"', $multiPartRequestBody);
        $this->assertContains('<title>Title</title>', $multiPartRequestBody);
    }

    public function testHasConfig()
    {
        $client = new HttpClient();
        $description = new Description(array());
        $guzzle = new GuzzleClient(
            $client,
            $description,
            $this->commandToRequestTransformer(),
            $this->responseToResultTransformer(),
            null,
            array('foo' => 'bar')
        );

        $this->assertSame($client, $guzzle->getHttpClient());
        $this->assertSame($description, $guzzle->getDescription());
        $this->assertEquals('bar', $guzzle->getConfig('foo'));
        $this->assertEquals(array(), $guzzle->getConfig('defaults'));
        $guzzle->setConfig('abc', 'listen');
        $this->assertEquals('listen', $guzzle->getConfig('abc'));
    }

    public function testAddsValidateHandlerWhenTrue()
    {
        $client = new HttpClient();
        $description = new Description(array());
        $guzzle = new GuzzleClient(
            $client,
            $description,
            $this->commandToRequestTransformer(),
            $this->responseToResultTransformer(),
            null,
            array(
                'validate' => true,
                'process' => false
            )
        );

        $handlers = explode("\n", $guzzle->getHandlerStack()->__toString());
        $handlers = array_filter($handlers);
        $this->assertCount(3, $handlers);
    }

    public function testDisablesHandlersWhenFalse()
    {
        $client = new HttpClient();
        $description = new Description(array());
        $guzzle = new GuzzleClient(
            $client,
            $description,
            $this->commandToRequestTransformer(),
            $this->responseToResultTransformer(),
            null,
            array(
                'validate' => false,
                'process' => false
            )
        );

        $handlers = explode("\n", $guzzle->getHandlerStack()->__toString());
        $handlers = array_filter($handlers);
        $this->assertCount(1, $handlers);
    }

    public function testValidateDescription()
    {
        $client = new HttpClient();
        $description = new Description(
            array(
                'name' => 'Testing API ',
                'baseUri' => 'http://httpbin.org/',
                'operations' => array(
                    'Foo' => array(
                        'httpMethod' => 'GET',
                        'uri' => '/get',
                        'parameters' => array(
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Bar',
                                'location' => 'query'
                            ),
                            'baz' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'baz',
                                'location' => 'query'
                            ),
                        ),
                        'responseModel' => 'Foo'
                    ),
                ),
                'models' => array(
                    'Foo' => array(
                        'type' => 'object',
                        'properties' => array(
                            'id' => array(
                                'location' => 'json',
                                'type' => 'string'
                            ),
                            'location' => array(
                                'location' => 'header',
                                'sentAs' => 'Location',
                                'type' => 'string'
                            ),
                            'age' => array(
                                'location' => 'json',
                                'type' => 'integer'
                            ),
                            'statusCode' => array(
                                'location' => 'statusCode',
                                'type' => 'integer'
                            ),
                        ),
                    ),
                ),
            )
        );

        $guzzle = new GuzzleClient(
            $client,
            $description,
            null,
            null,
            null,
            array(
                'validate' => true,
                'process' => false
            )
        );

        $command = $guzzle->getCommand('Foo', array('baz' => 'BAZ'));
        /** @var ResponseInterface $response */
        $response = $guzzle->execute($command);
        $this->assertInstanceOf('\Hough\Psr7\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @expectedException \Hough\Guzzle\Command\Exception\CommandException
     * @expectedExceptionMessage Validation errors: [baz] is a required string: baz
     */
    public function testValidateDescriptionFailsDueMissingRequiredParameter()
    {
        $client = new HttpClient();
        $description = new Description(
            array(
                'name' => 'Testing API ',
                'baseUri' => 'http://httpbin.org/',
                'operations' => array(
                    'Foo' => array(
                        'httpMethod' => 'GET',
                        'uri' => '/get',
                        'parameters' => array(
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Bar',
                                'location' => 'query'
                            ),
                            'baz' => array(
                                'type' => 'string',
                                'required' => true,
                                'description' => 'baz',
                                'location' => 'query'
                            ),
                        ),
                        'responseModel' => 'Foo'
                    ),
                ),
                'models' => array(
                    'Foo' => array(
                        'type' => 'object',
                        'properties' => array(
                            'id' => array(
                                'location' => 'json',
                                'type' => 'string'
                            ),
                            'location' => array(
                                'location' => 'header',
                                'sentAs' => 'Location',
                                'type' => 'string'
                            ),
                            'age' => array(
                                'location' => 'json',
                                'type' => 'integer'
                            ),
                            'statusCode' => array(
                                'location' => 'statusCode',
                                'type' => 'integer'
                            ),
                        ),
                    ),
                ),
            )
        );

        $guzzle = new GuzzleClient(
            $client,
            $description,
            null,
            null,
            null,
            array(
                'validate' => true,
                'process' => false
            )
        );

        $command = $guzzle->getCommand('Foo');
        /** @var ResultInterface $result */
        $result = $guzzle->execute($command);
        $this->assertInstanceOf('\Hough\Guzzle\Command\Result', $result);
        $result = $result->toArray();
        $this->assertEquals(200, $result['statusCode']);
    }

    /**
     * @expectedException \Hough\Guzzle\Command\Exception\CommandException
     * @expectedExceptionMessage Validation errors: [baz] must be of type integer
     */
    public function testValidateDescriptionFailsDueTypeMismatch()
    {
        $client = new HttpClient();
        $description = new Description(
            array(
                'name' => 'Testing API ',
                'baseUri' => 'http://httpbin.org/',
                'operations' => array(
                    'Foo' => array(
                        'httpMethod' => 'GET',
                        'uri' => '/get',
                        'parameters' => array(
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Bar',
                                'location' => 'query'
                            ),
                            'baz' => array(
                                'type' => 'integer',
                                'required' => true,
                                'description' => 'baz',
                                'location' => 'query'
                            ),
                        ),
                        'responseModel' => 'Foo'
                    ),
                ),
                'models' => array(
                    'Foo' => array(
                        'type' => 'object',
                        'properties' => array(
                            'id' => array(
                                'location' => 'json',
                                'type' => 'string'
                            ),
                            'location' => array(
                                'location' => 'header',
                                'sentAs' => 'Location',
                                'type' => 'string'
                            ),
                            'age' => array(
                                'location' => 'json',
                                'type' => 'integer'
                            ),
                            'statusCode' => array(
                                'location' => 'statusCode',
                                'type' => 'integer'
                            ),
                        ),
                    ),
                ),
            )
        );

        $guzzle = new GuzzleClient(
            $client,
            $description,
            null,
            null,
            null,
            array(
                'validate' => true,
                'process' => false
            )
        );

        $command = $guzzle->getCommand('Foo', array('baz' => 'Hello'));
        /** @var ResultInterface $result */
        $result = $guzzle->execute($command);
        $this->assertInstanceOf('\Hough\Guzzle\Command\Result', $result);
        $result = $result->toArray();
        $this->assertEquals(200, $result['statusCode']);
    }

    public function testValidateDescriptionDoesNotFailWhenSendingIntegerButExpectingString()
    {
        $client = new HttpClient();
        $description = new Description(
            array(
                'name' => 'Testing API ',
                'baseUri' => 'http://httpbin.org/',
                'operations' => array(
                    'Foo' => array(
                        'httpMethod' => 'GET',
                        'uri' => '/get',
                        'parameters' => array(
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Bar',
                                'location' => 'query'
                            ),
                            'baz' => array(
                                'type' => 'string',
                                'required' => true,
                                'description' => 'baz',
                                'location' => 'query'
                            ),
                        ),
                        'responseModel' => 'Foo'
                    ),
                ),
                'models' => array(
                    'Foo' => array(
                        'type' => 'object',
                        'properties' => array(
                            'id' => array(
                                'location' => 'json',
                                'type' => 'string'
                            ),
                            'location' => array(
                                'location' => 'header',
                                'sentAs' => 'Location',
                                'type' => 'string'
                            ),
                            'age' => array(
                                'location' => 'json',
                                'type' => 'integer'
                            ),
                            'statusCode' => array(
                                'location' => 'statusCode',
                                'type' => 'integer'
                            ),
                        ),
                    ),
                ),
            )
        );

        $guzzle = new GuzzleClient($client, $description);

        $command = $guzzle->getCommand('Foo', array('baz' => 42));
        /** @var ResultInterface $result */
        $result = $guzzle->execute($command);
        $this->assertInstanceOf('\Hough\Guzzle\Command\Result', $result);
        $result = $result->toArray();
        $this->assertEquals(200, $result['statusCode']);
    }

    public function testMagicMethodExecutesCommands()
    {
        $client = new HttpClient();
        $description = new Description(
            array(
                'name' => 'Testing API ',
                'baseUri' => 'http://httpbin.org/',
                'operations' => array(
                    'Foo' => array(
                        'httpMethod' => 'GET',
                        'uri' => '/get',
                        'parameters' => array(
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Bar',
                                'location' => 'query'
                            ),
                            'baz' => array(
                                'type' => 'string',
                                'required' => true,
                                'description' => 'baz',
                                'location' => 'query'
                            ),
                        ),
                        'responseModel' => 'Foo'
                    ),
                ),
                'models' => array(
                    'Foo' => array(
                        'type' => 'object',
                        'properties' => array(
                            'id' => array(
                                'location' => 'json',
                                'type' => 'string'
                            ),
                            'location' => array(
                                'location' => 'header',
                                'sentAs' => 'Location',
                                'type' => 'string'
                            ),
                            'age' => array(
                                'location' => 'json',
                                'type' => 'integer'
                            ),
                            'statusCode' => array(
                                'location' => 'statusCode',
                                'type' => 'integer'
                            ),
                        ),
                    ),
                ),
            )
        );

        $guzzle = $this->getMockBuilder('\Hough\Guzzle\Command\Guzzle\GuzzleClient')
            ->setConstructorArgs(array(
                $client,
                $description
            ))
            ->setMethods(array('execute'))
            ->getMock();

        $guzzle->expects($this->once())
            ->method('execute')
            ->will($this->returnValue('foo'));

        $this->assertEquals('foo', $guzzle->foo(array()));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No operation found named Foo
     */
    public function testThrowsWhenOperationNotFoundInDescription()
    {
        $client = new HttpClient();
        $description = new Description(array());
        $guzzle = new GuzzleClient(
            $client,
            $description,
            $this->commandToRequestTransformer(),
            $this->responseToResultTransformer()
        );
        $guzzle->getCommand('foo');
    }

    public function testReturnsProcessedResponse()
    {
        $client = new HttpClient();

        $description = new Description(
            array(
                'name' => 'Testing API ',
                'baseUri' => 'http://httpbin.org/',
                'operations' => array(
                    'Foo' => array(
                        'httpMethod' => 'GET',
                        'uri' => '/get',
                        'parameters' => array(
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Bar',
                                'location' => 'query'
                            ),
                            'baz' => array(
                                'type' => 'string',
                                'required' => true,
                                'description' => 'baz',
                                'location' => 'query'
                            ),
                        ),
                        'responseModel' => 'Foo'
                    ),
                ),
                'models' => array(
                    'Foo' => array(
                        'type' => 'object',
                        'properties' => array(
                            'id' => array(
                                'location' => 'json',
                                'type' => 'string'
                            ),
                            'location' => array(
                                'location' => 'header',
                                'sentAs' => 'Location',
                                'type' => 'string'
                            ),
                            'age' => array(
                                'location' => 'json',
                                'type' => 'integer'
                            ),
                            'statusCode' => array(
                                'location' => 'statusCode',
                                'type' => 'integer'
                            ),
                        ),
                    ),
                ),
            )
        );

        $guzzle = new GuzzleClient($client, $description, null, null);
        $command = $guzzle->getCommand('foo', array('baz' => 'BAZ'));

        /** @var ResultInterface $result */
        $result = $guzzle->execute($command);
        $this->assertInstanceOf('\Hough\Guzzle\Command\Result', $result);
        $result = $result->toArray();
        $this->assertEquals(200, $result['statusCode']);
    }

    private function getServiceClient(
        array $responses,
        MockHandler $mock = null,
        callable $commandToRequestTransformer = null
    ) {
        $mock = $mock ?: new MockHandler();

        foreach ($responses as $response) {
            $mock->append($response);
        }

        return new GuzzleClient(
            new HttpClient(array(
                'handler' => $mock
            )),
            $this->getDescription(),
            $commandToRequestTransformer,
            $this->responseToResultTransformer(),
            null,
            array('foo' => 'bar')
        );
    }

    private function commandToRequestTransformer()
    {
        return function (CommandInterface $command) {
            $data           = $command->toArray();
            $data['action'] = $command->getName();

            return new Request('POST', '/', array(), http_build_query($data));
        };
    }

    private function responseToResultTransformer()
    {
        return function (ResponseInterface $response, RequestInterface $request, CommandInterface $command) {
            $data = \Hough\Guzzle\json_decode($response->getBody(), true);
            parse_str($request->getBody(), $data['_request']);

            return new Result($data);
        };
    }

    private function getDescription()
    {
        return new Description(
            array(
                'name' => 'Testing API ',
                'baseUri' => 'http://httpbin.org/',
                'operations' => array(
                    'doThatThingYouDo' => array(
                        'responseModel' => 'Bar'
                    ),
                    'doThatThingOtherYouDo' => array(
                        'responseModel' => 'Foo'
                    ),
                    'doQueryLocation' => array(
                        'httpMethod' => 'GET',
                        'uri' => '/queryLocation',
                        'parameters' => array(
                            'foo' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing query request location',
                                'location' => 'query'
                            ),
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing query request location',
                                'location' => 'query'
                            ),
                            'baz' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing query request location',
                                'location' => 'query'
                            )
                        ),
                        'responseModel' => 'QueryResponse'
                    ),
                    'doBodyLocation' => array(
                        'httpMethod' => 'GET',
                        'uri' => '/bodyLocation',
                        'parameters' => array(
                            'foo' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing body request location',
                                'location' => 'body'
                            ),
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing body request location',
                                'location' => 'body'
                            ),
                            'baz' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing body request location',
                                'location' => 'body'
                            )
                        ),
                        'responseModel' => 'BodyResponse'
                    ),
                    'doJsonLocation' => array(
                        'httpMethod' => 'GET',
                        'uri' => '/jsonLocation',
                        'parameters' => array(
                            'foo' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing json request location',
                                'location' => 'json'
                            ),
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing json request location',
                                'location' => 'json'
                            ),
                            'baz' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing json request location',
                                'location' => 'json'
                            )
                        ),
                        'responseModel' => 'JsonResponse'
                    ),
                    'doHeaderLocation' => array(
                        'httpMethod' => 'GET',
                        'uri' => '/headerLocation',
                        'parameters' => array(
                            'foo' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing header request location',
                                'location' => 'header'
                            ),
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing header request location',
                                'location' => 'header'
                            ),
                            'baz' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing header request location',
                                'location' => 'header'
                            )
                        ),
                        'responseModel' => 'HeaderResponse'
                    ),
                    'doXmlLocation' => array(
                        'httpMethod' => 'GET',
                        'uri' => '/xmlLocation',
                        'parameters' => array(
                            'foo' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing xml request location',
                                'location' => 'xml'
                            ),
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing xml request location',
                                'location' => 'xml'
                            ),
                            'baz' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing xml request location',
                                'location' => 'xml'
                            )
                        ),
                        'responseModel' => 'XmlResponse'
                    ),
                    'doMultiPartLocation' => array(
                        'httpMethod' => 'POST',
                        'uri' => '/multipartLocation',
                        'parameters' => array(
                            'foo' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing multipart request location',
                                'location' => 'multipart'
                            ),
                            'bar' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing multipart request location',
                                'location' => 'multipart'
                            ),
                            'baz' => array(
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Testing multipart request location',
                                'location' => 'multipart'
                            ),
                            'file' => array(
                                'type' => 'any',
                                'required' => false,
                                'description' => 'Testing multipart request location',
                                'location' => 'multipart'
                            )
                        ),
                        'responseModel' => 'MultipartResponse'
                    ),
                ),
                'models'  => array(
                    'Foo' => array(
                        'type' => 'object',
                        'properties' => array(
                            'code' => array(
                                'location' => 'statusCode'
                            )
                        )
                    ),
                    'Bar' => array(
                        'type' => 'object',
                        'properties' => array(
                            'code' => array('
                                location' => 'statusCode'
                            )
                        )
                    )
                )
            )
        );
    }

    public function testDocumentationExampleFromReadme()
    {
        $client = new HttpClient();
        $description = new Description(array(
            'baseUrl' => 'http://httpbin.org/',
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

        $guzzle = new GuzzleClient($client, $description);

        $result = $guzzle->testing(array('foo' => 'bar'));
        $this->assertEquals('bar', $result['args']['foo']);
    }
}
