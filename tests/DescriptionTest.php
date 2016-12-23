<?php
namespace Hough\Guzzle\Tests\Command\Guzzle;

use Hough\Guzzle\Command\Guzzle\Description;
use Hough\Guzzle\Command\Guzzle\Operation;
use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\Guzzle\SchemaFormatter;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\Description
 */
class DescriptionTest extends \PHPUnit_Framework_TestCase
{
    protected $operations;

    public function setup()
    {
        $this->operations = array(
            'test_command' => array(
                'name'        => 'test_command',
                'description' => 'documentationForCommand',
                'httpMethod'  => 'DELETE',
                'class'       => 'FooModel',
                'parameters'  => array(
                    'bucket'  => array('required' => true),
                    'key'     => array('required' => true)
                )
            )
        );
    }

    public function testConstructor()
    {
        $service = new Description(array('operations' => $this->operations));
        $this->assertEquals(1, count($service->getOperations()));
        $this->assertFalse($service->hasOperation('foobar'));
        $this->assertTrue($service->hasOperation('test_command'));
    }

    public function testContainsModels()
    {
        $d = new Description(array(
            'operations' => array('foo' => array()),
            'models' => array(
                'Tag'    => array('type' => 'object'),
                'Person' => array('type' => 'object')
            )
        ));
        $this->assertTrue($d->hasModel('Tag'));
        $this->assertTrue($d->hasModel('Person'));
        $this->assertFalse($d->hasModel('Foo'));
        $this->assertInstanceOf('\Hough\Guzzle\Command\Guzzle\Parameter', $d->getModel('Tag'));
        $this->assertEquals(array('Tag', 'Person'), array_keys($d->getModels()));
    }

    public function testCanUseResponseClass()
    {
        $d = new Description(array(
            'operations' => array(
                'foo' => array('responseClass' => 'Tag')
            ),
            'models' => array('Tag' => array('type' => 'object'))
        ));
        $op = $d->getOperation('foo');
        $this->assertNotNull($op->getResponseModel());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRetrievingMissingModelThrowsException()
    {
        $d = new Description(array());
        $d->getModel('foo');
    }

    public function testHasAttributes()
    {
        $d = new Description(array(
            'operations'  => array(),
            'name'        => 'Name',
            'description' => 'Description',
            'apiVersion'  => '1.24'
        ));

        $this->assertEquals('Name', $d->getName());
        $this->assertEquals('Description', $d->getDescription());
        $this->assertEquals('1.24', $d->getApiVersion());
    }

    public function testPersistsCustomAttributes()
    {
        $data = array(
            'operations'  => array('foo' => array('class' => 'foo', 'parameters' => array())),
            'name'        => 'Name',
            'description' => 'Test',
            'apiVersion'  => '1.24',
            'auth'        => 'foo',
            'keyParam'    => 'bar'
        );
        $d = new Description($data);
        $this->assertEquals('foo', $d->getData('auth'));
        $this->assertEquals('bar', $d->getData('keyParam'));
        $this->assertEquals(array('auth' => 'foo', 'keyParam' => 'bar'), $d->getData());
        $this->assertNull($d->getData('missing'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionForMissingOperation()
    {
        $s = new Description(array());
        $this->assertNull($s->getOperation('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidatesOperationTypes()
    {
        new Description(array(
            'operations' => array('foo' => new \stdClass())
        ));
    }

    public function testHasbaseUrl()
    {
        $description = new Description(array('baseUrl' => 'http://foo.com'));
        $this->assertEquals('http://foo.com', $description->getBaseUri());
    }

    public function testHasbaseUri()
    {
        $description = new Description(array('baseUri' => 'http://foo.com'));
        $this->assertEquals('http://foo.com', $description->getBaseUri());
    }

    public function testModelsHaveNames()
    {
        $desc = array(
            'models' => array(
                'date' => array('type' => 'string'),
                'user'=> array(
                    'type' => 'object',
                    'properties' => array(
                        'dob' => array('$ref' => 'date')
                    )
                )
            )
        );

        $s = new Description($desc);
        $this->assertEquals('string', $s->getModel('date')->getType());
        $this->assertEquals('dob', $s->getModel('user')->getProperty('dob')->getName());
    }

    public function testHasOperations()
    {
        $desc = array('operations' => array('foo' => array('parameters' => array('foo' => array(
            'name' => 'foo'
        )))));
        $s = new Description($desc);
        $this->assertInstanceOf('\Hough\Guzzle\Command\Guzzle\Operation', $s->getOperation('foo'));
        $this->assertSame($s->getOperation('foo'), $s->getOperation('foo'));
    }

    public function testHasFormatter()
    {
        $s = new Description(array());
        $this->assertNotEmpty($s->format('date', 'now'));
    }

    public function testCanUseCustomFormatter()
    {
        $formatter = $this->getMockBuilder('\Hough\Guzzle\Command\Guzzle\SchemaFormatter')
            ->setMethods(array('format'))
            ->getMock();
        $formatter->expects($this->once())
            ->method('format');
        $s = new Description(array(), array('formatter' => $formatter));
        $s->format('time', 'now');
    }
}
