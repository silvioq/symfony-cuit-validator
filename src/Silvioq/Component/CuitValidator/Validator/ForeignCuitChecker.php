<?php

namespace Silvioq\Component\CuitValidator\Validator;

class ForeignCuitChecker
{
    /** @var array */
    private $list;

    public function __construct(array $foreignList = null)
    {
        if (null === $foreignList)
            $this->list = require(__DIR__ . '/../Resources/data/foreign_cuits.php');
        else
            $this->list = $foreignList;
    }

    public function isForeign(string $cuit):bool
    {
        return in_array($cuit, $this->list);
    }
}
// vim:sw=4 ts=4 sts=4 et
