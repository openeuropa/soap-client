<?php
declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer;

use Phpro\SoapClient\Soap\Metadata\Manipulators\TypesManipulatorInterface;
use Soap\Engine\Metadata\Collection\PropertyCollection;
use Soap\Engine\Metadata\Collection\TypeCollection;
use Soap\Engine\Metadata\Model\Type;
use Soap\Engine\Metadata\Model\Property;
use function Psl\Vec\map;

final class ReplaceTypesManipulator implements TypesManipulatorInterface
{
    public function __construct(
        private readonly TypeReplacer $typeReplacer
    ) {
    }

    public function __invoke(TypeCollection $types): TypeCollection
    {
        return new TypeCollection(
            ...map($types, $this->replaceTypeTypes(...))
        );
    }

    private function replaceTypeTypes(Type $type): Type
    {
        return new Type(
            ($this->typeReplacer)($type->getXsdType()),
            new PropertyCollection(
                ...map(
                    $type->getProperties(),
                    fn(Property $property): Property => new Property(
                        $property->getName(),
                        ($this->typeReplacer)($property->getType())
                    ),
                )
            )
        );
    }
}
