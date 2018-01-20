<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/20 0020
 * Time: 16:07
 */

use Ulue\Annotations\Annotations;

require dirname(__DIR__) . '/tests/boot.php';

$docBlock = <<<DOC
/**
 * @Inject()
 * @Scope(value=singleton1) @Scope(value=singleton2)
 * @Scope(value=singleton3) @Scope(value=singleton4)
 * @limits(start=10, limit=50)
 * @List(items={12,56,45,67})
 * @MultiLine(
 *   name="myBean",
 *   id=345,
 *   status=false,
 *   map={a = v1,b = v2}
 * )
 * @type(json)
 * @Route(index)
 * @Route(index2)
 * @Response(type=json)
 * @Apostrophe(type='json')
 * @DoubleQuotes(type="value2")
 */
DOC;

$ret = Annotations::parseAnnotations($docBlock);
var_dump($ret);
die;

/**
 * @Inject()
 * @Scope(value=singleton1) @Scope(value=singleton2)
 * @Scope(value=singleton3) @Scope(value=singleton4)
 * @limits(start=10, limit=50)
 * @List(items={12,56,45,67})
 * @MultiLine(
 *   name="myBean",
 *   id=345,
 *   status=false,
 *   map={a = v1,b = v2}
 * )
 * @type(json)
 * @Route(index)
 * @Route(index2)
 * @Response(type=json)
 * @Apostrophe(type='json')
 * @DoubleQuotes(type="value2")
 */
$text = <<<CMT
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
CMT;

$ret = Annotations::parseAnnotations($text);

var_dump($ret, $ret['Component']);
