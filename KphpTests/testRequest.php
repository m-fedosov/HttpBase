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

use Kaa\HttpFoundation\KphpTests\RequestTest;

require __DIR__ . '/../vendor/autoload.php';

$test = new RequestTest();

echo"\ntestInitialize\n";
$test->testInitialize();

echo"\ntestGetUser\n";
$test->testGetUser();

echo"\ntestGetPassword\n";
$test->testGetPassword();

echo"\ntestIsNoCache\n";
$test->testIsNoCache();

echo"\ntestGetContentTypeFormat\n";
$test->testGetContentTypeFormat();

echo"\ntestCreate\n";
$test->testCreate();

echo"\ntestCreateWithRequestUri\n";
$test->testCreateWithRequestUri();

echo"\ntestGetRequestUri\n";
$test->testGetRequestUri();

echo"\ntestGetRequestUriWithoutRequiredHeader\n";
$test->testGetRequestUriWithoutRequiredHeader();

echo"\ntestCreateCheckPrecedence\n";
$test->testCreateCheckPrecedence();

echo"\ntestDuplicate\n";
$test->testDuplicate();

echo"\ntestDuplicateWithFormat\n";
$test->testDuplicateWithFormat();

echo"\ntestGetPreferredFormat\n";
$test->testGetPreferredFormat();

echo"\ntestGetFormatFromMimeType\n";
$test->testGetFormatFromMimeType();

echo"\ntestGetFormatFromMimeTypeWithParameters\n";
$test->testGetFormatFromMimeTypeWithParameters();

echo"\ntestGetMimeTypeFromFormat\n";
$test->testGetMimeTypeFromFormat();

echo"\ntestGetMimeTypesFromFormat\n";
$test->testGetMimeTypesFromFormat();

echo"\ntestGetMimeTypesFromInexistentFormat\n";
$test->testGetMimeTypesFromInexistentFormat();

echo"\ntestGetFormatWithCustomMimeType\n";
$test->testGetFormatWithCustomMimeType();

echo"\ntestGetUri\n";
$test->testGetUri();

echo"\ntestGetUriForPath\n";
$test->testGetUriForPath();

echo"\ntestGetRelativeUriForPath\n";
$test->testGetRelativeUriForPath();

echo"\ntestGetUserInfo\n";
$test->testGetUserInfo();

echo"\ntestGetSchemeAndHttpHost\n";
$test->testGetSchemeAndHttpHost();

echo"\ntestGetQueryString\n";
$test->testGetQueryString();

echo"\ntestGetQueryStringReturnsNull\n";
$test->testGetQueryStringReturnsNull();

echo"\ntestGetHost\n";
$test->testGetHost();

echo"\ntestGetPort\n";
$test->testGetPort();

echo"\ntestGetHostWithFakeHttpHostValue\n";
$test->testGetHostWithFakeHttpHostValue();

echo"\ntestGetSetMethod\n";
$test->testGetSetMethod();

echo"\ntestGetClientIp\n";
$test->testGetClientIp();

echo"\ntestGetClientIps\n";
$test->testGetClientIps();

echo"\ntestGetClientIpsForwarded\n";
$test->testGetClientIpsForwarded();

echo"\ntestGetClientIpsWithConflictingHeaders\n";
$test->testGetClientIpsWithConflictingHeaders();

echo"\ntestGetClientIpsOnlyXHttpForwardedForTrusted\n";
$test->testGetClientIpsOnlyXHttpForwardedForTrusted();

echo"\ntestGetClientIpsWithAgreeingHeaders\n";
$test->testGetClientIpsWithAgreeingHeaders();

echo"\ntestGetContentWorksTwiceInDefaultMode\n";
$test->testGetContentWorksTwiceInDefaultMode();

echo"\ntestToArrayEmpty\n";
$test->testToArrayEmpty();

echo"\ntestToArrayNonJson\n";
$test->testToArrayNonJson();

echo"\ntestToArray\n";
$test->testToArray();

echo"\ntestCreateFromGlobals\n";
$test->testCreateFromGlobals();

echo"\ntestOverrideGlobals\n";
$test->testOverrideGlobals();

echo"\ntestGetScriptName\n";
$test->testGetScriptName();

echo"\ntestGetBasePath\n";
$test->testGetBasePath();

echo"\ntestGetPathInfo\n";
$test->testGetPathInfo();

echo"\ntestGetPreferredLanguage\n";
$test->testGetPreferredLanguage();

echo"\ntestIsXmlHttpRequest\n";
$test->testIsXmlHttpRequest();

echo"\ntestGetCharsets\n";
$test->testGetCharsets();

echo"\ntestGetEncodings\n";
$test->testGetEncodings();

echo"\ntestGetAcceptableContentTypes\n";
$test->testGetAcceptableContentTypes();

echo"\ntestGetLanguages\n";
$test->testGetLanguages();

echo"\ntestGetAcceptHeadersReturnString\n";
$test->testGetAcceptHeadersReturnString();

echo"\ntestGetRequestFormat\n";
$test->testGetRequestFormat();

echo"\ntestToString\n";
$test->testToString();

echo"\ntestIsMethod\n";
$test->testIsMethod();

echo"\ntestGetBaseUrl\n";
$test->testGetBaseUrl();

echo"\ntestTrustedProxiesXForwardedFor\n";
$test->testTrustedProxiesXForwardedFor();

echo"\ntestTrustedProxiesForwarded\n";
$test->testTrustedProxiesForwarded();

echo"\ntestIISRequestUri\n";
$test->testIISRequestUri();

echo"\ntestTrustedHosts\n";
$test->testTrustedHosts();

echo"\ntestSetTrustedHostsDoesNotBreakOnSpecialCharacters\n";
$test->testSetTrustedHostsDoesNotBreakOnSpecialCharacters();

echo"\ntestVeryLongHosts\n";
$test->testVeryLongHosts();

echo"\ntestHostValidity\n";
$test->testHostValidity();

echo"\ntestMethodIdempotent\n";
$test->testMethodIdempotent();

echo"\ntestMethodSafe\n";
$test->testMethodSafe();

echo"\ntestMethodCacheable\n";
$test->testMethodCacheable();

echo"\ntestProtocolVersion\n";
$test->testProtocolVersion();

echo"\ntestNonstandardRequests\n";
$test->testNonstandardRequests();

echo"\ntestTrustedHost\n";
$test->testTrustedHost();

echo"\ntestTrustedPrefix\n";
$test->testTrustedPrefix();

echo"\ntestTrustedPrefixWithSubdir\n";
$test->testTrustedPrefixWithSubdir();

echo"\ntestTrustedPrefixEmpty\n";
$test->testTrustedPrefixEmpty();

echo"\ntestTrustedPort\n";
$test->testTrustedPort();

echo"\ntestTrustedPortDoesNotDefaultToZero\n";
$test->testTrustedPortDoesNotDefaultToZero();

echo"\ntestTrustedPortDoesNotDefaultToZero\n";
$test->testTrustedPortDoesNotDefaultToZero();

echo"\ntestTrustedProxiesRemoteAddr\n";
$test->testTrustedProxiesRemoteAddr();

echo"\ntestPreferSafeContent\n";
$test->testPreferSafeContent();
