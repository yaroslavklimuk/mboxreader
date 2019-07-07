<?php

namespace YaroslavKlimuk\MboxReader\Tests;

use YaroslavKlimuk\MboxReader\{MboxReader, Message, Helpers, Constants};
use \PHPUnit\Framework\TestCase;

class MboxReaderTest extends TestCase
{
   /**
     *
     * @dataProvider providerGetWithFilters
     * @return void
     */
    public function testGetWithFilters(string $file, array $dates, array $subjects, 
        array $subjectRegexes, array $senders, array $senderRegexes, 
        array $attachmentNames, array $attachmentNameRegexes, int $count)
    {
        $mboxreader = new MboxReader($file);
        $mboxreader->withFromDate($dates[0])->withBeforeDate($dates[1]);
        foreach($subjects as $subject){
            $mboxreader->withSubject($subject);
        }
        foreach($subjectRegexes as $subjectRgx){
            $mboxreader->withSubjectRegex($subjectRgx);
        }
        foreach($senders as $sender){
            $mboxreader->withSender($sender);
        }
        foreach($senderRegexes as $senderRgx){
            $mboxreader->withSenderRegex($senderRgx);
        }
        foreach($attachmentNames as $attachmentName){
            $mboxreader->withAttachmentName($attachmentName);
        }
        foreach($attachmentNameRegexes as $attachmentNameRgx){
            $mboxreader->withAttachmentNameRegex($attachmentNameRgx);
        }

        $messages = $mboxreader->get();

        $this->assertEquals(count($messages), $count);
        foreach($messages as $message){
            $msgSender = $message->getHeader(Constants::H_FROM);
            $msgSubject = $message->getHeader(Constants::H_SUBJECT);
            $msgDate = $message->getHeader(Constants::H_DATE);
            if(count($senders) > 0){
                $this->assertTrue(in_array($msgSender, $senders));
            }
            if(count($subjects) > 0){
                $this->assertTrue(in_array($msgSubject, $subjects));
            }
            if(count($senderRegexes) > 0){
                $this->assertTrue(self::stringMatchesAtLeastOneRegex($msgSender, $senderRegexes));
            }
            if(count($subjectRegexes) > 0){
                $this->assertTrue(self::stringMatchesAtLeastOneRegex($msgSubject, $subjectRegexes));
            }
            if(count($attachmentNames) > 0){
                $msgAttachmentNames = array_map(function($att){ return $att->getName(); }, $message->getAllAttachments());
                $this->assertTrue(self::atLeastOneStringInArray($msgAttachmentNames, $attachmentNames));
            }
            if(count($attachmentNameRegexes) > 0){
                $msgAttachmentNames = array_map(function($att){ return $att->getName(); }, $message->getAllAttachments());
                $this->assertTrue(self::atLeastOneStringMatchesAtLeastOneRegex($msgAttachmentNames, $attachmentNameRegexes));
            }
        }
    }

    public function providerGetWithFilters()
    {
        return [
            [
                __DIR__.'/mocks/3_different_messages.txt',
                ['27.03.2019 19:13:00', '29.03.2019 23:34:00'],
                ['Checking', 'Просто веб-страница'],
                [],
                [],
                ['/.*hfghdgfh.*/'],
                [],
                [],
                1
            ],
            [
                __DIR__.'/mocks/3_different_messages.txt',
                ['27.03.2019 19:13:00', null],
                [],
                [],
                [],
                [],
                ['blank.pdf'],
                ['/.*Пустой.*/'],
                1
            ]
        ];
    }

    public static function atLeastOneStringMatchesAtLeastOneRegex(array $needles, array $regexes)
    {
        foreach($needles as $needle){
            foreach($regexes as $regex){
                if(\preg_match($regex, $needle)){
                    return true;
                }
            }
            
        }
        return false;
    }

    public static function atLeastOneStringInArray(array $needles, array $heap)
    {
        foreach($needles as $needle){
            if(in_array($needle, $heap)){
                return true;
            }
        }
        return false;
    }

    public static function stringMatchesAtLeastOneRegex(string $str, array $regexes)
    {
        foreach($regexes as $regex){
            if(\preg_match($regex, $str)){
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @dataProvider providerFilterByFromDate
     * @return void
     */
    public function testFilterByFromDate(string $filterDate, string $messageDate, bool $result)
    {
        $this->assertEquals(MboxReader::filterByFromDate($filterDate, $messageDate), $result);
    }

    public function providerFilterByFromDate()
    {
        return [
            ['2019-04-29', 'Tue, 30 Apr 2019 08:56:01 +0300', true],
            ['2019-04-28', 'Thu, 28 Mar 2019 10:27:43 +0300', false]
        ];
    }

    /**
     *
     * @dataProvider providerFilterByBeforeDate
     * @return void
     */
    public function testFilterByBeforeDate(string $filterDate, string $messageDate, bool $result)
    {
        $this->assertEquals(MboxReader::filterByBeforeDate($filterDate, $messageDate), $result);
    }

    public function providerFilterByBeforeDate()
    {
        return [
            ['2019-05-05', 'Tue, 30 Apr 2019 08:56:01 +0300', true],
            ['2019-03-22', 'Thu, 28 Mar 2019 10:27:43 +0300', false]
        ];
    }

    /**
     *
     * @dataProvider providerFilterBySenders
     * @return void
     */
    public function testFilterBySenders(array $senders, string $messageFrom, bool $result)
    {
        $this->assertEquals(MboxReader::filterBySenders($senders, $messageFrom), $result);
    }

    public function providerFilterBySenders()
    {
        return [
            [['hfghdgfh@kjhdkfg.com Hurufirrg'], 'hfghdgfh@kjhdkfg.com Hurufirrg', true],
            [['hfghdgfh@kjhdkfg.com'], 'hfghdgfh@kjhdkfg.com Отправитель', false]
        ];
    }

    /**
     *
     * @dataProvider providerFilterBySenderRegexes
     * @return void
     */
    public function testFilterBySenderRegexes(array $senderRegexes, string $messageFrom, bool $result)
    {
        $this->assertEquals(MboxReader::filterBySenderRegexes($senderRegexes, $messageFrom), $result);
    }

    public function providerFilterBySenderRegexes()
    {
        return [
            [['/.*hfghdgfh.*/'], 'hfghdgfh@kjhdkfg.com Hurufirrg', true],
            [['/.*Отправитель.*/'], 'hfghdgfh@kjhdkfg.com Отправитель', true]
        ];
    }
}
