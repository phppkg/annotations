<?php
/**
 * phpunit --bootstrap tests/boot.php tests
 */

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Asia/Shanghai');

spl_autoload_register(function($class)
{
    $file = null;

    if (0 === strpos($class,'Ulue\Annotations\Examples\\')) {
        $path = str_replace('\\', '/', substr($class, strlen('Ulue\Annotations\Examples\\')));
        $file = dirname(__DIR__) . "/examples/{$path}.php";

    } elseif (0 === strpos($class,'Ulue\Annotations\Tests\\')) {
        $path = str_replace('\\', '/', substr($class, strlen('Ulue\Annotations\Tests\\')));
        $file = __DIR__ . "/{$path}.php";
    } elseif (0 === strpos($class,'Ulue\Annotations\\')) {
        $path = str_replace('\\', '/', substr($class, strlen('Ulue\Annotations\\')));
        $file = dirname(__DIR__) . "/src/{$path}.php";
    }

    if ($file && is_file($file)) {
        include $file;
    }
});

$libDir = dirname(__DIR__);
$files = [
    'tests/Fixtures/classes/User.php',
    'tests/Fixtures/classes/Group.php',
    'tests/Fixtures/classes/Base/Annotation/PermissionAnnotation.php',
    'tests/Fixtures/classes/Base/Annotation/RoleAnnotation.php',
];

foreach ($files as $file) {
    if (is_file($libDir . '/' . $file)) {
        include $libDir . '/' . $file;
    }
}
