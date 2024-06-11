<?php
declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer;

use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;
use Soap\WsdlReader\Metadata\Predicate\IsOfType;
use Soap\WsdlReader\Model\Definitions\EncodingStyle;

final class SoapObjectReplacer implements TypeReplacer
{
    public function __invoke(XsdType $xsdType): XsdType
    {
        $check = new IsOfType(EncodingStyle::SOAP_11->value, 'Struct');
        if (!$check($xsdType)) {
            return $xsdType;
        }

        return $xsdType->copy('struct')
            ->withBaseType('object')
            ->withMeta(
                // Mark as simple to make sure no additional types are generated for this type.
                static fn (TypeMeta $meta): TypeMeta => $meta->withIsSimple(true)
            );
    }
}
