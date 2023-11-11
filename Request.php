<?php

namespace Kaa\HttpBase;

use InvalidArgumentException;
use Kaa\HttpBase\Exception\BadRequestException;
use Kaa\HttpBase\Exception\JsonException;
use Kaa\HttpBase\Exception\SuspiciousOperationException;
use Kaa\HttpBase\HeaderUtils;

class Request
{
    # In KPHP, there is not yet a predefined constant directory_separator
    public const DIRECTORY_SEPARATOR = "/";

    public ParameterBag $attributes;

    /**
     * Request body parameters ($_POST).
     */
    public InputBag $request;

    /**
     * Query string parameters ($_GET).
     */
    public InputBag $query;

    /**
     * Server and execution environment parameters ($_SERVER).
     */
    public ServerBag $server;

    /**
     * Headers (taken from the $_SERVER).
     */
    public HeaderBag $headers;

    /** @var string|false $content */
    private $content;

    private static bool $httpMethodParameterOverride = false;

    /** @var string[][] */
    private static $formats = [
        'html' => ['text/html', 'application/xhtml+xml'],
        'txt' => ['text/plain'],
        'js' => ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'css' => ['text/css'],
        'json' => ['application/json', 'application/x-json'],
        'jsonld' => ['application/ld+json'],
        'xml' => ['text/xml', 'application/xml', 'application/x-xml'],
        'rdf' => ['application/rdf+xml'],
        'atom' => ['application/atom+xml'],
        'rss' => ['application/rss+xml'],
        'form' => ['application/x-www-form-urlencoded', 'multipart/form-data'],
    ];

    /**
     * @param string[]             $query      The GET parameters
     * @param string[]             $request    The POST parameters
     * @param string[]             $server     The SERVER parameters
     * @param string|false         $content    The raw body data
     */
    public function __construct(
        $query = [],
        $request = [],
        $server = [],
        $content = false
    ) {
        $this->initialize($query, $request, $server, $content);
    }

    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param string[]             $query      The GET parameters
     * @param string[]             $request    The POST parameters
     * @param string[]             $server     The SERVER parameters
     * @param string|false         $content    The raw body data
     */
    public function initialize($query = [], $request = [], $server = [], $content = false): void
    {
        $this->query = new InputBag($query);
        $this->request = new InputBag($request);
        $this->server = new ServerBag($server);
        $this->headers = new HeaderBag($this->server->getHeaders());
        $this->content = $content;
    }

    /**
     * Creates a new request with values from PHP's super globals.
     */
    public static function createFromGlobals(): static
    {
        /** @var string[] $getArray */
        $getArray = array_map('strval', $_GET);

        /** @var string[] $postArray */
        $postArray = array_map('strval', $_POST);

        /** @var mixed $serverStringValues */
        $serverStringValues = array_filter($_SERVER, function ($value) {
            return !\is_array($value);
        });

        /** @var string[] $serverArray */
        $serverArray = array_map('strval', $serverStringValues);

        $request = new static($getArray, $postArray, $serverArray, false);

        $headerString = $request->headers->get('CONTENT_TYPE', '');

        if (
            isset($headerString) && str_starts_with($headerString, 'application/x-www-form-urlencoded')
            && \in_array(
                strtoupper((string)$request->server->get('REQUEST_METHOD', 'GET')),
                ['PUT', 'DELETE', 'PATCH'],
                true
            )
        ) {
            parse_str((string)$request->getContent(), $data);
            $request->request = new InputBag($data);
        }

        return $request;
    }

    /**
     * Creates a Request based on a given URI and configuration.
     *
     * The information contained in the URI always take precedence
     * over the other information (server and parameters).
     *
     * @param string               $uri        The URI
     * @param string               $method     The HTTP method
     * @param string[]             $parameters The query (GET) or request (POST) parameters
     * @param string[]             $server     The server parameters ($_SERVER)
     * @param ?string              $content    The raw body data
     */
    public static function create(
        $uri,
        $method = 'GET',
        $parameters = [],
        $server = [],
        $content = null
    ): self {
        # It does not make sense to put the $server variable as a ServerConfig class.
        # Let's just convert everything to a string[] array
        $server = array_replace([
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
            'HTTP_HOST' => 'localhost',
            'HTTP_USER_AGENT' => 'Symfony',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR' => '127.0.0.1',
            'SCRIPT_NAME' => '',
            'SCRIPT_FILENAME' => '',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_TIME' => (string)time(),
            'REQUEST_TIME_FLOAT' => (string)microtime(true),
        ], $server);

        $server['PATH_INFO'] = '';
        $server['REQUEST_METHOD'] = strtoupper($method);

        $components = parse_url($uri);

        if (is_array($components)) {
            $components = array_map('strval', $components);
        } else {
            $components = [];
        }

        if (isset($components['host'])) {
            $server['SERVER_NAME'] = $components['host'];
            $server['HTTP_HOST'] = $components['host'];
        }

        if (isset($components['scheme'])) {
            if ($components['scheme'] === 'https') {
                $server['HTTPS'] = 'on';
                $server['SERVER_PORT'] = '443';
            } else {
                unset($server['HTTPS']);
                $server['SERVER_PORT'] = '80';
            }
        }

        if (isset($components['port'])) {
            $server['SERVER_PORT'] = $components['port'];
            $server['HTTP_HOST'] .= ':' . $components['port'];
        }

        if (isset($components['user'])) {
            $server['PHP_AUTH_USER'] = $components['user'];
        }

        if (isset($components['pass'])) {
            $server['PHP_AUTH_PW'] = $components['pass'];
        }

        if (!isset($components['path'])) {
            $components['path'] = '/';
        }

        switch (strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                if (!isset($server['CONTENT_TYPE'])) {
                    $server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
                }
            // no break
            case 'PATCH':
                $request = $parameters;
                $query = [];
                break;
            default:
                $request = [];
                $query = $parameters;
                break;
        }

        $queryString = '';
        if (isset($components['query'])) {
            parse_str(html_entity_decode($components['query']), $qs);

            $qs = array_map('strval', $qs);

            if ((bool)$query) {
                $query = array_replace($qs, $query);
                $queryString = http_build_query($query, '', '&');
            } else {
                $query = $qs;
                $queryString = $components['query'];
            }
        } elseif ((bool)$query) {
            $queryString = http_build_query($query, '', '&');
        }

        $server['REQUEST_URI'] = $components['path'] . ((string)$queryString !== '' ? '?' . $queryString : '');
        $server['QUERY_STRING'] = $queryString;

        return new static($query, $request, $server, $content);
    }

    /**
     * Clones a request and overrides some of its parameters.
     *
     * @param ?string[] $query      The GET parameters
     * @param ?string[] $request    The POST parameters
     * @param ?string[] $server     The SERVER parameters
     */
    public function duplicate($query = null, $request = null, $server = null): static
    {
        $dup = clone $this;
        if ($query !== null) {
            $dup->query = new InputBag($query);
        }
        if ($request !== null) {
            $dup->request = new InputBag($request);
        }
        if ($server !== null) {
            $dup->server = new ServerBag($server);
            $dup->headers = new HeaderBag($dup->server->getHeaders());
        }

        if (!(bool)$dup->get('_format') && (bool)$this->get('_format')) {
            $dup->attributes->set('_format', (string)$this->get('_format'));
        }

        return $dup;
    }

    /**
     * Clones the current request.
     *
     * Note that the session is not cloned as duplicated requests
     * are most of the time sub-requests of the main one.
     */
    public function __clone()
    {
        $this->query = clone $this->query;
        $this->request = clone $this->request;
        $this->attributes = clone $this->attributes;
        $this->cookies = clone $this->cookies;
        $this->files = clone $this->files;
        $this->server = clone $this->server;
        $this->headers = clone $this->headers;
    }

    /**
     * @throws SuspiciousOperationException
     * @throws BadRequestException
     */
    public function __toString(): string
    {
        $content = $this->getContent();

        $cookieHeader = '';
        $cookies = [];

        foreach ($this->cookies->all() as $k => $v) {
            if (\is_array($v)) {
                $cookies[] = http_build_query([$k => $v], '', '; ', \PHP_QUERY_RFC3986);
            } else {
                $key = (string)$k;
                $value = (string)$v;
                $cookies[] = "{$key}={$value}";
            }
        }

        if (count($cookies) !== 0) {
            $cookieHeader = 'Cookie: ' . implode('; ', $cookies) . "\r\n";
        }

        return
            sprintf(
                '%s %s %s',
                $this->getMethod(),
                $this->getRequestUri(),
                $this->server->get('SERVER_PROTOCOL')
            ) .
            "\r\n" .
            (string)$this->headers .
            $cookieHeader . "\r\n" .
            $content;
    }

    /**
     * Overrides the PHP global variables according to this request instance.
     *
     * It overrides $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE.
     * $_FILES is never overridden, see rfc1867
     */
    public function overrideGlobals(): void
    {
        $this->server->set(
            'QUERY_STRING',
            self::normalizeQueryString(http_build_query($this->query->all(), '', '&'))
        );

        $_GET = $this->query->all();
        $_POST = $this->request->all();
        $_SERVER = $this->server->all();
        $_COOKIE = $this->cookies->all();

        if (count($this->headers->all()) !== 0) {
            foreach ($this->headers->all() as $key => $value) {
                $count = 0;
                $key = strtoupper(str_replace('-', '_', $key, $count));
                if (\in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                    $_SERVER[$key] = implode(', ', $value);
                } else {
                    $_SERVER['HTTP_' . $key] = implode(', ', $value);
                }
            }
        }

        $request = ['g' => $_GET, 'p' => $_POST, 'c' => $_COOKIE];

        $requestOrder = 'gpc';

        $_REQUEST = [[]];

        foreach (str_split($requestOrder) as $order) {
            $_REQUEST[] = $request[$order];
        }

        $requestArray = [];
        foreach ($_REQUEST as $requestData) {
            if (is_array($requestData)) {
                $requestArray = array_merge($requestArray, $requestData);
            }
        }
        $_REQUEST = $requestArray;
    }

    /**
     * Normalizes a query string.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized,
     * have consistent escaping and unneeded delimiters are removed.
     */
    public static function normalizeQueryString(?string $qs): string
    {
        if (($qs ?? '') === '') {
            return '';
        }

        $qs = HeaderUtils::parseQuery((string)$qs);
        ksort($qs);

        return http_build_query($qs, '', '&', \PHP_QUERY_RFC3986);
    }

    /**
     * Gets a "parameter" value from any bag.
     *
     * This method is mainly useful for libraries that want to provide some flexibility. If you don't need the
     * flexibility in controllers, it is better to explicitly get request parameters from the appropriate
     * public property instead (attributes, query, request).
     *
     * Order of precedence: PATH (routing placeholders or custom attributes), GET, POST
     *
     *@internal use explicit input sources instead
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $result = $this->attributes->get($key, (string)$this);
        if ((string)$this !== $result) {
            return $result;
        }

        $qAll = $this->query->all();
        if ($this->query->has($key) && \is_array($qAll)) {
            return (string)$qAll[$key];
        }

        $rAll = $this->request->all();
        if ($this->request->has($key) && \is_array($rAll)) {
            return (string)$rAll[$key];
        }

        return $default;
    }

    /**
     * Returns the request body content.
     */
    public function getContent(): ?string
    {
        if ($this->content === null) {
            $fileGetContents = file_get_contents('php://input');
            if ($fileGetContents !== false) {
                $this->content = $fileGetContents;
            }
        }

        return $this->content;
    }

    /**
     * Gets the request "intended" method.
     *
     * If the X-HTTP-Method-Override header is set, and if the method is a POST,
     * then it is used to determine the "real" intended HTTP method.
     *
     * The _method request parameter can also be used to determine the HTTP method,
     * but only if enableHttpMethodParameterOverride() has been called.
     *
     * The method is always an uppercased string.
     *
     * @throws SuspiciousOperationException
     * @throws BadRequestException
     *
     * @see getRealMethod()
     */
    public function getMethod(): ?string
    {
        if ($this->method !== null) {
            return $this->method;
        }

        $this->method = strtoupper((string)$this->server->get('REQUEST_METHOD', 'GET'));

        if ($this->method !== 'POST') {
            return $this->method;
        }

        $method = $this->headers->get('X-HTTP-METHOD-OVERRIDE');

        if (!(bool)$method && self::$httpMethodParameterOverride) {
            $method = $this->request->get('_method', (string)$this->query->get('_method', 'POST'));
        }

        if (!\is_string($method)) {
            return $this->method;
        }

        $method = strtoupper($method);

        if (
            \in_array(
                $method,
                ['GET', 'HEAD', 'POST',
                    'PUT', 'DELETE', 'CONNECT',
                    'OPTIONS', 'PATCH', 'PURGE',
                    'TRACE'],
                true
            )
        ) {
            return $this->method = $method;
        }

        if (!(bool)preg_match('/^[A-Z]++$/D', $method, $matches)) {
            throw new SuspiciousOperationException(sprintf('Invalid method override "%s".', $method));
        }

        return $this->method = $method;
    }

    /**
     * Checks if the request method is of specified type.
     *
     * @param string $method Uppercase request method (GET, POST etc)
     * @throws BadRequestException
     * @throws SuspiciousOperationException
     */
    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * Gets the request format.
     *
     * Here is the process to determine the format:
     *
     *  * format defined by the user (with setRequestFormat())
     *  * _format request attribute
     *  * $default
     *
     * @see getPreferredFormat
     */
    public function getRequestFormat(?string $default = 'html'): ?string
    {
        $this->format ??= $this->attributes->get('_format');

        return $this->format ?? $default;
    }

    /**
     * Gets the mime type associated with the format.
     */
    public function getMimeType(string $format): ?string
    {
        if (isset(self::$formats[$format])) {
            return self::$formats[$format][0];
        }
        return null;
    }


    /**
     * Returns the requested URI (path and query string).
     *
     * @return ?string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri(): ?string
    {
        return $this->requestUri ??= $this->prepareRequestUri();
    }

    /*
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (https://framework.zend.com/license).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (https://www.zend.com/)
     */

    private function prepareRequestUri(): string
    {
        $requestUri = '';

        if (
            $this->server->get('IIS_WasUrlRewritten') == '1'
            && $this->server->get('UNENCODED_URL') != ''
        ) {
            // IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
            $requestUri = $this->server->get('UNENCODED_URL', '');
            $this->server->remove('UNENCODED_URL');
            $this->server->remove('IIS_WasUrlRewritten');
        } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = (string)$this->server->get('REQUEST_URI', '');

            if (strlen($requestUri) !== 0 && ($requestUri)[0] === '/') {
                // To only use path and query remove the fragment.
                $pos = strpos($requestUri, '#');
                if ($pos !== false) {
                    $requestUri = substr($requestUri, 0, $pos);
                }
            } else {
                // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
                // only use URL path.
                /** @var mixed $uriComponents2 */
                $uriComponents2 = parse_url($requestUri);
                /** @var string[] $uriComponents */
                $uriComponents = array_map('strval', $uriComponents2);

                if (isset($uriComponents['path'])) {
                    $requestUri = $uriComponents['path'];
                }

                if (isset($uriComponents['query'])) {
                    $requestUri .= '?' . $uriComponents['query'];
                }
            }
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = (string)$this->server->get('ORIG_PATH_INFO', '');
            if ($this->server->get('QUERY_STRING') != '') {
                $requestUri .= '?' . $this->server->get('QUERY_STRING', '');
            }
            $this->server->remove('ORIG_PATH_INFO');
        }

        // normalize the request URI to ease creating sub-requests from this request
        $this->server->set('REQUEST_URI', (string)$requestUri);

        return (string)$requestUri;
    }

    /**
     * Gets the request body decoded as array, typically from a JSON payload.
     *
     * @return mixed
     *
     * @throws JsonException When the body cannot be decoded to an array
     */
    public function toArray()
    {
        $content = $this->getContent();

        if ($content === '') {
            throw new JsonException('Request body is empty.');
        }

        $content = json_decode((string)$content, true);

        if (!\is_array($content)) {
            throw new JsonException(
                sprintf(
                    'JSON content was expected to decode to an array, "%s" returned.',
                    gettype($content)
                )
            );
        }

        return $content;
    }
}
