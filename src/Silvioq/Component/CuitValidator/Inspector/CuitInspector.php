<?php

namespace Silvioq\Component\CuitValidator\Inspector;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * CUIT / CUIL inspector
 */
class CuitInspector
{
    const  URL_AFIP = 'https://soa.afip.gob.ar/sr-padron/v2/persona/';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface|null $client
     */
    public function __construct( $client = null )
    {
        if( null === $client )
            $this->client = new Client();
        else
            $this->client = $client;
    }
    
    /**
     * Sanitize CUIT / CUIL, cleaning invalid characters
     * @param string $cuit
     * @return string
     */
    static public function sanitizeCuit( $cuit )
    {
        return preg_replace( '/[^0123456789]/', '', $cuit );
    }

    /**
     * Get raw data from AFIP
     * @param string $cuit CUIT/CUIL number
     * @return object
     * @throws CuitInspectionException
     */
    public  function  getData( $cuit )
    {
        try
        {
            $data = $this->getAFIPData( $cuit );
            if( $data === false )
                throw new CuitInspectionException( "Can't access to AFIP Services" );
        }
        catch ( \GuzzleHttp\Exception\TransferException $te )
        {
            throw new CuitInspectionException( sprintf( 'Error in connection: %s', $te->getMessage() ) );
        }

        if( !isset( $data->success ) )
            throw new CuitInspectionException( 'Invalid data received' );

        if( !$data->success && isset( $data->error ) && isset( $data->error->mensaje ) )
            throw new InvalidCuitException( $data->error->mensaje );

        if( !$data->success )
            throw new CuitInspectionException( 'Invalid data received' );

        return  $data->data;
    }

    /**
     * Get simplified data from AFIP
     * @param string $cuit CUIT/CUIL number
     * @return object
     */
    public function getSimpleData( $cuit )
    {
        $data = $this->getData( $cuit );
        if( false === $data ) return false;

        return [
            'tipoPersona'         => $data->tipoPersona,
            'nombre'              => $data->nombre,
            'documento'           => isset( $data->numeroDocumento ) ?  $data->numeroDocumento : '',
            'domicilioCalle'      => isset( $data->domicilioFiscal ) &&
                    isset( $data->domicilioFiscal->direccion ) ?
                    $data->domicilioFiscal->direccion : '',
            'domicilioLocalidad'  => isset( $data->domicilioFiscal ) &&
                    isset( $data->domicilioFiscal->localidad ) ?
                    $data->domicilioFiscal->localidad : '',
            'domicilioCP'         => isset( $data->domicilioFiscal ) &&
                    isset( $data->domicilioFiscal->codPostal ) ?
                    $data->domicilioFiscal->codPostal : '',
            'domicilioProv'       => isset( $data->domicilioFiscal ) ? $data->domicilioFiscal->idProvincia : '',
            'tipoClave'           => $data->tipoClave,
            'estado'              => $data->estadoClave,
        ]
        ;
    }

    private function getAFIPData($cuit)
    {
        $res = $this->client->request( 'GET', self::URL_AFIP . self::sanitizeCuit( $cuit ) );
        if( $res->getStatusCode() != 200 ) return false;
        return json_decode( $res->getBody() );
    }
}
// vim:sw=4 ts=4 sts=4 et
