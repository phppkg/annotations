<?php
namespace Ulue\Annotations\Test;

use PHPUnit\Framework\TestCase;
use Ulue\Annotations\Annotations;
use Base\Annotation\PermissionAnnotation;
use Base\Annotation\RoleAnnotation;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-07-01 at 09:26:44.
 */
class AnnotationsTest extends TestCase
{
    /**
     * @var Annotations
     */
    protected $annotations;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->annotations = new Annotations;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Annotations::setDefaultNamespace
     */
    public function testSetDefaultNamespace()
    {
        $this->annotations->setDefaultNamespace('\Base\Annotation\\');
        $this->annotations->setStrict(true);

        $this->assertEquals('\Base\Annotation\\', $this->annotations->getDefaultAnnotationNamespace());

        return $this->annotations;
    }

    /**
     * @covers  Annotations::getClassAnnotations
     * @depends testSetDefaultNamespace
     * @param Annotations $annotations
     * @throws \ReflectionException
     */
    public function testGetClassAnnotations($annotations)
    {
        $result = $annotations->getClassAnnotations('User');

        $expected = Array(
            'Defaults' => Array(
                0 => Array(
                    'name' => 'user1',
                    'lastname' => 'sample',
                    'age' => 0,
                    'address' => Array(
                        'country' => 'USA',
                        'state' => 'NY'
                    ),
                    'phone' => '000-00000000'
                )
            ),
            'assertResult' => Array(
                0 => false
            ),
            'cache' => Array(
                0 => Array(
                    'collation' => 'UTF-8'
                )
            )
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Annotations::getMethodAnnotations
     * @param Annotations $annotations
     * @depends testSetDefaultNamespace
     */
    public function testGetMethodAnnotations($annotations)
    {
        $result = $annotations->getMethodAnnotations('User', 'load');

        $expected = Array(
            'cache' => array(0 => true),
            'type' => array(0 => 'json'),
            'limits' => array(
                0 => array(
                    'start' => 10,
                    'limit' => 50
                )
            )
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Annotations::getMethodAnnotationsObjects
     * @param Annotations $annotations
     * @depends testSetDefaultNamespace
     */
    public function testGetMethodAnnotationsObjects($annotations)
    {
        $result = $annotations->getMethodAnnotationsObjects('User', 'create');
        $expected = array();

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('Permission', $result);
        $this->assertArrayHasKey('Role', $result);
        $this->assertInstanceOf(PermissionAnnotation::class, $result['Permission']);
        $this->assertInstanceOf(RoleAnnotation::class, $result['Role']);
    }
}

