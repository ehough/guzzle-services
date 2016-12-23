<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\ResponseLocation;

use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\ResponseLocation\StatusCodeLocation;
use Hough\Guzzle\Command\Result;
use Hough\Psr7\Response;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\ResponseLocation\StatusCodeLocation
 * @covers \Hough\Guzzle\Command\Guzzle\ResponseLocation\AbstractLocation
 */
class StatusCodeLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group ResponseLocation
     */
    public function testVisitsLocation()
    {
        $location = new StatusCodeLocation();
        $parameter = new Parameter(array('name' => 'val'));
        $response = new Response(200);
        $result = new Result();
        $result = $location->visit($result, $response, $parameter);
        $this->assertEquals(200, $result['val']);
    }
}
