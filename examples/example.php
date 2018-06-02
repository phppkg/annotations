<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/20 0020
 * Time: 16:07
 */

require dirname(__DIR__) . '/test/boot.php';

$docBlock = <<<DOC
/**
 * @Inject()
 * @Scope(value=singleton1) @Scope(value=singleton2)
 * @Scope(value=singleton3) @Scope(value=singleton4)
 * @limits(start=10, limit=50)
 * @List(items={12,56,45,67})
 * @Route(methods={"GET", "POST"})
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

$nameAsKey = true;
$ret = \Ulue\Annotations\AnnotationParser::make()->parse($docBlock, $nameAsKey);
var_dump($ret);

