<?php

namespace Silvioq\Component\Test\CuitValidator\Constraints;

use Silvioq\Component\CuitValidator\Validator\Constraints\Cuit;
use Silvioq\Component\CuitValidator\Validator\Constraints\CuitValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Silvioq\Component\CuitValidator\Inspector\CuitInspector;

class CuitValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new CuitValidator();
    }

    public function testNullIsValid()
    {
        $this->validator->validate(null,new Cuit());
        $this->assertNoViolation();
    }

    /**
     * @dataProvider getInvalidCuits
     */
    public function testInvalidCuits($cuit,$message)
    {
        $this->validator->validate($cuit,new Cuit());
        $this->buildViolation($message)
            ->assertRaised()
            ;
    }

    /**
     * @dataProvider getValidCuits
     */
    public function testValidCuits($cuit)
    {
        $this->validator->validate($cuit,new Cuit());
        $this->assertNoViolation();
    }

    public function getInvalidCuits()
    {
        $cuit = new Cuit();
        return [
            [ '2', $cuit->incorrectLenMessage ],
            [ '20-1111111--2', $cuit->incorrectLenMessage ],
            [ '20-11111111-5', $cuit->invalidMessage ],
            [ '30-11111111-2', $cuit->invalidMessage ],
            [ '27-11111111-2', $cuit->invalidMessage ],
            [ '23-11111113-9', $cuit->invalidMessage ],
        ];
    }

    public function getValidCuits()
    {
        return [
            [ '20-11111111-2' ],
            [ '20-11111113-9' ],
         ];
    }

}
// vim:sw=4 ts=4 sts=4 et
