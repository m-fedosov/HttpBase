<?php

/*
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * This file was rewritten from the Symfony package
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Kaa\HttpBase\KphpTests\HeaderUtilsTest;

require __DIR__ . '/../vendor/autoload.php';

$test = new HeaderUtilsTest();

echo"\ntestSplit\n";
$test->testSplit();

echo"\ntestCombine\n";
$test->testCombine();

echo"\ntestToString\n";
$test->testToString();

echo"\ntestQuote\n";
$test->testQuote();

echo"\ntestUnquote\n";
$test->testUnquote();

echo"\ntestMakeDispositionInvalidDisposition\n";
$test->testMakeDispositionInvalidDisposition();

echo"\ntestMakeDisposition\n";
$test->testMakeDisposition();

echo"\ntestMakeDispositionFail\n";
$test->testMakeDispositionFail();

echo"\ntestParseQuery\n";
$test->testParseQuery();

echo"\ntestParseCookie\n";
$test->testParseCookie();

echo"\ntestParseQueryIgnoreBrackets\n";
$test->testParseQueryIgnoreBrackets();
