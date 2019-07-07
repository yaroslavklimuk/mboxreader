<?php

namespace YaroslavKlimuk\MboxReader\Headers;

use YaroslavKlimuk\MboxReader\Helpers;

/**
 * Description of ContentType
 *
 * @author yaklimuk
 */
class ContentType
{
    protected $value;
    protected $charset;
    protected $boundary;
    protected $name;

    public function __construct(string $value, string $charset, string $boundary = null, string $name = null)
    {
        $this->value = $value;
        $this->charset = $charset;
        $this->boundary = $boundary;
        $this->name = $name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function getBoundary()
    {
        return $this->boundary;
    }

    public function getName()
    {
        return $this->name;
    }

    public static function parseValue(string $ctype)
    {
        $ctypeParts = explode(';', $ctype);
        preg_match('/charset=\"?([a-zA-Z-0-9]+)/', $ctype, $matches);
        $charset = isset($matches[1]) ? $matches[1] : 'US-ASCII';

        preg_match('/.+boundary=\"?([\x20\x21\x23-\x7E]+)\"?.*/', $ctype, $matches);
        $boundary = isset($matches[1]) ? $matches[1] : null;

        preg_match('/.+name=\"?([\x20\x21\x23-\x7E]+)\"?.*/', $ctype, $matches);
        $name = isset($matches[1]) ? $matches[1] : null;
        if(isset($name) && Helpers::isMimeEncoded($name)){
            $name = Helpers::parseMimeEncoded($name);
        }

        return new ContentType($ctypeParts[0], $charset, $boundary, $name);
    }
}
