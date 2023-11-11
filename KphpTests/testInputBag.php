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

use Kaa\HttpBase\KphpTests\InputBagTest;

require __DIR__ . '/../vendor/autoload.php';

$test = new InputBagTest();

echo"\ntestGet\n";
$test->testGet();

echo"\ntestGetDoesNotUseDeepByDefault\n";
$test->testGetDoesNotUseDeepByDefault();

echo"\ntestGettingANonStringValue\n";
$test->testGettingANonStringValue();

echo"\ntestGetWithNonStringDefaultValue\n";
$test->testGetWithNonStringDefaultValue();
