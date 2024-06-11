<?php
declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer;

use Soap\Engine\Metadata\Model\XsdType;

interface TypeReplacer
{
    public function __invoke(XsdType $xsdType): XsdType;
}
