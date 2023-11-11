<?php

namespace Kaa\HttpBase\KphpTests;

use Kaa\HttpBase\ParameterBag;

class ParameterBagTest
{
    public function testAll(): void
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        var_dump(['foo' => 'bar'] === $bag->all());
    }

    public function testKeys(): void
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        var_dump(['foo'] === $bag->keys());
    }

    public function testAdd(): void
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $bag->add(['bar' => 'bas']);
        var_dump(['foo' => 'bar', 'bar' => 'bas'] === $bag->all());
    }

    public function testRemove(): void
    {
        $bag = new ParameterBag(['foo' => 'bar']);
        $bag->add(['bar' => 'bas']);
        var_dump(['foo' => 'bar', 'bar' => 'bas'] === $bag->all());
        $bag->remove('bar');
        var_dump(['foo' => 'bar'] === $bag->all());
    }

    public function testReplace(): void
    {
        $bag = new ParameterBag(['foo' => 'bar']);

        $bag->replace(['FOO' => 'BAR']);
        var_dump(['FOO' => 'BAR'] === $bag->all());
        var_dump(false === $bag->has('foo'));
    }

    public function testGet(): void
    {
        $bag = new ParameterBag(['foo' => 'bar']);

        var_dump('bar' === $bag->get('foo'));
        var_dump('default' === $bag->get('unknown', 'default'));
    }

    public function testSet(): void
    {
        $bag = new ParameterBag([]);

        $bag->set('foo', 'bar');
        var_dump('bar' === $bag->get('foo'));

        $bag->set('foo', 'baz');
        var_dump('baz' === $bag->get('foo'));
    }

    public function testHas(): void
    {
        $bag = new ParameterBag(['foo' => 'bar']);

        var_dump(true === $bag->has('foo'));
        var_dump(false === $bag->has('unknown'));
    }

    public function testGetAlpha(): void
    {
        $bag = new ParameterBag(['word' => 'foo_BAR_012']);

        var_dump('fooBAR' === $bag->getAlpha('word'));
        var_dump('' === $bag->getAlpha('unknown'));
    }

    public function testGetAlnum(): void
    {
        $bag = new ParameterBag(['word' => 'foo_BAR_012']);

        var_dump('fooBAR012' === $bag->getAlnum('word'));
        var_dump('' === $bag->getAlnum('unknown'));
    }

    public function testGetDigits(): void
    {
        $bag = new ParameterBag(['word' => 'foo_BAR_012']);

        var_dump('012' === $bag->getDigits('word'));
        var_dump('' === $bag->getDigits('unknown'));
    }

    public function testGetInt(): void
    {
        $bag = new ParameterBag(['digits' => '0123']);

        var_dump(123 === $bag->getInt('digits'));
        var_dump(0 === $bag->getInt('unknown'));
    }

    public function testGetIterator(): void
    {
        $parameters = ['foo' => 'bar', 'hello' => 'world'];
        $bag = new ParameterBag($parameters);

        $i = 0;
        foreach ($bag->all() as $key => $val) {
            ++$i;
            var_dump($parameters[$key] === $val);
        }

        var_dump(\count($parameters) === $i);
    }

    public function testCountAll(): void
    {
        $parameters = ['foo' => 'bar', 'hello' => 'world'];
        $bag = new ParameterBag($parameters);

        var_dump(\count($parameters) === count($bag->all()));
    }

    public function testGetBoolean(): void
    {
        $parameters = ['string_true' => 'true', 'string_false' => 'false'];
        $bag = new ParameterBag($parameters);

        var_dump(true === $bag->getBoolean('string_true'));
        var_dump(false === $bag->getBoolean('string_false'));
        var_dump(false === $bag->getBoolean('unknown'));
    }
}
