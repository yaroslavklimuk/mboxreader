<?php

namespace YaroslavKlimuk\MboxReader;

/**
 * Description of Attachment
 *
 * @author yaklimuk
 */
abstract class AbstractAttachment
{
    protected $file;
    protected $startPos;
    protected $endPos;
    protected $mimetype;
    protected $transferEncoding;

    public function __construct(string $file, int $startPos, int $endPos, string $mimetype, string $transEncoding)
    {
        $this->file = $file;
        $this->startPos = $startPos;
        $this->endPos = $endPos;
        $this->mimetype = $mimetype;
        $this->transferEncoding = $transEncoding;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getMimeType()
    {
        return $this->mimetype;
    }

    public function getTransferEncoding()
    {
        return $this->transferEncoding;
    }

    public function getContent()
    {
        return self::retrieveContent($this->file, $this->startPos, $this->endPos, $this->transferEncoding);
    }

    public static function retrieveContent(string $file, int $startPos, int $endPos, string $transferEncoding)
    {
        $content = '';
        $resource = \fopen($file, 'r');
        \fseek($resource, $startPos);
        $currPos = $startPos;
        while($currPos < $endPos){
            $content .= \fgets($resource);
            $currPos = \ftell($resource);
        }
        return Helpers::decodeFromTransferEncoding($transferEncoding, $content);
    }
}

?>
