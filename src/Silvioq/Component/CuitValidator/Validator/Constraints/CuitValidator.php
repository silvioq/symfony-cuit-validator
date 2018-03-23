<?php

namespace Silvioq\Component\CuitValidator\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Silvioq\Component\CuitValidator\Inspector\CuitInspector;
use Silvioq\Component\CuitValidator\Inspector\InvalidCuitException;
use Silvioq\Component\CuitValidator\Inspector\CuitInspectionException;
use Silvioq\Component\CuitValidator\Validator\ForeignCuitChecker;

/**
 * @author silvioq
 */

class CuitValidator extends ConstraintValidator
{
    private $inspector = null;
    public function __construct( CuitInspector $inspector = null )
    {
        $this->inspector = $inspector !== null ? $inspector : new CuitInspector();
    }

    public function validate($value, Constraint $constraint)
    {
        if( null === $value ) return;

        $k = array(5,4,3,2,7,6,5,4,3,2);
        $cuit = CuitInspector::sanitizeCuit( $value );

        // si no estan todos los digitos
        if (strlen($cuit) != 11) {
            $this->context
                ->buildViolation($constraint->incorrectLenMessage)
                ->addViolation();

            return;
        }

        $sumador = 0;
        $verificador = substr($cuit, 10, 1); //tomo el digito verificador
 
        for ($i=0; $i <=9; $i=$i+1)
            $sumador = $sumador + (substr($cuit, $i, 1)) * $k[$i];//separo cada digito y lo multiplico por el coeficiente
 
        $resultado = $sumador % 11;
        if( $resultado === 0 )
        {
            $resultado = 0;
        }
        else if( $resultado == 1 )
        {
            $resultado = 9;
            if( $cuit[1] != 3 )
            {
                $this->context
                    ->buildViolation($constraint->invalidMessage)
                    ->addViolation();
                return;
            }
        }
        else
            $resultado = 11 - $resultado;  //saco el digito verificador
 
        if (intval($verificador) != $resultado) {
            $this->context
                ->buildViolation($constraint->invalidMessage)
                ->addViolation();
            return;
        }

        if( $constraint->checkState )
        {
            try
            {
                $data = $this->inspector->getSimpleData($cuit);
                if( "ACTIVO" !== $data['estado'] )
                {
                    $this->context
                        ->buildViolation($constraint->cuitNotActiveMessage )
                        ->addViolation();
                }
            }
            catch( CuitInspectionException $ce )
            {
                if( $constraint->throwOnNetworkError )
                {
                    throw $ce;
                }
                else
                {
                    $this->context
                        ->buildViolation($constraint->serviceNotActiveMessage)
                        ->addViolation();
                }
            }
            catch( InvalidCuitException $ic )
            {
                if( $ic->getMessage() === 'No existe persona con ese Id' )
                    $this->context
                        ->buildViolation($constraint->invalidMessage)
                        ->addViolation();
                else
                    $this->context
                        ->buildViolation($constraint->serviceNotActiveMessage)
                        ->addViolation();
            }
        }

        if (true === $constraint->foreignCuit) {
            if (!(new ForeignCuitChecker())->isForeign($cuit)) {
                $this->context
                    ->buildViolation($constraint->invalidForeignCuit)
                    ->addViolation();
            }
        }
    }
}
// vim:sw=4 ts=4 sts=4 et
