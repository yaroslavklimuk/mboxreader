<?php

namespace YaroslavKlimuk\MboxReader;

/**
 * Description of Attachment
 *
 * @author yaklimuk
 */
class InlineAttachment extends AbstractAttachment
{
    protected $contentId;

    public function __construct(string $file, int $startPos, int $endPos, string $mimetype, string $transEncoding, string $contentId)
    {
        parent::__construct($file, $startPos, $endPos, $mimetype, $transEncoding);
        $this->contentId = $contentId;
    }

    public function getContentId()
    {
        return $this->contentId;
    }
}
