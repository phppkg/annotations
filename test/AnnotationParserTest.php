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
    public function testFilterAndParseDocComment1()
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

        $this->assertCount(2, $tags[1]);
        $this->assertCount(4, $tags);

        $this->assertEquals('tag1', $tags[1][0]);
    }

    public function testFilterAndParseDocComment2()
    {
        $docBlock = <<<DOC
/**
 * @Inject()
 * @Scope(value=singleton1) @Scope(value=singleton2)
 * @Scope(value=singleton3)
 * @limits(start=10, limit=50)
 * @List(items={12,56,45,67})
 * @MultiLine(
 *   name="tom",
 *   id=345,
 *   status=false,
 *   map={a = v1,b = v2}
 * )
 * @type(json)
 * @Response(type=json)
 * @Apostrophe(type='json')
 * @DoubleQuotes(type="value2")
 * @Route(route="{id}", method="GET", params={"id"="[1-9]\d*"})
 * @Route(route="{alias}", method="GET", params={"alias"="[a-zA-Z][\w-]+(?:.html)?"})
 */
DOC;
        $str = AnnotationParser::filterDocComment($docBlock);

        $this->assertStringStartsWith('@Inject', $str);
        $this->assertStringEndsWith('})', $str);

        $tags = AnnotationParser::parseToTagStrings($str);

        $this->assertArrayHasKey(1, $tags);
        $this->assertArrayHasKey(2, $tags);

    }
}
