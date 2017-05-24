<?php

namespace Silvioq\Component\Test\CuitValidator\Constraints;

use Silvioq\Component\CuitValidator\Validator\Constraints\Cuit;
use Silvioq\Component\CuitValidator\Validator\Constraints\CuitValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Silvioq\Component\CuitValidator\Inspector\CuitInspector;

class CuitValidatorNetworkFailTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        $clientMock  = $this->getMockBuilder(\GuzzleHttp\Client::class )
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock = $this->getMockBuilder( \Psr\Http\Message\ResponseInterface::class )
            ->getMock()
            ;
        $requestMock = $this->getMockBuilder(\Psr\Http\Message\RequestInterface::class)
              ->getMock()
              ;

        $clientMock->expects($this->any())
            ->method('request')
            ->with( 'GET', $this->equalTo(CuitInspector::URL_AFIP. 23123456785))
            ->will($this->throwException( new \GuzzleHttp\Exception\ServerException("msg", $requestMock) ) )
            ;

        return new CuitValidator( new CuitInspector( $clientMock ) );
    }

    public function testExceptionOnNetworkFail()
    {
        $this->expectException( \Silvioq\Component\CuitValidator\Inspector\CuitInspectionException::class );
        $this->validator->validate('23-12345678-5', new Cuit( [ 'checkState' => true ] ));
        $this->assertNoViolation();
    }

    public function testNoNetworkConnection()
    {
        $this->validator->validate('23-12345678-5', new Cuit( [ 'checkState' => false ] ));
        $this->assertNoViolation();
    }

    public function testExceptionOnNetworkFailWithoutThrow()
    {
        $cuit = new Cuit();
        $this->validator->validate('23-12345678-5', new Cuit( [ 'checkState' => true, 'throwOnNetworkError' => false ] ));
        $this->buildViolation($cuit->serviceNotActiveMessage)
            ->assertRaised()
            ;
    }
}
// vim:sw=4 ts=4 sts=4 et
