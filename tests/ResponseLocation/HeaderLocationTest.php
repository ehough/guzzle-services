<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\ResponseLocation;

use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\ResponseLocation\HeaderLocation;
use Hough\Guzzle\Command\Result;
use Hough\Psr7\Response;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\ResponseLocation\HeaderLocation
 * @covers \Hough\Guzzle\Command\Guzzle\ResponseLocation\AbstractLocation
 */
class HeaderLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group ResponseLocation
     */
    public function testVisitsLocation()
    {
        $location = new HeaderLocation();
        $parameter = new Parameter(array(
            'name'    => 'val',
            'sentAs'  => 'X-Foo',
            'filters' => array('strtoupper')
        ));
        $response = new Response(200, array('X-Foo' => 'bar'));
        $result = new Result();
        $result = $location->visit($result, $response, $parameter);
        $this->assertEquals('BAR', $result['val']);
    }
}
