<?php

namespace YaroslavKlimuk\MboxReader;

/**
 * Description of MboxReader
 *
 * @author yaklimuk
 */
class MailBody
{
    protected $file;
    protected $startPos;
    protected $endPos;

    protected $contentType;
    protected $charset;
    protected $transferEncoding;

    public function __construct(string $file, int $startPos, int $endPos, Headers\ContentType $contentType, string $transferEncoding = null)
    {
        $this->file = $file;
        $this->startPos = $startPos;
        $this->endPos = $endPos;
        $this->contentType = $contentType;
        $this->transferEncoding = $transferEncoding;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getContent()
    {
        if(!\is_file($this->file)){
            throw new Exception('Can not open the file: ' . $this->file);
        }
        $resource = \fopen($this->file, 'r');
        if(!\is_resource($resource)){
            throw new Exception('Can not open the file: ' . $this->file);
        }
        fseek($resource, $this->startPos);
        $content = null;
        while($line = \fgets($resource)){
            $content .= \rtrim($line, "\r\n");
            if(\ftell($resource) >= $this->endPos){
                break;
            }
        }
        return isset($this->transferEncoding) ? Helpers::decodeFromTransferEncoding($this->transferEncoding, $content) : $content;
    }

}
