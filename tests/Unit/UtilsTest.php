<?php

namespace Crunz\Tests\Unit;

use Crunz\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function testWordToNumber()
    {
        $this->assertEquals(65, Utils::wordToNumber('sixty five'));
        $this->assertEquals(892, Utils::wordToNumber('eight hundred ninety two'));
        $this->assertEquals(5438, Utils::wordToNumber('five thousand and four hundred thirty eight'));
    }
}
