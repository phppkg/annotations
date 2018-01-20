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
     * Static array to store already parsed annotations
     * @var array
     */
    private static $_annotations;

    /**
     * Indicates that annotations should has strict behavior, 'false' by default
     * @var boolean
     */
    private $strict = false;

    /**
     * Stores the default namespace for Objects instance, usually used on methods like getMethodAnnotationsObjects()
     * @var string e.g '\Annotation\\'
     */
    public $defaultNamespace = '';

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
    public function getClassAnnotations($className): array
    {
        if (!isset(self::$_annotations[$className])) {
            $class = new \ReflectionClass($className);
            self::$_annotations[$className] = self::parseAnnotations($class->getDocComment());
        }

        return self::$_annotations[$className];
    }

    /**
     * Gets all annotations with pattern @SomeAnnotation() from a determinated method of a given class
     *
     * @param  string $className class name
     * @param  string $methodName method name to get annotations
     * @return array[]  self::$_annotations all annotated elements of a method given
     */
    public function getMethodAnnotations($className, $methodName): array
    {
        if (!isset(self::$_annotations[$className . '::' . $methodName])) {
            try {
                $method = new \ReflectionMethod($className, $methodName);
                $annotations = self::parseAnnotations($method->getDocComment());
            } catch (\ReflectionException $e) {
                $annotations = array();
            }

            self::$_annotations[$className . '::' . $methodName] = $annotations;
        }

        return self::$_annotations[$className . '::' . $methodName];
    }

    /**
     * Gets all annotations with pattern @SomeAnnotation() from a determinated method of a given class
     * and instance its abcAnnotation class
     *
     * @param  string $className class name
     * @param  string $methodName method name to get annotations
     * @return array  self::$_annotations all annotated objects of a method given
     */
    public function getMethodAnnotationsObjects($className, $methodName): array
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
     * @param  string $docBlock
     * @return array parsed annotations params
     */
    public static function parseAnnotations(string $docBlock): array
    {
        $annotations = array();

        // Strip away the doc-block header and footer to ease parsing of one line annotations
        $docBlock = (string)substr($docBlock, 3, -2);

        if (preg_match_all('/@(?<name>[A-Za-z_-]+)[\s\t]*\((?<args>.*)\)[\s\t]*\r?$/m', $docBlock, $matches)) {
            $name = null;
            $numMatches = \count($matches[0]);

            for ($i = 0; $i < $numMatches; ++$i) {
                // annotations has arguments
                if (isset($matches['args'][$i])) {
                    $argsParts = trim($matches['args'][$i]);
                    $name = $matches['name'][$i];
                    $value = self::parseArgs($argsParts);
                } else {
                    $value = array();
                }

                if ($name) {
                    $annotations[$name][] = $value;
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
        $data = array();
        $len = \strlen($content);
        $i = 0;
        $var = '';
        $val = '';
        $level = 1;

        $prevDelimiter = '';
        $nextDelimiter = '';
        // $nextToken = '';
        $composing = false;
        $type = 'plain';
        $delimiter = null;
        $quoted = false;
        $tokens = ['"', '"', '{', '}', ',', '='];

        while ($i <= $len) {
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
                        // if (',' !== substr($content, $i, 1)) {
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
                            // $c = substr($content, $i++, 1);

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
                if ($type === 'plain' && $i === $len) {
                    $data = self::castValue($var);
                } else {
                    $data[trim($var)] = self::castValue($val, !$quoted);
                }

                $level = 1;
                $var = $val = '';
                $composing = false;
                $quoted = false;
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
}
