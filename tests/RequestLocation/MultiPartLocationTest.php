<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\RequestLocation;

use Hough\Guzzle\Command\Command;
use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\RequestLocation\MultiPartLocation;
use Hough\Guzzle\Command\Guzzle\RequestLocation\PostFileLocation;
use Hough\Psr7\Request;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\RequestLocation\MultiPartLocation
 */
class MultiPartLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group RequestLocation
     */
    public function testVisitsLocation()
    {
        $location = new MultiPartLocation();
        $command = new Command('foo', array('foo' => 'bar'));
        $request = new Request('POST', 'http://httbin.org', array());
        $param = new Parameter(array('name' => 'foo'));
        $request = $location->visit($command, $request, $param);
        $actual = $request->getBody()->getContents();

        $this->assertNotFalse(strpos($actual, 'name="foo"'));
        $this->assertNotFalse(strpos($actual, 'bar'));
    }
}
