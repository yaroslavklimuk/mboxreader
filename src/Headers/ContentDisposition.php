<?php

namespace YaroslavKlimuk\MboxReader\Headers;

use YaroslavKlimuk\MboxReader\Helpers;

/**
 * Description of ContentType
 *
 * @author yaklimuk
 */
class ContentDisposition
{
    protected $value;
    protected $filename;

    public function __construct(string $value, string $filename = null)
    {
        $this->value = $value;
        $this->filename = $filename;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public static function parseValue(string $cdisp)
    {
        $cdispParts = explode(';', $cdisp);
        preg_match('/filename=\"?([\x20\x21\x23-\x7E]+)\"?/', $cdisp, $matches);
        $filename = isset($matches[1]) ? $matches[1] : null;
        if(isset($filename) && Helpers::isMimeEncoded($filename)){
            $filename = Helpers::parseMimeEncoded($filename);
        }

        return new ContentDisposition($cdispParts[0], $filename);
    }
}
