<?php

namespace Kaa\HttpBase\KphpTests;

class RequestTest
{
    public function testInitialize()
    {
        $request = new Request();

        $request->initialize(['foo' => 'bar']);
        var_dump('bar' === $request->query->get('foo'));

        $request->initialize([], ['foo' => 'bar']);
        var_dump('bar' === $request->request->get('foo'));

        $request->initialize([], [], ['foo' => 'bar']);
        var_dump('bar' === $request->attributes->get('foo'));

        $request->initialize([], [], [], [], [], ['HTTP_FOO' => 'bar']);
        var_dump('bar' === $request->headers->get('FOO'));
    }

//    public function testGetLocale()
//    {
//        $request = new Request();
//        $request->setLocale('pl');
//        $locale = $request->getLocale();
//        var_dump('pl' === $locale);
//    }

    public function testGetUser()
    {
        $request = Request::create('http://user:password@test.com');
        $user = $request->getUser();

        var_dump('user' === $user);
    }

    public function testGetPassword()
    {
        $request = Request::create('http://user:password@test.com');
        $password = $request->getPassword();

        var_dump('password' === $password);
    }

    public function testIsNoCache()
    {
        $request = new Request();
        $isNoCache = $request->isNoCache();

        var_dump(false === $isNoCache);
    }

    public function testGetContentTypeFormat()
    {
        $request = new Request();
        var_dump(null === $request->getContentTypeFormat());

        $server = ['HTTP_CONTENT_TYPE' => 'application/json'];
        $request = new Request([], [], [], [], [], $server);
        var_dump('json' === $request->getContentTypeFormat());

        $server = ['HTTP_CONTENT_TYPE' => 'text/html'];
        $request = new Request([], [], [], [], [], $server);
        var_dump('html' === $request->getContentTypeFormat());
    }

//    public function testSetDefaultLocale()
//    {
//        $request = new Request();
//        $request->setDefaultLocale('pl');
//        $locale = $request->getLocale();
//
//        var_dump('pl' === $locale);
//    }
//
    public function testCreate()
    {
        $request = Request::create('http://test.com/foo?bar=baz');
        var_dump('http://test.com/foo?bar=baz' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('bar=baz' === $request->getQueryString());
        var_dump(80 === $request->getPort());
        var_dump('test.com' === $request->getHttpHost());
        var_dump(false === $request->isSecure());

        $request = Request::create('http://test.com/foo', 'GET', ['bar' => 'baz']);
        var_dump('http://test.com/foo?bar=baz' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('bar=baz' === $request->getQueryString());
        var_dump(80 === $request->getPort());
        var_dump('test.com' === $request->getHttpHost());
        var_dump(false === $request->isSecure());

        $request = Request::create('http://test.com/foo?bar=foo', 'GET', ['bar' => 'baz']);
        var_dump('http://test.com/foo?bar=baz' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('bar=baz' === $request->getQueryString());
        var_dump(80 === $request->getPort());
        var_dump('test.com' === $request->getHttpHost());
        var_dump(false === $request->isSecure());

        $request = Request::create('https://test.com/foo?foo.bar=baz');
        var_dump('https://test.com/foo?foo.bar=baz' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('foo.bar=baz' === $request->getQueryString());
        var_dump(443 === $request->getPort());
        var_dump('test.com' === $request->getHttpHost());
        var_dump(true === $request->isSecure());
        var_dump(['foo.bar' => 'baz'] === $request->query->all());

        $request = Request::create('test.com:90/foo');
        var_dump('http://test.com:90/foo' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('test.com' === $request->getHost());
        var_dump('test.com:90' === $request->getHttpHost());
        var_dump(90 === $request->getPort());
        var_dump(false === $request->isSecure());

        $request = Request::create('https://test.com:90/foo');
        var_dump('https://test.com:90/foo' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('test.com' === $request->getHost());
        var_dump('test.com:90' === $request->getHttpHost());
        var_dump(90 === $request->getPort());
        var_dump(true === $request->isSecure());

        $request = Request::create('https://127.0.0.1:90/foo');
        var_dump('https://127.0.0.1:90/foo' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('127.0.0.1' === $request->getHost());
        var_dump('127.0.0.1:90' === $request->getHttpHost());
        var_dump(90 === $request->getPort());
        var_dump(true === $request->isSecure());

        $request = Request::create('https://[::1]:90/foo');
        var_dump('https://[::1]:90/foo' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('[::1]' === $request->getHost());
        var_dump('[::1]:90' === $request->getHttpHost());
        var_dump(90 === $request->getPort());
        var_dump(true === $request->isSecure());

        $request = Request::create('https://[::1]/foo');
        var_dump('https://[::1]/foo' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('[::1]' === $request->getHost());
        var_dump('[::1]' === $request->getHttpHost());
        var_dump(443 === $request->getPort());
        var_dump(true === $request->isSecure());

        $json = '{"jsonrpc":"2.0","method":"echo","id":7,"params":["Hello World"]}';
        $request = Request::create('http://example.com/jsonrpc', 'POST', [], [], [], [], $json);
        var_dump($json === $request->getContent());
        var_dump(false === $request->isSecure());

        $request = Request::create('http://test.com');
        var_dump('http://test.com/' === $request->getUri());
        var_dump('/' === $request->getPathInfo());
        var_dump(null === $request->getQueryString());
        var_dump(80 === $request->getPort());
        var_dump('test.com' === $request->getHttpHost());
        var_dump(false === $request->isSecure());

        $request = Request::create('http://test.com?test=1');
        var_dump('http://test.com/?test=1' === $request->getUri());
        var_dump('/' === $request->getPathInfo());
        var_dump('test=1' === $request->getQueryString());
        var_dump(80 === $request->getPort());
        var_dump('test.com' === $request->getHttpHost());
        var_dump(false === $request->isSecure());

        $request = Request::create('http://test.com:90/?test=1');
        var_dump('http://test.com:90/?test=1' === $request->getUri());
        var_dump('/' === $request->getPathInfo());
        var_dump('test=1' === $request->getQueryString());
        var_dump(90 === $request->getPort());
        var_dump('test.com:90' === $request->getHttpHost());
        var_dump(false === $request->isSecure());

        $request = Request::create('http://username:password@test.com');
        var_dump('http://test.com/' === $request->getUri());
        var_dump('/' === $request->getPathInfo());
        var_dump(null === $request->getQueryString());
        var_dump(80 === $request->getPort());
        var_dump('test.com' === $request->getHttpHost());
        var_dump('username' === $request->getUser());
        var_dump('password' === $request->getPassword());
        var_dump(false === $request->isSecure());

        $request = Request::create('http://username@test.com');
        var_dump('http://test.com/' === $request->getUri());
        var_dump('/' === $request->getPathInfo());
        var_dump(null === $request->getQueryString());
        var_dump(80 === $request->getPort());
        var_dump('test.com' === $request->getHttpHost());
        var_dump('username' === $request->getUser());
        var_dump('' === $request->getPassword());
        var_dump(false === $request->isSecure());

        $request = Request::create('http://test.com/?foo');
        var_dump('/?foo' === $request->getRequestUri());
        var_dump(['foo' => ''] === $request->query->all());

        // assume rewrite rule: (.*) --> app/app.php; app/ is a symlink to a symfony web/ directory
        $request = Request::create(
            'http://test.com/apparthotel-1234',
            'GET',
            [],
            [],
            [],
            [
                'DOCUMENT_ROOT' => '/var/www/www.test.com',
                'SCRIPT_FILENAME' => '/var/www/www.test.com/app/app.php',
                'SCRIPT_NAME' => '/app/app.php',
                'PHP_SELF' => '/app/app.php/apparthotel-1234',
            ]
        );
        var_dump('http://test.com/apparthotel-1234' === $request->getUri());
        var_dump('/apparthotel-1234' === $request->getPathInfo());
        var_dump(null === $request->getQueryString());
        var_dump(80 === $request->getPort());
        var_dump('test.com' === $request->getHttpHost());
        var_dump(false === $request->isSecure());

        // Fragment should not be included in the URI
        $request = Request::create('http://test.com/foo#bar');
        var_dump('http://test.com/foo' === $request->getUri());
    }

    public function testCreateWithRequestUri()
    {
        $request = Request::create('http://test.com:80/foo');
        $request->server->set('REQUEST_URI', 'http://test.com:80/foo');
        var_dump('http://test.com/foo' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('test.com' === $request->getHost());
        var_dump('test.com' === $request->getHttpHost());
        var_dump(80 === $request->getPort());
        var_dump(false === $request->isSecure());

        $request = Request::create('http://test.com:8080/foo');
        $request->server->set('REQUEST_URI', 'http://test.com:8080/foo');
        var_dump('http://test.com:8080/foo' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('test.com' === $request->getHost());
        var_dump('test.com:8080' === $request->getHttpHost());
        var_dump(8080 === $request->getPort());
        var_dump(false === $request->isSecure());

        $request = Request::create('http://test.com/foo?bar=foo', 'GET', ['bar' => 'baz']);
        $request->server->set('REQUEST_URI', 'http://test.com/foo?bar=foo');
        var_dump('http://test.com/foo?bar=baz' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('bar=baz' === $request->getQueryString());
        var_dump('test.com' === $request->getHost());
        var_dump('test.com' === $request->getHttpHost());
        var_dump(80 === $request->getPort());
        var_dump(false === $request->isSecure());

        $request = Request::create('https://test.com:443/foo');
        $request->server->set('REQUEST_URI', 'https://test.com:443/foo');
        var_dump('https://test.com/foo' === $request->getUri());
        var_dump('/foo' === $request->getPathInfo());
        var_dump('test.com' === $request->getHost());
        var_dump('test.com' === $request->getHttpHost());
        var_dump(443 === $request->getPort());
        var_dump(true === $request->isSecure());

        // Fragment should not be included in the URI
        $request = Request::create('http://test.com/foo#bar');
        $request->server->set('REQUEST_URI', 'http://test.com/foo#bar');
        var_dump('http://test.com/foo' === $request->getUri());
    }

    /**
     * @dataProvider getRequestUriData
     */
    public function testGetRequestUri()
    {
        $data = [
                    ['/foo', '/foo'],
                    ['//bar/foo', '//bar/foo'],
                    ['///bar/foo', '///bar/foo'],
                    ['http://test.com/foo?bar=baz', '/foo?bar=baz'],

                    ['http://test.com:80/foo', '/foo'],
                    ['https://test.com:8080/foo', '/foo'],
                    ['https://test.com:443/foo', '/foo'],

                    ['http://test.com/foo#bar', '/foo'],
                    ['/foo#bar', '/foo']
                ];
        foreach ($data as $inputOutput) {
            $serverRequestUri = $inputOutput[0];
            $expected = $inputOutput[1];

            $request = new Request();
            $request->server->add([
                'REQUEST_URI' => $serverRequestUri,

                // For having http://test.com
                'SERVER_NAME' => 'test.com',
                'SERVER_PORT' => '80',
            ]);

            var_dump($expected === $request->getRequestUri());
            var_dump($expected === $request->server->get('REQUEST_URI'));
        }
    }

    public function testGetRequestUriWithoutRequiredHeader()
    {
        $expected = '';

        $request = new Request();

        $message = 'Fallback to empty URI when headers are missing.';
        var_dump($expected === $request->getRequestUri());
        var_dump($expected === $request->server->get('REQUEST_URI'));
    }

    public function testCreateCheckPrecedence()
    {
        // server is used by default
        $request = Request::create('/', 'DELETE', [], [], [], [
            'HTTP_HOST' => 'example.com',
            'HTTPS' => 'on',
            'SERVER_PORT' => '443',
            'PHP_AUTH_USER' => 'fabien',
            'PHP_AUTH_PW' => 'pa$$',
            'QUERY_STRING' => 'foo=bar',
            'CONTENT_TYPE' => 'application/json',
        ]);
        var_dump('example.com' === $request->getHost());
        var_dump(443 === $request->getPort());
        var_dump(true === $request->isSecure());
        var_dump('fabien' === $request->getUser());
        var_dump('pa$$' === $request->getPassword());
        var_dump(null === $request->getQueryString());
        var_dump('application/json' === $request->headers->get('CONTENT_TYPE'));

        // URI has precedence over server
        $request = Request::create('http://thomas:pokemon@example.net:8080/?foo=bar', 'GET', [], [], [], [
            'HTTP_HOST' => 'example.com',
            'HTTPS' => 'on',
            'SERVER_PORT' => '443',
        ]);
        var_dump('example.net' === $request->getHost());
        var_dump(8080 === $request->getPort());
        var_dump(false === $request->isSecure());
        var_dump('thomas' === $request->getUser());
        var_dump('pokemon' === $request->getPassword());
        var_dump('foo=bar' === $request->getQueryString());
    }

    public function testDuplicate()
    {
        $request = new Request(['foo' => 'bar'], ['foo' => 'bar'], ['foo' => 'bar'], [], [], ['HTTP_FOO' => 'bar']);
        $dup = $request->duplicate();

        var_dump($request->query->all() === $dup->query->all());
        var_dump($request->request->all() === $dup->request->all());
        var_dump($request->attributes->all() === $dup->attributes->all());
        var_dump($request->headers->all() === $dup->headers->all());

        $dup = $request->duplicate(['foo' => 'foobar'], ['foo' => 'foobar'], ['foo' => 'foobar'], [], [], ['HTTP_FOO' => 'foobar']);

        var_dump(['foo' => 'foobar'] === $dup->query->all());
        var_dump(['foo' => 'foobar'] === $dup->request->all());
        var_dump(['foo' => 'foobar'] === $dup->attributes->all());
        var_dump(['foo' => ['foobar']] === $dup->headers->all());
    }

    public function testDuplicateWithFormat()
    {
        $request = new Request([], [], ['_format' => 'json']);
        $dup = $request->duplicate();

        var_dump('json' === $dup->getRequestFormat());
        var_dump('json' === $dup->attributes->get('_format'));

        $request = new Request();
        $request->setRequestFormat('xml');
        $dup = $request->duplicate();

        var_dump('xml' === $dup->getRequestFormat());
    }

    public function testGetPreferredFormat()
    {
        $request = new Request();
        var_dump(null === $request->getPreferredFormat(null));
        var_dump('html' === $request->getPreferredFormat());
        var_dump('json' === $request->getPreferredFormat('json'));

        $request->setRequestFormat('atom');
        $request->headers->set('Accept', 'application/ld+json');
        var_dump('atom' === $request->getPreferredFormat());

        $request = new Request();
        $request->headers->set('Accept', 'application/xml');
        var_dump('xml' === $request->getPreferredFormat());

        $request = new Request();
        $request->headers->set('Accept', 'application/xml');
        var_dump('xml' === $request->getPreferredFormat());

        $request = new Request();
        $request->headers->set('Accept', 'application/json;q=0.8,application/xml;q=0.9');
        var_dump('xml' === $request->getPreferredFormat());
    }

    public function testGetFormatFromMimeType()
    {
        $data = self::getFormatToMimeTypeMapProvider();

        foreach ($data as $inputOutput) {
            $format = (string)$inputOutput[0];
            $mimeTypes = array_map('strval', $inputOutput[1]);

            $request = new Request();
            foreach ($mimeTypes as $mime) {
                var_dump($format === $request->getFormat($mime));
            }
            $request->setFormat($format, $mimeTypes);
            foreach ($mimeTypes as $mime) {
                var_dump($format === $request->getFormat($mime));

                if (!$format) {
                    var_dump($mimeTypes[0] === $request->getMimeType($format));
                }
            }
        }
    }

    public function testGetFormatFromMimeTypeWithParameters()
    {
        $request = new Request();
        var_dump('json' === $request->getFormat('application/json; charset=utf-8'));
        var_dump('json' === $request->getFormat('application/json;charset=utf-8'));
        var_dump('json' === $request->getFormat('application/json ; charset=utf-8'));
        var_dump('json' === $request->getFormat('application/json ;charset=utf-8'));
    }

    /**
     * @dataProvider getFormatToMimeTypeMapProvider
     */
    public function testGetMimeTypeFromFormat()
    {
        $data = self::getFormatToMimeTypeMapProvider();

        foreach ($data as $inputOutput) {
            $format = (string)$inputOutput[0];
            $mimeTypes = array_map('strval', $inputOutput[1]);

            $request = new Request();
            var_dump($mimeTypes[0] === $request->getMimeType($format));
        }
    }

    /**
     * @dataProvider getFormatToMimeTypeMapProvider
     */
    public function testGetMimeTypesFromFormat()
    {
        $data = self::getFormatToMimeTypeMapProvider();

        foreach ($data as $inputOutput) {
            $format = (string)$inputOutput[0];
            $mimeTypes = array_map('strval', $inputOutput[1]);

            var_dump($mimeTypes === Request::getMimeTypes($format));
        }
    }

    public function testGetMimeTypesFromInexistentFormat()
    {
        $request = new Request();
        var_dump(null === $request->getMimeType('foo'));
        var_dump([] === Request::getMimeTypes('foo'));
    }

    public function testGetFormatWithCustomMimeType()
    {
        $request = new Request();
        $request->setFormat('custom', 'application/vnd.foo.api;myversion=2.3');
        var_dump('custom' === $request->getFormat('application/vnd.foo.api;myversion=2.3'));
    }

    /** @return mixed[][] */
    public static function getFormatToMimeTypeMapProvider()
    {
        return [
            ['txt', ['text/plain']],
            ['js', ['application/javascript', 'application/x-javascript', 'text/javascript']],
            ['css', ['text/css']],
            ['json', ['application/json', 'application/x-json']],
            ['jsonld', ['application/ld+json']],
            ['xml', ['text/xml', 'application/xml', 'application/x-xml']],
            ['rdf', ['application/rdf+xml']],
            ['atom', ['application/atom+xml']],
            ['form', ['application/x-www-form-urlencoded', 'multipart/form-data']],
        ];
    }

    public function testGetUri()
    {
        $server = [];

        // Standard Request on non default PORT
        // http://host:8080/index.php/path/info?query=string

        $server['HTTP_HOST'] = 'host:8080';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '8080';

        $server['QUERY_STRING'] = 'query=string';
        $server['REQUEST_URI'] = '/index.php/path/info?query=string';
        $server['SCRIPT_NAME'] = '/index.php';
        $server['PATH_INFO'] = '/path/info';
        $server['PATH_TRANSLATED'] = 'redirect:/index.php/path/info';
        $server['PHP_SELF'] = '/index_dev.php/path/info';
        $server['SCRIPT_FILENAME'] = '/some/where/index.php';

        $request = new Request();

        $request->initialize([], [], [], [], [], $server);

        var_dump('http://host:8080/index.php/path/info?query=string' === $request->getUri());

        // Use std port number
        $server['HTTP_HOST'] = 'host';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize([], [], [], [], [], $server);

        var_dump('http://host/index.php/path/info?query=string' === $request->getUri());

        // Without HOST HEADER
        unset($server['HTTP_HOST']);
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize([], [], [], [], [], $server);

        var_dump('http://servername/index.php/path/info?query=string' === $request->getUri());

        // Request with URL REWRITING (hide index.php)
        //   RewriteCond %{REQUEST_FILENAME} !-f
        //   RewriteRule ^(.*)$ index.php [QSA,L]
        // http://host:8080/path/info?query=string
        $server = [];
        $server['HTTP_HOST'] = 'host:8080';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '8080';

        $server['REDIRECT_QUERY_STRING'] = 'query=string';
        $server['REDIRECT_URL'] = '/path/info';
        $server['SCRIPT_NAME'] = '/index.php';
        $server['QUERY_STRING'] = 'query=string';
        $server['REQUEST_URI'] = '/path/info?toto=test&1=1';
        $server['SCRIPT_NAME'] = '/index.php';
        $server['PHP_SELF'] = '/index.php';
        $server['SCRIPT_FILENAME'] = '/some/where/index.php';

        $request->initialize([], [], [], [], [], $server);
        var_dump('http://host:8080/path/info?query=string' === $request->getUri());

        // Use std port number
        //  http://host/path/info?query=string
        $server['HTTP_HOST'] = 'host';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize([], [], [], [], [], $server);

        var_dump('http://host/path/info?query=string' === $request->getUri());

        // Without HOST HEADER
        unset($server['HTTP_HOST']);
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize([], [], [], [], [], $server);

        var_dump('http://servername/path/info?query=string' === $request->getUri());

        // With encoded characters

        $server = [
            'HTTP_HOST' => 'host:8080',
            'SERVER_NAME' => 'servername',
            'SERVER_PORT' => '8080',
            'QUERY_STRING' => 'query=string',
            'REQUEST_URI' => '/ba%20se/index_dev.php/foo%20bar/in+fo?query=string',
            'SCRIPT_NAME' => '/ba se/index_dev.php',
            'PATH_TRANSLATED' => 'redirect:/index.php/foo bar/in+fo',
            'PHP_SELF' => '/ba se/index_dev.php/path/info',
            'SCRIPT_FILENAME' => '/some/where/ba se/index_dev.php',
        ];

        $request->initialize([], [], [], [], [], $server);

        var_dump(
            'http://host:8080/ba%20se/index_dev.php/foo%20bar/in+fo?query=string' ===
            $request->getUri()
        );

        // with user info

        $server['PHP_AUTH_USER'] = 'fabien';
        $request->initialize([], [], [], [], [], $server);
        var_dump('http://host:8080/ba%20se/index_dev.php/foo%20bar/in+fo?query=string' === $request->getUri());

        $server['PHP_AUTH_PW'] = 'symfony';
        $request->initialize([], [], [], [], [], $server);
        var_dump('http://host:8080/ba%20se/index_dev.php/foo%20bar/in+fo?query=string' === $request->getUri());
    }

    public function testGetUriForPath()
    {
        $request = Request::create('http://test.com/foo?bar=baz');
        var_dump('http://test.com/some/path' === $request->getUriForPath('/some/path'));

        $request = Request::create('http://test.com:90/foo?bar=baz');
        var_dump('http://test.com:90/some/path' === $request->getUriForPath('/some/path'));

        $request = Request::create('https://test.com/foo?bar=baz');
        var_dump('https://test.com/some/path' === $request->getUriForPath('/some/path'));

        $request = Request::create('https://test.com:90/foo?bar=baz');
        var_dump('https://test.com:90/some/path' === $request->getUriForPath('/some/path'));

        $server = [];

        // Standard Request on non default PORT
        // http://host:8080/index.php/path/info?query=string

        $server['HTTP_HOST'] = 'host:8080';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '8080';

        $server['QUERY_STRING'] = 'query=string';
        $server['REQUEST_URI'] = '/index.php/path/info?query=string';
        $server['SCRIPT_NAME'] = '/index.php';
        $server['PATH_INFO'] = '/path/info';
        $server['PATH_TRANSLATED'] = 'redirect:/index.php/path/info';
        $server['PHP_SELF'] = '/index_dev.php/path/info';
        $server['SCRIPT_FILENAME'] = '/some/where/index.php';

        $request = new Request();

        $request->initialize([], [], [], [], [], $server);

        var_dump('http://host:8080/index.php/some/path' === $request->getUriForPath('/some/path'));

        // Use std port number
        $server['HTTP_HOST'] = 'host';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize([], [], [], [], [], $server);

        var_dump('http://host/index.php/some/path' === $request->getUriForPath('/some/path'));

        // Without HOST HEADER
        unset($server['HTTP_HOST']);
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize([], [], [], [], [], $server);

        var_dump('http://servername/index.php/some/path' === $request->getUriForPath('/some/path'));

        // Request with URL REWRITING (hide index.php)
        //   RewriteCond %{REQUEST_FILENAME} !-f
        //   RewriteRule ^(.*)$ index.php [QSA,L]
        // http://host:8080/path/info?query=string
        $server = [];
        $server['HTTP_HOST'] = 'host:8080';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '8080';

        $server['REDIRECT_QUERY_STRING'] = 'query=string';
        $server['REDIRECT_URL'] = '/path/info';
        $server['SCRIPT_NAME'] = '/index.php';
        $server['QUERY_STRING'] = 'query=string';
        $server['REQUEST_URI'] = '/path/info?toto=test&1=1';
        $server['SCRIPT_NAME'] = '/index.php';
        $server['PHP_SELF'] = '/index.php';
        $server['SCRIPT_FILENAME'] = '/some/where/index.php';

        $request->initialize([], [], [], [], [], $server);
        var_dump('http://host:8080/some/path' === $request->getUriForPath('/some/path'));

        // Use std port number
        //  http://host/path/info?query=string
        $server['HTTP_HOST'] = 'host';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize([], [], [], [], [], $server);

        var_dump('http://host/some/path' === $request->getUriForPath('/some/path'));

        // Without HOST HEADER
        unset($server['HTTP_HOST']);
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize([], [], [], [], [], $server);

        var_dump('http://servername/some/path' === $request->getUriForPath('/some/path'));
        var_dump('servername' === $request->getHttpHost());

        // with user info

        $server['PHP_AUTH_USER'] = 'fabien';
        $request->initialize([], [], [], [], [], $server);
        var_dump('http://servername/some/path' === $request->getUriForPath('/some/path'));

        $server['PHP_AUTH_PW'] = 'symfony';
        $request->initialize([], [], [], [], [], $server);
        var_dump('http://servername/some/path' === $request->getUriForPath('/some/path'));
    }

    /**
     * @dataProvider getRelativeUriForPathData
     */
    public function testGetRelativeUriForPath()
    {
        $data = self::getRelativeUriForPathData();
        foreach ($data as $input) {
            $expected = $input[0];
            $pathinfo = $input[1];
            $path = $input[2];

            var_dump($expected === Request::create($pathinfo)->getRelativeUriForPath($path));
        }
    }

    /** @return string[][] */
    public static function getRelativeUriForPathData()
    {
        return [
            ['me.png', '/foo', '/me.png'],
            ['../me.png', '/foo/bar', '/me.png'],
            ['me.png', '/foo/bar', '/foo/me.png'],
            ['../baz/me.png', '/foo/bar/b', '/foo/baz/me.png'],
            ['../../fooz/baz/me.png', '/foo/bar/b', '/fooz/baz/me.png'],
            ['baz/me.png', '/foo/bar/b', 'baz/me.png'],
        ];
    }

    public function testGetUserInfo()
    {
        $request = new Request();

        $server = ['PHP_AUTH_USER' => 'fabien'];
        $request->initialize([], [], [], [], [], $server);
        var_dump('fabien' === $request->getUserInfo());

        $server['PHP_AUTH_USER'] = '0';
        $request->initialize([], [], [], [], [], $server);
        var_dump('0' === $request->getUserInfo());

        $server['PHP_AUTH_PW'] = '0';
        $request->initialize([], [], [], [], [], $server);
        var_dump('0:0' === $request->getUserInfo());
    }

    public function testGetSchemeAndHttpHost()
    {
        $request = new Request();

        $server = [];
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '90';
        $request->initialize([], [], [], [], [], $server);
        var_dump('http://servername:90' === $request->getSchemeAndHttpHost());

        $server['PHP_AUTH_USER'] = 'fabien';
        $request->initialize([], [], [], [], [], $server);
        var_dump('http://servername:90' === $request->getSchemeAndHttpHost());

        $server['PHP_AUTH_USER'] = '0';
        $request->initialize([], [], [], [], [], $server);
        var_dump('http://servername:90' === $request->getSchemeAndHttpHost());

        $server['PHP_AUTH_PW'] = '0';
        $request->initialize([], [], [], [], [], $server);
        var_dump('http://servername:90' === $request->getSchemeAndHttpHost());
    }

    /**
     * @dataProvider getQueryStringNormalizationData
     */
    public function testGetQueryString()
    {
        $data = self::getQueryStringNormalizationData();
        foreach ($data as $input) {
            $query = $input[0];
            $expectedQuery = $input[1];

            $request = new Request();

            $request->server->set('QUERY_STRING', $query);
            var_dump($expectedQuery === $request->getQueryString());
        }
    }

    /** @return string[][] */
    public static function getQueryStringNormalizationData()
    {
        return [
            ['foo', 'foo=', 'works with valueless parameters'],
            ['foo=', 'foo=', 'includes a dangling equal sign'],
            ['bar=&foo=bar', 'bar=&foo=bar', '->works with empty parameters'],
            ['foo=bar&bar=', 'bar=&foo=bar', 'sorts keys alphabetically'],

            // GET parameters, that are submitted from an HTML form, encode spaces as "+" by default (as defined in enctype application/x-www-form-urlencoded).
            // PHP also converts "+" to spaces when filling the global _GET or when using the function parse_str.
            ['baz=Foo%20Baz&bar=Foo+Bar', 'bar=Foo%20Bar&baz=Foo%20Baz', 'normalizes spaces in both encodings "%20" and "+"'],

            ['foo[]=1&foo[]=2', 'foo%5B0%5D=1&foo%5B1%5D=2', 'allows array notation'],
            ['foo=1&foo=2', 'foo=2', 'merges repeated parameters'],
            ['pa%3Dram=foo%26bar%3Dbaz&test=test', 'pa%3Dram=foo%26bar%3Dbaz&test=test', 'works with encoded delimiters'],
            ['0', '0=', 'allows "0"'],
            ['Foo Bar&Foo%20Baz', 'Foo%20Bar=&Foo%20Baz=', 'normalizes encoding in keys'],
            ['bar=Foo Bar&baz=Foo%20Baz', 'bar=Foo%20Bar&baz=Foo%20Baz', 'normalizes encoding in values'],
            ['foo=bar&&&test&&', 'foo=bar&test=', 'removes unneeded delimiters'],
            ['formula=e=m*c^2', 'formula=e%3Dm%2Ac%5E2', 'correctly treats only the first "=" as delimiter and the next as value'],

            // Ignore pairs with empty key, even if there was a value, e.g. "=value", as such nameless values cannot be retrieved anyway.
            // PHP also does not include them when building _GET.
            ['foo=bar&=a=b&=x=y', 'foo=bar', 'removes params with empty key'],

            // Don't reorder nested query string keys
            ['foo[]=Z&foo[]=A', 'foo%5B0%5D=Z&foo%5B1%5D=A', 'keeps order of values'],
            ['foo[Z]=B&foo[A]=B', 'foo%5BZ%5D=B&foo%5BA%5D=B', 'keeps order of keys'],

            ['utf8=✓', 'utf8=%E2%9C%93', 'encodes UTF-8'],
        ];
    }

    public function testGetQueryStringReturnsNull()
    {
        $request = new Request();

        var_dump(null === $request->getQueryString());

        $request->server->set('QUERY_STRING', '');
        var_dump(null === $request->getQueryString());
    }

    public function testGetHost()
    {
        $request = new Request();

        $request->initialize(['foo' => 'bar']);
        var_dump('' === $request->getHost());

        $request->initialize([], [], [], [], [], ['HTTP_HOST' => 'www.example.com']);
        var_dump('www.example.com' === $request->getHost());

        // Host header with port number
        $request->initialize([], [], [], [], [], ['HTTP_HOST' => 'www.example.com:8080']);
        var_dump('www.example.com' === $request->getHost());

        // Server values
        $request->initialize([], [], [], [], [], ['SERVER_NAME' => 'www.example.com']);
        var_dump('www.example.com' === $request->getHost());

        $request->initialize([], [], [], [], [], ['SERVER_NAME' => 'www.example.com', 'HTTP_HOST' => 'www.host.com']);
        var_dump('www.host.com' === $request->getHost());
    }

    public function testGetPort()
    {
        $request = Request::create('http://example.com', 'GET', [], [], [], [
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ]);
        $port = $request->getPort();

        var_dump(80 === $port);

        Request::setTrustedProxies(['1.1.1.1'], Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PORT);
        $request = Request::create('http://example.com', 'GET', [], [], [], [
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '8443',
        ]);
        var_dump(80 === $request->getPort());
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        var_dump(8443 === $request->getPort());

        $request = Request::create('http://example.com', 'GET', [], [], [], [
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);
        var_dump(80 === $request->getPort());
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        var_dump(443 === $request->getPort());

        $request = Request::create('http://example.com', 'GET', [], [], [], [
            'HTTP_X_FORWARDED_PROTO' => 'http',
        ]);
        var_dump(80 === $request->getPort());
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        var_dump(80 === $request->getPort());

        $request = Request::create('http://example.com', 'GET', [], [], [], [
            'HTTP_X_FORWARDED_PROTO' => 'On',
        ]);
        var_dump(80 === $request->getPort());
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        var_dump(443 === $request->getPort());

        $request = Request::create('http://example.com', 'GET', [], [], [], [
            'HTTP_X_FORWARDED_PROTO' => '1',
        ]);
        var_dump(80 === $request->getPort());
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        var_dump(443 === $request->getPort());

        $request = Request::create('http://example.com', 'GET', [], [], [], [
            'HTTP_X_FORWARDED_PROTO' => 'something-else',
        ]);
        $port = $request->getPort();
        var_dump(80 === $port);
    }

    public function testGetHostWithFakeHttpHostValue()
    {
        $request = new Request();
        $request->initialize([], [], [], [], [], ['HTTP_HOST' => 'www.host.com?query=string']);
        try {
            $request->getHost();
        } catch (SuspiciousOperationException $e) {
            var_dump($e->getMessage() === 'Invalid Host "www.host.com?query=string".');
        }
    }

    public function testGetSetMethod()
    {
        $request = new Request();

        var_dump('GET' === $request->getMethod());

        $request->setMethod('get');
        var_dump('GET' === $request->getMethod());

        $request->setMethod('PURGE');
        var_dump('PURGE' === $request->getMethod());

        $request->setMethod('POST');
        var_dump('POST' === $request->getMethod());

        $request->setMethod('POST');
        $request->request->set('_method', 'purge');
        var_dump('POST' === $request->getMethod());

        $request = new Request();
        $request->setMethod('POST');
        $request->request->set('_method', 'purge');

        var_dump(false === Request::getHttpMethodParameterOverride());

        Request::enableHttpMethodParameterOverride();

        var_dump(true === Request::getHttpMethodParameterOverride());

        var_dump('PURGE' === $request->getMethod());
        Request::disableHttpMethodParameterOverride();

        $request = new Request();
        $request->setMethod('POST');
        $request->query->set('_method', 'purge');
        var_dump('POST' === $request->getMethod());

        $request = new Request();
        $request->setMethod('POST');
        $request->query->set('_method', 'purge');
        Request::enableHttpMethodParameterOverride();
        var_dump('PURGE' === $request->getMethod());
        Request::disableHttpMethodParameterOverride();

        $request = new Request();
        $request->setMethod('POST');
        $request->headers->set('X-HTTP-METHOD-OVERRIDE', 'delete');
        var_dump('DELETE' === $request->getMethod());

        $request = new Request();
        $request->setMethod('POST');
        $request->headers->set('X-HTTP-METHOD-OVERRIDE', 'delete');
        var_dump('DELETE' === $request->getMethod());

        $request = new Request();
        $request->setMethod('POST');
        $request->query->set('_method', ['delete', 'patch']);
        var_dump('POST' === $request->getMethod());
    }

    /**
     * @dataProvider getClientIpsProvider
     */
    public function testGetClientIp()
    {
        $data = self::getClientIpsProvider();
        foreach ($data as $input) {
            $expected = array_map('strval', $input[0]);
            $remoteAddr = (string)$input[1];
            if ($input[2] !== null) {
                $httpForwardedFor = (string)$input[2];
            } else {
                $httpForwardedFor = null;
            }
            if ($input[3] !== null) {
                $trustedProxies = array_map('strval', $input[3]);
            } else {
                $trustedProxies = null;
            }

            $request = $this->getRequestInstanceForClientIpTests($remoteAddr, $httpForwardedFor, $trustedProxies);

            var_dump($expected[0] === $request->getClientIp());
        }
    }

    /**
     * @dataProvider getClientIpsProvider
     */
    public function testGetClientIps()
    {
        $data = self::getClientIpsProvider();
        foreach ($data as $input) {
            $expected = array_map('strval', $input[0]);
            $remoteAddr = (string)$input[1];
            if ($input[2] !== null) {
                $httpForwardedFor = (string)$input[2];
            } else {
                $httpForwardedFor = null;
            }
            if ($input[3] !== null) {
                $trustedProxies = array_map('strval', $input[3]);
            } else {
                $trustedProxies = null;
            }

            $request = $this->getRequestInstanceForClientIpTests($remoteAddr, $httpForwardedFor, $trustedProxies);

            var_dump($expected === $request->getClientIps());
        }
    }

    /**
     * @dataProvider getClientIpsForwardedProvider
     */
    public function testGetClientIpsForwarded()
    {
        $data = self::getClientIpsForwardedProvider();
        foreach ($data as $input) {
            $expected = array_map('strval', $input[0]);
            $remoteAddr = (string)$input[1];
            $httpForwarded = (string)$input[2];
            if ($input[3] !== null) {
                $trustedProxies = array_map('strval', $input[3]);
            } else {
                $trustedProxies = null;
            }

            $request = $this->getRequestInstanceForClientIpsForwardedTests($remoteAddr, $httpForwarded, $trustedProxies);

            var_dump($expected === $request->getClientIps());
        }
    }

    /** @return mixed[][] */
    public static function getClientIpsForwardedProvider()
    {
        //              $expected                                  $remoteAddr  $httpForwarded                                       $trustedProxies
        return [
            [['127.0.0.1'],                              '127.0.0.1', 'for="_gazonk"',                                      null],
            [['127.0.0.1'],                              '127.0.0.1', 'for="_gazonk"',                                      ['127.0.0.1']],
            [['88.88.88.88'],                            '127.0.0.1', 'for="88.88.88.88:80"',                               ['127.0.0.1']],
            [['192.0.2.60'],                             '::1',       'for=192.0.2.60;proto=http;by=203.0.113.43',          ['::1']],
            [['2620:0:1cfe:face:b00c::3', '192.0.2.43'], '::1',       'for=192.0.2.43, for="[2620:0:1cfe:face:b00c::3]"',   ['::1']],
            [['2001:db8:cafe::17'],                      '::1',       'for="[2001:db8:cafe::17]:4711',                      ['::1']],
        ];
    }

    /** @return mixed[][] */
    public static function getClientIpsProvider()
    {
        //        $expected                          $remoteAddr                 $httpForwardedFor            $trustedProxies
        return [
            // simple IPv4
            [['88.88.88.88'],              '88.88.88.88',              null,                        null],
            // trust the IPv4 remote addr
            [['88.88.88.88'],              '88.88.88.88',              null,                        ['88.88.88.88']],

            // simple IPv6
            [['::1'],                      '::1',                      null,                        null],
            // trust the IPv6 remote addr
            [['::1'],                      '::1',                      null,                        ['::1']],

            // forwarded for with remote IPv4 addr not trusted
            [['127.0.0.1'],                '127.0.0.1',                '88.88.88.88',               null],
            // forwarded for with remote IPv4 addr trusted + comma
            [['88.88.88.88'],              '127.0.0.1',                '88.88.88.88,',              ['127.0.0.1']],
            // forwarded for with remote IPv4 and all FF addrs trusted
            [['88.88.88.88'],              '127.0.0.1',                '88.88.88.88',               ['127.0.0.1', '88.88.88.88']],
            // forwarded for with remote IPv4 range trusted
            [['88.88.88.88'],              '123.45.67.89',             '88.88.88.88',               ['123.45.67.0/24']],

            // forwarded for with remote IPv6 addr not trusted
            [['1620:0:1cfe:face:b00c::3'], '1620:0:1cfe:face:b00c::3', '2620:0:1cfe:face:b00c::3',  null],
            // forwarded for with remote IPv6 addr trusted
            [['2620:0:1cfe:face:b00c::3'], '1620:0:1cfe:face:b00c::3', '2620:0:1cfe:face:b00c::3',  ['1620:0:1cfe:face:b00c::3']],
            // forwarded for with remote IPv6 range trusted
            [['88.88.88.88'],              '2a01:198:603:0:396e:4789:8e99:890f', '88.88.88.88',     ['2a01:198:603:0::/65']],

            // multiple forwarded for with remote IPv4 addr trusted
            [['88.88.88.88', '87.65.43.21', '127.0.0.1'], '123.45.67.89', '127.0.0.1, 87.65.43.21, 88.88.88.88', ['123.45.67.89']],
            // multiple forwarded for with remote IPv4 addr and some reverse proxies trusted
            [['87.65.43.21', '127.0.0.1'], '123.45.67.89',             '127.0.0.1, 87.65.43.21, 88.88.88.88', ['123.45.67.89', '88.88.88.88']],
            // multiple forwarded for with remote IPv4 addr and some reverse proxies trusted but in the middle
            [['88.88.88.88', '127.0.0.1'], '123.45.67.89',             '127.0.0.1, 87.65.43.21, 88.88.88.88', ['123.45.67.89', '87.65.43.21']],
            // multiple forwarded for with remote IPv4 addr and all reverse proxies trusted
            [['127.0.0.1'],                '123.45.67.89',             '127.0.0.1, 87.65.43.21, 88.88.88.88', ['123.45.67.89', '87.65.43.21', '88.88.88.88', '127.0.0.1']],

            // multiple forwarded for with remote IPv6 addr trusted
            [['2620:0:1cfe:face:b00c::3', '3620:0:1cfe:face:b00c::3'], '1620:0:1cfe:face:b00c::3', '3620:0:1cfe:face:b00c::3,2620:0:1cfe:face:b00c::3', ['1620:0:1cfe:face:b00c::3']],
            // multiple forwarded for with remote IPv6 addr and some reverse proxies trusted
            [['3620:0:1cfe:face:b00c::3'], '1620:0:1cfe:face:b00c::3', '3620:0:1cfe:face:b00c::3,2620:0:1cfe:face:b00c::3', ['1620:0:1cfe:face:b00c::3', '2620:0:1cfe:face:b00c::3']],
            // multiple forwarded for with remote IPv4 addr and some reverse proxies trusted but in the middle
            [['2620:0:1cfe:face:b00c::3', '4620:0:1cfe:face:b00c::3'], '1620:0:1cfe:face:b00c::3', '4620:0:1cfe:face:b00c::3,3620:0:1cfe:face:b00c::3,2620:0:1cfe:face:b00c::3', ['1620:0:1cfe:face:b00c::3', '3620:0:1cfe:face:b00c::3']],

            // client IP with port
            [['88.88.88.88'], '127.0.0.1', '88.88.88.88:12345, 127.0.0.1', ['127.0.0.1']],

            // invalid forwarded IP is ignored
            [['88.88.88.88'], '127.0.0.1', 'unknown,88.88.88.88', ['127.0.0.1']],
            [['88.88.88.88'], '127.0.0.1', '}__test|O:21:&quot;JDatabaseDriverMysqli&quot;:3:{s:2,88.88.88.88', ['127.0.0.1']],
        ];
    }

    // This method throws an Exception

    /**
     * @dataProvider getClientIpsWithConflictingHeadersProvider
     */
    public function testGetClientIpsWithConflictingHeaders()
    {
        $data = self::getClientIpsWithConflictingHeadersProvider();
        foreach ($data as $input) {
            $httpForwarded = $input[0];
            $httpXForwardedFor = $input[1];

            $request = new Request();

            $server = [
                'REMOTE_ADDR' => '88.88.88.88',
                'HTTP_FORWARDED' => $httpForwarded,
                'HTTP_X_FORWARDED_FOR' => $httpXForwardedFor,
            ];

            Request::setTrustedProxies(['88.88.88.88'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_FORWARDED);

            $request->initialize([], [], [], [], [], $server);

            try {
                $request->getClientIps();
            } catch (ConflictingHeadersException $e) {
                var_dump($e->getMessage() === 'The request has both a trusted "FORWARDED" header and a trusted "X_FORWARDED_FOR" header, conflicting with each other. You should either configure your proxy to remove one of them, or configure your project to distrust the offending one.');
            }
        }
    }

    /**
     * @dataProvider getClientIpsWithConflictingHeadersProvider
     */
    public function testGetClientIpsOnlyXHttpForwardedForTrusted()
    {
        $data = self::getClientIpsWithConflictingHeadersProvider();
        foreach ($data as $input) {
            $httpForwarded = $input[0];
            $httpXForwardedFor = $input[1];

            $request = new Request();

            $server = [
                'REMOTE_ADDR' => '88.88.88.88',
                'HTTP_FORWARDED' => $httpForwarded,
                'HTTP_X_FORWARDED_FOR' => $httpXForwardedFor,
            ];

            Request::setTrustedProxies(['88.88.88.88'], Request::HEADER_X_FORWARDED_FOR);

            $request->initialize([], [], [], [], [], $server);

            var_dump(array_reverse(explode(',', $httpXForwardedFor)) === $request->getClientIps());
        }
    }

    /** @return string[][] */
    public static function getClientIpsWithConflictingHeadersProvider()
    {
        //        $httpForwarded                   $httpXForwardedFor
        return [
            ['for=87.65.43.21',                 '192.0.2.60'],
            ['for=87.65.43.21, for=192.0.2.60', '192.0.2.60'],
            ['for=192.0.2.60',                  '192.0.2.60,87.65.43.21'],
            ['for="::face", for=192.0.2.60',    '192.0.2.60,192.0.2.43'],
            ['for=87.65.43.21, for=192.0.2.60', '192.0.2.60,87.65.43.21'],
        ];
    }

    /**
     * @dataProvider getClientIpsWithAgreeingHeadersProvider
     */
    public function testGetClientIpsWithAgreeingHeaders()
    {
        $data = self::getClientIpsWithAgreeingHeadersProvider();
        foreach ($data as $input) {
            $httpForwarded = (string)$input[0];
            $httpXForwardedFor = (string)$input[1];
            $expectedIps = array_map('strval', $input[2]);

            $request = new Request();

            $server = [
                'REMOTE_ADDR' => '88.88.88.88',
                'HTTP_FORWARDED' => $httpForwarded,
                'HTTP_X_FORWARDED_FOR' => $httpXForwardedFor,
            ];

            Request::setTrustedProxies(['88.88.88.88'], -1);

            $request->initialize([], [], [], [], [], $server);

            $clientIps = $request->getClientIps();

            var_dump($expectedIps === $clientIps);
        }
    }

    /** @return mixed[][] */
    public static function getClientIpsWithAgreeingHeadersProvider()
    {
        //        $httpForwarded                               $httpXForwardedFor
        return [
            ['for="192.0.2.60"',                          '192.0.2.60',             ['192.0.2.60']],
            ['for=192.0.2.60, for=87.65.43.21',           '192.0.2.60,87.65.43.21', ['87.65.43.21', '192.0.2.60']],
            ['for="[::face]", for=192.0.2.60',            '::face,192.0.2.60',      ['192.0.2.60', '::face']],
            ['for="192.0.2.60:80"',                       '192.0.2.60',             ['192.0.2.60']],
            ['for=192.0.2.60;proto=http;by=203.0.113.43', '192.0.2.60',             ['192.0.2.60']],
            ['for="[2001:db8:cafe::17]:4711"',            '2001:db8:cafe::17',      ['2001:db8:cafe::17']],
        ];
    }

    public function testGetContentWorksTwiceInDefaultMode()
    {
        $req = new Request();
        var_dump('' === $req->getContent());
        var_dump('' === $req->getContent());
    }

////    public function testGetContentReturnsResource()
////    {
////        $req = new Request();
////        $retval = $req->getContent(true);
////        $this->assertIsResource($retval);
////        var_dump('' === fread($retval, 1));
////        var_dump(true === feof($retval));
////    }
//
////    public function testGetContentReturnsResourceWhenContentSetInConstructor()
////    {
////        $req = new Request([], [], [], [], [], [], 'MyContent');
////        $resource = $req->getContent(true);
////
////        $this->assertIsResource($resource);
////        var_dump('MyContent' === stream_get_contents($resource));
////    }
//
//    public function testContentAsResource()
//    {
//        $resource = fopen('php://memory', 'r+');
//        fwrite($resource, 'My other content');
//        rewind($resource);
//
//        $req = new Request([], [], [], [], [], [], $resource);
//        var_dump('My other content' === stream_get_contents($req->getContent(true)));
//        var_dump('My other content' === $req->getContent());
//    }
//
//    public function getContentCantBeCalledTwiceWithResourcesProvider()
//    {
//        return [
//            'Resource then fetch' => [true, false],
//            'Resource then resource' => [true, true],
//        ];
//    }
//
//    /**
//     * @dataProvider getContentCanBeCalledTwiceWithResourcesProvider
//     */
//    public function testGetContentCanBeCalledTwiceWithResources($first, $second)
//    {
//        $req = new Request();
//        $a = $req->getContent($first);
//        $b = $req->getContent($second);
//
//        if ($first) {
//            $a = stream_get_contents($a);
//        }
//
//        if ($second) {
//            $b = stream_get_contents($b);
//        }
//
//        var_dump($a === $b);
//    }
//
//    public static function getContentCanBeCalledTwiceWithResourcesProvider()
//    {
//        return [
//            'Fetch then fetch' => [false, false],
//            'Fetch then resource' => [false, true],
//            'Resource then fetch' => [true, false],
//            'Resource then resource' => [true, true],
//        ];
//    }

    /** @return string[][] */
    public static function provideOverloadedMethods()
    {
        return [
            ['PUT'],
            ['DELETE'],
            ['PATCH'],
            ['put'],
            ['delete'],
            ['patch'],
        ];
    }

    public function testToArrayEmpty()
    {
        $req = new Request();
        try {
            $req->toArray();
        } catch (JsonException $e) {
            var_dump($e->getMessage() === 'Request body is empty.');
        }
    }


    public function testToArrayNonJson()
    {
        $req = new Request([], [], [], [], [], [], 'foobar');
        try {
            $req->toArray();
        } catch (JsonException $e) {
            var_dump($e->getMessage() === 'JSON content was expected to decode to an array, "NULL" returned.');
        }
    }

    public function testToArray()
    {
        $req = new Request([], [], [], [], [], [], json_encode([]));
        var_dump([] === $req->toArray());
        $req = new Request([], [], [], [], [], [], json_encode(['foo' => 'bar']));
        var_dump(['foo' => 'bar'] === $req->toArray());
    }

    /**
     * @dataProvider provideOverloadedMethods
     */
    public function testCreateFromGlobals()
    {
        $data = self::provideOverloadedMethods();
        foreach ($data as $input) {
            $method = $input[0];

            $normalizedMethod = strtoupper($method);

            $_GET['foo1'] = 'bar1';
            $_POST['foo2'] = 'bar2';
            $_COOKIE['foo3'] = 'bar3';
            $_FILES['foo4'] = 'bar4';
            $_SERVER['foo5'] = 'bar5';

            $request = Request::createFromGlobals();
            var_dump('bar1' === $request->query->get('foo1'));
            var_dump('bar2' === $request->request->get('foo2'));
            var_dump('bar3' === $request->cookies->get('foo3'));
            var_dump('bar4' === $request->files->get('foo4'));
            var_dump('bar5' === $request->server->get('foo5'));
            //        $this->assertInstanceOf(InputBag::class, $request->request);
            //        $this->assertInstanceOf(ParameterBag::class, $request->request);

            unset($_GET['foo1'], $_POST['foo2'], $_COOKIE['foo3'], $_FILES['foo4'], $_SERVER['foo5']);

            $_SERVER['REQUEST_METHOD'] = $method;
            $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
            $request = RequestContentProxy::createFromGlobals();
            var_dump($normalizedMethod === $request->getMethod());
            var_dump('mycontent' === $request->request->get('content'));
            //        $this->assertInstanceOf(InputBag::class, $request->request);
            //        $this->assertInstanceOf(ParameterBag::class, $request->request);

            unset($_SERVER['REQUEST_METHOD'], $_SERVER['CONTENT_TYPE']);

            Request::createFromGlobals();
            Request::enableHttpMethodParameterOverride();
            $_POST['_method'] = $method;
            $_POST['foo6'] = 'bar6';
            $_SERVER['REQUEST_METHOD'] = 'PoSt';
            $request = Request::createFromGlobals();
            var_dump($normalizedMethod === $request->getMethod());
            var_dump('POST' === $request->getRealMethod());
            var_dump('bar6' === $request->request->get('foo6'));

            unset($_POST['_method'], $_POST['foo6'], $_SERVER['REQUEST_METHOD']);
            Request::disableHttpMethodParameterOverride();
        }
    }

    public function testOverrideGlobals()
    {
        $request = new Request();
        $request->initialize(['foo' => 'bar']);

        // as the Request::overrideGlobals really work, it erase $_SERVER, so we must backup it
        $server = $_SERVER;

        $request->overrideGlobals();

        var_dump(['foo' => 'bar'] === $_GET);

        $request->initialize([], ['foo' => 'bar']);
        $request->overrideGlobals();

        var_dump(['foo' => 'bar'] === $_POST);

        var_dump(!isset($_SERVER['HTTP_X_FORWARDED_PROTO']));

        $request->headers->set('X_FORWARDED_PROTO', 'https');

        Request::setTrustedProxies(['1.1.1.1'], Request::HEADER_X_FORWARDED_PROTO);
        var_dump(false === $request->isSecure());
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        var_dump(true === $request->isSecure());

        $request->overrideGlobals();

        var_dump(isset($_SERVER['HTTP_X_FORWARDED_PROTO']));

        $request->headers->set('CONTENT_TYPE', 'multipart/form-data');
        $request->headers->set('CONTENT_LENGTH', 12345);

        $request->overrideGlobals();

        var_dump(isset($_SERVER['CONTENT_TYPE']));
        var_dump(isset($_SERVER['CONTENT_LENGTH']));

        $request->initialize(['foo' => 'bar', 'baz' => 'foo']);
        $request->query->remove('baz');

        $request->overrideGlobals();

        var_dump(['foo' => 'bar'] === $_GET);
        var_dump('foo=bar' === $_SERVER['QUERY_STRING']);
        var_dump('foo=bar' === $request->server->get('QUERY_STRING'));

        // restore initial $_SERVER array
        $_SERVER = $server;
    }

    public function testGetScriptName()
    {
        $request = new Request();
        var_dump('' === $request->getScriptName());

        $server = [];
        $server['SCRIPT_NAME'] = '/index.php';

        $request->initialize([], [], [], [], [], $server);

        var_dump('/index.php' === $request->getScriptName());

        $server = [];
        $server['ORIG_SCRIPT_NAME'] = '/frontend.php';
        $request->initialize([], [], [], [], [], $server);

        var_dump('/frontend.php' === $request->getScriptName());

        $server = [];
        $server['SCRIPT_NAME'] = '/index.php';
        $server['ORIG_SCRIPT_NAME'] = '/frontend.php';
        $request->initialize([], [], [], [], [], $server);

        var_dump('/index.php' === $request->getScriptName());
    }

    public function testGetBasePath()
    {
        $request = new Request();
        var_dump('' === $request->getBasePath());

        $server = [];
        $server['SCRIPT_FILENAME'] = '/some/where/index.php';
        $request->initialize([], [], [], [], [], $server);
        var_dump('' === $request->getBasePath());

        $server = [];
        $server['SCRIPT_FILENAME'] = '/some/where/index.php';
        $server['SCRIPT_NAME'] = '/index.php';
        $request->initialize([], [], [], [], [], $server);

        var_dump('' === $request->getBasePath());

        $server = [];
        $server['SCRIPT_FILENAME'] = '/some/where/index.php';
        $server['PHP_SELF'] = '/index.php';
        $request->initialize([], [], [], [], [], $server);

        var_dump('' === $request->getBasePath());

        $server = [];
        $server['SCRIPT_FILENAME'] = '/some/where/index.php';
        $server['ORIG_SCRIPT_NAME'] = '/index.php';
        $request->initialize([], [], [], [], [], $server);

        var_dump('' === $request->getBasePath());
    }

    public function testGetPathInfo()
    {
        $request = new Request();
        var_dump('/' === $request->getPathInfo());

        $server = [];
        $server['REQUEST_URI'] = '/path/info';
        $request->initialize([], [], [], [], [], $server);

        var_dump('/path/info' === $request->getPathInfo());

        $server = [];
        $server['REQUEST_URI'] = '/path%20test/info';
        $request->initialize([], [], [], [], [], $server);

        var_dump('/path%20test/info' === $request->getPathInfo());

        $server = [];
        $server['REQUEST_URI'] = '?a=b';
        $request->initialize([], [], [], [], [], $server);

        var_dump('/' === $request->getPathInfo());
    }

    public function testGetParameterPrecedence()
    {
        $request = new Request();
        $request->attributes->set('foo', 'attr');
        $request->query->set('foo', 'query');
        $request->request->set('foo', 'body');

        var_dump('attr' === $request->get('foo'));

        $request->attributes->remove('foo');
        var_dump('query' === $request->get('foo'));

        $request->query->remove('foo');
        var_dump('body' === $request->get('foo'));

        $request->request->remove('foo');
        var_dump(null === $request->get('foo'));
    }

    public function testGetPreferredLanguage()
    {
        $request = new Request();
        var_dump(null === $request->getPreferredLanguage());
        var_dump(null === $request->getPreferredLanguage([]));
        var_dump('fr' === $request->getPreferredLanguage(['fr']));
        var_dump('fr' === $request->getPreferredLanguage(['fr', 'en']));
        var_dump('en' === $request->getPreferredLanguage(['en', 'fr']));
        var_dump('fr-ch' === $request->getPreferredLanguage(['fr-ch', 'fr-fr']));

        $request = new Request();
        $request->headers->set('Accept-language', 'zh, en-us; q=0.8, en; q=0.6');
        var_dump('en' === $request->getPreferredLanguage(['en', 'en-us']));

        $request = new Request();
        $request->headers->set('Accept-language', 'zh, en-us; q=0.8, en; q=0.6');
        var_dump('en' === $request->getPreferredLanguage(['fr', 'en']));

        $request = new Request();
        $request->headers->set('Accept-language', 'zh, en-us; q=0.8');
        var_dump('en' === $request->getPreferredLanguage(['fr', 'en']));

        $request = new Request();
        $request->headers->set('Accept-language', 'zh, en-us; q=0.8, fr-fr; q=0.6, fr; q=0.5');
        var_dump('en' === $request->getPreferredLanguage(['fr', 'en']));
    }

    public function testIsXmlHttpRequest()
    {
        $request = new Request();
        var_dump(false === $request->isXmlHttpRequest());

        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        var_dump(true === $request->isXmlHttpRequest());

        $request->headers->remove('X-Requested-With');
        var_dump(false === $request->isXmlHttpRequest());
    }

//    /**
//     * @requires extension intl
//     */
//    public function testIntlLocale()
//    {
//        $request = new Request();
//
//        $request->setDefaultLocale('fr');
//        var_dump('fr' === $request->getLocale());
//        var_dump('fr' === \Locale::getDefault());
//
//        $request->setLocale('en');
//        var_dump('en' === $request->getLocale());
//        var_dump('en' === \Locale::getDefault());
//
//        $request->setDefaultLocale('de');
//        var_dump('en' === $request->getLocale());
//        var_dump('en' === \Locale::getDefault());
//    }
//
    public function testGetCharsets()
    {
        $request = new Request();
        var_dump([] === $request->getCharsets());
        $request->headers->set('Accept-Charset', 'ISO-8859-1, US-ASCII, UTF-8; q=0.8, ISO-10646-UCS-2; q=0.6');
        var_dump([] === $request->getCharsets()); // testing caching

        $request = new Request();
        $request->headers->set('Accept-Charset', 'ISO-8859-1, US-ASCII, UTF-8; q=0.8, ISO-10646-UCS-2; q=0.6');
        var_dump(['ISO-8859-1', 'US-ASCII', 'UTF-8', 'ISO-10646-UCS-2'] === $request->getCharsets());

        $request = new Request();
        $request->headers->set('Accept-Charset', 'ISO-8859-1,utf-8;q=0.7,*;q=0.7');
        var_dump(['ISO-8859-1', 'utf-8', '*'] === $request->getCharsets());
    }

    public function testGetEncodings()
    {
        $request = new Request();
        var_dump([] === $request->getEncodings());
        $request->headers->set('Accept-Encoding', 'gzip,deflate,sdch');
        var_dump([] === $request->getEncodings()); // testing caching

        $request = new Request();
        $request->headers->set('Accept-Encoding', 'gzip,deflate,sdch');
        var_dump(['gzip', 'deflate', 'sdch'] === $request->getEncodings());

        $request = new Request();
        $request->headers->set('Accept-Encoding', 'gzip;q=0.4,deflate;q=0.9,compress;q=0.7');
        var_dump(['deflate', 'compress', 'gzip'] === $request->getEncodings());
    }

    public function testGetAcceptableContentTypes()
    {
        $request = new Request();
        var_dump([] === $request->getAcceptableContentTypes());
        $request->headers->set('Accept', 'application/vnd.wap.wmlscriptc, text/vnd.wap.wml, application/vnd.wap.xhtml+xml, application/xhtml+xml, text/html, multipart/mixed, */*');
        var_dump([] === $request->getAcceptableContentTypes()); // testing caching

        $request = new Request();
        $request->headers->set('Accept', 'application/vnd.wap.wmlscriptc, text/vnd.wap.wml, application/vnd.wap.xhtml+xml, application/xhtml+xml, text/html, multipart/mixed, */*');
        var_dump(['application/vnd.wap.wmlscriptc', 'text/vnd.wap.wml', 'application/vnd.wap.xhtml+xml', 'application/xhtml+xml', 'text/html', 'multipart/mixed', '*/*'] === $request->getAcceptableContentTypes());
    }

    public function testGetLanguages()
    {
        $request = new Request();
        var_dump([] === $request->getLanguages());

        $request = new Request();
        $request->headers->set('Accept-language', 'zh, en-us; q=0.8, en; q=0.6');
        var_dump(['zh', 'en_US', 'en'] === $request->getLanguages());

        $request = new Request();
        $request->headers->set('Accept-language', 'zh, en-us; q=0.6, en; q=0.8');
        var_dump(['zh', 'en', 'en_US'] === $request->getLanguages()); // Test out of order qvalues

        $request = new Request();
        $request->headers->set('Accept-language', 'zh, en, en-us');
        var_dump(['zh', 'en', 'en_US'] === $request->getLanguages()); // Test equal weighting without qvalues

        $request = new Request();
        $request->headers->set('Accept-language', 'zh; q=0.6, en, en-us; q=0.6');
        var_dump(['en', 'zh', 'en_US'] === $request->getLanguages()); // Test equal weighting with qvalues

        $request = new Request();
        $request->headers->set('Accept-language', 'zh, i-cherokee; q=0.6');
        var_dump(['zh', 'cherokee'] === $request->getLanguages());
    }

    public function testGetAcceptHeadersReturnString()
    {
        $request = new Request();
        $request->headers->set('Accept', '123');
        $request->headers->set('Accept-Charset', '123');
        $request->headers->set('Accept-Encoding', '123');
        $request->headers->set('Accept-Language', '123');

        var_dump(['123'] === $request->getAcceptableContentTypes());
        var_dump(['123'] === $request->getCharsets());
        var_dump(['123'] === $request->getEncodings());
        var_dump(['123'] === $request->getLanguages());
    }

    public function testGetRequestFormat()
    {
        $request = new Request();
        var_dump('html' === $request->getRequestFormat());

        // Ensure that setting different default values over time is possible,
        // aka. setRequestFormat determines the state.
        var_dump('json' === $request->getRequestFormat('json'));
        var_dump('html' === $request->getRequestFormat('html'));

        $request = new Request();
        var_dump(null === $request->getRequestFormat(null));

        $request = new Request();
        $request->setRequestFormat('foo');
        var_dump('foo' === $request->getRequestFormat(null));

        $request = new Request(['_format' => 'foo']);
        var_dump('html' === $request->getRequestFormat());
    }

//    public function testHasSession()
//    {
//        $request = new Request();
//
//        var_dump(false === $request->hasSession());
//        var_dump(false === $request->hasSession(true));
//
//        $request->setSessionFactory(function () {});
//        var_dump(true === $request->hasSession());
//        var_dump(false === $request->hasSession(true));
//
//        $request->setSession(new Session(new MockArraySessionStorage()));
//        var_dump(true === $request->hasSession());
//        var_dump(true === $request->hasSession(true));
//    }
//
////    public function testGetSession()
////    {
////        $request = new Request();
////
////        $request->setSession(new Session(new MockArraySessionStorage()));
////        var_dump(true === $request->hasSession());
////
////        $this->assertInstanceOf(Session::class, $request->getSession());
////    }
//
//    public function testHasPreviousSession()
//    {
//        $request = new Request();
//
//        var_dump(false === $request->hasPreviousSession());
//        $request->cookies->set('MOCKSESSID', 'foo');
//        var_dump(false === $request->hasPreviousSession());
//        $request->setSession(new Session(new MockArraySessionStorage()));
//        var_dump(true === $request->hasPreviousSession());
//    }
//
    public function testToString()
    {
        $request = new Request();

        $request->headers->set('Accept-language', 'zh, en-us; q=0.8, en; q=0.6');
        $request->cookies->set('Foo', 'Bar');

        $asString = (string) $request;

        var_dump(strpos($asString, 'Accept-Language: zh, en-us; q=0.8, en; q=0.6') !== false);
        var_dump(strpos($asString, 'Cookie: Foo=Bar') !== false);

        $request->cookies->set('Another', 'Cookie');

        $asString = (string) $request;

        var_dump(strpos($asString, 'Cookie: Foo=Bar; Another=Cookie') !== false);

        $request->cookies->set('foo.bar', [1, 2]);

        $asString = (string) $request;

        var_dump(strpos($asString, 'foo.bar%5B0%5D=1; foo.bar%5B1%5D=2') !== false);
    }

    public function testIsMethod()
    {
        $request = new Request();
        $request->setMethod('POST');
        var_dump(true === $request->isMethod('POST'));
        var_dump(true === $request->isMethod('post'));
        var_dump(false === $request->isMethod('GET'));
        var_dump(false === $request->isMethod('get'));

        $request->setMethod('GET');
        var_dump(true === $request->isMethod('GET'));
        var_dump(true === $request->isMethod('get'));
        var_dump(false === $request->isMethod('POST'));
        var_dump(false === $request->isMethod('post'));
    }

    /**
     * @dataProvider getBaseUrlData
     */
    public function testGetBaseUrl()
    {
        $data = self::getBaseUrlData();
        foreach ($data as $input) {
            $uri = (string)$input[0];
            $server = array_map('strval', $input[1]);
            $expectedBaseUrl = (string)$input[2];
            $expectedPathInfo = (string)$input[3];

            $request = Request::create($uri, 'GET', [], [], [], $server);

            var_dump($expectedBaseUrl === $request->getBaseUrl());
            var_dump($expectedPathInfo === $request->getPathInfo());
        }
    }

    /** @return mixed[][] */
    public static function getBaseUrlData()
    {
        return [
            [
                '/fruit/strawberry/1234index.php/blah',
                [
                    'SCRIPT_FILENAME' => 'E:/Sites/cc-new/public_html/fruit/index.php',
                    'SCRIPT_NAME' => '/fruit/index.php',
                    'PHP_SELF' => '/fruit/index.php',
                ],
                '/fruit',
                '/strawberry/1234index.php/blah',
            ],
            [
                '/fruit/strawberry/1234index.php/blah',
                [
                    'SCRIPT_FILENAME' => 'E:/Sites/cc-new/public_html/index.php',
                    'SCRIPT_NAME' => '/index.php',
                    'PHP_SELF' => '/index.php',
                ],
                '',
                '/fruit/strawberry/1234index.php/blah',
            ],
            [
                '/foo%20bar/',
                [
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo bar/app.php',
                    'SCRIPT_NAME' => '/foo bar/app.php',
                    'PHP_SELF' => '/foo bar/app.php',
                ],
                '/foo%20bar',
                '/',
            ],
            [
                '/foo%20bar/home',
                [
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo bar/app.php',
                    'SCRIPT_NAME' => '/foo bar/app.php',
                    'PHP_SELF' => '/foo bar/app.php',
                ],
                '/foo%20bar',
                '/home',
            ],
            [
                '/foo%20bar/app.php/home',
                [
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo bar/app.php',
                    'SCRIPT_NAME' => '/foo bar/app.php',
                    'PHP_SELF' => '/foo bar/app.php',
                ],
                '/foo%20bar/app.php',
                '/home',
            ],
            [
                '/foo%20bar/app.php/home%3Dbaz',
                [
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo bar/app.php',
                    'SCRIPT_NAME' => '/foo bar/app.php',
                    'PHP_SELF' => '/foo bar/app.php',
                ],
                '/foo%20bar/app.php',
                '/home%3Dbaz',
            ],
            [
                '/foo/bar+baz',
                [
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo/app.php',
                    'SCRIPT_NAME' => '/foo/app.php',
                    'PHP_SELF' => '/foo/app.php',
                ],
                '/foo',
                '/bar+baz',
            ],
            [
                '/sub/foo/bar',
                [
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo/app.php',
                    'SCRIPT_NAME' => '/foo/app.php',
                    'PHP_SELF' => '/foo/app.php',
                ],
                '',
                '/sub/foo/bar',
            ],
            [
                '/sub/foo/app.php/bar',
                [
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo/app.php',
                    'SCRIPT_NAME' => '/foo/app.php',
                    'PHP_SELF' => '/foo/app.php',
                ],
                '/sub/foo/app.php',
                '/bar',
            ],
            [
                '/sub/foo/bar/baz',
                [
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo/app2.phpx',
                    'SCRIPT_NAME' => '/foo/app2.phpx',
                    'PHP_SELF' => '/foo/app2.phpx',
                ],
                '',
                '/sub/foo/bar/baz',
            ],
            [
                '/foo/api/bar',
                [
                    'SCRIPT_FILENAME' => '/var/www/api/index.php',
                    'SCRIPT_NAME' => '/api/index.php',
                    'PHP_SELF' => '/api/index.php',
                ],
                '',
                '/foo/api/bar',
            ],
        ];
    }

    // method getUrlencodedPrefix() is private. symfony test uses \ReflectionMethod
//    /**
//     * @dataProvider urlencodedStringPrefixData
//     */
//    public function testUrlencodedStringPrefix()
//    {
//        $data = self::urlencodedStringPrefixData();
//        foreach ($data as $input){
//            $string = $input[0];
//            $prefix = $input[1];
//            $expect = $input[2];
//
//            $request = new Request();
//
//            var_dump($expect === $request->getUrlencodedPrefix($string, $prefix));
//        }
//    }

//    /** @return string[][] */
//    public static function urlencodedStringPrefixData()
//    {
//        return [
//            ['foo', 'foo', 'foo'],
//            ['fo%6f', 'foo', 'fo%6f'],
//            ['foo/bar', 'foo', 'foo'],
//            ['fo%6f/bar', 'foo', 'fo%6f'],
//            ['f%6f%6f/bar', 'foo', 'f%6f%6f'],
//            ['%66%6F%6F/bar', 'foo', '%66%6F%6F'],
//            ['fo+o/bar', 'fo+o', 'fo+o'],
//            ['fo%2Bo/bar', 'fo+o', 'fo%2Bo'],
//        ];
//    }

    private function getRequestInstanceForClientIpTests(string $remoteAddr, ?string $httpForwardedFor, ?array $trustedProxies): Request
    {
        $request = new Request();

        $server = ['REMOTE_ADDR' => $remoteAddr];
        if (null !== $httpForwardedFor) {
            $server['HTTP_X_FORWARDED_FOR'] = $httpForwardedFor;
        }

        if ($trustedProxies) {
            Request::setTrustedProxies($trustedProxies, Request::HEADER_X_FORWARDED_FOR);
        }

        $request->initialize([], [], [], [], [], $server);

        return $request;
    }

    private function getRequestInstanceForClientIpsForwardedTests(string $remoteAddr, ?string $httpForwarded, ?array $trustedProxies): Request
    {
        $request = new Request();

        $server = ['REMOTE_ADDR' => $remoteAddr];

        if (null !== $httpForwarded) {
            $server['HTTP_FORWARDED'] = $httpForwarded;
        }

        if ($trustedProxies) {
            Request::setTrustedProxies($trustedProxies, Request::HEADER_FORWARDED);
        }

        $request->initialize([], [], [], [], [], $server);

        return $request;
    }

    public function testTrustedProxiesXForwardedFor()
    {
        $request = Request::create('http://example.com/');
        $request->server->set('REMOTE_ADDR', '3.3.3.3');
        $request->headers->set('X_FORWARDED_FOR', '1.1.1.1, 2.2.2.2');
        $request->headers->set('X_FORWARDED_HOST', 'foo.example.com:1234, real.example.com:8080');
        $request->headers->set('X_FORWARDED_PROTO', 'https');
        $request->headers->set('X_FORWARDED_PORT', 443);

        // no trusted proxies
        var_dump('3.3.3.3' === $request->getClientIp());
        var_dump('example.com' === $request->getHost());
        var_dump(80 === $request->getPort());
        var_dump(false === $request->isSecure());

        // disabling proxy trusting
        Request::setTrustedProxies([], Request::HEADER_X_FORWARDED_FOR);
        var_dump('3.3.3.3' === $request->getClientIp());
        var_dump('example.com' === $request->getHost());
        var_dump(80 === $request->getPort());
        var_dump(false === $request->isSecure());

        // request is forwarded by a non-trusted proxy
        Request::setTrustedProxies(['2.2.2.2'], Request::HEADER_X_FORWARDED_FOR);
        var_dump('3.3.3.3' === $request->getClientIp());
        var_dump('example.com' === $request->getHost());
        var_dump(80 === $request->getPort());
        var_dump(false === $request->isSecure());

        // trusted proxy via setTrustedProxies()
        Request::setTrustedProxies(['3.3.3.3', '2.2.2.2'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
        var_dump('1.1.1.1' === $request->getClientIp());
        var_dump('foo.example.com' === $request->getHost());
        var_dump(443 === $request->getPort());
        var_dump(true === $request->isSecure());

        // trusted proxy via setTrustedProxies()
        Request::setTrustedProxies(['3.3.3.4', '2.2.2.2'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
        var_dump('3.3.3.3' === $request->getClientIp());
        var_dump('example.com' === $request->getHost());
        var_dump(80 === $request->getPort());
        var_dump(false === $request->isSecure());

        // check various X_FORWARDED_PROTO header values
        Request::setTrustedProxies(['3.3.3.3', '2.2.2.2'], Request::HEADER_X_FORWARDED_PROTO);
        $request->headers->set('X_FORWARDED_PROTO', 'ssl');
        var_dump(true === $request->isSecure());

        $request->headers->set('X_FORWARDED_PROTO', 'https, http');
        var_dump(true === $request->isSecure());
    }

    public function testTrustedProxiesForwarded()
    {
        $request = Request::create('http://example.com/');
        $request->server->set('REMOTE_ADDR', '3.3.3.3');
        $request->headers->set('FORWARDED', 'for=1.1.1.1, host=foo.example.com:8080, proto=https, for=2.2.2.2, host=real.example.com:8080');

        // no trusted proxies
        var_dump('3.3.3.3' === $request->getClientIp());
        var_dump('example.com' === $request->getHost());
        var_dump(80 === $request->getPort());
        var_dump(false === $request->isSecure());

        // disabling proxy trusting
        Request::setTrustedProxies([], Request::HEADER_FORWARDED);
        var_dump('3.3.3.3' === $request->getClientIp());
        var_dump('example.com' === $request->getHost());
        var_dump(80 === $request->getPort());
        var_dump(false === $request->isSecure());

        // request is forwarded by a non-trusted proxy
        Request::setTrustedProxies(['2.2.2.2'], Request::HEADER_FORWARDED);
        var_dump('3.3.3.3' === $request->getClientIp());
        var_dump('example.com' === $request->getHost());
        var_dump(80 === $request->getPort());
        var_dump(false === $request->isSecure());

        // trusted proxy via setTrustedProxies()
        Request::setTrustedProxies(['3.3.3.3', '2.2.2.2'], Request::HEADER_FORWARDED);
        var_dump('1.1.1.1' === $request->getClientIp());
        var_dump('foo.example.com' === $request->getHost());
        var_dump(8080 === $request->getPort());
        var_dump(true === $request->isSecure());

        // trusted proxy via setTrustedProxies()
        Request::setTrustedProxies(['3.3.3.4', '2.2.2.2'], Request::HEADER_FORWARDED);
        var_dump('3.3.3.3' === $request->getClientIp());
        var_dump('example.com' === $request->getHost());
        var_dump(80 === $request->getPort());
        var_dump(false === $request->isSecure());

        // check various X_FORWARDED_PROTO header values
        Request::setTrustedProxies(['3.3.3.3', '2.2.2.2'], Request::HEADER_FORWARDED);
        $request->headers->set('FORWARDED', 'proto=ssl');
        var_dump(true === $request->isSecure());

        $request->headers->set('FORWARDED', 'proto=https, proto=http');
        var_dump(true === $request->isSecure());
    }

    /**
     * @dataProvider iisRequestUriProvider
     */
    public function testIISRequestUri()
    {
        $data = self::iisRequestUriProvider();
        foreach ($data as $input) {
            $headers = $input[0];
            $server = $input[1];
            $expectedRequestUri = $input[2][0];

            $request = new Request();
            $request->headers->replace($headers);
            $request->server->replace($server);

            var_dump($expectedRequestUri === $request->getRequestUri());

            $subRequestUri = '/bar/foo';
            $requestServerAll = $request->server->all();
            if (!\is_array($requestServerAll)) {
                /** @var string[] $requestServerAllArray */
                $requestServerAllArray = [(string)$requestServerAll];
            } else {
                /** @var string[] $requestServerAllArray */
                $requestServerAllArray = array_map('strval', $requestServerAll);
            }
            $subRequest = Request::create($subRequestUri, 'get', [], [], [], $requestServerAllArray);
            var_dump($subRequestUri === $subRequest->getRequestUri());
        }
    }

    /** @return string[][][] */
    public static function iisRequestUriProvider()
    {
        return [
            [
                [],
                [
                    'IIS_WasUrlRewritten' => '1',
                    'UNENCODED_URL' => '/foo/bar',
                ],
                ['/foo/bar'],
            ],
            [
                [],
                [
                    'ORIG_PATH_INFO' => '/foo/bar',
                ],
                ['/foo/bar'],
            ],
            [
                [],
                [
                    'ORIG_PATH_INFO' => '/foo/bar',
                    'QUERY_STRING' => 'foo=bar',
                ],
                ['/foo/bar?foo=bar'],
            ],
        ];
    }

    public function testTrustedHosts()
    {
        // create a request
        $request = Request::create('/');

        // no trusted host set -> no host check
        $request->headers->set('host', 'evil.com');
        var_dump('evil.com' === $request->getHost());

        // add a trusted domain and all its subdomains
        Request::setTrustedHosts(['^([a-z]{9}\.)?trusted\.com$']);

        // untrusted host
        $request->headers->set('host', 'evil.com');
        try {
            $request->getHost();
        } catch (SuspiciousOperationException $e) {
            var_dump('Untrusted Host "evil.com".' === $e->getMessage());
        }

        // trusted hosts
        $request->headers->set('host', 'trusted.com');
        var_dump('trusted.com' === $request->getHost());
        var_dump(80 === $request->getPort());

        $request->server->set('HTTPS', true);
        $request->headers->set('host', 'trusted.com');
        var_dump('trusted.com' === $request->getHost());
        var_dump(443 === $request->getPort());
        $request->server->set('HTTPS', false);

        $request->headers->set('host', 'trusted.com:8000');
        var_dump('trusted.com' === $request->getHost());
        var_dump(8000 === $request->getPort());

        $request->headers->set('host', 'subdomain.trusted.com');
        var_dump('subdomain.trusted.com' === $request->getHost());
    }

    public function testSetTrustedHostsDoesNotBreakOnSpecialCharacters()
    {
        Request::setTrustedHosts(['localhost(\.local){0,1}#,example.com', 'localhost']);

        $request = Request::create('/');
        $request->headers->set('host', 'localhost');
        var_dump('localhost' === $request->getHost());
    }

//    public function testFactory()
//    {
//        Request::setFactory(function (array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null) {
//            return new NewRequest();
//        });
//
//        var_dump('foo' === Request::create('/')->getFoo());
//
//        Request::setFactory(null);
//    }
//

    /**
     * @dataProvider getLongHostNames
     */
    public function testVeryLongHosts()
    {
        Request::setTrustedHosts([]);

        $data = self::getLongHostNames();
        foreach ($data as $input) {
            $host = $input[0];

            $start = microtime(true);

            $request = Request::create('/');
            $request->headers->set('host', $host);
            var_dump($host === $request->getHost());
            var_dump(5 > (microtime(true) - $start));
        }
    }

    /**
     * @dataProvider getHostValidities
     */
    public function testHostValidity()
    {
        Request::setTrustedHosts([]);

        $data = self::getHostValidities();
        foreach ($data as $input) {
            $host = $input[0];
            $isValid = $input[1];
            $expectedHost = $input[2];
            $expectedPort = $input[3];

            $request = Request::create('/');
            $request->headers->set('host', $host);

            if ($isValid) {
                if ($expectedHost) {
                    var_dump($expectedHost === $request->getHost());
                } else {
                    var_dump($host === $request->getHost());
                }

                if ($expectedPort) {
                    var_dump($expectedPort === $request->getPort());
                }
            } else {
                try {
                    $request->getHost();
                } catch (SuspiciousOperationException $e) {
                    var_dump(strpos($e->getMessage(), 'Invalid Host') !== false);
                }
            }
        }
    }

    /** @return mixed[][] */
    public static function getHostValidities()
    {
        return [
            ['.a', false, null, null],
            ['a..', false, null, null],
            ['a.', true, null, null],
            ["\xE9", false, null, null],
            ['[::1]', true, null, null],
            ['[::1]:80', true, '[::1]', 80],
            [str_repeat('.', 101), false, null, null],
        ];
    }

    /** @return string[][] */
    public static function getLongHostNames()
    {
        return [
            ['a' . str_repeat('.a', 40000)],
            [str_repeat(':', 101)],
        ];
    }

    /**
     * @dataProvider methodIdempotentProvider
     */
    public function testMethodIdempotent()
    {
        $data = self::methodIdempotentProvider();
        foreach ($data as $input) {
            $method = (string)$input[0];
            $idempotent = (bool)$input[1];

            $request = new Request();
            $request->setMethod($method);
            var_dump($idempotent === $request->isMethodIdempotent());
        }
    }

    /** @return mixed[][] */
    public static function methodIdempotentProvider()
    {
        return [
            ['HEAD', true],
            ['GET', true],
            ['POST', false],
            ['PUT', true],
            ['PATCH', false],
            ['DELETE', true],
            ['PURGE', true],
            ['OPTIONS', true],
            ['TRACE', true],
            ['CONNECT', false],
        ];
    }

    /**
     * @dataProvider methodSafeProvider
     */
    public function testMethodSafe()
    {
        $data = self::methodSafeProvider();
        foreach ($data as $input) {
            $method = (string)$input[0];
            $safe = (bool)$input[1];

            $request = new Request();
            $request->setMethod($method);
            var_dump($safe === $request->isMethodSafe());
        }
    }

    /** @return mixed[][] */
    public static function methodSafeProvider()
    {
        return [
            ['HEAD', true],
            ['GET', true],
            ['POST', false],
            ['PUT', false],
            ['PATCH', false],
            ['DELETE', false],
            ['PURGE', false],
            ['OPTIONS', true],
            ['TRACE', true],
            ['CONNECT', false],
        ];
    }

    /**
     * @dataProvider methodCacheableProvider
     */
    public function testMethodCacheable()
    {
        $data = self::methodCacheableProvider();
        foreach ($data as $input) {
            $method = (string)$input[0];
            $cacheable = (bool)$input[1];

            $request = new Request();
            $request->setMethod($method);
            var_dump($cacheable === $request->isMethodCacheable());
        }
    }

    /** @return mixed[][] */
    public static function methodCacheableProvider()
    {
        return [
            ['HEAD', true],
            ['GET', true],
            ['POST', false],
            ['PUT', false],
            ['PATCH', false],
            ['DELETE', false],
            ['PURGE', false],
            ['OPTIONS', false],
            ['TRACE', false],
            ['CONNECT', false],
        ];
    }

    /**
     * @dataProvider protocolVersionProvider
     */
    public function testProtocolVersion()
    {
        $data = self::protocolVersionProvider();
        foreach ($data as $input) {
            $serverProtocol = (string)$input[0];
            $trustedProxy = (bool)$input[1];
            if ($input[2] === null) {
                $via = null;
            } else {
                $via = (string)$input[2];
            }
            $expected = (string)$input[3];

            if ($trustedProxy) {
                Request::setTrustedProxies(['1.1.1.1'], -1);
            } else {
                Request::setTrustedProxies(null, -1);
            }

            $request = new Request();
            $request->server->set('SERVER_PROTOCOL', $serverProtocol);
            $request->server->set('REMOTE_ADDR', '1.1.1.1');

            if (null !== $via) {
                $request->headers->set('Via', $via);
            }

            var_dump($expected === $request->getProtocolVersion());
        }
    }

    /** @return mixed[][] */
    public static function protocolVersionProvider()
    {
        return [
            'untrusted with empty via' => ['HTTP/2.0', false, '', 'HTTP/2.0'],
            'untrusted without via' => ['HTTP/2.0', false, null, 'HTTP/2.0'],
            'untrusted with via' => ['HTTP/2.0', false, '1.0 fred, 1.1 nowhere.com (Apache/1.1)', 'HTTP/2.0'],
            'trusted with empty via' => ['HTTP/2.0', true, '', 'HTTP/2.0'],
            'trusted without via' => ['HTTP/2.0', true, null, 'HTTP/2.0'],
            'trusted with via' => ['HTTP/2.0', true, '1.0 fred, 1.1 nowhere.com (Apache/1.1)', 'HTTP/1.0'],
            'trusted with via and protocol name' => ['HTTP/2.0', true, 'HTTP/1.0 fred, HTTP/1.1 nowhere.com (Apache/1.1)', 'HTTP/1.0'],
            'trusted with broken via' => ['HTTP/2.0', true, 'HTTP/1^0 foo', 'HTTP/2.0'],
            'trusted with partially-broken via' => ['HTTP/2.0', true, '1.0 fred, foo', 'HTTP/1.0'],
        ];
    }

    /** @return string[][] */
    public static function nonstandardRequestsData()
    {
        return [
            ['',  '', '/', 'http://host:8080/', ''],
            ['/', '', '/', 'http://host:8080/', ''],

            ['hello/app.php/x',  '', '/x', 'http://host:8080/hello/app.php/x', '/hello', '/hello/app.php'],
            ['/hello/app.php/x', '', '/x', 'http://host:8080/hello/app.php/x', '/hello', '/hello/app.php'],

            ['',      'a=b', '/', 'http://host:8080/?a=b'],
            ['?a=b',  'a=b', '/', 'http://host:8080/?a=b'],
            ['/?a=b', 'a=b', '/', 'http://host:8080/?a=b'],

            ['x',      'a=b', '/x', 'http://host:8080/x?a=b'],
            ['x?a=b',  'a=b', '/x', 'http://host:8080/x?a=b'],
            ['/x?a=b', 'a=b', '/x', 'http://host:8080/x?a=b'],

            ['hello/x',  '', '/x', 'http://host:8080/hello/x', '/hello'],
            ['/hello/x', '', '/x', 'http://host:8080/hello/x', '/hello'],

            ['hello/app.php/x',      'a=b', '/x', 'http://host:8080/hello/app.php/x?a=b', '/hello', '/hello/app.php'],
            ['hello/app.php/x?a=b',  'a=b', '/x', 'http://host:8080/hello/app.php/x?a=b', '/hello', '/hello/app.php'],
            ['/hello/app.php/x?a=b', 'a=b', '/x', 'http://host:8080/hello/app.php/x?a=b', '/hello', '/hello/app.php'],
        ];
    }

    /**
     * @dataProvider nonstandardRequestsData
     */
    public function testNonstandardRequests()
    {
        $data = self::nonstandardRequestsData();
        foreach ($data as $input) {
            $requestUri = $input[0];
            $queryString = $input[1];
            $expectedPathInfo = $input[2];
            $expectedUri = $input[3];
            if (count($input) > 4) {
                $expectedBasePath = $input[4];
            } else {
                $expectedBasePath = '';
            }

            if (count($input) > 5) {
                $expectedBaseUrl = $input[5];
            } else {
                $expectedBaseUrl = $expectedBasePath;
            }

            $server = [
                'HTTP_HOST' => 'host:8080',
                'SERVER_PORT' => '8080',
                'QUERY_STRING' => $queryString,
                'PHP_SELF' => '/hello/app.php',
                'SCRIPT_FILENAME' => '/some/path/app.php',
                'REQUEST_URI' => $requestUri,
            ];

            $request = new Request([], [], [], [], [], $server);

            var_dump($expectedPathInfo === $request->getPathInfo());
            var_dump($expectedUri === $request->getUri());
            if (strlen($queryString) === 0) {
                var_dump(null === $request->getQueryString());
            } else {
                var_dump($queryString === $request->getQueryString());
            }
            var_dump(8080 === $request->getPort());
            var_dump('host:8080' === $request->getHttpHost());
            var_dump($expectedBaseUrl === $request->getBaseUrl());
            var_dump($expectedBasePath === $request->getBasePath());
        }
    }

    public function testTrustedHost()
    {
        Request::setTrustedProxies(['1.1.1.1'], -1);

        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        $request->headers->set('Forwarded', 'host=localhost:8080');
        $request->headers->set('X-Forwarded-Host', 'localhost:8080');

        var_dump('localhost:8080' === $request->getHttpHost());
        var_dump(8080 === $request->getPort());

        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        $request->headers->set('Forwarded', 'host="[::1]:443"');
        $request->headers->set('X-Forwarded-Host', '[::1]:443');
        $request->headers->set('X-Forwarded-Port', 443);

        var_dump('[::1]:443' === $request->getHttpHost());
        var_dump(443 === $request->getPort());
    }

    public function testTrustedPrefix()
    {
        Request::setTrustedProxies(['1.1.1.1'], Request::HEADER_X_FORWARDED_TRAEFIK);

        // test with index deployed under root
        $request = Request::create('/method');
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        $request->headers->set('X-Forwarded-Prefix', '/myprefix');
        $request->headers->set('Forwarded', 'host=localhost:8080');

        var_dump('/myprefix' === $request->getBaseUrl());
        var_dump('/myprefix' === $request->getBasePath());
        var_dump('/method' === $request->getPathInfo());
    }

    public function testTrustedPrefixWithSubdir()
    {
        Request::setTrustedProxies(['1.1.1.1'], Request::HEADER_X_FORWARDED_TRAEFIK);

        $server = [
            'SCRIPT_FILENAME' => '/var/hidden/app/public/public/index.php',
            'SCRIPT_NAME' => '/public/index.php',
            'PHP_SELF' => '/public/index.php',
        ];

        // test with index file deployed in subdir, i.e. local dev server (insecure!!)
        $request = Request::create('/public/method', 'GET', [], [], [], $server);
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        $request->headers->set('X-Forwarded-Prefix', '/prefix');
        $request->headers->set('Forwarded', 'host=localhost:8080');

        var_dump('/prefix/public' === $request->getBaseUrl());
        var_dump('/prefix/public' === $request->getBasePath());
        var_dump('/method' === $request->getPathInfo());
    }

    public function testTrustedPrefixEmpty()
    {
        // check that there is no error, if no prefix is provided
        Request::setTrustedProxies(['1.1.1.1'], Request::HEADER_X_FORWARDED_TRAEFIK);
        $request = Request::create('/method');
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        var_dump('' === $request->getBaseUrl());
    }

    public function testTrustedPort()
    {
        Request::setTrustedProxies(['1.1.1.1'], -1);

        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        $request->headers->set('Forwarded', 'host=localhost:8080');
        $request->headers->set('X-Forwarded-Port', 8080);

        var_dump(8080 === $request->getPort());

        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        $request->headers->set('Forwarded', 'host=localhost');
        $request->headers->set('X-Forwarded-Port', 80);

        var_dump(80 === $request->getPort());

        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        $request->headers->set('Forwarded', 'host="[::1]"');
        $request->headers->set('X-Forwarded-Proto', 'https');
        $request->headers->set('X-Forwarded-Port', 443);

        var_dump(443 === $request->getPort());
    }

    public function testTrustedPortDoesNotDefaultToZero()
    {
        Request::setTrustedProxies(['1.1.1.1'], Request::HEADER_X_FORWARDED_FOR);

        $request = Request::create('/');
        $request->server->set('REMOTE_ADDR', '1.1.1.1');
        $request->headers->set('X-Forwarded-Host', 'test.example.com');
        $request->headers->set('X-Forwarded-Port', '');

        var_dump(80 === $request->getPort());
    }

    /**
     * @dataProvider trustedProxiesRemoteAddr
     */
    public function testTrustedProxiesRemoteAddr()
    {
        $data = self::trustedProxiesRemoteAddr();
        foreach ($data as $input) {
            if ($input[0] !== null) {
                $serverRemoteAddr = $input[0];
            } else {
                $serverRemoteAddr = null;
            }
            $trustedProxies = array_map('strval', $input[1]);
            $result = array_map('strval', $input[2]);

            $_SERVER['REMOTE_ADDR'] = $serverRemoteAddr;
            Request::setTrustedProxies($trustedProxies, Request::HEADER_X_FORWARDED_FOR);
            var_dump($result === Request::getTrustedProxies());
        }
    }

    /** @return mixed[][] */
    public static function trustedProxiesRemoteAddr()
    {
        return [
            ['1.1.1.1', ['REMOTE_ADDR'], ['1.1.1.1']],
            ['1.1.1.1', ['REMOTE_ADDR', '2.2.2.2'], ['1.1.1.1', '2.2.2.2']],
            [null, ['REMOTE_ADDR'], []],
            [null, ['REMOTE_ADDR', '2.2.2.2'], ['2.2.2.2']],
        ];
    }

    /**
     * @dataProvider preferSafeContentData
     */
    public function testPreferSafeContent()
    {
        $data = self::preferSafeContentData();
        foreach ($data as $input) {
            $server = array_map('strval', $input[0]);
            $safePreferenceExpected = (bool)$input[1];

            $request = new Request([], [], [], [], [], $server);

            var_dump($safePreferenceExpected === $request->preferSafeContent());
        }
    }

    /** @return mixed[][] */
    public static function preferSafeContentData()
    {
        return [
            [[], false],
            [
                [
                    'HTTPS' => 'on',
                ],
                false,
            ],
            [
                [
                    'HTTPS' => 'off',
                    'HTTP_PREFER' => 'safe',
                ],
                false,
            ],
            [
                [
                    'HTTPS' => 'on',
                    'HTTP_PREFER' => 'safe',
                ],
                true,
            ],
            [
                [
                    'HTTPS' => 'on',
                    'HTTP_PREFER' => 'unknown-preference',
                ],
                false,
            ],
            [
                [
                    'HTTPS' => 'on',
                    'HTTP_PREFER' => 'unknown-preference=42, safe',
                ],
                true,
            ],
            [
                [
                    'HTTPS' => 'on',
                    'HTTP_PREFER' => 'safe, unknown-preference=42',
                ],
                true,
            ],
        ];
    }

//    public function testReservedFlags()
//    {
//        foreach ((new \ReflectionClass(Request::class))->getConstants() as $constant => $value) {
//            var_dump(0b10000000 !== $value);
//        }
//    }
}
//
//class RequestContentProxy extends Request
//{
//    public function getContent($asResource = false)
//    {
//        return http_build_query(['_method' => 'PUT', 'content' => 'mycontent'], '', '&');
//    }
//}
//
//class NewRequest extends Request
//{
//    public function getFoo()
//    {
//        return 'foo';
//    }
//}
