<?php

namespace Silvioq\Component\CuitValidator\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 * @author silvioq
 */
class Cuit extends Constraint
{
    public $invalidMessage = 'CUIT/CUIL is invalid';
    public $incorrectLenMessage = 'CUIT/CUIL has incorrect length';
    public $cuitNotActiveMessage = 'CUIT/CUIL is not active';
    public $serviceNotActiveMessage = 'AFIP service is not active or not could be reached';
    public $invalidForeignCuit = 'CUIT/CUIL is not foreign.';
    public $checkState = false;
    public $throwOnNetworkError = true;
    public $foreignCuit = false;
}
// vim:sw=4 ts=4 sts=4 et
