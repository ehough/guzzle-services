<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\ResponseLocation;

use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\ResponseLocation\BodyLocation;
use Hough\Guzzle\Command\Result;
use Hough\Psr7\Response;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\ResponseLocation\BodyLocation
 * @covers \Hough\Guzzle\Command\Guzzle\ResponseLocation\AbstractLocation
 */
class BodyLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group ResponseLocation
     */
    public function testVisitsLocation()
    {
        $location = new BodyLocation();
        $parameter = new Parameter(array(
            'name'    => 'val',
            'filters' => array('strtoupper')
        ));
        $response = new Response(200, array(), 'foo');
        $result = new Result();
        $result = $location->visit($result, $response, $parameter);
        $this->assertEquals('FOO', $result['val']);
    }
}
