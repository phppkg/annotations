<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/5/31 0031
 * Time: 21:40
 */

require dirname(__DIR__) . '/test/boot.php';

$text = <<<DOC
/**
 * This is some message.
 * @Scope(value=singleton)
 * @Component(name="myBean", id=345, status=false, map={a = v1,b = v2})
 * @MultiLine(
 *   name="myBean", 
 *   id=345, 
 *   status=false, 
 *   map={a = v1,b = v2}
 * )
 * @Permission(view)
 * @Permission(edit)
 * @cache(true)
 * @type(json)
 * @limits(start=10, limit=50)
 */
DOC;

$ret = \Ulue\Annotations\AnnotationParser::parse($text);

var_dump($ret);
