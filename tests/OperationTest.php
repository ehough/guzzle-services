<?php
namespace Guzzle\Tests\Service\Description;

use Hough\Guzzle\Command\Guzzle\Description;
use Hough\Guzzle\Command\Guzzle\Operation;

/**
 * @covers \Hough\Guzzle\Command\Guzzle\Operation
 */
class OperationTest extends \PHPUnit_Framework_TestCase
{
    public static function strtoupper($string)
    {
        return strtoupper($string);
    }

    public function testOperationIsDataObject()
    {
        $c = new Operation(array(
            'name'               => 'test',
            'summary'            => 'doc',
            'notes'              => 'notes',
            'documentationUrl'   => 'http://www.example.com',
            'httpMethod'         => 'POST',
            'uri'                => '/api/v1',
            'responseModel'      => 'abc',
            'deprecated'         => true,
            'parameters'         => array(
                'key' => array(
                    'required'  => true,
                    'type'      => 'string',
                    'maxLength' => 10,
                    'name'      => 'key'
                ),
                'key_2' => array(
                    'required' => true,
                    'type'     => 'integer',
                    'default'  => 10,
                    'name'     => 'key_2'
                )
            )
        ));

        $this->assertEquals('test', $c->getName());
        $this->assertEquals('doc', $c->getSummary());
        $this->assertEquals('http://www.example.com', $c->getDocumentationUrl());
        $this->assertEquals('POST', $c->getHttpMethod());
        $this->assertEquals('/api/v1', $c->getUri());
        $this->assertEquals('abc', $c->getResponseModel());
        $this->assertTrue($c->getDeprecated());

        $params = array_map(function ($c) {
            return $c->toArray();
        }, $c->getParams());

        $this->assertEquals(array(
            'key' => array(
                'required'  => true,
                'type'      => 'string',
                'maxLength' => 10,
                'name'       => 'key'
            ),
            'key_2' => array(
                'required' => true,
                'type'     => 'integer',
                'default'  => 10,
                'name'     => 'key_2'
            )
        ), $params);

        $this->assertEquals(array(
            'required' => true,
            'type'     => 'integer',
            'default'  => 10,
            'name'     => 'key_2'
        ), $c->getParam('key_2')->toArray());

        $this->assertNull($c->getParam('afefwef'));
        $this->assertArrayNotHasKey('parent', $c->getParam('key_2')->toArray());
    }

    public function testDeterminesIfHasParam()
    {
        $command = $this->getTestCommand();
        $this->assertTrue($command->hasParam('data'));
        $this->assertFalse($command->hasParam('baz'));
    }

    protected function getTestCommand()
    {
        return new Operation(array(
            'parameters' => array(
                'data' => array('type' => 'string')
            )
        ));
    }

    public function testAddsNameToParametersIfNeeded()
    {
        $command = new Operation(array('parameters' => array('foo' => array())));
        $this->assertEquals('foo', $command->getParam('foo')->getName());
    }

    public function testContainsApiErrorInformation()
    {
        $command = $this->getOperation();
        $this->assertEquals(1, count($command->getErrorResponses()));
    }

    public function testHasNotes()
    {
        $o = new Operation(array('notes' => 'foo'));
        $this->assertEquals('foo', $o->getNotes());
    }

    public function testHasData()
    {
        $o = new Operation(array('data' => array('foo' => 'baz', 'bar' => 123)));
        $this->assertEquals('baz', $o->getData('foo'));
        $this->assertEquals(123, $o->getData('bar'));
        $this->assertNull($o->getData('wfefwe'));
        $this->assertEquals(array('foo' => 'baz', 'bar' => 123), $o->getData());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMesssage Parameters must be arrays
     */
    public function testEnsuresParametersAreArrays()
    {
        new Operation(array('parameters' => array('foo' => true)));
    }

    public function testHasDescription()
    {
        $s = new Description(array());
        $o = new Operation(array(), $s);
        $this->assertSame($s, $o->getServiceDescription());
    }

    public function testHasAdditionalParameters()
    {
        $o = new Operation(array(
            'additionalParameters' => array(
                'type' => 'string', 'name' => 'binks',
            ),
            'parameters' => array(
                'foo' => array('type' => 'integer'),
            ),
        ));
        $this->assertEquals('string', $o->getAdditionalParameters()->getType());
    }

    /**
     * @return Operation
     */
    protected function getOperation()
    {
        return new Operation(array(
            'name'       => 'OperationTest',
            'class'      => get_class($this),
            'parameters' => array(
                'test'          => array('type' => 'object'),
                'bool_1'        => array('default' => true, 'type' => 'boolean'),
                'bool_2'        => array('default' => false),
                'float'         => array('type' => 'numeric'),
                'int'           => array('type' => 'integer'),
                'date'          => array('type' => 'string'),
                'timestamp'     => array('type' => 'string'),
                'string'        => array('type' => 'string'),
                'username'      => array('type' => 'string', 'required' => true, 'filters' => 'strtolower'),
                'test_function' => array('type' => 'string', 'filters' => __CLASS__ . '::strtoupper'),
            ),
            'errorResponses' => array(
                array(
                    'code' => 503,
                    'reason' => 'InsufficientCapacity',
                    'class' => 'Guzzle\\Exception\\RuntimeException',
                ),
            ),
        ));
    }

    public function testCanExtendFromOtherOperations()
    {
        $d = new Description(array(
            'operations' => array(
                'A' => array(
                    'parameters' => array(
                        'A' => array(
                            'type' => 'object',
                            'properties' => array('foo' => array('type' => 'string'))
                        ),
                        'B' => array('type' => 'string')
                    ),
                    'summary' => 'foo'
                ),
                'B' => array(
                    'extends' => 'A',
                    'summary' => 'Bar'
                ),
                'C' => array(
                    'extends' => 'B',
                    'summary' => 'Bar',
                    'parameters' => array(
                        'B' => array('type' => 'number')
                    )
                )
            )
        ));

        $a = $d->getOperation('A');
        $this->assertEquals('foo', $a->getSummary());
        $this->assertTrue($a->hasParam('A'));
        $this->assertEquals('string', $a->getParam('B')->getType());

        $b = $d->getOperation('B');
        $this->assertTrue($a->hasParam('A'));
        $this->assertEquals('Bar', $b->getSummary());
        $this->assertEquals('string', $a->getParam('B')->getType());

        $c = $d->getOperation('C');
        $this->assertTrue($a->hasParam('A'));
        $this->assertEquals('Bar', $c->getSummary());
        $this->assertEquals('number', $c->getParam('B')->getType());
    }
}
