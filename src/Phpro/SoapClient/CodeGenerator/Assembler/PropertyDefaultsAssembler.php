<?php

namespace Phpro\SoapClient\CodeGenerator\Assembler;

use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Context\PropertyContext;
use Phpro\SoapClient\Exception\AssemblerException;
use function Psl\Result\wrap;

final class PropertyDefaultsAssembler implements AssemblerInterface
{
    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof PropertyContext;
    }

    /**
     * @param ContextInterface|PropertyContext $context
     *
     * @throws AssemblerException
     */
    public function assemble(ContextInterface $context): void
    {
        $class = $context->getClass();
        $property = $context->getProperty();
        $propertyGenerator = $class->getProperty($property->getName());
        if (!$propertyGenerator) {
            return;
        }

        if ($propertyGenerator->getDefaultValue()) {
            return;
        }

        $defaultValue = wrap(
            fn (): mixed => match ($property->getPhpType()) {
                'mixed' => null,
                'string' => '',
                'int' => 0,
                'bool' => false,
                'float' => 0.0,
                'array' => [],
                default => throw new \RuntimeException('Type with unknown default: ' . $property->getPhpType())
            }
        );

        if ($defaultValue->isFailed()) {
            return;
        }

        $propertyGenerator
            ->setDefaultValue($defaultValue->getResult())
            ->omitDefaultValue(false);
    }
}
