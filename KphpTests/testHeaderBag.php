<?php

use Kaa\HttpBase\KphpTests\HeaderBagTest;

require __DIR__ . '/../vendor/autoload.php';

$test = new HeaderBagTest();

echo"\ntestConstructor\n";
$test->testConstructor();

echo"\ntestToStringNull\n";
$test->testToStringNull();

echo"\ntestKeys\n";
$test->testKeys();

echo"\ntestGetDate\n";
$test->testGetDate();

echo"\ntestGetDateNull\n";
$test->testGetDateNull();

echo"\ntestGetDateException\n";
$test->testGetDateException();

echo"\ntestGetCacheControlHeader\n";
$test->testGetCacheControlHeader();

echo"\ntestAll\n";
$test->testAll();

echo"\ntestReplace\n";
$test->testReplace();

echo"\ntestGet\n";
$test->testGet();

echo"\ntestSetAssociativeArray\n";
$test->testSetAssociativeArray();

echo"\ntestContains\n";
$test->testContains();

echo"\ntestCacheControlDirectiveAccessors\n";
$test->testCacheControlDirectiveAccessors();

echo"\ntestCacheControlDirectiveParsing\n";
$test->testCacheControlDirectiveParsing();

echo"\ntestCacheControlDirectiveParsingQuotedZero\n";
$test->testCacheControlDirectiveParsingQuotedZero();

echo"\ntestCacheControlDirectiveOverrideWithReplace\n";
$test->testCacheControlDirectiveOverrideWithReplace();

echo"\ntestCacheControlClone\n";
$test->testCacheControlClone();

echo"\ntestGetIterator\n";
$test->testGetIterator();

echo"\ntestCountAll\n";
$test->testCountAll();
