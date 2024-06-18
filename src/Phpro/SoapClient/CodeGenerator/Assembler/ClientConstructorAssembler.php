<?php
declare(strict_types=1);

namespace Phpro\SoapClient\CodeGenerator\Assembler;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Phpro\SoapClient\Caller\Caller;
use Phpro\SoapClient\CodeGenerator\Context\ClientContext;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\LaminasCodeFactory\DocBlockGeneratorFactory;
use Phpro\SoapClient\Exception\AssemblerException;
use function Psl\Type\non_empty_string;

class ClientConstructorAssembler implements AssemblerInterface
{
    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof ClientContext;
    }

    public function assemble(ContextInterface $context)
    {
        if (!$context instanceof ClientContext) {
            throw new AssemblerException(
                __METHOD__.' expects an '.ClientContext::class.' as input '.get_class($context).' given'
            );
        }

        $class = $context->getClass();
        try {
            $caller = $this->generateClassNameAndAddImport(Caller::class, $class);
            $class->addPropertyFromGenerator(
                (new PropertyGenerator('caller'))
                    ->setVisibility(PropertyGenerator::VISIBILITY_PRIVATE)
                    ->omitDefaultValue(true)
                    ->setDocBlock((new DocBlockGenerator())
                        ->setWordWrap(false)
                        ->setTags([
                            [
                                'name'        => 'var',
                                'description' => $caller,
                            ],
                        ])),
            );
            $class->addMethodFromGenerator(
                (new MethodGenerator('__construct'))
                    ->setParameter(new ParameterGenerator('caller', Caller::class))
                    ->setVisibility(MethodGenerator::VISIBILITY_PUBLIC)
                    ->setBody('$this->caller = $caller;')
            );
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }

        return true;
    }

    /**
     * @param non-empty-string $fqcn
     */
    private function generateClassNameAndAddImport(string $fqcn, ClassGenerator $class): string
    {
        $fqcn = non_empty_string()->assert(ltrim($fqcn, '\\'));
        $parts = explode('\\', $fqcn);
        $className = array_pop($parts);

        if (!\in_array($fqcn, $class->getUses(), true)) {
            $class->addUse($fqcn);
        }

        return $className;
    }
}
