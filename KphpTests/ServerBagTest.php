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

use Kaa\HttpBase\ServerBag;

class ServerBagTest
{
    public function testShouldExtractHeadersFromServerArray(): void
    {
        $server = [
            'SOME_SERVER_VARIABLE' => 'value',
            'SOME_SERVER_VARIABLE2' => 'value',
            'ROOT' => 'value',
            'HTTP_CONTENT_TYPE' => 'text/html',
            'HTTP_CONTENT_LENGTH' => '0',
            'HTTP_ETAG' => 'asdf',
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => 'bar',
        ];

        $bag = new ServerBag($server);

        var_dump([
            'CONTENT_TYPE' => 'text/html',
            'CONTENT_LENGTH' => '0',
            'ETAG' => 'asdf',
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => 'bar',
            'AUTHORIZATION' => 'Basic ' . base64_encode('foo:bar'),
        ] === $bag->getHeaders());
    }

    public function testHttpPasswordIsOptional(): void
    {
        $bag = new ServerBag(['PHP_AUTH_USER' => 'foo']);

        var_dump([
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => '',
            'AUTHORIZATION' => 'Basic ' . base64_encode('foo:'),
        ] === $bag->getHeaders());
    }

    public function testHttpPasswordIsOptionalWhenPassedWithHttpPrefix(): void
    {
        $bag = new ServerBag(['HTTP_PHP_AUTH_USER' => 'foo']);

        var_dump([
            'PHP_AUTH_USER' => 'foo',
            'AUTHORIZATION' => 'Basic ' . base64_encode('foo:'),
        ] === $bag->getHeaders());
    }

    public function testHttpBasicAuthWithPhpCgi(): void
    {
        $bag = new ServerBag(['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('foo:bar')]);

        var_dump([
            'AUTHORIZATION' => 'Basic ' . base64_encode('foo:bar'),
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => 'bar',
        ] === $bag->getHeaders());
    }

    public function testHttpBasicAuthWithPhpCgiBogus(): void
    {
        $bag = new ServerBag(['HTTP_AUTHORIZATION' => 'Basic_' . base64_encode('foo:bar')]);

        // Username and passwords should not be set as the header is bogus
        $headers = $bag->getHeaders();
        var_dump((string)($headers['PHP_AUTH_USER']) === '');
        var_dump((string)($headers['PHP_AUTH_PW']) === '');
    }

    public function testHttpBasicAuthWithPhpCgiRedirect(): void
    {
        $bag = new ServerBag(['REDIRECT_HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('username:pass:word')]);

        var_dump([
            'PHP_AUTH_USER' => 'username',
            'PHP_AUTH_PW' => 'pass:word',
            'AUTHORIZATION' => 'Basic ' . base64_encode('username:pass:word'),
        ] === $bag->getHeaders());
    }

    public function testHttpBasicAuthWithPhpCgiEmptyPassword(): void
    {
        $bag = new ServerBag(['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('foo:')]);

        var_dump([
            'AUTHORIZATION' => 'Basic ' . base64_encode('foo:'),
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => '',
        ] === $bag->getHeaders());
    }

    public function testHttpDigestAuthWithPhpCgi(): void
    {
        $digest = 'Digest username="foo", realm="acme", nonce="' . md5('secret') . '", uri="/protected, qop="auth"';
        $bag = new ServerBag(['HTTP_AUTHORIZATION' => $digest]);

        var_dump([
            'AUTHORIZATION' => $digest,
            'PHP_AUTH_DIGEST' => $digest,
        ] === $bag->getHeaders());
    }

    public function testHttpDigestAuthWithPhpCgiBogus(): void
    {
        $digest = 'Digest_username="foo", realm="acme", nonce="' . md5('secret') . '", uri="/protected, qop="auth"';
        $bag = new ServerBag(['HTTP_AUTHORIZATION' => $digest]);

        // Username and passwords should not be set as the header is bogus
        $headers = $bag->getHeaders();
        var_dump((string)($headers['PHP_AUTH_USER']) === '');
        var_dump((string)($headers['PHP_AUTH_PW']) === '');
    }

    public function testHttpDigestAuthWithPhpCgiRedirect(): void
    {
        $digest = 'Digest username="foo", realm="acme", nonce="' . md5('secret') . '", uri="/protected, qop="auth"';
        $bag = new ServerBag(['REDIRECT_HTTP_AUTHORIZATION' => $digest]);

        var_dump([
            'PHP_AUTH_DIGEST' => $digest,
            'AUTHORIZATION' => $digest,
        ] === $bag->getHeaders());
    }

    public function testOAuthBearerAuth(): void
    {
        $headerContent = 'Bearer L-yLEOr9zhmUYRkzN1jwwxwQ-PBNiKDc8dgfB4hTfvo';
        $bag = new ServerBag(['HTTP_AUTHORIZATION' => $headerContent]);

        var_dump([
            'AUTHORIZATION' => $headerContent,
        ] === $bag->getHeaders());
    }

    public function testOAuthBearerAuthWithRedirect(): void
    {
        $headerContent = 'Bearer L-yLEOr9zhmUYRkzN1jwwxwQ-PBNiKDc8dgfB4hTfvo';
        $bag = new ServerBag(['REDIRECT_HTTP_AUTHORIZATION' => $headerContent]);

        var_dump([
            'AUTHORIZATION' => $headerContent,
        ] === $bag->getHeaders());
    }

    /**
     * @see https://github.com/symfony/symfony/issues/17345
     */
    public function testItDoesNotOverwriteTheAuthorizationHeaderIfItIsAlreadySet(): void
    {
        $headerContent = 'Bearer L-yLEOr9zhmUYRkzN1jwwxwQ-PBNiKDc8dgfB4hTfvo';
        $bag = new ServerBag(['PHP_AUTH_USER' => 'foo', 'HTTP_AUTHORIZATION' => $headerContent]);

        var_dump([
            'AUTHORIZATION' => $headerContent,
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => '',
        ] === $bag->getHeaders());
    }
}
