<?php

namespace Silvioq\Component\Test\CuitValidator\Constraints;

use Silvioq\Component\CuitValidator\Validator\Constraints\Cuit;
use Silvioq\Component\CuitValidator\Validator\Constraints\CuitValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Silvioq\Component\CuitValidator\Inspector\CuitInspector;

class CuitValidatorActiveTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        $clientMock  = $this->getMockBuilder(\GuzzleHttp\Client::class )
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock = $this->getMockBuilder( \Psr\Http\Message\ResponseInterface::class )
            ->getMock()
            ;

        /**
         * @var string
         * Real Data obtained from cuit 30527508165
         */
        $mockData1 = '{"success":true,"data":{"idPersona":30527508165,"tipoPersona":"JURIDICA","tipoClave":"CUIT","estadoClave":"ACTIVO","nombre":"PROVINCIA SEGUROS S A","domicilioFiscal":{"direccion":"PELLEGRINI CARLOS 71","codPostal":"1009","idProvincia":0},"idDependencia":20,"mesCierre":6,"fechaInscripcion":"1988-03-16","fechaContratoSocial":"1925-02-18","impuestos":[10,25,30,103,217,218,301,353,365,767],"actividades":[651220],"caracterizaciones":[62,68,72,255,337]}}';
        $mockData2 = '{"success":false,"error":{ "tipoError":"client","mensaje":"No existe persona con ese Id"}}';
        $mockData3 = '{"success":true,"data":{"idPersona":30527508165,"tipoPersona":"JURIDICA","tipoClave":"CUIT","estadoClave":"BAJA","nombre":"PROVINCIA SEGUROS S A","domicilioFiscal":{"direccion":"PELLEGRINI CARLOS 71","codPostal":"1009","idProvincia":0},"idDependencia":20,"mesCierre":6,"fechaInscripcion":"1988-03-16","fechaContratoSocial":"1925-02-18","impuestos":[10,25,30,103,217,218,301,353,365,767],"actividades":[651220],"caracterizaciones":[62,68,72,255,337]}}';

        $clientMock->expects($this->any())
            ->method('request')
            ->with('GET', $this->equalTo(CuitInspector::URL_AFIP. 30527508165))
            ->will($this->returnValue($responseMock))
            ;

        $responseMock->expects($this->exactly(3))
            ->method('getStatusCode')
            ->will($this->returnValue(200))
            ;

        $responseMock->expects($this->exactly(3))
            ->method('getBody')
            ->will($this->onConsecutiveCalls($mockData1,$mockData2,$mockData3))
            ;

        return new CuitValidator( new CuitInspector( $clientMock ) );
    }

    public function testActiveAndInactiveCuit()
    {
        $cuit = new Cuit();
        $this->validator->validate('30-52750816-5', new Cuit( [ 'checkState' => true ] ));
        $this->assertNoViolation();

        $this->validator->validate('30-52750816-5', new Cuit( [ 'checkState' => true ] ));

        $violation = $this->buildViolation($cuit->invalidMessage)
            ;
        $violation
            ->assertRaised()
            ;

        $this->validator->validate('30-52750816-5', new Cuit( [ 'checkState' => true ] ));
        $violation
            ->buildNextViolation($cuit->cuitNotActiveMessage)
            ->assertRaised()
            ;

    }
}
// vim:sw=4 ts=4 sts=4 et
