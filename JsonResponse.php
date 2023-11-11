<?php

namespace Kaa\HttpBase;

use JsonEncoder;
use Kaa\HttpBase\Exception\JsonException;

/**
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * Response represents an HTTP response in JSON format.
 *
 * Note that this class does not force the returned JSON content to be an
 * object. It is however recommended that you do return an object as it
 * protects yourself against XSSI and JSON-JavaScript Hijacking.
 *
 * @see https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/AJAX_Security_Cheat_Sheet.md#always-return-json-with-an-object-on-the-outside
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class JsonResponse extends Response
{
    private string $data;

    private ?string $callback = null;

    // Encode <, >, ', &, and " characters in the JSON, making it also safe to be embedded into HTML.
    // 15 === JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    public const DEFAULT_ENCODING_OPTIONS = ['JSON_HEX_TAG', 'JSON_HEX_APOS', 'JSON_HEX_AMP', 'JSON_HEX_QUOT'];

    /** @var string[] $encodingOptions */
    private $encodingOptions = self::DEFAULT_ENCODING_OPTIONS;

    /**
     * @param mixed     $data
     * @param string[]  $headers
     *
     * @throws \TypeError
     * @throws \Exception
     */
    public function __construct($data = null, int $status = 200, $headers = [], bool $json = false)
    {
        parent::__construct('', $status, $headers);

        if ($json && !\is_string($data) && !is_numeric($data)) {
            throw new \TypeError(
                sprintf(
                    '"%s": If $json is set to true, argument $data must be a string "%s" given.',
                    __METHOD__,
                    gettype($data)
                )
            );
        }

        if ($data === null && $json === false) {
            $this->setJson('{}');
        } elseif ($json === true && is_string($data)) {
            $this->setJson($data);
        } elseif ($json === false && is_string($data)) {
            $this->setJson('"' . self::escapeJsonSpecialChars($data) . '"');
        } else {
            $this->setData($data);
        }
    }

    /**
     * @param string[] $headers
     * @throws JsonException
     */
    public static function fromObject(object $data, int $status = Response::HTTP_OK, array $headers = []): self
    {
        $jsonData = JsonEncoder::encode($data);
        if ($jsonData === '' && JsonEncoder::getLastError() !== '') {
            throw new JsonException(JsonEncoder::getLastError());
        }

        return new self($jsonData, $status, $headers);
    }

    /**
     * Factory method for chainability.
     *
     * Example:
     *
     *     return JsonResponse::fromJsonString('{"key": "value"}')
     *         ->setSharedMaxAge(300);
     *
     * @param string    $data    The JSON response string
     * @param int       $status  The response status code (200 "OK" by default)
     * @param string[]  $headers  An array of response headers
     */
    public static function fromJsonString(string $data, int $status = 200, $headers = []): self
    {
        return new self($data, $status, $headers, true);
    }

    /**
     * Sets the JSONP callback.
     *
     * @param string|null $callback The JSONP callback or null to use none
     *
     * @return $this
     *
     * @throws \InvalidArgumentException When the callback name is not valid
     */
    public function setCallback(?string $callback = null): Response
    {
        if ($callback !== null) {
            // partially taken from https://geekality.net/2011/08/03/valid-javascript-identifier/
            // partially taken from https://github.com/willdurand/JsonpCallbackValidator
            //      JsonpCallbackValidator is released under the MIT License.
            //      See https://github.com/willdurand/JsonpCallbackValidator/blob/v1.1.0/LICENSE for details.
            //      (c) William Durand <william.durand1@gmail.com>
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*(?:\[(?:"(?:\\\.|[^"\\\])*"|\'(?:\\\.|[^\'\\\])*\'|\d+)\])*?$/u';
            $reserved = [
                'break', 'do', 'instanceof', 'typeof', 'case', 'else', 'new', 'var', 'catch', 'finally', 'return',
                'void', 'continue', 'for', 'switch', 'while', 'debugger', 'function', 'this', 'with', 'default', 'if',
                'throw', 'delete', 'in', 'try', 'class', 'enum', 'extends', 'super',  'const', 'export', 'import',
                'implements', 'let', 'private', 'public', 'yield', 'interface', 'package', 'protected', 'static',
                'null', 'true', 'false',
            ];
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                $matches = [];
                if (!(bool)preg_match($pattern, $part, $matches) || \in_array($part, $reserved, true)) {
                    throw new \InvalidArgumentException('The callback name is not valid.');
                }
            }
        }

        $this->callback = $callback;

        return $this->update();
    }

    /**
     * Sets a raw string containing a JSON document to be sent.
     */
    public function setJson(string $json): Response
    {
        $this->data = $json;

        return $this->update();
    }

    /**
     * Sets the data to be sent as JSON.
     *
     * @param mixed $data
     *
     * @throws \Exception
     */
    public function setData($data): Response
    {
        $data = json_encode($data);

        return $this->setJson((string)$data);
    }

    /**
     * Returns options used while encoding data to JSON.
     *
     * @return string[]
     */
    public function getEncodingOptions()
    {
        return $this->encodingOptions;
    }

    /**
     * Updates the content and headers according to the JSON data and callback.
     */
    protected function update(): Response
    {
        if ($this->callback !== null) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');

            return $this->setContent(sprintf('/**/%s(%s);', $this->callback, $this->data));
        }

        // Only set the header when there is none or when it equals 'text/javascript'
        // (from a previous update with callback) in order to not overwrite a custom definition.
        if (
            !$this->headers->has('Content-Type')
            || $this->headers->get('Content-Type') === 'text/javascript'
        ) {
            $this->headers->set('Content-Type', 'application/json');
        }

        return $this->setContent($this->data);
    }

    public function escapeJsonSpecialChars(string $string): string
    {
        if ($this->encodingOptions === self::DEFAULT_ENCODING_OPTIONS) {
            $count = 0;
            $string = str_replace('&', '\u0026', $string, $count);
            $string = str_replace('<', '\u003C', $string, $count);
            $string = str_replace('>', '\u003E', $string, $count);
            $string = str_replace('"', '\u0022', $string, $count);
            $string = str_replace("'", '\u0027', $string, $count);
        }
        return $string;
    }
}
