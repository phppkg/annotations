<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/20 0020
 * Time: 16:07
 */

require dirname(__DIR__) . '/tests/boot.php';

$text = <<<CMT

/**
 * This is our bean.
 * @Component(name="myBean", id=345, status=false, map={a = v1,b = v2})
 * @InitMethod(method=init)
 * @DestroyMethod(method=destroy)
 * @Scope(value=singleton)
 * @Permission(view)
 * @Permission(edit)
 * @Role(administrator)
 * @cache(true)
 * @type(json)
 * @limits(start=10, limit=50)
 */
CMT;

$ret = \Ulue\Annotations\Annotations::make()->parseAnnotations($text);

var_dump($ret, $ret['Component']);
