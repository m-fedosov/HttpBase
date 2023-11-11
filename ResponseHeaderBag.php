<?php

namespace Kaa\HttpBase;

/**
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * ResponseHeaderBag is a container for Response HTTP headers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ResponseHeaderBag extends HeaderBag
{
    public const COOKIES_FLAT = 'flat';
    public const COOKIES_ARRAY = 'array';

    public const DISPOSITION_ATTACHMENT = 'attachment';
    public const DISPOSITION_INLINE = 'inline';

    /** @var mixed[] $computedCacheControl */
    protected $computedCacheControl = [];

    /** @var Cookie[][][] $cookies */
    protected $cookies = [];

    /** @var string[] $headerNames */
    protected $headerNames = [];

    /** @param string[][]|string[] $headers */
    public function __construct($headers = [])
    {
        parent::__construct($headers);

        if (!isset($this->headers['cache-control'])) {
            $this->setResponseHeader('Cache-Control', '');
        }

        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (!isset($this->headers['date'])) {
            $this->initDate();
        }
    }

    /**
     * Returns the headers, with original capitalizations.
     *
     * @return string[][]
     */
    public function allPreserveCase()
    {
        /** @var string[][] $headers */
        $headers = [];
        foreach ($this->all() as $name => $value) {
            $headers[$this->headerNames[$name] ?? $name] = array_map('strval', $value);
        }

        return $headers;
    }

    /** @return string[][] */
    public function allPreserveCaseWithoutCookies()
    {
        $headers = $this->allPreserveCase();
        if (isset($this->headerNames['set-cookie'])) {
            unset($headers[$this->headerNames['set-cookie']]);
        }

        return $headers;
    }

    /** @param string[][]|string[] $headers */
    public function replace($headers = []): void
    {
        $this->headerNames = [];

        parent::replace($headers);

        if (!isset($this->headers['cache-control'])) {
            $this->set('Cache-Control', '');
        }

        if (!isset($this->headers['date'])) {
            $this->initDate();
        }
    }

    /** @return string[]|string[][] */
    public function all(?string $key = null)
    {
        if ($key !== null) {
            /** @var string[] $result */
            $result = [];
            $key = strtr($key, self::UPPER, self::LOWER);
            if ($key === 'set-cookie') {
                foreach ($this->getCookiesFlat() as $cookie) {
                    $result [] = (string)$cookie;
                }
                return $result;
            } else {
                foreach (parent::all($key) as $header => $value) {
                    if (\is_string($value)) {
                        $result[$header] = $value;
                    }
                }
                return $result;
            }
        }

        /** @var string[][] $result */
        $result = [];
        foreach (parent::all(null) as $header => $valueArray) {
            if (\is_array($valueArray)) {
                $result[$header] = array_map('strval', $valueArray);
            }
        }

        foreach ($this->getCookiesFlat() as $cookie) {
            $result['set-cookie'][] = (string) $cookie;
        }

        return $result;
    }

    /**
     * Sets a header by name.
     *
     * @param int|string           $key     In fact, it's always just a string
     * @param mixed                $values  The value or an array of values
     * @param bool                 $replace Whether to replace the actual value or not (true by default)
     */

    public function set($key, $values, bool $replace = true): void
    {
        $key = (string)$key;
        $uniqueKey = strtr($key, self::UPPER, self::LOWER);

        if ($uniqueKey === 'set-cookie') {
            if ($replace) {
                $this->cookies = [];
            }
            foreach ((array) $values as $cookie) {
                $this->setCookie(Cookie::fromString((string)$cookie));
            }
            $this->headerNames[$uniqueKey] = $key;

            return;
        }

        $this->headerNames[$uniqueKey] = $key;

        parent::set($key, $values, $replace);

        // ensure the cache-control header has sensible defaults
        $computed = $this->computeCacheControlValue();
        if (
            \in_array($uniqueKey, ['cache-control', 'etag', 'last-modified', 'expires'], true)
            && $computed !== ''
        ) {
            $this->headers['cache-control'] = [$computed];
            $this->headerNames['cache-control'] = 'Cache-Control';
            $this->computedCacheControl = $this->parseCacheControl($computed);
        }
    }

    /**
     * Sets a header by name.
     *
     * @param mixed                $values  The value or an array of values
     * @param bool                 $replace Whether to replace the actual value or not (true by default)
     */
    public function setResponseHeader(string $key, $values, bool $replace = true): void
    {
        $uniqueKey = strtr($key, HeaderBag::UPPER, self::LOWER);

        if ($uniqueKey === 'set-cookie') {
            if ($replace) {
                $this->cookies = [];
            }

            return;
        }

        $this->headerNames[$uniqueKey] = $key;

        parent::set($key, $values, $replace);

        // ensure the cache-control header has sensible defaults
        $computed = $this->computeCacheControlValue();
        if (
            \in_array($uniqueKey, ['cache-control', 'etag', 'last-modified', 'expires'], true)
            && $computed !== ''
        ) {
            $this->headers['cache-control'] = [$computed];
            $this->headerNames['cache-control'] = 'Cache-Control';
            $computedCacheControlMixed = $this->parseCacheControl($computed);
            $this->computedCacheControl = array_map('strval', $computedCacheControlMixed);
        }
    }

    public function remove(string $key): void
    {
        $uniqueKey = strtr($key, self::UPPER, self::LOWER);
        unset($this->headerNames[$uniqueKey]);

        if ($uniqueKey === 'set-cookie') {
            $this->cookies = [];

            return;
        }

        parent::remove($key);

        if ($uniqueKey === 'cache-control') {
            $this->computedCacheControl = [];
        }

        if ($uniqueKey === 'date') {
            $this->initDate();
        }
    }

    public function hasCacheControlDirective(string $key): bool
    {
        return \array_key_exists($key, $this->computedCacheControl);
    }

    public function setCookie(Cookie $cookie): void
    {
        $this->cookies[(string)$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        $this->headerNames['set-cookie'] = 'Set-Cookie';
    }

    /**
     * Removes a cookie from the array, but does not unset it in the browser.
     */
    public function removeCookie(string $name, ?string $path = '/', string $domain = '')
    {
        $path ??= '/';

        unset($this->cookies[$domain][$path][$name]);

        if (empty($this->cookies[$domain][$path])) {
            unset($this->cookies[$domain][$path]);

            if (empty($this->cookies[$domain])) {
                unset($this->cookies[$domain]);
            }
        }

        if (empty($this->cookies)) {
            unset($this->headerNames['set-cookie']);
        }
    }

    // Originally, to get an array of cookies as represented in ResponseHeaderBag,
    // you had to call the getCookies() with the flag self::COOKIES_ARRAY.
    // And to get a one-dimensional array of Cookies, call with COOKIES_FLAT flag.
    // But then the return values of the getCookies() will be Cookies[]|Cookies[][][]
    // which KPHP converts to mixed. It was easier to split
    // one function into two with strict return types.

    /**
     * Returns a flattened array with all cookies.
     *
     * @return Cookie[]
     *
     * @throws \InvalidArgumentException When the $format is invalid
     */
    public function getCookiesFlat()
    {
        $flattenedCookies = [];
        foreach ($this->cookies as $path) {
            foreach ($path as $cookies) {
                foreach ($cookies as $cookie) {
                    $flattenedCookies[] = $cookie;
                }
            }
        }

        return $flattenedCookies;
    }

    /**
     * Returns an array with all cookies as it represented in ResponseHeaderBag
     *
     * @return Cookie[][][]
     */
    public function getCookiesArray()
    {
        return $this->cookies;
    }

    /**
     * Clears a cookie in the browser.
     */
    public function clearCookie(
        string $name,
        string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        ?string $sameSite = null
    ): void {
        $this->setCookie(new Cookie($name, null, 1, $path, $domain, $secure, $httpOnly, false, $sameSite));
    }

//    /**
//     * @see HeaderUtils::makeDisposition()
//     */
//    public function makeDisposition(string $disposition, string $filename, string $filenameFallback = ''): string
//    {
//        return HeaderUtils::makeDisposition($disposition, $filename, $filenameFallback);
//    }

    /**
     * Returns the calculated value of the cache-control header.
     *
     * This considers several other headers and calculates or modifies the
     * cache-control header to a sensible, conservative value.
     */
    protected function computeCacheControlValue(): string
    {
        if (count($this->cacheControl) === 0) {
            if ($this->has('Last-Modified') || $this->has('Expires')) {
                return 'private, must-revalidate';
                // allows for heuristic expiration (RFC 7234 Section 4.2.2) in the case of "Last-Modified"
            }

            // conservative by default
            return 'no-cache, private';
        }

        $header = $this->getCacheControlHeader();
        if (isset($this->cacheControl['public']) || isset($this->cacheControl['private'])) {
            return $header;
        }

        // public if s-maxage is defined, private otherwise
        if (!isset($this->cacheControl['s-maxage'])) {
            return $header . ', private';
        }

        return $header;
    }

    private function initDate(): void
    {
        $this->set('Date', gmdate('D, d M Y H:i:s') . ' GMT');
    }
}
