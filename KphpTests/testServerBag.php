<?php

use Kaa\HttpBase\KphpTests\ServerBagTest;

require __DIR__ . '/../vendor/autoload.php';

$test = new ServerBagTest();

echo"\ntestShouldExtractHeadersFromServerArray\n";
$test->testShouldExtractHeadersFromServerArray();

echo"\ntestHttpPasswordIsOptional\n";
$test->testHttpPasswordIsOptional();

echo"\ntestHttpPasswordIsOptionalWhenPassedWithHttpPrefix\n";
$test->testHttpPasswordIsOptionalWhenPassedWithHttpPrefix();

echo"\ntestHttpBasicAuthWithPhpCgi\n";
$test->testHttpBasicAuthWithPhpCgi();

echo"\ntestHttpBasicAuthWithPhpCgiBogus\n";
$test->testHttpBasicAuthWithPhpCgiBogus();

echo"\ntestHttpBasicAuthWithPhpCgiRedirect\n";
$test->testHttpBasicAuthWithPhpCgiRedirect();

echo"\ntestHttpBasicAuthWithPhpCgiEmptyPassword\n";
$test->testHttpBasicAuthWithPhpCgiEmptyPassword();

echo"\ntestHttpDigestAuthWithPhpCgi\n";
$test->testHttpDigestAuthWithPhpCgi();

echo"\ntestHttpDigestAuthWithPhpCgiBogus\n";
$test->testHttpDigestAuthWithPhpCgiBogus();

echo"\ntestHttpDigestAuthWithPhpCgiRedirect\n";
$test->testHttpDigestAuthWithPhpCgiRedirect();

echo"\ntestOAuthBearerAuth\n";
$test->testOAuthBearerAuth();

echo"\ntestOAuthBearerAuthWithRedirect\n";
$test->testOAuthBearerAuthWithRedirect();

echo"\ntestItDoesNotOverwriteTheAuthorizationHeaderIfItIsAlreadySet\n";
$test->testItDoesNotOverwriteTheAuthorizationHeaderIfItIsAlreadySet();
