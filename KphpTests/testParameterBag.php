<?php

use Kaa\HttpBase\KphpTests\ParameterBagTest;

require __DIR__ . '/../vendor/autoload.php';

$test = new ParameterBagTest();

echo"\ntestAll\n";
$test->testAll();

echo"\ntestKeys\n";
$test->testKeys();

echo"\ntestAdd\n";
$test->testAdd();

echo"\ntestRemove\n";
$test->testRemove();

echo"\ntestReplace\n";
$test->testReplace();

echo"\ntestGet\n";
$test->testGet();

echo"\ntestSet\n";
$test->testSet();

echo"\ntestHas\n";
$test->testHas();

echo"\ntestGetAlpha\n";
$test->testGetAlpha();

echo"\ntestGetAlnum\n";
$test->testGetAlnum();

echo"\ntestGetDigits\n";
$test->testGetDigits();

echo"\ntestGetInt\n";
$test->testGetInt();

echo"\ntestGetIterator\n";
$test->testGetIterator();

echo"\ntestCountAll\n";
$test->testCountAll();

echo"\ntestGetBoolean\n";
$test->testGetBoolean();
