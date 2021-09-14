<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/3 0003
 * Time: 23:39
 */

$str = <<<DOC
/**
 * @APIMeta(
 *     host="api.dev",
 *     basePath="/",
 *     schemes={"http", "https"},
 *     consumes={"application/json"},
 *     produces={"application/json"}
 * )
 * @APIInfo(
 *     version="1.0.0",
 *     title="user service center",
 *     description="## user service center [`env: {dev}`]
 * test"
 * )
 */
DOC;

require dirname(__DIR__) . '/test/boot.php';

// clear char *
$str = \PhpComLab\Annotations\AnnotationParser::filterDocComment($str);

// parse to string list
$arr = \PhpComLab\Annotations\AnnotationParser::make()->parseToTagStrings($str);

$data = \PhpComLab\Annotations\Full\TagContentParser::handle($arr[2][0]);

var_dump($data);

$data = \PhpComLab\Annotations\Full\TagContentParser::handle($arr[2][1]);

var_dump($data);
