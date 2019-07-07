<?php

namespace YaroslavKlimuk\MboxReader;

/**
 * Description of MboxReader
 *
 * @author yaklimuk
 */
class MboxReader implements MboxReaderInterface
{
    protected $file = null;

    protected $fromDate = null;
    protected $beforeDate = null;
    protected $subjects = [];
    protected $subjectRegexes = [];
    protected $senders = [];
    protected $senderRegexes = [];
    protected $attachmentNames = [];
    protected $attachmentNameRegexes = [];

    public function __construct(string $filePath)
    {
        $this->setFile($filePath);
    }

    public function setFile(string $filePath)
    {
        if (!\is_file($filePath)) {
            throw new Exception('Can not open the file.');
        }
        $this->file = $filePath;
    }

    public function get()
    {
        $filteredMessages = [];
        $messages = Message::parseMessages($this->file);
        while(count($messages) > 0){
            $message = array_pop($messages);
            if($this->filterByHeaders($message) && $this->filterByAttachments($message)){
                $filteredMessages[] = $message;
            }
        }
        return $filteredMessages;
    }

    public function withFromDate(string $date = null)
    {
        $this->fromDate = $date;
        return $this;
    }

    public function withBeforeDate(string $date = null)
    {
        $this->beforeDate = $date;
        return $this;
    }

    public function withSubject(string $subj = null)
    {
        $this->subjects[] = $subj;
        return $this;
    }

    public function withSubjectRegex(string $subjRgx)
    {
        $this->subjectRegexes[] = $subjRgx;
        return $this;
    }

    public function withSender(string $sender)
    {
        $this->senders[] = $sender;
        return $this;
    }

    public function withSenderRegex(string $senderRgx)
    {
        $this->senderRegexes[] = $senderRgx;
        return $this;
    }

    public function withAttachmentName(string $attachmentName)
    {
        $this->attachmentNames[] = $attachmentName;
        return $this;
    }

    public function withAttachmentNameRegex(string $attachmentRegex)
    {
        $this->attachmentNameRegexes[] = $attachmentRegex;
        return $this;
    }

    protected function filterByHeaders(Message $message)
    {
        if(isset($this->fromDate) && !self::filterByFromDate($this->fromDate, $message->getHeader(Constants::H_DATE))){
            return false;
        }
        if(isset($this->beforeDate) && !self::filterByBeforeDate($this->beforeDate, $message->getHeader(Constants::H_DATE))){
            return false;
        }
        if(count($this->senders) > 0 && !self::filterBySenders($this->senders, $message->getHeader(Constants::H_FROM))){
            return false;
        }
        if(count($this->senderRegexes) > 0 && !self::filterBySenderRegexes($this->senderRegexes, $message->getHeader(Constants::H_FROM))){
            return false;
        }
        if(count($this->subjects) > 0 && !self::filterBySubjects($this->subjects, $message->getHeader(Constants::H_SUBJECT))){
            return false;
        }
        if(count($this->subjectRegexes) > 0 && !self::filterBySubjectRegexes($this->subjectRegexes, $message->getHeader(Constants::H_SUBJECT))){
            return false;
        }
        return true;
    }

    public static function filterByFromDate(string $filterDate, string $messageDate)
    {
        return \strtotime(\substr($messageDate, 5, \strlen($messageDate) - 5)) >= \strtotime($filterDate);
    }

    public static function filterByBeforeDate(string $filterDate, string $messageDate)
    {
        return \strtotime(\substr($messageDate, 5, \strlen($messageDate) - 5)) <= \strtotime($filterDate);
    }

    public static function filterBySubjects(array $subjects, string $subject)
    {
        return in_array($subject, $subjects);
    }

    public static function filterBySubjectRegexes(array $subjectRgxs, string $subject)
    {
        foreach($subjectRgxs as $subjRgx){
            if(preg_match($subjRgx, $subject)){
                return true;
            }
        }
        return false;
    }

    public static function filterBySenders(array $senders, string $sender)
    {
        return in_array($sender, $senders);
    }

    public static function filterBySenderRegexes(array $senderRgxs, string $sender)
    {
        foreach($senderRgxs as $senderRgx){
            if(preg_match($senderRgx, $sender)){
                return true;
            }
        }
        return false;
    }

    protected function filterByAttachments(Message $message)
    {
        if(count($this->attachmentNames) > 0 && !self::filterByAttachmentNames($this->attachmentNames, $message)){
            return false;
        }
        if(count($this->attachmentNameRegexes) > 0 && !self::filterByAttachmentNameRegexes($this->attachmentNameRegexes, $message)){
            return false;
        }
        return true;
    }

    public static function filterByAttachmentNames(array $attachmentNames, Message $message)
    {
        foreach($attachmentNames as $attachmentName){
            if(count($message->getAttachmentsWithName($attachmentName)) > 0){
                return true;
            }
        }
        return false;
    }

    public static function filterByAttachmentNameRegexes(array $attachmentNameRgxs, Message $message)
    {
        foreach($attachmentNameRgxs as $attachmentNameRgx){
            if(count($message->getAttachmentsWithNameRegex($attachmentNameRgx)) > 0){
                return true;
            }
        }
        return false;
    }

    public function getFilters()
    {
        return [
            'fromDate' => $this->fromDate,
            'beforeDate' => $this->beforeDate,
            'subjects' => $this->subjects,
            'subjectRegexes' => $this->subjectRegexes,
            'senders' => $this->senders,
            'senderRegexes' => $this->senderRegexes,
            'attachmentNames' => $this->attachmentNames,
            'attachmentNameRegexes' => $this->attachmentNameRegexes
        ];
    }

    public function resetFilters()
    {
        $this->beforeDate = null;
        $this->fromDate = null;
        $this->subjects = [];
        $this->subjectRegexes = [];
        $this->senders = [];
        $this->senderRegexes = [];
        $this->attachmentNames = [];
        $this->attachmentNameRegexes = [];
    }

}
