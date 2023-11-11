<?php

namespace Kaa\HttpBase;

class HeaderUtils
{
    public const DISPOSITION_ATTACHMENT = 'attachment';
    public const DISPOSITION_INLINE = 'inline';

    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Splits an HTTP header by one or more separators.
     *
     * Example:
     *
     *     HeaderUtils::split("da, en-gb;q=0.8", ",;")
     *     // => ['da'], ['en-gb', 'q=0.8']]
     *
     * @param string $separators List of characters to split on, ordered by
     *                           precedence, e.g. ",", ";=", or ",;="
     *
     * @return mixed Nested array with as many levels as there are characters in
     *               $separators
     */
    public static function split(string $header, string $separators)
    {
        $quotedSeparators = preg_quote($separators, '/');

        $pattern = '/
                (?!\s)
                    (?:
                        # quoted-string
                        "(?:[^"\\\\]|\\\\.)*(?:"|\\\\|$)
                    |
                        # token
                        [^"' . $quotedSeparators . ']+
                    )+
                (?<!\s)
            |
                # separator
                \s*
                (?<separator>[' . $quotedSeparators . '])
                \s*
            /x';

        preg_match_all($pattern, trim($header), $matches, \PREG_SET_ORDER);

        return self::groupParts((array)$matches, $separators);
    }

    /**
     * Combines an array of arrays into one associative array.
     *
     * Each of the nested arrays should have one or two elements. The first
     * value will be used as the keys in the associative array, and the second
     * will be used as the values, or true if the nested array only contains one
     * element. Array keys are lowercased.
     *
     * Example:
     *
     *     HeaderUtils::combine([["foo", "abc"], ["bar"]])
     *     // => ["foo" => "abc", "bar" => true]
     *
     * @param mixed $parts
     * @return string[]|bool[]
     */
    public static function combine($parts)
    {
        /** @var string[]|bool[] $assoc */
        $assoc = [];
        foreach ($parts as $part) {
            $name = strtolower($part[0]);
            $value = $part[1] ?? true;
            $assoc[$name] = $value;
        }

        return $assoc;
    }

    /**
     * Joins an associative array into a string for use in an HTTP header.
     *
     * The key and value of each entry are joined with "=", and all entries
     * are joined with the specified separator and an additional space (for
     * readability). Values are quoted if necessary.
     *
     * Example:
     *
     *     HeaderUtils::toString(["foo" => "abc", "bar" => true, "baz" => "a b c"], ",")
     *     // => 'foo=abc, bar, baz="a b c"'
     *
     * KPHP:
     *     The conversion to string is actually thought out,
     *     since the real type is true|string
     */
    public static function toString(array $assoc, string $separator): string
    {
        $parts = [];
        foreach ($assoc as $name => $value) {
            // Checking for type is necessary, otherwise the KPHP compiler complains
            if (is_bool($value) && $value === true) {
                $parts[] = $name;
            } else {
                $parts[] = $name . '=' . self::quote((string)$value);
            }
        }

        return implode($separator . ' ', $parts);
    }

    /**
     * Encodes a string as a quoted string, if necessary.
     *
     * If a string contains characters not allowed by the "token" construct in
     * the HTTP specification, it is backslash-escaped and enclosed in quotes
     * to match the "quoted-string" construct.
     */
    public static function quote(string $s): string
    {
        if (preg_match('/^[a-z0-9!#$%&\'*.^_`|~-]+$/i', $s, $matches)) {
            return $s;
        }

        return '"' . addcslashes($s, '"\\"') . '"';
    }

    /**
     * Decodes a quoted string.
     *
     * If passed an unquoted string that matches the "token" construct (as
     * defined in the HTTP specification), it is passed through verbatim.
     *
     * @return mixed
     */
    public static function unquote(string $s)
    {
        return preg_replace('/\\\\(.)|"/', '$1', $s);
    }

    /**
     * Generates an HTTP Content-Disposition field-value.
     *
     * @param string $disposition      One of "inline" or "attachment"
     * @param string $filename         A unicode string
     * @param string $filenameFallback A string containing only ASCII characters that
     *                                 is semantically equivalent to $filename. If the filename is already ASCII,
     *                                 it can be omitted, or just copied from $filename
     *
     * @throws \InvalidArgumentException
     *
     * @see RFC 6266
     */
    public static function makeDisposition(string $disposition, string $filename, string $filenameFallback = ''): string
    {
        if (!\in_array($disposition, [self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The disposition must be either "%s" or "%s".',
                    self::DISPOSITION_ATTACHMENT,
                    self::DISPOSITION_INLINE
                )
            );
        }

        if ('' === $filenameFallback) {
            $filenameFallback = $filename;
        }

        // filenameFallback is not ASCII.
        if (!preg_match('/^[\x20-\x7e]*$/', $filenameFallback, $matches)) {
            throw new \InvalidArgumentException('The filename fallback must only contain ASCII characters.');
        }

        // percent characters aren't safe in fallback.
        if (strpos($filenameFallback, '%') !== false) {
            throw new \InvalidArgumentException('The filename fallback cannot contain the "%" character.');
        }

        // path separators aren't allowed in either.
        if (
            (strpos($filename, '/') !== false) || (strpos($filename, '\\') !== false) ||
            (strpos($filenameFallback, '/') !== false) || (strpos($filenameFallback, '\\') !== false)
        ) {
            throw new \InvalidArgumentException(
                'The filename and the fallback cannot contain the "/" and "\\" characters.'
            );
        }

        $params = ['filename' => $filenameFallback];
        if ($filename !== $filenameFallback) {
            $params['filename*'] = "utf-8''" . rawurlencode($filename);
        }

        return $disposition . '; ' . self::toString($params, ';');
    }

    /**
     * Like parse_str(), but preserves dots in variable names.
     * @return mixed
     */
    public static function parseQuery(string $query, bool $ignoreBrackets = false, string $separator = '&')
    {
        // fixme: Unfortunately there is no way to change it to string[][] only
        /** @var string[][]|string[] $q */
        $q = [];

        foreach (explode($separator, $query) as $v) {
            $i = strpos($v, "\0");
            if ($i !== false) {
                $v = substr($v, 0, $i);
            }

            $i = strpos($v, '=');
            if ($i === false) {
                $k = urldecode($v);
                $v = '';
            } else {
                $k = urldecode(substr($v, 0, $i));
                $v = substr($v, $i);
            }

            $i = strpos($k, "\0");
            if ($i !== false) {
                $k = substr($k, 0, $i);
            }

            $k = ltrim($k, ' ');

            if ($ignoreBrackets) {
                $q[$k][] = urldecode(substr($v, 1));

                continue;
            }

            $i = strpos($k, '[');
            if ($i === false) {
                $q[] = bin2hex($k) . $v;
            } else {
                $q[] = bin2hex(substr($k, 0, $i)) . rawurlencode(substr($k, $i)) . $v;
            }
        }

        if ($ignoreBrackets) {
            return $q;
        }

        // fixme: The parse_str function with input "61%5Bb=c" returns:
        // PHP:
        //      array(1) {
        //          [61_b]=>
        //          string(1) "c"
        //      }
        // KPHP:
        //      array(1) {
        //          [61]=>
        //          string(1) "c"
        //      }
        // var_dump(implode('&', $q));
        //
        // This bug break HeaderUtils testParseQuery test 5

        parse_str(implode('&', $q), $qArray);

        // var_dump($qArray);

        $query = [];

        foreach ($qArray as $k => $v) {
            $k = (string) $k;
//            fixme: The parse_str function currently works a bit differently in KPHP than it does in PHP.
//                   If you pass a string to the function without a key for the value. For example foo=bar&=a=b&=x=y.
//                   That is, foo=bar&(empty key)=a=b&(empty key)=x=y, then parse_str in KPHP will do this array:
//
//                   KPHP:
//                        array(2) {
//                          ["foo"]=>
//                          string(3) "bar"
//                          [""]=>
//                          string(3) "x=y"
//                        }
//
//                    PHP:
//                        array(1) {
//                          'foo' =>
//                          string(3) "bar"
//                        }

            if ($k === '') {
                continue;
            }


            $i = strpos($k, '_');

            if ($i !== false) {
                $query[substr_replace($k, hex2bin(substr($k, 0, $i)) . '[', 0, 1 + $i)] = $v;
            } else {
                $query[hex2bin($k)] = $v;
            }
        }

        return $query;
    }

    private static function groupParts(array $matches, string $separators, bool $first = true): array
    {
        $separator = $separators[0];
        $partSeparators = substr($separators, 1);

        $i = 0;
        $partMatches = [];
        $previousMatchWasSeparator = false;
        foreach ($matches as $match) {
            if (
                !$first && $previousMatchWasSeparator &&
                isset($match['separator']) && ($match['separator'] === $separator)
            ) {
                $previousMatchWasSeparator = true;
                $partMatches[$i][] = $match;
            } elseif (isset($match['separator']) && $match['separator'] === $separator) {
                $previousMatchWasSeparator = true;
                ++$i;
            } else {
                $previousMatchWasSeparator = false;
                $partMatches[$i][] = $match;
            }
        }

        $parts = [];
        if ($partSeparators) {
            foreach ($partMatches as $matches) {
                $parts[] = self::groupParts($matches, $partSeparators, false);
            }
        } else {
            foreach ($partMatches as $matches) {
                $parts[] = self::unquote((string)$matches[0][0]);
            }

            if (!$first && 2 < \count($parts)) {
                $parts = [
                    $parts[0],
                    implode($separator, \array_slice($parts, 1)),
                ];
            }
        }

        return $parts;
    }
}
