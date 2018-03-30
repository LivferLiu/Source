<?php
require_once('array_depth.php');

echo '<pre>';
echo  array_depth(array(array(array(array(1,array()))),array(array())));echo'<br>';
echo  array_depth(array(array(array(array(1,array(),array())))));echo'<br>';
echo  array_depth(array(array(array(array(1,45)))));	echo'<br>';
echo  array_depth(array(array(),array(array()),array()));	echo'<br>';
echo  array_depth(1);echo'<br>';


var_dump(homogeneous(array(/*1,45,'uuu',*/array(array(array(1))))));	echo'<br>';
var_dump(homogeneous(array(array(),array(array()),array())));	echo'<br>';
var_dump(homogeneous(array(1,2,3)));	echo'<br>';
var_dump(homogeneous(array(1,2.5,3)));	echo'<br>';
var_dump(homogeneous(array(array(1),array(2.5),array(3))));	echo'<br>';


$type=true;
var_dump(homogeneous(array(array(1.5,array(2.5)),array(2.5,array(2.5)),array(3.5,array(2.5))),$type));	echo'<br>';
echo $type;	echo'<br>';

$type=true;
var_dump(homogeneous(array(array(),2),$type));	echo'<br>';
echo $type;	echo'<br>';

var_dump(typesof(array(array(),array(array()),array())));	echo'<br>';

var_dump(signature(array(array(),array(array()),array())));	echo'<br>';

var_dump(rowsandcol($x=array(array(1,'c'=>2,3),array(1,'c'=>2,3),array(1,'c'=>array(),3)),$xiayo));	echo'<br>';

print_r( $xiayo);



?>