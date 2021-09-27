<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/4 0004
 * Time: 00:01
 */

require dirname(__DIR__) . '/test/boot.php';

// $data = \PhpPkg\Annotations\Full\TagContentParser::handle('{"sub-val"}');

// $data = \PhpPkg\Annotations\Full\TagContentParser::handle('val, k=val1, val2, val3');
// $data = \PhpPkg\Annotations\Full\TagContentParser::handle('"id"="[1-9]\d*"');
// $data = \PhpPkg\Annotations\Full\TagContentParser::handle('"alias"="[a-zA-Z][\w-]+(?:.html)?"');
// $data = \PhpPkg\Annotations\Full\TagContentParser::handle('route="{alias}", method="GET", params={"alias"="[a-zA-Z][\w-]+(?:.html)?"}');
//  * @Route(path="/blog/archive/{ym}", method="GET", params={ym="[2-9]\d{5}"})

$data = \PhpPkg\Annotations\Full\TagContentParser::handle('method="GET", params={ym="[2-9]\d{5}"}');
\var_dump($data);
