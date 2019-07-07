<?php

namespace YaroslavKlimuk\MboxReader\Tests;

use YaroslavKlimuk\MboxReader\Headers\ContentDisposition;
use \PHPUnit\Framework\TestCase;

class ContentDispositionTest extends TestCase
{

     /**
     * @dataProvider providerParseValue
     * @return void
     */
    public function testParseValue(string $rawHeader, string $value, string $filename = null)
    {
        $cdisp = ContentDisposition::parseValue($rawHeader);
        $this->assertEquals($cdisp->getValue(), $value);
        $this->assertEquals($cdisp->getFilename(), $filename);
    }

    public function providerParseValue()
    {
        return [
            ['attachment; filename="blank.pdf"', 'attachment', 'blank.pdf'],
            ['inline', 'inline', null]
        ];
    }
}
