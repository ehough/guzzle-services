<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\RequestLocation;

use Hough\Guzzle\Command\Command;
use Hough\Guzzle\Command\Guzzle\Operation;
use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\RequestLocation\FormParamLocation;
use Hough\Guzzle\Command\Guzzle\RequestLocation\PostFieldLocation;
use Hough\Psr7\Request;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\RequestLocation\FormParamLocation
 * @covers \Hough\Guzzle\Command\Guzzle\RequestLocation\AbstractLocation
 */
class FormParamLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group RequestLocation
     */
    public function testVisitsLocation()
    {
        $location = new FormParamLocation();
        $command = new Command('foo', array('foo' => 'bar'));
        $request = new Request('POST', 'http://httbin.org');
        $param = new Parameter(array('name' => 'foo'));
        $request = $location->visit($command, $request, $param);
        $operation = new Operation();
        $request = $location->after($command, $request, $operation);
        $this->assertEquals('foo=bar', $request->getBody()->getContents());
        $this->assertArraySubset(array(0 => 'application/x-www-form-urlencoded; charset=utf-8'), $request->getHeader('Content-Type'));
    }

    /**
     * @group RequestLocation
     */
    public function testAddsAdditionalProperties()
    {
        $location = new FormParamLocation();
        $command = new Command('foo', array('foo' => 'bar'));
        $command['add'] = 'props';
        $request = new Request('POST', 'http://httbin.org', array());
        $param = new Parameter(array('name' => 'foo'));
        $request = $location->visit($command, $request, $param);
        $operation = new Operation(array(
            'additionalParameters' => array(
                'location' => 'formParam'
            )
        ));
        $request = $location->after($command, $request, $operation);
        $this->assertEquals('foo=bar&add=props', $request->getBody()->getContents());
    }
}
