<?php
namespace Hough\Guzzle\Tests\Command\Guzzle;

use Hough\Guzzle\Command\Command;
use Hough\Guzzle\Command\Guzzle\Description;
use Hough\Guzzle\Command\Guzzle\Serializer;
use Hough\Psr7\Request;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\Serializer
 */
class SerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testAllowsUriTemplates()
    {
        $description = new Description(array(
            'baseUri' => 'http://test.com',
            'operations' => array(
                'test' => array(
                    'httpMethod'         => 'GET',
                    'uri'                => '/api/{key}/foo',
                    'parameters'         => array(
                        'key' => array(
                            'required'  => true,
                            'type'      => 'string',
                            'location'  => 'uri'
                        ),
                    )
                )
            )
        ));

        $command = new Command('test', array('key' => 'bar'));
        $serializer = new Serializer($description);
        /** @var Request $request */
        $request = $serializer($command);
        $this->assertEquals('http://test.com/api/bar/foo', $request->getUri());
    }
}
