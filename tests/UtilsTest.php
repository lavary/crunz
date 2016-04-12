<?php

use Crunz\Utils;

class UtilsTest extends PHPUnit_Framework_TestCase {
    
    public function testSplitCamel()
    {
        $this->assertEquals('thirty seven',                              Utils::splitCamel('thirtySeven'));
        $this->assertEquals('one thousand and five hundred sixty three', Utils::splitCamel('oneThousandAndFiveHundredSixtyThree'));
        $this->assertEquals('seven hundred eighty five',                 Utils::splitCamel('sevenHundredEightyFive'));
    }

    public function testWordToNumber()
    {
        $this->assertEquals(65,   Utils::wordToNumber('sixty five'));
        $this->assertEquals(892,  Utils::wordToNumber('eight hundred ninety two'));
        $this->assertEquals(5438, Utils::wordToNumber('five thousand and four hundred thirty eight'));
    }

}