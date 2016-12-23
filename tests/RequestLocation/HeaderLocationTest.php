<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\RequestLocation;

use Hough\Guzzle\Command\Command;
use Hough\Guzzle\Command\Guzzle\Operation;
use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\RequestLocation\HeaderLocation;
use Hough\Psr7\Request;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\RequestLocation\HeaderLocation
 * @covers \Hough\Guzzle\Command\Guzzle\RequestLocation\AbstractLocation
 */
class HeaderLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group RequestLocation
     */
    public function testVisitsLocation()
    {
        $location = new HeaderLocation('header');
        $command = new Command('foo', array('foo' => 'bar'));
        $request = new Request('POST', 'http://httbin.org');
        $param = new Parameter(array('name' => 'foo'));
        $request = $location->visit($command, $request, $param);

        $header = $request->getHeader('foo');
        $this->assertTrue(is_array($header));
        $this->assertArraySubset(array(0 => 'bar'), $request->getHeader('foo'));
    }

    /**
     * @group RequestLocation
     */
    public function testAddsAdditionalProperties()
    {
        $location = new HeaderLocation('header');
        $command = new Command('foo', array('foo' => 'bar'));
        $command['add'] = 'props';
        $operation = new Operation(array(
            'additionalParameters' => array(
                'location' => 'header'
            )
        ));
        $request = new Request('POST', 'http://httbin.org');
        $request = $location->after($command, $request, $operation);

        $header = $request->getHeader('add');
        $this->assertTrue(is_array($header));
        $this->assertArraySubset(array(0 => 'props'), $header);
    }
}
