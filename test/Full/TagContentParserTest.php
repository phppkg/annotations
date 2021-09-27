<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/3 0003
 * Time: 14:59
 */

namespace PhpPkg\Annotations\Test\Full;

use PHPUnit\Framework\TestCase;
use PhpPkg\Annotations\Full\TagContentParser;

/**
 * Class TagContentParserTest
 * @package PhpPkg\Annotations\Test\Full
 */
class TagContentParserTest extends TestCase
{
    /**
     */
    public function testBasicParse()
    {
        $data = TagContentParser::handle('val');
        $this->assertCount(1, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertSame('val', $data[0]);

        $data = TagContentParser::handle(0);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertSame(0, $data[0]);

        $data = TagContentParser::handle('');
        $this->assertCount(0, $data);
        $this->assertSame([], $data);

        $data = TagContentParser::handle('true');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]);

        $data = TagContentParser::handle('false');
        $this->assertCount(1, $data);
        $this->assertFalse($data[0]);
    }

    /**
     * @dataProvider simpleParseProvider
     * @param string $content
     */
    public function testSimpleParse($content)
    {
        $data = TagContentParser::handle($content);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertSame('val', $data[0]);
    }

    public function simpleParseProvider(): array
    {
        return [
            // -- 直接是值 @Tag(val) --
            ['val'],
            [' val'], // 前有空白
            ['val '], // 后有空白
            [' val '], // 前后有空白
            // -- 带有单引号 @Tag('val') --
            ["'val'"],
            ["' val'"],
            ["'val '"],
            ["' val'"],
            // 在新的一行@Tag('val
            //')
            ["'val\n'"],
            ["'val\t'"],
            ["'val\r\n'"],
            // -- 带有双引号 @Tag("val") --
            ['"val"'],
            ['" val"'],
            ['"val "'],
            ['" val "'],
            // 特殊的
            ["val\r"],
            ["val\t"],
            ["val\n"],
        ];
    }

    /**
     * @dataProvider KVParseProvider
     * @param string $content
     */
    public function testKVParse(string $content)
    {
        // k-v
        $data = TagContentParser::handle($content);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('key', $data);
        $this->assertSame('val', $data['key']);
    }

    public function KVParseProvider(): array
    {
        return [
            // @Tag(key=val)
            ['key=val'],
            ['key =val'],
            ['key= val'],
            ['key = val'],
            // @Tag(key='val')
            ["key='val'"],
            ["key ='val'"],
            ["key= 'val'"],
            ["key = 'val'"],
            // @Tag(key="val")
            ['key="val"'],
            ['key ="val"'],
            ['key= "val"'],
            ['key = "val"'],
        ];
    }

    /**
     * @dataProvider multiValParseProvider
     * @param string $content
     */
    public function testMultiValParse(string $content)
    {
        // multi val
        $data = TagContentParser::handle($content);
        $this->assertCount(2, $data);
        $this->assertSame(['val1', 'val2'], $data);
    }

    public function multiValParseProvider(): array
    {
        return [
            // @Tag(val1, val2)
            ['val1,val2'],
            ['val1 ,val2'],
            ['val1, val2'],
            ['val1 , val2'],
            ['val1, val2 '],
            [' val1, val2 '],
            // @Tag('val1', 'val2')
            ["'val1','val2'"],
            ["'val1', 'val2'"],
            ["'val1' ,'val2'"],
            ["'val1' , 'val2'"],
            ["'val1' , 'val2' "],
            // @Tag("val1", "val2")
            ['"val1","val2"'],
            ['"val1", "val2"'],
            ['"val1" ,"val2"'],
            ['"val1" , "val2"'],
            ['"val1" , "val2" '],
        ];
    }

    public function testAdvancedParse(): void
    {
        $data = TagContentParser::handle('val, val1, val2, val3');
        $this->assertCount(4, $data);
        $this->assertSame(['val', 'val1', 'val2', 'val3'], $data);

        $data = TagContentParser::handle('val1, type="val2"');
        $this->assertCount(2, $data);
        $this->assertArrayHasKey('type', $data);
        $this->assertSame(['val1', 'type' => 'val2'], $data);

        $data = TagContentParser::handle('val1, type="val2", val3, key4="val4"');
        $this->assertCount(4, $data);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('key4', $data);
        $this->assertSame(['val1', 'type' => 'val2', 'val3', 'key4' => 'val4'], $data);

        $data = TagContentParser::handle('{"sub-val"}');
        $this->assertCount(1, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertSame([['sub-val']], $data);

        $data = TagContentParser::handle('methods={"GET", "POST"}');
        $this->assertCount(1, $data);

        $data = TagContentParser::handle('method="GET", params={ym="[2-9]\d{5}"}');
        $this->assertCount(2, $data);
        $this->assertArrayHasKey('method', $data);
        $this->assertArrayHasKey('params', $data);
        $this->assertArrayHasKey('ym', $data['params']);
        $this->assertSame('[2-9]\d{5}', $data['params']['ym']);
    }
}
