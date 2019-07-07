<?php

namespace YaroslavKlimuk\MboxReader\Tests;

use YaroslavKlimuk\MboxReader\{Message, Constants};
use \PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{

    /**
     * @dataProvider providerParseMessages
     * @return void
     */
    public function testParseMessages(string $filename, array $subjects)
    {
        $messages = Message::parseMessages($filename);
        $parsedSubjs = array_map(function($msg){ return $msg->getHeader(Constants::H_SUBJECT); }, $messages);
        sort($subjects);
        sort($parsedSubjs);
        $this->assertEquals($parsedSubjs, $subjects);
    }

    public function providerParseMessages()
    {
        return [
            [__DIR__.'/mocks/1_html_7bit.txt', ['Checking']],
            [__DIR__.'/mocks/1_html_quoted_printables.txt', ['Просто веб-страница']],
            [__DIR__.'/mocks/1_multipart_html_and_attachment.txt', ['ATTACHMENT']],
            [__DIR__.'/mocks/1_multipart_related_alternative.txt', ['Сообщение с альтернативным содержимым']],
            [__DIR__.'/mocks/3_different_messages.txt', ['Checking', 'Просто веб-страница', 'Сообщение с альтернативным содержимым']]
        ];
    }

}
