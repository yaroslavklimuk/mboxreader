<?php

namespace YaroslavKlimuk\MboxReader;

/**
 * Description of Message
 *
 * @author yaklimuk
 */
class Message
{
    protected $file;

    protected $headers = [];
    protected $receivedChain = [];
    protected $contentType;
    protected $bodies = [];
    protected $attachments = [];

    public function __construct(string $filepath)
    {
        $this->file = $filepath;
    }

    public function getFilepath()
    {
        return $this->file;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setReceivedChain(array $receivedChain)
    {
        $this->receivedChain = $receivedChain;
        return $this;
    }

    public function getReceivedChain()
    {
        return $this->receivedChain;
    }

    public function setContentType(Headers\ContentType $ctype)
    {
        $this->contentType = $ctype;
        return $this;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function hasHeader(string $key)
    {
        return \array_key_exists($key, $this->headers);
    }

    public function getHeader(string $key)
    {
        if(!$this->hasHeader($key)){
            throw new Exception('No such header.');
        }
        return Helpers::parseMimeEncoded($this->headers[$key]);
    }

    public function addBodyOfCType(string $contentType, MailBody $body)
    {
        $this->bodies[$contentType] = $body;
        return $this;
    }

    public function hasBodyOfCType(string $contentType)
    {
        return isset($this->bodies[$contentType]);
    }

    public function getBodyOfCType(string $contentType = Constants::CT_TEXT_PLAIN)
    {
        if(!$this->hasBodyOfCType($contentType)){
            throw new Exception('No such body alterantive.');
        }
        return $this->bodies[$contentType];
    }

    public function addAttachment(CommonAttachment $attachment)
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    public function addInlineAttachment(InlineAttachment $attachment)
    {
        $this->inlineAttachments[] = $attachment;
    }

    public function getAllAttachments()
    {
        return $this->attachments;
    }

    public function getAllInlineAttachments()
    {
        return $this->inlineAttachments;
    }

    public function getAttachmentsWithName(string $filename)
    {
        $filteredAttachments = [];
        foreach($this->attachments as $attachment){
            if($filename === $attachment->getName()){
                $filteredAttachments[] = $attachment;
            }
        }
        return $filteredAttachments;
    }

    public function getAttachmentsWithNameRegex(string $filenameRegex)
    {
        $filteredAttachments = [];
        foreach($this->attachments as $attachment){
            if(\preg_match($filenameRegex, $attachment->getName())){
                $filteredAttachments[] = $attachment;
            }
        }
        return $filteredAttachments;
    }

    public function getInlineAttachmentWithContentId(string $contentId)
    {
        $filteredAttachments = [];
        foreach($this->inlineAttachments as $attachment){
            if($contentId === $attachment->getContentId()){
                $filteredAttachments[] = $attachment;
            }
        }
        return $filteredAttachments;
    }




    public static function parseMessages(string $filename)
    {
        $resource = \fopen($filename, 'r');
        $messages = [];

        while($line = fgets($resource)){
            list($message, $currentPos) = self::parseMessage($filename, \ftell($resource));
            \fseek($resource, $currentPos);
            $messages[] = $message;
        }
        return $messages;
    }

    public static function parseMessage(string $filename, int $startPos)
    {
        $resource = \fopen($filename, 'r');
        \fseek($resource, $startPos);

        $message = new Message($filename);
        self::parseMessageHeaders($resource, $message);
        $message->setContentType(Headers\ContentType::parseValue($message->getHeader(Constants::H_CONTENT_TYPE)));

        if(in_array($message->getContentType()->getValue(), [Constants::CT_MULTIPART_MIXED, Constants::CT_MULTIPART_RELATED])){
            $bodies = self::parseMultipartBody($resource, $message);
        } else {
            self::parseSimpleBody($resource, $message);
        }
        $lastPosition = \ftell($resource);
        \fclose($resource);
        return [$message, $lastPosition];
    }

    public static function parseMessageHeaders(&$resource, Message $message)
    {
        $prevPos = \ftell($resource);
        $headers = [];
        $receivedChain = [];
        
        while($line = \fgets($resource)){
            $line = \trim($line, "\r\n");
            if(Helpers::reachedBodyDelimiter($line)){
                break;
            }
            if(Helpers::isHeaderStart($line)){
                list($hkey, $hval) = Helpers::splitHeaderStart($line);
                $prevPos = \ftell($resource);
                while($line = \fgets($resource)){
                    $line = \trim($line, "\r\n");
                    if(Helpers::isHeaderStart($line) || Helpers::reachedBodyDelimiter($line)){
                        \fseek($resource, $prevPos); break;
                    }
                    if(Helpers::isHeaderTail($line)){
                        $hval .= $line;
                    }
                    $prevPos = \ftell($resource);
                }
                if($hkey === Constants::H_RECEIVED){
                    $receivedChain[] = Helpers::parseMimeEncoded(Helpers::removeCommentsFromString($hval));
                } else {
                    $headers[$hkey] = Helpers::parseMimeEncoded(Helpers::removeCommentsFromString($hval));
                }
            } else {
                \fseek($resource, $prevPos); break;
            }
            $prevPos = \ftell($resource);
        }
        $headers[Constants::H_CONTENT_TYPE] = isset($headers[Constants::H_CONTENT_TYPE]) ?
                            $headers[Constants::H_CONTENT_TYPE] : Constants::CT_TEXT_PLAIN;
        $message->setHeaders($headers)->setReceivedChain($receivedChain);
        // finished reading headers including a blank line delimiter
    }

    public static function parseSimpleBody(&$resource, Message $message)
    {
        $content = null;
        $startPos = \ftell($resource);
        $prevPos = $startPos;
        while($line = \fgets($resource)){
            if(Helpers::reachedNextMessage($line)){
                \fseek($resource, $prevPos);
                break;
            }
            $content .= $line;
            $prevPos = \ftell($resource);
        }
        $endPos = \ftell($resource);
        $contentType = $message->getContentType();
        $charset = $message->getContentType()->getCharset();
        $transferEnc = $message->hasHeader(Constants::H_TRANSFER_ENCODING) ?
                $message->getHeader(Constants::H_TRANSFER_ENCODING) : null;

        $message->addBodyOfCType(
            $contentType->getValue(),
            new MailBody($message->getFilepath(), $startPos, $endPos, $contentType, $charset, $transferEnc)
        );
    }

    public static function parseMultipartBody(&$resource, Message $message)
    {
        $boundary = $message->getContentType()->getBoundary();
        if(!isset($boundary)){ return; }
        while($line = \fgets($resource)){
            $line = \trim($line);
            if(Helpers::reachedMultipartBodyEnd($line, $boundary)){
                break;
            }
            if(Helpers::reachedNextSection($line, $boundary)){
                $prevPos = \ftell($resource);
                $line = \fgets($resource);
                if(false !== $line && !Helpers::reachedBodyDelimiter($line)){
                    \fseek($resource, $prevPos);
                    $sectHeaders = self::parseSectionHeaders($resource);
                    $body = self::parseSectionBody($resource, $boundary, $sectHeaders, $message);
                } else { break; }
            }
        }
    }

    public static function parseSectionHeaders(&$resource)
    {
        $prevPos = \ftell($resource);
        $headers = [];

        while($line = \fgets($resource)){
            $line = \trim($line, "\r\n");
            if(Helpers::reachedBodyDelimiter($line)){
                break;
            }
            if(Helpers::isHeaderStart($line)){
                list($hkey, $hval) = Helpers::splitHeaderStart($line);
                $prevPos = \ftell($resource);
                while($line = \fgets($resource)){
                    $line = \trim($line, "\r\n");
                    if(Helpers::isHeaderStart($line) || Helpers::reachedBodyDelimiter($line)){
                        \fseek($resource, $prevPos); break;
                    }
                    if(Helpers::isHeaderTail($line)){
                        $hval .= $line;
                    }
                    $prevPos = \ftell($resource);
                }
                $headers[$hkey] = $hval;
            } else {
                \fseek($resource, $prevPos); break;
            }
            $prevPos = \ftell($resource);
        }
        return $headers;
        // finished reading headers including a blank line delimiter
    }

    public static function parseSectionBody(&$resource, string $boundary, array $sectionHeaders, Message $message)
    {
        $contentType = isset($sectionHeaders[Constants::H_CONTENT_TYPE]) ?
                Headers\ContentType::parseValue($sectionHeaders[Constants::H_CONTENT_TYPE]) : null;
        
        $contentDisposition = isset($sectionHeaders[Constants::H_CONTENT_DISPOSITION]) ?
                Headers\ContentDisposition::parseValue($sectionHeaders[Constants::H_CONTENT_DISPOSITION])->getValue() : null;

        if(Helpers::sectionIsMainContent($contentType->getValue(), $contentDisposition)){
            self::parseMainContentSectionBody($resource, $boundary, $sectionHeaders, $message);
        } else if($contentType->getValue() === Constants::CT_MULTIPART_ALTER){
            $innerBoundary = $contentType->getBoundary();
            self::parseMultipartAlternativeSectionBody($resource, $boundary, $innerBoundary, $message);
        } else if($contentDisposition === Constants::CD_ATTACHMENT){
            self::parseAttachmentSectionBody($resource, $boundary, $sectionHeaders, $message);
        } else if($contentDisposition === Constants::CD_INLINE &&
                  !in_array($contentType->getValue(), [Constants::CT_TEXT_HTML, Constants::CT_TEXT_PLAIN])){
            self::parseInlineAttachmentSectionBody($resource, $boundary, $sectionHeaders, $message);
        }
    }

    public static function parseMainContentSectionBody(&$resource, string $boundary, array $sectionHeaders, Message $message)
    {
        $startPos = \ftell($resource);
        $prevPos = $startPos;
        while($line = \fgets($resource)){
            if(Helpers::reachedNextSection($line, $boundary)){
                \fseek($resource, $prevPos);
                break;
            }
            $prevPos = \ftell($resource);
        }
        $endPos = \ftell($resource);
        $contentType = isset($sectionHeaders[Constants::H_CONTENT_TYPE]) ?
                Headers\ContentType::parseValue($sectionHeaders[Constants::H_CONTENT_TYPE]) : null;
        $message->addBodyOfCType(
            $contentType->getValue(),
            new MailBody($message->getFilepath(), $startPos, $endPos, $contentType, $sectionHeaders[Constants::H_TRANSFER_ENCODING])
        );
    }

    public static function parseMultipartAlternativeSectionBody(&$resource, string $boundary, string $innerBoundary, Message $message)
    {
        $prevPos = \ftell($resource);
        while($line = \fgets($resource)){
            $line = \trim($line);
            if(Helpers::reachedNextSection($line, $innerBoundary)){
                $innerSectHeaders = self::parseSectionHeaders($resource);                
                if(isset($innerSectHeaders[Constants::H_CONTENT_TYPE])){
                    self::parseMainContentSectionBody($resource, $innerBoundary, $innerSectHeaders, $message);
                }
            }
            if(Helpers::reachedNextSection($line, $boundary)){
                \fseek($resource, $prevPos);
                break;
            }
            $prevPos = \ftell($resource);
        }
    }

    public static function getAttachmentBorders(&$resource, string $boundary, array $sectionHeaders, Message $message)
    {
        $contentType = Headers\ContentType::parseValue($sectionHeaders[Constants::H_CONTENT_TYPE]);
        $startPos = \ftell($resource);
        $prevPos = $startPos;
        while($line = \fgets($resource)){
            if(Helpers::reachedNextSection($line, $boundary)){
                \fseek($resource, $prevPos);
                break;
            }
            $prevPos = \ftell($resource);
        }
        $endPos = \ftell($resource);
        return [$startPos, $endPos];
    }

    public static function parseAttachmentSectionBody(&$resource, string $boundary, array $sectionHeaders, Message $message)
    {
        list($startPos, $endPos) = self::getAttachmentBorders($resource, $boundary, $sectionHeaders, $message);
        $contentType = Headers\ContentType::parseValue($sectionHeaders[Constants::H_CONTENT_TYPE]);
        $attachment = new CommonAttachment($message->getFilepath(), $startPos, $endPos, $contentType->getValue(), $sectionHeaders[Constants::H_TRANSFER_ENCODING], $contentType->getName());
        $message->addAttachment($attachment);
    }

    public static function parseInlineAttachmentSectionBody(&$resource, string $boundary, array $sectionHeaders, Message $message)
    {
        list($startPos, $endPos) = self::getAttachmentBorders($resource, $boundary, $sectionHeaders, $message);
        $contentType = Headers\ContentType::parseValue($sectionHeaders[Constants::H_CONTENT_TYPE]);
        $attachment = new InlineAttachment($message->getFilepath(), $startPos, $endPos, $contentType->getValue(), $sectionHeaders[Constants::H_TRANSFER_ENCODING], $sectionHeaders[Constants::H_CONTENT_ID]);
        $message->addInlineAttachment($attachment);
    }
}
