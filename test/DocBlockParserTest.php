<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/4 0004
 * Time: 23:40
 */

namespace PhpComLab\Annotations\Test;

use PHPUnit\Framework\TestCase;
use PhpComLab\Annotations\DocBlockParser;
use function vdump;

/**
 * Class DocBlockParserTest
 * @package PhpComLab\Annotations\Test
 * @covers \PhpComLab\Annotations\DocBlockParser
 */
class DocBlockParserTest extends TestCase
{
    public function testFilterAndParseDocComment1(): void
    {
        $str = <<<DOC
/**
 * Generate console command class
 * @usage {fullCommand} NAME SAVE_DIR [--option ...]
 * @arguments
 *  name       The class name, don't need suffix and ext.(eg. <info>demo</info>)
 *  dir        The class file save dir(default: <info>@app/Console/Command</info>)
 * @options
 *  --output       Setting the routes file(app/routes.php)
 *  -y, --yes      Whether display goon tips message.
 * @example
 *  <info>{fullCommand} demo</info>        Gen DemoCommand class to `@app/Console/Command`
 * @param  \Inhere\Console\IO\Input \$in
 * @param  \Inhere\Console\IO\Output \$out
 * @return int|mixed
 * @throws \ReflectionException
 */
DOC;
        DocBlockParser::notIgnoreTags(['example']);
        $tags = DocBlockParser::make()->parseToTagStrings($str);
vdump($tags);
        $this->assertCount(2, $tags[1]);
        $this->assertCount(5, $tags);

        $this->assertEquals('usage', $tags[1][0]);
    }
}
