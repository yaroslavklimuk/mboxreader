<?php

namespace YaroslavKlimuk\MboxReader\Tests;

use YaroslavKlimuk\MboxReader\Headers\ContentType;
use \PHPUnit\Framework\TestCase;

class ContentTypeTest extends TestCase
{

     /**
     * @dataProvider providerParseValue
     * @return void
     */
    public function testParseValue(string $rawHeader, string $value, string $name = null, string $boundary = null)
    {
        $ctype = ContentType::parseValue($rawHeader);
        $this->assertEquals($ctype->getValue(), $value);
        $this->assertEquals($ctype->getName(), $name);
        $this->assertEquals($ctype->getBoundary(), $boundary);
    }

    public function providerParseValue()
    {
        return [
            ['multipart/mixed;	boundary="----==--bound.266749.myt1-cd60b8ae9bb9"', 'multipart/mixed', null, '----==--bound.266749.myt1-cd60b8ae9bb9'],
            ['application/pdf; name="=?KOI8-R?b?8NXT1M/KIMbByswucGRm?="', 'application/pdf', 'Пустой файл.pdf', null],
            ['application/pdf; name="=?UTF-8?b?0J/Rg9GB0YLQvtC5INGE0LDQudC7LnBkZg==?="', 'application/pdf', 'Пустой файл.pdf', null],
            ['application/pdf; name=blank.pdf', 'application/pdf', 'blank.pdf', null]
        ];
    }
}
