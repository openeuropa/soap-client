<?php
declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Metadata\Manipulators\TypeReplacer;

use Phpro\SoapClient\Soap\Metadata\Manipulators\MethodsManipulatorInterface;
use Soap\Engine\Metadata\Collection\MethodCollection;
use Soap\Engine\Metadata\Collection\ParameterCollection;
use Soap\Engine\Metadata\Model\Method;
use Soap\Engine\Metadata\Model\Parameter;
use function Psl\Vec\map;

final class ReplaceMethodTypesManipulator implements MethodsManipulatorInterface
{
    public function __construct(
        private readonly TypeReplacer $typeReplacer
    ) {
    }

    public function __invoke(MethodCollection $methods): MethodCollection
    {
        return new MethodCollection(
            ...map($methods, $this->replaceMethodTypes(...))
        );
    }

    private function replaceMethodTypes(Method $method): Method
    {
        return new Method(
            $method->getName(),
            new ParameterCollection(...map(
                $method->getParameters(),
                fn(Parameter $parameter): Parameter => new Parameter(
                    $parameter->getName(),
                    ($this->typeReplacer)($parameter->getType()),
                )
            )),
            ($this->typeReplacer)($method->getReturnType()),
        );
    }
}
