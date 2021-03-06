<?php
/**
 * phpunit --bootstrap tests/boot.php tests
 */

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Asia/Shanghai');

spl_autoload_register(function ($class) {
    $file = null;

    if (0 === strpos($class, 'PhpPkg\Annotations\Examples\\')) {
        $path = str_replace('\\', '/', substr($class, strlen('PhpPkg\Annotations\Examples\\')));
        $file = dirname(__DIR__) . "/examples/$path.php";

    } elseif (0 === strpos($class, 'PhpPkg\Annotations\Test\\')) {
        $path = str_replace('\\', '/', substr($class, strlen('PhpPkg\Annotations\Test\\')));
        $file = __DIR__ . "/$path.php";
    } elseif (0 === strpos($class, 'PhpPkg\Annotations\\')) {
        $path = str_replace('\\', '/', substr($class, strlen('PhpPkg\Annotations\\')));
        $file = dirname(__DIR__) . "/src/$path.php";
    }

    if ($file && is_file($file)) {
        include $file;
    }
});

$libDir = __DIR__;
$files  = [
    'Fixtures/classes/User.php',
    'Fixtures/classes/Group.php',
    'Fixtures/classes/Base/Annotation/PermissionAnnotation.php',
    'Fixtures/classes/Base/Annotation/RoleAnnotation.php',
];

foreach ($files as $file) {
    if (is_file($libDir . '/' . $file)) {
        include $libDir . '/' . $file;
    }
}

if (is_file(dirname(__DIR__, 3) . '/autoload.php')) {
    require dirname(__DIR__, 3) . '/autoload.php';
} elseif (is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    require dirname(__DIR__) . '/vendor/autoload.php';
}
