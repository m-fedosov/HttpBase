<?php

namespace Kaa\HttpBase\KphpTests;

use Kaa\HttpBase\Cookie;
use Kaa\HttpBase\ResponseHeaderBag;

class ResponseHeaderBagTest
{
    public function testAllPreserveCase()
    {
        $headers = [
            'fOo' => 'BAR',
            'ETag' => 'xyzzy',
            'Content-MD5' => 'Q2hlY2sgSW50ZWdyaXR5IQ==',
            'P3P' => 'CP="CAO PSA OUR"',
            'WWW-Authenticate' => 'Basic realm="WallyWorld"',
            'X-UA-Compatible' => 'IE=edge,chrome=1',
            'X-XSS-Protection' => '1; mode=block',
        ];

        $bag = new ResponseHeaderBag($headers);
        $allPreservedCase = $bag->allPreserveCase();

        foreach (array_keys($headers) as $headerName) {
            var_dump(isset($allPreservedCase[$headerName]));
        }
    }

    public function testCacheControlHeader()
    {
        $bag = new ResponseHeaderBag([]);
        var_dump('no-cache, private' === $bag->get('Cache-Control'));
        var_dump(true === $bag->hasCacheControlDirective('no-cache'));

        $bag = new ResponseHeaderBag(['Cache-Control' => 'public']);
        var_dump('public' === $bag->get('Cache-Control'));
        var_dump(true === $bag->hasCacheControlDirective('public'));

        $bag = new ResponseHeaderBag(['ETag' => 'abcde']);
        var_dump('no-cache, private' === $bag->get('Cache-Control'));
        var_dump(true === $bag->hasCacheControlDirective('private'));
        var_dump(true === $bag->hasCacheControlDirective('no-cache'));
        var_dump(false === $bag->hasCacheControlDirective('max-age'));

        $bag = new ResponseHeaderBag(['Expires' => 'Wed, 16 Feb 2011 14:17:43 GMT']);
        var_dump('private, must-revalidate' === $bag->get('Cache-Control'));

        $bag = new ResponseHeaderBag([
            'Expires' => 'Wed, 16 Feb 2011 14:17:43 GMT',
            'Cache-Control' => 'max-age=3600',
        ]);
        var_dump('max-age=3600, private' === $bag->get('Cache-Control'));

        $bag = new ResponseHeaderBag(['Last-Modified' => 'abcde']);
        var_dump('private, must-revalidate' === $bag->get('Cache-Control'));

        $bag = new ResponseHeaderBag(['Etag' => 'abcde', 'Last-Modified' => 'abcde']);
        var_dump('private, must-revalidate' === $bag->get('Cache-Control'));

        $bag = new ResponseHeaderBag(['cache-control' => 'max-age=100']);
        var_dump('max-age=100, private' === $bag->get('Cache-Control'));

        $bag = new ResponseHeaderBag(['cache-control' => 's-maxage=100']);
        var_dump('s-maxage=100' === $bag->get('Cache-Control'));

        $bag = new ResponseHeaderBag(['cache-control' => 'private, max-age=100']);
        var_dump('max-age=100, private' === $bag->get('Cache-Control'));

        $bag = new ResponseHeaderBag(['cache-control' => 'public, max-age=100']);
        var_dump('max-age=100, public' === $bag->get('Cache-Control'));

        $bag = new ResponseHeaderBag();
        $bag->set('Last-Modified', 'abcde');
        var_dump('private, must-revalidate' === $bag->get('Cache-Control'));

        $bag = new ResponseHeaderBag();
        $bag->set('Cache-Control', ['public', 'must-revalidate']);
        var_dump(1 === count($bag->all('Cache-Control')));
        var_dump('must-revalidate, public' === $bag->get('Cache-Control'));

        $bag = new ResponseHeaderBag();
        $bag->set('Cache-Control', 'public');
        $bag->set('Cache-Control', 'must-revalidate', false);
        var_dump(1 === count($bag->all('Cache-Control')));
        var_dump('must-revalidate, public' === $bag->get('Cache-Control'));
    }

    public function testCacheControlClone()
    {
        $headers = ['foo' => 'bar'];
        $bag1 = new ResponseHeaderBag($headers);
        $bag2 = new ResponseHeaderBag($bag1->allPreserveCase());
        var_dump($bag1->allPreserveCase() === $bag2->allPreserveCase());
    }

    public function testToStringIncludesCookieHeaders()
    {
        $bag = new ResponseHeaderBag([]);
        $bag->setCookie(Cookie::create('foo', 'bar'));

        $this->assertSetCookieHeader('foo=bar; path=/; httponly; samesite=lax', $bag);

        $bag->clearCookie('foo');

        var_dump(true === $this->assertSetCookieHeader('foo=deleted; expires=' . gmdate('D, d M Y H:i:s T', time() - 31536001) . '; Max-Age=0; path=/; httponly', $bag));
    }

    public function testClearCookieSecureNotHttpOnly()
    {
        $bag = new ResponseHeaderBag([]);

        $bag->clearCookie('foo', '/', null, true, false);

        var_dump(true === $this->assertSetCookieHeader('foo=deleted; expires='.gmdate('D, d M Y H:i:s T', time() - 31536001).'; Max-Age=0; path=/; secure', $bag));
    }

    public function testClearCookieSamesite()
    {
        $bag = new ResponseHeaderBag([]);

        $bag->clearCookie('foo', '/', null, true, false, 'none');
        var_dump(true === $this->assertSetCookieHeader('foo=deleted; expires='.gmdate('D, d M Y H:i:s T', time() - 31536001).'; Max-Age=0; path=/; secure; samesite=none', $bag));
    }

    public function testReplace()
    {
        $bag = new ResponseHeaderBag([]);
        var_dump('no-cache, private' === $bag->get('Cache-Control'));
        var_dump(true === $bag->hasCacheControlDirective('no-cache'));

        $bag->replace(['Cache-Control' => 'public']);
        var_dump('public' === $bag->get('Cache-Control'));
        var_dump(true === $bag->hasCacheControlDirective('public'));
    }

    public function testReplaceWithRemove()
    {
        $bag = new ResponseHeaderBag([]);
        var_dump('no-cache, private' === $bag->get('Cache-Control'));
        var_dump(true === $bag->hasCacheControlDirective('no-cache'));

        $bag->remove('Cache-Control');
        $bag->replace([]);
        var_dump('no-cache, private' === $bag->get('Cache-Control'));
        var_dump(true === $bag->hasCacheControlDirective('no-cache'));
    }

    public function testCookiesWithSameNames()
    {
        $bag = new ResponseHeaderBag();
        $bag->setCookie(Cookie::create('foo', 'bar', 0, '/path/foo', 'foo.bar'));
        $bag->setCookie(Cookie::create('foo', 'bar', 0, '/path/bar', 'foo.bar'));
        $bag->setCookie(Cookie::create('foo', 'bar', 0, '/path/bar', 'bar.foo'));
        $bag->setCookie(Cookie::create('foo', 'bar'));

        var_dump(4 === count($bag->getCookiesFlat()));
        var_dump('foo=bar; path=/path/foo; domain=foo.bar; httponly; samesite=lax' === $bag->get('set-cookie'));
        var_dump([
            'foo=bar; path=/path/foo; domain=foo.bar; httponly; samesite=lax',
            'foo=bar; path=/path/bar; domain=foo.bar; httponly; samesite=lax',
            'foo=bar; path=/path/bar; domain=bar.foo; httponly; samesite=lax',
            'foo=bar; path=/; httponly; samesite=lax',
        ] === $bag->all('set-cookie'));

        $this->assertSetCookieHeader('foo=bar; path=/path/foo; domain=foo.bar; httponly; samesite=lax', $bag);
        $this->assertSetCookieHeader('foo=bar; path=/path/bar; domain=foo.bar; httponly; samesite=lax', $bag);
        $this->assertSetCookieHeader('foo=bar; path=/path/bar; domain=bar.foo; httponly; samesite=lax', $bag);
        $this->assertSetCookieHeader('foo=bar; path=/; httponly; samesite=lax', $bag);

        $cookies = $bag->getCookiesArray();

        var_dump(true === isset($cookies['foo.bar']['/path/foo']['foo']));
        var_dump(true === isset($cookies['foo.bar']['/path/bar']['foo']));
        var_dump(true === isset($cookies['bar.foo']['/path/bar']['foo']));
        var_dump(true === isset($cookies['']['/']['foo']));
    }

    public function testRemoveCookie()
    {
        $bag = new ResponseHeaderBag();
        var_dump(false === $bag->has('set-cookie'));

        $bag->setCookie(Cookie::create('foo', 'bar', 0, '/path/foo', 'foo.bar'));
        $bag->setCookie(Cookie::create('bar', 'foo', 0, '/path/bar', 'foo.bar'));
        var_dump(true === $bag->has('set-cookie'));

        $cookies = $bag->getCookiesArray();
        var_dump(true === isset($cookies['foo.bar']['/path/foo']));

        $bag->removeCookie('foo', '/path/foo', 'foo.bar');
        var_dump(true === $bag->has('set-cookie'));

        $cookies = $bag->getCookiesArray();
        var_dump(false === isset($cookies['foo.bar']['/path/foo']));

        $bag->removeCookie('bar', '/path/bar', 'foo.bar');
        var_dump(false === $bag->has('set-cookie'));

        $cookies = $bag->getCookiesArray();
        var_dump(false === isset($cookies['foo.bar']));
    }

    public function testRemoveCookieWithNullRemove()
    {
        $bag = new ResponseHeaderBag();
        $bag->setCookie(Cookie::create('foo', 'bar'));
        $bag->setCookie(Cookie::create('bar', 'foo'));

        $cookies = $bag->getCookiesArray();
        var_dump(true === isset($cookies['']['/']));

        $bag->removeCookie('foo', null);
        $cookies = $bag->getCookiesArray();
        var_dump(false === isset($cookies['']['/']['foo']));

        $bag->removeCookie('bar', null);
        $cookies = $bag->getCookiesArray();
        var_dump(false === isset($cookies['']['/']['bar']));
    }

    public function testSetCookieHeader()
    {
        $bag = new ResponseHeaderBag();
        $bag->set('set-cookie', 'foo=bar');
        var_dump((string)[Cookie::create('foo', 'bar', 0, '/', null, false, false, true, null)][0] === (string)$bag->getCookiesFlat()[0]);


        $bag->set('set-cookie', 'foo2=bar2', false);
        $bagCookies = [];
        foreach($bag->getCookiesFlat() as $cookie){
            $bagCookies []= (string)$cookie;
        }

        var_dump([
                (string)Cookie::create('foo', 'bar', 0, '/', null, false, false, true, null),
                (string)Cookie::create('foo2', 'bar2', 0, '/', null, false, false, true, null),
        ] == $bagCookies);

        $bag->remove('set-cookie');
        var_dump([] === $bag->getCookiesFlat());
    }

    public function testToStringDoesntMessUpHeaders()
    {
        $headers = new ResponseHeaderBag();

        $headers->set('Location', 'http://www.symfony.com');
        $headers->set('Content-type', 'text/html');

        (string) $headers;

        $allHeaders = $headers->allPreserveCase();
        var_dump(['http://www.symfony.com'] === $allHeaders['Location']);
        var_dump(['text/html'] === $allHeaders['Content-type']);
    }

    public function testDateHeaderAddedOnCreation()
    {
        $now = time();

        $bag = new ResponseHeaderBag();
        var_dump(true === $bag->has('Date'));

        var_dump($now === $bag->getDate('Date')->getTimestamp());
    }

    public function testDateHeaderCanBeSetOnCreation()
    {
        $someDate = 'Thu, 23 Mar 2017 09:15:12 GMT';
        $bag = new ResponseHeaderBag(['Date' => $someDate]);

        var_dump($someDate === $bag->get('Date'));
    }

    public function testDateHeaderWillBeRecreatedWhenRemoved()
    {
        $someDate = 'Thu, 23 Mar 2017 09:15:12 GMT';
        $bag = new ResponseHeaderBag(['Date' => $someDate]);
        $bag->remove('Date');

        // a (new) Date header is still present
        var_dump(true === $bag->has('Date'));
        var_dump($someDate !== $bag->get('Date'));
    }

    public function testDateHeaderWillBeRecreatedWhenHeadersAreReplaced()
    {
        $bag = new ResponseHeaderBag();
        $bag->replace([]);

        var_dump(true === $bag->has('Date'));
    }

    private function assertSetCookieHeader(string $expected, ResponseHeaderBag $actual): bool
    {
        $count = 0;
        $matches = [];
        return preg_match('#^Set-Cookie:\s+' . preg_quote($expected, '#') . '$#m', str_replace("\r\n", "\n", (string) $actual, $count), $matches) !== false;
    }
}
