<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/4 0004
 * Time: 00:01
 */

require dirname(__DIR__) . '/test/boot.php';

// $data = \Ulue\Annotations\Full\TagContentParser::handle('{"sub-val"}');

// $data = \Ulue\Annotations\Full\TagContentParser::handle('val, k=val1, val2, val3');
$data = \Ulue\Annotations\Full\TagContentParser::handle('"id"="[1-9]\d*"');
\var_dump($data);
