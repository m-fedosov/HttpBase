<?php

use Kaa\HttpBase\KphpTests\ResponseHeaderBagTest;

require __DIR__ . '/../vendor/autoload.php';

$test = new ResponseHeaderBagTest();

echo"\ntestAllPreserveCase\n";
$test->testAllPreserveCase();

echo"\ntestCacheControlHeader\n";
$test->testCacheControlHeader();

echo"\ntestCacheControlClone\n";
$test->testCacheControlClone();

echo"\ntestToStringIncludesCookieHeaders\n";
$test->testToStringIncludesCookieHeaders();

echo"\ntestClearCookieSecureNotHttpOnly\n";
$test->testClearCookieSecureNotHttpOnly();

echo"\ntestClearCookieSamesite\n";
$test->testClearCookieSamesite();

echo"\ntestReplace\n";
$test->testReplace();

echo"\ntestReplaceWithRemove\n";
$test->testReplaceWithRemove();

echo"\ntestCookiesWithSameNames\n";
$test->testCookiesWithSameNames();

echo"\ntestRemoveCookie\n";
$test->testRemoveCookie();

echo"\ntestRemoveCookieWithNullRemove\n";
$test->testRemoveCookieWithNullRemove();

echo"\ntestSetCookieHeader\n";
$test->testSetCookieHeader();

echo"\ntestToStringDoesntMessUpHeaders\n";
$test->testToStringDoesntMessUpHeaders();

echo"\ntestDateHeaderAddedOnCreation\n";
$test->testDateHeaderAddedOnCreation();

echo"\ntestDateHeaderCanBeSetOnCreation\n";
$test->testDateHeaderCanBeSetOnCreation();

echo"\ntestDateHeaderWillBeRecreatedWhenRemoved\n";
$test->testDateHeaderWillBeRecreatedWhenRemoved();

echo"\ntestDateHeaderWillBeRecreatedWhenHeadersAreReplaced\n";
$test->testDateHeaderWillBeRecreatedWhenHeadersAreReplaced();
