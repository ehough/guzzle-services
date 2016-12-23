<?php
namespace Hough\Guzzle\Tests\Command\Guzzle\RequestLocation;

use Hough\Guzzle\Command\Command;
use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\RequestLocation\BodyLocation;
use Hough\Psr7\Request;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\RequestLocation\BodyLocation
 */
class BodyLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group RequestLocation
     */
    public function testVisitsLocation()
    {
        $location = new BodyLocation('body');
        $command = new Command('foo', array('foo' => 'bar'));
        $request = new Request('POST', 'http://httbin.org');
        $param = new Parameter(array('name' => 'foo'));
        $request = $location->visit($command, $request, $param);
        $this->assertEquals('foo=bar', $request->getBody()->getContents());
    }
}
