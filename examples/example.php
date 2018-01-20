<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/20 0020
 * Time: 16:07
 */

use Ulue\Annotations\Annotations;

require dirname(__DIR__) . '/tests/boot.php';

$text = <<<CMT

/**
 * This is some message.
 * @Component(name="myBean", id=345, status=false, map={a = v1,b = v2})
 * @Scope(value=singleton)
 * @Permission(view)
 * @Permission(edit)
 * @Role(administrator)
 * @cache(true)
 * @type(json)
 * @limits(start=10, limit=50)
 */
CMT;

// $ret = Annotations::make()->parseAnnotations($text);

// var_dump($ret, $ret['Component']);

$ret = Annotations::make()->getAllMethodAnnotations('User');

var_dump($ret);

foreach ($ret as $name => $item) {
    var_dump($name, $item);
}
