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

namespace Kaa\HttpBase\KphpTests;

use Kaa\HttpBase\Exception\BadRequestException;
use Kaa\HttpBase\InputBag;

class InputBagTest
{
    public function testGet(): void
    {
        $bag = new InputBag([
            'foo' => 'bar',
            'null' => null,
            'int' => 1,
            'float' => 1.0,
            'bool' => false
        ]);

        var_dump('bar' === $bag->get('foo'));
        var_dump('default' === $bag->get('unknown', 'default'));
        var_dump(null === $bag->get('null', 'default'));
        var_dump(1 === $bag->get('int'));
        var_dump(false === $bag->get('bool'));

        $epsilon = 0.00001;
        var_dump(abs($bag->get('float') - 1.0) < $epsilon);
    }

    public function testGetDoesNotUseDeepByDefault(): void
    {
        $bag = new InputBag(['foo' => ['bar' => 'moo']]);

        var_dump(null === $bag->get('foo[bar]'));
    }

    public function testGettingANonStringValue(): void
    {
        try {
            $bag = new InputBag(['foo' => ['a', 'b']]);
            $bag->get('foo');
        } catch (BadRequestException $e) {
            var_dump($e->getMessage() === 'Input value "foo" contains a non-scalar value.');
        }
    }

    public function testGetWithNonStringDefaultValue(): void
    {
        try {
            $bag = new InputBag(['foo' => 'bar']);
            $bag->get('foo', ['a', 'b']);
        } catch (\InvalidArgumentException $e) {
            var_dump($e->getMessage() === 'Expected a scalar value as a 2nd argument to "Kaa\HttpBase\InputBag::get()", "array" given.');
        }
    }
}
