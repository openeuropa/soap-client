<?php

declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Metadata\Detector;

use Soap\Engine\Metadata\Collection\PropertyCollection;
use Soap\Engine\Metadata\Collection\TypeCollection;
use Soap\Engine\Metadata\Model\Type;

/**
 * Some XSD types like attributes contain local enums that are not globally available as a type.
 * This method unwinds them into a separate list.
 */
final class LocalEnumDetector
{
    public function __invoke(TypeCollection $types): TypeCollection
    {
        $detected = [];

        foreach ($types as $type) {
            foreach ($type->getProperties() as $property) {
                $xsdType = $property->getType();
                $meta = $xsdType->getMeta();
                $isLocal = $meta->isLocal()->unwrapOr(false);
                $isEnum = $meta->enums()->isSome();

                if ($isLocal && $isEnum) {
                    $detected[] = new Type($xsdType, new PropertyCollection());
                }
            }
        }

        return new TypeCollection(...$detected);
    }
}
