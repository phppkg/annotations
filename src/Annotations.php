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
 */
final class Annotations
{
    /**
     * @var array
     */
    private static $ignoredTags = [
        'Annotation' => 1,
        'abstract' => 1,
        'access' => 1,
        'api' => 1,
        'author' => 1,
        'category' => 1,
        'copyright' => 1,
        'codeCoverageIgnoreStart' => 1,
        'codeCoverageIgnoreEnd' => 1,
        'deprecated' => 1,
        'email' => 1,
        'example' => 1,
        'exception' => 1,
        'final' => 1,
        'filesource' => 1,
        'global' => 1,
        'ignore' => 1,
        'inheritdoc' => 1,
        'internal' => 1,
        'license' => 1,
        'link' => 1,
        'magic' => 1,
        'method' => 1,
        'name' => 1,
        'override' => 1,
        'package' => 1,
        'param' => 1,
        'private' => 1,
        'property' => 1,
        'return' => 1,
        'see' => 1,
        'since' => 1,
        'static' => 1,
        'subpackage' => 1,
        'throws' => 1,
        'throw' => 1,
        'todo' => 1,
        'tutorial' => 1,
        'uses' => 1,
        'version' => 1,
    ];

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
        $this->defaultNamespace = rtrim($namespace, '\\') . '\\';
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
            self::$_annotations[$key] = self::parseAnnotations($class->getDocComment());
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
                $annotations = self::parseAnnotations($method->getDocComment());
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
                $annotations = self::parseAnnotations($refMethod->getDocComment());
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
                $annotations = self::parseAnnotations($refMethod->getDocComment());
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

        foreach ($annotations as $annotationClass => $listParams) {
            $annotationClass = ucfirst($annotationClass);
            $class = $this->defaultNamespace . $annotationClass . 'Annotation';

            // verify is the annotation class exists, depending if Annotations::strict is true
            // if not, just skip the annotation instance creation.
            if (!class_exists($class)) {
                if ($this->strict) {
                    throw new \RuntimeException(sprintf('Runtime Error: Annotation Class Not Found: %s', $class));
                }

                // silent skip & continue
                continue;
            }

            if (empty($objects[$annotationClass])) {
                $objects[$annotationClass] = new $class();
            }

            foreach ($listParams as $params) {
                if (\is_array($params)) {
                    foreach ($params as $key => $value) {
                        $objects[$annotationClass]->set($key, $value);
                    }
                } else {
                    $objects[$annotationClass]->set($i++, $params);
                }
            }
        }

        return $objects;
    }

    /**
     * Parse annotations
     *
     * @param string $docBlock
     * param bool $allowRepeatTag
     * @return array parsed annotations params
     */
    public static function parseAnnotations(string $docBlock): array
    {
        $annotations = [];

        if (!$docBlock = trim($docBlock, '/')) {
            return $annotations;
        }

        // 去除所有的 * 符号
        $docBlock = str_replace("\r", '',
            trim(preg_replace('/^\s*\**( |\t)?/m', '', $docBlock))
        );

        if (!$docBlock) {
            return $annotations;
        }

        // 匹配
        if (preg_match_all('/@([A-Za-z_-]+)[\s\t]*\(([^\)]*)\)[\s\t]*\r?/m', $docBlock, $matches)) {
            /**
             * @var array[] $matches
             * - 0 完整模式的所有匹配
             * - 1 由 `([A-Za-z_-]+)` 匹配到的标签 names
             * - 2 由 `([^\)]*)` 匹配到的参数 list
             */
            foreach ($matches[1] as $index => $name) {
                // skip ignored
                if (isset(self::$ignoredTags[$name])) {
                    continue;
                }

                if (!isset($matches[2][$index])) {
                    continue;
                }

                // 多行参数 去掉换行符
                $argsParts = trim(str_replace("\n", '', $matches[2][$index]));
                $value = self::parseArgs($argsParts);

                // 第一次
                if (!isset($annotations[$name])) {
                    $annotations[$name] = $value;
                    // 使用了多个相同tag
                } elseif (isset($annotations[$name][0]) && \is_array($annotations[$name][0])) {
                    $annotations[$name][] = $value;
                } else {
                    $annotations[$name] = [$annotations[$name], $value];
                }
            }
        }

        return $annotations;
    }

    /**
     * Parse individual annotation arguments
     *
     * @param  string $content arguments string
     * @return array|string  annotated arguments
     */
    private static function parseArgs(string $content)
    {
        if (!$content) {
            return [$content];
        }

        $data = [];
        $len = \strlen($content);
        $i = 0;
        $var = $val = '';
        $level = 1;

        $type = 'plain';
        $prevDelimiter = $nextDelimiter = '';
        // $nextToken = '';
        $composing = $quoted = false;
        $delimiter = null;
        $tokens = ['"', '"', '{', '}', ',', '='];

        while ($i < $len) {
            $c = $content[$i++] ?? '';

            if ($c === '\'' || $c === '"') {
                $delimiter = $c;
                //open delimiter
                if (!$composing && empty($prevDelimiter) && empty($nextDelimiter)) {
                    $prevDelimiter = $nextDelimiter = $delimiter;
                    $val = '';
                    $composing = true;
                    $quoted = true;
                } else {
                    // close delimiter
                    if ($c !== $nextDelimiter) {
                        throw new \InvalidArgumentException(sprintf(
                            'Parse Error: enclosing error -> expected: [%s], given: [%s]',
                            $nextDelimiter, $c
                        ));
                    }

                    // validating syntax
                    if ($i < $len) {
                        if (',' !== $content[$i]) {
                            throw new \InvalidArgumentException(sprintf(
                                'Parse Error: missing comma separator near: ...%s<--',
                                substr($content, $i - 10, $i)
                            ));
                        }
                    }

                    $prevDelimiter = $nextDelimiter = '';
                    $composing = false;
                    $delimiter = null;
                }
            } elseif (!$composing && \in_array($c, $tokens, true)) {
                switch ($c) {
                    case '=':
                        $prevDelimiter = $nextDelimiter = '';
                        $level = 2;
                        $composing = false;
                        $type = 'assoc';
                        $quoted = false;
                        break;
                    case ',':
                        $level = 3;

                        // If composing flag is true yet,
                        // it means that the string was not enclosed, so it is parsing error.
                        if ($composing === true && !empty($prevDelimiter) && !empty($nextDelimiter)) {
                            throw new \InvalidArgumentException(sprintf(
                                'Parse Error: enclosing error -> expected: [%s], given: [%s]',
                                $nextDelimiter, $c
                            ));
                        }

                        $prevDelimiter = $nextDelimiter = '';
                        break;
                    case '{':
                        $subc = '';
                        $subComposing = true;

                        while ($i <= $len) {
                            $c = $content[$i++] ?? '';

                            if (isset($delimiter) && $c === $delimiter) {
                                throw new \InvalidArgumentException(sprintf(
                                    'Parse Error: Composite variable is not enclosed correctly.'
                                ));
                            }

                            if ($c === '}') {
                                $subComposing = false;
                                break;
                            }
                            $subc .= $c;
                        }

                        // if the string is composing yet means that the structure of var. never was enclosed with '}'
                        if ($subComposing) {
                            throw new \InvalidArgumentException(sprintf(
                                "Parse Error: Composite variable is not enclosed correctly. near: ...%s'",
                                $subc
                            ));
                        }

                        $val = self::parseArgs($subc);
                        break;
                }
            } else {
                if ($level === 1) {
                    $var .= $c;
                } elseif ($level === 2) {
                    $val .= $c;
                }
            }

            if ($level === 3 || $i === $len) {
                if ($type === 'plain') {//  && $i === $len
                    $data[] = self::castValue($var);
                } else {
                    $data[trim($var)] = self::castValue($val, !$quoted);
                }

                $level = 1;
                $var = $val = '';
                $composing = $quoted = false;
            }
        }

        return $data;
    }

    /**
     * Try determinate the original type variable of a string
     *
     * @param  string|array|bool $val string containing possibles variables that can be cast to bool or int
     * @param  boolean $trim indicate if the value passed should be trimmed after to try cast
     * @return mixed         returns the value converted to original type if was possible
     */
    private static function castValue($val, $trim = false)
    {
        if (\is_array($val)) {
            foreach ($val as $key => $value) {
                $val[$key] = self::castValue($value);
            }
        } elseif (\is_string($val)) {
            if ($trim) {
                $val = trim($val);
            }

            $tmp = strtolower($val);

            if ($tmp === 'false' || $tmp === 'true') {
                $val = $tmp === 'true';
            } elseif (is_numeric($val)) {
                return $val + 0;
            }

            unset($tmp);
        }

        return $val;
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
    public static function getIgnoredTags(): array
    {
        return self::$ignoredTags;
    }

    /**
     * @param array $ignoredTags
     */
    public static function setIgnoredTags(array $ignoredTags)
    {
        self::$ignoredTags = $ignoredTags;
    }

    /**
     * @param array $ignoredTags
     */
    public static function addIgnoredTags(array $ignoredTags)
    {
        self::$ignoredTags = array_merge(self::$ignoredTags, $ignoredTags);
    }

    /**
     * @param string $name
     */
    public static function addIgnoredTag(string $name)
    {
        self::$ignoredTags[$name] = 1;
    }
}
