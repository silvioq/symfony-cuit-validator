<?php

namespace Silvioq\Component\Test\CuitValidator;

use PHPUnit\Framework\TestCase;
use Silvioq\Component\CuitValidator\Inspector\CuitInspector;
use Psr\Http\Message\ResponseInterface;


class CuitInspectorTest extends TestCase
{
    public function testGetCuit()
    {
        $clientMock  = $this->getMockBuilder(\GuzzleHttp\Client::class )
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock = $this->getMockBuilder( ResponseInterface::class )
            ->getMock()
            ;

        $mockData = '{"success":true,"data":{"dummy":"data"}}';

        $clientMock->expects($this->once())
            ->method('request')
            ->with( 'GET', $this->equalTo(CuitInspector::URL_AFIP. 20123456781))
            ->will($this->returnValue( $responseMock))
            ;

        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(200))
            ;
        
        $responseMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($mockData))
            ;

        $service = new CuitInspector( $clientMock );
        $data = $service->getData( 20123456781 );
        $this->assertEquals( json_decode( $mockData )->data, $data );
    }

    public function testGetInvalidCuit()
    {
        $clientMock  = $this->getMockBuilder(\GuzzleHttp\Client::class )
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock = $this->getMockBuilder( ResponseInterface::class )
            ->getMock()
            ;

        $mockData = '{"success":false,"error":{"mensaje":"MESSAGE FROM AFIP"}}';

        $clientMock->expects($this->once())
            ->method('request')
            ->with( 'GET', $this->equalTo(CuitInspector::URL_AFIP. 20123456781))
            ->will($this->returnValue($responseMock))
            ;

        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(200))
            ;
        
        $responseMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($mockData))
            ;

        $service = new CuitInspector( $clientMock );
        try
        {
            $data = $service->getData( 20123456781 );
            $this->assertFalse( true, "Logic error. Test never must enter here" );
        }
        catch( \Silvioq\Component\CuitValidator\Inspector\InvalidCuitException $e )
        {
            $this->assertEquals( $e->getMessage(), 'MESSAGE FROM AFIP' );
        }
    }

    public function testConnectionError()
    {
        $clientMock  = $this->getMockBuilder(\GuzzleHttp\Client::class )
            ->disableOriginalConstructor()
            ->getMock();

        $requestMock = $this->getMockBuilder(\Psr\Http\Message\RequestInterface::class)
              ->getMock()
              ;

        $clientMock->expects($this->once())
            ->method('request')
            ->with( 'GET', $this->equalTo(CuitInspector::URL_AFIP. 20123456781))
            ->will($this->throwException( new \GuzzleHttp\Exception\ServerException("msg", $requestMock) ) )
            ;
        $service = new CuitInspector( $clientMock );
        
        $this->expectException( \Silvioq\Component\CuitValidator\Inspector\CuitInspectionException::class );
        $service->getData( 20123456781 );
    }

    public function testSimpleData()
    {
        $clientMock  = $this->getMockBuilder(\GuzzleHttp\Client::class )
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock = $this->getMockBuilder( ResponseInterface::class )
            ->getMock()
            ;

        /**
         * @var string
         * Real Data obtained from cuit 30527508165
         */
        $mockData = '{"success":true,"data":{"idPersona":30527508165,"tipoPersona":"JURIDICA","tipoClave":"CUIT","estadoClave":"ACTIVO","nombre":"PROVINCIA SEGUROS S A","domicilioFiscal":{"direccion":"PELLEGRINI CARLOS 71","codPostal":"1009","idProvincia":0},"idDependencia":20,"mesCierre":6,"fechaInscripcion":"1988-03-16","fechaContratoSocial":"1925-02-18","impuestos":[10,25,30,103,217,218,301,353,365,767],"actividades":[651220],"caracterizaciones":[62,68,72,255,337]}}';

        $clientMock->expects($this->once())
            ->method('request')
            ->with( 'GET', $this->equalTo(CuitInspector::URL_AFIP. 30527508165))
            ->will($this->returnValue($responseMock))
            ;

        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(200))
            ;

        $responseMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($mockData))
            ;

        $service = new CuitInspector( $clientMock );

        $data = $service->getSimpleData( 30527508165 );
        $this->assertEquals( 'JURIDICA', $data['tipoPersona'] );
        $this->assertEquals( 'PROVINCIA SEGUROS S A', $data['nombre'] );
        $this->assertEquals( 'PELLEGRINI CARLOS 71', $data['domicilioCalle'] );
        $this->assertEquals( '1009', $data['domicilioCP'] );
        $this->assertEquals( 'ACTIVO', $data['estado'] );
    }

    public function testSanitize()
    {
        $this->assertEquals( '20123456789', CuitInspector::sanitizeCuit( '20-12345678 9 ') );
    }

}
// vim:sw=4 ts=4 sts=4 et
