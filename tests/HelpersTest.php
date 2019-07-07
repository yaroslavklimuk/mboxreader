<?php

namespace YaroslavKlimuk\MboxReader\Tests;

use YaroslavKlimuk\MboxReader\{Helpers, Constants};
use \PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{

    /**
     * @dataProvider providerReachedNextMessage
     * @return void
     */
    public function testReachedNextMessage(string $line, bool $res)
    {
        $this->assertEquals(Helpers::reachedNextMessage($line), $res);
    }

    public function providerReachedNextMessage()
    {
        return [
            ['From: hfghdgfh@kjhdkfg.com', false],
            ['From hfghdgfh@kjhdkfg.com  Wed Mar 27 15:37:43 2019', true]
        ];
    }

    /**
     * @dataProvider providerReachedNextSection
     * @return void
     */
    public function testReachedNextSection(string $line, string $boundary, bool $res)
    {
        $this->assertEquals(Helpers::reachedNextSection($line, $boundary), $res);
    }

    public function providerReachedNextSection()
    {
        return [
            ['------==--bound.266749.myt1-cd60b8ae9bb9.qloud-c.yandex.net', '----==--bound.266749.myt1-cd60b8ae9bb9.qloud-c.yandex.net', true],
            ['------==--bound.266749.myt1-cd60b8ae9bb9.qloud-c.yandex.net', '----==--bound.266749.myt1-111111111.qloud-c.yandex.net', false]
        ];
    }

    /**
     * @dataProvider providerReachedMultipartBodyEnd
     * @return void
     */
    public function testReachedMultipartBodyEnd(string $line, string $boundary, bool $res)
    {
        $this->assertEquals(Helpers::reachedMultipartBodyEnd($line, $boundary), $res);
    }

    public function providerReachedMultipartBodyEnd()
    {
        return [
            ['------==--bound.266749.myt1-cd60b8ae9bb9.qloud-c.yandex.net', '----==--bound.266749.myt1-cd60b8ae9bb9.qloud-c.yandex.net', false],
            ['------==--bound.266749.myt1-cd60b8ae9bb9.qloud-c.yandex.net--', '----==--bound.266749.myt1-cd60b8ae9bb9.qloud-c.yandex.net', true]
        ];
    }

    /**
     * @dataProvider providerSplitHeaderStart
     * @return void
     */
    public function testSplitHeaderStart(string $line, string $key, string $value = null)
    {
        list($hkey, $hval) = Helpers::splitHeaderStart($line);
        $this->assertEquals($hkey, $key);
        $this->assertEquals($hval, $value);
    }

    public function providerSplitHeaderStart()
    {
        return [
            ['Received: by ghgj4h5-964eae3a5b05.test.test.net with HTTP;', 'Received', 'by ghgj4h5-964eae3a5b05.test.test.net with HTTP;'],
            ['someHeader: header header', 'someHeader', 'header header']
        ];
    }

    /**
     * @dataProvider providerIsHeaderTail
     * @return void
     */
    public function testIsHeaderTail(string $line)
    {
        $this->assertTrue(Helpers::isHeaderTail($line));
    }

    public function providerIsHeaderTail()
    {
        return [
            ['	boundary="----==--bound.266749.myt1-cd60b8ae9bb9.qloud-c.yandex.net"']
        ];
    }

    /**
     * @dataProvider providerSectionIsMainContent
     * @return void
     */
    public function testSectionIsMainContent(string $ctype = null, string $cdisp = null, bool $resp)
    {
        $this->assertEquals(Helpers::sectionIsMainContent($ctype, $cdisp), $resp);
    }

    public function providerSectionIsMainContent()
    {
        return [
            [Constants::CT_TEXT_HTML, Constants::CD_ATTACHMENT, false],
            [Constants::CT_TEXT_HTML, Constants::CD_INLINE, true],
            [null, null, true],
            [Constants::CT_TEXT_PLAIN, Constants::CD_ATTACHMENT, false],
            [Constants::CT_MULTIPART_ALTER, null, false]
        ];
    }

    /**
     * @dataProvider providerDecodeFromTransferEncoding
     * @return void
     */
    public function testDecodeFromTransferEncoding(string $encoding, string $encoded, string $decoded)
    {
        $this->assertEquals(Helpers::decodeFromTransferEncoding($encoding, $encoded), $decoded);
    }

    public function providerDecodeFromTransferEncoding()
    {
        return [
            [Constants::TR_ENC_BASE64, '0KHQvtC+0LHRidC10L3QuNC1INGBINCw0LvRjNGC0LXRgNC90LDRgtC40LLQvdGL0Lw=', 'Сообщение с альтернативным'],
            [Constants::TR_ENC_QUOTED_PRINTABLE, '=D0=A1=D0=BE=D0=BE=D0=B1=D1=89=D0=B5=D0=BD=D0=B8=D0=B5 =D1=81 =D0=B0=D0=BB=D1=8C=D1=82=D0=B5=D1=80=D0=BD=D0=B0=D1=82=D0=B8=D0=B2=D0=BD=D1=8B=D0=BC', 'Сообщение с альтернативным'],
        ];
    }

    
}
