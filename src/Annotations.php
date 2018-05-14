<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/20 0020
 * Time: 16:46
 */

namespace Ulue\Annotations;

/**
 * Class Annotations
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 * @link      https://github.com/eriknyk/Annotations
 * @link      https://github.com/ulue/Annotations
 */
final class Annotations
{
    /**
     * Static array to store already parsed annotations
     * @var array
     */
    private static $_annotations;

    /**
     * cached class reflections
     * @var \ReflectionClass[]
     */
    private static $reflections = [];

    /**
     * @var array
     * [ name => class ]
     */
    private static $tagClasses = [];

    /**
     * Indicates that annotations should has strict behavior, 'false' by default
     * @var boolean
     */
    private $strict = false;

    /**
     * Stores the default namespace for Objects instance, usually used on methods like getMethodAnnotationsObjects()
     * @var string e.g '\Annotation\\'
     */
    private $defaultNamespace = '';

    /**
     * @return Annotations
     */
    public static function make(): Annotations
    {
        return new self;
    }

    /**
     * Sets strict variable to true/false
     * @param bool $value boolean value to indicate that annotations to has strict behavior
     */
    public function setStrict($value)
    {
        $this->strict = (bool)$value;
    }

    /**
     * Sets default namespace to use in object instantiation
     * @param string $namespace default namespace
     */
    public function setDefaultNamespace(string $namespace)
    {
        $this->defaultNamespace = \rtrim($namespace, '\\') . '\\';
    }

    /**
     * Gets default namespace used in object instantiation
     * @return string $namespace default namespace
     */
    public function getDefaultAnnotationNamespace(): string
    {
        return $this->defaultNamespace;
    }

    /**
     * Gets all annotations with pattern @SomeAnnotation() from a given class
     *
     * @param  string $className class name to get annotations
     * @return array  self::$_annotations all annotated elements
     * @throws \ReflectionException
     */
    public function getClassAnnotations(string $className): array
    {
        $key = $className . '.class';

        if (!isset(self::$_annotations[$key])) {
            $class = self::createReflection($className);
            self::$_annotations[$key] = AnnotationParser::parse($class->getDocComment());
        }

        return self::$_annotations[$key];
    }

    /**
     * Gets all annotations with pattern @SomeAnnotation() from a determinated method of a given class
     *
     * @param  string $className class name
     * @param  string $methodName method name to get annotations
     * @return array[]  self::$_annotations all annotated elements of a method given
     */
    public function getMethodAnnotations(string $className, string $methodName = null): array
    {
        $prefix = $className . '.methods';

        if (!isset(self::$_annotations[$prefix][$methodName])) {
            try {
                $method = new \ReflectionMethod($className, $methodName);
                $annotations = AnnotationParser::parse($method->getDocComment());
            } catch (\ReflectionException $e) {
                $annotations = [];
            }

            self::$_annotations[$prefix][$methodName] = $annotations;
        }

        return self::$_annotations[$prefix][$methodName];
    }

    /**
     * @param string $className
     * @param int $filter Filter methods, default return all.
     * @return array
     * @throws \ReflectionException
     * @see \ReflectionMethod for filter flags
     * like:
     * - ReflectionMethod::IS_STATIC
     * - ReflectionMethod::IS_PUBLIC
     * ...
     */
    public function getMethodsAnnotations(string $className, int $filter = -1): array
    {
        $ref = self::createReflection($className);
        $prefix = $className . '.methods';
        $map = [];

        foreach ($ref->getMethods($filter) as $refMethod) {
            $methodName = $refMethod->getName();

            if (isset(self::$_annotations[$prefix][$methodName])) {
                $annotations = self::$_annotations[$prefix][$methodName];
            } else {
                $annotations = AnnotationParser::parse($refMethod->getDocComment());
            }

            $map[$methodName] = $annotations;
        }

        return $map;
    }

    /**
     * @param string $className
     * @param int $filter Filter methods, default return all.
     * @return \Generator
     * @throws \ReflectionException
     * @see \ReflectionMethod for filter flags
     * like:
     * - ReflectionMethod::IS_STATIC
     * - ReflectionMethod::IS_PUBLIC
     * ...
     */
    public function yieldMethodsAnnotations(string $className, int $filter = -1)
    {
        $ref = new \ReflectionClass($className);
        $prefix = $className . '.methods';

        foreach ($ref->getMethods($filter) as $refMethod) {
            $methodName = $refMethod->getName();

            if (isset(self::$_annotations[$prefix][$methodName])) {
                $annotations = self::$_annotations[$prefix][$methodName];
            } else {
                $annotations = AnnotationParser::parse($refMethod->getDocComment());
            }

            yield $methodName => $annotations;
        }
    }

    /**
     * Gets all annotations with pattern @SomeAnnotation() from a determinated method of a given class
     * and instance its abcAnnotation class
     *
     * @param  string $className class name
     * @param  string $methodName method name to get annotations
     * @return array  self::$_annotations all annotated objects of a method given
     */
    public function getMethodAnnotationsObjects(string $className, string $methodName): array
    {
        $i = 0;
        $objects = [];
        $annotations = $this->getMethodAnnotations($className, $methodName);

        foreach ($annotations as $annotationTag => $listParams) {
            if (isset(self::$tagClasses[$annotationTag])) {
                $class = self::$tagClasses[$annotationTag];
            } else {
                $class = $this->defaultNamespace . \ucfirst($annotationTag) . 'Annotation';
            }

            // verify is the annotation class exists, depending if Annotations::strict is true
            // if not, just skip the annotation instance creation.
            if (!class_exists($class)) {
                if ($this->strict) {
                    throw new \RuntimeException(sprintf(
                        'Annotation Class Not Found: %s for the tag: %s',
                        $class,
                        $annotationTag
                    ));
                }

                // silent skip & continue
                continue;
            }

            if (empty($objects[$annotationTag])) {
                $objects[$annotationTag] = new $class();
            }

            foreach ($listParams as $params) {
                if (\is_array($params)) {
                    foreach ($params as $key => $value) {
                        $objects[$annotationTag]->set($key, $value);
                    }
                } else {
                    $objects[$annotationTag]->set($i++, $params);
                }
            }
        }

        return $objects;
    }

    /**
     * @param string $class
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    public static function createReflection(string $class): \ReflectionClass
    {
        if (!isset(self::$reflections[$class])) {
            self::$reflections[$class] = new \ReflectionClass($class);
        }

        return self::$reflections[$class];
    }

    /**
     * @return array
     */
    public static function getTagClasses(): array
    {
        return self::$tagClasses;
    }

    /**
     * @param array $tagClasses
     */
    public static function setTagClasses(array $tagClasses)
    {
        self::$tagClasses = $tagClasses;
    }

    /**
     * @param array $tagClasses
     */
    public static function addTagClasses(array $tagClasses)
    {
        foreach ($tagClasses as $tag => $class) {
            self::addTagClass($tag, $class);
        }
    }

    /**
     * @param string $tag
     * @param string $class
     */
    public static function addTagClass(string $tag, string $class)
    {
        self::$tagClasses[$tag] = $class;
    }
}
