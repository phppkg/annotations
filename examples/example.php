<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/20 0020
 * Time: 16:07
 */

require dirname(__DIR__) . '/test/boot.php';

$docBlock = <<<DOC
/**
 * @author Somebody
 * @version 1.0
 *
 * @ChangeTrackingPolicy("NOTIFY")
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn( name="discr", type=" string " )
 * @Table(name="ecommerce_products", indexes={name="sexarch_idx", column="name"}, variant=false)
 */
DOC;

$nameAsKey = true;
$ret = \PhpComLab\Annotations\AnnotationParser::make()->parse($docBlock, $nameAsKey);
var_dump($ret);

