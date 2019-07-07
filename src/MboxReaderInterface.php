<?php

namespace YaroslavKlimuk\MboxReader;

/**
 *
 * @author yaklimuk
 */
interface MboxReaderInterface
{
    public function get();
    public function withFromDate(string $date = null);
    public function withBeforeDate(string $date = null);
    public function withSubject(string $subj);
    public function withSubjectRegex(string $subjRgx);
    public function withSender(string $sender);
    public function withSenderRegex(string $senderRegex);
    public function withAttachmentName(string $attachmentName);
    public function withAttachmentNameRegex(string $attachmentRegex);
}
