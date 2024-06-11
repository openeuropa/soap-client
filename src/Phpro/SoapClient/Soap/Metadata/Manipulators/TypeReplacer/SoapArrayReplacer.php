<?php
declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer;

use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;
use Soap\WsdlReader\Metadata\Predicate\IsOfType;
use Soap\WsdlReader\Model\Definitions\EncodingStyle;
use function Psl\Iter\any;
use function Psl\Vec\map;

final class SoapArrayReplacer implements TypeReplacer
{
    public function __invoke(XsdType $xsdType): XsdType
    {
        if (!$this->isArrayType($xsdType)) {
            return $xsdType;
        }

        return $xsdType->copy('array')
            ->withBaseType('array')
            ->withMeta(
                // Mark as simple to make sure no additional types are generated for this type.
                static fn (TypeMeta $meta): TypeMeta => $meta->withIsSimple(true)
            );
    }

    private function isArrayType(XsdType $type): bool
    {
        $checks = [
            new IsOfType(EncodingStyle::SOAP_11->value, 'Array'),
            ...map(
                EncodingStyle::listKnownSoap12Version(),
                /**
                 * @param non-empty-string $namespace
                 */
                static fn (string $namespace): IsOfType => new IsOfType($namespace, 'Array')
            ),
        ];

        return any($checks, static fn (IsOfType $check): bool => $check($type));
    }
}
