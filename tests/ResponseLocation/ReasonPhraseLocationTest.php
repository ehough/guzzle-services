<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\ResponseLocation;

use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\ResponseLocation\ReasonPhraseLocation;
use Hough\Guzzle\Command\Result;
use Hough\Psr7\Response;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\ResponseLocation\ReasonPhraseLocation
 * @covers \Hough\Guzzle\Command\Guzzle\ResponseLocation\AbstractLocation
 */
class ReasonPhraseLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group ResponseLocation
     */
    public function testVisitsLocation()
    {
        $location = new ReasonPhraseLocation();
        $parameter = new Parameter(array(
            'name' => 'val',
            'filters' => array('strtolower')
        ));
        $response = new Response(200);
        $result = new Result();
        $result = $location->visit($result, $response, $parameter);
        $this->assertEquals('ok', $result['val']);
    }
}
