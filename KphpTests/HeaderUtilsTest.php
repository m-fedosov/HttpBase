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

use Kaa\HttpBase\HeaderUtils;

class HeaderUtilsTest
{
    /**
     * @dataProvider provideHeaderToSplit
     */
    public function testSplit(): void
    {
        $data = self::provideHeaderToSplit();
        foreach ($data as $input) {
            $expected = $input[0];
            $header = (string)$input[1];
            $separator = (string)$input[2];

            var_dump($expected === HeaderUtils::split($header, $separator));
        }
    }

    /** @return mixed[][] */
    public static function provideHeaderToSplit()
    {
        return [
            [['foo=123', 'bar'], 'foo=123,bar', ','],
            [['foo=123', 'bar'], 'foo=123, bar', ','],
            [[['foo=123', 'bar']], 'foo=123; bar', ',;'],
            [[['foo=123'], ['bar']], 'foo=123, bar', ',;'],
            [['foo', '123, bar'], 'foo=123, bar', '='],
            [['foo', '123, bar'], ' foo = 123, bar ', '='],
            [[['foo', '123'], ['bar']], 'foo=123, bar', ',='],
            [[[['foo', '123']], [['bar'], ['foo', '456']]], 'foo=123, bar; foo=456', ',;='],
            [[[['foo', 'a,b;c=d']]], 'foo="a,b;c=d"', ',;='],

            [['foo', 'bar'], 'foo,,,, bar', ','],
            [['foo', 'bar'], ',foo, bar,', ','],
            [['foo', 'bar'], ' , foo, bar, ', ','],
            [['foo bar'], 'foo "bar"', ','],
            [['foo bar'], '"foo" bar', ','],
            [['foo bar'], '"foo" "bar"', ','],

            [[['foo_cookie', 'foo=1&bar=2&baz=3'], ['expires', 'Tue, 22-Sep-2020 06:27:09 GMT'], ['path', '/']], 'foo_cookie=foo=1&bar=2&baz=3; expires=Tue, 22-Sep-2020 06:27:09 GMT; path=/', ';='],
            [[['foo_cookie', 'foo=='], ['expires', 'Tue, 22-Sep-2020 06:27:09 GMT'], ['path', '/']], 'foo_cookie=foo==; expires=Tue, 22-Sep-2020 06:27:09 GMT; path=/', ';='],
            [[['foo_cookie', 'foo=a=b'], ['expires', 'Tue, 22-Sep-2020 06:27:09 GMT'], ['path', '/']], 'foo_cookie=foo="a=b"; expires=Tue, 22-Sep-2020 06:27:09 GMT; path=/', ';='],

            // These are not a valid header values. We test that they parse anyway,
            // and that both the valid and invalid parts are returned.
            [[], '', ','],
            [[], ',,,', ','],
            [['foo', 'bar', 'baz'], 'foo, "bar", "baz', ','],
            [['foo', 'bar, baz'], 'foo, "bar, baz', ','],
            [['foo', 'bar, baz\\'], 'foo, "bar, baz\\', ','],
            [['foo', 'bar, baz\\'], 'foo, "bar, baz\\\\', ','],
        ];
    }

    public function testCombine(): void
    {
        var_dump(['foo' => '123'] === HeaderUtils::combine([['foo', '123']]));
        var_dump(['foo' => true] === HeaderUtils::combine([['foo']]));
        var_dump(['foo' => true] === HeaderUtils::combine([['Foo']]));
        var_dump(['foo' => '123', 'bar' => true] === HeaderUtils::combine([['foo', '123'], ['bar']]));
    }

    public function testToString(): void
    {
        var_dump('foo' === HeaderUtils::toString(['foo' => true], ','));
        var_dump('foo; bar' === HeaderUtils::toString(['foo' => true, 'bar' => true], ';'));
        var_dump('foo=123' === HeaderUtils::toString(['foo' => '123'], ','));
        var_dump('foo="1 2 3"' === HeaderUtils::toString(['foo' => '1 2 3'], ','));
        var_dump('foo="1 2 3", bar' === HeaderUtils::toString(['foo' => '1 2 3', 'bar' => true], ','));
    }

    public function testQuote(): void
    {
        var_dump('foo' === HeaderUtils::quote('foo'));
        var_dump('az09!#$%&\'*.^_`|~-' === HeaderUtils::quote('az09!#$%&\'*.^_`|~-'));
        var_dump('"foo bar"' === HeaderUtils::quote('foo bar'));
        var_dump('"foo [bar]"' === HeaderUtils::quote('foo [bar]'));
        var_dump('"foo \"bar\""' === HeaderUtils::quote('foo "bar"'));
        var_dump('"foo \\\\ bar"' === HeaderUtils::quote('foo \\ bar'));
    }

    public function testUnquote(): void
    {
        var_dump('foo' === HeaderUtils::unquote('foo'));
        var_dump('az09!#$%&\'*.^_`|~-' === HeaderUtils::unquote('az09!#$%&\'*.^_`|~-'));
        var_dump('foo bar' === HeaderUtils::unquote('"foo bar"'));
        var_dump('foo [bar]' === HeaderUtils::unquote('"foo [bar]"'));
        var_dump('foo "bar"' === HeaderUtils::unquote('"foo \"bar\""'));
        var_dump('foo "bar"' === HeaderUtils::unquote('"foo \"\b\a\r\""'));
        var_dump('foo \\ bar' === HeaderUtils::unquote('"foo \\\\ bar"'));
    }

    public function testMakeDispositionInvalidDisposition(): void
    {
        try {
            HeaderUtils::makeDisposition('invalid', 'foo.html');
        } catch (\InvalidArgumentException $e) {
            var_dump($e->getMessage() === 'The disposition must be either "attachment" or "inline".');
        }
    }

    /**
     * @dataProvider provideMakeDisposition
     */
    public function testMakeDisposition(): void
    {
        $data = self::provideMakeDisposition();
        foreach ($data as $input) {
            $disposition = $input[0];
            $filename = $input[1];
            $filenameFallback = $input[2];
            $expected = $input[3];

            var_dump($expected === HeaderUtils::makeDisposition($disposition, $filename, $filenameFallback));
        }
    }

    /** @return string[][] */
    public static function provideMakeDisposition()
    {
        return [
            ['attachment', 'foo.html', 'foo.html', 'attachment; filename=foo.html'],
            ['attachment', 'foo.html', '', 'attachment; filename=foo.html'],
            ['attachment', 'foo bar.html', '', 'attachment; filename="foo bar.html"'],
            ['attachment', 'foo "bar".html', '', 'attachment; filename="foo \\"bar\\".html"'],
            ['attachment', 'foo%20bar.html', 'foo bar.html', 'attachment; filename="foo bar.html"; filename*=utf-8\'\'foo%2520bar.html'],
            ['attachment', 'föö.html', 'foo.html', 'attachment; filename=foo.html; filename*=utf-8\'\'f%C3%B6%C3%B6.html'],
        ];
    }

    /**
     * @dataProvider provideMakeDispositionFail
     */
    public function testMakeDispositionFail()
    {
        $data = self::provideMakeDispositionFail();
        foreach ($data as $input) {
            $disposition = $input[0];
            $filename = $input[1];
            try {
                HeaderUtils::makeDisposition($disposition, $filename);
            } catch (\InvalidArgumentException $e) {
                var_dump(strpos($e->getMessage(), 'The filename') !== false);
            }
        }
    }

    /** @return string[][] */
    public static function provideMakeDispositionFail()
    {
        return [
            ['attachment', 'foo%20bar.html'],
            ['attachment', 'foo/bar.html'],
            ['attachment', '/foo.html'],
            ['attachment', 'foo\bar.html'],
            ['attachment', '\foo.html'],
            ['attachment', 'föö.html'],
        ];
    }

    /**
     * @dataProvider provideParseQuery
     */
    public function testParseQuery()
    {
        $data = self::provideParseQuery();
        $i = 0;
        foreach ($data as $input) {
            $i++;
            $query = $input[0];
            if (count($input) > 1) {
                $expected = $input[1];
            } else {
                $expected = $input[0];
            }
            var_dump($expected === http_build_query(HeaderUtils::parseQuery($query), '', '&'));
        }
    }

    /** @return string[][] */
    public static function provideParseQuery()
    {
        return [
            ['a=b&c=d'],
            ['a.b=c'],
            ['a+b=c'],
            ["a\0b=c", 'a='],
            ['a%00b=c', 'a=c'],
            // fixme: this test need a bug fix. See HeaderUtils parseQuery()
//            ['a[b=c', 'a%5Bb=c'],
            ['a]b=c', 'a%5Db=c'],
            ['a[b]=c', 'a%5Bb%5D=c'],
            ['a[b][c.d]=c', 'a%5Bb%5D%5Bc.d%5D=c'],
            ['a%5Bb%5D=c'],
            ['f[%2525][%26][%3D][p.c]=d', 'f%5B%2525%5D%5B%26%5D%5B%3D%5D%5Bp.c%5D=d'],
        ];
    }

    public function testParseCookie(): void
    {
        $query = 'a.b=c; def%5Bg%5D=h';
        var_dump($query === http_build_query(HeaderUtils::parseQuery($query, false, ';'), '', '; '));
    }

    public function testParseQueryIgnoreBrackets()
    {
        var_dump(['a.b' => ['A', 'B']] === HeaderUtils::parseQuery('a.b=A&a.b=B', true));
        var_dump(['a.b[]' => ['A']] === HeaderUtils::parseQuery('a.b[]=A', true));
        var_dump(['a.b[]' => ['A']] === HeaderUtils::parseQuery('a.b%5B%5D=A', true));
    }
}
