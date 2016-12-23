<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\RequestLocation;

use Hough\Guzzle\Command\Command;
use Hough\Guzzle\Command\Guzzle\Operation;
use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\RequestLocation\QueryLocation;
use Hough\Psr7;
use Hough\Psr7\Request;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\RequestLocation\QueryLocation
 * @covers \Hough\Guzzle\Command\Guzzle\RequestLocation\AbstractLocation
 */
class QueryLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group RequestLocation
     */
    public function testVisitsLocation()
    {
        $location = new QueryLocation();
        $command = new Command('foo', array('foo' => 'bar'));
        $request = new Request('POST', 'http://httbin.org');
        $param = new Parameter(array('name' => 'foo'));
        $request = $location->visit($command, $request, $param);

        $query = Psr7\parse_query($request->getUri()->getQuery());
        $this->assertEquals('bar', $query['foo']);
    }

    /**
     * @group RequestLocation
     */
    public function testAddsAdditionalProperties()
    {
        $location = new QueryLocation();
        $command = new Command('foo', array('foo' => 'bar'));
        $command['add'] = 'props';
        $operation = new Operation(array(
            'additionalParameters' => array(
                'location' => 'query'
            )
        ));
        $request = new Request('POST', 'http://httbin.org');
        $request = $location->after($command, $request, $operation);

        $query = Psr7\parse_query($request->getUri()->getQuery());
        $this->assertEquals('props', $query['add']);
    }
}
