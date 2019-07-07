<?php

namespace YaroslavKlimuk\MboxReader;

/**
 * Description of Attachment
 *
 * @author yaklimuk
 */
class CommonAttachment extends AbstractAttachment
{
    protected $name;

    public function __construct(string $file, int $startPos, int $endPos, string $mimetype, string $transEncoding, string $name)
    {
        parent::__construct($file, $startPos, $endPos, $mimetype, $transEncoding);
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
