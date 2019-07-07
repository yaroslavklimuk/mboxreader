<?php

namespace YaroslavKlimuk\MboxReader;

/**
 * Description of Helpers
 *
 * @author yaklimuk
 */
class Helpers
{
    
    public static function reachedBodyDelimiter(string $line)
    {
        return 0 === \strlen($line);
    }

    public static function reachedNextMessage(string $line)
    {
        return 0 === \strncmp($line, 'From ', 5);
    }

    public static function reachedNextSection(string $line, string $boundary)
    {
        return 0 === \strncmp($line, '--'.$boundary, \strlen('--'.$boundary));
    }

    public static function reachedMultipartBodyEnd(string $line, string $boundary)
    {
        return 0 === \strncmp($line, '--'.$boundary.'--', \strlen('--'.$boundary.'--'));
    }

    public static function isHeaderStart(string $line)
    {
        return 0 !== \strncmp($line, ' ', 1) && false !== \strpos($line, ':');
    }

    public static function splitHeaderStart(string $line)
    {
        $delimPos = \strpos($line, ':');
        $key = \substr($line, 0, $delimPos);
        $value = \ltrim(\substr($line, $delimPos + 1), " ");
        return [$key, $value];
    }

    public static function isHeaderTail(string $line)
    {
        $symbol = substr($line, 0, 1);
        return $symbol === ' ' || ctype_cntrl($symbol);
    }

    public static function sectionIsMainContent(string $ctype = null, string $contentDisposition = null)
    {
        return !isset($ctype) ||
                (
                    in_array($ctype, [Constants::CT_TEXT_HTML, Constants::CT_TEXT_PLAIN]) &&
                    (
                        !isset($contentDisposition) ||
                        $contentDisposition === Constants::CD_INLINE
                    )
                );
    }

    public static function decodeFromTransferEncoding(string $encoding, string $value)
    {
        switch(\strtolower($encoding)){
            case Constants::TR_ENC_7BIT :
                $value = \preg_replace('/\r?\n/', '', $value);
                $value = \rtrim(\imap_utf7_decode(\rtrim($value)));
                break;
            case Constants::TR_ENC_BASE64 :
                $value = \preg_replace('/\r?\n/', '', $value);
                $value = \rtrim(\base64_decode($value, true));
                break;
            case Constants::TR_ENC_QUOTED_PRINTABLE :
                $value = \preg_replace('/=\?\r?\n/', '', $value);
                $value = \rtrim(\quoted_printable_decode($value));
                break;
        }
        return $value;
    }

    public static function removeCommentsFromString(string $str)
    {
        return preg_replace("/\(([^()]*+|(?R))*\)/","", $str);
    }

    public static function isMimeEncoded(string $value)
    {
        return preg_match('/=\?[a-zA-Z0-9\-]+\?([qbQB])\?([a-zA-Z=0-9\_:\+\-\/]+)\?=/', $value);
    }

    public static function parseMimeEncoded(string $value)
    {
        $items = explode(' ', $value);
        $result = '';
        for($i=0; $i<count($items); $i++){
            if(self::isMimeEncoded($items[$i])){
                preg_match('/([a-zA-Z0-9\-\*]+=\")?=\?([a-zA-Z0-9\-]+)\?([qbQB])\?([a-zA-Z=0-9\_:\+\-\/]+)\?=(\")?/', $items[$i], $matches);
                $prefix = $matches[1];
                $encodingType = $matches[2];
                $transType = \strtolower($matches[3]);
                $postfix = $matches[5] ?? '';
                $value = $matches[4];
                switch ($transType) {
                    case 'b' :
                        $value = iconv($encodingType, 'UTF-8', \base64_decode($value));
                        break;
                    case 'q' :
                        $value = iconv($encodingType, 'UTF-8', \quoted_printable_decode($value));
                        break;
                }
                $result .= $prefix . $value . $postfix . ' ';
            } else {
                $result .= $items[$i] . ' ';
            }
        }
        return \rtrim($result);
    }
}
