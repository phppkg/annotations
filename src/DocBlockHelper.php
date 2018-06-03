<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/3 0003
 * Time: 11:59
 */

namespace Ulue\Annotations;

/**
 * Class DocBlockHelper
 * @package Ulue\Annotations
 */
class DocBlockHelper
{
    /**
     * @param string $string
     * @return \Generator
     */
    public static function strToGenerator(string $string): \Generator
    {
        $start = 0;
        $encoding = 'UTF-8';
        $length = \mb_strlen($string, $encoding);

        while ($start < $length) {
            $char = \mb_substr($string, $start, 1, $encoding);
            $start++;
            yield $char;
        }
    }

    /**
     * @param string $string
     * @return array
     */
    public static function strToArray(string $string): array
    {
        $start = 0;
        $array = [];
        $encoding = 'UTF-8';
        $length = \mb_strlen($string, $encoding);

        while ($start < $length) {
            $char = \mb_substr($string, $start, 1, $encoding);

            $start++;
            $array[] = $char;
        }

        return $array;
    }

    /**
     * @param string $string
     * @return array
     */
    public static function strToArray1(string $string): array
    {
        $array = [];
        $encoding = 'UTF-8';

        while ($strlen = \mb_strlen($string, $encoding)) {
            $array[] = \mb_substr($string, 0, 1, $encoding);
            $string = \mb_substr($string, 1, $strlen, $encoding);
        }

        return $array;
    }


    /**
     * a (simpler) way to extract all characters from a UTF-8 string to array
     * @param string $string
     * @return array
     */
    public static function strToArray2(string $string): array
    {
        return \preg_split('//u', $string, -1, \PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Try determinate the original type variable of a string
     *
     * @param  string|array|bool $val string containing possibles variables that can be cast to bool or int
     * @param  boolean $trim indicate if the value passed should be trimmed after to try cast
     * @return mixed         returns the value converted to original type if was possible
     */
    public static function castValue($val, $trim = false)
    {
        if (\is_array($val)) {
            foreach ($val as $key => $value) {
                $val[$key] = self::castValue($value);
            }
        } elseif (\is_string($val)) {
            if ($trim) {
                $val = \trim($val);
            }

            $tmp = \strtolower($val);

            if ($tmp === 'false' || $tmp === 'true') {
                $val = $tmp === 'true';
            } elseif (\is_numeric($val)) {
                return $val + 0;
            }
        }

        return $val;
    }
}
