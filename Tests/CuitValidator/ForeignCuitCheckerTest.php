<?php

namespace Silvioq\Component\Test\CuitValidator;

use PHPUnit\Framework\TestCase;
use Silvioq\Component\CuitValidator\Validator\ForeignCuitChecker;
use Psr\Http\Message\ResponseInterface;


class ForeignCuitCheckerTest extends TestCase
{
    public function testForeignCuits()
    {
        $checker = new ForeignCuitChecker();
        $this->assertTrue($checker->isForeign('50000009986'));
        $this->assertTrue($checker->isForeign('55000006784'));
        $this->assertTrue($checker->isForeign('51600000016'));
        $this->assertFalse($checker->isForeign('51600000010'));
        $this->assertFalse($checker->isForeign('22333444556'));
        $this->assertFalse($checker->isForeign(''));

        $checker = new ForeignCuitChecker(['a','b','c']);
        $this->assertTrue($checker->isForeign('a'));
        $this->assertFalse($checker->isForeign('d'));
    }
}
// vim:sw=4 ts=4 sts=4 et
