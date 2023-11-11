<?php

namespace Kaa\HttpBase\KphpTests;

use Kaa\HttpBase\HeaderBag;

class HeaderBagTest
{
    public function testConstructor()
    {
        $bag = new HeaderBag(['foo' => 'bar']);
        var_dump(true === $bag->has('foo'));
    }

    public function testToStringNull()
    {
        $bag = new HeaderBag();
        var_dump('' === $bag->__toString());
    }

    public function testToStringNotNull()
    {
        $bag = new HeaderBag(['foo' => 'bar']);
        var_dump("Foo: bar\r\n" === $bag->__toString());
    }

    public function testKeys()
    {
        $bag = new HeaderBag(['foo' => 'bar']);
        $keys = $bag->keys();
        var_dump('foo' === $keys[0]);
    }

    public function testGetDate()
    {
        $bag = new HeaderBag(['foo' => 'Tue, 4 Sep 2012 20:00:00 +0200']);
        $headerDate = $bag->getDate('foo');
        var_dump($headerDate instanceof \DateTime);
    }

    public function testGetDateNull()
    {
        $bag = new HeaderBag(['foo' => null]);
        try {
            $bag->getDate('foo');
        } catch (\RuntimeException $e) {
            var_dump($e->getMessage() === 'The "foo" HTTP header is not parseable ().');
        }
    }

    public function testGetDateException()
    {
        $bag = new HeaderBag(['foo' => 'Tue']);
        try {
            $bag->getDate('foo');
        } catch (\RuntimeException $e) {
            var_dump($e->getMessage() === 'The "foo" HTTP header is not parseable (Tue).');
        }
    }

    public function testGetCacheControlHeader()
    {
        $bag = new HeaderBag();
        $bag->addCacheControlDirective('public', '#a');
        var_dump(true === $bag->hasCacheControlDirective('public'));
        var_dump('#a' === $bag->getCacheControlDirective('public'));
    }

    public function testAll()
    {
        $bag = new HeaderBag(['foo' => 'bar']);
        var_dump(['foo' => ['bar']] === $bag->all());

        $bag = new HeaderBag(['FOO' => 'BAR']);
        var_dump(['foo' => ['BAR']] === $bag->all());
    }

    public function testReplace()
    {
        $bag = new HeaderBag(['foo' => 'bar']);

        $bag->replace(['NOPE' => 'BAR']);
        var_dump(['nope' => ['BAR']] === $bag->all());
        var_dump(false === $bag->has('foo'));
    }

    public function testGet()
    {
        $bag = new HeaderBag(['foo' => 'bar', 'fuzz' => 'bizz']);
        var_dump('bar' === $bag->get('foo'));
        var_dump('bar' === $bag->get('FoO'));
        var_dump(['bar'] === $bag->all('foo'));

        // defaults
        var_dump(null === $bag->get('none'));
        var_dump('default' === $bag->get('none', 'default'));
        var_dump([] === $bag->all('none'));

        $bag->set('foo', 'bor', false);
        var_dump('bar' === $bag->get('foo'));
        var_dump(['bar', 'bor'] === $bag->all('foo'));

        $bag->set('baz', null);
        var_dump(null === $bag->get('baz', 'nope'));
    }

    public function testSetAssociativeArray()
    {
        $bag = new HeaderBag();
        $bag->set('foo', ['bad-assoc-index' => 'value']);
        var_dump('value' === $bag->get('foo'));
        var_dump(['value'] === $bag->all('foo'));
    }

    public function testContains()
    {
        $bag = new HeaderBag(['foo' => 'bar', 'fuzz' => 'bizz']);
        var_dump(true === $bag->contains('foo', 'bar'));
        var_dump(true === $bag->contains('fuzz', 'bizz'));
        var_dump(false === $bag->contains('nope', 'nope'));
        var_dump(false === $bag->contains('foo', 'nope'));

        // Multiple values
        $bag->set('foo', 'bor', false);
        var_dump(true === $bag->contains('foo', 'bar'));
        var_dump(true === $bag->contains('foo', 'bor'));
        var_dump(false === $bag->contains('foo', 'nope'));
    }

    public function testCacheControlDirectiveAccessors()
    {
        $bag = new HeaderBag();
        $bag->addCacheControlDirective('public');

        var_dump(true === $bag->hasCacheControlDirective('public'));
        var_dump(true === $bag->getCacheControlDirective('public'));
        var_dump('public' === $bag->get('cache-control'));

        $bag->addCacheControlDirective('max-age', 10);
        var_dump(true === $bag->hasCacheControlDirective('max-age'));
        var_dump('10' === $bag->getCacheControlDirective('max-age'));
        var_dump('max-age=10, public' === $bag->get('cache-control'));

        $bag->removeCacheControlDirective('max-age');
        var_dump(false === $bag->hasCacheControlDirective('max-age'));
    }

    public function testCacheControlDirectiveParsing()
    {
        $bag = new HeaderBag(['cache-control' => 'public, max-age=10']);
        var_dump(true === $bag->hasCacheControlDirective('public'));
        var_dump(true === $bag->getCacheControlDirective('public'));

        var_dump(true === $bag->hasCacheControlDirective('max-age'));
        var_dump('10' === $bag->getCacheControlDirective('max-age'));

        $bag->addCacheControlDirective('s-maxage', 100);
        var_dump('max-age=10, public, s-maxage=100' === $bag->get('cache-control'));
    }

    public function testCacheControlDirectiveParsingQuotedZero()
    {
        $bag = new HeaderBag(['cache-control' => 'max-age="0"']);
        var_dump(true === $bag->hasCacheControlDirective('max-age'));
        var_dump('0' === $bag->getCacheControlDirective('max-age'));
    }

    public function testCacheControlDirectiveOverrideWithReplace()
    {
        $bag = new HeaderBag(['cache-control' => 'private, max-age=100']);
        $bag->replace(['cache-control' => 'public, max-age=10']);
        var_dump(true === $bag->hasCacheControlDirective('public'));
        var_dump(true === $bag->getCacheControlDirective('public'));

        var_dump(true === $bag->hasCacheControlDirective('max-age'));
        var_dump('10' === $bag->getCacheControlDirective('max-age'));
    }

    public function testCacheControlClone()
    {
        $headers = ['foo' => 'bar'];
        $bag1 = new HeaderBag($headers);
        $bag2 = new HeaderBag($bag1->all());

        var_dump($bag1->all() === $bag2->all());
    }

    public function testGetIterator()
    {
        $headers = ['foo' => 'bar', 'hello' => 'world', 'third' => 'charm'];
        $headerBag = new HeaderBag($headers);

        $i = 0;
        foreach ($headerBag->all() as $key => $val) {
            ++$i;
            var_dump([$headers[$key]] === $val);
        }

        var_dump(\count($headers) === $i);
    }

    public function testCountAll()
    {
        $headers = ['foo' => 'bar', 'HELLO' => 'WORLD'];
        $headerBag = new HeaderBag($headers);

        var_dump(\count($headers) === count($headerBag->all()));
    }
}
