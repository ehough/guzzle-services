<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\RequestLocation;

use Hough\Guzzle\Command\Command;
use Hough\Guzzle\Command\Guzzle\Operation;
use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\RequestLocation\JsonLocation;
use Hough\Psr7\Request;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\RequestLocation\JsonLocation
 * @covers \Hough\Guzzle\Command\Guzzle\RequestLocation\AbstractLocation
 */
class JsonLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group RequestLocation
     */
    public function testVisitsLocation()
    {
        $location = new JsonLocation('json');
        $command = new Command('foo', array('foo' => 'bar'));
        $request = new Request('POST', 'http://httbin.org');
        $param = new Parameter(array('name' => 'foo'));
        $location->visit($command, $request, $param);
        $operation = new Operation();
        $request = $location->after($command, $request, $operation);
        $this->assertEquals('{"foo":"bar"}', $request->getBody()->getContents());
        $this->assertArraySubset(array(0 => 'application/json'), $request->getHeader('Content-Type'));
    }

    /**
     * @group RequestLocation
     */
    public function testVisitsAdditionalProperties()
    {
        $location = new JsonLocation('json', 'foo');
        $command = new Command('foo', array('foo' => 'bar'));
        $command['baz'] = array('bam' => array(1));
        $request = new Request('POST', 'http://httbin.org');
        $param = new Parameter(array('name' => 'foo'));
        $location->visit($command, $request, $param);
        $operation = new Operation(array(
            'additionalParameters' => array(
                'location' => 'json'
            )
        ));
        $request = $location->after($command, $request, $operation);
        $this->assertEquals('{"foo":"bar","baz":{"bam":[1]}}', $request->getBody()->getContents());
        $this->assertEquals(array(0 => 'foo'), $request->getHeader('Content-Type'));
    }

    /**
     * @group RequestLocation
     */
    public function testVisitsNestedLocation()
    {
        $location = new JsonLocation('json');
        $command = new Command('foo', array('foo' => 'bar'));
        $request = new Request('POST', 'http://httbin.org');
        $param = new Parameter(array(
            'name' => 'foo',
            'type' => 'object',
            'properties' => array(
                'baz' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'string',
                        'filters' => array('strtoupper')
                    )
                )
            ),
            'additionalProperties' => array(
                'type' => 'array',
                'items' => array(
                    'type' => 'string',
                    'filters' => array('strtolower')
                )
            )
        ));
        $command['foo'] = array(
            'baz' => array('a', 'b'),
            'bam' => array('A', 'B'),
        );
        $location->visit($command, $request, $param);
        $operation = new Operation();
        $request = $location->after($command, $request, $operation);
        $this->assertEquals('{"foo":{"baz":["A","B"],"bam":["a","b"]}}', (string) $request->getBody()->getContents());
        $this->assertEquals(array(0 => 'application/json'), $request->getHeader('Content-Type'));
    }
}
