<?php

namespace YaroslavKlimuk\MboxReader\Tests;

use YaroslavKlimuk\MboxReader\{AbstractAttachment, Helpers, Constants};
use \PHPUnit\Framework\TestCase;

class AbstractAttachmentTest extends TestCase
{

    /**
     * @dataProvider providerRetrieveContent
     * @return void
     */
    public function testRetrieveContent(string $file, int $startPos, int $endPos, string $transferEncoding, string $resultFile)
    {
        $this->assertEquals(
            AbstractAttachment::retrieveContent($file, $startPos, $endPos, $transferEncoding), 
            file_get_contents($resultFile)
        );
    }

    public function providerRetrieveContent()
    {
        return [
            [__DIR__.'/mocks/blank_pdf_base64.txt', 3, 1545, 'base64', __DIR__.'/mocks/blank_pdf_decoded.pdf'],
        ];
    }

}

?>
