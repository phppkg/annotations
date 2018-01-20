<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/20 0020
 * Time: 18:43
 */

use Ulue\Annotations\Annotations;

require dirname(__DIR__) . '/tests/boot.php';

$ret = Annotations::make()->yieldAllMethodAnnotations('User');

// var_dump($ret);

foreach ($ret as $name => $item) {
    var_dump($name, $item);
}
