<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/20 0020
 * Time: 18:43
 */

use Ulue\Annotations\Annotations;

require dirname(__DIR__) . '/test/boot.php';

$an = Annotations::make([
    'nameAsKey' => true,
]);
// var_dump($an);die;
$ret = $an->yieldMethodsAnnotations(\User::class);

// var_dump($ret);
foreach ($ret as $key => $item) {
    var_dump($key, $item);
}
