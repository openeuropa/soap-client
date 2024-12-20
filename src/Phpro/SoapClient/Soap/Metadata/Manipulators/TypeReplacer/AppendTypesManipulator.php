<?php
declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer;

use Phpro\SoapClient\Soap\Metadata\Manipulators\TypesManipulatorInterface;
use Soap\Engine\Metadata\Collection\TypeCollection;

final class AppendTypesManipulator implements TypesManipulatorInterface
{
    /**
     * @param callable(TypeCollection): TypeCollection $buildAppendedTypes
     */
    public function __construct(
        private readonly mixed $buildAppendedTypes
    ) {
    }

    public function __invoke(TypeCollection $types): TypeCollection
    {
        return new TypeCollection(
            ...$types,
            ...($this->buildAppendedTypes)($types),
        );
    }
}
