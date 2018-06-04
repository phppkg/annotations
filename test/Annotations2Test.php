<?php

namespace Ulue\Annotations\Test;

use PHPUnit\Framework\TestCase;
use Ulue\Annotations\AnnotationParser;
use Ulue\Annotations\Annotations;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-06-05 at 10:33:34.
 */
class Annotations2Test extends TestCase
{
    /**
     * @var Annotations
     */
    private $annotations;

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
     * @covers \Ulue\Annotations\Annotations::getClassAnnotations
     * @throws \ReflectionException
     */
    public function testGetClassAnnotations()
    {
        $result = $this->annotations->getClassAnnotations(\Group::class, true);
        // Note.- that reserved doc annotations like author, version, etc. will be ommitted
        //        the user class has @author and @version annotations keys, and them should be ommited

        $expected = [
            'ChangeTrackingPolicy' => [
                0 => 'NOTIFY'
            ],
            'InheritanceType' => [
                0 => 'JOINED'
            ],
            'DiscriminatorColumn' => [
                'name' => 'discr',
                'type' => ' string '
            ],
            'Table' => [
                'name' => 'ecommerce_products',
                'indexes' => [
                    'name' => 'sexarch_idx',
                    'column' => 'name'
                ],
                'variant' => false
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers \Ulue\Annotations\Annotations::getMethodAnnotations
     */
    public function testGetMethodAnnotations()
    {
        AnnotationParser::setAllowMultiTags(['Attribute', 'testAll']);

        $result = $this->annotations->getMethodAnnotations(\Group::class, 'build');
        // Note.- that reserved doc annotations like author, version, etc. will be ommitted
        //        the build method has @param and @return annotations keys, and them should be ommited

        $expected = [
            'Attribute' => [
                0 => 'firstname',
                1 => 'lastname'
            ],

            'Cache' => [
                0 => [
                    'max_time' => 50
                ]
            ],
            'testAll' => [
                0 => [
                    'bool_var' => false,
                    'int_var' => 12345,
                    'float_var' => 12345.6789,
                    'str_var' => 'hello',
                    'str_woq' => 'word',
                    'str_wq' => 'hello word'
                ],
                1 => [
                    'name' => 'erik',
                    'age' => 27,
                    'address' => [
                        'city' => 'La paz',
                        'country' => 'Bolivia',
                        'avenue' => 'El Prado',
                        'building' => 'Alameda',
                        'floor' => 15,
                        'dep_num' => 7
                    ],
                    'phone' => 1234567890
                ]
            ],
            'Description' => [
                0 => 'Your system 升级难道每次都要卸载重装？'
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testErrorMissingCloseBrace()
    {
        $this->annotations->getMethodAnnotations(\Group::class, 'errFunc1');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testErrorMissingCloseQuote()
    {
        $this->annotations->getMethodAnnotations(\Group::class, 'errFunc2');
    }

    public function testComplexString()
    {
        $result = $this->annotations->getMethodAnnotations(\Group::class, 'shouldWorks', true);
        $expected = [
            'sample' => [
                'err_var' => '1 + 1 = 2, 2+2 = 4',
                'test_var' => 'log text, {0}={1} to params...',
                'sample' => [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3
                ]
            ]
        ];
        $this->assertEquals($expected, $result);
    }
}

