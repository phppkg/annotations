# README

Simple and Lightweight PHP Class & Methods Annotations Reader

> Forked from [eriknyk/Annotations](https://github.com/eriknyk/Annotations)

## 项目地址

- **github** https://github.com/phppkg/annotations.git

## 安装

- composer 命令

```php
composer require phppkg/annotations
```

- composer.json

```json
{
    "require": {
        "phppkg/annotations": "dev-master"
    }
}
```

- 直接拉取

```bash
git clone https://github.com/phppkg/annotations.git // github
```

## 使用

Sample class User.php

```php
<?php
    /**
     * @Defaults(name="user1", lastname = "sample", age='0', address={country=USA, state=NY}, phone="000-00000000")
     * @assertResult(false)
     * @cache(collation = UTF-8)
     */
    class User
    {
        /**
         * @cache(true)
         * @type(json)
         * @limits(start=10, limit=50)
         */
        function load(){
        }

        /**
         * create a record
         *
         * @Permission(view)
         * @Permission(edit)
         * @Role(administrator)
         */
        public function create()
        {
        }
    }
```

Sample use. 

- get class annotations:

```php
include 'User.php';
$annotations = new PhpPkg\Annotations\Annotations();
$result = $annotations->getClassAnnotations('User');

print_r($result);
```

Result:

```php
Array
(
    [Defaults] => Array
        (
            [0] => Array
                (
                    [name] => user1
                    [lastname] => sample
                    [age] => 0
                    [address] => Array
                        (
                            [country] => USA
                            [state] => NY
                        )

                    [phone] => 000-00000000
                )

        )

    [assertResult] => Array
        (
            [0] => false
        )

    [cache] => Array
        (
            [0] => Array
                (
                    [collation] => UTF-8
                )

        )

)
```
- get method annotations:

```php
$result = $annotations->getMethodAnnotations('User', 'create');
print_r($result);
```

Result:

```php
    Array
    (
        [Permission] => Array
            (
                [0] => view
                [1] => edit
            )

        [Role] => Array
            (
                [0] => administrator
            )

    )
```

### Creating Annotated objects.

You can crate fast annotated objects.

Sample Annotated Classes.

```php
<?php
    // Annotation.php

    abstract class Annotation
    {
        protected $data = array();

        public function __construct($args = array())
        {
            $this->data = $args;
        }

        public function set($key, $value)
        {
            $this->data[$key] = $value;
        }

        public function get($key, $default = null)
        {
            if (empty($this->data[$key])) {
                return $default;
            }

            return $this->data[$key];
        }

        public function exists($key)
        {
            return isset($this->data[$key]);
        }
    }
```

```php
<?php
    // PermissionAnnotation.php
    namespace Annotation;

    class PermissionAnnotation extends Annotation
    {
    }
```

```
<?php
    namespace Base\Annotation;
    // RoleAnnotation.php

    class RoleAnnotation extends Annotation
    {
    }
```

```php
require dirname(__DIR__) . '/tests/boot.php';

$annotations->setDefaultAnnotationNamespace('\Annotation\\');
$result = $annotations->getMethodAnnotationsObjects('User', 'create');
print_r($result);
```

Result:

```text
Array
    (
        [Permission] => Base\Annotation\PermissionAnnotation Object
            (
                [data:protected] => Array
                    (
                        [0] => view
                        [1] => edit
                    )

            )

        [Role] => Base\Annotation\RoleAnnotation Object
            (
                [data:protected] => Array
                    (
                        [2] => administrator
                    )

            )

    )
```

## unit test

```base
phpunit
```

## Related

- https://github.com/marcioAlmada/annotations

## LICENSE

MIT
