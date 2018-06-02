<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/2 0002
 * Time: 11:23
 */

namespace Ulue\Annotations\Test;

use PHPUnit\Framework\TestCase;
use Ulue\Annotations\AnnotationParser;

/**
 * Class AnnotationParserTest
 * @package Ulue\Annotations\Test
 * @covers \Ulue\Annotations\AnnotationParser
 */
class AnnotationParserTest extends TestCase
{
    public function testFilterDocComment()
    {
        $docBlock = <<<DOC
/**
 * doc-block description text
 *
 * @tag0(arg0=val0, des=tag description text)
 * @tag1(arg0=val0, des="tag description text
 *  the second line message
 * ")
 * @tag2(arg0=val0, des="tag description \"text
 *  complex text
 * [
 *  'tag0' => [arg0 => val0, arg1 => val1, ...]
 * ]
 * ")
 * @tag3(arg0=val0, des=tag description text)
 * @throws \InvalidArgumentException
 */
DOC;

        $str = AnnotationParser::filterDocComment($docBlock);

        $this->assertNotContains('*', $str);

        $tags = AnnotationParser::parseToTagStrings($str);

        $this->assertArrayHasKey(1, $tags);
        $this->assertArrayHasKey(2, $tags);

        $this->assertCount(4, $tags[1]);
        $this->assertCount(4, $tags[2]);

        $this->assertEquals('tag0', $tags[1][0]);
    }
}
